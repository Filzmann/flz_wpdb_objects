<?php
/*
Plugin Name: FLZ WPDB Objects
Description: Stellt WordPress-Datenbankfunktionen als einfache CRUD-Modelle zur Verfügung
Version: 2.0.0
Author: Filzmann
License: GPLv2 or later
Requires at least: 6.5
Requires PHP: 8.1
Text Domain: flz-wpdb-objects
*/

defined( 'ABSPATH' ) || exit;

define( 'FLZ_WPDB_OBJECTS_VERSION', '2.0.0' );

// Exception-Texte sind interne Logdaten; HTML-Escaping erfolgt erst an der UI-Grenze.
// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped

require_once __DIR__ . '/FlzWpdbObject.php';
require_once __DIR__ . '/FlzPerson.php';
require_once __DIR__ . '/FlzZeitraum.php';

// Die konkreten Fachplugins verwalten ihre eigenen Tabellen.
register_activation_hook( __FILE__, 'flz_wpdb_objects_activate' );
register_deactivation_hook( __FILE__, 'flz_wpdb_objects_deactivate' );

function flz_wpdb_objects_activate(): void {}

// Bei der Deaktivierung werden absichtlich keine Daten gelöscht.
function flz_wpdb_objects_deactivate(): void {}

/**
 * Verhindert, dass Tabellenkalkulationen CSV-Zellen als Formel ausführen.
 *
 * @param mixed $value Zellwert.
 */
function flz_wpdb_objects_csv_safe_cell($value): string {
	$value = (string) $value;

	return preg_match( '/^[=+\-@]/', $value ) ? "'" . $value : $value;
}

/**
 * Baut eine CSV-Datei im Speicher.
 *
 * @param array<int,mixed> $header Kopfzeile.
 * @param array<int,array<int,mixed>> $rows Datenzeilen.
 *
 * @throws flz_wpdb_objects\FlzWpdbObjectsException Bei Stream-/Schreibfehlern.
 */
function flz_wpdb_objects_build_csv_string(
	array $header,
	array $rows,
	string $delimiter = ';',
	bool $with_bom = true
): string {
	$out = fopen( 'php://temp', 'w+b' );
	if ( $out === false ) {
		throw flz_wpdb_objects\FlzWpdbObjectsException::file_system(
			'Öffnen des temporären CSV-Speichers',
			'php://temp'
		);
	}

	$pending_error = null;
	try {
		if ( $with_bom && fwrite( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ) !== 3 ) {
			throw flz_wpdb_objects\FlzWpdbObjectsException::file_system(
				'Schreiben der CSV-Kodierungsmarkierung',
				'php://temp'
			);
		}

		if ( ! empty( $header ) && fputcsv( $out, array_map( 'flz_wpdb_objects_csv_safe_cell', $header ), $delimiter ) === false ) {
			throw flz_wpdb_objects\FlzWpdbObjectsException::file_system(
				'Schreiben der CSV-Kopfzeile',
				'php://temp'
			);
		}

		foreach ( $rows as $index => $row ) {
			if ( ! is_array( $row ) ) {
				throw new InvalidArgumentException(
					'Die CSV-Zeile mit Index "' . (string) $index . '" muss ein Array sein.'
				);
			}
			if ( fputcsv( $out, array_map( 'flz_wpdb_objects_csv_safe_cell', $row ), $delimiter ) === false ) {
				throw flz_wpdb_objects\FlzWpdbObjectsException::file_system(
					'Schreiben der CSV-Datenzeile mit Index ' . (string) $index,
					'php://temp'
				);
			}
		}

		if ( ! rewind( $out ) ) {
			throw flz_wpdb_objects\FlzWpdbObjectsException::file_system(
				'Zurücksetzen des CSV-Datenstroms',
				'php://temp'
			);
		}

		$csv = stream_get_contents( $out );
		if ( $csv === false ) {
			throw flz_wpdb_objects\FlzWpdbObjectsException::file_system(
				'Lesen des fertigen CSV-Datenstroms',
				'php://temp'
			);
		}
	} catch ( Throwable $error ) {
		$pending_error = $error;
	}

	if ( ! fclose( $out ) && $pending_error === null ) {
		throw flz_wpdb_objects\FlzWpdbObjectsException::file_system(
			'Schließen des temporären CSV-Speichers',
			'php://temp'
		);
	}

	if ( $pending_error !== null ) {
		throw $pending_error;
	}

	return $csv;
}

