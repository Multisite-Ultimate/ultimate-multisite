<?php
/**
 * Total payments view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-bg-gray-100 wu-mt-3 wu--mb-3 wu--mx-3 wu-p-4 wu-flex wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">

	<div class="wu-justify-self-end wu-mr-auto">

	<ul class="wu-list-none wu-m-0 wu-p-0">

		<?php foreach ($actions as $action_slug => $action) : ?>

		<li class="wu-inline-block wu-m-0 wu-mr-2">

		<a title="<?php echo esc_attr($action['label']); ?>" href="<?php echo esc_attr($action['href']); ?>" class="<?php echo esc_attr($action['classes']); ?>">

			<?php if ($action['icon_classes']) : ?>

			<span class="<?php echo esc_attr($action['icon_classes']); ?>"></span>

			<?php endif; ?>

			<?php echo esc_attr($action['label']); ?>

		</a>

		</li>

		<?php endforeach; ?>

	</ul>

	</div>

	<div class="wu-justify-self-end wu-ml-auto wu-justify-center wu-flex wu-items-center">

	<div>

		<span class="wu-text-xs wu-font-bold wu-uppercase wu-text-gray-600">

		<?php esc_html_e('Refunds:', 'multisite-ultimate'); ?>

		</span>

		<span class="wu-ml-2 wu-text-gray-700">

		<?php echo esc_html(wu_format_currency($payment->get_refund_total(), $payment->get_currency())); ?>

		</span>

	</div>

	<div class="wu-ml-6">

		<span class="wu-text-xs wu-font-bold wu-uppercase wu-text-gray-700">

		<?php esc_html_e('Total:', 'multisite-ultimate'); ?>

		</span>

		<span class="wu-ml-2">

		<?php echo esc_html(wu_format_currency($payment->get_total(), $payment->get_currency())); ?>

		</span>

	</div>

	</div>

</div>
