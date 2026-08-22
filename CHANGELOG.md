# Changelog

Alle wesentlichen Änderungen an `flz_wpdb_objects` werden in dieser Datei
dokumentiert. Ein Datum wird erst bei einer tatsächlichen Veröffentlichung
ergänzt.

## Unreleased

- Reproduzierbare PR-/Main-CI für PHP 8.1 und 8.5 ergänzt.
- Branchgleiche Verbraucherprüfungen für die drei Fachplugins ergänzt.
- Formale Lizenz- und Abnahmenachweise in den Delivery-Vertrag aufgenommen.
- PHP-No-Regression-Ratsche bei 54,99 Prozent remote enforced.
- Reproduzierbaren Ein-Wurzel-ZIP-Bau mit Manifest, SHA-256 und CI-Prüfung
  ergänzt.

## 2.0.0

- Dauerhaft öffentliche CSV-Dateien durch Speicher- und Direktdownload-APIs
  ersetzt; der alte Dateipfad schlägt erklärend fehl.
- Validierten CSV-Uploadvertrag mit Größen-, Zeilen- und Spaltenlimits ergänzt.
- Datenbank-, Transaktions- und Fehlerverträge gehärtet und getestet.
