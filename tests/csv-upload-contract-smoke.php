<?php

if ( PHP_SAPI !== 'cli' ) {
	http_response_code( 404 );
	exit;
}

define( 'ABSPATH', '/tmp/' );

// Test-Exceptions werden ausschließlich von der CLI ausgewertet.
// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped

function register_activation_hook(): void {}
function register_deactivation_hook(): void {}
function wp_unslash( $value ) {
	return $value;
}
function sanitize_file_name( $value ): string {
	return preg_replace( '/[^A-Za-z0-9._-]/', '', (string) $value ) ?? '';
}
function sanitize_text_field( $value ): string {
	return (string) $value;
}
function absint( $value ): int {
	return abs( (int) $value );
}
function wp_basename( string $path ): string {
	return basename( $path );
}
function flz_wpdb_objects_is_uploaded_file( string $path ): bool {
	return ! empty( $GLOBALS['flz_test_is_uploaded_file'] ) && is_file( $path );
}
function wp_check_filetype_and_ext( string $path, string $filename, array $mimes ): array {
	return $GLOBALS['flz_test_filetype'] ?? array( 'ext' => 'csv', 'type' => 'text/csv' );
}

require_once dirname( __DIR__ ) . '/flz_wpdb_objects.php';

/** @param callable():void $callback */
function flz_test_rejects( callable $callback, string $message ): void {
	try {
		$callback();
	} catch ( InvalidArgumentException|RuntimeException $error ) {
		return;
	}
	throw new RuntimeException( $message );
}

$path = tempnam( sys_get_temp_dir(), 'flz-csv-' );
if ( false === $path ) {
	throw new RuntimeException( 'Temporäre CSV-Testdatei konnte nicht angelegt werden.' );
}
file_put_contents( $path, "name;email\nBeispiel;test@example.test\n" );

$GLOBALS['flz_test_is_uploaded_file'] = true;
$_FILES['csv'] = array(
	'name' => 'import.csv',
	'tmp_name' => $path,
	'error' => UPLOAD_ERR_OK,
	'size' => filesize( $path ),
);
$rows = flz_wpdb_objects_read_uploaded_csv( 'csv', 'CSV-Test', false );
if ( 2 !== count( $rows ) ) {
	throw new RuntimeException( 'Ein gültiger CSV-Upload wurde nicht vollständig gelesen.' );
}

$_FILES['csv']['error'] = UPLOAD_ERR_PARTIAL;
flz_test_rejects(
	static fn() => flz_wpdb_objects_read_uploaded_csv( 'csv', 'CSV-Test', false ),
	'Ein unvollständiger Upload wurde akzeptiert.'
);

$_FILES['csv']['error'] = UPLOAD_ERR_OK;
$GLOBALS['flz_test_is_uploaded_file'] = false;
flz_test_rejects(
	static fn() => flz_wpdb_objects_read_uploaded_csv( 'csv', 'CSV-Test', false ),
	'Eine nicht über HTTP hochgeladene Datei wurde akzeptiert.'
);

$GLOBALS['flz_test_is_uploaded_file'] = true;
$_FILES['csv']['size'] = 64;
flz_test_rejects(
	static fn() => flz_wpdb_objects_read_uploaded_csv( 'csv', 'CSV-Test', false, ';', 32 ),
	'Eine zu große CSV-Datei wurde akzeptiert.'
);

$_FILES['csv']['size'] = filesize( $path );
$GLOBALS['flz_test_filetype'] = array( 'ext' => 'php', 'type' => 'text/x-php' );
flz_test_rejects(
	static fn() => flz_wpdb_objects_read_uploaded_csv( 'csv', 'CSV-Test', false ),
	'Ein Upload mit ungültigem tatsächlichem Dateityp wurde akzeptiert.'
);

$GLOBALS['flz_test_filetype'] = array( 'ext' => 'csv', 'type' => 'text/csv' );
flz_test_rejects(
	static fn() => flz_wpdb_objects_read_uploaded_csv( 'csv', 'CSV-Test', false, ';', 1024, 1 ),
	'Eine CSV oberhalb des Zeilenlimits wurde akzeptiert.'
);

unlink( $path );

echo "OK: flz_wpdb_objects CSV upload contract smoke test\n";
