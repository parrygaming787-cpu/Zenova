# Deployment-Anleitung

## Vorbereitung (lokal im Codespace / auf deinem PC)

1. **SMTP-Konfiguration einrichten**
   - Öffne `smtp_config.php`
   - Ersetze `YOUR_STRATO_PASSWORD_HERE` mit deinem echten Passwort
   - ODER: Setze Umgebungsvariablen:
     ```bash
     export SMTP_PASS="Zenova.2026!"
     ```

2. **Testen (lokal)**
   ```bash
   php test_send.php
   # Output sollte JSON mit "ok": true sein
   ```

3. **Formular testen (optional)**
   ```bash
   php -S 0.0.0.0:8000
   # Im Browser: http://localhost:8000/bewerbung.html
   ```

## Deploy auf Strato

1. **Dateien hochladen**
   - FTP/SFTP oder File Manager je nach Strato-Account
   - Lade das **gesamte Projektverzeichnis** hoch (inkl. `vendor/` Ordner)
   - Wichtig: `composer.lock` ist in `.gitignore` — du brauchst nur `vendor/` hochladen

2. **SMTP-Konfiguration auf dem Server**
   - Falls `smtp_config.php` nicht commitet wurde, erstelle sie auf dem Server:
     ```php
     <?php
     return [
       'use_smtp' => true,
       'host' => 'smtp.strato.de',
       'username' => 'zenova@play-zenova.de',
       'password' => 'Zenova.2026!',
       'port' => 587,
       'secure' => 'tls',
       'from_email' => 'zenova@play-zenova.de',
       'from_name' => 'Zenova Web',
     ];
     ```
   - ODER setze Umgebungsvariablen in Strato-Panel

3. **Live testen**
   - Öffne `https://deine-domain.tld/bewerbung.html`
   - Fülle das Formular aus und absenden
   - Prüfe Postfach `zenova@play-zenova.de` auf die Bewerbungsmail

## Features

- ✅ Formular validiert Eingaben (Mindestlänge, Pflichtfelder)
- ✅ Bewerbungsdaten werden vollständig in der E-Mail versendet
- ✅ SMTP + PHPMailer für sichere Mail-Zustellung
- ✅ Fallback auf PHP `mail()` falls SMTP ausfällt
- ✅ Fehlerlogs in Serv-error_log

## Dateien

- `bewerbung.html` — Formular (Frontend)
- `send-bewerbung.php` — Backend, verarbeitet Formular und sendet E-Mail
- `smtp_config.php` — SMTP-Konfiguration (in .gitignore, nicht committen!)
- `test_send.php` — Test-Script zum Prüfen der Mail-Zustellung
- `composer.json` / `vendor/` — PHPMailer-Abhängigkeit

## Sicherheit

- Passwörter und Zugangsdaten gehören **nicht** ins öffentliche Repo
- `.gitignore` schützt `smtp_config.php` vor Commits
- Benutzer-Eingaben werden gefiltert (HTML-Tags entfernt, Länge begrenzt)
