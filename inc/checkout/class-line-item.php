<?php
/**
 * Creates a cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Order
 * @since 2.0.0
 */

namespace WP_Ultimo\Checkout;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Database\Payments\Payment_Status;
use WP_Ultimo\Models\Product;

/**
 * Creates an cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 * @since 2.0.0
 */
class Line_Item implements \JsonSerializable {

	/**
	 * The hash to be used as a base to deal with id.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $hash;

	/**
	 * The id of the line item.
	 *
	 * It starts with LN_TYPE.
	 * E.g. LN_FEE_#HASH.
	 * Should not be set manually.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id;

	/**
	 * The line item type.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $type = 'product'; // discount, fee, credit, proration

	/**
	 * The product id, if any.
	 *
	 * @since 2.0.0
	 * @var null|int
	 */
	protected $product_id;

	/**
	 * The product slug, if any.
	 *
	 * @since 2.0.0
	 * @var null|string
	 */
	protected $product_slug;

	/**
	 * The discount code id, if any.
	 *
	 * @since 2.0.0
	 * @var null|int
	 */
	protected $discount_code_id;

	/**
	 * The title of the line item.
	 *
	 * Usually the name of the product.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $title = '';

	/**
	 * The description of the line item.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $description = '';

	/**
	 * Should we apply discounts to this line item?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $discountable = false;

	/**
	 * Should we apply taxes to this line item?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $taxable = false;

	/**
	 * Is this line item recurring?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $recurring = false;

	/**
	 * The billing cycle duration, if recurring.
	 *
	 * @since 2.0.0
	 * @var integer
	 */
	protected $duration = 1;

	/**
	 * The billing cycle duration unit, if recurring.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $duration_unit = 'month';

	/**
	 * The number of billing cycles, if recurring.
	 *
	 * @since 2.0.0
	 * @var integer
	 */
	protected $billing_cycles = 0;

	/**
	 * Quantity of the given product.
	 *
	 * @since 2.0.0
	 * @var float
	 */
	protected $quantity = 1;

	/**
	 * Unit price of the product.
	 *
	 * @since 2.0.0
	 * @var integer
	 */
	protected $unit_price = 0;

	/**
	 * Value before taxes, discounts, fees and etc.
	 *
	 * @since 2.0.0
	 * @var float
	 */
	protected $subtotal = 0;

	/**
	 * The value of the discount being applied.
	 *
	 * @since 2.0.0
	 * @var float
	 */
	protected $discount_rate = 0;

	/**
	 * The discount type.
	 *
	 * Percentage or absolute (for flat discounts)
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $discount_type = 'percentage';

	/**
	 * Discount Label.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $discount_label = '';

	/**
	 * If we should apply discount to renewals.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $apply_discount_to_renewals = true;

	/**
	 * The total value in discounts.
	 *
	 * @since 2.0.0
	 * @var integer
	 */
	protected $discount_total = 0;

	/**
	 * The tax category of the line item.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $tax_category = '';

	/**
	 * Label of the tax applied.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $tax_label = '';

	/**
	 * Type of the tax, percentage or absolute.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $tax_type = 'percentage';

	/**
	 * Tax amount, absolute or percentage.
	 *
	 * @since 2.0.0
	 * @var float
	 */
	protected $tax_rate = 0;

	/**
	 * If tax are included in the price or not.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $tax_inclusive = false;

	/**
	 * If the line item is tax exempt ot not.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $tax_exempt = false;

	/**
	 * The amount, in currency, of the tax.
	 *
	 * @since 2.0.0
	 * @var float
	 */
	protected $tax_total = 0;

	/**
	 * The total amount of the line item.
	 *
	 * @since 2.0.0
	 * @var integer
	 */
	protected $total = 0;

	/**
	 * The date the line item was created.
	 *
	 * @since 2.2.0
	 * @var string
	 */
	protected $date_created;

	/**
	 * Instantiate the class.
	 *
	 * @since 2.0.0
	 *
	 * @param array $atts Line item attributes.
	 */
	public function __construct($atts) {

		$this->attributes($atts);

		/*
		* Refresh totals.
		*/
		$this->recalculate_totals();
	}

