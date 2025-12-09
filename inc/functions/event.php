<?php
/**
 * Event Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Managers\Event_Manager;
use WP_Ultimo\Models\Event;

/**
 * Add a new event to the System.
 *
 * @since 2.0.0
 *
 * @param string $slug The slug of the event. Something like payment_received.
 * @param array  $payload defined when the event is registered.
 * @return bool
 */
function wu_do_event($slug, $payload) {

	return Event_Manager::get_instance()->do_event($slug, $payload);
}

/**
 * Register a new event globally in order to set the params.
 *
 * @since 2.0.0
 *
 * @param string $slug The slug of the event. Something like payment_received.
 * @param array  $args defined when the event is registered.
 * @return bool
 */
function wu_register_event_type($slug, $args) {

	return Event_Manager::get_instance()->register_event($slug, $args);
}

/**
 * Gets all th events registered in the system
 *
 * @since 2.0.0
 *
 * @return array with all events and parameters
 */
function wu_get_event_types() {

	return Event_Manager::get_instance()->get_events();
}

/**
 * Get the available event types as a key => label array.
 *
 * @since 2.0.0
 *
 * @return array
 */
function wu_get_event_types_as_options() {

	$event_types = Event_Manager::get_instance()->get_events();

	foreach ($event_types as $event_type_key => &$event_type) {
		$event_type = $event_type['name'];
	}

	return $event_types;
}

/**
 * Gets all th events registered in the system.
 *
 * @since 2.0.0
 *
 * @param string $slug of the event.
 * @return array with all events and parameters.
 */
function wu_get_event_type($slug) {

	return Event_Manager::get_instance()->get_event($slug);
}

/**
 * Queries events.
 *
 * @since 2.0.0
 *
 * @param array $query Query arguments.
 * @return \WP_Ultimo\Models\Event[]
 */
function wu_get_events($query = []) {

	return \WP_Ultimo\Models\Event::query($query);
}

/**
 * Gets a event on the ID.
 *
 * @since 2.0.0
 *
 * @param integer $event_id ID of the event to retrieve.
 * @return \WP_Ultimo\Models\Event|false
 */
function wu_get_event($event_id) {

	return \WP_Ultimo\Models\Event::get_by_id($event_id);
}

/**
 * Returns a event based on slug.
 *
 * @since 2.0.0
 *
 * @param string $event_slug The slug of the event.
 * @return \WP_Ultimo\Models\Event|false
 */
function wu_get_event_by_slug($event_slug) {

	return \WP_Ultimo\Models\Event::get_by('slug', $event_slug);
}

/**
 * Creates a new event.
 *
 * Check the wp_parse_args below to see what parameters are necessary.
 *
 * @since 2.0.0
 *
 * @param array $event_data Event attributes.
 * @return \WP_Error|\WP_Ultimo\Models\Event
 */
function wu_create_event($event_data) {

	$author_id = function_exists('get_current_user_id') ? get_current_user_id() : 0;

	$event_data = wp_parse_args(
		$event_data,
		[
			'severity'     => Event::SEVERITY_NEUTRAL,
			'initiator'    => 'system',
			'author_id'    => $author_id,
			'object_type'  => 'network',
			'object_id'    => 0,
			'date_created' => wu_get_current_time('mysql', true),
			'payload'      => [
				'key'       => 'None',
				'old_value' => 'None',
				'new_value' => 'None',
			],
		]
	);

	$event = new Event($event_data);

	$saved = $event->save();

	return is_wp_error($saved) ? $saved : $event;
}

/**
 * Generates payload arrays for events.
 *
 * If no model is passed, we mock one to generate example payloads!
 *
 * @since 2.0.0
 *
 * @param string       $model_name Model name. E.g. membership, site, customer, product, etc.
 * @param false|object $model The model object, or false.
 * @return array
 */
