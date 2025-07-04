<?php
/**
 * HTML email template
 *
 * This template can be overridden by copying it to yourtheme/wp-ultimo/emails/base.php.
 *
 * HOWEVER, on occasion Multisite Ultimate will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      NextPress
 * @package     WP_Ultimo/Views
 * @version     1.4.0
 */

if ( ! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

?>

<?php if ( ! $is_editor) : ?>

	<!DOCTYPE html>
	<html style="">
		<head>
			<meta name="viewport" content="width=device-width">
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			<title><?php echo esc_html($subject); ?></title>
		</head>

<?php endif; ?>

	<body style="line-height: 1.6em; -webkit-font-smoothing: antialiased; height: 100%; -webkit-text-size-adjust: none; width: 100% !important; margin: 0; padding: 0; background-color:#f6f6f6">
		<table style="line-height: 1.6em; width: 100%; margin: 0; padding: 20px;">
			<tr style="">
				<td style=""></td>
				<td style="line-height: 1.6em; clear: both !important; display: block !important; max-width: 600px !important; margin: 0 auto; padding: 20px; border: 1px solid #f0f0f0;background-color:#FFFFFF">
					<div style="line-height: 1.6em; display: block; max-width: 600px; margin: 0 auto; padding: 0;">
						<table style="line-height: 1.6em; width: 100%; margin: 0; padding: 0;">
							<tr>
								<td style="background: <?php echo esc_attr($template_settings['background_color']); ?>; text-align: center; padding: 20px 40px; /* margin: -20px; */">
									<a style="" href="<?php echo esc_attr($site_url); ?>">
										<?php if ($template_settings['use_custom_logo'] && $template_settings['custom_logo']) : ?>

											<img style="max-width: 280px; width: auto; max-height: 70px;" src="<?php echo esc_attr(wp_get_attachment_url($template_settings['custom_logo'])); ?>" alt="<?php echo esc_attr($site_name); ?>">

										<?php else : ?>

											<img style="max-width: 280px; width: auto; max-height: 70px;" src="<?php echo esc_attr($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>">

										<?php endif; ?>
									</a>
								</td>
							</tr>
							<tr style="">
								<td style="">
									<span style="font-family: <?php echo esc_attr($template_settings['content_font']); ?>; font-size: 14px; line-height: 1.6em; color: <?php echo esc_attr($template_settings['content_color']); ?>; font-weight: normal; margin: 0 0 10px; padding: 0; text-align: <?php echo esc_attr($template_settings['content_align']); ?>;"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									<br>
								</td>
							</tr>
						</table>
					</div>
				</td>
				<td style=""></td>
			</tr>
		</table>
		<table style="line-height: 1.6em; clear: both !important; width: 100%; margin: 0; padding: 0;">
			<tr style="">
				<td style=""></td>
				<td style="line-height: 1.6em; clear: both !important; display: block !important; max-width: 600px !important; margin: 0 auto; padding: 0;">
					<div style="line-height: 1.6em; display: block; max-width: 600px; margin: 0 auto; padding: 0;">
						<table style="line-height: 1.6em; width: 100%; margin: 0; padding: 0;">

						<?php if ($template_settings['footer_text']) : ?>

							<tr style="">
								<td style=" text-align: center;">
									<p style="font-family: <?php echo esc_attr($template_settings['footer_font']); ?>; font-size: 12px; line-height: 1.6em; color: <?php echo esc_attr($template_settings['footer_color']); ?>; font-weight: normal; margin: 0 0 10px; padding: 0; text-align: <?php echo esc_attr($template_settings['footer_align']); ?>">
										<?php echo wp_kses_post($template_settings['footer_text']); ?>
									</p>
								</td>
							</tr>

						<?php endif; ?>

						<?php if ($template_settings['display_company_address']) : ?>
							<tr style="">
								<td style=" text-align: center;">
									<p style="font-family: <?php echo esc_attr($template_settings['footer_font']); ?>; font-size: 12px; line-height: 1.6em; color: <?php echo esc_attr($template_settings['footer_color']); ?>; font-weight: normal; margin: 0 0 10px; padding: 0; text-align: <?php echo esc_attr($template_settings['footer_align']); ?>">
										<strong><?php echo esc_html(wu_get_setting('company_name')); ?></strong><br>
										<?php echo nl2br(esc_html(wu_get_setting('company_address', []))); ?>
									</p>
								</td>
							</tr>
						<?php endif; ?>

							<tr style="">
								<td style=" text-align: center;">
									<p style="font-family: <?php echo esc_attr($template_settings['footer_font']); ?>; font-size: 12px; line-height: 1.6em; color: <?php echo esc_attr($template_settings['footer_color']); ?>; font-weight: normal; margin: 0 0 10px; padding: 0; text-align: <?php echo esc_attr($template_settings['footer_align']); ?>">
										<a href="<?php echo esc_attr($site_url); ?>" style="line-height: 1.6em; color: #999999; margin: 0; padding: 0;">
											<?php echo esc_attr($site_name); ?>
										</a>
									</p>
								</td>
							</tr>
						</table>
					</div>
				</td>
				<td style=""></td>
			</tr>
		</table>
	</body>
	<?php if ( ! $is_editor) : ?>
		</html>
	<?php endif; ?>
