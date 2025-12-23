<?php
/**
 * The Email model for the Emails.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Models;

use WP_Ultimo\Models\Post_Base_Model;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Email model class. Implements the Base Model.
 *
 * @since 2.0.0
 */
class Email extends Post_Base_Model {

	/**
	 * Post model.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $model = 'email';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since  2.0.0
	 * @access public
	 * @var mixed
	 */
	protected $query_class = \WP_Ultimo\Database\Emails\Email_Query::class;

	/**
	 * Post type.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $type = 'system_email';

	/**
	 * Set the allowed types to prevent saving wrong types.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $allowed_types = ['system_email'];

	/**
	 * Email slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = '';

	/**
	 * Post status.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $allowed_status = ['publish', 'draft'];

	/**
	 * If this email is going to be send later.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $schedule;

	/**
	 * If we should send this to a customer or to the network admin.
	 *
	 * Can be either 'customer' or 'admin'.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $target;

	/**
	 * Checks if we should send a copy of the email to the admin.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $send_copy_to_admin;

	/**
	 * The event of this email.
	 *
	 * This determines when this email is going to be triggered.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $event;

	/**
	 * The active status of an email.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $active;

	/**
	 * Whether or not this is a legacy email.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $legacy;

	/**
	 * Plain or HTML.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $style;

	/**
	 * Set the validation rules for this particular model.
	 *
	 * To see how to setup rules, check the documentation of the
	 * validation library we are using: https://github.com/rakit/validation
	 *
	 * @since 2.0.0
	 * @link https://github.com/rakit/validation
	 * @return array
	 */
	public function validation_rules() {

		return [
			'schedule'            => 'boolean|default:0',
			'type'                => 'in:system_email|default:system_email',
			'event'               => 'required|default:',
			'send_hours'          => 'default:',
			'send_days'           => 'integer|default:',
			'schedule_type'       => 'in:days,hours',
			'name'                => 'default:title',
			'title'               => 'required',
			'slug'                => 'required',
			'custom_sender'       => 'boolean|default:0',
			'custom_sender_name'  => 'default:',
			'custom_sender_email' => 'default:',
			'target'              => 'required|in:customer,admin',
			'send_copy_to_admin'  => 'boolean|default:0',
			'active'              => 'default:1',
			'legacy'              => 'boolean|default:0',
		];
	}

	/**
	 * Get event of the email
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_event() {

		if (null === $this->event) {
			$this->event = $this->get_meta('wu_system_email_event');
		}

		return $this->event;
	}

	/**
	 * Get title of the email
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return $this->title;
	}

	/**
	 * Get title of the email using get_name
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_name() {

		return $this->title;
	}

	/**
	 * Get style of the email
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_style() {

		$this->style = $this->get_meta('wu_style', 'html');

		if ('use_default' === $this->style) {
			$this->style = wu_get_setting('email_template_type', 'html');
		}

		/*
		 * Do an extra check for old installs
		 * where the default value was not being
		 * properly installed.
		 */
		if (empty($this->style)) {
			$this->style = 'html';
		}

