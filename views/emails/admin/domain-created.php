<?php
/**
 * Domain Mapping Created Email Template
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
?>
<p><?php esc_html_e('Hey there', 'ultimate-multisite'); ?></p>
<?php // translators: %1$s: Customer Name, %2$s: Domain, %3$s: Site Title ?>
<p><?php printf(esc_html__('A new domain, %2$s, was added to the site %3$s.', 'ultimate-multisite'), '{{customer_name}}', '{{domain_domain}}', '{{site_title}}'); ?></p>

<h2><b><?php esc_html_e('Domain', 'ultimate-multisite'); ?></b></h2>

<table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
	<tbody>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Domain', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee; border: 1px solid #eee;">
		{{domain_domain}}
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('ID', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
		<a href="{{domain_admin_url}}" style="text-decoration: none;" rel="nofollow">{{domain_id}}</a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Domain Stage', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
		<code>{{domain_stage}}</code>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Active', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
		<code>{{domain_active}}</code>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Primary', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
		<code>{{domain_primary}}</code>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Secure', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
		<code>{{domain_secure}}</code>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Admin Panel', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		<a href="{{domain_manage_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Go to Domain &rarr;', 'ultimate-multisite'); ?></a>
		</td>
	</tr>
	</tbody>
</table>

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
		<a href="{{site_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Visit Site &rarr;', 'ultimate-multisite'); ?></a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Site Admin Panel', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		<a href="{{site_admin_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Visit Admin Panel &rarr;', 'ultimate-multisite'); ?></a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Admin Panel', 'ultimate-multisite'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		<a href="{{site_manage_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Go to Site Management &rarr;', 'ultimate-multisite'); ?></a>
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
		<a href="{{membership_manage_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Go to Membership &rarr;', 'ultimate-multisite'); ?></a>
		</td>
	</tr>
	</tbody>
</table>