	/**
	 * Loops through allowed fields and loads them.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Array of key => values billing address fields.
	 * @return void
	 */
	public function attributes($data): void {
		/*
		* Set type first to allow for overriding the other parameters.
		*/
		$type = wu_get_isset($data, 'type');

		if ($type) {
			$this->set_type($data['type']);
		}

		/*
		* Set product first to allow for overriding the other parameters.
		*/
		$product = wu_get_isset($data, 'product');

		if ($product) {
			$this->set_product($data['product']);

			unset($data['product']);
		}

		$allowed_attributes = array_keys(get_object_vars($this));

		foreach ($data as $key => $value) {
			if (in_array($key, $allowed_attributes, true)) {
				$this->{$key} = $value;
			}
		}

		$this->id = 'LN_' . strtoupper($this->type) . '_' . $this->hash;
	}

	/**
	 * Get the value of id
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_id() {

		return $this->id;
	}

	/**
	 * Get the value of type
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_type() {

		return $this->type;
	}

	/**
	 * Set the value of type.
	 *
	 * Accepted values: product, fee, credit, discount, prorate.
	 *
	 * @since 2.0.0
	 * @param string $type The line item type.
	 * @return void
	 */
	public function set_type($type): void {

		$this->type = $type;
	}

	/**
	 * Get product associated with this line item.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Product|false
	 */
	public function get_product() {

		$product = wu_get_product($this->product_id);

		if ( ! $product) {
			return false;
		}

		if ($product->is_recurring() && ($product->get_duration_unit() !== $this->duration_unit || $product->get_duration() !== $this->duration)) {
			$product_variation = $product->get_as_variation($this->duration, $this->duration_unit);

			/*
			 * Checks if the variation exists before re-setting the product.
			 */
			if ($product_variation) {
				$product = $product_variation;
			}
		}

		return $product;
	}

	/**
	 * Set product associated with this line item.
	 *
	 * @since 2.0.0
	 * @param Product $product Product associated with this line item.
	 * @return void
	 */
	public function set_product($product): void {

		$this->product_id = $product->get_id();

		$this->hash = $product->get_hash();

		$this->title = $product->get_name();

		$this->description = $product->get_description();

		$this->unit_price = $product->get_amount();

		$this->recurring = $product->is_recurring();

		$this->duration = $product->get_duration();

		$this->duration_unit = $product->get_duration_unit();

		$this->billing_cycles = $product->get_billing_cycles();

		$this->taxable = $product->is_taxable();

		$this->tax_category = $product->get_tax_category();

		$this->discountable = true;
	}

	/**
	 * Calculate the taxes value based on the tax_amount and tax_type.
	 *
	 * @since 2.0.0
	 * @param float $sub_total Value to apply taxes to.
	 * @return float
	 */
	public function calculate_taxes($sub_total) {

		return wu_get_tax_amount($sub_total, $this->get_tax_rate(), $this->get_tax_type(), false, $this->get_tax_inclusive());
	}

	/**
	 * Calculate the discounts value based on the discount_amount and discount_type.
	 *
	 * @since 2.0.0
	 * @param float $sub_total Value to apply discounts to.
	 * @return float
	 */
	public function calculate_discounts($sub_total) {

		return wu_get_tax_amount($sub_total, $this->get_discount_rate(), $this->get_discount_type(), false);
	}

	/**
	 * Recalculate payment totals.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Checkout\Line_Item
	 */
	public function recalculate_totals() {

		$sub_total = $this->get_quantity() * $this->get_unit_price();

		$discounts = $this->calculate_discounts($sub_total);

		$discounted_subtotal = $sub_total - $discounts;

		if ($sub_total > 0 && $discounted_subtotal < 0) {
			$discounted_subtotal = 0;

			$discounts = $sub_total;
		}

		$taxes = $this->calculate_taxes($discounted_subtotal);

		if ($this->get_tax_inclusive()) {
			$total = $this->is_tax_exempt() ? $discounted_subtotal - $taxes : $discounted_subtotal;
		} else {
			$total = $this->is_tax_exempt() ? $discounted_subtotal : $discounted_subtotal + $taxes; // tax exclusive

		}

		if ($this->is_tax_exempt()) {
			$taxes = 0;
		}

		$totals = [
			'subtotal'       => $sub_total,
			'discount_total' => $discounts,
			'tax_total'      => $taxes,
			'total'          => $total,
		];

		$this->attributes($totals);

		return $this;
	}

