<?php
/**
 * OpenSRS Checkout Integration for Ultimate Multisite
 * 
 * File: Checkout Forms
 * 
 * @package UltimateMultisite
 * @subpackage OpenSRS
 * @since 2.5.0
 */

/**
 * OpenSRS Checkout Integration Class
 */
class OpenSRS_Checkout_Integration {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_hooks();
	}
	
	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Add domain field to checkout form
		add_action( 'wu_checkout_form_fields', array( $this, 'add_domain_checkout_field' ), 20 );
		
		// Handle AJAX domain availability check
		add_action( 'wp_ajax_wu_check_domain_availability', array( $this, 'ajax_check_domain_availability' ) );
		add_action( 'wp_ajax_nopriv_wu_check_domain_availability', array( $this, 'ajax_check_domain_availability' ) );
		
		// Process domain registration on checkout
		add_action( 'wu_checkout_process', array( $this, 'process_domain_registration' ), 10, 2 );
		
		// Add domain cost to cart
		add_filter( 'wu_cart_line_items', array( $this, 'add_domain_to_cart' ), 10, 2 );
		
		// Enqueue checkout scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_checkout_scripts' ) );
	}
	
	/**
	 * Add domain field to checkout form
	 */
	public function add_domain_checkout_field() {
		if ( ! wu_get_setting( 'opensrs_enabled', false ) ) {
			return;
		}
		
		// Check if current product supports domains
		$product_id = isset( $_GET['product'] ) ? absint( $_GET['product'] ) : 0;
		if ( ! $product_id ) {
			return;
		}
		
		$domain_enabled = get_post_meta( $product_id, 'wu_opensrs_domain_enabled', true );
		if ( '1' !== $domain_enabled ) {
			return;
		}
		
		$allowed_tlds = get_post_meta( $product_id, 'wu_opensrs_allowed_tlds', true );
		$domain_included = get_post_meta( $product_id, 'wu_opensrs_domain_included', true );
		
		?>
		<div class="wu-widget wu-mb-4" id="wu-domain-widget">
			<div class="wu-widget-content">
				<h3 class="wu-widget-title">
					<?php esc_html_e( 'Domain Registration', 'wp-ultimo' ); ?>
					<?php if ( '1' === $domain_included ) : ?>
						<span class="wu-badge wu-bg-green-500 wu-text-white wu-text-xs wu-ml-2">
							<?php esc_html_e( 'Included', 'wp-ultimo' ); ?>
						</span>
					<?php endif; ?>
				</h3>
				
				<div class="wu-p-4">
					<label class="wu-block wu-mb-2">
						<input type="checkbox" 
							id="wu-register-domain" 
							name="register_domain" 
							value="1"
							class="wu-mr-2">
						<span><?php esc_html_e( 'Register a new domain', 'wp-ultimo' ); ?></span>
					</label>
					
					<div id="wu-domain-search-wrapper" style="display:none;">
						<div class="wu-flex wu-gap-2 wu-mb-2">
							<input type="text" 
								id="wu-domain-search" 
								name="domain_name" 
								placeholder="<?php esc_attr_e( 'Enter domain name', 'wp-ultimo' ); ?>"
								class="wu-flex-1 wu-p-2 wu-border wu-rounded">
							
							<select id="wu-domain-tld" 
								name="domain_tld" 
								class="wu-p-2 wu-border wu-rounded">
								<?php
								$tlds = ! empty( $allowed_tlds ) ? explode( ',', $allowed_tlds ) : array( 'com', 'net', 'org', 'io', 'co' );
								foreach ( $tlds as $tld ) {
									$tld = trim( $tld );
									echo '<option value="' . esc_attr( $tld ) . '">.' . esc_html( $tld ) . '</option>';
								}
								?>
							</select>
							
							<button type="button" 
								id="wu-check-domain" 
								class="wu-button wu-button-primary">
								<?php esc_html_e( 'Check', 'wp-ultimo' ); ?>
							</button>
						</div>
						
						<div id="wu-domain-result" class="wu-mt-2"></div>
						
						<div id="wu-domain-pricing" class="wu-mt-2" style="display:none;">
							<p class="wu-text-sm wu-text-gray-600">
								<?php if ( '1' !== $domain_included ) : ?>
									<?php esc_html_e( 'Domain price:', 'wp-ultimo' ); ?>
									<span id="wu-domain-price" class="wu-font-semibold"></span>
								<?php else : ?>
									<?php esc_html_e( 'This domain is included with your plan at no additional cost.', 'wp-ultimo' ); ?>
								<?php endif; ?>
							</p>
						</div>
						
						<input type="hidden" id="wu-domain-available" name="domain_available" value="0">
						<input type="hidden" id="wu-domain-full" name="domain_full" value="">
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Enqueue checkout scripts
	 */
	public function enqueue_checkout_scripts() {
		if ( ! is_page( wu_get_setting( 'checkout_page' ) ) ) {
			return;
		}
		
		wp_enqueue_script(
			'wu-opensrs-checkout',
			plugins_url( 'assets/js/opensrs-checkout.js', __FILE__ ),
			array( 'jquery' ),
			'1.0.0',
			true
		);
		
		wp_localize_script( 'wu-opensrs-checkout', 'wu_opensrs', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wu-opensrs-domain-check' ),
			'checking' => __( 'Checking availability...', 'wp-ultimo' ),
			'available' => __( 'Domain is available!', 'wp-ultimo' ),
			'unavailable' => __( 'Domain is not available', 'wp-ultimo' ),
			'error' => __( 'Error checking domain', 'wp-ultimo' ),
		) );
	}
	
	/**
	 * AJAX handler for domain availability check
	 */
	public function ajax_check_domain_availability() {
		check_ajax_referer( 'wu-opensrs-domain-check', 'nonce' );
		
		$domain_name = isset( $_POST['domain'] ) ? sanitize_text_field( $_POST['domain'] ) : '';
		$tld = isset( $_POST['tld'] ) ? sanitize_text_field( $_POST['tld'] ) : 'com';
		
		if ( empty( $domain_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a domain name', 'wp-ultimo' ) ) );
		}
		
		// Clean domain name
		$domain_name = preg_replace( '/[^a-z0-9-]/i', '', $domain_name );
		$full_domain = $domain_name . '.' . $tld;
		
		// Check availability via OpenSRS API
		$result = OpenSRS_API::lookup_domain( $full_domain );
		
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		
		$is_available = isset( $result['attributes']['status'] ) && 'available' === $result['attributes']['status'];
		
		if ( $is_available ) {
			// Get pricing
			$price = OpenSRS_Pricing::get_price( $tld, 'registration' );
			
			wp_send_json_success( array(
				'available' => true,
				'domain' => $full_domain,
				'price' => $price,
				'formatted_price' => wu_format_currency( $price ),
			) );
		} else {
			wp_send_json_success( array(
				'available' => false,
				'domain' => $full_domain,
			) );
		}
	}
	
	/**
	 * Add domain cost to cart
	 *
	 * @param array  $line_items Cart line items
	 * @param object $cart Cart object
	 * @return array
	 */
	public function add_domain_to_cart( $line_items, $cart ) {
		$register_domain = isset( $_POST['register_domain'] ) && '1' === $_POST['register_domain'];
		$domain_available = isset( $_POST['domain_available'] ) && '1' === $_POST['domain_available'];
		
		if ( ! $register_domain || ! $domain_available ) {
			return $line_items;
		}
		
		$product = $cart->get_product();
		$domain_included = get_post_meta( $product->get_id(), 'wu_opensrs_domain_included', true );
		
		// If domain is included, don't add separate line item
		if ( '1' === $domain_included ) {
			return $line_items;
		}
		
		$domain_full = isset( $_POST['domain_full'] ) ? sanitize_text_field( $_POST['domain_full'] ) : '';
		$tld = substr( strrchr( $domain_full, '.' ), 1 );
		$price = OpenSRS_Pricing::get_price( $tld, 'registration' );
		
		if ( $price ) {
			$line_items[] = array(
				'id' => 'domain_registration',
				'name' => sprintf( __( 'Domain Registration: %s', 'wp-ultimo' ), $domain_full ),
				'price' => $price,
				'quantity' => 1,
				'total' => $price,
				'type' => 'domain',
			);
		}
		
		return $line_items;
	}
	
	/**
	 * Process domain registration after checkout
	 *
	 * @param object $membership Membership object
	 * @param array  $checkout_data Checkout data
	 */
	public function process_domain_registration( $membership, $checkout_data ) {
		$register_domain = isset( $checkout_data['register_domain'] ) && '1' === $checkout_data['register_domain'];
		$domain_available = isset( $checkout_data['domain_available'] ) && '1' === $checkout_data['domain_available'];
		
		if ( ! $register_domain || ! $domain_available ) {
			return;
		}
		
		$domain_full = isset( $checkout_data['domain_full'] ) ? sanitize_text_field( $checkout_data['domain_full'] ) : '';
		
		if ( empty( $domain_full ) ) {
			return;
		}
		
		// Get customer information
		$customer = $membership->get_customer();
		
		// Prepare registration data
		$registration_data = array(
			'domain' => $domain_full,
			'period' => 1,
			'username' => $customer->get_username(),
			'password' => wp_generate_password( 16, true, true ),
			'contact' => array(
				'first_name' => $customer->get_first_name(),
				'last_name' => $customer->get_last_name(),
				'email' => $customer->get_email_address(),
				'phone' => $customer->get_phone_number(),
				'address1' => $customer->get_billing_address_line_1(),
				'city' => $customer->get_billing_city(),
				'state' => $customer->get_billing_state(),
				'postal_code' => $customer->get_billing_zip_code(),
				'country' => $customer->get_billing_country(),
			),
		);
		
		// Register domain via OpenSRS
		$result = OpenSRS_API::register_domain( $registration_data );
		
		if ( is_wp_error( $result ) ) {
			error_log( 'OpenSRS Domain Registration Failed: ' . $result->get_error_message() );
			return;
		}
		
		if ( 1 === $result['is_success'] ) {
			// Store domain in database
			global $wpdb;
			$table = $wpdb->prefix . 'wu_opensrs_domains';
			
			$wpdb->insert(
				$table,
				array(
					'domain_id' => $result['attributes']['id'],
					'domain_name' => $domain_full,
					'customer_id' => $customer->get_id(),
					'site_id' => $membership->get_site_id(),
					'product_id' => $membership->get_product_id(),
					'registration_date' => current_time( 'mysql' ),
					'expiration_date' => $result['attributes']['expiry_date'],
					'auto_renew' => 0,
					'status' => 'active',
					'domain_lock' => 1,
					'created_at' => current_time( 'mysql' ),
				),
				array( '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%s', '%d', '%s' )
			);
			
			// Map domain to site
			$this->map_domain_to_site( $domain_full, $membership->get_site_id() );
			
			// Send confirmation email
			$this->send_registration_confirmation( $customer, $domain_full );
		}
	}
	
	/**
	 * Map domain to site using existing domain mapping
	 *
	 * @param string $domain Domain name
	 * @param int    $site_id Site ID
	 */
	private function map_domain_to_site( $domain, $site_id ) {
		// Use Ultimate Multisite's existing domain mapping functionality
		do_action( 'wu_map_domain', $domain, $site_id );
	}
	
	/**
	 * Send registration confirmation email
	 *
	 * @param object $customer Customer object
	 * @param string $domain Domain name
	 */
	private function send_registration_confirmation( $customer, $domain ) {
		wu_send_mail(
			$customer->get_email_address(),
			__( 'Domain Registration Successful', 'wp-ultimo' ),
			sprintf(
				__( 'Congratulations! Your domain %s has been successfully registered.', 'wp-ultimo' ),
				$domain
			)
		);
	}
}

// Initialize
new OpenSRS_Checkout_Integration();
