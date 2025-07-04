<?php
/**
 * Repeater field view.
 *
 * @since 2.0.0
 */

?>
<?php if ( $field->title ) : ?>

	<li id=""
		class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

		<div class="wu-w-full wu-block">

			<?php

			/**
			 * Adds the partial title template.
			 *
			 * @since 2.0.0
			 */
			wu_get_template(
				'admin-pages/fields/partials/field-title',
				['field' => $field]
			);
			?>

			<?php

			/**
			 * Adds the partial title template.
			 *
			 * @since 2.0.0
			 */
			wu_get_template(
				'admin-pages/fields/partials/field-description',
				[
					'field' => $field,
				]
			);

			?>

		</div>

	</li>

<?php endif; ?>

<?php

if (! $field->values && $field->value) {
	$_values = [];
	$columns = array_keys($field->value);
	$values  = $field->value;

	foreach ($columns as $column) {
		$count = count(array_pop($field->value));
		for ($i = 0; $i < $count; $i++ ) {
			$_values[ $i ][ $column ] = $field->value[ $column ][ $i ];
		}
	}

	$field->values = $_values;
}

$fields = [];

foreach ($field->fields as $key => $value) {
	$fields[ $key . '[]' ] = $field->fields[ $key ];
}

if (is_array($field->values)) {
	$position  = 0;
	$field_len = count($field->values);
	foreach ($field->values as $key => $value) {
		$field_id = esc_attr($field->id);

		$field_id .= $field_len - 1 !== $position ? $key : '';
		++$position;
		?>
		<li id="<?php echo esc_attr($field_id); ?>-line"
			class="field-repeater wu-bg-gray-100 <?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="wu-w-full <?php echo esc_attr($field->classes); ?>">
				<?php
				foreach ($value as $field_name => $field_value) {
					$fields[ $field_name . '[]' ]['value'] = $field_value;
				}

				(new \WP_Ultimo\UI\Form(
					$field->id,
					$fields,
					[
						'views'                 => 'admin-pages/fields',
						'classes'               => 'wu-flex',
						'field_wrapper_classes' => 'wu-bg-transparent',
					]
				))->render();
				?>
			</div>
		</li>
		<?php
	}
} else {
	?>
	<li id="<?php echo esc_attr($field->id); ?>-line"
		class="field-repeater wu-bg-gray-100 <?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

		<div class="wu-w-full <?php echo esc_attr($field->classes); ?>">

			<?php
			/**
			 * Instantiate the form for the order details.
			 *
			 * @since 2.0.0
			 */
			(new \WP_Ultimo\UI\Form(
				$field->id,
				$fields,
				[
					'views'                 => 'admin-pages/fields',
					'classes'               => 'wu-flex',
					'field_wrapper_classes' => 'wu-bg-transparent',
				]
			))->render();

			?>

		</div>

	</li>
	<?php
}

?>

<li class="<?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<a class="button wu-w-full wu-text-center" href="#"
		v-on:click.prevent="duplicate_and_clean($event, '.field-repeater')">
		<?php esc_html_e('Add new Line', 'multisite-ultimate'); ?>
	</a>

</li>
