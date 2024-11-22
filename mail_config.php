<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendVerificationEmail($userEmail, $userName, $verificationToken) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  
        $mail->SMTPAuth = true;
        $mail->Username = 'juanitoaraneta02@gmail.com'; 
        $mail->Password = 'hymn ljnb sykb aoyq'; 
        $mail->SMTPSecure = 'tls';  
        $mail->Port = 587;

        $mail->setFrom('your-email@gmail.com', 'AW-K9 PET SHOP');
        $mail->addAddress($userEmail, $userName);

        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email';
        $verification_link = "http://localhost/working/verify.php?token=" . $verificationToken;
        
        $mail->Body = "
            <html>
            <body>
                <h2>Hello $userName,</h2>
                <p>Please click the following link to verify your email:</p>
                <p><a href='$verification_link'>Verify Email</a></p>
            </body>
            </html>
        ";
        $mail->AltBody = "Hello $userName,\nPlease click the following link to verify your email:\n$verification_link";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>