/**
 * Liest ein hochgeladenes CSV-Feld sicher in Zeilen ein.
 *
 * Nonce- und Berechtigungsprüfung bleiben bewusst Aufgabe des aufrufenden
 * Workflows. Diese Funktion kümmert sich nur um Dateiart, Öffnen/Schließen und
 * saubere Fehlerkontexte.
 *
 * @return array<int,array<int,string|null>>
 *
 * @throws RuntimeException|InvalidArgumentException|flz_wpdb_objects\FlzWpdbObjectsException
 */
function flz_wpdb_objects_read_uploaded_csv(
	string $file_field,
	string $operation,
	bool $skip_header = true,
	string $delimiter = ';',
	int $max_bytes = 2097152,
	int $max_rows = 10000,
	int $max_columns = 100
): array {
	if ( '' === trim( $file_field ) ) {
		throw new InvalidArgumentException( 'Das Upload-Feld für den CSV-Import darf nicht leer sein.' );
	}
	if ( $max_bytes <= 0 || $max_rows <= 0 || $max_columns <= 0 ) {
		throw new InvalidArgumentException( 'CSV-Uploadlimits müssen größer als null sein.' );
	}

	// Nonce- und Berechtigungsprüfung erfolgen im aufrufenden Admin-Workflow,
	// damit dieser generische Dateihelper keine fachlichen Formularaktionen kennen muss.
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Dateiupload wird feldbezogen geprüft und der Name anschließend bereinigt.
	if (
		! isset(
			$_FILES[ $file_field ]['tmp_name'],
			$_FILES[ $file_field ]['name'],
			$_FILES[ $file_field ]['error'],
			$_FILES[ $file_field ]['size']
		)
	) {
		throw new RuntimeException( 'Keine CSV-Datei hochgeladen.' );
	}

	$file_original_name = sanitize_file_name( wp_unslash( $_FILES[ $file_field ]['name'] ) );
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Der PHP-Uploadpfad wird nicht ausgegeben und über is_uploaded_file() geprüft.
	$file_path = (string) wp_unslash( $_FILES[ $file_field ]['tmp_name'] );
	$upload_error = absint( wp_unslash( $_FILES[ $file_field ]['error'] ) );
	$declared_size = absint( wp_unslash( $_FILES[ $file_field ]['size'] ) );
	// phpcs:enable WordPress.Security.NonceVerification.Missing
	if ( UPLOAD_ERR_OK !== $upload_error ) {
		throw new RuntimeException( 'Der CSV-Upload wurde nicht vollständig übertragen (Fehlercode ' . $upload_error . ').' );
	}
	if ( strtolower( pathinfo( $file_original_name, PATHINFO_EXTENSION ) ) !== 'csv' ) {
		throw new InvalidArgumentException( 'Das ist keine CSV-Datei.' );
	}
	if ( ! flz_wpdb_objects_is_uploaded_file( $file_path ) ) {
		throw new RuntimeException( 'Die CSV-Datei stammt nicht aus einem gültigen HTTP-Upload.' );
	}
	$actual_size = filesize( $file_path );
	if ( false === $actual_size || $declared_size > $max_bytes || $actual_size > $max_bytes ) {
		throw new InvalidArgumentException( 'Die CSV-Datei überschreitet das erlaubte Größenlimit.' );
	}
	$filetype = wp_check_filetype_and_ext(
		$file_path,
		$file_original_name,
		array(
			'csv' => 'text/csv',
		)
	);
	if ( 'csv' !== ( $filetype['ext'] ?? null ) || 'text/csv' !== ( $filetype['type'] ?? null ) ) {
		throw new InvalidArgumentException( 'Der tatsächliche Dateityp des Uploads ist keine CSV-Datei.' );
	}

	error_clear_last();
	$file_handle = @fopen( $file_path, 'rb' );
	if ( $file_handle === false ) {
		$php_error = error_get_last();
		throw flz_wpdb_objects\FlzWpdbObjectsException::file_system(
			'Öffnen der CSV-Datei zum Lesen',
			$file_original_name,
			(string) ( $php_error['message'] ?? '' )
		);
	}

	$rows          = array();
	$pending_error = null;
	try {
		$row_number = 0;
		while ( ( $line = fgetcsv( $file_handle, 0, $delimiter ) ) !== false ) {
			++$row_number;
			if ( $row_number > $max_rows ) {
				throw new InvalidArgumentException( 'Die CSV-Datei überschreitet das erlaubte Zeilenlimit.' );
			}
			if ( count( $line ) > $max_columns ) {
				throw new InvalidArgumentException( 'Eine CSV-Zeile überschreitet das erlaubte Spaltenlimit.' );
			}
			if ( $skip_header && 1 === $row_number ) {
				continue;
			}
			$rows[] = $line;
		}
	} catch ( Throwable $error ) {
		$pending_error = flz_wpdb_objects\FlzWpdbObjectsException::operation(
			$operation,
			'CSV-Upload ' . $file_original_name,
			$error
		);
	}

	error_clear_last();
	if ( ! @fclose( $file_handle ) && $pending_error === null ) {
		$php_error = error_get_last();
		throw flz_wpdb_objects\FlzWpdbObjectsException::file_system(
			'Schließen der CSV-Datei',
			$file_original_name,
			(string) ( $php_error['message'] ?? '' )
		);
	}

	if ( $pending_error !== null ) {
		throw $pending_error;
	}

	return $rows;
}

