<?php
header('Content-Type: text/html; charset=utf-8');

// Discord Webhook Konfiguration laden
$discordConfig = [];
if (file_exists(__DIR__ . '/discord_webhook_config.php')) {
  $discordConfig = include __DIR__ . '/discord_webhook_config.php';
}

// kleine Hilfsfunktion: säubern und Länge begrenzen
function clean_input($s, $max = 5000) {
  $s = trim((string)$s);
  $s = strip_tags($s);
  if (strlen($s) > $max) $s = substr($s, 0, $max);
  return $s;
}

$vorstellung = isset($_POST['vorstellung']) ? clean_input($_POST['vorstellung'], 8000) : '';
$erfahrung    = isset($_POST['erfahrung']) ? clean_input($_POST['erfahrung'], 6000) : '';
$warum        = isset($_POST['warum']) ? clean_input($_POST['warum'], 6000) : '';
$rolle        = isset($_POST['rolle']) ? clean_input($_POST['rolle'], 200) : '';
$discord      = isset($_POST['discord']) ? clean_input($_POST['discord'], 200) : '';

// Pflichtfelder prüfen
if ($vorstellung === '' || $erfahrung === '' || $warum === '' || $rolle === '' || $discord === '') {
  header('Location: bewerbung.html?error=1');
  exit;
}

// zusätzliche Mindestprüfung: Vorstellung sollte sinnvoll lang sein
if (strlen($vorstellung) < 50) {
  header('Location: bewerbung.html?error=1');
  exit;
}

// Versuche Bewerbung zu Discord zu senden
$sentOk = false;

if (!empty($discordConfig['webhook_url'])) {
  // Discord Embed erstellen
  $embed = [
    'title' => '📝 Neue Bewerbung eingegangen',
    'color' => $discordConfig['embed_color'] ?? 3447003,
    'fields' => [
      [
        'name' => '👤 Discord Name',
        'value' => $discord,
        'inline' => true
      ],
      [
        'name' => '🎯 Position',
        'value' => $rolle,
        'inline' => true
      ],
      [
        'name' => '📖 Vorstellung',
        'value' => $vorstellung,
        'inline' => false
      ],
      [
        'name' => '💪 Erfahrungen',
        'value' => $erfahrung,
        'inline' => false
      ],
      [
        'name' => '⭐ Warum wir dich nehmen sollten',
        'value' => $warum,
        'inline' => false
      ]
    ],
    'footer' => [
      'text' => 'Zenova Bewerbungsformular'
    ],
    'timestamp' => date('c')
  ];

  $payload = [
    'username' => $discordConfig['server_name'] ?? 'Zenova',
    'embeds' => [$embed]
  ];

  $ch = curl_init($discordConfig['webhook_url']);
  curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10
  ]);

  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  // HTTP 204 (No Content) ist auch OK bei Discord Webhooks
  if (($httpCode >= 200 && $httpCode < 300) || $httpCode == 204) {
    $sentOk = true;
    error_log('Bewerbung erfolgreich zu Discord versendet (HTTP ' . $httpCode . ')');
  } else {
    error_log('Bewerbung Discord: HTTP ' . $httpCode . ' - ' . $response);
    $sentOk = false;
  }
} else {
  error_log('Bewerbung: Discord Webhook URL nicht konfiguriert');
  $sentOk = false;
}

if ($sentOk) {
  header('Location: bewerbung.html?sent=1');
} else {
  error_log('Bewerbung: Mail konnte nicht gesendet werden (weder PHPMailer noch mail()).');
  header('Location: bewerbung.html?error=1');
}
exit;
