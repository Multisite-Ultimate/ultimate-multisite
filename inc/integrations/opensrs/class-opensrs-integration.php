<?php
/**
 * OpenSRS Integration for Ultimate Multisite
 * 
 * Phase 1: Core API Handler, Settings, and Database Schema
 * 
 * @package UltimateMultisite
 * @subpackage OpenSRS
 * @since 2.5.0
 */

namespace WP_Ultimo\Integrations\OpenSRS;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main OpenSRS Integration Class
 */
class OpenSRS_Integration {
	
	/**
	 * Singleton instance
	 *
	 * @var OpenSRS_Integration
	 */
	private static $instance = null;
	
	/**
	 * Get singleton instance
	 *
	 * @return OpenSRS_Integration
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init_hooks();
	}
	
	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		// Register activation hook for database setup
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		
		// Add settings page
		add_action( 'wu_settings_sections', array( $this, 'register_settings_section' ) );
		add_action( 'wu_settings_opensrs', array( $this, 'render_settings_page' ) );
		
		// Register daily cron job for pricing updates
		add_action( 'wu_opensrs_update_pricing', array( 'OpenSRS_Pricing', 'update_pricing_cron' ) );
		
		// Register weekly cron job for renewal checks
		add_action( 'wu_opensrs_check_renewals', array( 'OpenSRS_Renewals', 'check_renewals_cron' ) );
		
		// Register monthly cron job for expiration checks
		add_action( 'wu_opensrs_check_expirations', array( 'OpenSRS_Renewals', 'check_expirations_cron' ) );
	}
	
	/**
	 * Activation hook - Create database tables and schedule crons
	 */
	public function activate() {
		$this->create_database_tables();
		$this->schedule_cron_jobs();
	}
	
	/**
	 * Create database tables for OpenSRS integration
	 */
	private function create_database_tables() {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'wu_opensrs_domains';
		
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			domain_id varchar(100) NOT NULL,
			domain_name varchar(255) NOT NULL,
			customer_id bigint(20) UNSIGNED NOT NULL,
			site_id bigint(20) UNSIGNED NOT NULL,
			product_id bigint(20) UNSIGNED DEFAULT NULL,
			registration_date datetime NOT NULL,
			expiration_date datetime NOT NULL,
			renewal_date datetime DEFAULT NULL,
			last_renewal_check datetime DEFAULT NULL,
			auto_renew tinyint(1) DEFAULT 0,
			status varchar(50) NOT NULL DEFAULT 'active',
			nameservers text DEFAULT NULL,
			whois_privacy tinyint(1) DEFAULT 0,
			domain_lock tinyint(1) DEFAULT 1,
			contact_info text DEFAULT NULL,
			dns_records longtext DEFAULT NULL,
			last_updated datetime DEFAULT NULL,
			created_at datetime NOT NULL,
			updated_at datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY domain_id (domain_id),
			KEY customer_id (customer_id),
			KEY site_id (site_id),
			KEY domain_name (domain_name),
			KEY expiration_date (expiration_date),
			KEY status (status)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		// Create pricing table
		$pricing_table = $wpdb->prefix . 'wu_opensrs_pricing';
		
		$pricing_sql = "CREATE TABLE IF NOT EXISTS $pricing_table (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			tld varchar(50) NOT NULL,
			registration_price decimal(10,2) NOT NULL,
			renewal_price decimal(10,2) NOT NULL,
			transfer_price decimal(10,2) NOT NULL,
			whois_privacy_price decimal(10,2) DEFAULT 0.00,
			currency varchar(10) NOT NULL DEFAULT 'USD',
			last_updated datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY tld (tld)
		) $charset_collate;";
		
