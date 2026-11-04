function warnUser($pdo, $user_id, $reason) {
    // Uyarı sayısını artır
    $stmt = $pdo->prepare("UPDATE users SET warnings = warnings + 1 WHERE id = ?");
    $stmt->execute([$user_id]);

    // Mevcut uyarı sayısını al
    $stmt = $pdo->prepare("SELECT warnings FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $warnings = $stmt->fetchColumn();

    // Kullanıcıya bildirim gönder
    $msg = "Uyarı: $reason. Toplam uyarı sayınız: $warnings.";
    sendNotification($pdo, $user_id, $msg); // Bu fonksiyonu senin mevcut sistemine göre uyarlayabilirim

    // 3 uyarıda otomatik ban
    if ($warnings >= 3) {
        $stmt = $pdo->prepare("UPDATE users SET is_banned = 1 WHERE id = ?");
        $stmt->execute([$user_id]);
        sendNotification($pdo, $user_id, "Hesabınız 3 kural ihlali nedeniyle geçici olarak yasaklanmıştır.");
    }
}