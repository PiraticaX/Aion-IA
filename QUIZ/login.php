<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$user_email = trim($_POST['user_email'] ?? '');

// Validate email
if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    echo "<p>Invalid email address. <a href='index.php'>Go back</a></p>";
    exit;
}

// Path to CSV
$csvFile = __DIR__ . '/results.csv';

// Check if email already exists
function email_exists_in_csv($csvFile, $email) {
    if (!file_exists($csvFile)) return false;
    if (($f = fopen($csvFile, 'r')) === false) return false;
    fgetcsv($f); // skip header
    while (($row = fgetcsv($f)) !== false) {
        if (isset($row[1]) && trim(strtolower($row[1])) === strtolower($email)) {
            fclose($f);
            return true;
        }
    }
    fclose($f);
    return false;
}

if (email_exists_in_csv($csvFile, $user_email)) {
    ?>
    <!doctype html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>Already Completed</title>
      <link rel="stylesheet" href="style.css">
    </head>
    <body>
      <main class="centered">
        <div class="card login-card">
          <h2>Quiz Not Available</h2>
          <p class="note">This email has already been used or the session ended. If you think this is an error, contact the admin.</p>
          <p><a class="btn ghost" href="index.php">Back</a></p>
        </div>
      </main>
    </body>
    </html>
    <?php
    exit;
}

// Save email in session
$_SESSION['user_email'] = $user_email;
$_SESSION['started_at'] = time();

header('Location: quiz.php');
exit;
?>