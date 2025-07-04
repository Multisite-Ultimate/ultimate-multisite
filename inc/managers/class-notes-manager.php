<?php
/**
 * Notes Manager
 *
 * Handles processes related to notes.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Notes
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to notes.
 *
 * @since 2.0.0
 */
class Notes_Manager extends Base_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = 'notes';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $model_class = '\\WP_Ultimo\\Models\\Notes';

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		add_action('plugins_loaded', [$this, 'register_forms']);

		add_filter('wu_membership_options_sections', [$this, 'add_notes_options_section'], 10, 2);

		add_filter('wu_payments_options_sections', [$this, 'add_notes_options_section'], 10, 2);

		add_filter('wu_customer_options_sections', [$this, 'add_notes_options_section'], 10, 2);

		add_filter('wu_site_options_sections', [$this, 'add_notes_options_section'], 10, 2);
	}

	/**
	 * Register ajax forms that we use for object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms(): void {
		/*
		 * Add note
		 */
		wu_register_form(
			'add_note',
			[
				'render'     => [$this, 'render_add_note_modal'],
				'handler'    => [$this, 'handle_add_note_modal'],
				'capability' => 'edit_notes',
			]
		);

		/*
		 * Clear notes
		 */
		wu_register_form(
			'clear_notes',
			[
				'render'     => [$this, 'render_clear_notes_modal'],
				'handler'    => [$this, 'handle_clear_notes_modal'],
				'capability' => 'delete_notes',
			]
		);

		/*
		 * Clear notes
		 */
		wu_register_form(
			'delete_note',
			[
				'render'     => [$this, 'render_delete_note_modal'],
				'handler'    => [$this, 'handle_delete_note_modal'],
				'capability' => 'delete_notes',
			]
		);
	}

	/**
	 * Add all domain mapping settings.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $sections Array sections.
	 * @param object $obj   The object.
	 *
	 * @return array
	 */
	public function add_notes_options_section($sections, $obj) {

		if ( ! current_user_can('read_notes') && ! current_user_can('edit_notes')) {
			return $sections;
		}

		$fields = [];

		$fields['notes_panel'] = [
			'type'              => 'html',
			'wrapper_classes'   => 'wu-m-0 wu-p-2 wu-notes-wrapper',
			'wrapper_html_attr' => [
				'style' => sprintf('min-height: 500px; background: url("%s");', wu_get_asset('pattern-wp-ultimo.webp')),
			],
			'content'           => wu_get_template_contents(
				'base/edit/display-notes',
				[
					'notes' => $obj->get_notes(),
					'model' => $obj->model,
				]
			),
		];

		$fields_buttons = [];

		if (current_user_can('delete_notes')) {
			$fields_buttons['button_clear_notes'] = [
				'type'            => 'link',
				'display_value'   => __('Clear Notes', 'multisite-ultimate'),
				'wrapper_classes' => 'wu-mb-0',
				'classes'         => 'button wubox',
				'html_attr'       => [
					'href'  => wu_get_form_url(
						'clear_notes',
						[
							'object_id' => $obj->get_id(),
							'model'     => $obj->model,
						]
					),
					'title' => __('Clear Notes', 'multisite-ultimate'),
				],
			];
		}

		if (current_user_can('edit_notes')) {
			$fields_buttons['button_add_note'] = [
				'type'            => 'link',
				'display_value'   => __('Add new Note', 'multisite-ultimate'),
				'wrapper_classes' => 'wu-mb-0',
				'classes'         => 'button button-primary wubox wu-absolute wu-right-5',
				'html_attr'       => [
					'href'  => wu_get_form_url(
						'add_note',
						[
							'object_id' => $obj->get_id(),
							'model'     => $obj->model,
							'height'    => 306,
						]
					),
					'title' => __('Add new Note', 'multisite-ultimate'),
				],
			];
		}

		$fields['buttons'] = [
			'type'            => 'group',
			'wrapper_classes' => 'wu-bg-white',
			'fields'          => $fields_buttons,
		];

		$sections['notes'] = [
			'title'  => __('Notes', 'multisite-ultimate'),
			'desc'   => __('Add notes to this model.', 'multisite-ultimate'),
			'icon'   => 'dashicons-wu-text-document',
			'order'  => 1001,
			'fields' => $fields,
		];

		return $sections;
	}

	/**
	 * Renders the notes form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_add_note_modal(): void {

		$fields = [
			'content'         => [
				'id'        => 'content',
				'type'      => 'wp-editor',
				'title'     => __('Note Content', 'multisite-ultimate'),
				'desc'      => __('Basic formatting is supported.', 'multisite-ultimate'),
				'settings'  => [
					'tinymce' => [
						'toolbar1' => 'bold,italic,strikethrough,link,unlink,undo,redo,pastetext',
					],
				],
				'html_attr' => [
					'v-model' => 'content',
				],
			],
			'submit_add_note' => [
				'type'            => 'submit',
				'title'           => __('Add Note', 'multisite-ultimate'),
				'placeholder'     => __('Add Note', 'multisite-ultimate'),
				'value'           => 'save',
				'classes'         => 'wu-w-full button button-primary',
				'wrapper_classes' => 'wu-items-end',
			],
			'object_id'       => [
				'type'  => 'hidden',
				'value' => wu_request('object_id'),
			],
			'model'           => [
				'type'  => 'hidden',
				'value' => wu_request('model'),
			],
		];

		$fields = apply_filters('wu_notes_options_section_fields', $fields);

		$form = new \WP_Ultimo\UI\Form(
			'add_note',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'add_note',
					'data-state'  => wu_convert_to_state(
						[
							'content' => '',
						]
					),
				],
			]
		);

		$form->render();
	}

	/**
	 * Handles the notes form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_add_note_modal(): void {

		$model         = wu_request('model');
		$function_name = "wu_get_{$model}";
		$object        = $function_name(wu_request('object_id'));

		$status = $object->add_note(
			[
				'text'      => wu_remove_empty_p(wu_request('content')),
				'author_id' => get_current_user_id(),
				'note_id'   => uniqid(),
			]
		);

		if (is_wp_error($status)) {
			wp_send_json_error($status);
		}

		wp_send_json_success(
			[
				'redirect_url' => wu_network_admin_url(
					"wp-ultimo-edit-{$model}",
					[
						'id'      => $object->get_id(),
						'updated' => 1,
						'options' => 'notes',
					]
				),
			]
		);
	}

	/**
	 * Renders the clear notes confirmation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_clear_notes_modal(): void {

		$fields = [
			'confirm_clear_notes' => [
				'type'      => 'toggle',
				'title'     => __('Confirm clear all notes?', 'multisite-ultimate'),
				'desc'      => __('This action can not be undone.', 'multisite-ultimate'),
				'html_attr' => [
					'v-model' => 'confirmed',
				],
			],
			'submit_clear_notes'  => [
				'type'            => 'submit',
				'title'           => __('Clear Notes', 'multisite-ultimate'),
				'placeholder'     => __('Clear Notes', 'multisite-ultimate'),
				'value'           => 'save',
				'classes'         => 'wu-w-full button button-primary',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => [
					'v-bind:disabled' => '!confirmed',
				],
			],
			'object_id'           => [
				'type'  => 'hidden',
				'value' => wu_request('object_id'),
			],
			'model'               => [
				'type'  => 'hidden',
				'value' => wu_request('model'),
			],
		];

		$form = new \WP_Ultimo\UI\Form(
			'clear_notes',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'clear_notes',
					'data-state'  => wu_convert_to_state(
						[
							'confirmed' => false,
						]
					),
				],
			]
		);

		$form->render();
	}

	/**
	 * Handles the clear notes modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_clear_notes_modal(): void {

		$model         = wu_request('model');
		$function_name = "wu_get_{$model}";
		$object        = $function_name(wu_request('object_id'));

		if ( ! $object) {
			return;
		}

		$status = $object->clear_notes();

		if (is_wp_error($status)) {
			wp_send_json_error($status);
		}

		wp_send_json_success(
			[
				'redirect_url' => wu_network_admin_url(
					"wp-ultimo-edit-{$model}",
					[
						'id'      => $object->get_id(),
						'deleted' => 1,
						'options' => 'notes',
					]
				),
			]
		);
	}

	/**
	 * Renders the delete note confirmation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_delete_note_modal(): void {

		$fields = [
			'confirm_delete_note' => [
				'type'      => 'toggle',
				'title'     => __('Confirm clear the note?', 'multisite-ultimate'),
				'desc'      => __('This action can not be undone.', 'multisite-ultimate'),
				'html_attr' => [
					'v-model' => 'confirmed',
				],
			],
			'submit_delete_note'  => [
				'type'            => 'submit',
				'title'           => __('Clear Note', 'multisite-ultimate'),
				'placeholder'     => __('Clear Note', 'multisite-ultimate'),
				'value'           => 'save',
				'classes'         => 'wu-w-full button button-primary',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => [
					'v-bind:disabled' => '!confirmed',
				],
			],
			'object_id'           => [
				'type'  => 'hidden',
				'value' => wu_request('object_id'),
			],
			'model'               => [
				'type'  => 'hidden',
				'value' => wu_request('model'),
			],
			'note_id'             => [
				'type'  => 'hidden',
				'value' => wu_request('note_id'),
			],
		];

		$form = new \WP_Ultimo\UI\Form(
			'delete_note',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'delete_note',
					'data-state'  => wu_convert_to_state(
						[
							'confirmed' => false,
						]
					),
				],
			]
		);

		$form->render();
	}

	/**
	 * Handles the delete note modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_delete_note_modal(): void {

		$model         = wu_request('model');
		$function_name = "wu_get_{$model}";
		$object        = $function_name(wu_request('object_id'));
		$note_id       = wu_request('note_id');

		if ( ! $object) {
			return;
		}

		$status = $object->delete_note($note_id);

		if (is_wp_error($status) || false === $status) {
			wp_send_json_error(new \WP_Error('not-found', __('Note not found', 'multisite-ultimate')));
		}

		wp_send_json_success(
			[
				'redirect_url' => wu_network_admin_url(
					"wp-ultimo-edit-{$model}",
					[
						'id'      => $object->get_id(),
						'deleted' => 1,
						'options' => 'notes',
					]
				),
			]
		);
	}
}
