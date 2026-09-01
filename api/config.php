<?php

declare(strict_types=1);

/*
 * ============================================================
 * AION-IA
 * Backend configuration
 * ============================================================
 */

define('CONTACT_EMAIL', 'info@aion-ia.in');
define('CAREERS_EMAIL', 'careers@aion-ia.in');

define('SITE_NAME', 'AION-IA');
define('SITE_DOMAIN', 'https://www.aion-ia.in');

define('MAX_RESUME_SIZE', 10 * 1024 * 1024);

define('ALLOWED_RESUME_EXTENSIONS', [
    'pdf',
    'doc',
    'docx'
]);

define('ALLOWED_RESUME_MIME_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
]);


/*
 * ============================================================
 * Security headers
 * ============================================================
 */

function sendSecurityHeaders(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}


/*
 * ============================================================
 * CORS
 * ============================================================
 */

function validateOrigin(): void
{
    $allowedOrigins = [
        'https://www.aion-ia.in',
        'https://aion-ia.in'
    ];

    /*
     * If the browser sends an Origin header,
     * validate it.
     */

    if (isset($_SERVER['HTTP_ORIGIN'])) {

        $origin = rtrim(
            (string) $_SERVER['HTTP_ORIGIN'],
            '/'
        );

        if (!in_array(
            $origin,
            $allowedOrigins,
            true
        )) {

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
            'Access-Control-Allow-Methods: POST, OPTIONS'
        );

        header(
            'Access-Control-Allow-Headers: Content-Type, Accept'
        );

        header(
            'Access-Control-Max-Age: 86400'
        );

        header(
            'Vary: Origin'
        );
    }
}


/*
 * ============================================================
 * Handle browser preflight
 * ============================================================
 */

function handlePreflight(): void
{
    if (
        isset($_SERVER['REQUEST_METHOD']) &&
        $_SERVER['REQUEST_METHOD'] === 'OPTIONS'
    ) {

        http_response_code(204);

        exit;
    }
}


/*
 * ============================================================
 * JSON response
 * ============================================================
 */

