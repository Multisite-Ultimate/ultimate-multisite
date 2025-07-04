<?php
/**
 * Template File: Basic Pricing Table.
 *
 * To see what methods are available on the product variable, @see inc/models/class-producs.php.
 *
 * This template can also be overrid using template overrides.
 * See more here: https://github.com/superdav42/wp-multisite-waas/wiki/Template-Overrides.
 *
 * @since 2.0.0
 * @param array $products List of product objects.
 */
?>

<?php if (empty($products)) : ?>
	<div class="wu-text-center wu-bg-gray-100 wu-rounded wu-uppercase wu-font-semibold wu-text-xs wu-text-gray-700 wu-p-4">
		<?php esc_html_e('No Products Found.', 'multisite-ultimate'); ?>
	</div>
<?php endif; ?>

<div class="wu-flex wu-mb-4 wu--mx-2">
	<?php foreach ($products as $product) : ?>
		<div class="<?php echo esc_attr('wu-product-' . $product->get_id()); ?> wu-bg-gray-100 wu-m-2 wu-px-4 wu-py-4 wu-border wu-border-solid wu-rounded wu-border-gray-400 wu-box-border wu-flex-1 wu-flex wu-flex-col wu-justify-end">
			<div class="wu-self-start">
				<span class="wu-font-bold wu-block wu-text-xl"><?php echo esc_html($product->get_name()); ?></span>
				<span class="wu-block wu-font-semibold"><?php echo esc_html($product->get_price_description(false)); ?></span>
			</div>
			<div class="wu-my-4">
				<ul class="wu-m-0 wu-list-none">
					<?php foreach ($product->get_pricing_table_lines() as $key => $line) : ?>
						<li class="<?php echo esc_attr(str_replace('_', '-', $key)); ?>"><?php echo esc_html($line); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="wu-relative">
				<a href="#wu-checkout-add-<?php echo esc_attr($product->get_slug()); ?>" class="button btn wu-w-full wu-text-center wu-inline-block"><?php esc_html_e('Select', 'multisite-ultimate'); ?></a>
			</div>
			<input type="checkbox" style="display: none;" name="products[]" value="<?php echo esc_attr($product->get_slug()); ?>" v-model="products">
		</div>
	<?php endforeach; ?>
</div>
