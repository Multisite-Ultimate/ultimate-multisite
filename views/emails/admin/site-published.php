<?php
/**
 * Site Published Email Template - Admin
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
?>
<p><?php esc_html_e('Hey there', 'ultimate-multisite'); ?></p>
<?php // translators: %1$s: Site Title, %2$s: Site Url ?>
<p><?php echo wp_kses(sprintf(__('A new website, <b>%1$s</b> (%2$s), was created successfully on your network!', 'ultimate-multisite'), '{{site_title}}', '<a href="{{site_url}}" style="text-decoration: none;" rel="nofollow">{{site_url}}</a>'), 'pre_user_description'); ?></p>

<h2><b><?php esc_html_e('Site', 'ultimate-multisite'); ?></b></h2>

<table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
	<tbody>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Title', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee; border: 1px solid #eee;">
		{{site_title}}
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('ID', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
		<a href="{{site_admin_url}}" style="text-decoration: none;" rel="nofollow">{{site_id}}</a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Site URL', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		<a href="{{site_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Visit Site →', 'ultimate-multisite'); ?></a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Site Admin Panel', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		<a href="{{site_admin_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Visit Admin Panel →', 'ultimate-multisite'); ?></a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Admin Panel', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		<a href="{{site_manage_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Go to Site Management →', 'ultimate-multisite'); ?></a>
		</td>
	</tr>
	</tbody>
</table>

<h2><b><?php esc_html_e('Membership', 'ultimate-multisite'); ?></b></h2>

<table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
	<tbody>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Amount', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		{{membership_description}}
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Initial Amount', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		{{membership_initial_amount}}
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('ID', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
		<a href="{{membership_manage_url}}" style="text-decoration: none;" rel="nofollow">{{membership_id}}</a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Reference Code', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
		<a href="{{membership_manage_url}}" style="text-decoration: none;" rel="nofollow">{{membership_reference_code}}</a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Expiration', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">{{membership_date_expiration}}</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Admin Panel', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		<a href="{{membership_manage_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Go to Membership →', 'ultimate-multisite'); ?></a>
		</td>
	</tr>
	</tbody>
</table>

<h2><b><?php esc_html_e('Customer', 'ultimate-multisite'); ?></b></h2>

<table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
	<tbody>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Customer', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		{{customer_avatar}}<br />
		{{customer_name}}
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Email Address', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		<a href="mailto:{{customer_user_email}}" style="text-decoration: none;" rel="nofollow">{{customer_user_email}}</a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('ID', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
		<a href="{{customer_manage_url}}" style="text-decoration: none;" rel="nofollow">{{customer_id}}</a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Billing Address', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">{{customer_billing_address}}</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Admin Panel', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		<a href="{{customer_manage_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Go to Customer →', 'ultimate-multisite'); ?></a>
		</td>
	</tr>
	</tbody>
</table>

