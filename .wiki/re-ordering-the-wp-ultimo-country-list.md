# Re-ordering the Multisite Ultimate country list

The dropdown to select a country is ordered alphabetically based on the English variants of the country names. This might mean that, sometimes, the home country of your company and/or most of your customers is buried in the middle of a huge country name list.

To help you fix that and add your country of choice to the top of the selection list, you can use the snippet below (you can add it to a mu-plugin):

add_filter('wu_get_countries', function($countries) {

// Change DE to the two-letter code of your country

// Example below for Germany.

unset($countries['DE']);

// Change DE to the two-letter code of your country

// and the name of your country's name.

// Example below, for Germany.

$countries = array_merge(array( 'DE' => __('Germany', 'wp-ultimo'), ), $countries);

return $countries;

});
