<?php
// assets/php/form-process.php

// CONFIG — change this:
$toEmail = "shaikh.abdullah3132@gmail.com";       // <-- your destination email
$subjectPrefix = "Website Contact";  // subject prefix
$allowFromUserEmail = true;          // if true, uses visitor email in "Reply-To"

// Basic CORS / headers for AJAX
header('Content-Type: application/json; charset=utf-8');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Method not allowed']);
  exit;
}

// Honeypot (must be empty)
if (!empty($_POST['company'] ?? '')) {
  echo json_encode(['success' => true, 'message' => 'Thank you!']); // act like success to confuse bots
  exit;
}

// Grab + sanitize inputs
$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$message = trim($_POST['message'] ?? '');

// Validate
if ($name === '' || $email === '' || $message === '') {
  echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
  exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
  exit;
}

// Extra hardening
$maxLen = 5000;
if (mb_strlen($name) > 200 || mb_strlen($email) > 254 || mb_strlen($message) > $maxLen) {
  echo json_encode(['success' => false, 'message' => 'Input is too long.']);
  exit;
}

// Build message
$site  = $_SERVER['HTTP_HOST'] ?? 'Website';
$ip    = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
$ua    = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown UA';
$date  = date('Y-m-d H:i:s');

$subject = "$subjectPrefix: New message from {$name}";
$bodyTxt = "You have received a new message from your website contact form.\n\n"
         . "Name: {$name}\n"
         . "Email: {$email}\n"
         . "Date: {$date}\n"
         . "IP: {$ip}\n"
         . "User Agent: {$ua}\n"
         . "Site: {$site}\n\n"
         . "Message:\n{$message}\n";

$bodyHtml = nl2br(htmlentities($bodyTxt, ENT_QUOTES, 'UTF-8'));

// Choose one of the two sending methods below.
// -------------------------------------------------------------
// A) Simple PHP mail()  (works on many hosts, but not all)
// -------------------------------------------------------------
$fromEmail = "no-reply@" . preg_replace('/^www\./', '', $site);
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "From: {$subjectPrefix} <{$fromEmail}>\r\n";
if ($allowFromUserEmail) {
  $headers .= "Reply-To: {$name} <{$email}>\r\n";
}

$sent = @mail($toEmail, $subject, $bodyTxt, $headers);

// -------------------------------------------------------------
// B) PHPMailer via SMTP (more reliable)
// To use, install PHPMailer with Composer and uncomment below.
// -------------------------------------------------------------
/*
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
  $mail = new PHPMailer(true);
  $mail->isSMTP();
  $mail->Host       = 'smtp.yourprovider.com';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'smtp-username';
  $mail->Password   = 'smtp-password';
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // or PHPMailer::ENCRYPTION_SMTPS
  $mail->Port       = 587; // 465 for SMTPS

  $mail->setFrom('no-reply@yourdomain.com', $subjectPrefix);
  $mail->addAddress($toEmail);
  if ($allowFromUserEmail) {
    $mail->addReplyTo($email, $name);
  }

  $mail->isHTML(true);
  $mail->Subject = $subject;
  $mail->Body    = $bodyHtml;
  $mail->AltBody = $bodyTxt;

  $mail->send();
  $sent = true;
} catch (Exception $e) {
  $sent = false;
}
*/

if ($sent) {
  echo json_encode(['success' => true, 'message' => 'Thanks! Your message has been sent.']);
} else {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Sorry, we could not send your message. Please try again later.']);
}
