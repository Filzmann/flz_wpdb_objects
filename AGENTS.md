# Regeln für flz_wpdb_objects

Dieses Repository enthält ausschließlich das Shared-Plugin
`flz_wpdb_objects`. Quellcode wird hier bearbeitet und in WordPress nur per
Symlink bereitgestellt. Core, fremde Erweiterungen, Uploads, Secrets und
produktive Konfiguration bleiben außerhalb des Scopes.

Das Plugin veröffentlicht kleine Datenbank-, Transaktions-, CSV- und
Fehlerverträge. Es besitzt keine fachlichen Tabellen, UI, Rollen oder
Berechtigungsentscheidungen. Öffentliche APIs bleiben rückwärtskompatibel oder
erhalten einen dokumentierten Migrationspfad und Verbraucherprüfung.

- SQL-Werte binden; dynamische Identifier ausschließlich intern validieren.
- Destruktive Tabellenmethoden nur hinter einer bereits autorisierten Grenze.
- CSV-Helfer erzeugen keine dauerhaft öffentliche personenbezogene Datei.
- Upload-Helper prüfen Dateifehler, Typ und Limits; Capability und Nonce bleiben
  expliziter Vertrag des Fachworkflows.
- Technische Logs enthalten Vorgang und Ursache, aber keine Secrets oder
  personenbezogenen Payloads.
- Änderungen beobachtbaren Verhaltens testgetrieben ausführen; Sicherheitsfälle
  belegen erlaubte und verweigerte Pfade ohne verbotene Nebenwirkung.

Vor Änderungen Verbraucher bestimmen: derzeit `flz_elternsprechtag`,
`flz_probeunterricht` und `flz_ags`. Mindestens
`php tests/error-handling-smoke.php` und `./scripts/check-fast` ausführen.
WordPress-/Datenbankintegration separat benennen. Keine Commits, Pushes oder
Deployments ohne ausdrückliche Freigabe; nie `git add .` verwenden.
