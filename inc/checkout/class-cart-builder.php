<?php
/**
 * Cart Builder class for building cart state from payments and memberships.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 */

namespace WP_Ultimo\Checkout;

use WP_Ultimo\Database\Memberships\Membership_Status;
use WP_Ultimo\Database\Payments\Payment_Status;
use WP_Ultimo\Models\Product;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Cart Builder class for constructing cart state.
 *
 * Handles the complex logic of building cart state from payments,
 * memberships, and calculating pro-rate credits.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 * @since 2.4.8
 */
class Cart_Builder {

	/**
	 * The cart instance being built.
	 *
	 * @var Cart
	 */
	protected Cart $cart;

	/**
	 * Constructor.
	 *
	 * @param Cart $cart The cart instance to build.
	 */
	public function __construct(Cart $cart) {

		$this->cart = $cart;
	}

	/**
	 * Builds the cart.
	 *
	 * Here, we try to determine the type of
	 * cart so we can properly set it up, based
	 * on the payment, membership, and products passed.
	 *
	 * @return void
	 */
	public function build_cart(): void {
		/*
		 * Maybe deal with payment recovery first.
		 */
		$is_recovery_cart = $this->build_from_payment((int) $this->cart->get_attribute('payment_id'));

		/*
		 * If we are recovering a payment, we stop right here.
		 * The pending payment object has all the info we need
		 * to build the proper cart.
		 */
		if ($is_recovery_cart) {
			return;
		}

		/*
		 * The next step is to deal with membership changes.
		 * These include downgrades/upgrades and addons.
		 */
		$is_membership_change = $this->build_from_membership((int) $this->cart->get_attribute('membership_id'));

		/*
		 * If this is a membership change,
		 * we can return as our work is done.
		 */
		if ($is_membership_change) {
			return;
		}

		/*
		 * Otherwise, we add the products normally,
		 * and set the cart as new.
		 */
		$this->cart->set_cart_type('new');

		$products = $this->cart->get_attribute('products');

		if (is_array($products)) {
			/*
			 * Otherwise, we add the products to build the cart.
			 */
			foreach ($products as $product_id) {
				$this->cart->add_product($product_id);
			}

			/*
			 * Cancel conflicting pending payments for new checkouts.
			 */
			$this->cancel_conflicting_pending_payments();
		}
	}

