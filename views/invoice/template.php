<?php
/**
 * Template invoice view.
 *
 * @since 2.0.0
 */

$has_tax_included = false;

?>
<style>
.invoice-box {
	width: 100%;
	margin: auto;
	font-size: 16px;
	line-height: 24px;
	color: #555;
}

.invoice-box table {
	width: 100%;
	line-height: inherit;
	text-align: left;
}

.invoice-box table td {
	padding: 5px;
	vertical-align: top;
}

.invoice-box table tr td:nth-child(2) {
	text-align: right;
}

.invoice-box table tr.top table td {
	padding-bottom: 20px;
}

.invoice-box table tr.top table td.title {
	font-size: 45px;
	line-height: 45px;
	color: #333;
}

.invoice-box table tr.information table td {
	padding-bottom: 40px;
}

.invoice-box table tr.heading td {
	background: #eee;
	border-bottom: 1px solid #ddd;
	font-weight: 500;
}

.invoice-box table {
	border-collapse: 1px;
}

.invoice-box table tr.heading th {
	border-left: 1px solid #ddd;
	border-right: 1px solid #ddd;
}

.invoice-box table tr.item td {
	vertical-align: middle;
}

.invoice-box table tr.heading th {
	background: #eee;
	border-top: 1px solid #ddd;
	border-bottom: 1px solid #ddd;
	padding: 10px;
	text-align: right;
	font-weight: bold;
	text-transform: uppercase;
	font-size: 80%;
}

.invoice-box table tr.details td {
	padding: 10px;
}

.invoice-box table tr.item td{
	border-bottom: 1px solid #eee;
	padding: 10px;
}

.invoice-box table tr.item.last td {
	border-bottom: none;
}

.invoice-box table tr.total td {
	border-top: 2px solid #eee;
	font-weight: bold;
	padding-bottom: 60px;
	padding-top: 10px;
	text-align: right;
}

@media only screen and (max-width: 600px) {
	.invoice-box table tr.top table td {
		width: 100%;
		display: block;
		text-align: center;
	}

	.invoice-box table tr.information table td {
		width: 100%;
		display: block;
		text-align: center;
	}
}

/** RTL **/
.rtl {
	direction: rtl;

}

.rtl table {
	text-align: right;
}

.rtl table tr td:nth-child(2) {
	text-align: left;
}

.primary-color {
	padding: 10px;
	background-color: <?php echo esc_attr($primary_color); ?>;
}
</style>

<div class="invoice-box">
	<table cellpadding="0" cellspacing="0">
		<tr class="top">
			<td colspan="5">
				<table>
					<tr>
						<td class="title">
							<?php if ($use_custom_logo && $custom_logo) : ?>
								<?php echo wp_get_attachment_image($custom_logo, 'full', false, array('style' => 'width: 100px; height: auto;')); ?>
							<?php else : ?>
								<img width="100" src="<?php echo esc_attr($logo_url); ?>" alt="<?php echo esc_attr(get_network_option(null, 'site_name')); ?>">
							<?php endif; ?>
						</td>

						<td>
							<strong><?php esc_html_e('Invoice #', 'multisite-ultimate'); ?></strong><br>
							<?php echo esc_html($payment->get_invoice_number()); ?>
							<br>
							<?php // translators: %s is the payment creation date ?>
							<?php echo esc_html(sprintf(esc_html__('Created: %s', 'multisite-ultimate'), date_i18n(get_option('date_format'), strtotime($payment->get_date_created())))); ?><br>

							<?php esc_html_e('Due on Receipt', 'multisite-ultimate'); ?><br>
						</td>
					</tr>
				</table>
			</td>
		</tr>

		<tr class="information">
			<td colspan="5">
				<table>
					<tr>
						<td>
							<strong>
								<?php

								/**
								 * Displays company name.
								 */
								echo esc_html($company_name);

								?>
							</strong>

							<br>

							<?php

							/**
							 * Displays the company address.
							 */
							echo nl2br(esc_html($company_address));

							?>
						</td>

						<td>
							<strong><?php esc_html_e('Bill to', 'multisite-ultimate'); ?></strong>
							<br>
							<?php

							/**
							 * Displays the clients address.
							 */
							echo nl2br(esc_html(implode(PHP_EOL, (array) $billing_address)));

							?>
						</td>
					</tr>
				</table>
			</td>
		</tr>

		<tr class="heading">

			<th style="text-align: left;">
				<?php esc_html_e('Item', 'multisite-ultimate'); ?>
			</th>

			<th style="width: 17%;">
				<?php esc_html_e('Price', 'multisite-ultimate'); ?>
			</th>

			<th style="width: 17%;">
				<?php esc_html_e('Discount', 'multisite-ultimate'); ?>
			</th>

			<th style="width: 17%;">
				<?php esc_html_e('Tax', 'multisite-ultimate'); ?>
			</th>

			<th style="width: 17%;">
				<?php esc_html_e('Total', 'multisite-ultimate'); ?>
			</th>

		</tr>

		<?php foreach ($line_items as $line_item) : ?>

			<tr class="item">

				<td>
					<span class="font-weight: medium;"><?php echo esc_html($line_item->get_title()); ?></span>
					<br>
					<small><?php echo wp_kses($line_item->get_description(), wu_kses_allowed_html()); ?></small>
				</td>

				<td style="text-align: right;">
					<?php echo esc_html(wu_format_currency($line_item->get_subtotal(), $payment->get_currency())); ?>
				</td>

				<td style="text-align: right;">
					<?php echo esc_html(wu_format_currency($line_item->get_discount_total(), $payment->get_currency())); ?>
				</td>

				<td style="text-align: right;">
					<?php echo esc_html(wu_format_currency($line_item->get_tax_total(), $payment->get_currency())); ?>
					<br>
					<small><?php echo esc_html($line_item->get_tax_label()); ?> (<?php echo esc_html($line_item->get_tax_rate()); ?>%)</small>
					<?php if ($line_item->get_tax_inclusive()) : ?>
						<?php $has_tax_included = true; ?>
						<small>*</small>
					<?php endif; ?>
				</td>

				<td style="text-align: right;">
					<?php echo esc_html(wu_format_currency($line_item->get_total(), $payment->get_currency())); ?>
				</td>

			</tr>

		<?php endforeach; ?>

		<tr class="total">
			<?php if ($has_tax_included) : ?>
				<td style="text-align: left; font-weight: normal;">
					<small>* <?php esc_html_e('Tax included in price.', 'multisite-ultimate'); ?></small>
				</td>
			<?php endif; ?>
			<td colspan='5'>

				<?php // translators: %s is the total amount in currency format. ?>
				<?php printf(esc_html__('Total: %s', 'multisite-ultimate'), esc_html(wu_format_currency($payment->get_total(), $payment->get_currency()))); ?>
			</td>
		</tr>

		<?php if ( ! $payment->is_payable()) : ?>

			<tr class="heading">
				<th colspan="5" style="text-align: left;">
					<?php esc_html_e('Payment Method', 'multisite-ultimate'); ?>
				</th>
			</tr>

			<tr class="details">
				<td colspan="5">
					<?php echo esc_html($payment->get_payment_method()); ?>
				</td>
			</tr>

		<?php endif; ?>
	</table>
</div>