	/**
	 * Get quantity of the given product.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_quantity() {

		return $this->quantity;
	}

	/**
	 * Set quantity of the given product.
	 *
	 * @since 2.0.0
	 * @param float $quantity Quantity of the given product.
	 * @return void
	 */
	public function set_quantity($quantity): void {

		$this->quantity = $quantity;
	}

	/**
	 * Get unit price of the product.
	 *
	 * @since 2.0.0
	 * @return integer
	 */
	public function get_unit_price() {

		return $this->unit_price;
	}

	/**
	 * Set unit price of the product.
	 *
	 * @since 2.0.0
	 * @param integer $unit_price Unit price of the product.
	 * @return void
	 */
	public function set_unit_price($unit_price): void {

		$this->unit_price = $unit_price;
	}

	/**
	 * Get tax amount, absolute or percentage.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_tax_rate() {

		return $this->tax_rate;
	}

	/**
	 * Set tax amount, absolute or percentage.
	 *
	 * @since 2.0.0
	 * @param float $tax_rate Tax amount, absolute or percentage.
	 * @return void
	 */
	public function set_tax_rate($tax_rate): void {

		$this->tax_rate = $tax_rate;
	}

	/**
	 * Get type of the tax, percentage or absolute.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_tax_type() {

		return $this->tax_type;
	}

	/**
	 * Set type of the tax, percentage or absolute.
	 *
	 * @since 2.0.0
	 * @param string $tax_type Type of the tax, percentage or absolute.
	 * @return void
	 */
	public function set_tax_type($tax_type): void {

		$this->tax_type = $tax_type;
	}

	/**
	 * Get if tax are included in the price or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function get_tax_inclusive() {

		return (bool) $this->tax_inclusive;
	}

	/**
	 * Set if tax are included in the price or not.
	 *
	 * @since 2.0.0
	 * @param boolean $tax_inclusive If tax are included in the price or not.
	 * @return void
	 */
	public function set_tax_inclusive($tax_inclusive): void {

		$this->tax_inclusive = $tax_inclusive;
	}

	/**
	 * Get if the line item is tax exempt ot not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_tax_exempt() {

		return $this->tax_exempt;
	}

	/**
	 * Set if the line item is tax exempt ot not.
	 *
	 * @since 2.0.0
	 * @param boolean $tax_exempt If the line item is tax exempt ot not.
	 * @return void
	 */
	public function set_tax_exempt($tax_exempt): void {

		$this->tax_exempt = $tax_exempt;
	}

	/**
	 * Get the amount, in currency, of the tax.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_tax_total() {

		return $this->tax_total;
	}

	/**
	 * Set the amount, in currency, of the tax.
	 *
	 * @since 2.0.0
	 * @param float $tax_total The amount, in currency, of the tax.
	 * @return void
	 */
	public function set_tax_total($tax_total): void {

		$this->tax_total = $tax_total;
	}

	/**
	 * Get the value of total
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_total() {

		return $this->total;
	}

	/**
	 * Set the value of total.
	 *
	 * @since 2.0.0
	 * @param float $total The total value of the line.
	 * @return void
	 */
	public function set_total($total): void {

		$this->total = $total;
	}

	/**
	 * Get the value of recurring.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_recurring() {

		return $this->recurring;
	}

	/**
	 * Set the value of recurring.
	 *
	 * @since 2.0.0
	 * @param boolean $recurring If this item is recurring or not.
	 * @return void
	 */
	public function set_recurring($recurring): void {

		$this->recurring = $recurring;
	}

	/**
	 * Get value before taxes, discounts, fees and etc.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_subtotal() {

		return $this->subtotal;
	}

	/**
	 * Set value before taxes, discounts, fees and etc.
	 *
	 * @since 2.0.0
	 * @param float $subtotal Value before taxes, discounts, fees and etc.
	 * @return void
	 */
	public function set_subtotal($subtotal): void {

		$this->subtotal = $subtotal;
	}

	/**
	 * Get the value of duration
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_duration() {

		return $this->duration;
	}

	/**
	 * Set the value of duration.
	 *
	 * @since 2.0.0
	 * @param int $duration The billing cycle duration.
	 * @return void
	 */
	public function set_duration($duration): void {

		$this->duration = $duration;
	}

