<?php
/**
 * Custom test configuration to override database settings for wp-env.
 */

// a helper function to lookup "env_FILE", "env", then fallback
if (!function_exists('getenv_docker')) {
	// https://github.com/docker-library/wordpress/issues/588 (WP-CLI will load this file 2x)
	function getenv_docker($env, $default) {
		if ($fileEnv = getenv($env . '_FILE')) {
			return rtrim(file_get_contents($fileEnv), "\r\n");
		}
		else if (($val = getenv($env)) !== false) {
			return $val;
		}
		else {
			return $default;
		}
	}
}

// Override database configuration for wp-env test environment
define('DB_NAME', getenv_docker('WORDPRESS_DB_NAME', 'tests-wordpress'));
define('DB_USER', getenv_docker('WORDPRESS_DB_USER', 'root'));
define('DB_PASSWORD', getenv_docker('WORDPRESS_DB_PASSWORD', 'password'));
define('DB_HOST', getenv_docker('WORDPRESS_DB_HOST', 'tests-mysql'));

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', getenv_docker('WORDPRESS_DB_CHARSET', 'utf8mb4') );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', getenv_docker('WORDPRESS_DB_COLLATE', '') );

/**#@+
 * Authentication unique keys and salts.
 */
define( 'AUTH_KEY',         getenv_docker('WORDPRESS_AUTH_KEY',         '6629e233bb5085231f76ab957e0d54ec3feed53e') );
define( 'SECURE_AUTH_KEY',  getenv_docker('WORDPRESS_SECURE_AUTH_KEY',  'a68d3162faf309cc94c33af7dc423feaab3a6a0d') );
define( 'LOGGED_IN_KEY',    getenv_docker('WORDPRESS_LOGGED_IN_KEY',    'f121d70bf80e9492193e33f601cc865d397c8f8c') );
define( 'NONCE_KEY',        getenv_docker('WORDPRESS_NONCE_KEY',        'e017eeefe3af5170ea8c3d3b3e59562b3d8ccaba') );
define( 'AUTH_SALT',        getenv_docker('WORDPRESS_AUTH_SALT',        'e525f6ee7fb124e151db10ad97271a60e50db9d8') );
define( 'SECURE_AUTH_SALT', getenv_docker('WORDPRESS_SECURE_AUTH_SALT', '58f09b39299499c4a9dbe0a3abc4c285a19d490d') );
define( 'LOGGED_IN_SALT',   getenv_docker('WORDPRESS_LOGGED_IN_SALT',   '7ee97bcffdf46e2c31d1d0a49eb7063fad41d2dc') );
define( 'NONCE_SALT',       getenv_docker('WORDPRESS_NONCE_SALT',       '3d8713e4fb4f28492c609ba1ef7564f3460a7863') );

/** WordPress database table prefix. */
$table_prefix = getenv_docker('WORDPRESS_TABLE_PREFIX', 'wp_');

/** Test environment constants */
define( 'FS_METHOD', 'direct' );
define( 'SCRIPT_DEBUG', true );
define( 'WP_ENVIRONMENT_TYPE', 'local' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_TESTS_DOMAIN', 'localhost:8889' );
define( 'WP_SITEURL', 'http://localhost:8889' );
define( 'WP_HOME', 'http://localhost:8889' );
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_DEBUG_LOG', true );
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'WP_MAX_MEMORY_LIMIT', '512M' );
define( 'WP_DEBUG', true );

/* Multisite */
define( 'WP_ALLOW_MULTISITE', true );
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', false );
$base = '/';
define( 'DOMAIN_CURRENT_SITE', 'localhost:8889' );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/var/www/html/' );
	define( 'WP_DEFAULT_THEME', 'default' );
}