function wu_generate_event_payload($model_name, $model = false): array {

	$payload = [];

	if ( ! $model) {
		switch ($model_name) {
			case 'product':
				$model = wu_mock_product();
				break;
			case 'customer':
				$model = wu_mock_customer();
				break;
			case 'membership':
				$model = wu_mock_membership();
				break;
			case 'payment':
				$model = wu_mock_payment();
				break;
			case 'site':
				$model = wu_mock_site();
				break;
			case 'domain':
				$model = wu_mock_domain();
				break;
		}

		if ( ! $model) {
			return [];
		}
	}

	if ('customer' === $model_name) {
		$payload = $model->to_search_results();

		$payload = [
			'customer_id'                 => $payload['id'],
			'customer_name'               => $payload['display_name'],
			'customer_user_id'            => $payload['user_id'],
			'customer_user_email'         => $payload['user_email'],
			'customer_email_verification' => $payload['email_verification'],
			'customer_avatar'             => $payload['avatar'],
			'customer_billing_address'    => $payload['billing_address'],
			'customer_manage_url'         => wu_network_admin_url(
				'wp-ultimo-edit-customer',
				[
					'id' => $model->get_id(),
				]
			),
		];
	} elseif ('membership' === $model_name) {
		$payload = $model->to_search_results();

		$p = $payload;

		$payload = [
			'membership_id'                 => $p['id'],
			'membership_status'             => $p['status'],
			'membership_reference_code'     => $p['reference_code'],
			'membership_initial_amount'     => wu_format_currency($p['initial_amount'], $p['currency']),
			'membership_initial_amount_raw' => $p['initial_amount'],
			'membership_amount'             => wu_format_currency($p['amount'], $p['currency']),
			'membership_amount_raw'         => $p['amount'],
			'membership_currency'           => $p['currency'],
			'membership_description'        => $p['formatted_price'],
			'membership_gateway'            => $p['gateway'],
			'membership_date_expiration'    => $p['date_expiration'],
			'membership_manage_url'         => wu_network_admin_url(
				'wp-ultimo-edit-membership',
				[
					'id' => $model->get_id(),
				]
			),
		];
	} elseif ('product' === $model_name) {
		$payload = $model->to_search_results();

		$payload = [
			'product_id'            => $payload['id'],
			'product_amount'        => wu_format_currency($payload['amount'], $payload['currency']),
			'product_amount_raw'    => $payload['amount'],
			'product_setup_fee'     => wu_format_currency($payload['setup_fee'], $payload['currency']),
			'product_setup_fee_raw' => $payload['setup_fee'],
			'product_currency'      => $payload['currency'],
			'product_description'   => $payload['formatted_price'],
			'product_image'         => $payload['image'],
			'product_manage_url'    => wu_network_admin_url(
				'wp-ultimo-edit-payment',
				[
					'id' => $model->get_id(),
				]
			),
		];
	} elseif ('payment' === $model_name) {
		$payload = $model->to_search_results();

		$payload = [
			'payment_id'             => $payload['id'],
			'payment_status'         => $payload['status'],
			'payment_reference_code' => $payload['reference_code'],
			'payment_subtotal'       => wu_format_currency($payload['subtotal'], $payload['currency']),
			'payment_subtotal_raw'   => $payload['subtotal'],
			'payment_tax_total'      => wu_format_currency($payload['tax_total'], $payload['currency']),
			'payment_tax_total_raw'  => $payload['tax_total'],
			'payment_total'          => wu_format_currency($payload['total'], $payload['currency']),
			'payment_total_raw'      => $payload['total'],
			'payment_currency'       => $payload['currency'],
			'payment_product_names'  => $payload['product_names'],
			'payment_date_created'   => $payload['date_created'],
			'payment_gateway'        => $payload['gateway'],
			'payment_invoice_url'    => $model->get_invoice_url(),
			'payment_manage_url'     => wu_network_admin_url(
				'wp-ultimo-edit-payment',
				[
					'id' => $model->get_id(),
				]
			),
		];
	} elseif ('site' === $model_name) {
		$payload = $model->to_search_results();

		$payload = [
			'site_id'          => $payload['blog_id'],
			'site_title'       => $payload['title'],
			'site_description' => $payload['description'],
			'site_url'         => $payload['siteurl'],
			'site_admin_url'   => get_admin_url($model->get_id()),
			'site_manage_url'  => wu_network_admin_url(
				'wp-ultimo-edit-site',
				[
					'id' => $model->get_id(),
				]
			),
		];
	} elseif ('domain' === $model_name) {
		$payload = $model->to_search_results();

		$payload = [
			'domain_id'           => $payload['id'],
			'domain_domain'       => $payload['domain'],
			'domain_site_id'      => $payload['blog_id'],
			'domain_stage'        => $payload['stage'],
			'domain_active'       => var_export(wu_string_to_bool($payload['active']), true), // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
			'domain_primary'      => var_export(wu_string_to_bool($payload['primary_domain']), true), // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
			'domain_secure'       => var_export(wu_string_to_bool($payload['secure']), true), // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
			'domain_date_created' => $payload['date_created'],
			'domain_manage_url'   => wu_network_admin_url(
				'wp-ultimo-edit-domain',
				[
					'id' => $model->get_id(),
				]
			),
		];
	}

	return $payload;
}

/**
 * Checks if the payload is a callable or if it's ready to use.
 *
 * @since 2.0.8
 *
 * @param mixed $payload The payload.
 * @return array
 */
function wu_maybe_lazy_load_payload($payload) {

	if (is_callable($payload)) {
		$payload = (array) call_user_func($payload);
	}

	/*
	 * Adds the version number for control purposes.
	 */
	$payload['wu_version'] = wu_get_version();

	return $payload;
}