		return $this->style;
	}

	/**
	 * Set the style.
	 *
	 * @since 2.0.0
	 *
	 * @param string $style The email style. Can be 'html' or 'plain-text'.
	 * @options html,plain-text
	 * @return void
	 */
	public function set_style($style): void {

		$this->style = $style;

		$this->meta['wu_style'] = $this->style;
	}

	/**
	 * Get if the email has a schedule.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function has_schedule() {

		if (null === $this->schedule) {
			$this->schedule = $this->get_meta('wu_schedule', false);
		}

		return $this->schedule;
	}

	/**
	 * Set the email schedule.
	 *
	 * @since 2.0.0
	 * @param bool $schedule Whether or not this is a scheduled email.
	 * @return void
	 */
	public function set_schedule($schedule): void {

		$this->schedule = $schedule;

		$this->meta['wu_schedule'] = $schedule;
	}

	/**
	 * Set the email schedule.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_schedule_type() {

		return $this->get_meta('system_email_schedule_type', 'days');
	}

	/**
	 * Get schedule send in days of the email
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_send_days() {

		return $this->get_meta('system_email_send_days', 0);
	}

	/**
	 * Get schedule send in hours of the email.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_send_hours() {

		return $this->get_meta('system_email_send_hours', '12:00');
	}

	/**
	 * Returns a timestamp in the future when this email should be sent.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_when_to_send() {

		$when_to_send = 0;

		if ( ! $this->has_schedule()) {
			return $when_to_send;
		}

		if ($this->get_schedule_type() === 'hours') {
			$send_time = explode(':', $this->get_send_hours());

			$when_to_send = strtotime('+' . $send_time[0] . ' hours ' . $send_time[1] . ' minutes');
		}

		if ($this->get_schedule_type() === 'days') {
			$when_to_send = strtotime('+' . $this->get_send_days() . ' days');
		}

		return $when_to_send;
	}

	/**
	 * Get email slug.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_slug() {

		return $this->slug;
	}

	/**
	 * Get the custom sender option.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_custom_sender() {

		return $this->get_meta('system_email_custom_sender');
	}

	/**
	 * Get the custom sender name.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_custom_sender_name() {

		return $this->get_meta('system_email_custom_sender_name');
	}

	/**
	 * Get the custom sender email.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_custom_sender_email() {

		return $this->get_meta('system_email_custom_sender_email');
	}

	/**
	 * Adds checks to prevent saving the model with the wrong type.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type The type being set.
	 * @return void
	 */
	public function set_type($type): void {

		if ( ! in_array($type, $this->allowed_types, true)) {
			$type = 'system_email';
		}

		$this->type = $type;
	}

	/**
	 * Set the email event.
	 *
	 * @since 2.0.0
	 *
	 * @param string $event The event that needs to be fired for this email to be sent.
	 * @return void
	 */
	public function set_event($event): void {

		$this->event = $event;

		$this->meta['wu_system_email_event'] = $event;
	}

	/**
	 * Set if the email is schedule.
	 *
	 * @since 2.0.0
	 *
	 * @param string $email_schedule if the send will be schedule.
	 * @return void
	 */
	public function set_email_schedule($email_schedule): void {

		$this->meta['system_email_schedule'] = $email_schedule;
	}

	/**
	 * Set the schedule date in hours.
	 *
	 * @since 2.0.0
	 *
	 * @param string $send_hours The amount of hours that the email will wait before is sent.
	 * @return void
	 */
	public function set_send_hours($send_hours): void {

		$this->meta['system_email_send_hours'] = $send_hours;
	}

	/**
	 * Set the schedule date in days.
	 *
	 * @since 2.0.0
	 *
	 * @param string $send_days The amount of days that the email will wait before is sent.
	 * @return void
	 */
	public function set_send_days($send_days): void {

		$this->meta['system_email_send_days'] = $send_days;
	}

	/**
	 * Set the schedule type.
	 *
	 * @since 2.0.0
	 *
	 * @param string $schedule_type The type of schedule. Can be 'days' or 'hours'.
	 * @options days,hours
	 * @return void
	 */
	public function set_schedule_type($schedule_type): void {

		$this->meta['system_email_schedule_type'] = $schedule_type;
	}

	/**
	 * Set title using the name parameter.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name The name being set as title.
	 * @return void
	 */
	public function set_name($name): void {

		$this->set_title($name);
	}

	/**
	 * Set the slug.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug The slug being set.
	 * @return void
	 */
	public function set_slug($slug): void {

		$this->slug = $slug;
	}

	/**
	 * Set the custom sender.
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $custom_sender If has a custom sender.
	 * @return void
	 */
	public function set_custom_sender($custom_sender): void {

		$this->meta['system_email_custom_sender'] = $custom_sender;
	}

	/**
	 * Set the custom sender name.
	 *
	 * @since 2.0.0
	 *
	 * @param string $custom_sender_name The name of the custom sender. E.g. From: John Doe.
	 * @return void
	 */
	public function set_custom_sender_name($custom_sender_name): void {

		$this->meta['system_email_custom_sender_name'] = $custom_sender_name;
	}

	/**
	 * Set the custom sender email.
	 *
	 * @since 2.0.0
	 *
	 * @param string $custom_sender_email The email of the custom sender. E.g. From: johndoe@gmail.com.
	 * @return void
	 */
	public function set_custom_sender_email($custom_sender_email): void {

		$this->meta['system_email_custom_sender_email'] = $custom_sender_email;
	}

	/**
	 * Get if we should send this to a customer or to the network admin.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_target() {

		if (null === $this->target) {
			$this->target = $this->get_meta('wu_target', 'admin');
		}

		return $this->target;
	}

	/**
	 * Set if we should send this to a customer or to the network admin.
	 *
	 * @since 2.0.0
	 * @param string $target If we should send this to a customer or to the network admin. Can be 'customer' or 'admin'.
	 * @options customer,admin
	 * @return void
	 */
	public function set_target($target): void {

		$this->target = $target;

		$this->meta['wu_target'] = $target;
	}

	/**
	 * Gets the list of targets for an email.
	 *
	 * @since 2.0.0
	 *
	 * @param array $payload The payload of the email being sent. Used to get the customer id.
	 * @return array
	 */
	public function get_target_list($payload = []) {

		$target_list = [];

		$target_type = $this->get_target();

		if ('admin' === $target_type) {
			$target_list = self::get_super_admin_targets();
		} elseif ('customer' === $target_type) {
			if ( ! wu_get_isset($payload, 'customer_id')) {
				return [];
			}

			/*
			 * Try to get customer data from payload first.
			 * This prevents issues where wu_get_customer() might fail
			 * due to caching when the customer was just created.
			 */
			$customer_email = wu_get_isset($payload, 'customer_user_email');
			$customer_name  = wu_get_isset($payload, 'customer_name');

			/*
			 * Ensure customer_name is a string, not an array or object.
			 */
			if (is_array($customer_name) || is_object($customer_name)) {
				$customer_name = '';
			}

			/*
			 * If email is not in payload, fallback to database query.
			 */
			if ( ! $customer_email) {
				$customer = wu_get_customer($payload['customer_id']);

				if ( ! $customer) {
					return [];
				}

				$customer_email = $customer->get_email_address();
				$customer_name  = $customer->get_display_name();
			}

			/*
			 * Validate email before adding to target list.
			 */
			if ( ! is_email($customer_email)) {
				return [];
			}

			/*
			 * Use email as name fallback if name is empty.
			 */
			if (empty($customer_name)) {
				$customer_name = $customer_email;
			}

			$target_list[] = [
				'name'  => $customer_name,
				'email' => $customer_email,
			];

			/*
			 * Maybe ad super admins as well.
			 */
			if ($this->get_send_copy_to_admin()) {
				$admin_targets = self::get_super_admin_targets();

				$target_list = array_merge($target_list, $admin_targets);
			}
		}

		return $target_list;
	}

	/**
	 * Returns the list of super admin targets.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_super_admin_targets() {

		$target_list = [];

		$super_admins = get_super_admins();

		foreach ($super_admins as $super_admin) {
			$user = get_user_by('login', $super_admin);

			if ($user) {
				$target_list[] = [
					'name'  => $user->display_name,
					'email' => $user->user_email,
				];
			}
		}

		return $target_list;
	}

	/**
	 * Get if we should send a copy of the email to the admin.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function get_send_copy_to_admin() {

		if (null === $this->send_copy_to_admin) {
			$this->send_copy_to_admin = $this->get_meta('wu_send_copy_to_admin', false);
		}

		return $this->send_copy_to_admin;
	}

	/**
	 * Set if we should send a copy of the email to the admin.
	 *
	 * @since 2.0.0
	 * @param boolean $send_copy_to_admin Checks if we should send a copy of the email to the admin.
	 * @return void
	 */
	public function set_send_copy_to_admin($send_copy_to_admin): void {

		$this->send_copy_to_admin = $send_copy_to_admin;

		$this->meta['wu_send_copy_to_admin'] = $send_copy_to_admin;
	}

	/**
	 * Get the active status of an email.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_active() {

		if (null === $this->active) {
			$this->active = $this->get_meta('wu_active', true);
		}

		return $this->active;
	}

	/**
	 * Set the active status of an email.
	 *
	 * @since 2.0.0
	 * @param bool $active Set this email as active (true), which means available will fire when the event occur, or inactive (false).
	 * @return void
	 */
	public function set_active($active): void {

		$this->active = $active;

		$this->meta['wu_active'] = $active;
	}

	/**
	 * Get whether or not this is a legacy email.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_legacy() {

		if (null === $this->legacy) {
			$this->legacy = $this->get_meta('wu_legacy', false);
		}

		return $this->legacy;
	}

	/**
	 * Set whether or not this is a legacy email.
	 *
	 * @since 2.0.0
	 * @param bool $legacy Whether or not this is a legacy email.
	 * @return void
	 */
	public function set_legacy($legacy): void {

		$this->legacy = $legacy;

		$this->meta['wu_legacy'] = $legacy;
	}
}
