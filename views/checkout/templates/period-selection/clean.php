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
 * @version     2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

?>
<ul class="wu-border-solid wu-border wu-border-gray-300 wu-shadow-sm wu-p-4 wu-flex wu-rounded wu-relative wu-m-0 wu-mb-4 wu-list-none wu-justify-center">

	<?php foreach ($period_options as $index => $period_option) : ?>

	<li class="wu-mx-2">
		<a
		:class="(duration == <?php echo esc_attr($period_option['duration']); ?> && duration_unit == '<?php echo esc_attr($period_option['duration_unit']); ?>') || (<?php echo wp_json_encode(0 === $index); ?> && duration === '') ? 'wu-font-semibold active' : ''"
		v-on:click.prevent="duration = <?php echo esc_attr($period_option['duration']); ?>; duration_unit = '<?php echo esc_attr($period_option['duration_unit']); ?>'"
		href="#"
		>
		<?php echo esc_html($period_option['label']); ?>
		</a>
	</li>

	<?php endforeach; ?>

</ul>
