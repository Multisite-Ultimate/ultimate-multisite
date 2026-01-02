<?php
/**
 * Email accounts view.
 *
 * @since 2.3.0
 */
defined('ABSPATH') || exit;

?>
<div class="wu-styling <?php echo esc_attr($className); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase ?>">

	<div class="<?php echo esc_attr(wu_env_picker('', 'wu-widget-inset')); ?>">

	<!-- Title Element -->
	<div class="wu-p-4 wu-flex wu-items-center <?php echo esc_attr(wu_env_picker('', 'wu-bg-gray-100')); ?>">

		<?php if ( $title ) : ?>

		<h3 class="wu-m-0 <?php echo esc_attr(wu_env_picker('', 'wu-widget-title')); ?>">

			<?php echo esc_html($title); ?>

		</h3>

		<?php endif; ?>

		<div class="wu-ml-auto wu-flex wu-items-center wu-gap-2">

			<?php if ( 0 !== $remaining_slots ) : ?>

				<?php if ( 'unlimited' === $remaining_slots || $remaining_slots > 0 ) : ?>

					<a title="<?php esc_html_e('Create Email Account', 'ultimate-multisite'); ?>" href="<?php echo esc_attr($create_modal['url']); ?>" class="wu-text-sm wu-no-underline wubox button">
						<?php esc_html_e('Create Email Account', 'ultimate-multisite'); ?>
					</a>

				<?php endif; ?>

			<?php endif; ?>

		</div>

	</div>
	<!-- Title Element - End -->

	<!-- Quota Info -->
	<?php if ($email_accounts_enabled) : ?>
	<div class="wu-px-4 wu-py-2 wu-bg-gray-50 wu-border-t wu-border-solid wu-border-0 wu-border-gray-200 wu-text-xs wu-text-gray-600">
		<?php if ( 'unlimited' === $remaining_slots ) : ?>
			<span class="dashicons-before dashicons-wu-mail wu-align-middle wu-mr-1"></span>
			<?php
			printf(
				/* translators: %d is the number of email accounts */
				esc_html(_n('%d email account', '%d email accounts', $current_count, 'ultimate-multisite')),
				absint($current_count)
			);
			?>
			<span class="wu-ml-2 wu-text-green-600"><?php esc_html_e('(Unlimited)', 'ultimate-multisite'); ?></span>
		<?php elseif ($limit > 0) : ?>
			<span class="dashicons-before dashicons-wu-mail wu-align-middle wu-mr-1"></span>
			<?php
			printf(
				/* translators: %1$d is current count, %2$d is the limit */
				esc_html__('%1$d of %2$d email accounts used', 'ultimate-multisite'),
				absint($current_count),
				absint($limit)
			);
			?>
			<?php if ($remaining_slots > 0) : ?>
				<span class="wu-ml-2 wu-text-gray-500">
					<?php
					printf(
						/* translators: %d is remaining slots */
						esc_html(_n('(%d remaining)', '(%d remaining)', $remaining_slots, 'ultimate-multisite')),
						absint($remaining_slots)
					);
					?>
				</span>
			<?php endif; ?>
		<?php else : ?>
			<span class="dashicons-before dashicons-wu-alert-circle wu-align-middle wu-mr-1 wu-text-yellow-600"></span>
			<?php esc_html_e('Email accounts are not included in your current plan.', 'ultimate-multisite'); ?>
		<?php endif; ?>
	</div>
	<?php endif; ?>
	<!-- Quota Info - End -->

	<div class="wu-border-t wu-border-solid wu-border-0 wu-border-gray-200">

		<table class="wu-m-0 wu-my-2 wu-p-0 wu-w-full">

		<tbody class="wu-align-baseline">

			<?php if ($email_accounts) : ?>

				<?php
				foreach ($email_accounts as $account_data) :
					$account = $account_data['account'];
					?>

					<tr>

					<td class="wu-px-1">

						<?php

						$status_label = $account->get_status_label();
						$status_class = $account->get_status_class();

						$status = "<span class='wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-leading-none wu-font-mono $status_class'>$status_label</span>";

						$second_row_actions = [];

						// Webmail link (only for active accounts)
						if ($account->get_status() === 'active' && ! empty($account_data['webmail_url'])) {
							$second_row_actions['webmail'] = [
								'wrapper_classes' => '',
								'icon'            => 'dashicons-wu-globe wu-align-middle wu-mr-1',
								'label'           => '',
								'url'             => $account_data['webmail_url'],
								'value'           => __('Open Webmail', 'ultimate-multisite'),
								'attrs'           => 'target="_blank" rel="noopener"',
							];
						}

						// View credentials (only for active accounts)
						if ($account->get_status() === 'active') {
							$second_row_actions['credentials'] = [
								'wrapper_classes' => 'wubox',
								'icon'            => 'dashicons-wu-key wu-align-middle wu-mr-1',
								'label'           => '',
								'url'             => $account_data['credentials_link'],
								'value'           => __('View Settings', 'ultimate-multisite'),
							];
						}

						// DNS instructions link
						if (! empty($account_data['dns_link'])) {
							$second_row_actions['dns'] = [
								'wrapper_classes' => 'wubox',
								'icon'            => 'dashicons-wu-server wu-align-middle wu-mr-1',
								'label'           => '',
								'url'             => $account_data['dns_link'],
								'value'           => __('DNS Setup', 'ultimate-multisite'),
							];
						}

						// Delete link
						$second_row_actions['delete'] = [
							'wrapper_classes' => 'wu-text-red-500 wubox',
							'icon'            => 'dashicons-wu-trash-2 wu-align-middle wu-mr-1',
							'label'           => '',
							'value'           => __('Delete', 'ultimate-multisite'),
							'url'             => $account_data['delete_link'],
						];

						// Provider info
						$provider_id    = $account->get_provider();
						$provider_label = ucfirst($provider_id);

						// Get provider instance for better label
						$manager = \WP_Ultimo\Managers\Email_Account_Manager::get_instance();
						if ($manager) {
							$provider = $manager->get_provider($provider_id);
							if ($provider) {
								$provider_label = $provider->get_title();
							}
						}

						wu_responsive_table_row(
							[
								'id'     => false,
								'title'  => strtolower($account->get_email_address()),
								'url'    => false,
								'status' => $status,
							],
							[
								'provider' => [
									'wrapper_classes' => '',
									'icon'            => 'dashicons-wu-mail wu-align-text-bottom wu-mr-1',
									'label'           => '',
									'value'           => $provider_label,
								],
								'quota'    => [
									'wrapper_classes' => '',
									'icon'            => 'dashicons-wu-database wu-align-text-bottom wu-mr-1',
									'label'           => '',
									'value'           => function () use ($account) {
										$quota = $account->get_quota_mb();
										if ($quota > 0) {
											if ($quota >= 1024) {
												printf(
													/* translators: %s is quota in GB */
													esc_html__('%s GB', 'ultimate-multisite'),
													esc_html(number_format_i18n($quota / 1024, 1))
												);
											} else {
												printf(
													/* translators: %d is quota in MB */
													esc_html__('%d MB', 'ultimate-multisite'),
													absint($quota)
												);
											}
										} else {
											esc_html_e('Unlimited', 'ultimate-multisite');
										}
									},
								],
							],
							$second_row_actions
						);

						?>

					</td>

					</tr>

				<?php endforeach; ?>

			<?php else : ?>

			<div class="wu-text-center wu-bg-gray-100 wu-rounded wu-uppercase wu-font-semibold wu-text-xs wu-text-gray-700 wu-p-4 wu-m-4 wu-mt-6">
				<?php if ($email_accounts_enabled && ('unlimited' === $remaining_slots || $remaining_slots > 0)) : ?>
					<span class="dashicons-before dashicons-wu-mail wu-align-middle wu-mr-2"></span>
					<span><?php echo esc_html__('No email accounts created yet.', 'ultimate-multisite'); ?></span>
					<br>
					<a href="<?php echo esc_attr($create_modal['url']); ?>" class="wu-mt-3 wu-inline-block wu-text-blue-600 wu-no-underline wubox">
						<?php esc_html_e('Create your first email account', 'ultimate-multisite'); ?>
					</a>
				<?php elseif (! $email_accounts_enabled) : ?>
					<span class="dashicons-before dashicons-wu-info wu-align-middle wu-mr-2"></span>
					<span><?php echo esc_html__('Email accounts are not enabled for this network.', 'ultimate-multisite'); ?></span>
				<?php else : ?>
					<span class="dashicons-before dashicons-wu-alert-circle wu-align-middle wu-mr-2"></span>
					<span><?php echo esc_html__('Email accounts are not included in your current plan.', 'ultimate-multisite'); ?></span>
				<?php endif; ?>
			</div>

			<?php endif; ?>

		</tbody>

	</table>

	</div>

	</div>

</div>
