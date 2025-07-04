<?php
/**
 * Multisite Ultimate uninstall script.
 *
 * @package WP_Ultimo
 * @since 2.0.0
 */

if ( ! defined('WP_UNINSTALL_PLUGIN')) {
	exit;
} // end if;

global $wpdb;

$wu_settings_key = 'v2_settings';

/*
 * Manually grab the plugin settings. No helpers here =(
 */
$wu_settings = get_network_option(null, "wp-ultimo_{$wu_settings_key}");

/*
 * Check if we want to wipe things clean on uninstall...
 */
$wu_settings_uninstall_wipe_tables = $wu_settings['uninstall_wipe_tables'] ?? false;

/*
 * Let's do it.
 */
if ($wu_settings_uninstall_wipe_tables) {
	$wu_tables = [
		'customers',
		'customermeta',
		'discount_codes',
		'domain_mappings',
		'events',
		'forms',
		'membershipmeta',
		'memberships',
		'paymentmeta',
		'payments',
		'postmeta',
		'posts',
		'productmeta',
		'products',
		'webhooks',
	];

	$wu_prefix_table = "{$wpdb->prefix}wu_";

	foreach ($wu_tables as $wu_table) {
		$wu_table_name = $wu_prefix_table . $wu_table;

		$wu_table_version = "wpdb_wu_{$wu_table}_version";

		$wpdb->query("DROP TABLE IF EXISTS $wu_table_name"); // phpcs:ignore

		delete_network_option(null, $wu_table_version);
	} // end foreach;

	/*
	 * Remove states saved
	 */
	delete_network_option(null, "wp-ultimo_{$wu_settings_key}");
	delete_network_option(null, 'wp-ultimo_debug_faker');
	delete_network_option(null, 'wu_setup_finished');
	delete_network_option(null, 'wu_default_email_template');
	delete_network_option(null, 'wu_default_system_emails_created');
	delete_network_option(null, 'wu_default_invoice_template');
} // end if;
