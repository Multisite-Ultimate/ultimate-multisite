<form id="wu_form" method="post" class="wu-styling wu-relative">

	<?php

	/**
	 * Instantiate the form for the order details.
	 *
	 * @since 2.0.0
	 */
	$order_form = new \WP_Ultimo\UI\Form(
		'product-fields',
		$product_fields,
		[
			'title' => __('Products', 'multisite-ultimate'),
			'views' => 'checkout/fields',
		]
	);

	/**
	* Render form fields.
	*
	* @see /view/checkout/fields/ for the template files for each field type.
	* @since 2.0.0
	*/
	$order_form->render();

	/**
	 * Instantiate the form for the submit button and such.
	 *
	 * @since 2.0.0
	 */
	$submit = new \WP_Ultimo\UI\Form('submit-fields', $submit_fields, ['views' => 'checkout/fields']);

	/**
	 * Render form fields.
	 *
	 * @see /view/checkout/fields/ for the template files for each field type.
	 * @since 2.0.0
	 */
	$submit->render();

	/**
	 * Add a security nonce field.
	 */
	wp_nonce_field('wu_checkout');

	?>

</form>
