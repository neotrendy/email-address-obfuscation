#!/usr/bin/env bash
#
# Installs WordPress and the WordPress PHPUnit test library into .wp-tests/
# and creates the test database.
#
# Usage: bin/install-wp-tests.sh <db-name> <db-user> [db-host] [wp-version]
#
# The database password is read from the WP_TESTS_DB_PASS environment variable,
# or prompted for. WARNING: the test suite deletes all tables in <db-name>.
# Never use the database of a real site.

set -euo pipefail

if [ $# -lt 2 ]; then
	echo "Usage: $0 <db-name> <db-user> [db-host] [wp-version]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_HOST=${3:-localhost}
WP_VERSION=${4:-latest}

if [ -z "${WP_TESTS_DB_PASS+x}" ]; then
	read -r -s -p "MySQL password for ${DB_USER}: " WP_TESTS_DB_PASS
	echo
fi

ROOT_DIR=$(cd "$(dirname "$0")/.." && pwd)
WP_TESTS_ROOT="${ROOT_DIR}/.wp-tests"
WP_CORE_DIR="${WP_TESTS_ROOT}/wordpress"
WP_TESTS_DIR="${WP_TESTS_ROOT}/wordpress-tests-lib"

if [ "$WP_VERSION" = "latest" ]; then
	WP_VERSION=$(curl -fsS https://api.wordpress.org/core/version-check/1.7/ | grep -o '"version":"[^"]*' | head -1 | sed 's/"version":"//')
fi

# Test library tag: x.y.0 is tagged as x.y.
WP_TESTS_TAG="tags/${WP_VERSION%.0}"

echo "WordPress ${WP_VERSION} -> ${WP_TESTS_ROOT}"

if [ ! -d "$WP_CORE_DIR" ]; then
	mkdir -p "$WP_CORE_DIR"
	curl -fsS "https://wordpress.org/wordpress-${WP_VERSION}.tar.gz" | tar --strip-components=1 -zxm -C "$WP_CORE_DIR"
fi

if [ ! -d "$WP_TESTS_DIR" ]; then
	mkdir -p "$WP_TESTS_DIR"
	svn export --quiet --ignore-externals "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/" "$WP_TESTS_DIR/includes"
	svn export --quiet --ignore-externals "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/" "$WP_TESTS_DIR/data"
fi

CONFIG="$WP_TESTS_DIR/wp-tests-config.php"
curl -fsS "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php" > "$CONFIG"
DB_NAME="$DB_NAME" DB_USER="$DB_USER" DB_PASS="$WP_TESTS_DB_PASS" DB_HOST="$DB_HOST" ABSPATH_DIR="$WP_CORE_DIR/" php -r '
	$file = $argv[1];
	$config = file_get_contents( $file );
	$config = str_replace(
		array( "dirname( __FILE__ ) . \x27/src/\x27", "__DIR__ . \x27/src/\x27", "\x27youremptytestdbnamehere\x27", "\x27yourusernamehere\x27", "\x27yourpasswordhere\x27", "\x27localhost\x27" ),
		array( var_export( getenv( "ABSPATH_DIR" ), true ), var_export( getenv( "ABSPATH_DIR" ), true ), var_export( getenv( "DB_NAME" ), true ), var_export( getenv( "DB_USER" ), true ), var_export( getenv( "DB_PASS" ), true ), var_export( getenv( "DB_HOST" ), true ) ),
		$config
	);
	file_put_contents( $file, $config );
' "$CONFIG"
chmod 600 "$CONFIG"

# MYSQL_PWD keeps the password out of the process list.
if MYSQL_PWD="$WP_TESTS_DB_PASS" mysql --user="$DB_USER" --host="$DB_HOST" --batch --skip-column-names --execute="SHOW DATABASES LIKE '${DB_NAME}'" | grep -qx "$DB_NAME"; then
	echo "Database ${DB_NAME} already exists, keeping it."
else
	MYSQL_PWD="$WP_TESTS_DB_PASS" mysql --user="$DB_USER" --host="$DB_HOST" --execute="CREATE DATABASE \`${DB_NAME}\`"
	echo "Created database ${DB_NAME}."
fi

echo "Done. Run: composer test"
