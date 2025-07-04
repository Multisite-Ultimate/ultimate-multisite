<?php
/**
 * Template File: Basic Pricing Table.
 *
 * To see what methods are available on the product variable, @see inc/models/class-products.php.
 *
 * This template can also be override using template overrides.
 * See more here: https://github.com/superdav42/wp-multisite-waas/wiki/Template-Overrides.
 *
 * @since 2.0.0
 * @param array $products List of product objects.
 * @param string $name ID of the field.
 * @param string $label The field label.
 */

$products_to_reduce = array_merge([false], $products);

$first_recurring_product = array_reduce(
	$products_to_reduce,
	function ($chosen_product, $product) {

		if ($product && $product->is_recurring() && ! $chosen_product) {
			$chosen_product = $product;
		}

		return $chosen_product;
	}
);

$legacy_mode = array_reduce(
	$products_to_reduce,
	function ($all_have_same_duration, $product) use ($first_recurring_product) {

		if ($product && $product->is_recurring()) {
			$all_have_same_duration = $first_recurring_product->get_recurring_description() === $product->get_recurring_description();
		}

		return $all_have_same_duration;
	}
);

wp_add_inline_script(
	'wu-checkout',
	sprintf(
		'

  /**
   * Force different durations.
   */
  window.wu_force_different_durations = %s;

  window.wu_legacy_mode = %s;

',
		wp_json_encode($force_different_durations),
		wp_json_encode($legacy_mode)
	),
	'after'
);

if (null !== $first_recurring_product) {
	wp_add_inline_script(
		'wu-checkout',
		sprintf(
			"

    /**
     * Add durations if necessary.
     */
    wp.hooks.addFilter('wu_before_form_init', 'next-press/wp-ultimo', function(data) {

      data.wu_force_different_durations = wu_force_different_durations;
      data.wu_legacy_mode               = wu_legacy_mode;

      if (!data.duration && !wu_force_different_durations) {

        data.duration = %s;

      }

      if (!data.duration_unit && !wu_force_different_durations) {

        data.duration_unit = %s;

      }

      return data;

    });

  ",
			wp_json_encode($first_recurring_product->get_duration()),
			wp_json_encode($first_recurring_product->get_duration_unit())
		),
		'after'
	);
}
?>

<?php if (empty($products)) : ?>

<div class="wu-text-center wu-bg-gray-100 wu-rounded wu-uppercase wu-font-semibold wu-text-xs wu-text-gray-700 wu-p-4">

	<?php esc_html_e('No Products Found.', 'multisite-ultimate'); ?>

</div>

<?php else : ?>

	<div class="wu-content-plan">

	<div class="layer plans wu-overflow-hidden wu-flex">

		<?php foreach ($products as $product) : ?>

		<div
			id="plan-<?php echo esc_attr($product->get_id()); ?>"
			class="<?php echo esc_attr("wu-product-{$product->get_id()}"); ?> lift wu-plan plan-tier wu-flex-1 <?php echo esc_attr($product->is_featured_plan() ? 'callout' : ''); ?> wu-flex wu-flex-col wu-justify-between"
			v-show="wu_force_different_durations || (duration && wu_legacy_mode) || (( (!duration) || duration == <?php echo esc_attr($product->get_duration()); ?> && duration_unit == '<?php echo esc_attr($product->get_duration_unit()); ?>' ) || <?php echo wp_json_encode($product->get_pricing_type() !== 'paid'); ?>)"
		>

			<div class="wu-relative">

			<?php if ($product->is_featured_plan()) : ?>

				<h6>

				<?php

					/**
					 * Featured tag.
					 */
					echo esc_html(apply_filters('wu_featured_plan_label', __('Featured Plan', 'multisite-ultimate'), $product));

				?>

				</h6>

			<?php endif; ?>

			<h4 class="wp-ui-primary">

				<?php echo esc_html($product->get_name()); ?>

			</h4>

			<?php

				/**
				 * Case Free
				 */
			if ($product->get_pricing_type() === 'free') :

				?>

				<!-- Price -->
				<h5>

				<span class="plan-price">

				<?php esc_html_e('Free!', 'multisite-ultimate'); ?>

				</span>

				</h5>

				<?php

				/**
				 * Case Free
				 */
				elseif ($product->get_pricing_type() === 'contact_us') :

					?>

				<!-- Price -->
				<h5>

				<span class="plan-price">

					<?php echo esc_html(apply_filters('wu_plan_contact_us_price_line', __('--', 'multisite-ultimate'))); ?>

				</span>

				</h5>

			<?php else : ?>

				<!-- Price -->
				<h5>

				<?php

					/**
					 * Price display.
					 */

					$symbol_left = in_array(wu_get_setting('currency_position', '%s%v'), ['%s%v', '%s %v'], true);

				?>

				<?php if ($symbol_left) : ?>

					<sup class="superscript">

					<?php esc_html(wu_get_currency_symbol($product->get_currency())); ?>

					</sup>

				<?php endif; ?>

				<span class="plan-price" v-if="wu_force_different_durations || (duration == <?php echo esc_attr($product->get_duration()); ?> && duration_unit == '<?php echo esc_attr($product->get_duration_unit()); ?>')">

					<?php

					$n = $product->get_amount();

					echo esc_html(str_replace(wu_get_currency_symbol(), '', wu_format_currency($n)));

					?>

				</span>

				<?php
				foreach ([3, 12] as $freq) :
					$price_variation = $product->get_price_variation($freq, 'month');

					if ( ! $price_variation) {
						continue;
					}

					?>

					<span class="plan-price" v-cloak v-if="duration == <?php echo esc_attr($price_variation['duration']); ?> && duration_unit == '<?php echo esc_attr($price_variation['duration_unit']); ?>'">

					<?php

						$n = $price_variation ? $price_variation['monthly_amount'] : false;

					if ($n) {
						echo esc_html(str_replace(wu_get_currency_symbol(), '', wu_format_currency($n)));
					} else {
						echo '--';
					}

					?>

					</span>

