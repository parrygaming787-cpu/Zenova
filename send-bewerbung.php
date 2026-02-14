<?php
header('Content-Type: text/html; charset=utf-8');

$empfaenger = 'zenova@play-zenova.de';
$betreff = 'Bewerbung Zenova';

// Autoloader für PHPMailer (wenn mit Composer installiert)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
  require_once __DIR__ . '/vendor/autoload.php';
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

$nachricht = "Neue Bewerbung über die Zenova-Website\n\n";
$nachricht .= "--- Vorstellung ---\n" . $vorstellung . "\n\n";
$nachricht .= "--- Erfahrungen ---\n" . $erfahrung . "\n\n";
$nachricht .= "--- Warum sollten wir dich nehmen? ---\n" . $warum . "\n\n";
$nachricht .= "--- Bewerbung als ---\n" . $rolle . "\n\n";
$nachricht .= "--- Discordname ---\n" . $discord . "\n";

// From-Adresse des Servers verwenden (vermeidet viele SPF/DMARC-Probleme)
$fromAddress = 'Zenova Web <noreply@play-zenova.de>';
$header = "From: " . $fromAddress . "\r\n";
$header .= "Reply-To: " . $empfaenger . "\r\n";
$header .= "MIME-Version: 1.0\r\n";
$header .= "Content-Type: text/plain; charset=UTF-8\r\n";
$header .= "Content-Transfer-Encoding: 8bit\r\n";

// Versuche PHPMailer/SMTP wenn konfiguriert
$smtpConfig = [];
if (file_exists(__DIR__ . '/smtp_config.php')) {
  $smtpConfig = include __DIR__ . '/smtp_config.php';
}

$sentOk = false;
// PHPMailer verwenden, falls installiert und gewünscht
if (!empty($smtpConfig['use_smtp']) && class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
  try {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    // Server-Einstellungen
    if (!empty($smtpConfig['use_smtp'])) {
      $mail->isSMTP();
      $mail->Host = $smtpConfig['host'];
      $mail->SMTPAuth = true;
      $mail->Username = $smtpConfig['username'];
      $mail->Password = $smtpConfig['password'];
      $mail->SMTPSecure = !empty($smtpConfig['secure']) ? $smtpConfig['secure'] : '';
      $mail->Port = !empty($smtpConfig['port']) ? $smtpConfig['port'] : 587;
    }

    $fromEmail = !empty($smtpConfig['from_email']) ? $smtpConfig['from_email'] : 'noreply@play-zenova.de';
    $fromName = !empty($smtpConfig['from_name']) ? $smtpConfig['from_name'] : 'Zenova Web';

    $mail->setFrom($fromEmail, $fromName);
    $mail->addAddress($empfaenger);
    $mail->Subject = $betreff;
    $mail->Body = $nachricht;
    $mail->CharSet = 'UTF-8';

    $sentOk = $mail->send();
  } catch (Exception $e) {
    error_log('Bewerbung: PHPMailer Fehler: ' . $e->getMessage());
    $sentOk = false;
  }
} else {
  // Fallback auf PHP mail()
  $gesendet = @mail($empfaenger, $betreff, $nachricht, $header);
  $sentOk = (bool)$gesendet;
}

if ($sentOk) {
  header('Location: bewerbung.html?sent=1');
} else {
  error_log('Bewerbung: Mail konnte nicht gesendet werden (weder PHPMailer noch mail()).');
  header('Location: bewerbung.html?error=1');
}
exit;
