<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

requirePost();

$data = getJsonInput();

checkHoneypot($data);

$name = requiredField(
    $data,
    'name',
    150
);

$email = requiredEmail(
    $data,
    'email'
);

$phone = cleanText(
    isset($data['phone']) ? (string) $data['phone'] : '',
    50
);

$organisation = cleanText(
    isset($data['organisation'])
        ? (string) $data['organisation']
        : '',
    200
);

$subject = cleanText(
    isset($data['subject'])
        ? (string) $data['subject']
        : 'Website enquiry',
    250
);

$message = requiredField(
    $data,
    'message',
    10000
);

validateEmailHeader($email);

$emailSubject =
    '[AION-IA Website] ' .
    $subject;

$body = '';

$body .= "AION-IA WEBSITE ENQUIRY\n";
$body .= "=======================\n\n";

$body .= "Name: " . $name . "\n";
$body .= "Email: " . $email . "\n";
$body .= "Phone: " . ($phone ?: 'Not provided') . "\n";
$body .= "Organisation: " .
    ($organisation ?: 'Not provided') . "\n";

$body .= "Subject: " . $subject . "\n";

$body .= "\nMessage\n";
$body .= "-------\n";
$body .= $message . "\n";

$body .= "\nSubmission details\n";
$body .= "------------------\n";
$body .= "Time: " . currentTimestamp() . "\n";
$body .= "IP: " .
    ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";

$sent = sendAionEmail(
    CONTACT_EMAIL,
    $emailSubject,
    $body,
    $email
);

if (!$sent) {

    jsonResponse(
        false,
        'We could not process your message at this time. Please try again or email info@aion-ia.in directly.',
        500
    );
}

jsonResponse(
    true,
    'Message sent successfully. AION-IA will respond shortly.'
);