	/**
	 * Decides if we are trying to recover a payment.
	 *
	 * @param int $payment_id A valid payment ID.
	 */
	protected function build_from_payment(int $payment_id): bool {
		/*
		 * No valid payment id passed, so we
		 * are not trying to recover a payment.
		 */
		if (empty($payment_id)) {
			return false;
		}

		/*
		 * Now, let's try to fetch the payment in question.
		 */
		$payment = wu_get_payment($payment_id);

		if ( ! $payment) {
			$this->cart->get_errors()->add('payment_not_found', __('The payment in question was not found.', 'ultimate-multisite'));

			return true;
		}

		/*
		 * The payment exists, set it globally.
		 */
		$this->cart->set_payment($payment);

		/*
		 * Adds the country to calculate taxes.
		 */
		$customer = $this->cart->get_customer();
		$country  = $this->cart->get_country();

		if (empty($country)) {
			$this->cart->set_country($customer ? $customer->get_country() : '');
		}

		/*
		 * Set the currency in the cart
		 */
		$this->cart->set_currency($payment->get_currency());

		/*
		 * Check for the correct permissions.
		 *
		 * For obvious reasons, only the customer that owns
		 * a payment can pay it. Let's check for that.
		 */
		if (empty($customer) || $customer->get_id() !== $payment->get_customer_id()) {
			$this->cart->get_errors()->add('lacks_permission', __('You are not allowed to modify this payment.', 'ultimate-multisite'));

			return true;
		}

		/*
		 * Sets the membership as well to prevent issues
		 */
		$membership = $payment->get_membership();

		if ( ! $membership) {
			$this->cart->get_errors()->add('membership_not_found', __('The membership in question was not found.', 'ultimate-multisite'));

			return true;
		}

		if ($payment->get_discount_code()) {
			/**
			 *  First check if is a membership discount code;
			 */
			$discount_code = $membership->get_discount_code();

			if ($discount_code && $discount_code->get_code() === $payment->get_discount_code()) {
				$this->cart->add_discount_code($discount_code);
			} else {
				$this->cart->add_discount_code($payment->get_discount_code());
			}
		}

		/*
		 * Sets membership globally.
		 */
		$this->cart->set_membership($membership);
		$this->cart->set_duration($membership->get_duration());
		$this->cart->set_duration_unit($membership->get_duration_unit());

		$duration      = $membership->get_duration();
		$duration_unit = $membership->get_duration_unit();

		/*
		 * Finally, copy the line items from the payment.
		 */
		foreach ($payment->get_line_items() as $line_item) {
			$product = $line_item->get_product();

			if ($product) {
				if ($product->is_recurring() && ($product->get_duration_unit() !== $duration_unit || $product->get_duration() !== $duration)) {
					$product_variation = $product->get_as_variation($duration, $duration_unit);

					/*
					 * Checks if the variation exists before re-setting the product.
					 */
					if ($product_variation) {
						$product = $product_variation;
					}
				}

				$this->cart->add_to_products($product);

				if ($line_item->get_type() === 'product' && $product->get_type() === 'plan') {
					/*
					 * If we already have a plan, we can't add
					 * another one.
					 */
					if (empty($this->cart->get_plan_id())) {
						$this->cart->set_plan_id($product->get_id());
						$this->cart->set_billing_cycles($product->get_billing_cycles());

						$this->cart->set_duration($line_item->get_duration());
						$this->cart->set_duration_unit($line_item->get_duration_unit());
					}
				}
			}

			$this->cart->add_line_item($line_item);
		}

		/*
		 * If the payment is completed
		 * this can't be a retry, so we skip
		 * the rest.
		 */
		if ($payment->get_status() === 'completed') {
			/**
			 * We should return false to continue in case of membership updates.
			 */
			return false;
		}

		/*
		 * Check for payment status.
		 *
		 * We want to make sure we only allow for repayment of pending,
		 * canceled, or abandoned payments
		 */
		$allowed_status = apply_filters(
			'wu_cart_set_payment_allowed_status',
			[
				'pending',
			]
		);

		if ( ! in_array($payment->get_status(), $allowed_status, true)) {
			$this->cart->get_errors()->add('invalid_status', __('The payment in question has an invalid status.', 'ultimate-multisite'));

			return true;
		}

		/*
		 * If the membership is active or is
		 * already in trial, this can't be a
		 * retry, so we skip the rest.
		 */
		if ($membership->is_active() || ($membership->get_status() === Membership_Status::TRIALING && ! $this->cart->has_trial())) {
			return false;
		}

		/*
		 * We got here; that means
		 * the intent behind this cart was to actually
		 * recover a payment.
		 *
		 * That means we can safely set the cart type to retry.
		 */
		$this->cart->set_cart_type('retry');

		return true;
	}

