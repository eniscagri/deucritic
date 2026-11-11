<?php
require_once __DIR__ . '/admin_init.php';
require_admin();
require_once 'header.php';

if (admin_table_exists($pdo, 'request')) {
    $requests_stmt = $pdo->prepare("
        SELECT r.*, u.username, u.email
        FROM request r
        LEFT JOIN users u ON r.user_id = u.id
        ORDER BY r.date DESC
    ");
    $requests_stmt->execute();
    $requests = $requests_stmt->fetchAll();
} else {
    $requests = [];
}

if (admin_table_exists($pdo, 'school_requests')) {
    $school_stmt = $pdo->prepare("
        SELECT s.*, u.username, u.email
        FROM school_requests s
        LEFT JOIN users u ON s.user_id = u.id
        ORDER BY s.date DESC
    ");
    $school_stmt->execute();
    $school_requests = $school_stmt->fetchAll();
} else {
    $school_requests = [];
}
?>

<div class="bg-white p-6 rounded-xl shadow-md mt-6">
    <h2 class="text-2xl font-semibold mb-4 text-gray-800">📌 Kullanıcı Talepleri</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 rounded-lg">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-4 py-2 border">ID</th>
                    <th class="px-4 py-2 border">Kullanıcı</th>
                    <th class="px-4 py-2 border">Email</th>
                    <th class="px-4 py-2 border">Talep</th>
                    <th class="px-4 py-2 border">Tarih</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 border"><?= $req['id'] ?></td>
                        <td class="px-4 py-2 border"><?= htmlspecialchars($req['username']) ?></td>
                        <td class="px-4 py-2 border"><?= htmlspecialchars($req['email']) ?></td>
                        <td class="px-4 py-2 border text-blue-600 font-semibold"><?= htmlspecialchars($req['feature']) ?></td>
                        <td class="px-4 py-2 border"><?= $req['date'] ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (count($requests) === 0): ?>
                    <tr><td colspan="5" class="text-center py-4 text-gray-500">Talep bulunmuyor.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>



<div class="bg-white p-6 rounded-xl shadow-md mt-8">
    <h2 class="text-2xl font-semibold mb-4 text-gray-800">🏫 Okul Değişikliği / Okul Ekleme Talepleri</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 rounded-lg">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-4 py-2 border">ID</th>
                    <th class="px-4 py-2 border">Kullanıcı</th>
                    <th class="px-4 py-2 border">Email</th>
                    <th class="px-4 py-2 border">Okul Adı</th>
                    <th class="px-4 py-2 border">Sebep</th>
                    <th class="px-4 py-2 border">Tarih</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($school_requests as $req): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 border"><?= $req['id'] ?></td>
                        <td class="px-4 py-2 border"><?= htmlspecialchars($req['username']) ?></td>
                        <td class="px-4 py-2 border"><?= htmlspecialchars($req['email']) ?></td>
                        <td class="px-4 py-2 border font-semibold text-indigo-600"><?= htmlspecialchars($req['school_name']) ?></td>
                        <td class="px-4 py-2 border"><?= nl2br(htmlspecialchars($req['reason'])) ?></td>
                        <td class="px-4 py-2 border"><?= $req['date'] ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (count($school_requests) === 0): ?>
                    <tr><td colspan="6" class="text-center py-4 text-gray-500">Okul talebi bulunmuyor.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
