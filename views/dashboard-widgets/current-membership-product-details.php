<?php
/**
 * Product Details
 *
 * @since 2.0.0
 */
?>

<div class="<?php echo esc_attr("wu-product-{$product->get_id()}-head"); ?> wu-bg-gray-100 wu-p-4 wu-flex wu-items-center">

	<div>

	<span class="wu-text-xl wu-font-medium wu-block"><?php echo esc_html($product->get_name()); ?></span>

	<small class="wu-text-gray-600 wu-text-sm wu-block wu-mt-2"><?php echo esc_html($product->get_price_description()); ?></small>

	</div>

	<?php if ($product->get_featured_image()) : ?>

	<div class="wu-ml-auto">

		<img 
		class="wu-h-12 wu-w-12 wu-rounded" 
		src="<?php echo esc_url($product->get_featured_image()); ?>" 
		alt="<?php echo esc_attr($product->get_name()); ?>"
		>

	</div>

	<?php endif; ?>

</div>

<div class="<?php echo esc_attr("wu-product-{$product->get_id()}-description"); ?> wu-p-4 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b wu-border-gray-300 wu-border-solid">

	<?php if ($product->get_description()) : ?>

	<span class="wu-text-xs wu-uppercase wu-font-bold wu-block">

		<?php esc_html_e('Product Description:', 'multisite-ultimate'); ?>

	</span>

	<p class="wu-mb-6"><?php echo wp_kses($product->get_description(), wu_kses_allowed_html()); ?></p>

	<?php endif; ?>

	<span class="wu-text-xs wu-uppercase wu-font-bold wu-block">

	<?php esc_html_e('Product Characteristics:', 'multisite-ultimate'); ?>

	</span>

	<ul class="wu-m-0 wu-mt-4 wu-p-0 wu-list-none">

	<?php foreach ($product->get_pricing_table_lines() as $key => $line) : ?>

		<li class="<?php echo esc_attr(str_replace('_', '-', $key)); ?>"><?php echo $line; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>

	<?php endforeach; ?>

	</ul>

</div>