<?php endforeach; ?>

				<sub v-if="1 == <?php echo esc_attr($product->get_duration()); ?> && 'month' == '<?php echo esc_attr($product->get_duration_unit()); ?>'">

					<?php

					/**
					 * Period Unit.
					 */
					$symbol = $product->is_recurring() ? __('/mo', 'multisite-ultimate') : '';

					echo esc_html((! $symbol_left ? wu_get_currency_symbol() : '') . ' ' . $symbol);

					?>

				</sub>

				<sub v-else>

					<?php

					/**
					 * Period Unit.
					 */
					$symbol = $product->is_recurring() ? $product->get_recurring_description() : '';

					echo esc_html((! $symbol_left ? wu_get_currency_symbol() : '') . ' ' . $symbol);

					?>

				</sub>

				</h5>
				<!-- end Price -->

			<?php endif; ?>

			<p class="early-adopter-price">

				<?php echo wp_kses($product->get_description(), wu_kses_allowed_html()); ?>

			</p>

			</div>

			<br>

			<!-- Feature List Begins -->
			<ul>

			<?php

				/**
				 *
				 * Display quarterly and Annually plans, to be hidden.
				 */
				$prices_total = [
					3  => __('every 3 months', 'multisite-ultimate'),
					12 => __('yearly', 'multisite-ultimate'),
				];

				foreach ($prices_total as $freq => $string) {
					$price_variation = $product->get_price_variation($freq, 'month');

					if ( ! $price_variation || $product->get_pricing_type() === 'free' || $product->get_pricing_type() === 'contact_us') {
						echo "<li v-cloak v-show='duration == " . esc_attr($freq) . "' class='total-price total-price-(" . esc_attr($freq) . ")'>-</li>";
					} else {
						// translators: %1$s: the price, %2$s: the period.
						$text = sprintf(__('%1$s, billed %2$s', 'multisite-ultimate'), wu_format_currency($price_variation['amount']), $string);

						$extra_check_for_annual = '';

						if (12 === $freq) {
							$extra_check_for_annual = ' || (duration == "1" && duration_unit == "year")';
						}

						echo "<li v-cloak v-show='duration == " . esc_attr($freq . $extra_check_for_annual) . "' class='total-price total-price-" . esc_attr($freq) . "'>" . esc_html($text) . '</li>';
					}
				}

				?>

			<?php foreach ($product->get_pricing_table_lines() as $key => $line) : ?>

				<li class="<?php echo esc_attr(str_replace('_', '-', $key)); ?>"><?php echo esc_html($line); ?></li>

			<?php endforeach; ?>

			<li class="wu-cta">

				<button
				v-if="<?php echo wp_json_encode($product->get_pricing_type() !== 'contact_us'); ?>"
				v-on:click="add_plan(<?php echo esc_attr($product->get_id()); ?>)"
				type="button"
				name="products[]"
				value="<?php echo esc_attr($product->get_id()); ?>"
				class="button button-primary button-next"
				>
				<?php esc_html_e('Select Plan', 'multisite-ultimate'); ?>
				</button>

				<button
				v-else
				v-on:click="open_url('<?php echo esc_url($product->get_contact_us_link()); ?>', '_blank');" type="button"
				name="products[]"
				value="<?php echo esc_attr($product->get_id()); ?>"
				class="button button-primary button-next"
				>
				<?php esc_html_e('Select Plan', 'multisite-ultimate'); ?>
				</button>

			</li>

			</ul>
			<!-- Feature List Ends -->

		</div>

<?php endforeach; ?>

	</div>

	</div>

<?php endif; ?>
