<?php
/**
 * Class used for setting up the email accounts table.
 *
 * @package WP_Ultimo
 * @subpackage Database\Email_Accounts
 * @since 2.3.0
 */

namespace WP_Ultimo\Database\Email_Accounts;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_email_accounts" database table
 *
 * @since 2.3.0
 */
final class Email_Accounts_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected $name = 'email_accounts';

	/**
	 * Is this table global?
	 *
	 * @since 2.3.0
	 * @var boolean
	 */
	protected $global = true;

	/**
	 * Table current version
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected $version = '2.3.0';

	/**
	 * List of table upgrades.
	 *
	 * @var array
	 */
	protected $upgrades = [];

	/**
	 * Set up the database schema
	 *
	 * @access protected
	 * @since  2.3.0
	 * @return void
	 */
	protected function set_schema(): void {

		$this->schema = "id bigint(20) NOT NULL auto_increment,
			customer_id bigint(20) NOT NULL,
			membership_id bigint(20) NULL,
			site_id bigint(20) NULL,
			email_address varchar(255) NOT NULL,
			domain varchar(191) NOT NULL,
			provider varchar(50) NOT NULL,
			status enum('pending', 'provisioning', 'active', 'suspended', 'failed') DEFAULT 'pending',
			quota_mb int(11) unsigned DEFAULT 0,
			external_id varchar(255) NULL,
			password_hash text NULL,
			purchase_type enum('membership_included', 'per_account') DEFAULT 'membership_included',
			payment_id bigint(20) NULL,
			date_created datetime NULL,
			date_modified datetime NULL,
			PRIMARY KEY (id),
			KEY customer_id (customer_id),
			KEY membership_id (membership_id),
			KEY site_id (site_id),
			KEY email_address (email_address),
			KEY domain (domain),
			KEY provider (provider),
			KEY status (status)";
	}
}
