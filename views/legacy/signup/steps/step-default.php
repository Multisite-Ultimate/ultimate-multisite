<?php
/**
 * This is the default template used for steps defined ont he steps array
 *
 * This template can be overridden by copying it to yourtheme/wp-ultimo/signup/steps/step-default.php.
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

<div class="wu-setup-content wu-content-<?php echo esc_attr($signup->step); ?>">

	<!-- <p class="message" style="width: 320px; margin-left: auto; margin-right: auto; box-sizing: border-box;">
	Please enter your username or email address. You will receive a link to create a new password via email.
	</p> -->

	<form name="loginform" id="loginform" method="post">

	<?php

	foreach ($fields as $field_slug => $field) {

		/**
		 * Prints each of our fields using a helper function
		 */
		wu_print_signup_field($field_slug, $field, $results);
	}

	?>

	<?php do_action("wp_ultimo_registration_step_$signup->step"); ?>

	</form>

</div>
