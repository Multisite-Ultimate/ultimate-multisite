<?php
/**
 * Log domain view.
 *
 * @since 2.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div id="wu-domain-log" class="">

	<pre id="content" class="wu-overflow-auto wu-p-4 wu-m-0 wu-mt-3 wu-rounded wu-content-center wu-bg-gray-800 wu-text-white wu-font-mono wu-border wu-border-solid wu-border-gray-300 wu-max-h-screen wu-overflow-y-auto">
	<?php esc_html_e('Loading log contents...', 'ultimate-multisite'); ?>
	</pre>

</div>

<div class="wu-box-border wu-p-4 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid wu-bg-gray-200 wu-text-right wu--mx-3 wu-mt-3 wu--mb-3 wu-relative wu-overflow-hidden">

	<button id="refresh-logs" type="submit" name="submit_button" value="refresh-logs" class="button wu-float-right">
	<?php esc_html_e('Refresh Logs', 'ultimate-multisite'); ?>
	</button>

</div>

