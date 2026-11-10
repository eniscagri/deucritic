<?php
http_response_code(410);
header('Content-Type: application/json; charset=utf-8');
exit(json_encode(['success' => false, 'msg' => 'Hikaye özelliği kaldırıldı.']));
