<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_email'])) {
    echo json_encode(['success'=>false, 'message'=>'No active session.']);
    exit;
}

$action = $_POST['action'] ?? '';
$action = trim($action);

$user_email = $_SESSION['user_email'];
$csvFile = __DIR__ . '/results.csv';

function code_exists($csvFile, $email) {
    if (!file_exists($csvFile)) return false;
    if (($f = fopen($csvFile,'r')) === false) return false;
    fgetcsv($f); // skip header
    while (($row = fgetcsv($f)) !== false) {
        if (isset($row[1]) && $row[1] === $email) {
            fclose($f);
            return true;
        }
    }
    fclose($f);
    return false;
}

// Ensure CSV header
$header = array_merge(['timestamp','user_email'], array_map(function($i){ return "Q{$i}"; }, range(1,20)), ['status']);
if (!file_exists($csvFile)) {
    $f0 = fopen($csvFile,'w');
    fputcsv($f0, $header);
    fclose($f0);
}

if ($action === 'end') {
    if (code_exists($csvFile, $user_email)) {
        session_unset();
        session_destroy();
        echo json_encode(['success'=>true,'message'=>'Already recorded.']);
        exit;
    }
    $row = array_merge([date('Y-m-d H:i:s'), $user_email], array_fill(0,20,''), ['ended']);
    $f = fopen($csvFile,'a');
    if ($f && flock($f, LOCK_EX)) {
        fputcsv($f,$row);
        fflush($f);
        flock($f, LOCK_UN);
    }
    fclose($f);
    session_unset();
    session_destroy();
    echo json_encode(['success'=>true,'message'=>'Session ended recorded.']);
    exit;
}

if ($action === 'submit') {
    if (code_exists($csvFile, $user_email)) {
        session_unset();
        session_destroy();
        echo json_encode(['success'=>false,'message'=>'This email has already been used.']);
        exit;
    }

    $answers = [];
    for ($i=1;$i<=20;$i++) {
        $key = 'q'.$i;
        if (!isset($_POST[$key])) {
            echo json_encode(['success'=>false,'message'=>"Answer for question {$i} missing."]);
            exit;
        }
        $val = strtoupper(substr(preg_replace('/[^A-Za-z]/','',$_POST[$key]),0,1));
        $answers[] = $val;
    }

    $row = array_merge([date('Y-m-d H:i:s'), $user_email], $answers, ['submitted']);
    $f = fopen($csvFile,'a');
    if ($f && flock($f, LOCK_EX)) {
        fputcsv($f,$row);
        fflush($f);
        flock($f, LOCK_UN);
    }
    fclose($f);

    session_unset();
    session_destroy();
    echo json_encode(['success'=>true,'message'=>'Results saved.']);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Invalid action.']);
exit;
?>