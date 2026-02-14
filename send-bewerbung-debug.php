<?php
// Debug-Version zum Testen des Formular-Submits
header('Content-Type: text/plain; charset=utf-8');

// Discord Config laden
$discordConfig = [];
if (file_exists(__DIR__ . '/discord_webhook_config.php')) {
  $discordConfig = include __DIR__ . '/discord_webhook_config.php';
}

// Debug-Output
echo "=== DEBUG: send-bewerbung.php ===\n\n";
echo "POST-Daten erhalten: " . (count($_POST) > 0 ? "JA" : "NEIN") . "\n";
echo "POST-Keys: " . implode(', ', array_keys($_POST)) . "\n\n";

// Sauberfunktion
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

echo "Eingaben validiert:\n";
echo "- discord: '" . substr($discord, 0, 20) . "...'\n";
echo "- rolle: '$rolle'\n";
echo "- vorstellung länge: " . strlen($vorstellung) . " Zeichen\n\n";

// Validierung
if ($vorstellung === '' || $erfahrung === '' || $warum === '' || $rolle === '' || $discord === '') {
  echo "FEHLER: Pflichtfeld leer!\n";
  exit;
}

if (strlen($vorstellung) < 50) {
  echo "FEHLER: Vorstellung zu kurz (mindestens 50 Zeichen)\n";
  exit;
}

echo "Validierung: OK\n";
echo "Webhook URL: " . (empty($discordConfig['webhook_url']) ? "NICHT GESETZT" : "OK") . "\n\n";

// Webhook testen
if (!empty($discordConfig['webhook_url'])) {
  echo "Sende Discord Webhook...\n";
  
  $embed = [
    'title' => '📝 Neue Bewerbung eingegangen',
    'color' => $discordConfig['embed_color'] ?? 3447003,
    'fields' => [
      ['name' => '👤 Discord Name', 'value' => $discord, 'inline' => true],
      ['name' => '🎯 Position', 'value' => $rolle, 'inline' => true],
      ['name' => '📖 Vorstellung', 'value' => $vorstellung, 'inline' => false],
      ['name' => '💪 Erfahrungen', 'value' => $erfahrung, 'inline' => false],
      ['name' => '⭐ Warum wir dich nehmen sollten', 'value' => $warum, 'inline' => false]
    ],
    'footer' => ['text' => 'Zenova Bewerbungsformular'],
    'timestamp' => date('c')
  ];

  $payload = ['username' => $discordConfig['server_name'] ?? 'Zenova', 'embeds' => [$embed]];
  
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
  $curlError = curl_error($ch);
  curl_close($ch);

  echo "HTTP Status: $httpCode\n";
  if ($curlError) echo "Curl Fehler: $curlError\n";
  if ($response) echo "Response: " . substr($response, 0, 100) . "\n";
  
  if ($httpCode >= 200 && $httpCode < 300) {
    echo "\n✅ SUCCESS - Bewerbung wurde zu Discord gesendet!";
  } else {
    echo "\n❌ FEHLER - HTTP $httpCode";
  }
} else {
  echo "FEHLER: Webhook URL nicht konfiguriert!\n";
}
