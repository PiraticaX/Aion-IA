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

$phone = requiredField(
    $data,
    'phone',
    50
);

$paper = requiredField(
    $data,
    'whitepaper',
    300
);

validateEmailHeader($email);

$emailSubject =
    '[AION-IA Whitepaper Access] ' .
    $paper;

$body = '';

$body .= "AION-IA WHITEPAPER ACCESS REQUEST\n";
$body .= "=================================\n\n";

$body .= "Name: " . $name . "\n";
$body .= "Email: " . $email . "\n";
$body .= "Phone: " . $phone . "\n";
$body .= "Whitepaper: " . $paper . "\n";

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
        'We could not process your request at this time. Please try again.',
        500
    );
}

jsonResponse(
    true,
    'Access request received.'
);