<?php
/**
 * Ajax button field view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-my-6">

	<div class="wu-flex">

	<div class="wu-w-1/3">

		<label for="<?php echo esc_attr($field->id); ?>">

		<?php echo $field->title; ?>

		</label>

	</div>

	<div class="wu-w-2/3">

		<label for="<?php echo esc_attr($field->id); ?>">
		<button class="button" name="<?php echo esc_attr($field->id); ?>" id="<?php echo esc_attr($field->id); ?>" value="<?php echo esc_attr(wp_create_nonce($field->action)); ?>">
			<?php echo esc_html($field->title); ?>
		</button>
		</label>

		<?php if ($field->desc) : ?>

		<p class="description" id="<?php echo esc_attr($field->id); ?>-desc">

			<?php echo esc_html($field->desc); ?>

		</p>

		<?php endif; ?>

	</div>

	</div>

</div>

<script type="text/javascript">
(function($) {
	$('#<?php echo esc_js($field->id); ?>').on('click', function(e) {

	e.preventDefault();

	var $this = $(this);
	var default_label = $this.html();

	$this.html('...').attr('disabled', 'disabled');

	$.ajax({
		url: "<?php echo esc_js(admin_url('admin-ajax.php?action=') . $field->action); ?>",
		dataType: "json",
		success: function(response) {

		$this.html(response.message);

		setTimeout(function() {
			$this.html(default_label).removeAttr('disabled');
		}, 4000);

		}

	});

	});
})(jQuery);
</script>
