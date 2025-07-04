<?php
/**
 * This is the template used for the Template Previewer.
 *
 * This template can be overridden by copying it to yourtheme/wp-ultimo/signup/steps/step-template-previewer.php.
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

/**
 * Allow developers to run code before the template previewer is loaded.
 */
do_action('wu_template_previewer_before');

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>

	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="<?php bloginfo('charset'); ?>">

	<?php
	wu_ignore_errors(
		function () {
			wp_head();
		}
	);
	?>

	</head>

	<body>

	<div id="switcher">

		<div class="center">

			<div class="logo">

				<a title="<?php echo esc_attr(get_network_option(null, 'site_name')); ?>" href="<?php echo esc_attr(network_home_url()); ?>" target="_blank">

					<?php if ($use_custom_logo && $custom_logo) : ?>

								<?php echo wp_get_attachment_image($custom_logo, 'full'); ?>

					<?php else : ?>

					<img src="<?php echo esc_attr($logo_url); ?>" alt="<?php echo esc_attr(get_network_option(null, 'site_name')); ?>">

					<?php endif; ?>

				</a>

			</div>

			<ul id="theme_list_selector">

				<li id="theme_list">

					<?php if ($selected_template) : ?>

						<a id="template_selector" href="#">

										<?php echo esc_html($selected_template->get_title()); ?>

							<span style="float: right; margin-top:  -3px" class="dashicons dashicons-arrow-down-alt2"></span>

						</a>

					<?php else : ?>

						<a id="template_selector" href="#">

									<?php esc_html_e('Select template...', 'multisite-ultimate'); ?>

							<span style="float: right; margin-top:  -3px" class="dashicons dashicons-arrow-down-alt2"></span>

						</a>

					<?php endif; ?>

					<ul id="test1a">

						<?php foreach ($templates as $template) : ?>

							<li>

								<a
								href="<?php echo esc_attr($tp->get_preview_url($template->get_id())); ?>"
								data-frame="<?php echo esc_attr(add_query_arg('wu-preview', '1', $template->get_active_site_url())); ?>"
								data-title="<?php echo esc_attr($template->get_title()); ?>"
								data-id="<?php echo esc_attr($template->get_id()); ?>"
								>

												<?php echo esc_html($template->get_title()); ?>

								</a>

								<img alt="" class="preview" src="<?php echo esc_attr($template->get_featured_image()); ?>">

							</li>

						<?php endforeach; ?>

					</ul>

				</li>

			</ul>

			<?php if ($display_responsive_controls) : ?>

				<div class="responsive">

					<a href="#" class="desktop active dashicons-before dashicons-desktop" title="<?php esc_attr_e('View Desktop Version', 'multisite-ultimate'); ?>"></a>

					<a href="#" class="tabletlandscape dashicons-before dashicons-tablet" title="<?php esc_attr_e('View Tablet Landscape (1024x768)', 'multisite-ultimate'); ?>"></a>

					<a href="#" class="tabletportrait dashicons-before dashicons-tablet" title="<?php esc_attr_e('View Tablet Portrait (768x1024)', 'multisite-ultimate'); ?>"></a>

					<a href="#" class="mobilelandscape dashicons-before dashicons-smartphone" title="<?php esc_attr_e('View Mobile Landscape (480x320)', 'multisite-ultimate'); ?>"></a>

					<a href="#" class="mobileportrait dashicons-before dashicons-smartphone" title="<?php esc_attr_e('View Mobile Portrait (320x480)', 'multisite-ultimate'); ?>"></a>

				</div>

			<?php endif; ?>

		</div>

		<?php if ( ! isset($_GET['switching'])) : // phpcs:ignore WordPress.Security.NonceVerification ?>

		<ul class="links">

			<?php if (wu_request('open')) : ?>

				<li class="select-template">

					<a id="action-select" href="#"><?php echo esc_html($button_text); ?> &rarr;</a>

				</li>

			<?php else : ?>

				<li class="select-template">

					<a id="action-select-link" href="<?php echo esc_attr(wu_get_registration_url('?template_selection=' . $selected_template->get_id())); ?>"><?php echo esc_html($button_text); ?> &rarr;</a>

				</li>

			<?php endif; ?>

		</ul>

		<?php endif; ?>

		<input type="hidden" id="template-selector" value="<?php echo esc_attr((int) $_GET['template-preview'] ?? 0); // phpcs:ignore WordPress.Security.NonceVerification ?>" />

	</div>

	<?php if ( ! isset($_GET['switching'])) : // phpcs:ignore WordPress.Security.NonceVerification ?>

		<div class="mobile-selector">

				<?php if (wu_request('open')) : ?>

				<a id="action-select2" href="#"><?php echo esc_html($button_text); ?> &rarr;</a>

			<?php else : ?>

				<a id="action-select-link" href="<?php echo esc_attr(wu_get_registration_url('?template_id=' . $selected_template->get_id())); ?>"><?php echo esc_html($button_text); ?> &rarr;</a>

			<?php endif; ?>

		</div>

	<?php endif; ?>

	<?php if ( ! wu_request('customizer')) : ?>

		<iframe id="iframe" src="<?php echo esc_attr(set_url_scheme(add_query_arg('wu-preview', '1', get_home_url($selected_template->get_id())))); ?>" width="100%" height="100%"></iframe>

	<?php else : ?>

		<div class="wu-styling">

		<div class="wu-w-full wu-text-center wu-relative wu-flex wu-justify-center wu-items-center wu-h-screen">

			<div class="wu-text-xl wu-rounded wu-font-bold wu-uppercase wu-inline-block wu-p-8 wu-opacity-50" style="margin-top: 62px; background-color: #000; color: #666;">

				<?php esc_html_e('Site Template Preview will go here!', 'multisite-ultimate'); ?>

			</div>

		</div>

		</div>

	<?php endif; ?>

		<?php
		wu_ignore_errors(
			function () {
				wp_footer();
			}
		);
		?>

	</body>

</html>
