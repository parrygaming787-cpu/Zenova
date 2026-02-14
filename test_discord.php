<?php
header('Content-Type: text/plain; charset=utf-8');

$discordConfig = [];
if (file_exists(__DIR__ . '/discord_webhook_config.php')) {
  $discordConfig = include __DIR__ . '/discord_webhook_config.php';
}

$result = ['ok' => false, 'error' => null];

if (empty($discordConfig['webhook_url'])) {
  $result['error'] = 'Webhook URL nicht konfiguriert';
  echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  exit;
}

// Test-Embed
$embed = [
  'title' => '🧪 Test-Bewerbung (Discord Webhook funktioniert!)',
  'color' => 65280, // Grün
  'fields' => [
    [
      'name' => '👤 Discord Name',
      'value' => 'TestUser#1234',
      'inline' => true
    ],
    [
      'name' => '🎯 Position',
      'value' => 'Builder',
      'inline' => true
    ],
    [
      'name' => '📖 Vorstellung',
      'value' => 'Dies ist ein Test der Discord Webhook Funktionalität.',
      'inline' => false
    ]
  ],
  'footer' => ['text' => 'Test vom ' . date('d.m.Y H:i:s')],
  'timestamp' => date('c')
];

$payload = [
  'username' => 'Zenova Test',
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

if ($httpCode >= 200 && $httpCode < 300) {
  $result['ok'] = true;
} else {
  $result['error'] = 'HTTP ' . $httpCode . ' - ' . $response;
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
