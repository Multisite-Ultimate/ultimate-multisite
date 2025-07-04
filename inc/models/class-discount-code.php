<?php
/**
 * The Discount_Code model for the Discount Codes.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Models;

use WP_Ultimo\Models\Base_Model;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Discount_Code model class. Implements the Base Model.
 *
 * @since 2.0.0
 */
class Discount_Code extends Base_Model {

	use \WP_Ultimo\Traits\WP_Ultimo_Coupon_Deprecated;

	/**
	 * Name of the discount code.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name;

	/**
	 * Code to redeem the discount code.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $code;

	/**
	 * Text describing the coupon code. Useful for identifying it.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $description;

	/**
	 * Number of times this discount was applied.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $uses = 0;

	/**
	 * The number of times this discount can be used before becoming inactive.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $max_uses;

	/**
	 * If we should apply the discount to renewals as well.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $apply_to_renewals = false;

	/**
	 * Type of the discount. Can be a percentage or absolute.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $type = 'percentage';

	/**
	 * Amount discounted in cents.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $value = 0;

	/**
	 * Type of the discount for the setup fee value. Can be a percentage or absolute.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $setup_fee_type = 'percentage';

	/**
	 * Amount discounted fpr setup fees in cents.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $setup_fee_value = 0;

	/**
	 * If this coupon code is active or not.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $active = 1;

	/**
	 * If we should check for products or not.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $limit_products;

	/**
	 * Holds the list of allowed products.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $allowed_products;

	/**
	 * Start date for the coupon code to be considered valid.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_start;

	/**
	 * Expiration date for the coupon code.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_expiration;

	/**
	 * Date when this discount code was created.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_created;

	/**
	 * Query Class to the static query methods.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = \WP_Ultimo\Database\Discount_Codes\Discount_Code_Query::class;

	/**
	 * Set the validation rules for this particular model.
	 *
	 * To see how to setup rules, check the documentation of the
	 * validation library we are using: https://github.com/rakit/validation
	 *
	 * @since 2.0.0
	 * @link https://github.com/rakit/validation
	 * @return array
	 */
	public function validation_rules() {

		return [
			'name'              => 'required|min:2',
			'code'              => 'required|min:2|max:20|alpha_dash',
			'uses'              => 'integer|default:0',
			'max_uses'          => 'integer|min:0|default:0',
			'active'            => 'default:1',
			'apply_to_renewals' => 'default:0',
			'type'              => 'default:absolute|in:percentage,absolute',
			'value'             => 'required|numeric',
			'setup_fee_type'    => 'in:percentage,absolute',
			'setup_fee_value'   => 'numeric',
			'allowed_products'  => 'array',
			'limit_products'    => 'default:0',
		];
	}

	/**
	 * Get name of the discount code.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_name() {

		return $this->name;
	}

	/**
	 * Set name of the discount code.
	 *
	 * @since 2.0.0
	 * @param string $name Your discount code name, which is used as discount code title as well.
	 * @return void
	 */
	public function set_name($name): void {

		$this->name = $name;
	}

	/**
	 * Get code to redeem the discount code.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_code() {

		return $this->code;
	}

	/**
	 * Set code to redeem the discount code.
	 *
	 * @since 2.0.0
	 * @param string $code A unique identification to redeem the discount code. E.g. PROMO10.
	 * @return void
	 */
	public function set_code($code): void {

		$this->code = $code;
	}

	/**
	 * Get text describing the coupon code. Useful for identifying it.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return $this->description;
	}

	/**
	 * Set text describing the coupon code. Useful for identifying it.
	 *
	 * @since 2.0.0
	 * @param string $description A description for the discount code, usually a short text.
	 * @return void
	 */
	public function set_description($description): void {

		$this->description = $description;
	}

	/**
	 * Get number of times this discount was applied.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_uses() {

		return (int) $this->uses;
	}

	/**
	 * Set number of times this discount was applied.
	 *
	 * @since 2.0.0
	 * @param int $uses Number of times this discount was applied.
	 * @return void
	 */
	public function set_uses($uses): void {

		$this->uses = (int) $uses;
	}

	/**
	 * Add uses to this discount code.
	 *
	 * @since 2.0.4
	 * @param integer $uses Number of uses to add.
	 * @return void
	 */
	public function add_use($uses = 1): void {

		$use_count = (int) $this->get_uses();

		$this->set_uses($use_count + (int) $uses);
	}

	/**
	 * Get the number of times this discount can be used before becoming inactive.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_max_uses() {

		return (int) $this->max_uses;
	}

	/**
	 * Set the number of times this discount can be used before becoming inactive.
	 *
	 * @since 2.0.0
	 * @param int $max_uses The number of times this discount can be used before becoming inactive.
	 * @return void
	 */
	public function set_max_uses($max_uses): void {

		$this->max_uses = (int) $max_uses;
	}