		dbDelta( $pricing_sql );
	}
	
	/**
	 * Schedule cron jobs
	 */
	private function schedule_cron_jobs() {
		// Daily pricing update at 2 AM
		if ( ! wp_next_scheduled( 'wu_opensrs_update_pricing' ) ) {
			wp_schedule_event( strtotime( '02:00:00' ), 'daily', 'wu_opensrs_update_pricing' );
		}
		
		// Weekly renewal check on Sundays at 1 AM
		if ( ! wp_next_scheduled( 'wu_opensrs_check_renewals' ) ) {
			wp_schedule_event( strtotime( 'next Sunday 01:00:00' ), 'weekly', 'wu_opensrs_check_renewals' );
		}
		
		// Monthly expiration check on 1st of month at 3 AM
		if ( ! wp_next_scheduled( 'wu_opensrs_check_expirations' ) ) {
			wp_schedule_event( strtotime( 'first day of next month 03:00:00' ), 'monthly', 'wu_opensrs_check_expirations' );
		}
	}
	
	/**
	 * Register settings section
	 */
	public function register_settings_section() {
		// Use new Ultimate Multisite settings API
		wu_register_settings_section( 'opensrs', array(
			'title' => __( 'OpenSRS Integration', 'wp-ultimo' ),
			'desc'  => __( 'Configure OpenSRS domain registration and management settings.', 'wp-ultimo' ),
			'icon'  => 'dashicons-admin-site-alt3',
		) );
	
		// Register individual settings fields
		$this->register_settings_fields();
	}

	/**
	 * Register settings fields
	 */
	private function register_settings_fields() {
		// Enable/Disable toggle
		wu_register_settings_field( 'opensrs', 'opensrs_enabled', array(
			'title'   => __( 'Enable OpenSRS Integration', 'wp-ultimo' ),
			'desc'    => __( 'Enable or disable the OpenSRS integration', 'wp-ultimo' ),
			'type'    => 'toggle',
			'default' => false,
		) );
	
		// API Mode
		wu_register_settings_field( 'opensrs', 'opensrs_mode', array(
			'title'   => __( 'API Mode', 'wp-ultimo' ),
			'desc'    => __( 'Select whether to use the test (sandbox) or live environment', 'wp-ultimo' ),
			'type'    => 'select',
			'options' => array(
				'test' => __( 'Test/Sandbox Mode', 'wp-ultimo' ),
				'live' => __( 'Live Mode', 'wp-ultimo' ),
			),
			'default' => 'test',
		) );
	
		// Username
		wu_register_settings_field( 'opensrs', 'opensrs_username', array(
			'title'       => __( 'Reseller Username', 'wp-ultimo' ),
			'desc'        => __( 'Your OpenSRS reseller username', 'wp-ultimo' ),
			'type'        => 'text',
			'placeholder' => 'your_username',
		) );
	
		// API Key
		wu_register_settings_field( 'opensrs', 'opensrs_api_key', array(
			'title'       => __( 'API Key', 'wp-ultimo' ),
			'desc'        => __( 'Your OpenSRS API key (generate from OpenSRS Control Panel)', 'wp-ultimo' ),
			'type'        => 'password',
			'placeholder' => 'your_api_key',
		) );
	
		// Test Connection Button
		wu_register_settings_field( 'opensrs', 'opensrs_test_connection', array(
			'title'    => __( 'Test API Connection', 'wp-ultimo' ),
			'desc'     => __( 'Test your OpenSRS API credentials and connection', 'wp-ultimo' ),
			'type'     => 'submit',
			'value'    => __( 'Test Connection', 'wp-ultimo' ),
			'classes'  => 'button button-primary',
			'wrapper_classes' => 'wu-bg-gray-100 wu-p-4',
		) );
	}

	} // End of OpenSRS_Integration class

	/**
	 * OpenSRS API Handler Class
	 */
	class OpenSRS_API {
	
	/**
	 * API endpoints
	 */
	const TEST_ENDPOINT = 'https://horizon.opensrs.net:55443';
	const LIVE_ENDPOINT = 'https://rr-n1-tor.opensrs.net:55443';
	
	/**
	 * Get API endpoint based on mode
	 *
	 * @return string
	 */
	private static function get_endpoint() {
		$mode = wu_get_setting( 'opensrs_mode', 'test' );
		return ( 'live' === $mode ) ? self::LIVE_ENDPOINT : self::TEST_ENDPOINT;
	}
	
	/**
	 * Generate MD5 signature for authentication
	 *
	 * @param string $xml XML content
	 * @return string
	 */
	private static function generate_signature( $xml ) {
		$api_key = wu_get_setting( 'opensrs_api_key', '' );
		$md5_signature = md5( md5( $xml . $api_key ) . $api_key );
		return $md5_signature;
	}
	
	/**
	 * Make API request
	 *
	 * @param string $xml XML request
	 * @return array|WP_Error
	 */
	private static function make_request( $xml ) {
		$username = wu_get_setting( 'opensrs_username', '' );
		$signature = self::generate_signature( $xml );
		$endpoint = self::get_endpoint();
		
		$headers = array(
			'Content-Type' => 'text/xml',
			'X-Username' => $username,
			'X-Signature' => $signature,
		);
		
		$response = wp_remote_post( $endpoint, array(
			'body' => $xml,
			'headers' => $headers,
			'timeout' => 30,
			'sslverify' => true,
		) );
		
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		
		$body = wp_remote_retrieve_body( $response );
		$parsed = self::parse_xml_response( $body );
		
		return $parsed;
	}
	
	/**
	 * Parse XML response
	 *
	 * @param string $xml XML response
	 * @return array
	 */
	private static function parse_xml_response( $xml ) {
		$doc = new \DOMDocument();
		$doc->loadXML( $xml );
		
		$result = array(
			'is_success' => 0,
			'response_code' => '',
			'response_text' => '',
			'attributes' => array(),
		);
		
		// Parse response - simplified version
		$xpath = new \DOMXPath( $doc );
		
		// This is a basic parser - you may need to enhance it based on specific responses
		$is_success = $xpath->query( '//item[@key="is_success"]' );
		if ( $is_success->length > 0 ) {
			$result['is_success'] = (int) $is_success->item(0)->nodeValue;
		}
		
		return $result;
	}
	
	/**
	 * Test API connection
	 *
	 * @return array
	 */
	public static function test_connection() {
		$xml = self::build_xml_request( 'BALANCE', 'GET' );
		$response = self::make_request( $xml );
		
		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
			);
		}
		
		if ( 1 === $response['is_success'] ) {
			return array(
				'success' => true,
				'message' => __( 'Connection successful!', 'wp-ultimo' ),
			);
		}
		
		return array(
			'success' => false,
			'message' => $response['response_text'],
		);
	}
	
	/**
	 * Test connection callback for settings page
	 */
	public static function test_connection_callback() {
		check_ajax_referer( 'wu-opensrs-test', 'nonce' );
		
		$result = self::test_connection();
		wp_send_json( $result );
	}
	
	/**
	 * Build XML request
	 *
	 * @param string $object Object type (DOMAIN, BALANCE, etc.)
	 * @param string $action Action type (GET, LOOKUP, REGISTER, etc.)
	 * @param array  $attributes Request attributes
	 * @return string
	 */
	private static function build_xml_request( $object, $action, $attributes = array() ) {
		$xml = '<?xml version="1.0" encoding="UTF-8" standalone="no" ?>';
		$xml .= '<!DOCTYPE OPS_envelope SYSTEM "ops.dtd">';
		$xml .= '<OPS_envelope>';
		$xml .= '<header><version>0.9</version></header>';
		$xml .= '<body><data_block><dt_assoc>';
		$xml .= '<item key="protocol">XCP</item>';
		$xml .= '<item key="action">' . esc_xml( $action ) . '</item>';
		$xml .= '<item key="object">' . esc_xml( $object ) . '</item>';
		
		if ( ! empty( $attributes ) ) {
			$xml .= '<item key="attributes"><dt_assoc>';
			foreach ( $attributes as $key => $value ) {
				$xml .= '<item key="' . esc_xml( $key ) . '">' . esc_xml( $value ) . '</item>';
			}
			$xml .= '</dt_assoc></item>';
		}
		
		$xml .= '</dt_assoc></data_block></body>';
		$xml .= '</OPS_envelope>';
		
		return $xml;
	}
	
	/**
	 * 1. Domain availability lookup
	 *
	 * @param string $domain Domain name to check
	 * @return array|WP_Error
	 */
	public static function lookup_domain( $domain ) {
		$xml = self::build_xml_request( 'DOMAIN', 'LOOKUP', array(
			'domain' => $domain,
		) );
		
		return self::make_request( $xml );
	}
	
	/**
	 * 2. Register domain
	 *
	 * @param array $data Domain registration data
	 * @return array|WP_Error
	 */
	public static function register_domain( $data ) {
		$attributes = array(
			'domain' => $data['domain'],
			'period' => isset( $data['period'] ) ? $data['period'] : 1,
			'custom_nameservers' => isset( $data['custom_nameservers'] ) ? $data['custom_nameservers'] : 0,
			'reg_username' => $data['username'],
			'reg_password' => $data['password'],
			'handle' => 'process',
		);
		
		// Add contact information
		if ( isset( $data['contact'] ) ) {
			$attributes = array_merge( $attributes, $data['contact'] );
		}
		
		$xml = self::build_xml_request( 'DOMAIN', 'SW_REGISTER', $attributes );
		
		return self::make_request( $xml );
	}
	
	/**
	 * 3. Renew domain
	 *
	 * @param string $domain Domain name
	 * @param int    $period Renewal period in years
	 * @return array|WP_Error
	 */
	public static function renew_domain( $domain, $period = 1 ) {
		$xml = self::build_xml_request( 'DOMAIN', 'RENEW', array(
			'domain' => $domain,
			'period' => $period,
			'handle' => 'process',
		) );
		
		return self::make_request( $xml );
	}
	
	/**
	 * 4. Transfer domain
	 *
	 * @param array $data Transfer data
	 * @return array|WP_Error
	 */
	public static function transfer_domain( $data ) {
		$attributes = array(
			'domain' => $data['domain'],
			'auth_info' => $data['auth_code'],
			'period' => isset( $data['period'] ) ? $data['period'] : 1,
			'reg_username' => $data['username'],
			'reg_password' => $data['password'],
		);
		
		$xml = self::build_xml_request( 'DOMAIN', 'TRANSFER', $attributes );
		
		return self::make_request( $xml );
	}
	
	/**
	 * 5. Manage DNS/Nameservers
	 *
	 * @param string $domain Domain name
	 * @param array  $nameservers Array of nameservers
	 * @return array|WP_Error
	 */
	public static function update_nameservers( $domain, $nameservers ) {
		$attributes = array(
			'domain' => $domain,
			'op_type' => 'assign',
		);
		
		// Add nameservers
		for ( $i = 1; $i <= count( $nameservers ); $i++ ) {
			$attributes[ 'ns' . $i ] = $nameservers[ $i - 1 ];
		}
		
		$xml = self::build_xml_request( 'DOMAIN', 'MODIFY', $attributes );
		
		return self::make_request( $xml );
	}
	
	/**
	 * 6. Enable/Disable WHOIS privacy
	 *
	 * @param string $domain Domain name
	 * @param bool   $enable Enable or disable privacy
	 * @return array|WP_Error
	 */
	public static function toggle_whois_privacy( $domain, $enable = true ) {
		$state = $enable ? 'enable' : 'disable';
		
		$xml = self::build_xml_request( 'DOMAIN', 'MODIFY', array(
			'domain' => $domain,
			'data' => $state . '_whois_privacy',
		) );
		
		return self::make_request( $xml );
	}
	
	/**
	 * 7. Lock/Unlock domain
	 *
	 * @param string $domain Domain name
	 * @param bool   $lock Lock or unlock domain
	 * @return array|WP_Error
	 */
	public static function toggle_domain_lock( $domain, $lock = true ) {
		$state = $lock ? 'lock' : 'unlock';
		
		$xml = self::build_xml_request( 'DOMAIN', 'MODIFY', array(
			'domain' => $domain,
			'data' => $state,
		) );
		
		return self::make_request( $xml );
	}
	
	/**
	 * 8. Update contact information
	 *
	 * @param string $domain Domain name
	 * @param array  $contact_data Contact information
	 * @return array|WP_Error
	 */
	public static function update_contact_info( $domain, $contact_data ) {
		$attributes = array(
			'domain' => $domain,
			'contact_set' => 'all',
		);
		
		$attributes = array_merge( $attributes, $contact_data );
		
		$xml = self::build_xml_request( 'DOMAIN', 'MODIFY', $attributes );
		
		return self::make_request( $xml );
	}
	
	/**
	 * Get domain info
	 *
	 * @param string $domain Domain name
	 * @return array|WP_Error
	 */
	public static function get_domain_info( $domain ) {
		$xml = self::build_xml_request( 'DOMAIN', 'GET', array(
			'domain' => $domain,
			'type' => 'all_info',
		) );
		
		return self::make_request( $xml );
	}
	
	/**
	 * Get pricing for TLDs
	 *
	 * @return array|WP_Error
	 */
	public static function get_pricing() {
		$xml = self::build_xml_request( 'PRICE', 'GET_PRICE', array(
			'product' => 'domain',
		) );
		
		return self::make_request( $xml );
	}
}

