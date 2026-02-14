# Discord Webhook Setup für Bewerbungen

## So richtest du den Discord Webhook ein

1. **Öffne deinen Discord Server-Einstellungen**
   - Klick auf den Servernamen (oben links)
   - → Einstellungen → Integrations

2. **Neuen Webhook erstellen**
   - Klick "Webhooks"
   - Klick "Neuer Webhook"
   - Name: z. B. "Zenova Bewerbungen"
   - Channel: Wähle `#bewerbungen` (oder erstelle einen neuen)

3. **Webhook-URL kopieren**
   - Klick "Webhook kopieren" oder kopiere die URL manuell
   - Die URL sieht so aus: `https://discordapp.com/api/webhooks/123456789/abcdefg...`

4. **URL in `discord_webhook_config.php` eintragen**
   - Öffne `discord_webhook_config.php`
   - Ersetze `YOUR_WEBHOOK_ID/YOUR_WEBHOOK_TOKEN` mit deiner echten URL
   - Oder: Stelle die Umgebungsvariable `DISCORD_WEBHOOK_URL`

## Testen

```bash
php test_discord.php
```

Wenn es funktioniert, siehst du eine Test-Botschaft im Discord Channel.

## Formular testen

1. Starte den PHP-Server:
```bash
php -S 127.0.0.1:8000
```

2. Öffne `http://localhost:8000/bewerbung.html`

3. Fülle das Formular aus und sende

4. Prüfe deinen Discord Channel — die Bewerbung sollte als schönes Embed ankommen

## Deployment auf Strato

1. Lade `discord_webhook_config.php` mit der echten Webhook-URL hoch
2. Oder setze die Umgebungsvariable auf dem Server
3. Fertig — Bewerbungen landen direkt in Discord

## Sicherheit

- Webhook-URL gehört **nicht** ins öffentliche Repo
- `.gitignore` schützt `discord_webhook_config.php` vor Commits
- Die URL gibt volle Schreibrechte auf den Channel — behandle sie geheim!
