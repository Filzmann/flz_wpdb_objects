<?php

if ( PHP_SAPI !== 'cli' ) {
	http_response_code( 404 );
	exit;
}

define( 'ABSPATH', '/tmp/' );

function register_activation_hook(): void {}
function register_deactivation_hook(): void {}
function sanitize_email( $value ): string {
	return (string) filter_var( $value, FILTER_SANITIZE_EMAIL );
}

require_once dirname( __DIR__ ) . '/flz_wpdb_objects.php';

// Test-Exceptions werden ausschließlich von der CLI ausgewertet.
// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped

function flz_value_objects_check( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Test fehlgeschlagen: ' . $message );
	}
}

function flz_value_objects_rejects( callable $callback, string $message_fragment ): void {
	try {
		$callback();
	} catch ( InvalidArgumentException|flz_wpdb_objects\FlzWpdbObjectsException $error ) {
		flz_value_objects_check(
			str_contains( $error->getMessage(), $message_fragment ),
			'Fehlermeldung enthält nicht "' . $message_fragment . '".'
		);

		return;
	}

	throw new RuntimeException( 'Test fehlgeschlagen: Erwartete Exception wurde nicht geworfen.' );
}

$person = new FlzPerson(
	array(
		'id'        => '17',
		'name'      => 'Beispiel',
		'firstName' => 'Alex',
		'gender'    => 'm',
		'email'     => 'alex@example.test',
	)
);
flz_value_objects_check( 17 === $person->id, 'Die Personen-ID wurde nicht normalisiert.' );
flz_value_objects_check( 'Beispiel' === $person->name, 'Der Nachname ging verloren.' );
flz_value_objects_check( 'Alex' === $person->firstName, 'Der Vorname ging verloren.' );
flz_value_objects_check( 'alex@example.test' === $person->email, 'Die E-Mail-Adresse ging verloren.' );
flz_value_objects_check( 'Herr' === $person->get_gender_as_anrede(), 'Die männliche Anrede ist falsch.' );

$person = new FlzPerson( array( 'gender' => 'f' ) );
flz_value_objects_check( 'Frau' === $person->get_gender_as_anrede(), 'Die weibliche Anrede ist falsch.' );
flz_value_objects_check( null === $person->name, 'Ein fehlender Name wurde nicht als null behandelt.' );

$person = new FlzPerson( array( 'gender' => 'x', 'email' => '' ) );
flz_value_objects_check( '' === $person->get_gender_as_anrede(), 'Eine unbekannte Anrede wurde erfunden.' );
flz_value_objects_check( null === $person->email, 'Eine leere E-Mail-Adresse wurde nicht als null behandelt.' );

flz_value_objects_rejects(
	static fn() => new FlzPerson( array( 'name' => array( 'nicht', 'skalar' ) ) ),
	'muss skalar sein'
);
flz_value_objects_rejects(
	static fn() => new FlzZeitraum( array( 'beginn' => 'keine Ganzzahl' ) ),
	'muss eine Ganzzahl sein'
);
flz_value_objects_rejects(
	static fn() => ( new FlzZeitraum( array( 'beginn' => 20, 'ende' => 10 ) ) )->dauer(),
	'Ende darf nicht vor dem Beginn liegen'
);

$duration = ( new FlzZeitraum( array( 'beginn' => '10', 'ende' => '70' ) ) )->dauer();
flz_value_objects_check( 60 === $duration['Dauer in Sekunden'], 'Die Gesamtdauer ist falsch.' );
flz_value_objects_check( 1 === $duration['Minuten'], 'Die Minutenzerlegung ist falsch.' );

echo "OK: flz_wpdb_objects value objects smoke test\n";

// phpcs:enable WordPress.Security.EscapeOutput.ExceptionNotEscaped
