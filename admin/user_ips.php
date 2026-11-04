<?php
require_once "header.php"; 
require_once "config.php";

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, email, last_ip, last_login, is_banned FROM users ORDER BY last_login DESC");
$stmt->execute();
$users = $stmt->fetchAll();

function get_ip_info($ip) {
    if (!$ip || $ip == "0" || $ip == "127.0.0.1" || $ip == "::1") {
        return [
            'country' => 'Localhost',
            'city' => '-',
            'isp' => '-'
        ];
    }

    // IP-API (ücretsiz)
    $json = @file_get_contents("http://ip-api.com/json/$ip?fields=status,country,city,isp");

    if (!$json) {
        return [
            'country' => 'N/A',
            'city' => 'N/A',
            'isp' => 'N/A'
        ];
    }

    $data = json_decode($json, true);

    if ($data['status'] !== "success") {
        return [
            'country' => 'Unknown',
            'city' => 'Unknown',
            'isp' => 'Unknown'
        ];
    }

    return [
        'country' => $data['country'],
        'city' => $data['city'],
        'isp' => $data['isp']
    ];
}
?>

<div class="bg-white p-6 rounded-xl shadow-md mt-6">
    <h2 class="text-3xl font-bold mb-5 text-gray-800 text-center">🌍 Kullanıcı IP & Konum Bilgileri</h2>

    <div class="overflow-x-auto mt-4">
        <table class="min-w-full border border-gray-200 rounded-lg">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-4 py-2 border">ID</th>
                    <th class="px-4 py-2 border">Kullanıcı</th>
                    <th class="px-4 py-2 border">Email</th>
                    <th class="px-4 py-2 border">Son IP</th>
                    <th class="px-4 py-2 border">Ülke</th>
                    <th class="px-4 py-2 border">Şehir</th>
                    <th class="px-4 py-2 border">Servis Sağlayıcı</th>
                    <th class="px-4 py-2 border">Son Giriş</th>
                    <th class="px-4 py-2 border">Durum</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($users as $u): 
                $ip_info = get_ip_info($u['last_ip']);
            ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 border"><?= $u['id'] ?></td>

                    <td class="px-4 py-2 border font-semibold text-blue-600">
                        <?= htmlspecialchars($u['username']) ?>
                    </td>

                    <td class="px-4 py-2 border"><?= htmlspecialchars($u['email']) ?></td>

                    <td class="px-4 py-2 border text-gray-700 font-mono"><?= $u['last_ip'] ?></td>

                    <td class="px-4 py-2 border"><?= $ip_info['country'] ?></td>
                    <td class="px-4 py-2 border"><?= $ip_info['city'] ?></td>
                    <td class="px-4 py-2 border"><?= $ip_info['isp'] ?></td>

                    <td class="px-4 py-2 border">
                        <?= $u['last_login'] ? $u['last_login'] : '-' ?>
                    </td>

                    <td class="px-4 py-2 border">
                        <?php if ($u['is_banned']): ?>
                            <span class="text-red-600 font-semibold">Banlı</span>
                        <?php else: ?>
                            <span class="text-green-600 font-semibold">Aktif</span>
                        <?php endif; ?>
                    </td>
                </tr>

            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>