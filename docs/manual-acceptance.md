# Manuelles Abnahmeprotokoll – FLZ WPDB Objects

Dieses Formular dokumentiert die Abnahme des exakten Release-Artefakts. Keine
Secrets, produktiven Pfade oder personenbezogenen Echtdaten eintragen. Pro Fall
genau ein Ergebnis markieren: `[ ] erfolgreich`, `[ ] nicht erfolgreich` oder
`[ ] nicht geprüft`; Abweichungen und fehlende Prüfungen werden begründet.

## Kopfdaten

| Feld | Eintrag |
|---|---|
| Datum / Prüfer*in | 19.10.2026 / Auftraggeber (vollständige menschliche Fachabnahme bestätigt) |
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

### Automatisierter Teilnachweis vom 18. Oktober 2026

- Geprüfter Stand: `47fd02ed900dd2df5039a0e60ac795c0dfd6372d`
  (`flz_wpdb_objects` 2.0.0).
- `./scripts/check-fast` war erfolgreich: PHP-Syntax sowie die CSV-Upload-,
  Fehlerbehandlungs- und Value-Object-Smokes bestanden. PHPCS lief dabei mit
  der vorhandenen lokalen Toolchain.
- Die drei abhängigen Komponenten bestanden anschließend jeweils
  `./scripts/check-fast`: `flz_elternsprechtag`, `flz_probeunterricht` und
  `flz_ags`. Damit sind ihre Abhängigkeits- sowie CSV- und Download-Sicherheits-
  verträge gegen diesen Providerstand automatisiert belegt.
- Zwei unabhängige Bauten wurden mit
  `scripts/verify-component-release flz_wpdb_objects
  repositories/flz_wpdb_objects /tmp/flz-wpdb-objects-release-verify-20261018`
  bytegleich verifiziert. Das Artefakt
  `flz_wpdb_objects-2.0.0.zip` hat SHA-256
  `45f6392c436c7a11fde8536e9c65c919f79136033f737c74fa2e40d97fe1ce73`.
- Dieser Bau- und Vertragstest ersetzt keine fachlichen CSV-, Fehler- oder
  manuellen Prüffälle; der separate Installations- und Upgrade-Nachweis folgt.

### Automatisierter Installations- und Upgrade-Teilnachweis vom 18. Oktober 2026

- Eine neue, nach dem Durchlauf vollständig gelöschte WordPress-7.1.3-
  Installation unter DDEV (PHP 8.3, MariaDB 10.11) verwendete das oben
  genannte exakte 2.0.0-ZIP.
- Die Frischinstallation und Aktivierung waren erfolgreich. Nach vorherigem
  WordPress-Initialisierungslauf blieben Tabellen und Optionen unverändert;
  ausgenommen ist erwartungsgemäß die WordPress-Option `active_plugins`.
- Der Upgradepfad vom unmittelbaren Vorvertragsstand 1.4.0 auf 2.0.0 wurde
  erfolgreich geprüft. Anschließend war das Plugin aktiv, zeigte Version
  2.0.0 und ließ sich wieder ohne Fehler deaktivieren.
- Datenbank, temporärer WordPress-Baum und bereitgestellte Testarchive wurden
  nach dem Lauf nachweislich entfernt. Die fachlichen CSV-, Fehler- und
  manuellen Prüffälle bleiben offen.

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
| WDB-01 | Artefakt in sauberer WordPress-Instanz installieren und aktivieren. | Aktivierung ohne Warnung; keine fachlichen Tabellen, Rollen oder Optionen entstehen. | [x] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | Menschliche Abnahme durch Auftraggeber am 19.10.2026 bestätigt. |
| WDB-02 | Zulässige kleine UTF-8-CSV über einen neutralen Testverbraucher einlesen. | Inhalt wird innerhalb der dokumentierten Zeilen-, Spalten-, Größen- und MIME-Grenzen gelesen. | [x] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | Menschliche Abnahme durch Auftraggeber am 19.10.2026 bestätigt. |
| WDB-03 | Zu große, fehlerhafte und manipuliert deklarierte Datei testen. | Jeder Fall wird sicher abgewiesen; keine Datei und keine fachliche Nebenwirkung bleiben zurück. | [x] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | Menschliche Abnahme durch Auftraggeber am 19.10.2026 bestätigt. |
| WDB-04 | Direkten CSV-Download eines Verbrauchers prüfen. | Download besitzt korrekte Header; keine öffentliche Upload-Datei entsteht. | [x] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | Menschliche Abnahme durch Auftraggeber am 19.10.2026 bestätigt. |
| WDB-05 | Fehler- und Transaktionspfad mit synthetischen Daten auslösen. | Rollback ist vollständig; Log enthält technische Ursache, aber keine Nutzdaten. | [x] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | Menschliche Abnahme durch Auftraggeber am 19.10.2026 bestätigt. |
| WDB-06 | Plugin deaktivieren und Verbraucherzustand prüfen. | Deaktivierung löscht keine Verbraucherdaten und verursacht keinen Fatal Error. | [x] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | Menschliche Abnahme durch Auftraggeber am 19.10.2026 bestätigt. |

## Abschlussentscheidung

| Feld | Eintrag |
|---|---|
| Erfolgreich / nicht erfolgreich / nicht geprüft | erfolgreich |
| Kritische Abweichungen / Tickets | keine bestätigt |
| Rückbau geprüft | [x] ja [ ] nein |
| Gesamtentscheidung | [x] abgenommen [ ] mit Auflagen abgenommen [ ] nicht abgenommen |
| Name / Datum | Auftraggeber / 19.10.2026 |
