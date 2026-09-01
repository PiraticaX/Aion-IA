<?php
/*
 * ============================================================
 * AION-IA
 * Secure form configuration
 * ============================================================
 *
 * IMPORTANT:
 * No credentials are stored in the frontend.
 *
 * This file is executed only by PHP on Hostinger.
 * ============================================================
 */

declare(strict_types=1);

/*
 * Where notifications are sent.
 */
define('CONTACT_EMAIL', 'info@aion-ia.in');
define('CAREERS_EMAIL', 'careers@aion-ia.in');

/*
 * Website identity.
 */
define('SITE_NAME', 'AION-IA');
define('SITE_DOMAIN', 'https://www.aion-ia.in');

/*
 * Maximum upload size for resumes.
 *
 * 10 MB.
 */
define('MAX_RESUME_SIZE', 10 * 1024 * 1024);

/*
 * Allowed resume extensions.
 */
define('ALLOWED_RESUME_EXTENSIONS', [
    'pdf',
    'doc',
    'docx'
]);

/*
 * Allowed MIME types.
 */
define('ALLOWED_RESUME_MIME_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
]);

/*
 * Common security headers.
 */
function sendSecurityHeaders(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

/*
 * JSON response helper.
 */
function jsonResponse(
    bool $success,
    string $message,
    int $statusCode = 200,
    array $extra = []
): never {

    http_response_code($statusCode);

    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode(
        array_merge([
            'success' => $success,
            'message' => $message
        ], $extra),
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );

    exit;
}

/*
 * Only accept POST requests.
 */
function requirePost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(
            false,
            'Method not allowed.',
            405
        );
    }
}

/*
 * Get JSON body.
 */
function getJsonInput(): array
{
    $raw = file_get_contents('php://input');

    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $data = json_decode($raw, true);

    if (!is_array($data)) {
        return [];
    }

    return $data;
}

/*
 * Clean text input.
 */
function cleanText(?string $value, int $maxLength = 5000): string
{
    $value = trim((string) $value);

    /*
     * Remove null bytes.
     */
    $value = str_replace("\0", '', $value);

    /*
     * Limit length.
     */
    if (mb_strlen($value) > $maxLength) {
        $value = mb_substr($value, 0, $maxLength);
    }

    return $value;
}

/*
 * Clean email.
 */
function cleanEmail(?string $value): string
{
    $value = trim((string) $value);

    return filter_var($value, FILTER_SANITIZE_EMAIL);
}

/*
 * Validate required field.
 */
function requiredField(
    array $data,
    string $field,
    int $maxLength = 5000
): string {

    $value = cleanText(
        isset($data[$field]) ? (string) $data[$field] : '',
        $maxLength
    );

    if ($value === '') {
        jsonResponse(
            false,
            'Please complete all required fields.',
            422
        );
    }

    return $value;
}

/*
 * Validate email field.
 */
function requiredEmail(array $data, string $field = 'email'): string
{
    $email = cleanEmail(
        isset($data[$field]) ? (string) $data[$field] : ''
    );

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(
            false,
            'Please enter a valid email address.',
            422
        );
    }

    return $email;
}

/*
 * Prevent email header injection.
 */
function containsHeaderInjection(string $value): bool
{
    return preg_match('/[\r\n]/', $value) === 1;
}

/*
 * Validate all email-related fields.
 */
function validateEmailHeader(string $value): void
{
    if (containsHeaderInjection($value)) {
        jsonResponse(
            false,
            'Invalid input.',
            422
        );
    }
}

/*
 * Generate a safe timestamp.
 */
function currentTimestamp(): string
{
    return date('Y-m-d H:i:s');
}

/*
 * Build a plain-text email.
 */
function sendAionEmail(
    string $to,
    string $subject,
    string $body,
    ?string $replyTo = null,
    ?string $attachmentPath = null,
    ?string $attachmentName = null
): bool {

    validateEmailHeader($to);
    validateEmailHeader($subject);

    if ($replyTo !== null) {
        validateEmailHeader($replyTo);
    }

    /*
     * Use the AION-IA domain as the sender.
     *
     * This is important for deliverability.
     */
    $fromAddress = 'website@aion-ia.in';

    /*
     * If there is no attachment, use a simple email.
     */
    if ($attachmentPath === null) {

        $headers = [];

        $headers[] = 'From: AION-IA Website <' . $fromAddress . '>';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';

        if ($replyTo !== null) {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        return mail(
            $to,
            $subject,
            $body,
            implode("\r\n", $headers)
        );
    }

    /*
     * Attachment handling.
     */
    if (!file_exists($attachmentPath)) {
        return false;
    }

    $fileContent = file_get_contents($attachmentPath);

    if ($fileContent === false) {
        return false;
    }

    $encodedFile = chunk_split(
        base64_encode($fileContent)
    );

    $boundary = md5(
        'aion-ia-' . microtime(true)
    );

    $headers = [];

    $headers[] = 'From: AION-IA Website <' . $fromAddress . '>';
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

    if ($replyTo !== null) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }

    $message = '';

    $message .= '--' . $boundary . "\r\n";
    $message .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
    $message .= 'Content-Transfer-Encoding: 8bit' . "\r\n";
    $message .= "\r\n";
    $message .= $body . "\r\n";
    $message .= "\r\n";

    $message .= '--' . $boundary . "\r\n";
    $message .= 'Content-Type: application/octet-stream; name="' .
        $attachmentName . '"' . "\r\n";
    $message .= 'Content-Transfer-Encoding: base64' . "\r\n";
    $message .= 'Content-Disposition: attachment; filename="' .
        $attachmentName . '"' . "\r\n";
    $message .= "\r\n";
    $message .= $encodedFile . "\r\n";
    $message .= "\r\n";

    $message .= '--' . $boundary . '--';

    return mail(
        $to,
        $subject,
        $message,
        implode("\r\n", $headers)
    );
}

/*
 * Basic honeypot spam protection.
 *
 * Add a hidden field called "website" to your forms.
 * Legitimate users leave it empty.
 */
function checkHoneypot(array $data): void
{
    if (!empty($data['website'])) {
        /*
         * Pretend submission succeeded.
         * This prevents bots from learning the field exists.
         */
        jsonResponse(
            true,
            'Submission received.'
        );
    }
}

/*
 * Basic request origin validation.
 *
 * This is not a replacement for server-side security,
 * but it reduces casual cross-site abuse.
 */
function validateOrigin(): void
{
    if (!isset($_SERVER['HTTP_ORIGIN'])) {
        return;
    }

    $origin = rtrim(
        (string) $_SERVER['HTTP_ORIGIN'],
        '/'
    );

    $allowed = [
        'https://www.aion-ia.in',
        'https://aion-ia.in'
    ];

    if (!in_array($origin, $allowed, true)) {
        jsonResponse(
            false,
            'Invalid request origin.',
            403
        );
    }

    header(
        'Access-Control-Allow-Origin: ' . $origin
    );

    header(
        'Vary: Origin'
    );
}

/*
 * Apply common configuration.
 */
sendSecurityHeaders();
validateOrigin();