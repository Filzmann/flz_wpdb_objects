<?php

/**
 * Short-lived, per-user staging for a CSV that has already passed a dry-run.
 * Callers retain parsing, authorization and domain validation responsibilities.
 */
final class FlzCsvImportStage {
	public static function store( string $scope, int $user_id, array $rows, string $proof, int $ttl = 900 ): void {
		if ( $user_id <= 0 || $ttl <= 0 || $scope === '' ) {
			throw new InvalidArgumentException( 'Ungültiger CSV-Zwischenspeicher-Kontext.' );
		}
		set_transient( self::key( $scope, $user_id ), array( 'rows' => $rows, 'proof' => $proof ), $ttl );
	}

	public static function consume( string $scope, int $user_id ): ?array {
		$key = self::key( $scope, $user_id );
		$stage = get_transient( $key );
		delete_transient( $key );
		return is_array( $stage ) && isset( $stage['rows'], $stage['proof'] ) && is_array( $stage['rows'] ) && is_string( $stage['proof'] ) ? $stage : null;
	}

	public static function exists( string $scope, int $user_id ): bool {
		return is_array( get_transient( self::key( $scope, $user_id ) ) );
	}

	public static function discard( string $scope, int $user_id ): void {
		delete_transient( self::key( $scope, $user_id ) );
	}

	private static function key( string $scope, int $user_id ): string {
		return 'flz_csv_stage_' . substr( hash( 'sha256', $scope . '|' . $user_id ), 0, 40 );
	}
}
