<?php
/**
 * Country Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns the list of countries.
 *
 * @since 2.0.0
 * @return array
 */
function wu_get_countries() {

	return apply_filters(
		'wu_get_countries',
		[
			'AF' => __('Afghanistan', 'multisite-ultimate'),
			'AX' => __('&#197;land Islands', 'multisite-ultimate'),
			'AL' => __('Albania', 'multisite-ultimate'),
			'DZ' => __('Algeria', 'multisite-ultimate'),
			'AS' => __('American Samoa', 'multisite-ultimate'),
			'AD' => __('Andorra', 'multisite-ultimate'),
			'AO' => __('Angola', 'multisite-ultimate'),
			'AI' => __('Anguilla', 'multisite-ultimate'),
			'AQ' => __('Antarctica', 'multisite-ultimate'),
			'AG' => __('Antigua and Barbuda', 'multisite-ultimate'),
			'AR' => __('Argentina', 'multisite-ultimate'),
			'AM' => __('Armenia', 'multisite-ultimate'),
			'AW' => __('Aruba', 'multisite-ultimate'),
			'AU' => __('Australia', 'multisite-ultimate'),
			'AT' => __('Austria', 'multisite-ultimate'),
			'AZ' => __('Azerbaijan', 'multisite-ultimate'),
			'BS' => __('Bahamas', 'multisite-ultimate'),
			'BH' => __('Bahrain', 'multisite-ultimate'),
			'BD' => __('Bangladesh', 'multisite-ultimate'),
			'BB' => __('Barbados', 'multisite-ultimate'),
			'BY' => __('Belarus', 'multisite-ultimate'),
			'BE' => __('Belgium', 'multisite-ultimate'),
			'PW' => __('Belau', 'multisite-ultimate'),
			'BZ' => __('Belize', 'multisite-ultimate'),
			'BJ' => __('Benin', 'multisite-ultimate'),
			'BM' => __('Bermuda', 'multisite-ultimate'),
			'BT' => __('Bhutan', 'multisite-ultimate'),
			'BO' => __('Bolivia', 'multisite-ultimate'),
			'BQ' => __('Bonaire, Saint Eustatius and Saba', 'multisite-ultimate'),
			'BA' => __('Bosnia and Herzegovina', 'multisite-ultimate'),
			'BW' => __('Botswana', 'multisite-ultimate'),
			'BV' => __('Bouvet Island', 'multisite-ultimate'),
			'BR' => __('Brazil', 'multisite-ultimate'),
			'IO' => __('British Indian Ocean Territory', 'multisite-ultimate'),
			'VG' => __('British Virgin Islands', 'multisite-ultimate'),
			'BN' => __('Brunei', 'multisite-ultimate'),
			'BG' => __('Bulgaria', 'multisite-ultimate'),
			'BF' => __('Burkina Faso', 'multisite-ultimate'),
			'BI' => __('Burundi', 'multisite-ultimate'),
			'KH' => __('Cambodia', 'multisite-ultimate'),
			'CM' => __('Cameroon', 'multisite-ultimate'),
			'CA' => __('Canada', 'multisite-ultimate'),
			'CV' => __('Cape Verde', 'multisite-ultimate'),
			'KY' => __('Cayman Islands', 'multisite-ultimate'),
			'CF' => __('Central African Republic', 'multisite-ultimate'),
			'TD' => __('Chad', 'multisite-ultimate'),
			'CL' => __('Chile', 'multisite-ultimate'),
			'CN' => __('China', 'multisite-ultimate'),
			'CX' => __('Christmas Island', 'multisite-ultimate'),
			'CC' => __('Cocos (Keeling) Islands', 'multisite-ultimate'),
			'CO' => __('Colombia', 'multisite-ultimate'),
			'KM' => __('Comoros', 'multisite-ultimate'),
			'CG' => __('Congo (Brazzaville)', 'multisite-ultimate'),
			'CD' => __('Congo (Kinshasa)', 'multisite-ultimate'),
			'CK' => __('Cook Islands', 'multisite-ultimate'),
			'CR' => __('Costa Rica', 'multisite-ultimate'),
			'HR' => __('Croatia', 'multisite-ultimate'),
			'CU' => __('Cuba', 'multisite-ultimate'),
			'CW' => __('Cura&ccedil;ao', 'multisite-ultimate'),
			'CY' => __('Cyprus', 'multisite-ultimate'),
			'CZ' => __('Czech Republic', 'multisite-ultimate'),
			'DK' => __('Denmark', 'multisite-ultimate'),
			'DJ' => __('Djibouti', 'multisite-ultimate'),
			'DM' => __('Dominica', 'multisite-ultimate'),
			'DO' => __('Dominican Republic', 'multisite-ultimate'),
			'EC' => __('Ecuador', 'multisite-ultimate'),
			'EG' => __('Egypt', 'multisite-ultimate'),
			'SV' => __('El Salvador', 'multisite-ultimate'),
			'GQ' => __('Equatorial Guinea', 'multisite-ultimate'),
			'ER' => __('Eritrea', 'multisite-ultimate'),
			'EE' => __('Estonia', 'multisite-ultimate'),
			'ET' => __('Ethiopia', 'multisite-ultimate'),
			'FK' => __('Falkland Islands', 'multisite-ultimate'),
			'FO' => __('Faroe Islands', 'multisite-ultimate'),
			'FJ' => __('Fiji', 'multisite-ultimate'),
			'FI' => __('Finland', 'multisite-ultimate'),
			'FR' => __('France', 'multisite-ultimate'),
			'GF' => __('French Guiana', 'multisite-ultimate'),
			'PF' => __('French Polynesia', 'multisite-ultimate'),
			'TF' => __('French Southern Territories', 'multisite-ultimate'),
			'GA' => __('Gabon', 'multisite-ultimate'),
			'GM' => __('Gambia', 'multisite-ultimate'),
			'GE' => __('Georgia', 'multisite-ultimate'),
			'DE' => __('Germany', 'multisite-ultimate'),
			'GH' => __('Ghana', 'multisite-ultimate'),
			'GI' => __('Gibraltar', 'multisite-ultimate'),
			'GR' => __('Greece', 'multisite-ultimate'),
			'GL' => __('Greenland', 'multisite-ultimate'),
			'GD' => __('Grenada', 'multisite-ultimate'),
			'GP' => __('Guadeloupe', 'multisite-ultimate'),
			'GU' => __('Guam', 'multisite-ultimate'),
			'GT' => __('Guatemala', 'multisite-ultimate'),
			'GG' => __('Guernsey', 'multisite-ultimate'),
			'GN' => __('Guinea', 'multisite-ultimate'),
			'GW' => __('Guinea-Bissau', 'multisite-ultimate'),
			'GY' => __('Guyana', 'multisite-ultimate'),
			'HT' => __('Haiti', 'multisite-ultimate'),
			'HM' => __('Heard Island and McDonald Islands', 'multisite-ultimate'),
			'HN' => __('Honduras', 'multisite-ultimate'),
			'HK' => __('Hong Kong', 'multisite-ultimate'),
			'HU' => __('Hungary', 'multisite-ultimate'),
			'IS' => __('Iceland', 'multisite-ultimate'),
			'IN' => __('India', 'multisite-ultimate'),
			'ID' => __('Indonesia', 'multisite-ultimate'),
			'IR' => __('Iran', 'multisite-ultimate'),
			'IQ' => __('Iraq', 'multisite-ultimate'),
			'IE' => __('Ireland', 'multisite-ultimate'),
			'IM' => __('Isle of Man', 'multisite-ultimate'),
			'IL' => __('Israel', 'multisite-ultimate'),
			'IT' => __('Italy', 'multisite-ultimate'),
			'CI' => __('Ivory Coast', 'multisite-ultimate'),
			'JM' => __('Jamaica', 'multisite-ultimate'),
			'JP' => __('Japan', 'multisite-ultimate'),
			'JE' => __('Jersey', 'multisite-ultimate'),
			'JO' => __('Jordan', 'multisite-ultimate'),
			'KZ' => __('Kazakhstan', 'multisite-ultimate'),
			'KE' => __('Kenya', 'multisite-ultimate'),
			'KI' => __('Kiribati', 'multisite-ultimate'),
			'KW' => __('Kuwait', 'multisite-ultimate'),
			'KG' => __('Kyrgyzstan', 'multisite-ultimate'),
			'LA' => __('Laos', 'multisite-ultimate'),
			'LV' => __('Latvia', 'multisite-ultimate'),
			'LB' => __('Lebanon', 'multisite-ultimate'),
			'LS' => __('Lesotho', 'multisite-ultimate'),
			'LR' => __('Liberia', 'multisite-ultimate'),
			'LY' => __('Libya', 'multisite-ultimate'),
			'LI' => __('Liechtenstein', 'multisite-ultimate'),
			'LT' => __('Lithuania', 'multisite-ultimate'),
			'LU' => __('Luxembourg', 'multisite-ultimate'),
			'MO' => __('Macao S.A.R., China', 'multisite-ultimate'),
			'MK' => __('Macedonia', 'multisite-ultimate'),
			'MG' => __('Madagascar', 'multisite-ultimate'),
			'MW' => __('Malawi', 'multisite-ultimate'),
			'MY' => __('Malaysia', 'multisite-ultimate'),
			'MV' => __('Maldives', 'multisite-ultimate'),
			'ML' => __('Mali', 'multisite-ultimate'),
			'MT' => __('Malta', 'multisite-ultimate'),
			'MH' => __('Marshall Islands', 'multisite-ultimate'),
			'MQ' => __('Martinique', 'multisite-ultimate'),
			'MR' => __('Mauritania', 'multisite-ultimate'),
			'MU' => __('Mauritius', 'multisite-ultimate'),
			'YT' => __('Mayotte', 'multisite-ultimate'),
			'MX' => __('Mexico', 'multisite-ultimate'),
			'FM' => __('Micronesia', 'multisite-ultimate'),
			'MD' => __('Moldova', 'multisite-ultimate'),
			'MC' => __('Monaco', 'multisite-ultimate'),
			'MN' => __('Mongolia', 'multisite-ultimate'),
			'ME' => __('Montenegro', 'multisite-ultimate'),
			'MS' => __('Montserrat', 'multisite-ultimate'),
			'MA' => __('Morocco', 'multisite-ultimate'),
			'MZ' => __('Mozambique', 'multisite-ultimate'),
			'MM' => __('Myanmar', 'multisite-ultimate'),
			'NA' => __('Namibia', 'multisite-ultimate'),
			'NR' => __('Nauru', 'multisite-ultimate'),
			'NP' => __('Nepal', 'multisite-ultimate'),
			'NL' => __('Netherlands', 'multisite-ultimate'),
			'NC' => __('New Caledonia', 'multisite-ultimate'),
			'NZ' => __('New Zealand', 'multisite-ultimate'),
			'NI' => __('Nicaragua', 'multisite-ultimate'),
			'NE' => __('Niger', 'multisite-ultimate'),
			'NG' => __('Nigeria', 'multisite-ultimate'),
			'NU' => __('Niue', 'multisite-ultimate'),
			'NF' => __('Norfolk Island', 'multisite-ultimate'),
			'MP' => __('Northern Mariana Islands', 'multisite-ultimate'),
			'KP' => __('North Korea', 'multisite-ultimate'),
			'NO' => __('Norway', 'multisite-ultimate'),
			'OM' => __('Oman', 'multisite-ultimate'),
			'PK' => __('Pakistan', 'multisite-ultimate'),
			'PS' => __('Palestinian Territory', 'multisite-ultimate'),
			'PA' => __('Panama', 'multisite-ultimate'),
			'PG' => __('Papua New Guinea', 'multisite-ultimate'),
			'PY' => __('Paraguay', 'multisite-ultimate'),
			'PE' => __('Peru', 'multisite-ultimate'),
			'PH' => __('Philippines', 'multisite-ultimate'),
			'PN' => __('Pitcairn', 'multisite-ultimate'),
			'PL' => __('Poland', 'multisite-ultimate'),
			'PT' => __('Portugal', 'multisite-ultimate'),
			'PR' => __('Puerto Rico', 'multisite-ultimate'),
			'QA' => __('Qatar', 'multisite-ultimate'),
			'RE' => __('Reunion', 'multisite-ultimate'),
			'RO' => __('Romania', 'multisite-ultimate'),
			'RU' => __('Russia', 'multisite-ultimate'),
			'RW' => __('Rwanda', 'multisite-ultimate'),
			'BL' => __('Saint Barth&eacute;lemy', 'multisite-ultimate'),
			'SH' => __('Saint Helena', 'multisite-ultimate'),
			'KN' => __('Saint Kitts and Nevis', 'multisite-ultimate'),
			'LC' => __('Saint Lucia', 'multisite-ultimate'),
			'MF' => __('Saint Martin (French part)', 'multisite-ultimate'),
			'SX' => __('Saint Martin (Dutch part)', 'multisite-ultimate'),
			'PM' => __('Saint Pierre and Miquelon', 'multisite-ultimate'),
			'VC' => __('Saint Vincent and the Grenadines', 'multisite-ultimate'),
			'SM' => __('San Marino', 'multisite-ultimate'),
			'ST' => __('S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'multisite-ultimate'),
			'SA' => __('Saudi Arabia', 'multisite-ultimate'),
			'SN' => __('Senegal', 'multisite-ultimate'),
			'RS' => __('Serbia', 'multisite-ultimate'),
			'SC' => __('Seychelles', 'multisite-ultimate'),
			'SL' => __('Sierra Leone', 'multisite-ultimate'),
			'SG' => __('Singapore', 'multisite-ultimate'),
			'SK' => __('Slovakia', 'multisite-ultimate'),
			'SI' => __('Slovenia', 'multisite-ultimate'),
			'SB' => __('Solomon Islands', 'multisite-ultimate'),
			'SO' => __('Somalia', 'multisite-ultimate'),
			'ZA' => __('South Africa', 'multisite-ultimate'),
			'GS' => __('South Georgia/Sandwich Islands', 'multisite-ultimate'),
			'KR' => __('South Korea', 'multisite-ultimate'),
			'SS' => __('South Sudan', 'multisite-ultimate'),
			'ES' => __('Spain', 'multisite-ultimate'),
			'LK' => __('Sri Lanka', 'multisite-ultimate'),
			'SD' => __('Sudan', 'multisite-ultimate'),
			'SR' => __('Suriname', 'multisite-ultimate'),
			'SJ' => __('Svalbard and Jan Mayen', 'multisite-ultimate'),
			'SZ' => __('Swaziland', 'multisite-ultimate'),
			'SE' => __('Sweden', 'multisite-ultimate'),
			'CH' => __('Switzerland', 'multisite-ultimate'),
			'SY' => __('Syria', 'multisite-ultimate'),
			'TW' => __('Taiwan', 'multisite-ultimate'),
			'TJ' => __('Tajikistan', 'multisite-ultimate'),
			'TZ' => __('Tanzania', 'multisite-ultimate'),
			'TH' => __('Thailand', 'multisite-ultimate'),
			'TL' => __('Timor-Leste', 'multisite-ultimate'),
			'TG' => __('Togo', 'multisite-ultimate'),
			'TK' => __('Tokelau', 'multisite-ultimate'),
			'TO' => __('Tonga', 'multisite-ultimate'),
			'TT' => __('Trinidad and Tobago', 'multisite-ultimate'),
			'TN' => __('Tunisia', 'multisite-ultimate'),
			'TR' => __('Turkey', 'multisite-ultimate'),
			'TM' => __('Turkmenistan', 'multisite-ultimate'),
			'TC' => __('Turks and Caicos Islands', 'multisite-ultimate'),
			'TV' => __('Tuvalu', 'multisite-ultimate'),
			'UG' => __('Uganda', 'multisite-ultimate'),
			'UA' => __('Ukraine', 'multisite-ultimate'),
			'AE' => __('United Arab Emirates', 'multisite-ultimate'),
			'GB' => __('United Kingdom (UK)', 'multisite-ultimate'),
			'US' => __('United States (US)', 'multisite-ultimate'),
			'UM' => __('United States (US) Minor Outlying Islands', 'multisite-ultimate'),
			'VI' => __('United States (US) Virgin Islands', 'multisite-ultimate'),
			'UY' => __('Uruguay', 'multisite-ultimate'),
			'UZ' => __('Uzbekistan', 'multisite-ultimate'),
			'VU' => __('Vanuatu', 'multisite-ultimate'),
			'VA' => __('Vatican', 'multisite-ultimate'),
			'VE' => __('Venezuela', 'multisite-ultimate'),
			'VN' => __('Vietnam', 'multisite-ultimate'),
			'WF' => __('Wallis and Futuna', 'multisite-ultimate'),
			'EH' => __('Western Sahara', 'multisite-ultimate'),
			'WS' => __('Samoa', 'multisite-ultimate'),
			'YE' => __('Yemen', 'multisite-ultimate'),
			'ZM' => __('Zambia', 'multisite-ultimate'),
			'ZW' => __('Zimbabwe', 'multisite-ultimate'),
		]
	);
}

/**
 * Returns the list of countries with an additional empty state option.
 *
 * @since 2.0.0
 * @return array
 */
function wu_get_countries_as_options() {

	return array_merge(
		[
			'' => __('Select Country', 'multisite-ultimate'),
		],
		wu_get_countries()
	);
}

/**
 * Returns the country object.
 *
 * @since 2.0.11
 *
 * @param string      $country_code Two-letter country ISO code.
 * @param string|null $name The country name.
 * @param array       $fallback_attributes Fallback attributes if the country class is not present.
 * @return \WP_Ultimo\Country\Country
 */
function wu_get_country($country_code, $name = null, $fallback_attributes = []) {

	$country_code = strtoupper($country_code);

	$country_class = "\\WP_Ultimo\\Country\\Country_{$country_code}";

	if (class_exists($country_class)) {
		return $country_class::get_instance();
	}

	return \WP_Ultimo\Country\Country_Default::build($country_code, $name, $fallback_attributes);
}

/**
 * Get the state list for a country.
 *
 * @since 2.0.12
 *
 * @param string $country_code The country code.
 * @param string $key_name The name to use for the key entry.
 * @param string $value_name The name to use for the value entry.
 * @return array
 */
function wu_get_country_states($country_code, $key_name = 'id', $value_name = 'value') {

	static $state_options = [];

	$options = [];

	$cache = wu_get_isset($state_options, $country_code, false);

	if ($cache) {
		$options = $cache;
	} else {
		$country = wu_get_country($country_code);

		$state_options[ $country_code ] = $country->get_states_as_options(false);

		$options = $state_options[ $country_code ];
	}

	if (empty($key_name)) {
		return $options;
	}

	return wu_key_map_to_array($options, $key_name, $value_name);
}

/**
 * Get cities for a collection of states of a country.
 *
 * @since 2.0.11
 *
 * @param string $country_code The country code.
 * @param array  $states The list of state codes to retrieve.
 * @param string $key_name The name to use for the key entry.
 * @param string $value_name The name to use for the value entry.
 * @return array
 */
function wu_get_country_cities($country_code, $states, $key_name = 'id', $value_name = 'value') {

	static $city_options = [];

	$states = (array) $states;

	$options = [];

	foreach ($states as $state_code) {
		$cache = wu_get_isset($city_options, $state_code, false);

		if ($cache) {
			$options = array_merge($options, $cache);
		} else {
			$country = wu_get_country($country_code);

			$city_options[ $state_code ] = $country->get_cities_as_options($state_code, false);

			$options = array_merge($options, $city_options[ $state_code ]);
		}
	}

	if (empty($key_name)) {
		return $options;
	}

	return wu_key_map_to_array($options, $key_name, $value_name);
}

/**
 * Returns the country name for a given country code.
 *
 * @since 2.0.0
 *
 * @param string $country_code Country code.
 * @return string
 */
function wu_get_country_name($country_code) {

	$country_name = wu_get_isset(wu_get_countries(), $country_code, __('Not found', 'multisite-ultimate'));

	return apply_filters('wu_get_country_name', $country_name, $country_code);
}

/**
 * Get the list of countries and counts based on customers.
 *
 * @since 2.0.0
 * @param integer        $count The number of results to return.
 * @param boolean|string $start_date The start date.
 * @param boolean|string $end_date The end date.
 * @return array
 */
function wu_get_countries_of_customers($count = 10, $start_date = false, $end_date = false) {

	global $wpdb;

	$table_name          = "{$wpdb->base_prefix}wu_customermeta";
	$customer_table_name = "{$wpdb->base_prefix}wu_customers";

	$date_query = '';

	if ($start_date || $end_date) {
		$date_query = 'AND c.date_registered >= %s AND c.date_registered <= %s';

		$date_query = $wpdb->prepare($date_query, $start_date . ' 00:00:00', $end_date . " 23:59:59"); // phpcs:ignore
	}

	$query = "
		SELECT m.meta_value as country, COUNT(distinct c.id) as count
		FROM `{$table_name}` as m
		INNER JOIN `{$customer_table_name}` as c ON m.wu_customer_id = c.id
		WHERE m.meta_key = 'ip_country' AND m.meta_value != ''
		$date_query
		GROUP BY m.meta_value
		ORDER BY count DESC
		LIMIT %d
	";

	$query = $wpdb->prepare($query, $count); // phpcs:ignore

	$results = $wpdb->get_results($query); // phpcs:ignore

	$countries = [];

	foreach ($results as $result) {
		$countries[ $result->country ] = $result->count;
	}

	return $countries;
}

/**
 * Get the list of countries and counts based on customers.
 *
 * @since 2.0.0
 * @param string         $country_code The country code.
 * @param integer        $count The number of results to return.
 * @param boolean|string $start_date The start date.
 * @param boolean|string $end_date The end date.
 * @return array
 */
function wu_get_states_of_customers($country_code, $count = 100, $start_date = false, $end_date = false) {

	global $wpdb;

	static $states = [];

	$table_name          = "{$wpdb->base_prefix}wu_customermeta";
	$customer_table_name = "{$wpdb->base_prefix}wu_customers";

	$date_query = '';

	if ($start_date || $end_date) {
		$date_query = 'AND c.date_registered >= %s AND c.date_registered <= %s';

		$date_query = $wpdb->prepare($date_query, $start_date . ' 00:00:00', $end_date . " 23:59:59"); // phpcs:ignore
	}

	$states = wu_get_country_states('BR', false);

	if (empty($states)) {
		return [];
	}

	$states_in = implode("','", array_keys($states));

	$query = "
		SELECT m.meta_value as state, COUNT(distinct c.id) as count
		FROM `{$table_name}` as m
		INNER JOIN `{$customer_table_name}` as c ON m.wu_customer_id = c.id
		WHERE m.meta_key = 'ip_state' AND m.meta_value IN ('$states_in')
		$date_query
		GROUP BY m.meta_value
		ORDER BY count DESC
		LIMIT %d
	";

	$query = $wpdb->prepare($query, $count); // phpcs:ignore

	$results = $wpdb->get_results($query); // phpcs:ignore

	if (empty($results)) {
		return [];
	}

	$_states = [];

	foreach ($results as $result) {
		$final_label = sprintf('%s (%s)', $states[ $result->state ], $result->state);

		$_states[ $final_label ] = absint($result->count);
	}

	return $_states;
}
