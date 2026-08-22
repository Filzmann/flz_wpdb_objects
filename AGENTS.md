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

## Commit-, Coverage- und Release-Gates

- Der aktuelle Übernahmestand ist Phase 1: PR-/Main-CI, Lizenz, Changelog und
  branchgleiche Verbrauchertests sind lokal konfiguriert. Bis zum ersten
  grünen Remote-Lauf und den Coverage-Gates bleiben normale Produkt- und
  Releasecommits blockiert; ausdrücklich beauftragte Quality-Rollout-Commits
  dürfen die fehlende Infrastruktur schrittweise herstellen.
- Vor einem späteren normalen Commit sind Status, Diff-Statistik und vollständige
  Dateiliste zu zeigen; fokussierte Tests, `./scripts/check-fast`, relevante
  Provider-/Consumer-Tests, CI und Coverage-Gates müssen grün sein. Dateien
  werden einzeln gestaged; `git add .` bleibt verboten.
- PHP-Line-Coverage und – bei ausführbarem JavaScript – JavaScript-Line-Coverage
  werden getrennt ausgewiesen. Eine gemessene Baseline darf nicht sinken;
  neuer oder wesentlich geänderter Code erreicht mindestens 85 Prozent,
  Sicherheitsinvarianten unabhängig davon vollständig.
- Ein Fast- oder Diagnosecheck ist kein Releaseurteil. Ein Release braucht ein
  sauberes Repository, konsistente Version/Changelog/Lizenz, vollständig
  ausgefülltes `docs/manual-acceptance.md`, ein reproduzierbares Ein-Wurzel-
  Archiv, Manifest und SHA-256 sowie geprüfte Installation, Upgrade,
  Deaktivierung, Datenschutz, sichtbare UI und Rückbau aus dem exakten Artefakt.
- Bauen, Signieren, Taggen, Pushen, Publizieren und Deployen bleiben getrennte,
  ausdrücklich zu autorisierende Aktionen.
