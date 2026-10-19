# flz_wpdb_objects

Commit-, CI- und Coverage-Gates sind in Übernahmephase 2 enforced. Ein Release
bleibt bis zur ausgefüllten Abnahme sowie zur Installation und zum Rückbau aus
dem exakten Artefakt blockiert. Für die fachliche und technische Prüfung steht
[`docs/manual-acceptance.md`](docs/manual-acceptance.md) bereit.

Ein sauberer Commit wird reproduzierbar paketiert mit:

```bash
./scripts/build-release /tmp/flz_wpdb_objects-release
```

## Modelltabellen und Custom-Queries

Tabellennamen werden zentral aus dem konkreten Modellnamen abgeleitet und in
Snake-Case pluralisiert. Beispiele:

- `FlzEstTeacher` → `{prefix}flz_est_teachers`
- `FlzPuParticipant` → `{prefix}flz_pu_participants`
- `FLZ_AGS_Course` → `{prefix}flz_ags_courses`

Fachplugins sollen keine eigenen Tabellennamen ableiten und keine
SQL-Strings aus Request-Daten bauen. Einfache Abfragen laufen über
`get_by_id()`, `get_by_fields()`, `get_all_by()` und `count_by()`. Wenn ein
Modell kontrollierte JOINs oder fachliche Sortierungen braucht, stehen
`query_rows()` und `query_models()` als geschützte Helper bereit. SQL-Fragmente
müssen dabei weiterhin aus dem Modell selbst stammen; Nutzwerte werden über
Platzhalter übergeben.

Für historische statische `afterInsert()`-Hooks gibt es
`last_insert_id()`. Neue Logik sollte nach `save()` die Objekt-ID verwenden;
falls ein alter Hook die Insert-ID braucht, bleibt der `$wpdb`-Zugriff damit
zentral geprüft und im Fehlerfall als Modellfehler erklärbar.

## CSV

CSV-Ausgabe arbeitet zentral mit Arrays statt mit modellindividuellen
`getCsvLine()`-Methoden:

- `flz_wpdb_objects_build_csv_string($header, $rows)` erzeugt CSV ausschließlich
  im temporären Speicher.
- `flz_wpdb_objects_send_csv_download($header, $rows, $filename)` sendet einen
  Download direkt an den Browser. Berechtigung und Nonce müssen vorher im
  Fachworkflow geprüft worden sein.
- `flz_wpdb_objects_read_uploaded_csv(...)` validiert PHP-Uploadfehler,
  tatsächlichen HTTP-Upload, `.csv`-Typ sowie standardmäßig maximal 2 MiB,
  10.000 Zeilen und 100 Spalten. Capability und Nonce bleiben Verantwortung
  des Fachworkflows.

`flz_wpdb_objects_create_csv_file()` ist seit 2.0.0 ein absichtlich
fehlschlagender Migrationspfad und erzeugt keine Datei mehr. Verbraucher müssen
auf den autorisierten Direktdownload umstellen. CSV-Zellen werden weiterhin
gegen Tabellenkalkulations-Formeln neutralisiert.

## Öffentliche API und Kompatibilität

Öffentlich unterstützt werden `FlzWpdbObject`, `FlzWpdbTransaction`,
`FlzWpdbObjectsException`, die obigen CSV-Funktionen sowie die
personenbezogenen Basisklassen `FlzPerson` und `FlzZeitraum`. Verbraucher sind
`flz_ags`, `flz_elternsprechtag` und `flz_probeunterricht`.

Additive Änderungen erfolgen in Minor-Releases. Geänderte Signaturen,
wegfallendes Verhalten oder strengere Vorbedingungen benötigen ein
Major-Release, einen dokumentierten Migrationshinweis und grüne Fast-Checks
aller drei Verbraucher. 2.0.0 entfernt bewusst den dauerhaft öffentlichen
CSV-Dateiexport; bestehende Funktionsaufrufe erhalten eine erklärende
`BadMethodCallException` statt einer stillen Dateiablage.

Der historische Befund und die Umstellung der frueheren SQL-API sind als
[Migrationsnachweis zu 2.0.0](docs/records/2.0.0-hardening-migration.md)
archiviert. Dieser Nachweis ergaenzt die aktuelle API-Beschreibung, ersetzt sie
aber nicht.

## Fehlervertrag

Datenbank-, Modell-, Hook- und Dateisystemfehler werden als
`flz_wpdb_objects\FlzWpdbObjectsException` weitergegeben. Beim Ergänzen von
Kontext bleibt die ursprüngliche Exception über `getPrevious()` erhalten.
Aufrufer sollen die vollständige Kette protokollieren und an der UI-Grenze eine
separate, escapte Meldung ohne interne Details ausgeben.

Für App-Logging steht zusätzlich
`FlzWpdbObjectsException::log_error($error, $plugin_slug, $context)` bereit.
Damit bleibt das Logformat über alle eigenen Plugins gleich: Plugin, fachlicher
Vorgang und vollständige Ursachekette werden gemeinsam protokolliert. Sichtbare
Fehlermeldungen bleiben bewusst Aufgabe des jeweiligen Fachplugins.

## Transaktionen

`FlzWpdbTransaction::run($callback, $operation)` führt mehrere
Modelloperationen atomar aus. Start, Commit und Rollback werden geprüft. Bei
einem Fehler enthält die weitergereichte Exception den fachlichen Vorgang und
die ursprüngliche Ursache; schlägt auch das Rollback fehl, werden beide Fehler
genannt.

Betroffene abhängige Plugins:

- `flz_ags`: AGs, Termine, Demo-Setup und Anmeldungen.
- `flz_elternsprechtag`: Buchungen, Termin-Reset und Lehrkräfte-Import.
- `flz_probeunterricht`: Anmeldungen, Schulplätze und Teilnehmer-Reset.

### Historischer Migrationshinweis 1.4.0

Die Tabellennamen für Modelle mit CamelCase-Klassen wurden von der alten
Kleinschreibung auf Snake-Case umgestellt. Das betrifft insbesondere
`flz_elternsprechtag` und `flz_probeunterricht`. Die Fachplugins besitzen dafür
inzwischen eigene additive Upgradepfade. Alte Tabellen werden durch dieses
Shared-Plugin weder verändert noch gelöscht.

## Checks

Die Fehler- und Transaktionspfade können ohne WordPress-Datenbank geprüft
werden:

```bash
php flz_wpdb_objects/tests/error-handling-smoke.php
php flz_wpdb_objects/tests/csv-upload-contract-smoke.php
```
