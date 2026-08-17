=== SEV hreflang ===
Contributors: hfranz
Tags: hreflang, seo, multilingual, alternate, international seo
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Hinterlegt hreflang-Alternates für Seiten, Beiträge und Kategorien und gibt sie korrekt im <head> aus – inklusive Self-Referencing-Link.

== Description ==

SEV hreflang fügt Seiten, Beiträgen und Kategorien ein Feld hinzu, in dem sich beliebig viele alternative Sprachversionen (hreflang + URL) hinterlegen lassen. Das Plugin gibt daraus automatisch `<link rel="alternate" hreflang="…">`-Tags im `<head>` der jeweiligen Ansicht aus – inklusive eines Self-Referencing-Links auf die Seite selbst, wie von Google empfohlen.

**Warum kein Block?**

hreflang-Angaben werden von Suchmaschinen nur im `<head>` (bzw. per HTTP-Header oder Sitemap) ausgewertet, nicht im sichtbaren Seiteninhalt. Ein Block, den man in den Content einfügt, würde im `<body>` landen und schlicht ignoriert. Deshalb setzt dieses Plugin bewusst auf Post-/Term-Meta statt auf einen Block.

**Funktionsumfang**

* Sidebar-Panel im Block-Editor für Seiten und Beiträge.
* Eigenes Formularfeld beim Anlegen/Bearbeiten von Kategorien (dort gibt es keinen Block-Editor).
* Unterstützt auch die statische "Beiträgeseite" (Einstellungen > Lesen).
* Automatischer Self-Referencing-hreflang-Link, basierend auf der Sprache der Website.
* Keine Einstellungen, keine externen Aufrufe – Aktivieren reicht.

Für Multisite: Da jede Site ihre eigene Datenbanktabelle für Optionen/Meta hat, lässt sich das Plugin ganz normal pro Site (de-)aktivieren.

== Installation ==

1. Lade den Ordner `sev-hreflang` in das Verzeichnis `/wp-content/plugins/` hoch.
2. Aktiviere das Plugin über das Menü "Plugins" in WordPress.
3. Beim Bearbeiten einer Seite/eines Beitrags: Sidebar öffnen, Panel "hreflang (alternative Sprachversionen)" ausklappen. Bei Kategorien: Feld direkt im Formular.

== Frequently Asked Questions ==

= Warum sehe ich keinen Block im Editor? =

Es gibt bewusst keinen Block. hreflang-Tags müssen im `<head>` stehen, nicht im Content – ein Content-Block würde von Suchmaschinen ignoriert.

= Funktioniert das Plugin mit WPML/Polylang? =

Das Plugin ist unabhängig von Mehrsprachigkeits-Plugins nutzbar und verwaltet die hreflang-Werte manuell pro Seite/Beitrag/Kategorie. Falls WPML oder Polylang aktiv ist und bereits hreflang ausgibt, kann es zu doppelten Tags kommen – in dem Fall bitte nur eine der beiden Lösungen verwenden.

= Wird ein x-default-Wert unterstützt? =

Ja – das hreflang-Feld ist ein freies Textfeld, `x-default` kann dort direkt eingetragen werden.

== Screenshots ==

1. Sidebar-Panel im Block-Editor

== Changelog ==

= 1.0.0 =
* Erste Veröffentlichung.