/**
 * Kleine testbare Grenze um die PHP-Uploadprüfung.
 */
if ( ! function_exists( 'flz_wpdb_objects_is_uploaded_file' ) ) {
	function flz_wpdb_objects_is_uploaded_file( string $path ): bool {
		return is_uploaded_file( $path );
	}
}

function flz_wpdb_objects_sanitize_csv_filename( string $filename ): string {
	$filename = sanitize_file_name( wp_basename( $filename ) );
	if ( $filename === '' || strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) !== 'csv' ) {
		throw new InvalidArgumentException( 'Der Dateiname muss auf .csv enden.' );
	}

	return $filename;
}

/**
 * Schreibt eine arraybasierte CSV-Datei in das WordPress-Uploadverzeichnis.
 *
 * Nonce- und Berechtigungsprüfung bleiben Aufgabe des aufrufenden Workflows.
 *
 * @param array<int,mixed>            $header Kopfzeile.
 * @param array<int,array<int,mixed>> $rows   Datenzeilen.
 *
 * @throws InvalidArgumentException                 Bei ungültigem Dateinamen.
 * @throws flz_wpdb_objects\FlzWpdbObjectsException Bei Datei- oder CSV-Fehlern.
 */
function flz_wpdb_objects_create_csv_file(
	array $header,
	array $rows,
	string $filename = 'default.csv'
): string {
	unset( $header, $rows, $filename );
	throw new BadMethodCallException(
		'flz_wpdb_objects_create_csv_file() wird seit Version 2.0.0 nicht mehr unterstützt; '
		. 'nach vorgelagerter Autorisierung ist flz_wpdb_objects_send_csv_download() zu verwenden.'
	);
}

/**
 * Sendet eine CSV-Datei direkt als kontrollierten Download.
 *
 * Diese Funktion beendet den Request absichtlich mit exit. Aufrufer müssen
 * vorher Berechtigungen und Nonces geprüft haben.
 *
 * @param array<int,mixed>            $header Kopfzeile.
 * @param array<int,array<int,mixed>> $rows   Datenzeilen.
 *
 * @throws InvalidArgumentException                 Bei ungültigem Dateinamen.
 * @throws flz_wpdb_objects\FlzWpdbObjectsException Bei CSV-Fehlern.
 */
function flz_wpdb_objects_send_csv_download(
	array $header,
	array $rows,
	string $filename,
	string $delimiter = ';'
): void {
	$filename = flz_wpdb_objects_sanitize_csv_filename( $filename );
	$csv      = flz_wpdb_objects_build_csv_string( $header, $rows, $delimiter );

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Kontrolliert erzeugte CSV-Datei, kein HTML-Kontext.
	echo $csv;
	exit;
}

// phpcs:enable WordPress.Security.EscapeOutput.ExceptionNotEscaped
