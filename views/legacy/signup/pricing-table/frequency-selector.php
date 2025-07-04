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

if ( ! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

?>

<?php if (wu_get_setting('enable_price_3', true) || wu_get_setting('enable_price_12', true)) : ?>

<ul class="wu-plans-frequency-selector">

	<?php

	$prices = [
		1  => __('Monthly', 'multisite-ultimate'),
		3  => __('Quarterly', 'multisite-ultimate'),
		12 => __('Yearly', 'multisite-ultimate'),
	];

	$first = true;

	foreach ($prices as $type => $name) :
		if ( ! wu_get_setting('enable_price_' . $type, true)) {
			continue;
		}

		?>

	<li>
	<a class="<?php echo $first ? 'active first' : ''; ?>" data-frequency-selector="<?php echo esc_attr($type); ?>" href="#">
		<?php echo esc_html($name); ?>
	</a>
	</li>

		<?php
		$first = false;
endforeach;
	?>

</ul>

<?php endif; ?>