	/**
	 * Checks if the given discount code has a number of max uses.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_max_uses() {

		return $this->get_max_uses() > 0;
	}

	/**
	 * Get if we should apply this coupon to renewals as well.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function should_apply_to_renewals() {

		return (bool) $this->apply_to_renewals;
	}

	/**
	 * Set if we should apply this coupon to renewals as well.
	 *
	 * @since 2.0.0
	 * @param bool $apply_to_renewals Wether or not we should apply the discount to membership renewals.
	 * @return void
	 */
	public function set_apply_to_renewals($apply_to_renewals): void {

		$this->apply_to_renewals = (bool) $apply_to_renewals;
	}

	/**
	 * Get type of the discount. Can be a percentage or absolute.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return $this->type;
	}

	/**
	 * Set type of the discount. Can be a percentage or absolute.
	 *
	 * @since 2.0.0
	 * @param string $type The type of the discount code. Can be 'percentage' (e.g. 10% OFF), 'absolute' (e.g. $10 OFF).
	 * @options percentage,absolute
	 * @return void
	 */
	public function set_type($type): void {

		$this->type = $type;
	}

	/**
	 * Get amount discounted in cents.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_value() {

		return (float) $this->value;
	}

	/**
	 * Set amount discounted in cents.
	 *
	 * @since 2.0.0
	 * @param int $value Amount discounted in cents.
	 * @return void
	 */
	public function set_value($value): void {

		$this->value = $value;
	}

	/**
	 * Get type of the discount for the setup fee value. Can be a percentage or absolute.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_setup_fee_type() {

		return $this->setup_fee_type;
	}

	/**
	 * Set type of the discount for the setup fee value. Can be a percentage or absolute.
	 *
	 * @since 2.0.0
	 * @param string $setup_fee_type Type of the discount for the setup fee value. Can be a percentage or absolute.
	 * @options percentage,absolute
	 * @return void
	 */
	public function set_setup_fee_type($setup_fee_type): void {

		$this->setup_fee_type = $setup_fee_type;
	}

	/**
	 * Get amount discounted fpr setup fees in cents.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_setup_fee_value() {

		return (float) $this->setup_fee_value;
	}

	/**
	 * Set amount discounted for setup fees in cents.
	 *
	 * @since 2.0.0
	 * @param int $setup_fee_value Amount discounted for setup fees in cents.
	 * @return void
	 */
	public function set_setup_fee_value($setup_fee_value): void {

		$this->setup_fee_value = $setup_fee_value;
	}

	/**
	 * Get if this coupon code is active or not.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_active() {

		return (bool) $this->active;
	}

	/**
	 * Checks if a given coupon code is valid and can be applied.
	 *
	 * @since 2.0.0
	 * @param int|\WP_Ultimo\Models\Product $product Product to check against.
	 * @return true|\WP_Error
	 */
	public function is_valid($product = false) {

		if ($this->is_active() === false) {
			return new \WP_Error('discount_code', __('This coupon code is not valid.', 'multisite-ultimate'));
		}

		/*
		 * Check for uses
		 */
		if ($this->has_max_uses() && $this->get_uses() >= $this->get_max_uses()) {
			return new \WP_Error('discount_code', __('This discount code was already redeemed the maximum amount of times allowed.', 'multisite-ultimate'));
		}

		/*
		 * Fist, check date boundaries.
		 */
		$start_date      = $this->get_date_start();
		$expiration_date = $this->get_date_expiration();

		$now = wu_date();

		if ($start_date) {
			$start_date_instance = wu_date($start_date);

			if ($now < $start_date_instance) {
				return new \WP_Error('discount_code', __('This coupon code is not valid.', 'multisite-ultimate'));
			}
		}

		if ($expiration_date) {
			$expiration_date_instance = wu_date($expiration_date);

			if ($now > $expiration_date_instance) {
				return new \WP_Error('discount_code', __('This coupon code is not valid.', 'multisite-ultimate'));
			}
		}

		if ( ! $this->get_limit_products()) {
			return true;
		}

		if ( ! empty($product)) {
			if (is_a($product, '\WP_Ultimo\Models\Product')) {
				$product_id = $product->get_id();
			} elseif (is_numeric($product)) {
				$product_id = $product;
			}

			$allowed = $this->get_limit_products() && in_array($product_id, $this->get_allowed_products()); // phpcs:ignore

			if (false === $allowed) {
				return new \WP_Error('discount_code', __('This coupon code is not valid.', 'multisite-ultimate'));
			}
		}

		return true;
	}

