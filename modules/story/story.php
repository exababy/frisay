<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class StoryModule extends ModuleBase
{
	public string $name = 'story';
	public string $title = 'Story (Hikaye) Modülü';
	public string $version = '1.0.0';
	public string $description = 'Ana sayfa story (daire) alanında görsel ve video hikayelerin yönetimi';
	public string $author = 'Can MEMİŞ';

	public array $displayHooks = [
		'home_slider' => 'Ana sayfa üst slayt alanı (story + slider birlikte)',
	];

	public array $defaultDisplayHooks = ['home_slider'];

	public array $frontStylesheets = ['front.css'];
	public array $frontScripts = ['front.js'];
	public array $adminStylesheets = ['admin.css'];
	public array $adminScripts = ['admin.js'];

	public function install(): bool
	{
		if (!$this->runSqlFile('install.sql')) {
			return false;
		}

		$uploadDir = $this->getUploadDir();
		if (!is_dir($uploadDir)) {
			@mkdir($uploadDir, 0755, true);
		}

		// Varsayılan seed story'ler ekle (kullanıcı hemen görebilsin)
		if ((int) DB::getValue('SELECT COUNT(*) FROM story_items') === 0) {
			$defaultStories = [
				['title' => 'TÜMÜ', 'link_url' => '', 'position' => 1],
				['title' => 'ELEKTRONİK', 'link_url' => 'elektronik', 'position' => 2],
				['title' => 'KOZMETİK', 'link_url' => 'kozmetik', 'position' => 3],
				['title' => 'VİTAMİN', 'link_url' => 'vitamin-takviye', 'position' => 4],
			];

			foreach ($defaultStories as $ds) {
				DB::insert('story_items', [
					'title' => $ds['title'],
					'cover_file' => '',
					'media_type' => 'image',
					'media_file' => '',
					'link_url' => $ds['link_url'],
					'position' => $ds['position'],
					'active' => 1,
				]);
			}
		}

		return true;
	}

	public function uninstall(): bool
	{
		return $this->runSqlFile('uninstall.sql');
	}

	public static function getUploadDir(): string
	{
		return dirname(__DIR__) . '/story/assets/uploads';
	}

	public static function getMediaUrl(string $file): string
	{
		global $domain;

		if ($file === '') {
			return '';
		}

		return rtrim($domain, '/') . '/modules/story/assets/uploads/' . rawurlencode($file);
	}

	public function boot(): void
	{
		if (!Module::isInstalled($this->name)) {
			return;
		}

		$assigned = Module::getAssignedDisplayHooks($this->name);

		if ($assigned === [] || in_array('home_story', $assigned, true)) {
			Module::setDisplayHooks($this->name, ['home_slider']);
		}

		// Aynı hook'ta slider'dan önce göster (position sıralaması)
		DB::execute(
			'UPDATE module_display_hooks SET position = -10 WHERE module_name = ? AND hook_name = ?',
			[$this->name, 'home_slider']
		);
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		$flash = '';

		// Hikaye kaydet (Ekle / Güncelle)
		if (Tools::isSubmit('saveStory')) {
			$postToken = (string) Tools::getValue('token');

			if (!hash_equals($adminToken, $postToken)) {
				$flash = 'Geçersiz güvenlik kodu!';
			} else {
				$result = self::saveStory([
					'id_story' => (int) Tools::getValue('id_story'),
					'title' => trim((string) Tools::getValue('title')),
					'media_type' => (string) Tools::getValue('media_type', 'image'),
					'link_url' => trim((string) Tools::getValue('link_url')),
					'position' => (int) Tools::getValue('position'),
					'active' => Tools::getValue('active') ? 1 : 0,
				], $_FILES['cover_file'] ?? null, $_FILES['media_file'] ?? null);

				$flash = $result['message'];
			}
		}

		// Hikaye Sil
		if (Tools::isSubmit('deleteStory')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$result = self::deleteStory((int) Tools::getValue('id_story'));
				$flash = $result['message'];
			}
		}

		// Durum Değiştir (Aktif / Pasif)
		if (Tools::isSubmit('toggleStory')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$result = self::toggleActive((int) Tools::getValue('id_story'));
				$flash = $result['message'];
			}
		}

		$editId = (int) Tools::getValue('edit');
		$editStory = $editId > 0 ? self::getById($editId) : null;

		$smarty->assign([
			'stories' => self::getList(false),
			'editStory' => $editStory,
			'flash' => $flash,
		]);
	}

	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook !== 'home_slider') {
			return null;
		}

		$stories = self::getList(true);

		$html = $this->renderFrontTemplate('home_story', [
			'stories' => $stories,
		]);

		return $html !== '' ? $html : null;
	}

	/** @return array<int, array<string, mixed>> */
	public static function getList(bool $activeOnly = true): array
	{
		$sql = 'SELECT * FROM story_items';

		if ($activeOnly) {
			$sql .= ' WHERE active = 1';
		}

		$sql .= ' ORDER BY position ASC, id_story ASC';

		$rows = DB::execute($sql) ?: [];

		foreach ($rows as &$row) {
			$row = self::enrich($row);
		}
		unset($row);

		return $rows;
	}

	public static function getById(int $idStory): ?array
	{
		$row = DB::getRowSafe('story_items', 'id_story = ?', [$idStory]);

		return $row ? self::enrich($row) : null;
	}

	/** @param array<string, mixed> $row */
	private static function enrich(array $row): array
	{
		$row['cover_url'] = self::getMediaUrl((string) ($row['cover_file'] ?? ''));
		$row['media_url'] = self::getMediaUrl((string) ($row['media_file'] ?? ''));

		if ($row['cover_url'] === '' && $row['media_url'] !== '' && $row['media_type'] === 'image') {
			$row['cover_url'] = $row['media_url'];
		}

		return $row;
	}

	/** @param array<string, mixed> $data */
	public static function saveStory(array $data, ?array $coverFile = null, ?array $mediaFile = null): array
	{
		$idStory = (int) ($data['id_story'] ?? 0);
		$title = trim((string) ($data['title'] ?? ''));
		$mediaType = in_array(($data['media_type'] ?? 'image'), ['image', 'video'], true) ? $data['media_type'] : 'image';
		$linkUrl = trim((string) ($data['link_url'] ?? ''));
		$position = (int) ($data['position'] ?? 0);
		$active = !empty($data['active']) ? 1 : 0;

		if ($title === '') {
			return ['success' => false, 'message' => 'Hikaye başlığı boş olamaz!'];
		}

		$existing = $idStory > 0 ? self::getById($idStory) : null;
		$coverFileName = $existing ? (string) $existing['cover_file'] : '';
		$mediaFileName = $existing ? (string) $existing['media_file'] : '';

		$uploadDir = self::getUploadDir();
		if (!is_dir($uploadDir)) {
			@mkdir($uploadDir, 0755, true);
		}

		// Ortam kütüphanesinden kapak resmi seçilmişse
		$selectedCover = trim((string) Tools::getValue('selected_cover_path'));
		if ($selectedCover !== '') {
			$fullSrcPath = dirname(__DIR__, 2) . '/' . $selectedCover;
			if (file_exists($fullSrcPath)) {
				if (strpos($selectedCover, 'modules/story/assets/uploads/') === 0) {
					$coverFileName = basename($selectedCover);
				} else {
					$ext = pathinfo($fullSrcPath, PATHINFO_EXTENSION) ?: 'jpg';
					$newFilename = 'story_cover_' . uniqid() . '.' . $ext;
					if (copy($fullSrcPath, $uploadDir . '/' . $newFilename)) {
						if ($coverFileName !== '' && is_file($uploadDir . '/' . $coverFileName)) {
							@unlink($uploadDir . '/' . $coverFileName);
						}
						$coverFileName = $newFilename;
					}
				}
			}
		}

		// Ortam kütüphanesinden hikaye medyası seçilmişse
		$selectedMedia = trim((string) Tools::getValue('selected_media_path'));
		if ($selectedMedia !== '') {
			$fullSrcPath = dirname(__DIR__, 2) . '/' . $selectedMedia;
			if (file_exists($fullSrcPath)) {
				if (strpos($selectedMedia, 'modules/story/assets/uploads/') === 0) {
					$mediaFileName = basename($selectedMedia);
				} else {
					$ext = pathinfo($fullSrcPath, PATHINFO_EXTENSION) ?: 'jpg';
					$newFilename = 'story_media_' . uniqid() . '.' . $ext;
					if (copy($fullSrcPath, $uploadDir . '/' . $newFilename)) {
						if ($mediaFileName !== '' && is_file($uploadDir . '/' . $mediaFileName)) {
							@unlink($uploadDir . '/' . $mediaFileName);
						}
						$mediaFileName = $newFilename;
					}
				}
			}
		}

		// Kapak görseli yükle (Image)
		if ($coverFile && !empty($coverFile['name']) && $coverFile['error'] === UPLOAD_ERR_OK) {
			$uploadedCover = self::handleFileUpload($coverFile, ['jpg', 'jpeg', 'png', 'webp', 'gif'], 'cover');
			if ($uploadedCover['success']) {
				if ($coverFileName !== '' && is_file($uploadDir . '/' . $coverFileName)) {
					@unlink($uploadDir . '/' . $coverFileName);
				}
				$coverFileName = $uploadedCover['filename'];
			} else {
				return $uploadedCover;
			}
		}

		// Hikaye medyası yükle (Image or Video)
		if ($mediaFile && !empty($mediaFile['name']) && $mediaFile['error'] === UPLOAD_ERR_OK) {
			$allowedExts = $mediaType === 'video' ? ['mp4', 'webm'] : ['jpg', 'jpeg', 'png', 'webp', 'gif'];
			$uploadedMedia = self::handleFileUpload($mediaFile, $allowedExts, 'media');
			if ($uploadedMedia['success']) {
				if ($mediaFileName !== '' && is_file($uploadDir . '/' . $mediaFileName)) {
					@unlink($uploadDir . '/' . $mediaFileName);
				}
				$mediaFileName = $uploadedMedia['filename'];
			} else {
				return $uploadedMedia;
			}
		}

		$fields = [
			'title' => $title,
			'cover_file' => $coverFileName,
			'media_type' => $mediaType,
			'media_file' => $mediaFileName,
			'link_url' => $linkUrl,
			'position' => $position,
			'active' => $active,
		];

		if ($idStory > 0 && $existing) {
			DB::update('story_items', $fields, 'id_story = :where_id', ['where_id' => $idStory]);
			return ['success' => true, 'message' => 'Hikaye başarıyla güncellendi.'];
		}

		DB::insert('story_items', $fields);
		return ['success' => true, 'message' => 'Yeni hikaye başarıyla eklendi.'];
	}

	private static function handleFileUpload(array $file, array $allowedExts, string $prefix): array
	{
		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

		if (!in_array($ext, $allowedExts, true)) {
			return [
				'success' => false,
				'message' => 'Geçersiz dosya formatı (.' . $ext . ')! İzin verilenler: ' . implode(', ', $allowedExts),
			];
		}

		$filename = 'story_' . $prefix . '_' . time() . '_' . substr(md5(uniqid('', true)), 0, 8) . '.' . $ext;
		$targetPath = self::getUploadDir() . '/' . $filename;

		if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
			return ['success' => false, 'message' => 'Dosya yükleme hatası oluştu!'];
		}

		return ['success' => true, 'filename' => $filename];
	}

	public static function deleteStory(int $idStory): array
	{
		$story = self::getById($idStory);

		if (!$story) {
			return ['success' => false, 'message' => 'Hikaye bulunamadı'];
		}

		$uploadDir = self::getUploadDir();
		if (!empty($story['cover_file']) && is_file($uploadDir . '/' . $story['cover_file'])) {
			@unlink($uploadDir . '/' . $story['cover_file']);
		}
		if (!empty($story['media_file']) && is_file($uploadDir . '/' . $story['media_file'])) {
			@unlink($uploadDir . '/' . $story['media_file']);
		}

		DB::execute('DELETE FROM story_items WHERE id_story = ?', [$idStory]);

		return ['success' => true, 'message' => 'Hikaye silindi'];
	}

	public static function toggleActive(int $idStory): array
	{
		$story = self::getById($idStory);

		if (!$story) {
			return ['success' => false, 'message' => 'Hikaye bulunamadı'];
		}

		$newActive = $story['active'] ? 0 : 1;
		DB::update('story_items', ['active' => $newActive], 'id_story = :where_id', ['where_id' => $idStory]);

		return ['success' => true, 'message' => 'Durum güncellendi'];
	}
}