	/**
	 * Get the value of duration_unit.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_duration_unit() {

		return $this->duration_unit;
	}

	/**
	 * Set the value of duration_unit.
	 *
	 * @since 2.0.0
	 * @param string $duration_unit The duration unit.
	 * @return void
	 */
	public function set_duration_unit($duration_unit): void {

		$this->duration_unit = $duration_unit;
	}

	/**
	 * Get the value of billing_cycles.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_billing_cycles() {

		return $this->billing_cycles;
	}

	/**
	 * Set the value of billing_cycles.
	 *
	 * @since 2.0.0
	 * @param int $billing_cycles The number of billing cycles.
	 * @return void
	 */
	public function set_billing_cycles($billing_cycles): void {

		$this->billing_cycles = $billing_cycles;
	}

	/**
	 * Get the value of discount_total.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_discount_total() {

		return $this->discount_total;
	}

	/**
	 * Set the value of discount_total.
	 *
	 * @since 2.0.0
	 * @param float $discount_total The total value of discounts.
	 * @return void
	 */
	public function set_discount_total($discount_total): void {

		$this->discount_total = $discount_total;
	}

	/**
	 * Get the value of tax_category.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_tax_category() {

		return $this->tax_category;
	}

	/**
	 * Set the value of tax_category.
	 *
	 * @since 2.0.0
	 * @param string $tax_category The tax category.
	 * @return void
	 */
	public function set_tax_category($tax_category): void {

		$this->tax_category = $tax_category;
	}

	/**
	 * Get the value of discountable.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_discountable() {

		return $this->discountable;
	}

	/**
	 * Set the value of discountable.
	 *
	 * @since 2.0.0
	 * @param boolean $discountable If the line is discountable.
	 * @return void
	 */
	public function set_discountable($discountable): void {

		$this->discountable = $discountable;
	}

	/**
	 * Get the value of taxable.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_taxable() {

		return $this->taxable;
	}

	/**
	 * Set the value of taxable.
	 *
	 * @since 2.0.0
	 * @param boolean $taxable If the item is taxable or not.
	 * @return void
	 */
	public function set_taxable($taxable): void {

		$this->taxable = $taxable;
	}

	/**
	 * Get the value of discount_rate.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_discount_rate() {

		return $this->discount_rate;
	}

	/**
	 * Set the value of discount_rate.
	 *
	 * @since 2.0.0
	 * @param float $discount_rate The discount amount (flat or percentage).
	 * @return void
	 */
	public function set_discount_rate($discount_rate): void {

		$this->discount_rate = $discount_rate;
	}

	/**
	 * Get the value of discount_type.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_discount_type() {

		return $this->discount_type;
	}

	/**
	 * Set the value of discount_type.
	 *
	 * @since 2.0.0
	 * @param string $discount_type The type of discount, percentage or absolute.
	 * @return void
	 */
	public function set_discount_type($discount_type): void {

		$this->discount_type = $discount_type;
	}

	/**
	 * Get discount Label.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_discount_label() {

		return $this->discount_label;
	}

	/**
	 * Set discount Label.
	 *
	 * @since 2.0.0
	 * @param string $discount_label Discount Label.
	 * @return void
	 */
	public function set_discount_label($discount_label): void {

		$this->discount_label = $discount_label;
	}

	/**
	 * Get if we should apply discount to renewals.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function should_apply_discount_to_renewals() {

		return $this->apply_discount_to_renewals;
	}

	/**
	 * Set if we should apply discount to renewals.
	 *
	 * @since 2.0.0
	 * @param boolean $apply_discount_to_renewals If we should apply discount to renewals.
	 * @return void
	 */
	public function set_apply_discount_to_renewals($apply_discount_to_renewals): void {

		$this->apply_discount_to_renewals = $apply_discount_to_renewals;
	}

	/**
	 * Get the value of product_id.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_product_id() {

		return $this->product_id;
	}

	/**
	 * Set the value of product_id.
	 *
	 * @since 2.0.0
	 * @param int $product_id The product id.
	 * @return void
	 */
	public function set_product_id($product_id): void {

		$this->product_id = $product_id;
	}

