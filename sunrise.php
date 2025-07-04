<?php
// WP Ultimo Starts #
/**
 * Multisite Ultimate Sunrise
 * Plugin URI: https://wpmultisitewaas.org
 * Version: 2.0.0.8
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * WordPress Core has a few ways of allowing plugin developers to run things earlier in the app lifecycle.
 * One of this ways is to place a sunrise.php file inside the wp-content directory while setting
 * The SUNRISE constant to true.
 *
 * This tells WordPress that it should load our sunrise file before plugins get loaded and
 * the request is processed. We use this great power to handle domain mapping logic and more.
 *
 * @since 2.0.0.5 Adds a network admin notice warning that sunrise is still active when Ultimo is deactivated.
 * @since 2.0.0.5 Change return statement to a continue statement to prevent an early exit from the file.
 *
 * @author      Arindo Duque
 * @category    WP_Ultimo
 * @package     WP_Ultimo/Sunrise
 * @version     2.0.0.5
 */

defined('ABSPATH') || exit;

const WP_ULTIMO_SUNRISE_VERSION = '2.0.0.8';

$wu_sunrise = defined('WP_PLUGIN_DIR')
	? WP_PLUGIN_DIR . '/wp-multisite-waas/inc/class-sunrise.php'
	: WP_CONTENT_DIR . '/plugins/wp-multisite-waas/inc/class-sunrise.php';

$wu_mu_sunrise = defined('WPMU_PLUGIN_DIR')
	? WPMU_PLUGIN_DIR . '/wp-multisite-waas/inc/class-sunrise.php'
	: WP_CONTENT_DIR . '/mu-plugins/wp-multisite-waas/inc/class-sunrise.php';

/**
 * We search for the sunrise class file
 * in the plugins and mu-plugins folders.
 *
 * @since 2.0.0.3 Sunrise Version.
 */

foreach ([$wu_sunrise, $wu_mu_sunrise] as $wu_sunrise_file) {
	if (file_exists($wu_sunrise_file)) {
		if ($wu_sunrise_file === $wu_mu_sunrise) {

			/**
			 * Use a particular function that is defined
			 * after mu-plugins are loaded but before regular plugins
			 * to check if we are being loaded in a must-use context.
			 */
			define('WP_ULTIMO_IS_MUST_USE', true);
		}

		require_once $wu_sunrise_file;

		define('WP_ULTIMO_SUNRISE_FILE', $wu_sunrise_file);

		WP_Ultimo\Sunrise::init();

		add_action('network_admin_notices', 'wu_remove_sunrise_warning', 0);

		break; // Exit the loop.
	}
}
unset($wu_sunrise_file);
unset($wu_mu_sunrise);
unset($wu_sunrise);
/**
 * Include Mercator.
 *
 * This is here purely for backwards compatibility reasons.
 * The file included here is a dumb file in version 2.0.7+.
 *
 * @since 2.0.7
 */
$wu_mercator = defined('WP_PLUGIN_DIR')
	? WP_PLUGIN_DIR . '/wp-multisite-waas/inc/mercator/mercator.php'
	: WP_CONTENT_DIR . '/plugins/wp-multisite-waas/inc/mercator/mercator.php';

if (file_exists($wu_mercator)) {
	require $wu_mercator;
}
unset($wu_mercator);

/**
 * Adds a warning when Multisite Ultimate is not present but the sunrise file is.
 *
 * @since 2.0.0
 * @return void
 */
function wu_remove_sunrise_warning() {

	if (function_exists('WP_Ultimo') === false) {

		?>
	<div class="notice notice-warning">
		<p>
			Multisite Ultimate is deactivated, yet its <strong>sunrise.php</strong> file is still being loaded. If you have no intentions of continuing to use Multisite Ultimate and this was not a temporary deactivation, we recommend removing the <code>define('SUNRISE', true);</code> line from your <strong>wp-config.php</strong> file. Keeping Multisite Ultimate <strong>sunrise.php</strong> file active without Multisite Ultimate can lead to unexpected behaviors and it is not advised.
		</p>
	</div>

		<?php
	}
}

// WP Ultimo Ends #
