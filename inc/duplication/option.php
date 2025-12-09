<?php
defined('ABSPATH') || exit;

/**
 * Option management for the plugin
 */
if ( ! class_exists('MUCD_Option') ) {

	/**
	 * Multisite Ultimate Clone Duplicator Option class.
	 *
	 * Manages WordPress options during site duplication, handling
	 * the copying and updating of site-specific configuration.
	 */
	class MUCD_Option {

		/**
		 * Init 'mucd_duplicable' options
		 *
		 * @param string $blogs_value   The value for blogs options.
		 * @param string $network_value The value for site option.
		 * @since 0.2.0
		 */
		public static function init_duplicable_option($blogs_value = 'no', $network_value = 'all'): void {
			$network_blogs = MUCD_Functions::get_sites();
			foreach ( $network_blogs as $blog ) {
				$blog_id = $blog['blog_id'];
				add_blog_option($blog_id, 'mucd_duplicable', $blogs_value);
			}

			add_site_option('mucd_duplicables', $network_value);
		}


		/**
		 * Delete 'mucd_duplicable' option for all sites
		 *
		 * @since 0.2.0
		 */
		public static function delete_duplicable_option(): void {
			$network_blogs = MUCD_Functions::get_sites();
			foreach ( $network_blogs as $blog ) {
				$blog_id = $blog['blog_id'];
				delete_blog_option($blog_id, 'mucd_duplicable');
			}

			delete_site_option('mucd_duplicables');
		}


		/**
		 * Set 'mucd_duplicable' option to "yes" for the list of blogs, other to "no"
		 *
		 * @since 0.2.0
		 * @param array $blogs List of blog IDs to set the option to "yes".
		 */
		public static function set_duplicable_option($blogs): void {
			$network_blogs = MUCD_Functions::get_sites();
			foreach ( $network_blogs as $blog ) {
				if (in_array($blog['blog_id'], $blogs)) {
					update_blog_option($blog['blog_id'], 'mucd_duplicable', 'yes');
				} else {
					update_blog_option($blog['blog_id'], 'mucd_duplicable', 'no');
				}
			}
		}


		/**
		 * Add plugin default options
		 *
		 * @since 1.3.0
		 */
		public static function init_options(): void {
			add_site_option('mucd_copy_files', 'yes');
			add_site_option('mucd_keep_users', 'yes');
			add_site_option('mucd_log', 'no');
			$upload_dir = wp_upload_dir();
			add_site_option('mucd_log_dir', $upload_dir['basedir'] . '/multisite-clone-duplicator-logs/');
			add_site_option('mucd_disable_enhanced_site_select', 'no');
			self::init_duplicable_option();
		}


		/**
		 * Removes plugin options
		 *
		 * @since 1.3.0
		 */
		public static function delete_options(): void {
			delete_site_option('mucd_copy_files');
			delete_site_option('mucd_keep_users');
			delete_site_option('mucd_log');
			delete_site_option('mucd_log_dir');
			delete_site_option('mucd_disable_enhanced_site_select');
			self::delete_duplicable_option();
		}


		/**
		 * Get log directory option
		 *
		 * @since 0.2.0
		 * @return string the path
		 */
		public static function get_option_log_directory() {
			$upload_dir = wp_upload_dir();
			return get_site_option('mucd_log_dir', $upload_dir['basedir'] . '/multisite-clone-duplicator-logs/');
		}


		/**
		 * Get directories to exclude from file copy when duplicated site is primary site
		 *
		 * @since 0.2.0
		 * @return  array of string
		 */
		public static function get_primary_dir_exclude() {
			return [
				'sites',
			];
		}


		/**
		 * Get default options that should be preserved in the new blog.
		 *
		 * @since 0.2.0
		 * @return  array of string
		 */
		public static function get_default_saved_option() {
			return [
				'siteurl'                                => '',
				'home'                                   => '',
				'upload_path'                            => '',
				'fileupload_url'                         => '',
				'upload_url_path'                        => '',
				'admin_email'                            => '',
				'blogname'                               => '',
				'schema-ActionScheduler_Abstract_Schema' => '',
				'action_scheduler_hybrid_store_demarkation' => '',
				'schema-ActionScheduler_StoreSchema'     => '',
				'schema-ActionScheduler_LoggerSchema'    => '',
				'action_scheduler_lock_async-request-runner' => '',
			];
		}


		/**
		 * Get filtered options that should be preserved in the new blog.
		 *
		 * @since 0.2.0
		 * @return  array of string (filtered)
		 */
		public static function get_saved_option() {
			return apply_filters('mucd_copy_blog_data_saved_options', self::get_default_saved_option());
		}


		/**
		 * Get default fields to scan for an update after data copy
		 *
		 * @since 0.2.0
		 * @return array '%table_name' => array('%field_name_1','%field_name_2','%field_name_3', ...)
		 */
		public static function get_default_fields_to_update() {
			return [
				'commentmeta'        => [],
				'comments'           => [],
				'links'              => ['link_url', 'link_image'],
				'options'            => ['option_name', 'option_value'],
				'postmeta'           => ['meta_value'],
				'posts'              => ['post_content', 'guid', 'post_title', 'post_name'],
				'terms'              => [],
				'term_relationships' => [],
				'term_taxonomy'      => [],
			];
		}


		/**
		 * Get filtered fields to scan for an update after data copy
		 *
		 * @since 0.2.0
		 * @return  array of string (filtered)
		 */
		public static function get_fields_to_update() {
			return apply_filters('mucd_default_fields_to_update', self::get_default_fields_to_update());
		}


		/**
		 * Get default tables to duplicate when duplicated site is primary site
		 *
		 * @since 0.2.0
		 * @return  array of string
		 */
		public static function get_default_primary_tables_to_copy() {
			return [
				'commentmeta',
				'comments',
				'links',
				'options',
				'postmeta',
				'posts',
				'terms',
				'term_relationships',
				'term_taxonomy',
				'termmeta',
			];
		}


		/**
		 * Get filtered tables to duplicate when duplicated site is primary site
		 *
		 * @since 0.2.0
		 * @return  array of string (filtered)
		 */
		public static function get_primary_tables_to_copy() {
			return apply_filters('mucd_default_primary_tables_to_copy', self::get_default_primary_tables_to_copy());
		}
	}

}
