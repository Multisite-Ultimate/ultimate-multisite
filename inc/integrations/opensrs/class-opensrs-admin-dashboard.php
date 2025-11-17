<?php
/**
 * OpenSRS Integration for Ultimate Multisite
 * 
 * File: Admin Dashboard and Domain Management
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
 * OpenSRS Admin Dashboard Class
 */
class OpenSRS_Admin_Dashboard {
	
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
		// Add domains menu item
		add_action( 'wu_admin_pages', array( $this, 'register_admin_page' ) );
		
		// Add domains column to customers list
		add_filter( 'wu_customers_list_table_columns', array( $this, 'add_domains_column' ) );
		add_action( 'wu_customers_list_table_column_domains', array( $this, 'render_domains_column' ), 10, 2 );
		
		// Add bulk actions for domains
		add_filter( 'wu_domains_list_table_bulk_actions', array( $this, 'register_bulk_actions' ) );
		add_action( 'wu_domains_list_table_handle_bulk_action', array( $this, 'handle_bulk_actions' ), 10, 3 );
		
		// AJAX handlers for admin
		add_action( 'wp_ajax_wu_admin_delete_domain', array( $this, 'ajax_delete_domain' ) );
		add_action( 'wp_ajax_wu_admin_sync_domain', array( $this, 'ajax_sync_domain' ) );
		add_action( 'wp_ajax_wu_admin_search_domains', array( $this, 'ajax_search_domains' ) );
	}
	
	/**
	 * Register admin page for domain management
	 */
	public function register_admin_page() {
		wu_register_admin_page( 'wp-ultimo-domains', array(
			'title'       => __( 'Domains', 'wp-ultimo' ),
			'menu_title'  => __( 'Domains', 'wp-ultimo' ),
			'capability'  => 'manage_network',
			'icon'        => 'dashicons-admin-site-alt3',
			'position'    => 30,
			'screen_id'   => 'wp-ultimo_page_wp-ultimo-domains',
		) );
	}
	
	/**
	 * Add domains column to customers list
	 *
	 * @param array $columns Existing columns
	 * @return array
	 */
	public function add_domains_column( $columns ) {
		if ( ! wu_get_setting( 'opensrs_enabled', false ) ) {
			return $columns;
		}
		
		$columns['domains'] = __( 'Domains', 'wp-ultimo' );
		return $columns;
	}
	
	/**
	 * Render domains column content
	 *
	 * @param object $customer Customer object
	 * @param string $column_name Column name
	 */
	public function render_domains_column( $customer, $column_name ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wu_opensrs_domains';
		
		$domain_count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table WHERE customer_id = %d AND status = 'active'",
			$customer->get_id()
		) );
		
		if ( $domain_count > 0 ) {
			printf(
				'<a href="%s">%d %s</a>',
				esc_url( add_query_arg( array(
					'page' => 'wp-ultimo-domains',
					'customer_id' => $customer->get_id(),
				), network_admin_url( 'admin.php' ) ) ),
				(int) $domain_count,
				esc_html( _n( 'domain', 'domains', $domain_count, 'wp-ultimo' ) )
			);
		} else {
			echo '—';
		}
	}
	
	/**
	 * Register bulk actions
	 *
	 * @param array $actions Existing actions
	 * @return array
	 */
	public function register_bulk_actions( $actions ) {
		$actions['sync'] = __( 'Sync with OpenSRS', 'wp-ultimo' );
		$actions['delete_expired'] = __( 'Delete Expired Domains', 'wp-ultimo' );
		$actions['enable_auto_renew'] = __( 'Enable Auto-Renew', 'wp-ultimo' );
		$actions['disable_auto_renew'] = __( 'Disable Auto-Renew', 'wp-ultimo' );
		
		return $actions;
	}
	
	/**
	 * Handle bulk actions
	 *
	 * @param string $action Action name
	 * @param array  $items Selected items
	 * @param object $list_table List table object
	 */
	public function handle_bulk_actions( $action, $items, $list_table ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wu_opensrs_domains';
		
		switch ( $action ) {
			case 'sync':
				foreach ( $items as $domain_id ) {
					$this->sync_domain_with_opensrs( $domain_id );
				}
				wu_add_notice( __( 'Selected domains synced successfully.', 'wp-ultimo' ), 'success' );
				break;
				
			case 'delete_expired':
				$wpdb->query(
					"DELETE FROM $table WHERE status = 'expired' AND id IN (" . implode( ',', array_map( 'absint', $items ) ) . ")"
				);
				wu_add_notice( __( 'Expired domains deleted.', 'wp-ultimo' ), 'success' );
				break;
				
			case 'enable_auto_renew':
				$wpdb->query(
					"UPDATE $table SET auto_renew = 1 WHERE id IN (" . implode( ',', array_map( 'absint', $items ) ) . ")"
				);
				wu_add_notice( __( 'Auto-renew enabled for selected domains.', 'wp-ultimo' ), 'success' );
				break;
				
			case 'disable_auto_renew':
				$wpdb->query(
					"UPDATE $table SET auto_renew = 0 WHERE id IN (" . implode( ',', array_map( 'absint', $items ) ) . ")"
				);
				wu_add_notice( __( 'Auto-renew disabled for selected domains.', 'wp-ultimo' ), 'success' );
				break;
		}
	}
	
	/**
	 * Sync domain with OpenSRS
	 *
	 * @param int $domain_id Domain ID
	 * @return bool
	 */
	private function sync_domain_with_opensrs( $domain_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wu_opensrs_domains';
		
		$domain = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $table WHERE id = %d",
			$domain_id
		) );
		
		if ( ! $domain ) {
			return false;
		}
		
		// Get domain info from OpenSRS
		$info = OpenSRS_API::get_domain_info( $domain->domain_name );
		
		if ( is_wp_error( $info ) || 1 !== $info['is_success'] ) {
			return false;
		}
		
		// Update local database with OpenSRS data
		$update_data = array(
			'expiration_date' => $info['attributes']['expiry_date'],
			'status' => $info['attributes']['status'],
			'domain_lock' => isset( $info['attributes']['locked'] ) ? (int) $info['attributes']['locked'] : 0,
			'whois_privacy' => isset( $info['attributes']['whois_privacy'] ) ? (int) $info['attributes']['whois_privacy'] : 0,
			'last_updated' => current_time( 'mysql' ),
		);
		
		if ( isset( $info['attributes']['nameservers'] ) ) {
			$update_data['nameservers'] = wp_json_encode( $info['attributes']['nameservers'] );
		}
		
		$wpdb->update(
			$table,
			$update_data,
			array( 'id' => $domain_id ),
			array( '%s', '%s', '%d', '%d', '%s', '%s' ),
			array( '%d' )
		);
		
		return true;
	}
	
	/**
	 * AJAX: Delete domain
	 */
	public function ajax_delete_domain() {
		check_ajax_referer( 'wu-admin-domains', 'nonce' );
		
		if ( ! current_user_can( 'manage_network' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'wp-ultimo' ) ) );
		}
		
		$domain_id = isset( $_POST['domain_id'] ) ? absint( $_POST['domain_id'] ) : 0;
		
		if ( ! $domain_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid domain ID', 'wp-ultimo' ) ) );
		}
		
		global $wpdb;
		$table = $wpdb->prefix . 'wu_opensrs_domains';
		
		$deleted = $wpdb->delete( $table, array( 'id' => $domain_id ), array( '%d' ) );
		
		if ( $deleted ) {
			wp_send_json_success( array( 'message' => __( 'Domain deleted successfully', 'wp-ultimo' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to delete domain', 'wp-ultimo' ) ) );
		}
	}
	
	/**
	 * AJAX: Sync domain
	 */
	public function ajax_sync_domain() {
		check_ajax_referer( 'wu-admin-domains', 'nonce' );
		
		if ( ! current_user_can( 'manage_network' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'wp-ultimo' ) ) );
		}
		
		$domain_id = isset( $_POST['domain_id'] ) ? absint( $_POST['domain_id'] ) : 0;
		
		if ( ! $domain_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid domain ID', 'wp-ultimo' ) ) );
		}
		
		$synced = $this->sync_domain_with_opensrs( $domain_id );
		
		if ( $synced ) {
			wp_send_json_success( array( 'message' => __( 'Domain synced successfully', 'wp-ultimo' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to sync domain', 'wp-ultimo' ) ) );
		}
	}
	
	/**
	 * AJAX: Search domains
	 */
	public function ajax_search_domains() {
		check_ajax_referer( 'wu-admin-domains', 'nonce' );
		
		if ( ! current_user_can( 'manage_network' ) ) {
			wp_send_json_error();
		}
		
		$search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
		$status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';
		
		global $wpdb;
		$table = $wpdb->prefix . 'wu_opensrs_domains';
		
		$where = array( '1=1' );
		
		if ( ! empty( $search ) ) {
			$where[] = $wpdb->prepare( 'domain_name LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' );
		}
		
		if ( ! empty( $status ) ) {
			$where[] = $wpdb->prepare( 'status = %s', $status );
		}
		
		$query = "SELECT * FROM $table WHERE " . implode( ' AND ', $where ) . " ORDER BY created_at DESC LIMIT 50";
		$domains = $wpdb->get_results( $query );
		
		wp_send_json_success( array( 'domains' => $domains ) );
	}
}

/**
 * OpenSRS Domains List Table
 */

// Load WordPress List Table class if not already loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

	class OpenSRS_Domains_List_Table extends \WP_List_Table {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'domain',
			'plural'   => 'domains',
			'ajax'     => false,
		) );
	}
	
	/**
	 * Get columns
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'               => '<input type="checkbox" />',
			'domain_name'      => __( 'Domain Name', 'wp-ultimo' ),
			'customer'         => __( 'Customer', 'wp-ultimo' ),
			'status'           => __( 'Status', 'wp-ultimo' ),
			'registration_date' => __( 'Registered', 'wp-ultimo' ),
			'expiration_date'  => __( 'Expires', 'wp-ultimo' ),
			'auto_renew'       => __( 'Auto-Renew', 'wp-ultimo' ),
			'actions'          => __( 'Actions', 'wp-ultimo' ),
		);
	}
	
	/**
	 * Get sortable columns
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'domain_name'       => array( 'domain_name', false ),
			'registration_date' => array( 'registration_date', true ),
			'expiration_date'   => array( 'expiration_date', false ),
			'status'            => array( 'status', false ),
		);
	}
	
	/**
	 * Prepare items
	 */
	public function prepare_items() {
		global $wpdb;
		$table = $wpdb->prefix . 'wu_opensrs_domains';
		
		$per_page = 20;
		$current_page = $this->get_pagenum();
		$offset = ( $current_page - 1 ) * $per_page;
		
		// Handle search
		$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		
		// Handle status filter
		$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
		
		// Build query
		$where = array( '1=1' );
		
		if ( ! empty( $search ) ) {
			$where[] = $wpdb->prepare(
				'domain_name LIKE %s',
				'%' . $wpdb->esc_like( $search ) . '%'
			);
		}
		
		if ( ! empty( $status ) ) {
			$where[] = $wpdb->prepare( 'status = %s', $status );
		}
		
		// Handle customer filter
		if ( isset( $_GET['customer_id'] ) && ! empty( $_GET['customer_id'] ) ) {
			$where[] = $wpdb->prepare( 'customer_id = %d', absint( $_GET['customer_id'] ) );
		}
		
		$where_clause = implode( ' AND ', $where );
		
		// Get total items
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE $where_clause" );
		
		// Handle sorting
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'created_at';
		$order = isset( $_GET['order'] ) && 'asc' === $_GET['order'] ? 'ASC' : 'DESC';
		
		// Get items
		$this->items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table WHERE $where_clause ORDER BY $orderby $order LIMIT %d OFFSET %d",
				$per_page,
				$offset
			)
		);
		
		// Set pagination
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}
	
	/**
	 * Render checkbox column
	 *
	 * @param object $item Domain item
	 * @return string
	 */
	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="domain[]" value="%d" />', $item->id );
	}
	
	/**
	 * Render domain name column
	 *
	 * @param object $item Domain item
	 * @return string
	 */
	protected function column_domain_name( $item ) {
		$edit_url = add_query_arg( array(
			'page' => 'wp-ultimo-domains',
			'action' => 'edit',
			'id' => $item->id,
		), network_admin_url( 'admin.php' ) );
		
		return sprintf(
			'<strong><a href="%s">%s</a></strong>',
			esc_url( $edit_url ),
			esc_html( $item->domain_name )
		);
	}
	
	/**
	 * Render customer column
	 *
	 * @param object $item Domain item
	 * @return string
	 */
	protected function column_customer( $item ) {
		$customer = wu_get_customer( $item->customer_id );
		
		if ( ! $customer ) {
			return '—';
		}
		
		return sprintf(
			'<a href="%s">%s</a>',
			esc_url( get_edit_user_link( $customer->get_user_id() ) ),
			esc_html( $customer->get_display_name() )
		);
	}
	
	/**
	 * Render status column
	 *
	 * @param object $item Domain item
	 * @return string
	 */
	protected function column_status( $item ) {
		$status_labels = array(
			'active'  => '<span class="wu-badge wu-bg-green-500 wu-text-white">' . __( 'Active', 'wp-ultimo' ) . '</span>',
			'expired' => '<span class="wu-badge wu-bg-red-500 wu-text-white">' . __( 'Expired', 'wp-ultimo' ) . '</span>',
			'pending' => '<span class="wu-badge wu-bg-yellow-500 wu-text-white">' . __( 'Pending', 'wp-ultimo' ) . '</span>',
		);
		
		return isset( $status_labels[ $item->status ] ) ? $status_labels[ $item->status ] : $item->status;
	}
	
	/**
	 * Render registration date column
	 *
	 * @param object $item Domain item
	 * @return string
	 */
	protected function column_registration_date( $item ) {
		return date_i18n( get_option( 'date_format' ), strtotime( $item->registration_date ) );
	}
	
	/**
	 * Render expiration date column
	 *
	 * @param object $item Domain item
	 * @return string
	 */
	protected function column_expiration_date( $item ) {
		$expiration = strtotime( $item->expiration_date );
		$now = time();
		$days_until = floor( ( $expiration - $now ) / DAY_IN_SECONDS );
		
		$date_string = date_i18n( get_option( 'date_format' ), $expiration );
		
		if ( $days_until < 0 ) {
			return '<span class="wu-text-red-600">' . $date_string . '</span>';
		} elseif ( $days_until < 30 ) {
			return '<span class="wu-text-yellow-600">' . $date_string . '<br><small>(' . $days_until . ' days)</small></span>';
		}
		
		return $date_string;
	}
	
	/**
	 * Render auto-renew column
	 *
	 * @param object $item Domain item
	 * @return string
	 */
	protected function column_auto_renew( $item ) {
		return $item->auto_renew ? __( 'Yes', 'wp-ultimo' ) : __( 'No', 'wp-ultimo' );
	}
	
	/**
	 * Render actions column
	 *
	 * @param object $item Domain item
	 * @return string
	 */
	protected function column_actions( $item ) {
		$actions = array();
		
		$actions[] = sprintf(
			'<a href="#" class="wu-sync-domain" data-domain-id="%d">%s</a>',
			$item->id,
			__( 'Sync', 'wp-ultimo' )
		);
		
		$actions[] = sprintf(
			'<a href="#" class="wu-delete-domain" data-domain-id="%d">%s</a>',
			$item->id,
			__( 'Delete', 'wp-ultimo' )
		);
		
		return implode( ' | ', $actions );
	}
	
	/**
	 * Get bulk actions
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		return apply_filters( 'wu_domains_list_table_bulk_actions', array(
			'sync' => __( 'Sync with OpenSRS', 'wp-ultimo' ),
			'delete_expired' => __( 'Delete Expired', 'wp-ultimo' ),
			'enable_auto_renew' => __( 'Enable Auto-Renew', 'wp-ultimo' ),
			'disable_auto_renew' => __( 'Disable Auto-Renew', 'wp-ultimo' ),
		) );
	}
	
	/**
	 * Display extra tablenav
	 *
	 * @param string $which Position
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}
		
		$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
		
		?>
		<div class="alignleft actions">
			<select name="status">
				<option value=""><?php esc_html_e( 'All Statuses', 'wp-ultimo' ); ?></option>
				<option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Active', 'wp-ultimo' ); ?></option>
				<option value="expired" <?php selected( $status, 'expired' ); ?>><?php esc_html_e( 'Expired', 'wp-ultimo' ); ?></option>
				<option value="pending" <?php selected( $status, 'pending' ); ?>><?php esc_html_e( 'Pending', 'wp-ultimo' ); ?></option>
			</select>
			<?php submit_button( __( 'Filter', 'wp-ultimo' ), '', 'filter_action', false ); ?>
		</div>
		<?php
	}
}

/**
 * Render admin domains page
 */
