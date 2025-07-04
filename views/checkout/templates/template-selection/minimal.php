<?php
/**
 * Displays the frequency selector for the pricing tables
 *
 * This template can be overridden by copying it to yourtheme/wp-ultimo/signup/pricing-table/frequency-selector.php.
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

// Exit if accessed directly
defined('ABSPATH') || exit;

if ( ! $should_display) {
	return;
}
$sites = array_map('wu_get_site', $sites ?? []);

$categories ??= [];

$customer_sites_category = __('Your Sites', 'multisite-ultimate');

$customer_sites = isset($customer_sites) ? array_map('intval', $customer_sites) : [];

?>
<div id="wu-site-template-container">

	<ul id="wu-site-template-filter">

		<li class="wu-site-template-filter-all">
			<a
				href="#"
				data-category=""
				:class="$parent.template_category === '' ? 'current wu-font-semibold' : ''"
				v-on:click.prevent="$parent.template_category = ''"
			>
				<?php esc_html_e('All', 'multisite-ultimate'); ?>
			</a>
		</li>

		<?php if ( ! empty($customer_sites)) : ?>

			<li class="wu-site-template-filter-<?php echo esc_attr(sanitize_title($customer_sites_category)); ?>">
				<a
					href="#"
					data-category="<?php echo esc_attr($customer_sites_category); ?>"
					:class="$parent.template_category === '<?php echo esc_attr($customer_sites_category); ?>' ? 'current wu-font-semibold' : ''"
					v-on:click.prevent="$parent.template_category = '<?php echo esc_attr($customer_sites_category); ?>'"
				>
				<?php echo esc_html($customer_sites_category); ?></a>
			</li>

		<?php endif; ?>

		<?php if (isset($categories) && $categories) : ?>

			<?php foreach ($categories as $category) : ?>

				<li class="wu-site-template-filter-<?php echo esc_attr(sanitize_title($category)); ?>">
					<a
						href="#"
						data-category="<?php echo esc_attr($category); ?>"
						:class="$parent.template_category === '<?php echo esc_attr($category); ?>' ? 'current wu-font-semibold' : ''"
						v-on:click.prevent="$parent.template_category = '<?php echo esc_attr($category); ?>'"
					>
					<?php echo esc_html($category); ?></a>
				</li>

			<?php endforeach; ?>

		<?php endif; ?>

	</ul>

	<div id="wu-site-template-container-grid">

		<?php foreach ($sites as $site_template) : ?>

			<?php
			if ($site_template->get_type() !== 'site_template' && ! in_array($site_template->get_id(), $customer_sites, true)) {
				continue; }
			?>

			<?php $is_template = $site_template->get_type() === 'site_template'; ?>

			<?php $categories = array_merge($site_template->get_categories(), ! $is_template ? [$customer_sites_category] : []); ?>

			<div
				id="wu-site-template-<?php echo esc_attr($site_template->get_id()); ?>"
				v-show="!$parent.template_category || <?php echo esc_attr(wp_json_encode($categories)); ?>.join(',').indexOf($parent.template_category) > -1"
				v-cloak
			>

				<img class="wu-site-template-image" src="<?php echo esc_attr($site_template->get_featured_image()); ?>" alt="<?php echo esc_attr($site_template->get_title()); ?>">

				<h3 class="wu-site-template-title">
					<?php echo esc_html($site_template->get_title()); ?>
				</h3>

				<p class="wu-site-template-description">
					<?php echo esc_html($site_template->get_description()); ?>
				</p>

				<div class="wu-site-template-preview-block">

					<a class="wu-site-template-selector" <?php echo $site_template->get_preview_url_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php esc_html_e('View Template Preview', 'multisite-ultimate'); ?>
					</a>

				</div>

				<label for="wu-site-template-id-<?php echo esc_attr($site_template->get_id()); ?>">

					<input id="wu-site-template-id-<?php echo esc_attr($site_template->get_id()); ?>" type="radio" name="template_id" v-model="$parent.template_id" value="<?php echo esc_attr($site_template->get_id()); ?>" />

					<a class="wu-site-template-selector" @click.prevent="" href="#">

						<?php esc_html_e('Select this Template', 'multisite-ultimate'); ?>

					</a>

				</label>

			</div>

		<?php endforeach; ?>

	</div>

</div>
