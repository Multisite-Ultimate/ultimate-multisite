<?php
/**
 * Development Bootstrap File
 *
 * This file loads some of the tooling we use to develop Multisite Ultimate.
 * It is not part of the final zip file we ship, so there's no need to worry
 * about things added in here.
 *
 * @package Development
 * @since 2.1.0
 */

use Rarst\wps\Plugin as Whoops;

return;

/**
 * Give an option to disable whoops automatically via a get query string (whoops-disable)
 * or by setting the constant we used to have on previous versions.
 */
if (isset($_GET['whoops-disable']) || (defined('WP_ULTIMO_DISABLE_WHOOPS') && WP_ULTIMO_DISABLE_WHOOPS)) { // phpcs:ignore WordPress.Security.NonceVerification
	return;
}

$wu_whoops = new Whoops();

/**
 * Adds VSCode as the editor, so file paths can be opened
 * directly inside VS Code, for debugging and fixes.
 */
$wu_whoops['handler.pretty']->setEditor('vscode');

/**
 * Silence Notices and Warnings in general.
 */
$wu_whoops['run']->silenceErrorsInPaths('~.*~', E_NOTICE | E_WARNING);

/**
 * Silence known WordPress Core deprecation notices.
 */
$wu_whoops['run']->silenceErrorsInPaths('~/Requests/Cookie/Jar.php', E_DEPRECATED);

/**
 * Installs the Whoops handler.
 */
$wu_whoops->run();