	/**
	 * Uses the membership to decide if this is an upgrade/downgrade/addon cart.
	 *
	 * @param int $membership_id A valid membership ID.
	 */
	protected function build_from_membership(int $membership_id): bool {
		/*
		 * No valid membership id passed, so we
		 * are not trying to change a membership.
		 */
		if (empty($membership_id)) {
			return false;
		}

		/*
		 * We got here; that means
		 * the intent behind this cart was to actually
		 * change a membership.
		 *
		 * We can set the cart type provisionally.
		 * This assignment might change in the future, as we make
		 * additional assertions about the contents of the cart.
		 */
		$this->cart->set_cart_type('upgrade');

		/*
		 * Now, let's try to fetch the membership in question.
		 */
		$membership = wu_get_membership($membership_id);

		if ( ! $membership) {
			$this->cart->get_errors()->add('membership_not_found', __('The membership in question was not found.', 'ultimate-multisite'));

			return true;
		}

		/*
		 * The membership exists, set it globally.
		 */
		$this->cart->set_membership($membership);

		/*
		 * In the case of membership changes,
		 * the status is not that relevant, as customers
		 * might want to make changes to memberships that are
		 * active, canceled, etc.
		 *
		 * We do need to check for permissions, though.
		 * Only the customer that owns a membership can change it.
		 */
		$customer = $this->cart->get_customer();

		if (empty($customer) || $customer->get_id() !== $membership->get_customer_id()) {
			$this->cart->get_errors()->add('lacks_permission', __('You are not allowed to modify this membership.', 'ultimate-multisite'));

			return true;
		}

		/*
		 * Adds the country to calculate taxes.
		 */
		if (empty($this->cart->get_country())) {
			$this->cart->set_country($customer->get_country());
		}

		/*
		 * Set the currency in a cart
		 */
		$this->cart->set_currency($membership->get_currency());

		/*
		 * If we get to this point, we now need to assess
		 * what the changes being made are.
		 *
		 * First, we need to see if there are actual products
		 * being added and process those.
		 */
		$products = $this->cart->get_attribute('products');

		if (empty($products)) {
			if ($this->cart->get_payment()) {
				/**
				 *  If we do not have any change, but we have an already
				 *  created payment, it means that this cart is to pay
				 *  for this.
				 */
				return false;
			}

			$this->cart->get_errors()->add('no_changes', __('This cart proposes no changes to the current membership.', 'ultimate-multisite'));

			return true;
		}

		/*
		 * Otherwise, we add the products to build the cart.
		 */
		foreach ($products as $product_id) {
			$this->cart->add_product($product_id);
		}

		/*
		 * With products added, let's check if this is an addon.
		 *
		 * An addon cart adds a new product or service to the current membership.
		 * If this cart, after adding the products, doesn't have a plan, it means
		 * it should continue to use the membership plan, and the other products
		 * must be added to the membership.
		 */
		if (empty($this->cart->get_plan_id())) {
			if (count($this->cart->get_all_products()) === 0) {
				$this->cart->get_errors()->add('no_changes', __('This cart proposes no changes to the current membership.', 'ultimate-multisite'));

				return true;
			}

			/*
			 * Set the type to addon.
			 */
			$this->cart->set_cart_type('addon');

			/*
			 * Sets the durations to avoid problems
			 * with addon purchases.
			 */
			$plan_product = $membership->get_plan();

			if ($plan_product && ! $membership->is_free()) {
				$this->cart->set_duration($plan_product->get_duration());
				$this->cart->set_duration_unit($plan_product->get_duration_unit());
			}

			/*
			 * Checks the membership to see if we need to add back the
			 * setup fee.
			 *
			 * If the membership was already successfully charged once,
			 * it probably means that the setup fee was already paid, so we can skip it.
			 */
			add_filter('wu_apply_signup_fee', fn() => $membership->get_times_billed() <= 0);

			/*
			 * Adds the membership plan back in, for completeness.
			 * This is also useful to make sure we present
			 * the totals correctly for the customer.
			 */
			$this->cart->add_product($membership->get_plan_id());

			/*
			 * Adds the credit line after
			 * calculating pro-rate.
			 */
			$this->calculate_prorate_credits();

			return true;
		}

		/*
		 * With products added, let's check if the plan is changing.
		 *
		 * A plan change implies an upgrade or a downgrade, which we will determine
		 * below.
		 *
		 * A plan change can take many forms.
		 * - Different plan altogether;
		 * - Same plan with different periodicity;
		 * - upgrade to lifetime;
		 * - downgrade to free;
		 */
		$is_plan_change = false;

		$duration      = $this->cart->get_duration();
		$duration_unit = $this->cart->get_duration_unit();

		if ($membership->get_plan_id() !== $this->cart->get_plan_id() || $membership->get_duration_unit() !== $duration_unit || $membership->get_duration() !== $duration) {
			$is_plan_change = true;
		}

		/*
		 * Checks for periodicity changes.
		 */
		$old_periodicity = sprintf('%s-%s', $membership->get_duration(), $membership->get_duration_unit());
		$new_periodicity = sprintf('%s-%s', $duration, $duration_unit);

		if ($old_periodicity !== $new_periodicity) {
			$is_plan_change = true;
		}

		/*
		 * If there is no plan change, but the product count is > 1
		 * We know that there is another product in this cart other than the
		 * plan, so this is again an addon cart.
		 */
		if (count($this->cart->get_all_products()) > 1 && false === $is_plan_change) {
			/*
			 * Set the type to addon.
			 */
			$this->cart->set_cart_type('addon');

			/*
			 * Sets the durations to avoid problems
			 * with addon purchases.
			 */
			$plan_product = $membership->get_plan();

			if ($plan_product && ! $membership->is_free()) {
				$this->cart->set_duration($plan_product->get_duration());
				$this->cart->set_duration_unit($plan_product->get_duration_unit());
			}

			/*
			 * Checks the membership to see if we need to add back the
			 * setup fee.
			 *
			 * If the membership was already successfully charged once,
			 * it probably means that the setup fee was already paid, so we can skip it.
			 */
			add_filter('wu_apply_signup_fee', fn() => $membership->get_times_billed() <= 0);

			/*
			 * Adds the credit line after
			 * calculating pro-rate.
			 */
			$this->calculate_prorate_credits();

			return true;
		}

		/*
		 * We'll probably never enter this if, but we
		 * hev it here to prevent bugs.
		 */
		if ( ! $is_plan_change || ($this->cart->get_plan_id() === $membership->get_plan_id() && $membership->get_duration_unit() === $duration_unit && $membership->get_duration() === $duration)) {
			$this->cart->clear_products();
			$this->cart->clear_line_items();

			$this->cart->get_errors()->add('no_changes', __('This cart proposes no changes to the current membership.', 'ultimate-multisite'));

			return true;
		} else {
			$new_plan        = $this->cart->get_plan();
			$new_limitations = $new_plan->get_limitations();
			$sites           = $this->cart->get_membership()->get_sites(false);
			foreach ($sites as $site) {
				switch_to_blog($site->get_id());

				$overlimits = $new_limitations->post_types->check_all_post_types();

				if ($overlimits) {
					foreach ($overlimits as $post_type_slug => $limit) {
						$post_type = get_post_type_object($post_type_slug);

						$this->cart->get_errors()->add(
							'overlimits_' . $post_type_slug,
							sprintf(
							// translators: %1$d: current number of posts, %2$s: post-type name, %3$d: post quota, %4$s: post-type name, %5$d: number of posts to be deleted, %6$s: post-type name.
								esc_html__('Your site currently has %1$d %2$s but the new plan is limited to %3$d %4$s. You must trash %5$d %6$s before you can downgrade your plan.', 'ultimate-multisite'),
								$limit['current'],
								$limit['current'] > 1 ? $post_type->labels->name : $post_type->labels->singular_name,
								$limit['limit'],
								$limit['limit'] > 1 ? $post_type->labels->name : $post_type->labels->singular_name,
								$limit['current'] - $limit['limit'],
								$limit['current'] - $limit['limit'] > 1 ? $post_type->labels->name : $post_type->labels->singular_name
							)
						);
					}
					restore_current_blog();

					return true;
				}

				// Check domain mapping limits for downgrade
				$domain_overlimits = $new_limitations->domain_mapping->check_all_domains($site->get_id());

				if ($domain_overlimits) {
					$domain_count = $domain_overlimits['current'];
					$domain_limit = $domain_overlimits['limit'];

					if (0 === $domain_limit) {
						$this->cart->get_errors()->add(
							'overlimits',
							esc_html__('This new plan does NOT support custom domains. You must remove all custom domains before you can downgrade your plan.', 'ultimate-multisite'),
						);
					} else {
						$this->cart->get_errors()->add(
							'overlimits',
							sprintf(
							// translators: %1$d: current number of custom domains, %2$s: 'custom domain' or 'custom domains', %3$d: domain limit, %4$s: 'custom domain' or 'custom domains', %5$d: number of domains to be removed, %6$s: 'custom domain' or 'custom domains'.
								esc_html__('Your site currently has %1$d %2$s but the new plan is limited to %3$d %4$s. You must remove %5$d %6$s before you can downgrade your plan.', 'ultimate-multisite'),
								$domain_count,
								$domain_count > 1 ? __('custom domains', 'ultimate-multisite') : __('custom domain', 'ultimate-multisite'),
								$domain_limit,
								$domain_limit > 1 ? __('custom domains', 'ultimate-multisite') : __('custom domain', 'ultimate-multisite'),
								$domain_count - $domain_limit,
								($domain_count - $domain_limit) > 1 ? __('custom domains', 'ultimate-multisite') : __('custom domain', 'ultimate-multisite')
							)
						);
					}
					restore_current_blog();

					return true;
				}
				restore_current_blog();
			}
		}

		/*
		 * Upgrade to Lifetime.
		 */
		if ( ! $this->cart->has_recurring() && ! $this->cart->is_free()) {
			/*
			 * Adds the credit line after
			 * calculating pro-rate.
			 */
			$this->calculate_prorate_credits();

			return true;
		}

		/*
		 * If we get to this point, we know that this is either
		 * an upgrade or a downgrade, so we need to determine which.
		 *
		 * Since by default we set the value to upgrade,
		 * we just need to check for a downgrade scenario.
		 */
		$days_in_old_cycle = wu_get_days_in_cycle($membership->get_duration_unit(), $membership->get_duration());
		$days_in_new_cycle = wu_get_days_in_cycle($duration_unit, $duration);

		$old_price_per_day = $days_in_old_cycle > 0 ? $membership->get_amount() / $days_in_old_cycle : $membership->get_amount();
		$new_price_per_day = $days_in_new_cycle > 0 ? $this->cart->get_recurring_total() / $days_in_new_cycle : $this->cart->get_recurring_total();

		$is_same_product = $membership->get_plan_id() === $this->cart->get_plan_id();

		/**
		 * Here we search for variations of the plans
		 * with the same duration to avoid mistakes
		 * when setting a downgrade cart.
		 */
		if ($days_in_old_cycle !== $days_in_new_cycle) {
			$old_plan = $membership->get_plan();
			$new_plan = $this->cart->get_plan();

			$variations = $this->search_for_same_period_plans($old_plan, $new_plan);

			if ($variations) {
				$old_plan = $variations[0];
				$new_plan = $variations[1];

				$days_in_old_cycle_plan = wu_get_days_in_cycle($old_plan->get_duration_unit(), $old_plan->get_duration());
				$days_in_new_cycle_plan = wu_get_days_in_cycle($new_plan->get_duration_unit(), $new_plan->get_duration());

				$old_price_per_day = $days_in_old_cycle_plan > 0 ? $old_plan->get_amount() / $days_in_old_cycle_plan : $old_plan->get_amount();
				$new_price_per_day = $days_in_new_cycle_plan > 0 ? $new_plan->get_amount() / $days_in_new_cycle_plan : $new_plan->get_amount();
			}
		}

		if ( ! $membership->is_free() && $old_price_per_day < $new_price_per_day && $days_in_old_cycle > $days_in_new_cycle && $membership->get_status() === Membership_Status::ACTIVE) {
			$this->cart->clear_products();
			$this->cart->clear_line_items();

			$description = sprintf(
				1 === $membership->get_duration() ? '%2$s' : '%1$s %2$s',
				$membership->get_duration(),
				wu_get_translatable_string(($membership->get_duration() <= 1 ? $membership->get_duration_unit() : $membership->get_duration_unit() . 's'))
			);

			// Translators: Placeholder receives the recurring period description
			$message = sprintf(__('You already have an active %s agreement.', 'ultimate-multisite'), $description);

			$this->cart->get_errors()->add('no_changes', $message);

			return true;
		}

		/*
		 * If is the same product and the customer will start to pay less
		 * or if is a different product and the price per day is smaller
		 * this is a downgrade
		 */
		if (($is_same_product && $membership->get_amount() > $this->cart->get_recurring_total()) || (! $is_same_product && $old_price_per_day > $new_price_per_day)) {
			$this->cart->set_cart_type('downgrade');

			// If membership is active or trialing, we will schedule the swap
			if ($membership->is_active() || $membership->get_status() === Membership_Status::TRIALING) {
				$line_item_params = apply_filters(
					'wu_checkout_credit_line_item_params',
					[
						'type'         => 'credit',
						'title'        => __('Scheduled Swap Credit', 'ultimate-multisite'),
						'description'  => __('Swap scheduled to next billing cycle.', 'ultimate-multisite'),
						'discountable' => false,
						'taxable'      => false,
						'quantity'     => 1,
						'unit_price'   => - $this->cart->get_total(),
					]
				);

				$credit_line_item = new Line_Item($line_item_params);

				$this->cart->add_line_item($credit_line_item);
			}
		}

		// If this is an upgrade, we need to prorate the current amount
		if ('upgrade' === $this->cart->get_cart_type()) {
			$this->calculate_prorate_credits();
		}

		/*
		 * All set!
		 */
		return true;
	}

