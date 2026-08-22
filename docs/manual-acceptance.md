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
| Komponenten- und Security-Smokes | `./scripts/check-fast` | |
| PHP-Line-Coverage / Baseline / Ziel 85 % | | |
| Provider-/Consumer-Verträge | | |
| Reproduzierbarkeit und Archivinhalt | | |

## Manuelle Prüffälle

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