/**
 * OpenSRS Pricing Class
 */
class OpenSRS_Pricing {
	
	/**
	 * Update pricing from OpenSRS API
	 */
	public static function update_pricing_cron() {
		if ( ! wu_get_setting( 'opensrs_enabled', false ) ) {
			return;
		}
		
		$pricing_data = OpenSRS_API::get_pricing();
		
		if ( is_wp_error( $pricing_data ) ) {
			error_log( 'OpenSRS Pricing Update Failed: ' . $pricing_data->get_error_message() );
			return;
		}
		
		self::store_pricing( $pricing_data );
	}
	
	/**
	 * Store pricing in database
	 *
	 * @param array $pricing_data Pricing data from API
	 */
	private static function store_pricing( $pricing_data ) {
		global $wpdb;
		
		$table = $wpdb->prefix . 'wu_opensrs_pricing';
		
		// Parse and store pricing data
		// This is a simplified version - actual implementation depends on API response format
		foreach ( $pricing_data as $tld => $prices ) {
			$wpdb->replace(
				$table,
				array(
					'tld' => $tld,
					'registration_price' => $prices['registration'],
					'renewal_price' => $prices['renewal'],
					'transfer_price' => $prices['transfer'],
					'whois_privacy_price' => isset( $prices['privacy'] ) ? $prices['privacy'] : 0,
					'currency' => 'USD',
					'last_updated' => current_time( 'mysql' ),
				),
				array( '%s', '%f', '%f', '%f', '%f', '%s', '%s' )
			);
		}
	}
	
