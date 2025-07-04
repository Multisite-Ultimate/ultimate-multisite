<?php
/**
 * Widget message view.
 *
 * @since 2.0.0
 */
?>
<ul class="wu-widget-list wu-striped wu-m-0 wu--my-2 wu--mx-3">

	<li class="wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-400 wu-border-solid">

	<h3 class="wu-my-1 wu-text-2xs wu-uppercase"><?php echo esc_html__('Message', 'multisite-ultimate'); ?></h3>

	<span class="wu-my-1 wu-inline-block">
		<?php echo $object->get_message(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</span>

	</li>

</ul>

<div class="wu-bg-gray-100 wu-px-4 wu-py-2 wu--m-3 wu-mt-3 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-400 wu-border-solid wu-text-right">

</div>
