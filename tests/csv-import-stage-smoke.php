<?php
if ( PHP_SAPI !== 'cli' ) { exit; }
define( 'ABSPATH', '/tmp/' );
$GLOBALS['flz_stage'] = array();
function register_activation_hook(): void {} function register_deactivation_hook(): void {}
function sanitize_email( $v ): string { return (string) $v; }
function set_transient( $k, $v ): void { $GLOBALS['flz_stage'][ $k ] = $v; }
function get_transient( $k ) { return $GLOBALS['flz_stage'][ $k ] ?? false; }
function delete_transient( $k ): void { unset( $GLOBALS['flz_stage'][ $k ] ); }
require dirname( __DIR__ ) . '/flz_wpdb_objects.php';
FlzCsvImportStage::store( 'test', 7, array( array( 'x' ) ), 'proof' );
if ( ! FlzCsvImportStage::exists( 'test', 7 ) ) { throw new RuntimeException( 'stage missing' ); }
$stage = FlzCsvImportStage::consume( 'test', 7 );
if ( ! is_array( $stage ) || 'proof' !== $stage['proof'] || FlzCsvImportStage::exists( 'test', 7 ) ) { throw new RuntimeException( 'stage not consumed once' ); }
echo "OK: CSV import staging contract\n";
