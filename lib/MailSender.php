<?php


require './mail/class.phpmailer.php';

class MailSender
{
    private static $mail;

    public static function send($to, $subject, $content = '', $attachment = null)
    {
        self::$mail = new PHPMailer(false); //New instance, with exceptions enabled
        self::$mail->IsSMTP();                           // tell the class to use SMTP
        self::$mail->SMTPDebug = 0;
        self::$mail->SMTPAuth = true;                  // enable SMTP authentication
        self::$mail->Port = 465;                    // set the SMTP server port
        self::$mail->Host = 'smtp.gmail.com'; // SMTP server
        self::$mail->Username = '';     // SMTP server username
        self::$mail->Password = '';            // SMTP server password
        self::$mail->IsSendmail();  // tell the class to use Sendmail
        self::$mail->From = '';
        self::$mail->FromName = 'VILMAR';
        self::$mail->AddAddress($to);
        self::$mail->Subject = $subject;
        self::$mail->CharSet = 'UTF-8';
        self::$mail->AltBody = $content; // optional, comment out and test
        self::$mail->WordWrap = 80; // set word wrap
        self::$mail->MsgHTML($content);
        self::$mail->IsHTML(true); // send as HTML

        if ($attachment) {
            self::$mail->addAttachment($attachment['path'], $attachment['name']);
        }

        return self::$mail->Send();
    }
}
