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
            $mail->setFrom( CONFIG_EMAIL['MAIL'], CONFIG_EMAIL['REPRESENTANTE'] );
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












    // public static function smtpEmail($nome, $email, $assunto, $altBody, $message)
    public static function smtpEmailyyyyyyy()
    {

        $mail = new PHPMailer(true); // Passing `true` enables exceptions
        // To load the version
        $mail->setLanguage('pt_br', '/optional/path/to/language/phpmailer.lang-pt_br.php');
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
            // $mail->setFrom( CONFIG_EMAIL['MAIL_EMPRESA'], CONFIG_EMAIL['REPRESENTATE'] );
            $mail->setFrom( "contato@brunodamiao.com.br", 'REPRESENTATE' );

            $nome  = 'DevNome';
            $email = 'devbrunodamiao@gmail.com';
            $assunto = 'assunto';
            $message = 'msgs <strong> oi </strong>';

            $mail->addAddress($email, $nome); // Add a recipient

            # Content
            $mail->isHTML(true); // Set email format to HTML

            $mail->Body = '
            <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
            <html>
            <head>
              <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
              <title>PHPMailer Test</title>
            </head>
            <body>
            <div style="width: 640px; font-family: Arial, Helvetica, sans-serif; font-size: 11px;">
              <h1>This is a test of PHPMailer.</h1>
              <div align="center">
                <a href="https://github.com/PHPMailer/PHPMailer/">
                <img src="https://www.abmn.org.br/wp-content/uploads/2014/12/Oi-logo-2.png" height="90" width="340" alt="PHPMailer rocks"></a>
              </div>
              <p>This example uses <strong>HTML</strong> with the UTF-8 unicode charset.</p>
              <p>Brazil text: Bruno Damião P. da Silva</p>
              <p>Chinese text: 郵件內容為空</p>
              <p>Russian text: Пустое тело сообщения</p>
              <p>Armenian text: Հաղորդագրությունը դատարկ է</p>
              <p>Czech text: Prázdné tělo zprávy</p>
              <p>Emoji: <span style="font-size: 48px">😂 🦄 💥 📤 📧</span></p>
              <p>Image data URL (base64)<img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" alt="#"></p>
              <p>Image data URL (URL-encoded)<img src="data:image/gif,GIF89a%01%00%01%00%00%00%00%21%F9%04%01%0A%00%01%00%2C%00%00%00%00%01%00%01%00%00%02%02L%01%00%3B" alt="#"></p>
              <p>Image data URL (URL-encoded)<img src="data:image/png,'.PATH_FAVICON.'" alt="#"></p>
            </div>
            </body>
            </html>
            ';
            $mail->AltBody="This is text only alternative body.";


            /*$mail->Subject = $assunto;
            $mail->Body    = $message;*/

            # $mail->msgHTML(file_get_contents('contents.html'), __DIR__);
            // $mail->msgHTML = $message;
            // $mail->AltBody = $altBody;

            if ($mail->send())
                return true;
            else
                return false;

        } catch (Exception $e) {
            // return false;
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
            pp('Mailer Error:'.$mail->ErrorInfo,1);
        }

    }

    public static function smtpEmailxxx($nome, $email, $assunto, $altBody, $message)
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