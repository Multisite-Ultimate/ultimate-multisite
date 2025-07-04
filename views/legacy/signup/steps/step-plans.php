<?php
/**
 * This is the template used for the Plan Step, which is usually the first one in the signup process.
 *
 * This template can be overridden by copying it to yourtheme/wp-ultimo/signup/steps/step-plans.php.
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

// Get all available plans
$plans = wu_get_products(
	[
		'type' => 'plan',
	]
);

// Render the selector
wu_get_template(
	'legacy/signup/pricing-table/pricing-table',
	[
		'plans'        => $plans,
		'signup'       => $signup,
		'current_plan' => false,
		'is_shortcode' => false,
		'atts'         => [
			'primary_color'          => '#00a1ff', // wu_get_setting('primary-color', '#00a1ff'),
			'accent_color'           => '#78b336', // wu_get_setting('accent-color', '#78b336'),
			'default_pricing_option' => 1, // wu_get_setting('default_pricing_option', 1),
			'show_selector'          => true,
		],
	]
);