	/**
	 * Get price for a TLD
	 *
	 * @param string $tld TLD (e.g., 'com', 'net')
	 * @param string $type Type of price (registration, renewal, transfer)
	 * @return float|null
	 */
	public static function get_price( $tld, $type = 'registration' ) {
		global $wpdb;
		
		$table = $wpdb->prefix . 'wu_opensrs_pricing';
		$column = $type . '_price';
		
		$price = $wpdb->get_var( $wpdb->prepare(
			"SELECT $column FROM $table WHERE tld = %s",
			$tld
		) );
		
		return $price ? (float) $price : null;
	}
}

/**
 * OpenSRS Renewals Class
 */
class OpenSRS_Renewals {
	
	/**
	 * Check renewal status for all domains (weekly)
	 */
	public static function check_renewals_cron() {
		if ( ! wu_get_setting( 'opensrs_enabled', false ) ) {
			return;
		}
		
		global $wpdb;
		$table = $wpdb->prefix . 'wu_opensrs_domains';
		
		// Get all active domains
		$domains = $wpdb->get_results(
			"SELECT * FROM $table WHERE status = 'active'"
		);
		
		foreach ( $domains as $domain ) {
			$info = OpenSRS_API::get_domain_info( $domain->domain_name );
			
			if ( ! is_wp_error( $info ) && isset( $info['attributes']['expiry_date'] ) ) {
				// Update domain information
				$wpdb->update(
					$table,
					array(
						'expiration_date' => $info['attributes']['expiry_date'],
						'last_renewal_check' => current_time( 'mysql' ),
					),
					array( 'id' => $domain->id ),
					array( '%s', '%s' ),
					array( '%d' )
				);
			}
		}
	}
	
