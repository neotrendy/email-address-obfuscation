<?php
/**
 * PHPUnit bootstrap file: loads the WordPress test library and the plugin from trunk/.
 *
 * @package EmailAddressObfuscation
 */

$neotrendy_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $neotrendy_tests_dir ) {
	$neotrendy_tests_dir = dirname( __DIR__ ) . '/.wp-tests/wordpress-tests-lib';
}

if ( ! file_exists( $neotrendy_tests_dir . '/includes/functions.php' ) ) {
	echo 'Could not find ' . $neotrendy_tests_dir . '/includes/functions.php, run bin/install-wp-tests.sh first.' . PHP_EOL;
	exit( 1 );
}

require_once dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
require_once $neotrendy_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	function () {
		require dirname( __DIR__ ) . '/trunk/email-address-obfuscation.php';
	}
);

require $neotrendy_tests_dir . '/includes/bootstrap.php';
