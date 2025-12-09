<?php
/**
 * Dynamic styles for the template previewer.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
?>
body #switcher {
	background-color: #<?php echo esc_attr($bg_color->getHex()); ?>;
	border-bottom: 5px solid <?php echo esc_attr($bg_color->isDark() ? '#f9f9f9' : '#333'); ?>;
}

#template_selector {
	color: <?php echo esc_attr($bg_color->isDark() ? '#dfdfdf' : '#555'); ?>;
}

.responsive a {
	color: <?php echo esc_attr($bg_color->isDark() ? '#fff' : '#444'); ?>
}

.responsive a.active, .responsive a:hover {
	color: <?php echo esc_attr($bg_color->isDark() ? '#fff' : '#444'); ?>
}

.select-template a, .mobile-selector a {
	background-color: #<?php echo esc_attr($button_bg_color->getHex()); ?>;
	color: <?php echo esc_attr($button_bg_color->isDark() ? '#fff' : '#444'); ?>;
}