function render_admin_domains_page() {
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Domains', 'wp-ultimo' ); ?></h1>
		
		<?php
		$list_table = new OpenSRS_Domains_List_Table();
		$list_table->prepare_items();
		?>
		
		<form method="get">
			<input type="hidden" name="page" value="wp-ultimo-domains" />
			<?php
			$list_table->search_box( __( 'Search Domains', 'wp-ultimo' ), 'domain' );
			$list_table->display();
			?>
		</form>
	</div>
	
	<script>
	jQuery(document).ready(function($) {
		// Handle sync domain
		$('.wu-sync-domain').on('click', function(e) {
			e.preventDefault();
			var domainId = $(this).data('domain-id');
			
			if (!confirm('<?php esc_html_e( "Sync this domain with OpenSRS?", "wp-ultimo" ); ?>')) {
				return;
			}
			
			$.post(ajaxurl, {
				action: 'wu_admin_sync_domain',
				domain_id: domainId,
				nonce: '<?php echo wp_create_nonce( "wu-admin-domains" ); ?>'
			}, function(response) {
				if (response.success) {
					alert(response.data.message);
					location.reload();
				} else {
					alert(response.data.message);
				}
			});
		});
		
		// Handle delete domain
		$('.wu-delete-domain').on('click', function(e) {
			e.preventDefault();
			var domainId = $(this).data('domain-id');
			
			if (!confirm('<?php esc_html_e( "Are you sure you want to delete this domain? This action cannot be undone.", "wp-ultimo" ); ?>')) {
				return;
			}
			
			$.post(ajaxurl, {
				action: 'wu_admin_delete_domain',
				domain_id: domainId,
				nonce: '<?php echo wp_create_nonce( "wu-admin-domains" ); ?>'
			}, function(response) {
				if (response.success) {
					alert(response.data.message);
					location.reload();
				} else {
					alert(response.data.message);
				}
			});
		});
	});
	</script>
	<?php
}

// Initialize admin dashboard
new OpenSRS_Admin_Dashboard();

// Hook to render the admin page
add_action( 'wu_page_wp-ultimo-domains', __NAMESPACE__ . '\render_admin_domains_page' );