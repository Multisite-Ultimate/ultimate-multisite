<?php
/**
 * Payment Received Email Template
 *
 * @since 2.0.19
 */
?>
<?php // translators: %s: Customer Name ?>
<p><?php printf(esc_html__('Hey %s,', 'multisite-ultimate'), '{{customer_name}}'); ?></p>
<?php // translators: %1$s: total payment ammount. ?>
<p><?php printf(esc_html__('You have a new pending payment of %1$s for your membership.', 'multisite-ultimate'), '{{payment_total}}'); ?></p>

<p><a href="{{default_payment_url}}" style="text-decoration: none;" rel="nofollow"><?php esc_html_e('Pay Now', 'multisite-ultimate'); ?></a></p>

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
		<td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php esc_html_e('Created at', 'multisite-ultimate'); ?></b></td>
		<td style="padding: 8px; background: #fff; border: 1px solid #eee;">{{payment_date_created}}</td>
	</tr>
	</tbody>
</table>
