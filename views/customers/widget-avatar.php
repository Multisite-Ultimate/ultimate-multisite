<?php
/**
 * Avatar widget view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-striped wu-m-0 wu--mt-2 wu-mb-2 wu--mx-3">

	<div class="wu-w-full wu-box-border wu-p-4 wu-m-0 wu-border-t-0 wu-border-l-0 wu-border-r-0 wu-border-b wu-border-gray-400 wu-border-solid wu-text-center">

	<div class="customer-actions wu--mt-widget-inset wu--mx-4 wu-p-6 wu-bg-gray-100 wu-border wu-border-solid wu-border-gray-300 wu-border-l-0 wu-border-r-0 wu-border-t-0" style="background-image: url('
	<?php
	echo esc_attr(
		get_avatar_url(
			$user->ID,
			[
				'force_display' => true,
				'size'          => 300,
				'default'       => 'identicon',
			]
		)
	);
	?>
	');">
		&nbsp;
	</div>

	<div class="wu-mb-2 wu--mt-8">

		<?php
		echo get_avatar(
			$user->ID,
			86,
			'identicon',
			'',
			[
				'force_display' => true,
				'class'         => 'wu-rounded-full wu-border wu-border-solid wu-border-gray-300 wu-relative',
			]
		);
		?>

	</div>

	<div class="">

		<div class="wu-block wu-my-1 wu-text-base wu-font-semibold">
		<?php echo esc_html($user->display_name); ?>
		</div>

		<div class="wu-block wu-my-2">

		<a href="mailto:<?php echo esc_attr($user->user_email); ?>" class="wu-no-underline" <?php echo wu_tooltip_text(esc_html__('Send an email to this customer.', 'multisite-ultimate')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php echo esc_html($user->user_email); ?>
		</a>

		</div>

		<div class="wu-block wu-pt-2">

		<?php if (get_current_user_id() !== $user->ID) : ?>

			<a 
			href="<?php echo esc_attr(\WP_Ultimo\User_Switching::get_instance()->render($user->ID)); ?>"
			class="button wu-w-full <?php echo \WP_Ultimo\User_Switching::get_instance()->check_user_switching_is_activated() ? '' : 'wubox'; ?> wu-block wu-text-center"
			title="<?php echo \WP_Ultimo\User_Switching::get_instance()->check_user_switching_is_activated() ? '' : esc_attr__('Install User Switching', 'multisite-ultimate'); ?>"
			>
			<?php esc_html_e('Switch To &rarr;', 'multisite-ultimate'); ?>
			</a>

		<?php else : ?>

			<button class="button wu-w-full" disabled="disabled">
			<span <?php echo wu_tooltip_text(esc_html__('Switching to your own account is not possible.', 'multisite-ultimate')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php esc_html_e('Switch To &rarr;', 'multisite-ultimate'); ?>
			</span>
			</button>

		<?php endif; ?>

		<a href="<?php echo esc_attr(get_edit_user_link($user->ID)); ?>" target="_blank" class="wu-w-full wu-block wu-text-center wu-no-underline wu-mt-4">

			<?php esc_html_e('Visit Profile &rarr;', 'multisite-ultimate'); ?>

		</a>

		</div>

	</div>

	</div>

</div>