	/**
	 * Check for expiring domains (monthly)
	 */
	public static function check_expirations_cron() {
		if ( ! wu_get_setting( 'opensrs_enabled', false ) ) {
			return;
		}
		
		global $wpdb;
		$table = $wpdb->prefix . 'wu_opensrs_domains';
		
		// Get domains expiring in 30 days or less
		$expiring_domains = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table 
				WHERE status = 'active' 
				AND expiration_date <= DATE_ADD(NOW(), INTERVAL 30 DAY)
				AND expiration_date >= NOW()"
			)
		);
		
		foreach ( $expiring_domains as $domain ) {
			self::send_expiration_notice( $domain );
		}
	}
	
	/**
	 * Send expiration notice to customer
	 *
	 * @param object $domain Domain object
	 */
	private static function send_expiration_notice( $domain ) {
		$customer = wu_get_customer( $domain->customer_id );
		
		if ( ! $customer ) {
			return;
		}
		
		$days_until_expiration = floor( ( strtotime( $domain->expiration_date ) - time() ) / DAY_IN_SECONDS );
		
		// Use Ultimate Multisite email system
		wu_send_mail(
			$customer->get_email_address(),
			__( 'Domain Expiration Notice', 'wp-ultimo' ),
			sprintf(
				__( 'Your domain %s will expire in %d days. Please renew it to avoid service interruption.', 'wp-ultimo' ),
				$domain->domain_name,
				$days_until_expiration
			)
		);
	}
}

// Initialize the integration
OpenSRS_Integration::get_instance();