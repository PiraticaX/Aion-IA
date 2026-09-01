<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

requirePost();

checkHoneypot($_POST);


/*
 * ============================================================
 * Required information
 * ============================================================
 */

$name = requiredField(
    $_POST,
    'name',
    150
);


$email = requiredEmail(
    $_POST,
    'email'
);


$phone = cleanText(
    isset($_POST['phone'])
        ? (string) $_POST['phone']
        : '',
    50
);


$profileUrl = requiredField(
    $_POST,
    'profile_url',
    500
);


$project = requiredField(
    $_POST,
    'project',
    5000
);


$whyAion = requiredField(
    $_POST,
    'why_aion',
    5000
);



/*
 * ============================================================
 * Validate profile URL
 * ============================================================
 */

if (
    !filter_var(
        $profileUrl,
        FILTER_VALIDATE_URL
    )
) {

    jsonResponse(
        false,
        'Please enter a valid GitHub or LinkedIn URL.',
        422
    );

}



/*
 * ============================================================
 * Resume validation
 * ============================================================
 */

if (
    !isset($_FILES['resume']) ||
    !is_array($_FILES['resume'])
) {

    jsonResponse(
        false,
        'Please upload your resume.',
        422
    );

}


$resume =
    $_FILES['resume'];



/*
 * Upload error.
 */

if (
    $resume['error'] !==
    UPLOAD_ERR_OK
) {

    jsonResponse(
        false,
        'There was a problem uploading your resume.',
        422
    );

}



/*
 * Maximum file size.
 */

if (
    (int) $resume['size'] >
    MAX_RESUME_SIZE
) {

    jsonResponse(
        false,
        'Resume must be 10 MB or smaller.',
        422
    );

}



/*
 * Original filename.
 */

$originalName =
    basename(
        (string) $resume['name']
    );


$extension =
    strtolower(
        pathinfo(
            $originalName,
            PATHINFO_EXTENSION
        )
    );



/*
 * Extension validation.
 */

if (
    !in_array(
        $extension,
        ALLOWED_RESUME_EXTENSIONS,
        true
    )
) {

    jsonResponse(
        false,
        'Only PDF, DOC, and DOCX resumes are accepted.',
        422
    );

}



/*
 * MIME validation.
 */

$finfo =
    new finfo(
        FILEINFO_MIME_TYPE
    );


$mimeType =
    $finfo->file(
        $resume['tmp_name']
    );


if (
    !in_array(
        $mimeType,
        ALLOWED_RESUME_MIME_TYPES,
        true
    )
) {

    jsonResponse(
        false,
        'The uploaded resume file type is not supported.',
        422
    );

}



/*
 * Confirm genuine upload.
 */

if (
    !is_uploaded_file(
        $resume['tmp_name']
    )
) {

    jsonResponse(
        false,
        'Invalid file upload.',
        422
    );

}



/*
 * ============================================================
 * Temporary resume file
 * ============================================================
 */

$safeName =
    'aion-resume-' .
    bin2hex(
        random_bytes(16)
    ) .
    '.' .
    $extension;


$tempDirectory =
    sys_get_temp_dir();


$tempPath =
    $tempDirectory .
    DIRECTORY_SEPARATOR .
    $safeName;



/*
 * Move uploaded file.
 */

if (
    !move_uploaded_file(
        $resume['tmp_name'],
        $tempPath
    )
) {

    jsonResponse(
        false,
        'Could not process the uploaded resume.',
        500
    );

}



/*
 * ============================================================
 * Email body
 * ============================================================
 */

$emailSubject =
    '[AION-IA Careers] Application from ' .
    $name;


$body = '';

$body .=
    "AION-IA CAREER APPLICATION\n";

$body .=
    "==========================\n\n";


$body .=
    "Name: " .
    $name .
    "\n";


$body .=
    "Email: " .
    $email .
    "\n";


$body .=
    "Phone: " .
    ($phone ?: 'Not provided') .
    "\n";


$body .=
    "GitHub / LinkedIn: " .
    $profileUrl .
    "\n";


$body .=
    "\nInteresting Project\n";

$body .=
    "-------------------\n";

$body .=
    $project .
    "\n";


$body .=
    "\nWhy AION-IA\n";

$body .=
    "-----------\n";

$body .=
    $whyAion .
    "\n";


$body .=
    "\nSubmission details\n";

$body .=
    "------------------\n";

$body .=
    "Time: " .
    currentTimestamp() .
    "\n";


$body .=
    "IP: " .
    ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') .
    "\n";



/*
 * ============================================================
 * Send email
 * ============================================================
 */

$sent =
    sendAionEmail(
        CAREERS_EMAIL,
        $emailSubject,
        $body,
        $email,
        $tempPath,
        $originalName
    );



/*
 * Always remove temporary resume.
 */

if (
    file_exists($tempPath)
) {

    unlink($tempPath);

}



/*
 * ============================================================
 * Result
 * ============================================================
 */

if (!$sent) {

    jsonResponse(
        false,
        'We could not submit your application at this time. Please try again.',
        500
    );

}


jsonResponse(
    true,
    'Application received. AION-IA will follow up by email.'
);