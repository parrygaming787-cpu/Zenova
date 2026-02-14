<?php
header('Content-Type: text/html; charset=utf-8');

$empfaenger = 'zenova@play-zenova.de';
$betreff = 'Bewerbung Zenova';

$vorstellung = isset($_POST['vorstellung']) ? trim($_POST['vorstellung']) : '';
$erfahrung    = isset($_POST['erfahrung']) ? trim($_POST['erfahrung']) : '';
$warum        = isset($_POST['warum']) ? trim($_POST['warum']) : '';
$rolle        = isset($_POST['rolle']) ? trim($_POST['rolle']) : '';
$discord      = isset($_POST['discord']) ? trim($_POST['discord']) : '';

if ($vorstellung === '' || $erfahrung === '' || $warum === '' || $rolle === '' || $discord === '') {
  header('Location: bewerbung.html?error=1');
  exit;
}

$nachricht = "Neue Bewerbung über die Zenova-Website\n\n";
$nachricht .= "--- Vorstellung ---\n" . $vorstellung . "\n\n";
$nachricht .= "--- Erfahrungen ---\n" . $erfahrung . "\n\n";
$nachricht .= "--- Warum sollten wir dich nehmen? ---\n" . $warum . "\n\n";
$nachricht .= "--- Bewerbung als ---\n" . $rolle . "\n\n";
$nachricht .= "--- Discordname ---\n" . $discord . "\n";

$header = "From: " . $empfaenger . "\r\n";
$header .= "Reply-To: " . $empfaenger . "\r\n";
$header .= "Content-Type: text/plain; charset=UTF-8\r\n";

$gesendet = @mail($empfaenger, $betreff, $nachricht, $header);

if ($gesendet) {
  header('Location: bewerbung.html?sent=1');
} else {
  header('Location: bewerbung.html?error=1');
}
exit;
