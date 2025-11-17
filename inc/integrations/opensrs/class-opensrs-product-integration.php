<?php
/**
 * OpenSRS Product Integration for Ultimate Multisite
 * 
 * File: includes/integrations/opensrs/class-opensrs-product-integration.php
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
 * OpenSRS Product Integration Class
 */
class OpenSRS_Product_Integration {
	
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
		// Add domain service type to products
		add_filter( 'wu_product_types', array( $this, 'register_domain_product_type' ) );
		
		// Add domain settings to product edit page
		add_action( 'wu_product_options_after_billing', array( $this, 'render_domain_product_settings' ) );
		
		// Save domain product settings
		add_action( 'wu_save_product', array( $this, 'save_domain_product_settings' ) );
		
		// Add domain pricing to product
		add_filter( 'wu_product_price_description', array( $this, 'add_domain_price_description' ), 10, 2 );
	}
	
	/**
	 * Register domain as a product type
	 *
	 * @param array $types Existing product types
	 * @return array
	 */
	public function register_domain_product_type( $types ) {
		$types['domain'] = array(
			'label' => __( 'Domain Registration', 'wp-ultimo' ),
			'description' => __( 'OpenSRS domain registration service', 'wp-ultimo' ),
			'icon' => 'dashicons-admin-site-alt3',
		);
		
		return $types;
	}
	
	/**
	 * Render domain product settings
	 *
	 * @param object $product Product object
	 */
	public function render_domain_product_settings( $product ) {
		if ( ! wu_get_setting( 'opensrs_enabled', false ) ) {
			return;
		}
		
		$domain_enabled = get_post_meta( $product->get_id(), 'wu_opensrs_domain_enabled', true );
		$domain_included = get_post_meta( $product->get_id(), 'wu_opensrs_domain_included', true );
		$domain_as_addon = get_post_meta( $product->get_id(), 'wu_opensrs_domain_as_addon', true );
		$allowed_tlds = get_post_meta( $product->get_id(), 'wu_opensrs_allowed_tlds', true );
		
		?>
		<div class="wu-styling">
			<h3><?php esc_html_e( 'Domain Settings', 'wp-ultimo' ); ?></h3>
			
			<div class="wu-p-4 wu-bg-gray-100 wu-rounded">
				<!-- Enable Domain Service -->
				<div class="wu-mb-4">
					<label class="wu-flex wu-items-center">
						<input type="checkbox" 
							name="wu_opensrs_domain_enabled" 
							value="1" 
							<?php checked( $domain_enabled, '1' ); ?>
							class="wu-mr-2">
						<span class="wu-font-semibold">
							<?php esc_html_e( 'Enable Domain Registration', 'wp-ultimo' ); ?>
						</span>
					</label>
					<p class="wu-text-sm wu-text-gray-600 wu-mt-1">
						<?php esc_html_e( 'Allow customers to register domains with this plan', 'wp-ultimo' ); ?>
					</p>
				</div>
				
				<!-- Domain Pricing Model -->
				<div class="wu-mb-4" id="domain-pricing-options">
					<label class="wu-block wu-font-semibold wu-mb-2">
						<?php esc_html_e( 'Domain Pricing Model', 'wp-ultimo' ); ?>
					</label>
					
					<label class="wu-flex wu-items-center wu-mb-2">
						<input type="radio" 
							name="wu_opensrs_domain_pricing" 
							value="included" 
							<?php checked( $domain_included, '1' ); ?>
							class="wu-mr-2">
						<span><?php esc_html_e( 'Included in Plan Price', 'wp-ultimo' ); ?></span>
					</label>
					
					<label class="wu-flex wu-items-center">
						<input type="radio" 
							name="wu_opensrs_domain_pricing" 
							value="addon" 
							<?php checked( $domain_as_addon, '1' ); ?>
							class="wu-mr-2">
						<span><?php esc_html_e( 'Charge as Separate Add-on', 'wp-ultimo' ); ?></span>
					</label>
					
					<p class="wu-text-sm wu-text-gray-600 wu-mt-1">
						<?php esc_html_e( 'Choose whether domain cost is included in the plan or charged separately', 'wp-ultimo' ); ?>
					</p>
				</div>
				
				<!-- Allowed TLDs -->
				<div class="wu-mb-4">
					<label class="wu-block wu-font-semibold wu-mb-2">
						<?php esc_html_e( 'Allowed TLDs', 'wp-ultimo' ); ?>
					</label>
					<input type="text" 
						name="wu_opensrs_allowed_tlds" 
						value="<?php echo esc_attr( $allowed_tlds ); ?>"
						placeholder="com,net,org,io"
						class="wu-w-full wu-p-2 wu-border wu-rounded">
					<p class="wu-text-sm wu-text-gray-600 wu-mt-1">
						<?php esc_html_e( 'Comma-separated list of allowed TLDs. Leave empty to allow all.', 'wp-ultimo' ); ?>
					</p>
				</div>
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			$('input[name="wu_opensrs_domain_enabled"]').on('change', function() {
				$('#domain-pricing-options').toggle(this.checked);
			}).trigger('change');
		});
		</script>
		<?php
	}
	
	/**
	 * Save domain product settings
	 *
	 * @param int $product_id Product ID
	 */
	public function save_domain_product_settings( $product_id ) {
		if ( ! current_user_can( 'manage_network' ) ) {
			return;
		}
		
		$domain_enabled = isset( $_POST['wu_opensrs_domain_enabled'] ) ? '1' : '0';
		update_post_meta( $product_id, 'wu_opensrs_domain_enabled', $domain_enabled );
		
		if ( isset( $_POST['wu_opensrs_domain_pricing'] ) ) {
			$pricing_model = sanitize_text_field( $_POST['wu_opensrs_domain_pricing'] );
			update_post_meta( $product_id, 'wu_opensrs_domain_included', $pricing_model === 'included' ? '1' : '0' );
			update_post_meta( $product_id, 'wu_opensrs_domain_as_addon', $pricing_model === 'addon' ? '1' : '0' );
		}
		
		if ( isset( $_POST['wu_opensrs_allowed_tlds'] ) ) {
			$allowed_tlds = sanitize_text_field( $_POST['wu_opensrs_allowed_tlds'] );
			update_post_meta( $product_id, 'wu_opensrs_allowed_tlds', $allowed_tlds );
		}
	}
	
	/**
	 * Add domain price to product description
	 *
	 * @param string $description Price description
	 * @param object $product Product object
	 * @return string
	 */
	public function add_domain_price_description( $description, $product ) {
		$domain_enabled = get_post_meta( $product->get_id(), 'wu_opensrs_domain_enabled', true );
		$domain_included = get_post_meta( $product->get_id(), 'wu_opensrs_domain_included', true );
		
		if ( '1' === $domain_enabled && '1' === $domain_included ) {
			$description .= '<br><small>' . __( '+ Free domain registration included', 'wp-ultimo' ) . '</small>';
		}
		
		return $description;
	}
}

// Initialize
new OpenSRS_Product_Integration();
