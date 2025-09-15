<?php
/**
 * Actions field view.
 *
 * @since 2.0.0

/**
 * @package MyPlugin
 */
defined( 'ABSPATH' ) || exit;

?>
<li class="wu-bg-gray-100 <?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php $field->print_wrapper_html_attributes(); ?>>

	<?php foreach ($field->actions as $action_slug => $action) : ?>

		<span class="wu-flex wu-flex-wrap wu-content-center">

		<?php $action = new \WP_Ultimo\UI\Field($action_slug, $action); ?>

			<button class="button <?php echo esc_attr($action->classes); ?>" id="action_button" data-action="<?php echo esc_attr($action->action); ?>" data-object="<?php echo esc_attr($action->object_id); ?>" value="<?php echo esc_attr(wp_create_nonce($action->action)); ?>" <?php $field->print_html_attributes(); ?> >

		<?php echo esc_html($action->title); ?>

		<?php if ($action->tooltip) : ?>


			<?php wu_tooltip($action->tooltip); ?>

		<?php endif; ?>

			</button>

			<span data-loading="wu_action_button_loading_<?php echo esc_attr($action->object_id); ?>" id="wu_action_button_loading" class="wu-blinking-animation wu-text-gray-600 wu-my-1 wu-text-2xs wu-uppercase wu-font-semibold wu-text-center wu-self-center wu-px-4 wu-py wu-mt-1 hidden" >

		<?php echo esc_html($action->loading_text); ?>

			</span>

		</span>

	<?php endforeach; ?>

</li>
