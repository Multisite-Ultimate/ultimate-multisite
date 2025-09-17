<?php
/**
 * This is the template used for the Plan Step, which is usually the first one in the signup process.
 *
 * This template can be overridden by copying it to yourtheme/wp-ultimo/signup/steps/step-plans.php.
 *
 * HOWEVER, on occasion Ultimate Multisite will need to update template files and you
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
			'show_selector' => true,
		],
	]
);
