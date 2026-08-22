# Manuelles Abnahmeprotokoll – FLZ WPDB Objects

Dieses Formular dokumentiert die Abnahme des exakten Release-Artefakts. Keine
Secrets, produktiven Pfade oder personenbezogenen Echtdaten eintragen. Pro Fall
genau ein Ergebnis markieren: `[ ] erfolgreich`, `[ ] nicht erfolgreich` oder
`[ ] nicht geprüft`; Abweichungen und fehlende Prüfungen werden begründet.

## Kopfdaten

| Feld | Eintrag |
|---|---|
| Datum / Prüfer*in | |
| Umgebung / WordPress / PHP / Datenbank | |
| Plugin-Version / vollständiger Git-Commit | |
| Artefaktname / SHA-256 | |
| Ausgangsversion für Upgrade | |

## Automatisierte Nachweise

| Nachweis | Kommando / Lauf | Ergebnis / Beleg |
|---|---|---|
| PR-/`main`-CI / PHP 8.1 und 8.5 | Workflow-Lauf / vollständiger Commit | |
| Komponenten- und Security-Smokes | `./scripts/check-fast` | |
| PHP-Line-Coverage / Baseline / Ziel 85 % | | |
| Provider-/Consumer-Verträge | | |
| Reproduzierbarkeit und Archivinhalt | | |

## Manuelle Prüffälle

### Technischer ZIP-Teilnachweis vom 22. August 2026

- Umgebung: DDEV, WordPress 7.1, PHP 8.3, MariaDB 10.11.
- Exaktes Artefakt: `flz_wpdb_objects-2.0.0.zip`, Commit
  `62934a1f65d89d6122e19351a7f23354015f471e`, SHA-256
  `74f9271ab35c73953f2e3949217b1550e0f0749b9a495fb3232041d2f2d32536`.
- Zwei Bauten waren bytegleich; Archivwurzel, Manifest, SHA und installierter
  Dateibaum wurden geprüft. WP-CLI-Installation, Aktivstatus, Deaktivierung,
  Reaktivierung und HTTP 200 waren erfolgreich.
- Rückbau über Snapshot `before-exact-zip-acceptance-20260822` und die sechs
  ursprünglichen Quell-Symlinks war erfolgreich.
- Noch nicht belegt: saubere Frischinstallation, Upgrade aus der relevanten
  Vorversion sowie die nachfolgenden fachlichen und manuellen Prüffälle.

| ID | Prüfschritte | Erwartetes Ergebnis | Ergebnis | Warum / Beleg / Abweichung |
|---|---|---|---|---|
| WDB-01 | Artefakt in sauberer WordPress-Instanz installieren und aktivieren. | Aktivierung ohne Warnung; keine fachlichen Tabellen, Rollen oder Optionen entstehen. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| WDB-02 | Zulässige kleine UTF-8-CSV über einen neutralen Testverbraucher einlesen. | Inhalt wird innerhalb der dokumentierten Zeilen-, Spalten-, Größen- und MIME-Grenzen gelesen. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| WDB-03 | Zu große, fehlerhafte und manipuliert deklarierte Datei testen. | Jeder Fall wird sicher abgewiesen; keine Datei und keine fachliche Nebenwirkung bleiben zurück. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| WDB-04 | Direkten CSV-Download eines Verbrauchers prüfen. | Download besitzt korrekte Header; keine öffentliche Upload-Datei entsteht. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| WDB-05 | Fehler- und Transaktionspfad mit synthetischen Daten auslösen. | Rollback ist vollständig; Log enthält technische Ursache, aber keine Nutzdaten. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| WDB-06 | Plugin deaktivieren und Verbraucherzustand prüfen. | Deaktivierung löscht keine Verbraucherdaten und verursacht keinen Fatal Error. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |

## Abschlussentscheidung

| Feld | Eintrag |
|---|---|
| Erfolgreich / nicht erfolgreich / nicht geprüft | |
| Kritische Abweichungen / Tickets | |
| Rückbau geprüft | [ ] ja [ ] nein |
| Gesamtentscheidung | [ ] abgenommen [ ] mit Auflagen abgenommen [ ] nicht abgenommen |
| Name / Datum | |
