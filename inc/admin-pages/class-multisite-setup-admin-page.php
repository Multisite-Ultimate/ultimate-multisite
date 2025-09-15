<?php
/**
 * Multisite Setup Admin Page.
 *
 * Handles the configuration and activation of WordPress Multisite
 * when it's not already enabled.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Multisite Setup Admin Page.
 */
class Multisite_Setup_Admin_Page extends Wizard_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-multisite-setup';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $type = 'menu';

	/**
	 * This page has no parent, so we need to highlight another sub-menu.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $highlight_menu_slug = false;

	/**
	 * If this number is greater than 0, a badge with the number will be displayed alongside the menu title
	 *
	 * @since 2.0.0
	 * @var integer
	 */
	protected $badge_count = 0;

	/**
	 * Holds the admin panels where this page should be displayed, as well as which capability to require.
	 *
	 * To add a page to the regular admin (wp-admin/), use: 'admin_menu' => 'capability_here'
	 * To add a page to the network admin (wp-admin/network), use: 'network_admin_menu' => 'capability_here'
	 * To add a page to the user (wp-admin/user) admin, use: 'user_admin_menu' => 'capability_here'
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $supported_panels = [
		'admin_menu' => 'manage_options',
	];

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function __construct() {

		$this->type      = 'menu';
		$this->position  = 10_101_010;
		$this->menu_icon = 'dashicons-wu-wp-ultimo';

		parent::__construct();

		add_action('admin_enqueue_scripts', [$this, 'register_scripts']);
	}

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title(): string {
		return __('Enable WordPress Multisite', 'multisite-ultimate');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {
		return __('Multisite Ultimate', 'multisite-ultimate');
	}

	/**
	 * Returns the logo for the wizard.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {
		return wu_get_asset('logo.webp', 'img');
	}

	/**
	 * Returns the sections for this Wizard.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_sections() {

		return [
			'welcome'   => [
				'title'       => __('Multisite Required', 'multisite-ultimate'),
				'description' => implode(
					'<br><br>',
					[
						__('WordPress Multisite is required for Multisite Ultimate to function properly.', 'multisite-ultimate'),
						__('This wizard will guide you through enabling WordPress Multisite and configuring your network.', 'multisite-ultimate'),
						__('We recommend creating a backup of your files and database before proceeding.', 'multisite-ultimate'),
					]
				),
				'next_label'  => __('Get Started &rarr;', 'multisite-ultimate'),
				'back'        => false,
			],
			'configure' => [
				'title'       => __('Network Configuration', 'multisite-ultimate'),
				'description' => __('Configure your network settings. These settings determine how your sites will be structured.', 'multisite-ultimate'),
				'next_label'  => __('Create Network', 'multisite-ultimate'),
				'handler'     => [$this, 'handle_configure'],
				'fields'      => [$this, 'get_network_configuration_fields'],
			],
			'complete'  => [
				'title'       => __('Setup Complete', 'multisite-ultimate'),
				'description' => __('WordPress Multisite setup is now complete!', 'multisite-ultimate'),
				'view'        => [$this, 'section_complete'],
				'back'        => false,
				'next'        => false,
			],
		];
	}

	/**
	 * Welcome section view.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function section_welcome(): void {

		?>
		<div class="wu-mb-6">
			<h3 class="wu-text-lg wu-font-semibold wu-text-gray-900 wu-mb-3">
				<?php esc_html_e('What is WordPress Multisite?', 'multisite-ultimate'); ?>
			</h3>
			<ul class="wu-list-disc wu-list-inside wu-text-gray-600 wu-space-y-2">
				<li><?php esc_html_e('Create multiple websites from a single WordPress installation', 'multisite-ultimate'); ?></li>
				<li><?php esc_html_e('Share themes, plugins, and users across all sites in the network', 'multisite-ultimate'); ?></li>
				<li><?php esc_html_e('Manage all sites from a central network administration panel', 'multisite-ultimate'); ?></li>
				<li><?php esc_html_e('Perfect foundation for Website-as-a-Service platforms', 'multisite-ultimate'); ?></li>
			</ul>
		</div>

		<div class="wu-mb-6">
			<h3 class="wu-text-lg wu-font-semibold wu-text-gray-900 wu-mb-3">
				<?php esc_html_e('What happens next?', 'multisite-ultimate'); ?>
			</h3>
			<p class="wu-text-gray-600 wu-mb-4">
				<?php esc_html_e('This wizard will guide you through the process of enabling WordPress Multisite. We will:', 'multisite-ultimate'); ?>
			</p>
			<ol class="wu-list-decimal wu-list-inside wu-text-gray-600 wu-space-y-2">
				<li><?php esc_html_e('Configure your network settings (subdomain or subdirectory structure)', 'multisite-ultimate'); ?></li>
				<li><?php esc_html_e('Automatically modify your wp-config.php file (if we have write access)', 'multisite-ultimate'); ?></li>
				<li><?php esc_html_e('Create the necessary database tables', 'multisite-ultimate'); ?></li>
				<li><?php esc_html_e('Complete the multisite setup process', 'multisite-ultimate'); ?></li>
			</ol>
		</div>

		<div class="wu-bg-blue-50 wu-border wu-border-blue-200 wu-rounded-lg wu-p-4">
			<div class="wu-flex">
				<div class="wu-flex-shrink-0">
					<span class="dashicons dashicons-info wu-text-blue-500"></span>
				</div>
				<div class="wu-ml-3">
					<h4 class="wu-text-sm wu-font-medium wu-text-blue-800">
						<?php esc_html_e('Important Notice', 'multisite-ultimate'); ?>
					</h4>
					<p class="wu-text-sm wu-text-blue-700 wu-mt-1">
						<?php esc_html_e('This process will make changes to your WordPress installation. We recommend creating a backup of your files and database before proceeding.', 'multisite-ultimate'); ?>
					</p>
				</div>
			</div>
		</div>
		<?php

		$this->render_submit_box();
	}

	/**
	 * Returns the network configuration fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_network_configuration_fields() {

		$home_url    = get_option('home');
		$base_domain = parse_url($home_url, PHP_URL_HOST);
		$user        = wp_get_current_user();

		return [
			'network_structure_header' => [
				'type'  => 'header',
				'title' => __('Network Structure', 'multisite-ultimate'),
				'desc'  => __('Choose how you want your network sites to be organized:', 'multisite-ultimate'),
			],
			'subdomain_install'        => [
				'type'    => 'radio',
				'title'   => __('Site Structure', 'multisite-ultimate'),
				'desc'    => __('Choose between subdirectories or subdomains for your network sites.', 'multisite-ultimate'),
				'options' => [
					'sub0' => sprintf(__('Sites will use sub-directories like %s (Recommended)', 'multisite-ultimate'), '<code>' . esc_html($base_domain) . '/site1</code>'),
					
					'sub1'  => sprintf(__('Sites will use sub-domains like %s (Requires wildcard DNS)', 'multisite-ultimate'), '<code>site1.' . esc_html($base_domain) . '</code>'),

				],
				'default' => '0',
			],
			'network_details_header'   => [
				'type'  => 'header',
				'title' => __('Network Details', 'multisite-ultimate'),
			],
			'sitename'                 => [
				'type'        => 'text',
				'title'       => __('Network Title', 'multisite-ultimate'),
				'desc'        => __('This will be the title of your network.', 'multisite-ultimate'),
				'placeholder' => __('Enter network title', 'multisite-ultimate'),
				'default'     => get_option('blogname'),
			],
			'email'                    => [
				'type'        => 'email',
				'title'       => __('Network Admin Email', 'multisite-ultimate'),
				'desc'        => __('This email address will be used for network administration.', 'multisite-ultimate'),
				'placeholder' => __('Enter admin email', 'multisite-ultimate'),
				'default'     => $user->user_email,
			],
			'backup_warning'           => [
				'type' => 'note',
				'desc' => '<div class="wu-bg-yellow-50 wu-border wu-border-yellow-200 wu-rounded-lg wu-p-4">
					<div class="wu-flex">
						<div class="wu-flex-shrink-0">
							<span class="dashicons dashicons-warning wu-text-yellow-500"></span>
						</div>
						<div class="wu-ml-3">
							<h4 class="wu-text-sm wu-font-medium wu-text-yellow-800">' . __('Before You Continue', 'multisite-ultimate') . '</h4>
							<p class="wu-text-sm wu-text-yellow-700 wu-mt-1">' . __('Please ensure you have a recent backup of your website files and database. The multisite setup process will modify your wp-config.php file and create new database tables.', 'multisite-ultimate') . '</p>
						</div>
					</div>
				</div>',
			],
		];
	}

	/**
	 * Handles the network configuration form submission.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_configure(): void {

		if (! current_user_can('manage_options')) {
			wp_die(__('Permission denied.', 'multisite-ultimate'));
		}

		$subdomain_install = (bool) wu_request('subdomain_install', 0);
		$sitename          = sanitize_text_field(wu_request('sitename', ''));
		$email             = sanitize_email(wu_request('email', ''));

		// Store values in transients for completion page
		set_transient('wu_multisite_subdomain_install', $subdomain_install, 300);
		set_transient('wu_multisite_sitename', $sitename, 300);
		set_transient('wu_multisite_email', $email, 300);

		// Try to enable multisite
		$wp_config_modified = $this->modify_wp_config();
		$network_created    = false;

		if ($wp_config_modified) {
			// Create the network
			$network_created = $this->create_network($subdomain_install, $sitename, $email);
		}

		// Store results
		set_transient('wu_multisite_wp_config_modified', $wp_config_modified, 300);
		set_transient('wu_multisite_network_created', $network_created, 300);

		// Redirect to completion step
		wp_safe_redirect($this->get_next_section_link());
		exit;
	}

	/**
	 * Completion section view.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function section_complete(): void {

		$wp_config_modified = get_transient('wu_multisite_wp_config_modified');
		$network_created    = get_transient('wu_multisite_network_created');

		if ($network_created && $wp_config_modified) :
			?>
			<div class="wu-bg-green-50 wu-border wu-border-green-200 wu-rounded-lg wu-p-4 wu-mb-6">
				<div class="wu-flex">
					<div class="wu-flex-shrink-0">
						<span class="dashicons dashicons-yes-alt wu-text-green-500"></span>
					</div>
					<div class="wu-ml-3">
						<h4 class="wu-text-sm wu-font-medium wu-text-green-800">
							<?php esc_html_e('Success!', 'multisite-ultimate'); ?>
						</h4>
						<p class="wu-text-sm wu-text-green-700 wu-mt-1">
							<?php esc_html_e('WordPress Multisite has been successfully enabled. You can now continue with the Multisite Ultimate setup.', 'multisite-ultimate'); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="wu-flex wu-justify-center">
				<a href="<?php echo esc_url(wu_network_admin_url('wp-ultimo-setup')); ?>" class="wu-inline-flex wu-items-center wu-px-6 wu-py-3 wu-border wu-border-transparent wu-text-base wu-font-medium wu-rounded-md wu-text-white wu-bg-blue-600 hover:wu-bg-blue-700 wu-transition-colors">
					<?php esc_html_e('Continue to Multisite Ultimate Setup', 'multisite-ultimate'); ?>
					<span class="dashicons dashicons-arrow-right-alt wu-ml-2"></span>
				</a>
			</div>
			<?php
		else :
			$this->display_manual_instructions();
		endif;

		// Clean up transients
		delete_transient('wu_multisite_wp_config_modified');
		delete_transient('wu_multisite_network_created');
		delete_transient('wu_multisite_subdomain_install');
		delete_transient('wu_multisite_sitename');
		delete_transient('wu_multisite_email');
	}

	/**
	 * Display manual configuration instructions.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function display_manual_instructions(): void {

		$home_url          = get_option('home');
		$base_domain       = parse_url($home_url, PHP_URL_HOST);
		$subdomain_install = get_transient('wu_multisite_subdomain_install');

		$wp_config_constants = "define( 'WP_ALLOW_MULTISITE', true );
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', " . ($subdomain_install ? 'true' : 'false') . " );
define( 'DOMAIN_CURRENT_SITE', '{$base_domain}' );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );";

		$htaccess_rules = 'RewriteEngine On
RewriteRule ^index\.php$ - [L]

# add a trailing slash to /wp-admin
RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$ $1wp-admin/ [R=301,L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) $2 [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(.*\.php)$ $2 [L]
RewriteRule . index.php [L]';

		?>
		<div class="wu-mb-6">
			<p class="wu-text-gray-600 wu-mb-4">
				<?php esc_html_e('We were unable to automatically configure your wp-config.php file. Please follow the manual instructions below to complete the multisite setup.', 'multisite-ultimate'); ?>
			</p>
		</div>

		<div class="wu-mb-6">
			<h3 class="wu-text-lg wu-font-semibold wu-text-gray-900 wu-mb-3">
				<?php esc_html_e('Step 1: Add to wp-config.php', 'multisite-ultimate'); ?>
			</h3>
			<p class="wu-text-gray-600 wu-mb-4">
				<?php
				printf(
					esc_html__('Add the following lines to your %1$s file, just before the %2$s comment:', 'multisite-ultimate'),
					'<code>wp-config.php</code>',
					'<code>/* That\'s all, stop editing! Happy publishing. */</code>'
				);
				?>
			</p>
			<div class="wu-bg-gray-50 wu-border wu-border-gray-200 wu-rounded-lg wu-p-4 wu-mb-4">
				<pre class="wu-text-sm wu-overflow-x-auto"><code><?php echo esc_html($wp_config_constants); ?></code></pre>
			</div>
		</div>

		<div class="wu-mb-6">
			<h3 class="wu-text-lg wu-font-semibold wu-text-gray-900 wu-mb-3">
				<?php esc_html_e('Step 2: Add to .htaccess', 'multisite-ultimate'); ?>
			</h3>
			<p class="wu-text-gray-600 wu-mb-4">
				<?php esc_html_e('Replace the existing WordPress rules in your .htaccess file with:', 'multisite-ultimate'); ?>
			</p>
			<div class="wu-bg-gray-50 wu-border wu-border-gray-200 wu-rounded-lg wu-p-4 wu-mb-4">
				<pre class="wu-text-sm wu-overflow-x-auto"><code><?php echo esc_html($htaccess_rules); ?></code></pre>
			</div>
		</div>

		<div class="wu-bg-blue-50 wu-border wu-border-blue-200 wu-rounded-lg wu-p-4 wu-mb-6">
			<div class="wu-flex">
				<div class="wu-flex-shrink-0">
					<span class="dashicons dashicons-info wu-text-blue-500"></span>
				</div>
				<div class="wu-ml-3">
					<h4 class="wu-text-sm wu-font-medium wu-text-blue-800">
						<?php esc_html_e('Next Steps', 'multisite-ultimate'); ?>
					</h4>
					<p class="wu-text-sm wu-text-blue-700 wu-mt-1">
						<?php esc_html_e('After making these changes, refresh this page. WordPress will detect that multisite is enabled and you can proceed with the Multisite Ultimate setup.', 'multisite-ultimate'); ?>
					</p>
				</div>
			</div>
		</div>

		<div class="wu-flex wu-justify-center">
			<a href="<?php echo esc_url(admin_url()); ?>" class="wu-inline-flex wu-items-center wu-px-4 wu-py-2 wu-border wu-border-transparent wu-text-sm wu-font-medium wu-rounded-md wu-text-white wu-bg-green-600 hover:wu-bg-green-700 wu-transition-colors">
				<?php esc_html_e('Refresh and Check Again', 'multisite-ultimate'); ?>
				<span class="dashicons dashicons-update wu-ml-1"></span>
			</a>
		</div>
		<?php
	}

	/**
	 * Attempts to modify wp-config.php to enable multisite.
	 *
	 * @since 2.0.0
	 * @return bool Whether the modification was successful.
	 */
	protected function modify_wp_config(): bool {

		$wp_config_path = ABSPATH . 'wp-config.php';

		if (! file_exists($wp_config_path) || ! is_writable($wp_config_path)) {
			return false;
		}

		$config_content = file_get_contents($wp_config_path);

		if ($config_content === false) {
			return false;
		}

		// Check if WP_ALLOW_MULTISITE is already defined
		if (strpos($config_content, 'WP_ALLOW_MULTISITE') !== false) {
			return true; // Already configured
		}

		// Find the location to insert the constant
		$search          = "/* That's all, stop editing! Happy publishing. */";
		$insert_position = strpos($config_content, $search);

		if ($insert_position === false) {
			// Fallback: look for the wp-settings.php include
			$search          = "require_once ABSPATH . 'wp-settings.php';";
			$insert_position = strpos($config_content, $search);
		}

		if ($insert_position === false) {
			return false; // Can't find a safe place to insert
		}

		$constant_to_add = "\n// Multisite Ultimate: Enable WordPress Multisite\ndefine( 'WP_ALLOW_MULTISITE', true );\n\n";

		$new_content = substr_replace($config_content, $constant_to_add, $insert_position, 0);

		return file_put_contents($wp_config_path, $new_content) !== false;
	}

	/**
	 * Creates the multisite network.
	 *
	 * @since 2.0.0
	 * @param bool   $subdomain_install Whether to use subdomains.
	 * @param string $sitename Network title.
	 * @param string $email Network admin email.
	 * @return bool Whether the network creation was successful.
	 */
	protected function create_network(bool $subdomain_install, string $sitename, string $email): bool {

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Load network functions
		if (! function_exists('install_network')) {
			require_once ABSPATH . 'wp-admin/includes/network.php';
		}

		try {
			// Create network tables
			install_network();

			$base   = parse_url(trailingslashit(get_option('home')), PHP_URL_PATH);
			$domain = parse_url(get_option('home'), PHP_URL_HOST);

			// Populate network
			$result = populate_network(1, $domain, $email, $sitename, $base, $subdomain_install);

			if (is_wp_error($result)) {
				return false;
			}

			// Add final multisite constants to wp-config.php
			$this->add_final_multisite_constants($subdomain_install, $domain);

			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Adds the final multisite constants to wp-config.php.
	 *
	 * @since 2.0.0
	 * @param bool   $subdomain_install Whether subdomains are used.
	 * @param string $domain The main domain.
	 * @return bool Whether the modification was successful.
	 */
	protected function add_final_multisite_constants(bool $subdomain_install, string $domain): bool {

		$wp_config_path = ABSPATH . 'wp-config.php';

		if (! file_exists($wp_config_path) || ! is_writable($wp_config_path)) {
			return false;
		}

		$config_content = file_get_contents($wp_config_path);

		if ($config_content === false) {
			return false;
		}

		// Check if MULTISITE is already defined
		if (strpos($config_content, 'MULTISITE') !== false) {
			return true; // Already configured
		}

		$constants_to_add  = "\n// Multisite Ultimate: Multisite Configuration\n";
		$constants_to_add .= "define( 'MULTISITE', true );\n";
		$constants_to_add .= "define( 'SUBDOMAIN_INSTALL', " . ($subdomain_install ? 'true' : 'false') . " );\n";
		$constants_to_add .= "define( 'DOMAIN_CURRENT_SITE', '{$domain}' );\n";
		$constants_to_add .= "define( 'PATH_CURRENT_SITE', '/' );\n";
		$constants_to_add .= "define( 'SITE_ID_CURRENT_SITE', 1 );\n";
		$constants_to_add .= "define( 'BLOG_ID_CURRENT_SITE', 1 );\n\n";

		// Find the location to insert the constants (after WP_ALLOW_MULTISITE)
		$search          = "define( 'WP_ALLOW_MULTISITE', true );";
		$insert_position = strpos($config_content, $search);

		if ($insert_position !== false) {
			$insert_position += strlen($search);
			$new_content      = substr_replace($config_content, $constants_to_add, $insert_position, 0);
			return file_put_contents($wp_config_path, $new_content) !== false;
		}

		return false;
	}

	/**
	 * Register page scripts and styles.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts(): void {

		if (get_current_screen()->id !== 'toplevel_page_wp-ultimo-multisite-setup') {
			return;
		}

		wp_add_inline_script(
			'wp-admin',
			'
			// Copy to clipboard functionality
			document.addEventListener("DOMContentLoaded", function() {
				document.querySelectorAll("button[onclick*=\'navigator.clipboard.writeText\']").forEach(function(button) {
					button.addEventListener("click", function() {
						var textarea = this.nextElementSibling;
						if (textarea && textarea.tagName === "TEXTAREA") {
							navigator.clipboard.writeText(textarea.value).then(function() {
								button.textContent = "Copied!";
								setTimeout(function() {
									button.textContent = "Copy to clipboard";
								}, 2000);
							});
						}
					});
				});
			});
		'
		);
	}
}