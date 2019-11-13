<?php
require("../PHPMailer-master/src/PHPMailer.php");
require("../PHPMailer-master/src/SMTP.php");

$mail = new PHPMailer\PHPMailer\PHPMailer();
$mail->IsSMTP(); // enable SMTP

$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
$mail->SMTPAuth = true; // authentication enabled
$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
$mail->Host = "smtp.gmail.com";
$mail->Port = 465; // or 587
$mail->IsHTML(true);
$mail->Username="thtan56@gmail.com";
$mail->Password='cancer1956';
$mail->SetFrom('t.h.tan@dunelm.org.uk');
$mail->subject = "testing 123";
$mail->Body="Hello world! tan ";
$mail->AddAddress('thtan56@gmail.com');   // recipent
$mail->Send();
?>
