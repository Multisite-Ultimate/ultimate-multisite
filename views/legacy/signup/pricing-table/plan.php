<?php
/**
 * Displays each individual plan on the pricing table loop
 *
 * This template can be overridden by copying it to yourtheme/wp-ultimo/signup/plan.php.
 *
 * HOWEVER, on occasion Multisite Ultimate will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      NextPress
 * @package     WP_Ultimo/Views
 * @version     1.0.0
 */

if ( ! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

?>

<?php

/**
 * Set plan attributes
 *
 * @var string
 */
$plan_attrs = '';

foreach ([1, 3, 12] as $type) {
	$price       = $plan->free ? __('Free!', 'multisite-ultimate') : str_replace(wu_get_currency_symbol(), '', wu_format_currency((((float) $plan->{'price_' . $type}) / $type)));
	$plan_attrs .= " data-price-$type='$price'";
}

$plan_attrs = apply_filters('wu_pricing_table_plan', $plan_attrs, $plan);

?>

<div id="plan-<?php echo esc_attr($plan->get_id()); ?>" data-plan="<?php echo esc_attr($plan->get_id()); ?>" <?php echo $plan_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="<?php echo esc_attr("wu-product-{$plan->get_id()}"); ?> lift wu-plan plan-tier <?php echo $plan->is_featured_plan() ? 'callout' : ''; ?> wu-col-sm-<?php echo esc_attr($columns); ?> wu-col-xs-12">

	<?php if ($plan->is_featured_plan()) : ?>

	<h6><?php echo esc_html(apply_filters('wu_featured_plan_label', __('Featured Plan', 'multisite-ultimate'), $plan)); ?></h6>

	<?php endif; ?>

	<h4 class="wp-ui-primary"><?php echo esc_html($plan->get_name()); ?></h4>

	<!-- Price -->
	<?php if ($plan->is_free()) : ?>

	<h5>
		<span class="plan-price"><?php esc_html_e('Free!', 'multisite-ultimate'); ?></span>
	</h5>

	<?php elseif ($plan->is_contact_us()) : ?>

	<h5>
		<span class="plan-price-contact-us"><?php echo esc_html(apply_filters('wu_plan_contact_us_price_line', __('--', 'multisite-ultimate'))); ?></span>
	</h5>

	<?php else : ?>

	<h5>
		<?php $symbol_left = in_array(wu_get_setting('currency_position', '%s%v'), ['%s%v', '%s %v'], true); ?>
		<?php
		if ($symbol_left) :
			?>
			<sup class="superscript"><?php echo esc_html(wu_get_currency_symbol()); ?></sup><?php endif; ?>
		<span class="plan-price"><?php echo esc_html(str_replace(wu_get_currency_symbol(), '', wu_format_currency($plan->price_1))); ?></span>
		<sub> <?php echo esc_html((! $symbol_left ? wu_get_currency_symbol() : '') . ' ' . __('/mo', 'multisite-ultimate')); ?></sub>
	</h5>

	<?php endif; ?>
	<!-- end Price -->

	<p class="early-adopter-price"><?php echo esc_html($plan->get_description()); ?>&nbsp;</p><br>


	<!-- Feature List Begins -->
	<ul>

	<?php
	/**
	 *
	 * Display quarterly and Annually plans, to be hidden
	 */
	$prices_total = [
		3  => __('every 3 months', 'multisite-ultimate'),
		12 => __('yearly', 'multisite-ultimate'),
	];

	foreach ($prices_total as $freq => $string) {
		// translators: %1$s: the price, %2$s: the period.
		$text = sprintf(__('%1$s, billed %2$s', 'multisite-ultimate'), wu_format_currency($plan->{"price_$freq"}), $string);

		if ($plan->free || $plan->is_contact_us()) {
			echo "<li class='total-price total-price-" . esc_attr($freq) . "'>-</li>";
		} else {
			echo "<li class='total-price total-price-" . esc_attr($freq) . "'>" . esc_html($text) . '</li>';
		}
	}

	/**
	 * Loop and Displays Pricing Table Lines
	 */
	foreach ($plan->get_pricing_table_lines() as $key => $line) :
		?>

		<li class="<?php echo esc_attr(str_replace('_', '-', $key)); ?>"><?php echo esc_html($line); ?></li>

	<?php endforeach; ?>

	<?php
	$button_attrubutes = apply_filters('wu_plan_select_button_attributes', '', $plan, $current_plan);
	$button_label      = null != $current_plan && $plan->get_id() == $current_plan->id ? __('This is your current plan', 'multisite-ultimate') : __('Select Plan', 'multisite-ultimate');
	$button_label      = apply_filters('wu_plan_select_button_label', $button_label, $plan, $current_plan);
	?>

	<?php if ($plan->is_contact_us()) : ?>

		<li class="wu-cta">
		<a href="<?php echo esc_attr($plan->contact_us_link); ?>" class="button button-primary">
			<?php echo esc_html($plan->get_contact_us_label()); ?>
		</a>
		</li>

	<?php else : ?>

		<li class="wu-cta">
		<button type="submit" name="plan_id" class="button button-primary button-next" value="<?php echo esc_attr($plan->get_id()); ?>" <?php echo $button_attrubutes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php echo esc_html($button_label); ?>
		</button>
		</li>

	<?php endif; ?>

	</ul>
	<!-- Feature List Begins -->

</div>
