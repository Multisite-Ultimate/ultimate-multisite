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

?>

<div class="wu-mx-auto wu-text-center wu-mb-4">

	<ul class="wu-plans-frequency-selector">

	<?php foreach ($period_options as $index => $period_option) : ?>

		<li>
		<a class="wu-text-center" :class="(duration == <?php echo esc_html($period_option['duration']); ?> && duration_unit == '<?php echo esc_attr($period_option['duration_unit']); ?>') || (<?php echo wp_json_encode(0 === $index); ?> && duration === '') ? 'active' : ''" v-on:click.prevent="duration = <?php echo esc_attr($period_option['duration']); ?>; duration_unit = '<?php echo esc_attr($period_option['duration_unit']); ?>'" href="#">
			<?php echo esc_html($period_option['label']); ?>
		</a>
		</li>

	<?php endforeach; ?>

	</ul>

</div>