	/**
	 * Search for variations of the plans with the same duration.
	 *
	 * @param Product $plan_a The first plan without variations.
	 * @param Product $plan_b The second plan without variations.
	 *
	 * @return array
	 */
	protected function search_for_same_period_plans(Product $plan_a, Product $plan_b): array {

		if ($plan_a->get_duration_unit() === $plan_b->get_duration_unit() && $plan_a->get_duration() === $plan_b->get_duration()) {
			return [
				$plan_a,
				$plan_b,
			];
		}

		$plan_a_variation = $plan_a->get_as_variation($plan_b->get_duration(), $plan_b->get_duration_unit());

		if ($plan_a_variation) {
			return [
				$plan_a_variation,
				$plan_b,
			];
		}

		$plan_b_variation = $plan_b->get_as_variation($plan_a->get_duration(), $plan_a->get_duration_unit());

		if ($plan_b_variation) {
			return [
				$plan_a,
				$plan_b_variation,
			];
		}

		$duration      = $this->cart->get_duration();
		$duration_unit = $this->cart->get_duration_unit();

		if ($duration_unit && $duration && ($plan_b->get_duration_unit() !== $duration_unit || $plan_b->get_duration() !== $duration)) {
			$plan_a_variation = $plan_a->get_as_variation($duration, $duration_unit);

			if ( ! $plan_a_variation) {
				return [];
			}

			if ($plan_b->get_duration_unit() === $plan_a_variation->get_duration_unit() && $plan_b->get_duration() === $plan_a_variation->get_duration()) {
				return [
					$plan_a_variation,
					$plan_b,
				];
			}

			$plan_b_variation = $plan_b->get_as_variation($duration, $duration_unit);

			if ($plan_b_variation) {
				return [
					$plan_a_variation,
					$plan_b_variation,
				];
			}
		}

		return [];
	}

