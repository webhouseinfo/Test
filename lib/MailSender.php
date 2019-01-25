<?php


require './mail/class.phpmailer.php';

class MailSender
{
    private static $mail;

    public static function send($to, $subject, $content = '', $attachment = null)
    {
        // self::$mail = new PHPMailer();

        // self::$mail->IsSMTP();
        // self::$mail->SMTPDebug = 1;
        // self::$mail->SMTPAuth = true;
        // self::$mail->SMTPSecure = 'ssl';
        // self::$mail->CharSet = 'UTF-8';
        // self::$mail->Port = 465;
        // self::$mail->Username = 'info@cft.am';
        // self::$mail->Password = 'cft2018.';
        // self::$mail->Host = 'smtp.yandex.com';
        // self::$mail->Mailer = 'smtp';
        // self::$mail->Priority = '5';
        // self::$mail->SetFrom('info@cft.am', 'CFT');

        // self::$mail->AddAddress($to);
        // self::$mail->Subject = $subject;
        // self::$mail->Body = $content;
        // //self::$mail->ConfirmReadingTo = 'mail.yandex.com';
        // self::$mail->IsHTML(true);
        // self::$mail->AddReplyTo('info@cft.am');

        // return self::$mail->Send();

        self::$mail = new PHPMailer(false); //New instance, with exceptions enabled
        self::$mail->IsSMTP();                           // tell the class to use SMTP
        self::$mail->SMTPDebug = 0;
        self::$mail->SMTPAuth = true;                  // enable SMTP authentication
        self::$mail->Port = 465;                    // set the SMTP server port
        self::$mail->Host = 'smtp.gmail.com'; // SMTP server
        self::$mail->Username = 'vilmar.market@gmail.com';     // SMTP server username
        self::$mail->Password = 'vil-mar2018.';            // SMTP server password
        self::$mail->IsSendmail();  // tell the class to use Sendmail
        self::$mail->From = 'vilmar.market@gmail.com';
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
