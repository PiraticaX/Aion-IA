<?php
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    // Optional: simple server-side email domain check
    $banned = ['gmail.com','yahoo.com','outlook.com','hotmail.com','icloud.com'];
    $domain = strtolower(substr(strrchr($email, "@"), 1));
    foreach($banned as $b){
        if(strpos($domain,$b)!==false){
            http_response_code(400);
            echo "Please use a professional email.";
            exit;
        }
    }

    $to = "info@aion-ia.in";
    $subject = "Whitepaper Access Request";
    $message = "Name: $name\nPhone: $phone\nEmail: $email";
    $headers = "From: noreply@aion-ia.in";

    if(mail($to,$subject,$message,$headers)){
        echo "success";
    } else {
        http_response_code(500);
        echo "error";
    }
}
?>