<?php
/**
 * Payment Received Email Template
 *
 * @since 2.0.0
 */
?>
<p><?php esc_html_e('Hey there', 'multisite-ultimate'); ?></p>
<?php // translators: %1$s: Customer name, %2$s: Customer email, %3$s: Customer user email, %4$s: Payment total. ?>
<p><?php printf(esc_html__('We have great news! You received %1$s from %2$s (%3$s) for %4$s.', 'multisite-ultimate'), '{{payment_total}}', '{{customer_name}}', '{{customer_user_email}}', '{{payment_product_names}}'); ?></p>

<p><a href="{{payment_invoice_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Download Invoice', 'multisite-ultimate'); ?></a></p>

<h2><b><?php esc_html_e('Payment', 'multisite-ultimate'); ?></b></h2>

<table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
	<tbody>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Products', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee; border: 1px solid #eee;">
		{{payment_product_names}}
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Subtotal', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		{{payment_subtotal}}
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Tax', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		{{payment_tax_total}}
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Total', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		{{payment_total}}
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Paid with', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">{{payment_gateway}}</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('ID', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
		<a href="{{payment_manage_url}}" style="text-decoration: none;" rel="nofollow">{{payment_id}}</a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Reference Code', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
		<a href="{{payment_manage_url}}" style="text-decoration: none;" rel="nofollow">{{payment_reference_code}}</a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Processed at', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">{{payment_date_created}}</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9;"><b><?php esc_html_e('Invoice', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
		<a href="{{payment_invoice_url}}" style="text-decoration: none;" rel="nofollow">
			<?php esc_html_e('Download PDF', 'multisite-ultimate'); ?>
		</a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Type', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">Initial Payment</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Admin Panel', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		<a href="{{payment_manage_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Go to Payment &rarr;', 'multisite-ultimate'); ?></a>
		</td>
	</tr>
	</tbody>
</table>

<h2><b><?php esc_html_e('Membership', 'multisite-ultimate'); ?></b></h2>

<table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
	<tbody>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Amount', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		{{membership_description}}
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Initial Amount', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		{{membership_initial_amount}}
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('ID', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
		<a href="{{membership_manage_url}}" style="text-decoration: none;" rel="nofollow">{{membership_id}}</a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Reference Code', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
		<a href="{{membership_manage_url}}" style="text-decoration: none;" rel="nofollow">{{membership_reference_code}}</a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Expiration', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">{{membership_date_expiration}}</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Admin Panel', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		<a href="{{membership_manage_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Go to Membership &rarr;', 'multisite-ultimate'); ?></a>
		</td>
	</tr>
	</tbody>
</table>

<h2><b><?php esc_html_e('Customer', 'multisite-ultimate'); ?></b></h2>

<table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
	<tbody>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Customer', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		{{customer_avatar}}<br />
		{{customer_name}}
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Email Address', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		<a href="mailto:{{customer_user_email}}" style="text-decoration: none;" rel="nofollow">{{customer_user_email}}</a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('ID', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
		<a href="{{customer_manage_url}}" style="text-decoration: none;" rel="nofollow">{{customer_id}}</a>
		</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Billing Address', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">{{customer_billing_address}}</td>
	</tr>
	<tr>
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Admin Panel', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">
		<a href="{{customer_manage_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Go to Customer &rarr;', 'multisite-ultimate'); ?></a>
		</td>
	</tr>
	</tbody>
</table>
