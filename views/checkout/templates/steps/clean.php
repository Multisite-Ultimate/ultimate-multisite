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

<!-- This example requires Tailwind CSS v2.0+ -->
<nav aria-label="<?php esc_attr_e('Progress', 'multisite-ultimate'); ?>">
	<ul class="wu-clean-steps wu-list-none md:wu-flex wu-p-0 wu--mx-2 wu-my-4">


	<?php
	foreach ($steps as $index => $step) :
		$step_key = $step['id'];

		/**
		 * Class element of the Step Status Bar
		*
		 * @var string
		 */
		$container_class = '';
		$color           = 'gray';

		if ($step_key === $current_step) {
			$color = 'blue';
		} elseif (array_search($current_step, array_column($steps, 'id')) > array_search($step_key, array_column($steps, 'id'))) {
			$container_class = 'wu-opacity-50';
			$color           = 'blue';
		}

		?>

		<li class="wu-py-0 md:wu-flex-1 wu-px-2 <?php echo esc_attr($container_class); ?>">
		<span class="wu-h-2 wu-block wu-mb-2 wu-bg-<?php echo esc_attr($color); ?>-500">&nbsp;</span>
		<?php // translators: %d is a placeholder for the current step number. ?>
		<span class="wu-block wu-text-2xs wu-font-medium wu-tracking-wide wu-uppercase wu-text-<?php echo esc_attr($color); ?>-500"><?php printf(esc_html__('Step %d', 'multisite-ultimate'), esc_html($index + 1)); ?></span>
		<span class="wu-block wu-text-sm wu-font-medium wu-text-<?php echo esc_attr($color); ?>-600"><?php echo esc_html($step['name']); ?></span>
		</li>

<?php endforeach; ?>

	</ul>
</nav>
