<?php
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendResetLinkEmail($email, $resetLink) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.turkticaret.net'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bilgi@deucritic.com'; 
        $mail->Password   = 'Enis076546.'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Port 465 için SSL
        $mail->Port       = 465; 
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('bilgi@deucritic.com', 'Deucritic.com');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Şifre Sıfırlama Bağlantınız | Deucritic';

        $mailTemplate = "
        <div style='font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; background-color: #f8fafc; padding: 40px 10px; margin: 0;'>
            <div style='max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);'>
                <div style='background-color: #4f46e5; padding: 35px 20px; text-align: center;'>
                    <h1 style='color: #ffffff; margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -0.5px;'>Deucritic</h1>
                </div>
                <div style='padding: 40px 30px;'>
                    <h2 style='color: #1e293b; margin-top: 0; font-size: 20px; text-align: center;'>Şifre Sıfırlama Talebi</h2>
                    <p style='color: #64748b; font-size: 15px; line-height: 1.6; text-align: center; margin-bottom: 30px;'>
                        Merhaba, DeuCritic hesabının şifresini sıfırlamak için bir talepte bulundun. Aşağıdaki butona tıklayarak yeni şifreni belirleyebilirsin.
                    </p>
                    <div style='text-align: center; margin-bottom: 25px;'>
                        <a href='{$resetLink}' style='background-color: #4f46e5; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 10px; font-weight: bold; font-size: 16px; display: inline-block;'>Şifremi Sıfırla 🔐</a>
                    </div>
                    <p style='color: #94a3b8; font-size: 13px; text-align: center; margin: 0;'>
                        Bu bağlantı <strong>1 saat</strong> boyunca geçerlidir.
                    </p>
                </div>
                <div style='background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 25px 20px; text-align: center;'>
                    <p style='color: #94a3b8; font-size: 12px; line-height: 1.5; margin: 0;'>
                        &copy; " . date('Y') . " Deucritic. Tüm hakları saklıdır.
                    </p>
                </div>
            </div>
        </div>";

        $mail->Body = $mailTemplate;
        $mail->AltBody = "Şifrenizi sıfırlamak için şu linke tıklayın: {$resetLink}";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Hata ayıklama modu (İşlem bitince bu kısmı silebilirsin)
        echo "<div style='background: black; color: #00ff00; padding: 20px; font-family: monospace;'>";
        echo "<h3>MAİL GÖNDERİM HATASI:</h3>";
        echo $mail->ErrorInfo;
        echo "</div>";
        exit;
    }
}