<?php
namespace FwBD\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Email
{

    public static function email($nome, $email, $assunto, $altBody, $message)
    {
        $to      = $email;
        $subject = $assunto;
        $message = $message;

        $headers = 'MIME-Version: 1.0\r\n';
        $headers .= 'Content-type: text/html; charset=UTF-8\r\n';

        // Additional headers
        /*$headers .= 'To: Bruno <devbrunodamiao@gmail.com>\r\n';
        $headers .= 'From: '.$nome.' <'.$email.'>\r\n';*/

        // if (mail($to, $subject, $message, implode("\r\n", $headers)))
        if (mail($to, $subject, $message))
            return true;
        else
            return false;

    }

    public static function smtpEmail($nome, $email, $assunto, $altBody, $message)
    {

        $mail = new PHPMailer(true); // Passing `true` enables exceptions
        try {

            //Server settings
            $mail->SMTPDebug  = 0;
            $mail->isSMTP();
            $mail->Host       = CONFIG_EMAIL['HOST'];
            $mail->SMTPAuth   = CONFIG_EMAIL['SMTP_AUTH'];
            $mail->Username   = CONFIG_EMAIL['USERNAME']; //'devbrunodamiao@gmail.com';
            $mail->Password   = CONFIG_EMAIL['PASSWORD']; //'brhenry13';
            $mail->SMTPSecure = CONFIG_EMAIL['SMTP_SECURE']; //'tls';
            $mail->Port       = CONFIG_EMAIL['PORT']; //587;

            # Recipients
            $mail->setFrom( CONFIG_EMAIL['MAIL_EMPRESA'], CONFIG_EMAIL['REPRESENTATE'] );
            $mail->addAddress($email, $nome); // Add a recipient

            # Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $assunto;
            $mail->Body    = $message;

            # $mail->msgHTML(file_get_contents('contents.html'), __DIR__);
            $mail->AltBody = $altBody;

            if ($mail->send())
                return true;
            else
                return false;

        } catch (Exception $e) {
            return false;
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }

    }

    

}