<?php

function productFilesResponse(array $data, int $code = 200): void
{
	http_response_code($code);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($data);
	exit;
}

if (!defined('IN_SCRIPT') || !Admin::isLoggedIn()) {
	productFilesResponse(['success' => false, 'message' => 'Yönetici oturumu bulunamadı'], 403);
}

// /api/module.php admin bootstrap'i kullanmadığı için token'ı oturumdan doğrula.
$expectedToken = (string) ($_SESSION['admin_csrf_token'] ?? '');
$token = (string) Tools::getValue('token');
if ($expectedToken === '' || !hash_equals($expectedToken, $token)) {
	productFilesResponse(['success' => false, 'message' => 'Geçersiz istek (CSRF token eşleşmedi)'], 403);
}

$id = (int) Tools::getValue('id_product');
if ($id < 1 || !Product::getByIdAdmin($id)) {
	productFilesResponse(['success' => false, 'message' => 'Önce ürünü kaydedin'], 422);
}

$kind = (string) Tools::getValue('kind');
$base = dirname(__DIR__) . '/uploads/' . $id;
if (!is_dir($base) && !mkdir($base, 0755, true) && !is_dir($base)) {
	productFilesResponse(['success' => false, 'message' => 'Yükleme klasörü oluşturulamadı'], 500);
}

if ($kind === 'documents') {
	$titles = $_POST['document_titles'] ?? [];
	$files = $_FILES['documents'] ?? null;
	if (!$files || !is_array($files['name'] ?? null)) {
		productFilesResponse(['success' => false, 'message' => 'Yüklemek için dosya seçin'], 422);
	}
	$inserted = 0;
	$count = count($files['name']);
	for ($i = 0; $i < $count; $i++) {
		if ((int) ($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
		if ((int) ($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $files['tmp_name'][$i])) {
			productFilesResponse(['success' => false, 'message' => 'Dosyalardan biri yüklenemedi'], 422);
		}
		$original = basename((string) $files['name'][$i]);
		$ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
		if (!in_array($ext, ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'txt'], true) || (int) $files['size'][$i] > 15 * 1024 * 1024) {
			productFilesResponse(['success' => false, 'message' => 'Yalnız PDF, resim ve TXT dosyaları kabul edilir (dosya başına en fazla 15 MB)'], 422);
		}
		$mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $files['tmp_name'][$i]);
		$allowedMime = ['pdf' => ['application/pdf'], 'png' => ['image/png'], 'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'gif' => ['image/gif'], 'webp' => ['image/webp'], 'txt' => ['text/plain', 'application/octet-stream']];
		if (!in_array($mime, $allowedMime[$ext] ?? [], true)) {
			productFilesResponse(['success' => false, 'message' => 'Dosya içeriği uzantıyla eşleşmiyor: ' . $original], 422);
		}
		$stored = 'document-' . bin2hex(random_bytes(16)) . '.' . $ext;
		if (!move_uploaded_file((string) $files['tmp_name'][$i], $base . '/' . $stored)) {
			productFilesResponse(['success' => false, 'message' => 'Dosya kaydedilemedi'], 500);
		}
		$title = trim(strip_tags((string) ($titles[$i] ?? '')));
		if ($title === '') $title = pathinfo($original, PATHINFO_FILENAME);
		DB::execute('INSERT INTO product_files_documents (id_product, title, file_path, file_name) VALUES (?, ?, ?, ?)', [$id, mb_substr($title, 0, 255), 'uploads/' . $id . '/' . $stored, $original]);
		$inserted++;
	}
	productFilesResponse(['success' => $inserted > 0, 'message' => $inserted . ' indirilebilir dosya eklendi']);
}

if ($kind === 'logo') {
	$file = $_FILES['logo_file'] ?? null;
	if (!$file || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
		productFilesResponse(['success' => false, 'message' => 'Yüklemek için bir logo seçin'], 422);
	}
	$original = basename((string) $file['name']);
	$ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
	if (!in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true) || (int) $file['size'] > 15 * 1024 * 1024) {
		productFilesResponse(['success' => false, 'message' => 'Logo PNG, JPG, GIF veya WEBP olmalı ve 15 MB sınırını aşmamalı'], 422);
	}
	$mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
	$allowed = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'webp' => 'image/webp'];
	if (($allowed[$ext] ?? '') !== $mime) productFilesResponse(['success' => false, 'message' => 'Logo dosyasının içeriği doğrulanamadı'], 422);
	$rows = DB::getAssocRow('product_files_assets', 'id_product = ' . $id);
	$row = is_array($rows[0] ?? null) ? $rows[0] : [];
	$stored = 'logo-' . bin2hex(random_bytes(16)) . '.' . $ext;
	if (!move_uploaded_file((string) $file['tmp_name'], $base . '/' . $stored)) productFilesResponse(['success' => false, 'message' => 'Logo kaydedilemedi'], 500);
	if (!empty($row['logo_path'])) @unlink(dirname(__DIR__) . '/' . $row['logo_path']);
	DB::execute('INSERT INTO product_files_assets (id_product, logo_path, logo_name) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE logo_path = VALUES(logo_path), logo_name = VALUES(logo_name)', [$id, 'uploads/' . $id . '/' . $stored, $original]);
	productFilesResponse(['success' => true, 'message' => 'Ürün logosu yüklendi']);
}

productFilesResponse(['success' => false, 'message' => 'Geçersiz işlem'], 400);
