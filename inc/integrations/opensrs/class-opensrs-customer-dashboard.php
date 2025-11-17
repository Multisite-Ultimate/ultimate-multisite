<?php
/**
 * OpenSRS Integration for Ultimate Multisite
 * 
 * Phase 2: Product Integration, Checkout Forms, and Customer Dashboard
 * 
 * @package UltimateMultisite
 * @subpackage OpenSRS
 * @since 2.5.0
 */

/**
 * OpenSRS Customer Dashboard Integration
 */
class OpenSRS_Customer_Dashboard {
	
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
		// Add domains tab to customer dashboard
		add_filter( 'wu_account_page_tabs', array( $this, 'add_domains_tab' ) );
		
		// Render domains page
		add_action( 'wu_account_page_domains', array( $this, 'render_domains_page' ) );
		
		// Handle domain management actions
		add_action( 'init', array( $this, 'handle_domain_actions' ) );
		
		// AJAX handlers for domain management
		add_action( 'wp_ajax_wu_update_nameservers', array( $this, 'ajax_update_nameservers' ) );
		add_action( 'wp_ajax_wu_toggle_whois_privacy', array( $this, 'ajax_toggle_whois_privacy' ) );
		add_action( 'wp_ajax_wu_toggle_domain_lock', array( $this, 'ajax_toggle_domain_lock' ) );
		add_action( 'wp_ajax_wu_renew_domain', array( $this, 'ajax_renew_domain' ) );
	}
	
	/**
	 * Add domains tab to customer dashboard
	 *
	 * @param array $tabs Existing tabs
	 * @return array
	 */
	public function add_domains_tab( $tabs ) {
		if ( ! wu_get_setting( 'opensrs_enabled', false ) ) {
			return $tabs;
		}
		
		$tabs['domains'] = array(
			'title' => __( 'My Domains', 'wp-ultimo' ),
			'icon' => 'dashicons-admin-site-alt3',
		);
		
		return $tabs;
	}
	
	/**
	 * Render domains page
	 */
	public function render_domains_page() {
		$customer = wu_get_current_customer();
		
		if ( ! $customer ) {
			return;
		}
		
		// Get customer's domains
		global $wpdb;
		$table = $wpdb->prefix . 'wu_opensrs_domains';
		
		$domains = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table WHERE customer_id = %d ORDER BY created_at DESC",
			$customer->get_id()
		) );
		
		?>
		<div class="wu-domains-manager">
			<h2><?php esc_html_e( 'My Domains', 'wp-ultimo' ); ?></h2>
			
			<?php if ( empty( $domains ) ) : ?>
				<div class="wu-empty-state wu-p-8 wu-text-center">
					<p><?php esc_html_e( 'You don\'t have any domains yet.', 'wp-ultimo' ); ?></p>
				</div>
			<?php else : ?>
				<div class="wu-domains-list">
					<?php foreach ( $domains as $domain ) : ?>
						<div class="wu-domain-card wu-mb-4 wu-p-4 wu-border wu-rounded" data-domain-id="<?php echo esc_attr( $domain->id ); ?>">
							<div class="wu-flex wu-justify-between wu-items-start">
								<div class="wu-flex-1">
									<h3 class="wu-text-lg wu-font-semibold wu-mb-2">
										<?php echo esc_html( $domain->domain_name ); ?>
										<?php if ( 'active' === $domain->status ) : ?>
											<span class="wu-badge wu-bg-green-500 wu-text-white wu-text-xs wu-ml-2">
												<?php esc_html_e( 'Active', 'wp-ultimo' ); ?>
											</span>
										<?php endif; ?>
									</h3>
									
									<div class="wu-text-sm wu-text-gray-600 wu-space-y-1">
										<p>
											<strong><?php esc_html_e( 'Registered:', 'wp-ultimo' ); ?></strong>
											<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $domain->registration_date ) ) ); ?>
										</p>
										<p>
											<strong><?php esc_html_e( 'Expires:', 'wp-ultimo' ); ?></strong>
											<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $domain->expiration_date ) ) ); ?>
										</p>
										<p>
											<strong><?php esc_html_e( 'Auto-Renew:', 'wp-ultimo' ); ?></strong>
											<?php echo $domain->auto_renew ? esc_html__( 'Enabled', 'wp-ultimo' ) : esc_html__( 'Disabled', 'wp-ultimo' ); ?>
										</p>
									</div>
								</div>
								
								<div>
									<button class="wu-button wu-button-sm" 
										onclick="toggleDomainDetails(<?php echo esc_js( $domain->id ); ?>)">
										<?php esc_html_e( 'Manage', 'wp-ultimo' ); ?>
									</button>
								</div>
							</div>
							
							<!-- Domain Management Details (Hidden by default) -->
							<div id="domain-details-<?php echo esc_attr( $domain->id ); ?>" 
								class="wu-domain-details wu-mt-4 wu-pt-4 wu-border-t" 
								style="display:none;">
								
								<!-- Nameservers -->
								<div class="wu-mb-4">
									<h4 class="wu-font-semibold wu-mb-2"><?php esc_html_e( 'Nameservers', 'wp-ultimo' ); ?></h4>
									<form class="wu-nameservers-form" data-domain-id="<?php echo esc_attr( $domain->id ); ?>">
										<?php
										$nameservers = json_decode( $domain->nameservers, true ) ?: array( '', '', '', '' );
										for ( $i = 1; $i <= 4; $i++ ) :
										?>
											<input type="text" 
												name="nameserver<?php echo $i; ?>" 
												value="<?php echo esc_attr( $nameservers[ $i - 1 ] ?? '' ); ?>"
												placeholder="ns<?php echo $i; ?>.example.com"
												class="wu-w-full wu-p-2 wu-border wu-rounded wu-mb-2">
										<?php endfor; ?>
										<button type="submit" class="wu-button wu-button-primary wu-button-sm">
											<?php esc_html_e( 'Update Nameservers', 'wp-ultimo' ); ?>
										</button>
									</form>
								</div>
								
								<!-- WHOIS Privacy -->
								<div class="wu-mb-4">
									<h4 class="wu-font-semibold wu-mb-2"><?php esc_html_e( 'WHOIS Privacy', 'wp-ultimo' ); ?></h4>
									<label class="wu-flex wu-items-center">
										<input type="checkbox" 
											class="wu-toggle-whois-privacy wu-mr-2"
											data-domain-id="<?php echo esc_attr( $domain->id ); ?>"
											<?php checked( $domain->whois_privacy, 1 ); ?>>
										<span><?php esc_html_e( 'Enable WHOIS Privacy Protection', 'wp-ultimo' ); ?></span>
									</label>
								</div>
								
								<!-- Domain Lock -->
								<div class="wu-mb-4">
									<h4 class="wu-font-semibold wu-mb-2"><?php esc_html_e( 'Domain Lock', 'wp-ultimo' ); ?></h4>
									<label class="wu-flex wu-items-center">
										<input type="checkbox" 
											class="wu-toggle-domain-lock wu-mr-2"
											data-domain-id="<?php echo esc_attr( $domain->id ); ?>"
											<?php checked( $domain->domain_lock, 1 ); ?>>
										<span><?php esc_html_e( 'Lock domain to prevent unauthorized transfers', 'wp-ultimo' ); ?></span>
									</label>
								</div>
								
								<!-- Renewal -->
								<div class="wu-mb-4">
									<h4 class="wu-font-semibold wu-mb-2"><?php esc_html_e( 'Renewal', 'wp-ultimo' ); ?></h4>
									<?php
									$days_until_expiry = floor( ( strtotime( $domain->expiration_date ) - time() ) / DAY_IN_SECONDS );
									?>
									<p class="wu-text-sm wu-mb-2">
										<?php
										printf(
											esc_html__( 'Your domain expires in %d days', 'wp-ultimo' ),
											$days_until_expiry
										);
										?>
									</p>
									<button class="wu-button wu-button-primary wu-button-sm wu-renew-domain"
										data-domain-id="<?php echo esc_attr( $domain->id ); ?>">
										<?php esc_html_e( 'Renew Now', 'wp-ultimo' ); ?>
									</button>
								</div>
								
								<!-- Contact Information -->
								<div class="wu-mb-4">
									<h4 class="wu-font-semibold wu-mb-2"><?php esc_html_e( 'Contact Information', 'wp-ultimo' ); ?></h4>
									<button class="wu-button wu-button-sm"
										onclick="openContactEditor(<?php echo esc_js( $domain->id ); ?>)">
										<?php esc_html_e( 'Update Contact Info', 'wp-ultimo' ); ?>
									</button>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		
		<script>
		function toggleDomainDetails(domainId) {
			var details = document.getElementById('domain-details-' + domainId);
			if (details.style.display === 'none') {
				details.style.display = 'block';
			} else {
				details.style.display = 'none';
			}
		}
		
		function openContactEditor(domainId) {
			// This would open a modal or redirect to contact edit page
			alert('Contact editor for domain ' + domainId);
		}
		
		jQuery(document).ready(function($) {
			// Handle nameserver updates
			$('.wu-nameservers-form').on('submit', function(e) {
				e.preventDefault();
				var form = $(this);
				var domainId = form.data('domain-id');
				var nameservers = [];
				
				form.find('input[type="text"]').each(function() {
					if ($(this).val()) {
						nameservers.push($(this).val());
					}
				});
				
				$.post(ajaxurl, {
					action: 'wu_update_nameservers',
					domain_id: domainId,
					nameservers: nameservers,
					nonce: '<?php echo wp_create_nonce( "wu-domain-management" ); ?>'
				}, function(response) {
					if (response.success) {
						alert('<?php esc_html_e( "Nameservers updated successfully", "wp-ultimo" ); ?>');
					} else {
						alert('<?php esc_html_e( "Error updating nameservers", "wp-ultimo" ); ?>');
					}
				});
			});
			
			// Handle WHOIS privacy toggle
			$('.wu-toggle-whois-privacy').on('change', function() {
				var checkbox = $(this);
				var domainId = checkbox.data('domain-id');
				var enabled = checkbox.is(':checked');
				
				$.post(ajaxurl, {
					action: 'wu_toggle_whois_privacy',
					domain_id: domainId,
					enabled: enabled ? 1 : 0,
					nonce: '<?php echo wp_create_nonce( "wu-domain-management" ); ?>'
				}, function(response) {
					if (!response.success) {
						checkbox.prop('checked', !enabled);
						alert('<?php esc_html_e( "Error updating WHOIS privacy", "wp-ultimo" ); ?>');
					}
				});
			});
			
			// Handle domain lock toggle
			$('.wu-toggle-domain-lock').on('change', function() {
				var checkbox = $(this);
				var domainId = checkbox.data('domain-id');
				var locked = checkbox.is(':checked');
				
				$.post(ajaxurl, {
					action: 'wu_toggle_domain_lock',
					domain_id: domainId,
					locked: locked ? 1 : 0,
					nonce: '<?php echo wp_create_nonce( "wu-domain-management" ); ?>'
				}, function(response) {
					if (!response.success) {
						checkbox.prop('checked', !locked);
						alert('<?php esc_html_e( "Error updating domain lock", "wp-ultimo" ); ?>');
					}
				});
			});
			
			// Handle domain renewal
			$('.wu-renew-domain').on('click', function() {
				var button = $(this);
				var domainId = button.data('domain-id');
				
				if (!confirm('<?php esc_html_e( "Are you sure you want to renew this domain?", "wp-ultimo" ); ?>')) {
					return;
				}
				
				button.prop('disabled', true).text('<?php esc_html_e( "Processing...", "wp-ultimo" ); ?>');
				
				$.post(ajaxurl, {
					action: 'wu_renew_domain',
					domain_id: domainId,
					nonce: '<?php echo wp_create_nonce( "wu-domain-management" ); ?>'
				}, function(response) {
					if (response.success) {
						alert('<?php esc_html_e( "Domain renewed successfully", "wp-ultimo" ); ?>');
						location.reload();
					} else {
						alert('<?php esc_html_e( "Error renewing domain", "wp-ultimo" ); ?>');
						button.prop('disabled', false).text('<?php esc_html_e( "Renew Now", "wp-ultimo" ); ?>');
					}
				});
			});
		});
		</script>
		<?php
	}
	
	/**
	 * Handle domain actions
	 */
	public function handle_domain_actions() {
		// Additional domain action handlers can be added here
	}
	
	/**
	 * AJAX: Update nameservers
	 */
	public function ajax_update_nameservers() {
		check_ajax_referer( 'wu-domain-management', 'nonce' );
		
		$domain_id = isset( $_POST['domain_id'] ) ? absint( $_POST['domain_id'] ) : 0;
		$nameservers = isset( $_POST['nameservers'] ) ? array_map( 'sanitize_text_field', $_POST['nameservers'] ) : array();
		
		if ( ! $domain_id || empty( $nameservers ) ) {
			wp_send_json_error();
		}
		
		// Get domain from database
		global $wpdb;
		$table = $wpdb->prefix . 'wu_opensrs_domains';
		$domain = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $domain_id ) );
		
		if ( ! $domain ) {
			wp_send_json_error();
		}
		
		// Verify ownership
		$customer = wu_get_current_customer();
		if ( ! $customer || $customer->get_id() !== (int) $domain->customer_id ) {
			wp_send_json_error();
		}
		
		// Update via API
		$result = OpenSRS_API::update_nameservers( $domain->domain_name, $nameservers );
		
		if ( is_wp_error( $result ) || 1 !== $result['is_success'] ) {
			wp_send_json_error();
		}
		
		// Update database
		$wpdb->update(
			$table,
			array(
				'nameservers' => wp_json_encode( $nameservers ),
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $domain_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
		
		wp_send_json_success();
	}
	
	/**
	 * AJAX: Toggle WHOIS privacy
	 */
	public function ajax_toggle_whois_privacy() {
		check_ajax_referer( 'wu-domain-management', 'nonce' );
		
		$domain_id = isset( $_POST['domain_id'] ) ? absint( $_POST['domain_id'] ) : 0;
		$enabled = isset( $_POST['enabled'] ) && '1' === $_POST['enabled'];
		
		if ( ! $domain_id ) {
			wp_send_json_error();
		}
		
		// Get domain from database
		global $wpdb;
		$table = $wpdb->prefix . 'wu_opensrs_domains';
		$domain = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $domain_id ) );
		
		if ( ! $domain ) {
			wp_send_json_error();
		}
		
		// Verify ownership
		$customer = wu_get_current_customer();
		if ( ! $customer || $customer->get_id() !== (int) $domain->customer_id ) {
			wp_send_json_error();
		}
		
		// Update via API
		$result = OpenSRS_API::toggle_whois_privacy( $domain->domain_name, $enabled );
		
		if ( is_wp_error( $result ) || 1 !== $result['is_success'] ) {
			wp_send_json_error();
		}
		
		// Update database
		$wpdb->update(
			$table,
			array(
				'whois_privacy' => $enabled ? 1 : 0,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $domain_id ),
			array( '%d', '%s' ),
			array( '%d' )
		);
		
		wp_send_json_success();
	}
	
	/**
	 * AJAX: Toggle domain lock
	 */
	public function ajax_toggle_domain_lock() {
		check_ajax_referer( 'wu-domain-management', 'nonce' );
		
		$domain_id = isset( $_POST['domain_id'] ) ? absint( $_POST['domain_id'] ) : 0;
		$locked = isset( $_POST['locked'] ) && '1' === $_POST['locked'];
		
		if ( ! $domain_id ) {
			wp_send_json_error();
		}
		
		// Get domain from database
		global $wpdb;
		$table = $wpdb->prefix . 'wu_opensrs_domains';
		$domain = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $domain_id ) );
		
		if ( ! $domain ) {
			wp_send_json_error();
		}
		
		// Verify ownership
		$customer = wu_get_current_customer();
		if ( ! $customer || $customer->get_id() !== (int) $domain->customer_id ) {
			wp_send_json_error();
		}
		
		// Update via API
		$result = OpenSRS_API::toggle_domain_lock( $domain->domain_name, $locked );
		
		if ( is_wp_error( $result ) || 1 !== $result['is_success'] ) {
			wp_send_json_error();
		}
		
		// Update database
		$wpdb->update(
			$table,
			array(
				'domain_lock' => $locked ? 1 : 0,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $domain_id ),
			array( '%d', '%s' ),
			array( '%d' )
		);
		
		wp_send_json_success();
	}
	
	/**
	 * AJAX: Renew domain
	 */
	public function ajax_renew_domain() {
		check_ajax_referer( 'wu-domain-management', 'nonce' );
		
		$domain_id = isset( $_POST['domain_id'] ) ? absint( $_POST['domain_id'] ) : 0;
		
		if ( ! $domain_id ) {
			wp_send_json_error();
		}
		
		// Get domain from database
		global $wpdb;
		$table = $wpdb->prefix . 'wu_opensrs_domains';
		$domain = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $domain_id ) );
		
		if ( ! $domain ) {
			wp_send_json_error();
		}
		
		// Verify ownership
		$customer = wu_get_current_customer();
		if ( ! $customer || $customer->get_id() !== (int) $domain->customer_id ) {
			wp_send_json_error();
		}
		
		// Get pricing for renewal
		$tld = substr( strrchr( $domain->domain_name, '.' ), 1 );
		$renewal_price = OpenSRS_Pricing::get_price( $tld, 'renewal' );
		
		// TODO: Process payment through Ultimate Multisite payment gateways
		// This is a simplified version - you'll need to integrate with existing payment system
		
		// Renew via API
		$result = OpenSRS_API::renew_domain( $domain->domain_name, 1 );
		
		if ( is_wp_error( $result ) || 1 !== $result['is_success'] ) {
			wp_send_json_error();
		}
		
		// Update database with new expiration date
		$new_expiration = date( 'Y-m-d H:i:s', strtotime( $domain->expiration_date . ' +1 year' ) );
		
		$wpdb->update(
			$table,
			array(
				'expiration_date' => $new_expiration,
				'renewal_date' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $domain_id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);
		
		wp_send_json_success();
	}
}

// Initialize
new OpenSRS_Customer_Dashboard();
