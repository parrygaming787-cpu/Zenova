<?php
// SMTP-Konfiguration für PHPMailer.
// Diese Datei liest bevorzugt Umgebungsvariablen (praktisch zum Testen),
// andernfalls werden die hier eingetragenen Platzhalter genutzt.
// Sicherheit: Niemals echte Zugangsdaten in ein öffentliches Repo committen.

// Beispiel für Strato (diese Werte sind Platzhalter):
// host: smtp.strato.de
// port: 587 (TLS) oder 465 (SSL)
// username/from_email: deine komplette Strato‑E‑Mailadresse

$env = function($k, $d = null) {
  $v = getenv($k);
  return $v === false ? $d : $v;
};

return [
  // use_smtp: true/false (auch per Umgebungsvariable SMTP_USE)
  'use_smtp' => filter_var($env('SMTP_USE', 'false'), FILTER_VALIDATE_BOOLEAN),
  'host' => $env('SMTP_HOST', 'smtp.strato.de'),
  'username' => $env('SMTP_USER', 'zenova@play-zenova.de'),
  'password' => $env('SMTP_PASS', 'Zenova.2026!'),
  'port' => (int) $env('SMTP_PORT', 587),
  'secure' => $env('SMTP_SECURE', 'tls'), // 'tls', 'ssl' oder ''
  'from_email' => $env('SMTP_FROM', 'zenova@play-zenova.de'),
  'from_name' => $env('SMTP_FROM_NAME', 'Zenova Web'),
];
