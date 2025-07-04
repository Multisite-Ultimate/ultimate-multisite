<?php
/**
 * This helper class allow us to keep our external link references
 * in one place for better control; Links are also filterable;
 *
 * @package WP_Ultimo
 * @subpackage Documentation
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This helper class allow us to keep our external link references
 * in one place for better control; Links are also filterable;
 *
 * @since 2.0.0
 */
class Documentation {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Holds the links so we can retrieve them later
	 *
	 * @var array
	 */
	protected $links;

	/**
	 * Holds the default link
	 *
	 * @var string
	 */
	protected $default_link = 'https://github.com/superdav42/wp-multisite-waas/wiki';

	/**
	 * Set the default links.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		$links = [];

		// Multisite Ultimate Dashboard
		$links['wp-ultimo'] = 'https://github.com/superdav42/wp-multisite-waas/wiki';

		// Settings Page
		$links['wp-ultimo-settings'] = 'https://github.com/superdav42/wp-multisite-waas/wiki';

		// Checkout Pages
		$links['wp-ultimo-checkout-forms']         = 'https://github.com/superdav42/wp-multisite-waas/wiki/checkout-forms';
		$links['wp-ultimo-edit-checkout-form']     = 'https://github.com/superdav42/wp-multisite-waas/wiki/checkout-forms';
		$links['wp-ultimo-populate-site-template'] = 'https://github.com/superdav42/wp-multisite-waas/wiki/Pre-populate-Site-Template';

		// Products
		$links['wp-ultimo-products']     = 'https://github.com/superdav42/wp-multisite-waas/wiki/creating-your-first-subscription-product-v2';
		$links['wp-ultimo-edit-product'] = 'https://github.com/superdav42/wp-multisite-waas/wiki/creating-your-first-subscription-product-v2';

		// Memberships
		$links['wp-ultimo-memberships']     = 'https://github.com/superdav42/wp-multisite-waas/wiki/managing-memberships-v2';
		$links['wp-ultimo-edit-membership'] = 'https://github.com/superdav42/wp-multisite-waas/wiki/managing-memberships-v2';

		// Payments
		$links['wp-ultimo-payments']     = 'https://github.com/superdav42/wp-multisite-waas/wiki/managing-payments-and-invoices';
		$links['wp-ultimo-edit-payment'] = 'https://github.com/superdav42/wp-multisite-waas/wiki/managing-payments-and-invoices';

		// WP Config Closte Instructions
		$links['wp-ultimo-closte-config'] = 'https://github.com/superdav42/wp-multisite-waas/wiki/Closte-Integration';

		// Requirements
		$links['wp-ultimo-requirements'] = 'https://github.com/superdav42/wp-multisite-waas/wiki/wp-ultimo-requirements';

		// Installer - Migrator
		$links['installation-errors'] = 'https://github.com/superdav42/wp-multisite-waas/wiki/error-installing-the-sunrise-file';
		$links['migration-errors']    = 'https://github.com/superdav42/wp-multisite-waas/wiki/migrating-from-v1';

		// Multiple Accounts
		$links['multiple-accounts'] = 'https://github.com/superdav42/wp-multisite-waas/wiki/Multiple-Accounts';

		$this->links = apply_filters('wu_documentation_links_list', $links);
	}

	/**
	 * Checks if a link exists.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $slug The slug of the link to be returned.
	 * @return boolean
	 */
	public function has_link($slug) {

		return (bool) $this->get_link($slug, false);
	}

	/**
	 * Retrieves a link registered
	 *
	 * @since 1.7.0
	 * @param  string $slug The slug of the link to be returned.
	 * @param  bool   $return_default If we should return a default value.
	 * @return string
	 */
	public function get_link($slug, $return_default = true) {

		$default = $return_default ? $this->default_link : false;

		$link = wu_get_isset($this->links, $slug, $default);

		/**
		 * Allow plugin developers to filter the links.
		 * Not sure how that could be useful, but it doesn't hurt to have it
		 *
		 * @since 1.7.0
		 * @param string $link         The link registered
		 * @param string $slug         The slug used to retrieve the link
		 * @param string $default_link The default link registered
		 */
		return apply_filters('wu_documentation_get_link', $link, $slug, $this->default_link);
	}

	/**
	 * Add a new link to the list of links available for reference
	 *
	 * @since 2.0.0
	 * @param string $slug The slug of a new link.
	 * @param string $link The documentation link.
	 * @return void
	 */
	public function register_link($slug, $link): void {

		$this->links[ $slug ] = $link;
	}
}
