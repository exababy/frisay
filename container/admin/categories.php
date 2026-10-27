<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}
	$sonuc = '';
	if (Tools::isSubmit('saveCategoryTree')) {
		$token = (string) Tools::getValue('token');
		$raw = (string) Tools::getValue('tree');
		$items = json_decode($raw, true);
		$ok = hash_equals((string) $adminToken, $token) && is_array($items) && Category::saveAdminTree($items);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(['success' => $ok, 'message' => $ok ? 'Kategori düzeni kaydedildi.' : 'Kategori düzeni kaydedilemedi.'], JSON_UNESCAPED_UNICODE);
		exit;
	}
	if (Tools::isSubmit('deleteCategory'))
	{
		$formToken = md5(Tools::getValue('deleteCategory'));
		if (md5($adminToken) == $formToken)
		{
			$idCategory 	= (int)Tools::getValue('idCategory');
			$isHave		= DB::getRow('categories', 'id_category = '.(int)$idCategory.'', 'category_name');
			if ($isHave)
			{
				DB::execute('DELETE FROM categories WHERE id_category = '.(int)$idCategory.' LIMIT 1');
				$sonuc = $isHave.' kategorisi başarıyla silindi';
			}
		}
	}

	if (Tools::isSubmit('toggleHomeCategory')) {
		$postToken = (string) Tools::getValue('token');

		if (hash_equals((string) $adminToken, $postToken)) {
			$idCategory = (int) Tools::getValue('idCategory');
			$show = (int) Tools::getValue('show_on_home') === 1;

			if (Category::setShowOnHome($idCategory, $show)) {
				$sonuc = $show
					? adminT('Category added to homepage')
					: adminT('Category removed from homepage');
			}
		} else {
			$sonuc = adminT('Invalid request');
		}
	}

	if (Tools::isSubmit('toggleActiveCategory')) {
		$postToken = (string) Tools::getValue('token');
		if (hash_equals((string) $adminToken, $postToken)) {
			$idCategory = (int) Tools::getValue('idCategory');
			$active = (int) Tools::getValue('active') === 1;
			if (Category::setActive($idCategory, $active)) {
				$sonuc = $active ? 'Kategori aktifleştirildi.' : 'Kategori pasife alındı.';
			}
		} else {
			$sonuc = adminT('Invalid request');
		}
	}

	$activeFilter = Tools::getIsset('active') ? (int) Tools::getValue('active') : -1;
	$categorySearch = trim((string) Tools::getValue('q', ''));
	$categoryMenuFilter = (string) Tools::getValue('menu', 'all');
	if (!in_array($categoryMenuFilter, ['all', 'top', 'child'], true)) {
		$categoryMenuFilter = 'all';
	}
	$queryParams = array_filter([
		'active' => $activeFilter >= 0 ? $activeFilter : null,
	], static fn($v) => $v !== null && $v !== '');

	$explicitSort = trim((string) Tools::getValue('sort', ''));
	$explicitDir = trim((string) Tools::getValue('dir', ''));

	$sortState = Pagination::adminColumnSort(
		[
			'id' => 'c.id_category',
			'name' => 'c.category_name',
			'parent' => 'p.category_name',
			'home' => 'c.show_on_home',
			'status' => 'c.active',
		],
		'id',
		'asc',
		Admin::url('categories'),
		$queryParams,
		$explicitSort !== '' ? $explicitSort : 'id',
		$explicitDir !== '' ? $explicitDir : 'asc'
	);

	if ($explicitSort === '') {
		foreach ($sortState['links'] as $key => $link) {
			$sortState['links'][$key]['active'] = false;
			$sortState['links'][$key]['dir'] = '';
		}
		$orderBy = '';
		$sortState['sort'] = '';
		$sortState['dir'] = '';
	} else {
		$orderBy = $sortState['order_by'];
	}

	$categories = Category::getAdminTree($activeFilter);
	if ($categorySearch !== '' || $categoryMenuFilter !== 'all') {
		$searchNeedle = function_exists('mb_strtolower') ? mb_strtolower($categorySearch, 'UTF-8') : strtolower($categorySearch);
		$rootCategoryId = Category::getRootId();
		$categories = array_values(array_filter($categories, static function (array $row) use ($searchNeedle, $categoryMenuFilter, $rootCategoryId): bool {
			$name = (string) ($row['category_name'] ?? '');
			$link = (string) ($row['category_link'] ?? '');
			if ($searchNeedle !== '') {
				$haystack = function_exists('mb_strtolower') ? mb_strtolower($name . ' ' . $link, 'UTF-8') : strtolower($name . ' ' . $link);
				if (strpos($haystack, $searchNeedle) === false) {
					return false;
				}
			}
			$isTop = (int) ($row['id_parent'] ?? 0) === $rootCategoryId && (int) ($row['id_category'] ?? 0) !== $rootCategoryId;
			return $categoryMenuFilter === 'all' || ($categoryMenuFilter === 'top' && $isTop) || ($categoryMenuFilter === 'child' && !$isTop);
		}));
	}

	$smarty->assign([
		'categories' 	=> $categories,
		'activeFilter' 	=> $activeFilter,
		'sortLinks' 	=> $sortState['links'],
		'listSort' 		=> $sortState['sort'],
		'listDir' 		=> $sortState['dir'],
		'sonuc' 		=> $sonuc,
		'categoryTreeUrl' => Admin::url('categories'),
		'categorySearch' => $categorySearch,
		'categoryMenuFilter' => $categoryMenuFilter,
		'categoryTreeEditable' => $activeFilter < 0 && $categorySearch === '' && $categoryMenuFilter === 'all',
	]);

	AdminPage::add('categories', 'Categories');
