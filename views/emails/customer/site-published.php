<?php
/**
 * Site Published Email Template - Customer
 *
 * @since 2.0.0
 */
?>
<?php // translators: %s: Customer Name ?>
<p><?php printf(esc_html__('Hey %s,', 'multisite-ultimate'), '{{customer_name}}'); ?></p>
<?php // translators: %1$s: Site Title, %2$s: Site Url ?>
<p><?php echo wp_kses(sprintf(__('We have great news! The site <b>%1$s</b> (%2$s) was created successfully and is ready!', 'multisite-ultimate'), '{{site_title}}', '<a href="{{site_url}}" style="text-decoration: none;" rel="nofollow">{{site_url}}</a>'), 'pre_user_description'); ?></p>

<h2><b><?php esc_html_e('Your Site', 'multisite-ultimate'); ?></b></h2>

<table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
	<tbody>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Title', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee; border: 1px solid #eee;">
		{{site_title}}
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('URL', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		<a href="{{site_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Visit Site →', 'multisite-ultimate'); ?></a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Admin Panel', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		<a href="{{site_admin_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Visit Admin Panel →', 'multisite-ultimate'); ?></a>
		</td>
	</tr>
	</tbody>
</table>
