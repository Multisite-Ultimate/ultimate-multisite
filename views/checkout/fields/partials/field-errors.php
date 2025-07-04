<?php
/**
 *      Errors field partial view.
 *
 * @since 2.0.0
 */
?>

<span 
	v-cloak 
	class="wu-block wu-bg-red-100 wu-p-2 wu-mb-4" 
	v-if="get_error('<?php echo esc_attr($field->id); ?>')" 
	v-html="get_error('<?php echo esc_attr($field->id); ?>').message"
></span>
