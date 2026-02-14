<?php
header('Content-Type: text/plain; charset=utf-8');

$empfaenger = 'zenova@play-zenova.de';
$betreff = 'Testmail von Zenova';
$nachricht = "Dies ist eine Testmail, um die Mail-Konfiguration zu prüfen.\n\nWenn du diese Mail bekommst, funktioniert die Konfiguration.";

// Autoloader für PHPMailer (wenn mit Composer installiert)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
  require_once __DIR__ . '/vendor/autoload.php';
}

$smtpConfig = [];
if (file_exists(__DIR__ . '/smtp_config.php')) {
  $smtpConfig = include __DIR__ . '/smtp_config.php';
}

$result = ['ok' => false, 'method' => null, 'error' => null];

if (!empty($smtpConfig['use_smtp']) && class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
  $result['method'] = 'phpmailer_smtp';
  try {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $smtpConfig['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtpConfig['username'];
    $mail->Password = $smtpConfig['password'];
    $mail->SMTPSecure = !empty($smtpConfig['secure']) ? $smtpConfig['secure'] : '';
    $mail->Port = !empty($smtpConfig['port']) ? $smtpConfig['port'] : 587;
    $fromEmail = !empty($smtpConfig['from_email']) ? $smtpConfig['from_email'] : 'noreply@play-zenova.de';
    $fromName = !empty($smtpConfig['from_name']) ? $smtpConfig['from_name'] : 'Zenova Web';

    $mail->setFrom($fromEmail, $fromName);
    $mail->addAddress($empfaenger);
    $mail->Subject = $betreff;
    $mail->Body = $nachricht;
    $mail->CharSet = 'UTF-8';

    if ($mail->send()) {
      $result['ok'] = true;
    } else {
      $result['error'] = 'Unbekannter Fehler beim Senden mit PHPMailer';
    }
  } catch (Exception $e) {
    $result['error'] = $e->getMessage();
  }
} else {
  $result['method'] = 'php_mail_fallback';
  $headers = "From: Zenova Web <noreply@play-zenova.de>\r\n";
  $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
  $sent = @mail($empfaenger, $betreff, $nachricht, $headers);
  if ($sent) $result['ok'] = true;
  else $result['error'] = 'PHP mail() returned false (kein Mail-Transport konfiguriert)';
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
