<?php
/**
 * Displays the navigation part on the bottom of the page
 *
 * This template can be overridden by copying it to yourtheme/wp-ultimo/signup/signup-steps-navigation.php.
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

<ol class="wu-setup-steps wu-flex">

	<?php
	foreach ($steps as $index => $step) :
		$step_key = $step['id'];

		/**
		 * Class element of the Step Status Bar
	 *
		 * @var string
		 */
		$class = '';

		if ($step_key === $current_step) {
			$class = 'active';
		} elseif (array_search($current_step, array_column($steps, 'id')) > array_search($step_key, array_column($steps, 'id'))) {
			$class = 'done';
		}

		?>

	<li class="<?php echo esc_attr($class); ?> wu-flex-1">

		<?php echo esc_html($step['name']); ?>

	</li>

		<?php endforeach; ?>

</ol>
