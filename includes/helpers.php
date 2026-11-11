<?php


function fetch_topic(PDO $pdo, int $topic_id): ?array {
    $stmt = $pdo->prepare("
        SELECT t.id, t.title, t.description, t.created_at, t.anonymous, t.created_by,
               u.username AS owner, u.profile_photo AS owner_pp
        FROM topics t
        JOIN users u ON t.created_by = u.id
        WHERE t.id = ?
    ");
    $stmt->execute([$topic_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function fetch_entries(PDO $pdo, int $topic_id): array {
    $stmt = $pdo->prepare("
        SELECT e.id, e.content, e.created_at, e.image, e.anonymous AS entry_anon, u.username
        FROM entries e
        JOIN users u ON e.user_id = u.id
        WHERE e.topic_id = ?
        ORDER BY e.created_at DESC
    ");
    $stmt->execute([$topic_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetch_entry_like_count(PDO $pdo, int $entry_id): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM entry_likes WHERE entry_id=?");
    $stmt->execute([$entry_id]);
    return (int)$stmt->fetchColumn();
}

function has_user_liked(PDO $pdo, int $user_id, int $entry_id): bool {
    $stmt = $pdo->prepare("SELECT 1 FROM entry_likes WHERE entry_id=? AND user_id=?");
    $stmt->execute([$entry_id, $user_id]);
    return (bool)$stmt->fetch();
}

function linkify_html(string $text): string {
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace_callback('/\[(.*?)\]\((https?:\/\/[^\s<]+)\)/i', function($m){
        $title = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
        $url = 'linksafe.php?url=' . urlencode($m[2]);
        return "<a href='$url' target='_blank' rel='nofollow noopener' class='text-blue-600 hover:underline'>$title</a>";
    }, $text);
    $text = preg_replace_callback('/\b((https?:\/\/|www\.)[^\s<]+)/i', function($m){
        $url = strpos($m[1], 'www.')===0?'http://'.$m[1]:$m[1];
        $url_safe = 'linksafe.php?url=' . urlencode($url);
        return "<a href='$url_safe' target='_blank' rel='nofollow noopener' class='text-blue-600 hover:underline'>🔗 link</a>";
    }, $text);
    return nl2br($text);
}

function time_ago(string $datetime): string {
    $now = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->diff($then);

    $daysOfWeek = ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'];

    if ($diff->days === 0) return "Bugün ".$then->format('H:i');
    if ($diff->days === 1 && $then<$now) return "Dün ".$then->format('H:i');
    if ($diff->days === 2 && $then<$now) return "Önceki gün ".$then->format('H:i');
    if ($diff->days<7 && $then<$now) return "Geçen hafta ".$daysOfWeek[(int)$then->format('w')]." ".$then->format('H:i');
    if ($diff->y>=1) return $then->format('d.m.Y H:i');
    return $then->format('d.m H:i');
}
