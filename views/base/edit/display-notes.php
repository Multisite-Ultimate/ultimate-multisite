<?php
/**
 * Display notes widget view.
 *
 * @since 2.0.0
 */
?>

<?php if (empty($notes)) : ?>

	<?php
	echo wu_render_empty_state( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		[
			'message'                  => __('No notes yet.', 'multisite-ultimate'),
			'sub_message'              => __('Use the "Add new Note" to create the first one.', 'multisite-ultimate'),
			'link_url'                 => false,
			'display_background_image' => false,
		]
	);
	?>

<?php else : ?>

	<?php foreach ($notes as $note) : ?>

		<div class="wu-flex wu-justify-end wu-items-end wu-flex-col wu-mt-4">

			<div class="wu-m-0 wu-p-3 wu-rounded wu-bg-gray-200 wu-text-right" id="wu-text-note">

				<?php echo wp_kses(wu_remove_empty_p($note->text), wu_kses_allowed_html()); ?>

			</div>

			<div class="wu-m-0 wu-mb-4 wu-p-0" id="wu-date-avatar">

				<?php $user = get_user_by('ID', $note->author_id); ?>

				<div class="wu-flex wu-overflow-hidden wu-ml-3 wu-mt-1">

					<?php
					echo wp_kses_post(
						get_avatar(
							$note->author_id,
							20,
							'identicon',
							'',
							[
								'force_display' => true,
								'class'         => 'wu-rounded-full wu-mr-2',
							]
						)
					);
					?>
					<?php echo esc_html($user->display_name); ?>

				</div>

				<div class="wu-text-right">

					<span class="wu-text-xs wu-text-gray-500">

						<?php echo esc_html(date_i18n('M d, H:i', strtotime($note->date_created))); ?>

					</span>

					<?php if (current_user_can('delete_notes')) : ?>

						<?php
						$modal_atts = [
							'object_id' => wu_request('id'),
							'model'     => $model,
							'note_id'   => $note->note_id,
							'height'    => 306,
						];
						?>

						<span class="wu-ml-2">

							<a class="dashicons-wu-trash wu-p-0 wu-border-none wu-text-red-600 wu-button-delete wu-no-underline wubox" href="<?php echo esc_url(wu_get_form_url('delete_note', $modal_atts)); ?>"
							title="<?php echo esc_attr__('Clear Note', 'multisite-ultimate'); ?>"></a>

						</span>

					<?php endif; ?>

				</div>

			</div>

		</div>

	<?php endforeach; ?>

<?php endif; ?>