function jsonResponse(
    bool $success,
    string $message,
    int $statusCode = 200,
    array $extra = []
): never {

    http_response_code($statusCode);

    header(
        'Content-Type: application/json; charset=UTF-8'
    );

    echo json_encode(
        array_merge(
            [
                'success' => $success,
                'message' => $message
            ],
            $extra
        ),
        JSON_UNESCAPED_SLASHES |
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*
 * ============================================================
 * Request method
 * ============================================================
 */

function requirePost(): void
{
    if (
        !isset($_SERVER['REQUEST_METHOD']) ||
        $_SERVER['REQUEST_METHOD'] !== 'POST'
    ) {

        jsonResponse(
            false,
            'Method not allowed.',
            405
        );

    }
}


/*
 * ============================================================
 * JSON input
 * ============================================================
 */

function getJsonInput(): array
{
    $raw =
        file_get_contents('php://input');

    if (
        $raw === false ||
        trim($raw) === ''
    ) {

        return [];
    }

    $data =
        json_decode(
            $raw,
            true
        );

    if (!is_array($data)) {
        return [];
    }

    return $data;
}


/*
 * ============================================================
 * Input cleaning
 * ============================================================
 */

function cleanText(
    ?string $value,
    int $maxLength = 5000
): string {

    $value =
        trim((string) $value);

    /*
     * Remove null bytes.
     */

    $value =
        str_replace(
            "\0",
            '',
            $value
        );

    /*
     * Limit length.
     */

    if (
        mb_strlen($value) >
        $maxLength
    ) {

        $value =
            mb_substr(
                $value,
                0,
                $maxLength
            );

    }

    return $value;
}


/*
 * ============================================================
 * Email cleaning
 * ============================================================
 */

function cleanEmail(
    ?string $value
): string {

    $value =
        trim((string) $value);

    return filter_var(
        $value,
        FILTER_SANITIZE_EMAIL
    );
}


/*
 * ============================================================
 * Required field
 * ============================================================
 */

function requiredField(
    array $data,
    string $field,
    int $maxLength = 5000
): string {

    $value =
        cleanText(
            isset($data[$field])
                ? (string) $data[$field]
                : '',
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
 * ============================================================
 * Required email
 * ============================================================
 */

function requiredEmail(
    array $data,
    string $field = 'email'
): string {

    $email =
        cleanEmail(
            isset($data[$field])
                ? (string) $data[$field]
                : ''
        );

    if (
        $email === '' ||
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        jsonResponse(
            false,
            'Please enter a valid email address.',
            422
        );

    }

    return $email;
}


/*
 * ============================================================
 * Email header injection protection
 * ============================================================
 */

function containsHeaderInjection(
    string $value
): bool {

    return preg_match(
        '/[\r\n]/',
        $value
    ) === 1;
}


function validateEmailHeader(
    string $value
): void {

    if (
        containsHeaderInjection(
            $value
        )
    ) {

        jsonResponse(
            false,
            'Invalid input.',
            422
        );

    }
}


/*
 * ============================================================
 * Timestamp
 * ============================================================
 */

function currentTimestamp(): string
{
    return date(
        'Y-m-d H:i:s'
    );
}


/*
 * ============================================================
 * Send email
 * ============================================================
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
     * Sender.
     */

    $fromAddress =
        'website@aion-ia.in';


    /*
     * Simple email.
     */

    if (
        $attachmentPath === null
    ) {

        $headers = [];

        $headers[] =
            'From: AION-IA Website <' .
            $fromAddress .
            '>';

        $headers[] =
            'MIME-Version: 1.0';

        $headers[] =
            'Content-Type: text/plain; charset=UTF-8';

        if ($replyTo !== null) {

            $headers[] =
                'Reply-To: ' .
                $replyTo;

        }

        return mail(
            $to,
            $subject,
            $body,
            implode(
                "\r\n",
                $headers
            )
        );
    }


    /*
     * Attachment.
     */

    if (
        !file_exists(
            $attachmentPath
        )
    ) {

        return false;
    }


    $fileContent =
        file_get_contents(
            $attachmentPath
        );


    if (
        $fileContent === false
    ) {

        return false;
    }


    $encodedFile =
        chunk_split(
            base64_encode(
                $fileContent
            )
        );


    $boundary =
        md5(
            'aion-ia-' .
            microtime(true)
        );


    $headers = [];

    $headers[] =
        'From: AION-IA Website <' .
        $fromAddress .
        '>';

    $headers[] =
        'MIME-Version: 1.0';

    $headers[] =
        'Content-Type: multipart/mixed; boundary="' .
        $boundary .
        '"';


    if ($replyTo !== null) {

        $headers[] =
            'Reply-To: ' .
            $replyTo;

    }


    $message = '';

    $message .=
        '--' .
        $boundary .
        "\r\n";

    $message .=
        'Content-Type: text/plain; charset=UTF-8' .
        "\r\n";

    $message .=
        'Content-Transfer-Encoding: 8bit' .
        "\r\n\r\n";

    $message .=
        $body .
        "\r\n\r\n";


    $message .=
        '--' .
        $boundary .
        "\r\n";

    $message .=
        'Content-Type: application/octet-stream; name="' .
        $attachmentName .
        '"' .
        "\r\n";

    $message .=
        'Content-Transfer-Encoding: base64' .
        "\r\n";

    $message .=
        'Content-Disposition: attachment; filename="' .
        $attachmentName .
        '"' .
        "\r\n\r\n";

    $message .=
        $encodedFile .
        "\r\n";


    $message .=
        '--' .
        $boundary .
        '--';


    return mail(
        $to,
        $subject,
        $message,
        implode(
            "\r\n",
            $headers
        )
    );
}


/*
 * ============================================================
 * Honeypot
 * ============================================================
 */

function checkHoneypot(
    array $data
): void {

    if (
        !empty(
            $data['website']
        )
    ) {

        jsonResponse(
            true,
            'Submission received.'
        );

    }
}


/*
 * ============================================================
 * Initialise backend
 * ============================================================
 */

sendSecurityHeaders();

validateOrigin();

handlePreflight();