	/**
	 * Calculate pro-rate credits.
	 *
	 * @return void
	 */
	public function calculate_prorate_credits(): void {
		/*
		 * Now we come to the craziest part: pro-rating!
		 *
		 * This is super hard to get right, but we basically need to add
		 * new line items to account for the time using the old plan.
		 */

		$membership = $this->cart->get_membership();

		/*
		 * If the membership is in a trial period, there's nothing to prorate.
		 */
		if ($membership->get_status() === Membership_Status::TRIALING) {
			return;
		}

		if ($membership->is_lifetime() || ! $membership->is_recurring()) {
			$credit = $membership->get_initial_amount();
		} else {
			$days_unused = $membership->get_remaining_days_in_cycle();

			$days_in_old_cycle = wu_get_days_in_cycle($membership->get_duration_unit(), $membership->get_duration());

			$old_price_per_day = $days_in_old_cycle > 0 ? $membership->get_amount() / $days_in_old_cycle : $membership->get_amount();

			if (($membership->get_date_created() && wu_date($membership->get_date_created())->format('Y-m-d') === wu_date()->format('Y-m-d')) ||
				($membership->get_date_renewed() && wu_date($membership->get_date_renewed())->format('Y-m-d') === wu_date()->format('Y-m-d'))) {
				// If the membership was created today, We'll use the average days in the cycle to prevent some odd numbers.
				$days_unused = $days_in_old_cycle;
			}

			$credit = $days_unused * $old_price_per_day;

			if ($credit > $membership->get_amount()) {
				$credit = $membership->get_amount();
			}
		}

		/*
		 * No credits
		 */
		if (empty($credit)) {
			return;
		}

		/*
		 * Checks if we need to add back the value of the
		 * setup fee
		 */
		$has_setup_fee = $this->cart->get_line_items_by_type('fee');

		if ( ! empty($has_setup_fee) || $this->cart->get_cart_type() === 'upgrade') {
			$old_plan = $membership->get_plan();

			$new_plan = $this->cart->get_plan();

			if ($old_plan && $new_plan) {
				$old_setup_fee = $old_plan->get_setup_fee();
				$new_setup_fee = $new_plan->get_setup_fee();

				$fee_credit = min($old_setup_fee, $new_setup_fee);

				if ($fee_credit > 0) {
					$new_line_item = new Line_Item(
						[
							'product'     => $old_plan,
							'type'        => 'fee',
							'description' => '--',
							'title'       => '',
							'taxable'     => $old_plan->is_taxable(),
							'recurring'   => false,
							'unit_price'  => $fee_credit,
							'quantity'    => 1,
						]
					);

					$new_line_item = $this->cart->apply_taxes_to_item($new_line_item);

					$new_line_item->recalculate_totals();

					$credit += $new_line_item->get_total();
				}
			}
		}

		/**
		 * Allow plugin developers to meddle with the credit value.
		 *
		 * @param int  $credit The credit amount.
		 * @param Cart $cart This cart object.
		 */
		$credit = apply_filters('wu_checkout_calculate_prorate_credits', $credit, $this->cart);

		$credit = round($credit, wu_currency_decimal_filter());

		/*
		 * No credits
		 */
		if (empty($credit)) {
			return;
		}

		$line_item_params = apply_filters(
			'wu_checkout_credit_line_item_params',
			[
				'type'         => 'credit',
				'title'        => __('Credit', 'ultimate-multisite'),
				'description'  => __('Prorated amount based on the previous membership.', 'ultimate-multisite'),
				'discountable' => false,
				'taxable'      => false,
				'quantity'     => 1,
				'unit_price'   => - $credit,
			]
		);

		/*
		 * Finally, we add the credit to the purchase.
		 */
		$credit_line_item = new Line_Item($line_item_params);

		$this->cart->add_line_item($credit_line_item);
	}

	/**
	 * Cancels conflicting pending payments for new checkouts.
	 *
	 * @return void
	 */
	protected function cancel_conflicting_pending_payments(): void {

		$customer = $this->cart->get_customer();

		if ($this->cart->get_cart_type() !== 'new' || ! $customer) {
			return;
		}

		$pending_payments = wu_get_payments(
			[
				'customer_id' => $customer->get_id(),
				'status'      => Payment_Status::PENDING,
			]
		);

		foreach ($pending_payments as $payment) {
			// Cancel if it's a different cart (simple check: different total or products)
			$payment_total = $payment->get_total();
			$cart_total    = $this->cart->get_total();

			if (abs($payment_total - $cart_total) > 0.01) { // Allow small differences
				$payment->set_status(Payment_Status::CANCELLED);
				$payment->save();
			}
		}
	}
}
