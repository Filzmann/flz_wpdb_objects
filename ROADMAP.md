# Roadmap: flz_wpdb_objects

## Prüfstatus

**Funktionsstand 2.0.0; Release-Gate in Übernahmephase 1 blockiert.** SQL-Werte werden zentral gebunden, dynamische
Bezeichner validiert und Fehler gekapselt. Der öffentliche persistente
CSV-Dateipfad ist abgeschaltet, Uploads sind begrenzt und die öffentliche API
samt Breaking-Change-Prozess ist dokumentiert. PR-/Main-CI einschließlich
branchgleicher Verbrauchertests ist lokal konfiguriert; vor einem Tag fehlen
ein grüner Remote-Lauf, gemessene No-Regression-Coverage, vollständige
Verbraucher-/DDEV-Abnahme und ein reproduzierbar geprüftes Release-Artefakt.
Das ausfüllbare Protokoll liegt unter `docs/manual-acceptance.md`.

Der PHPCOV-/Xdebug-Job ist konfiguriert. Eine PHP-Baseline darf erst aus dem
reproduzierbaren Remote-Lauf übernommen werden und bleibt bis dahin `pending`.

## P1

1. **Erledigt:** `flz_wpdb_objects_create_csv_file()` als öffentlichen Exportpfad ablösen.
   Bevorzugter Vertrag: CSV sicher im Speicher erzeugen und nach vorgelagerter
   Autorisierung direkt streamen. Persistente Dateien nur für ausdrücklich
   genehmigte, nicht personenbezogene Anwendungsfälle über WordPress Filesystem
   API und dokumentierten Lebenszyklus zulassen.
2. **Erledigt:** Upload-Helper um vollständige serverseitige Dateiprüfung erweitern oder die
   Verantwortung als überprüfbaren Vertrag präzisieren: Uploadfehlercode,
   tatsächlicher Upload, Größen-/Zeilenlimits, MIME/Dateityp und sichere
   Fehlerfälle. Capability/Nonce verbleiben nachweisbar beim Fachworkflow.
3. **In Prüfung:** Öffentliche API, unterstützte Verbraucher und Breaking-Change-Prozess
   dokumentieren; Verbraucher-Smokes für Elternsprechtag, Probeunterricht und
   AGs als Gate ausführen.

## P2

1. Tests für SQL-Identifier, Null-/IN-Abfragen, Hydrierung, Transaktionsfehler,
   CSV-Formelneutralisierung, große/ungültige Uploads und Download-Header
   ergänzen; reale WordPress-DB-Integration separat prüfen.
2. Schema-Helfer dokumentieren und sicherstellen, dass Fachmodelle eine eigene
   Schema-/Versionsquelle behalten; destruktive Tabellenmethoden klar als
   privilegierte interne Werkzeuge kennzeichnen.
3. **Erledigt:** Plugin-Header (Platzhalter-URIs, Textdomain, unterstützte PHP-/WordPress-
   Version) und Formatierung bereinigen.

## P3

1. Coverage der Kernklassen messen und die 85-Prozent-Zielmarke für wesentlich
   geänderten Code nachweisen.
2. Release-Artefakt und Rückwärtskompatibilität unabhängig von den
   Fachplugins reproduzierbar prüfen.