	/**
	 * Get the value of title
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return $this->title;
	}

	/**
	 * Set the value of title.
	 *
	 * @since 2.0.0
	 * @param string $title The line item title.
	 * @return void
	 */
	public function set_title($title): void {

		$this->title = $title;
	}

	/**
	 * Get the value of description.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return $this->description;
	}

	/**
	 * Set the value of description.
	 *
	 * @since 2.0.0
	 * @param string $description The line item description.
	 * @return void
	 */
	public function set_description($description): void {

		$this->description = $description;
	}

	/**
	 * Get label of the tax applied.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_tax_label() {

		return $this->tax_label;
	}

	/**
	 * Set label of the tax applied.
	 *
	 * @since 2.0.0
	 * @param string $tax_label Label of the tax applied.
	 * @return void
	 */
	public function set_tax_label($tax_label): void {

		$this->tax_label = $tax_label;
	}

	/**
	 * @return string
	 */
	public function get_date_created(): string {
		return $this->date_created;
	}

	/**
	 * Returns the amount recurring in a human-friendly way.
	 *
	 * @since 2.0.8
	 * @return string
	 */
	public function get_recurring_description() {

		if ( ! $this->is_recurring()) {
			return '';
		}

		$description = sprintf(
			// translators: %1$s the duration, and %2$s the duration unit (day, week, month, etc)
			_n('%2$s', 'every %1$s %2$s', $this->get_duration(), 'multisite-ultimate'), // phpcs:ignore
			$this->get_duration(),
			wu_get_translatable_string(($this->get_duration() <= 1 ? $this->get_duration_unit() : $this->get_duration_unit() . 's'))
		);

		return $description;
	}

	/**
	 * Converts the line item to an array.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_array() {

		$array = get_object_vars($this);

		$array['recurring_description'] = $this->get_recurring_description();

		return $array;
	}

	/**
	 * Implements our on json_decode version of this object. Useful for use in vue.js.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {

		return $this->to_array();
	}

	/**
	 * Queries the database for Line Items across payments.
	 *
	 * @since 2.0.0
	 *
	 * @param array $query Query arguments.
	 * @return array
	 */
	public static function get_line_items($query = []) {

		global $wpdb;

		$query = wp_parse_args(
			$query,
			[
				'number'         => 100,
				'date_query'     => [],
				'payment_status' => false,
			]
		);

		$query['date_query']['column'] = 'p.date_created';

		$date_query = new \WP_Date_Query($query['date_query']);

		$date_query_sql = $date_query->get_sql();

		$taxes_paid_list = [];

		$status_query_sql = '';

		if ($query['payment_status'] && (new Payment_Status())->is_valid($query['payment_status'])) {
			$status_query_sql = "AND p.status = '{$query['payment_status']}'";
		}

		// phpcs:disable;
		$query = $wpdb->prepare( "
			SELECT m.wu_payment_id, m.meta_value as line_items, p.date_created
			FROM {$wpdb->base_prefix}wu_paymentmeta as m
			JOIN {$wpdb->base_prefix}wu_payments as p
			WHERE 
				m.meta_key = %s 
			AND 
				p.id = m.wu_payment_id
			{$status_query_sql}
			{$date_query_sql}
			ORDER BY p.date_created DESC
			LIMIT %d
		", 'wu_line_items', $query['number']);
		// phpcs:enable;

		$results = $wpdb->get_results($query); // phpcs:ignore

		foreach ($results as &$ln) {
			$copy = $ln;

			$line_items = $ln->line_items;

			$ln = maybe_unserialize($line_items);

			$ln = array_map(
				function ($_ln) use ($copy) {

					$_ln->date_created = $copy->date_created;

					return $_ln;
				},
				$ln
			);
		}

		return $results;
	}

	/**
	 * Get the product slug, if any.
	 *
	 * @since 2.1.0
	 * @return null|string
	 */
	public function get_product_slug() {

		return $this->product_slug;
	}

	/**
	 * Set the product slug.
	 *
	 * @since 2.1.0
	 * @param null|string $product_slug The product slug.
	 * @return void
	 */
	public function set_product_slug($product_slug): void {

		$this->product_slug = $product_slug;
	}
}
