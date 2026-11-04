<?php
// Eğer require satırlarında hata alırsan yolları 'PHPMailer/src/Exception.php' şeklinde ayarlayabilirsin.
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendAuthCode($email, $code, $type = 'reset') {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.turkticaret.net'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bilgi@deucritic.com'; 
        $mail->Password   = 'Enis076546.'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
        $mail->Port       = 465; 
        
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('bilgi@deucritic.com', 'Deucritic');
        $mail->addAddress($email);

        $mail->isHTML(true);

        $subject = '';
        $title = '';
        $message = '';
        $expTime = '';

        if ($type == 'reset') {
            $subject = 'Şifre Sıfırlama Kodunuz | Deucritic';
            $title = 'Şifre Sıfırlama Talebi';
            $message = 'Hesabınızın şifresini sıfırlamak için bir talepte bulundunuz. İşlemi tamamlamak için aşağıdaki doğrulama kodunu kullanın.';
            $expTime = '15';
        } else {
            $subject = 'Giriş Doğrulama Kodunuz | Deucritic';
            $title = 'Yeni Giriş Denemesi';
            $message = 'Hesabınıza yeni bir giriş yapılıyor. Güvenliğiniz için lütfen aşağıdaki doğrulama kodunu girin.';
            $expTime = '5';
        }

        $mailTemplate = "
        <div style='font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; background-color: #f8fafc; padding: 40px 10px; margin: 0;'>
            <div style='max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);'>
                
                <div style='background-color: #4f46e5; padding: 35px 20px; text-align: center;'>
                    <h1 style='color: #ffffff; margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -0.5px;'>Deucritic</h1>
                </div>
                
                <div style='padding: 40px 30px;'>
                    <h2 style='color: #1e293b; margin-top: 0; font-size: 20px; text-align: center;'>{$title}</h2>
                    <p style='color: #64748b; font-size: 15px; line-height: 1.6; text-align: center; margin-bottom: 30px;'>
                        Merhaba,<br><br>
                        {$message}
                    </p>
                    
                    <div style='background-color: #f1f5f9; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 25px;'>
                        <span style='display: block; font-size: 38px; font-weight: 800; color: #4f46e5; letter-spacing: 8px;'>{$code}</span>
                    </div>
                    
                    <p style='color: #94a3b8; font-size: 13px; text-align: center; margin: 0;'>
                        Bu kod <strong>{$expTime} dakika</strong> boyunca geçerlidir.
                    </p>
                </div>
                
                <!-- Alt Bilgi (Footer) Kısmı -->
                <div style='background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 25px 20px; text-align: center;'>
                    <p style='color: #94a3b8; font-size: 12px; line-height: 1.5; margin: 0;'>
                        Bu işlemi siz başlatmadıysanız, lütfen bu e-postayı dikkate almayın ve hesabınızın güvenliği için şifrenizi değiştirin.<br><br>
                        &copy; " . date('Y') . " Deucritic. Tüm hakları saklıdır.
                    </p>
                </div>
                
            </div>
        </div>
        ";

        $mail->Subject = $subject;
        $mail->Body    = $mailTemplate;
        
        $mail->AltBody = "Merhaba, \n\n{$message} \n\nDoğrulama Kodunuz: {$code} \n\nBu kod {$expTime} dakika geçerlidir.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Hata durumunda test etmek istersen alttaki log satırını açabilirsin
        // error_log("E-posta Gönderim Hatası: " . $mail->ErrorInfo);
        return false;
    }
}
?>