	/**
	 * Checks if this discount applies just for the first payment.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_one_time() {

		return (bool) $this->should_apply_to_renewals();
	}

	/**
	 * Set if this coupon code is active or not.
	 *
	 * @since 2.0.0
	 * @param bool $active Set this discount code as active (true), which means available to be used, or inactive (false).
	 * @return void
	 */
	public function set_active($active): void {

		$this->active = (bool) $active;
	}

	/**
	 * Get start date for the coupon code to be considered valid.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_start() {

		if ( ! wu_validate_date($this->date_start)) {
			return '';
		}

		return $this->date_start;
	}

	/**
	 * Set start date for the coupon code to be considered valid.
	 *
	 * @since 2.0.0
	 * @param string $date_start Start date for the coupon code to be considered valid.
	 * @return void
	 */
	public function set_date_start($date_start): void {

		$this->date_start = $date_start;
	}

	/**
	 * Get expiration date for the coupon code.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_expiration() {

		if ( ! wu_validate_date($this->date_expiration)) {
			return '';
		}

		return $this->date_expiration;
	}

	/**
	 * Set expiration date for the coupon code.
	 *
	 * @since 2.0.0
	 * @param string $date_expiration Expiration date for the coupon code.
	 * @return void
	 */
	public function set_date_expiration($date_expiration): void {

		$this->date_expiration = $date_expiration;
	}

	/**
	 * Get date when this discount code was created.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_created() {

		return $this->date_created;
	}

	/**
	 * Set date when this discount code was created.
	 *
	 * @since 2.0.0
	 * @param string $date_created Date when this discount code was created.
	 * @return void
	 */
	public function set_date_created($date_created): void {

		$this->date_created = $date_created;
	}

	/**
	 * Returns a text describing the discount code values.
	 *
	 * @since 2.0.0
	 */
	public function get_discount_description(): string {

		$description = [];

		if ($this->get_value() > 0) {
			$value = wu_format_currency($this->get_value());

			if ($this->get_type() === 'percentage') {
				$value = $this->get_value() . '%';
			}

			$description[] = sprintf(
				// translators: placeholder is the value off. Can be wither $X.XX or X%
				__('%1$s OFF on Subscriptions', 'multisite-ultimate'),
				$value
			);
		}

		if ($this->get_setup_fee_value() > 0) {
			$setup_fee_value = wu_format_currency($this->get_setup_fee_value());

			if ($this->get_setup_fee_type() === 'percentage') {
				$setup_fee_value = $this->get_setup_fee_value() . '%';
			}

			$description[] = sprintf(
				// translators: placeholder is the value off. Can be wither $X.XX or X%
				__('%1$s OFF on Setup Fees', 'multisite-ultimate'),
				$setup_fee_value
			);
		}

		return implode(' ' . __('and', 'multisite-ultimate') . ' ', $description);
	}

	/**
	 * Transform the object into an assoc array.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_array() {

		$array = parent::to_array();

		$array['discount_description'] = $this->get_discount_description();

		return $array;
	}

	/**
	 * Save (create or update) the model on the database.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function save() {

		$results = parent::save();

		if ( ! is_wp_error($results) && has_action('wp_ultimo_coupon_after_save')) {
			if (did_action('wp_ultimo_coupon_after_save')) {
				return $results;
			}

			$compat_coupon = $this;

			do_action_deprecated('wp_ultimo_coupon_after_save', [$compat_coupon], '2.0.0', 'wu_discount_code_post_save');
		}

		return $results;
	}

	/**
	 * Get holds the list of allowed products.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_allowed_products() {

		if (null === $this->allowed_products) {
			$this->allowed_products = $this->get_meta('wu_allowed_products', []);
		}

		return (array) $this->allowed_products;
	}

	/**
	 * Set holds the list of allowed products.
	 *
	 * @since 2.0.0
	 * @param array $allowed_products The list of products that allows this discount code to be used. If empty, all products will accept this code.
	 * @return void
	 */
	public function set_allowed_products($allowed_products): void {

		$this->meta['wu_allowed_products'] = (array) $allowed_products;

		$this->allowed_products = $this->meta['wu_allowed_products'];
	}

	/**
	 * Get if we should check for products or not.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function get_limit_products() {

		if (null === $this->limit_products) {
			$this->limit_products = $this->get_meta('wu_limit_products', false);
		}

		return (bool) $this->limit_products;
	}

	/**
	 * Set if we should check for products or not.
	 *
	 * @since 2.0.0
	 * @param bool $limit_products This discount code will be limited to be used in certain products? If set to true, you must define a list of allowed products.
	 * @return void
	 */
	public function set_limit_products($limit_products): void {

		$this->meta['wu_limit_products'] = (bool) $limit_products;

		$this->limit_products = $this->meta['wu_limit_products'];
	}
}
