<?php
/**
 * Displays the error message in case there are no plans availabe for subscription
 *
 * This template can be overridden by copying it to yourtheme/wp-ultimo/signup/no-plan.php.
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

<div class="wu-setup-content-error">
	<p><?php esc_html_e('There are no Plans created in the platform.', 'multisite-ultimate'); ?></p><br>
</div>
