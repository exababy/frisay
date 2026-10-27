<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$css = 'catalog.css';
	$query = trim((string) (Tools::getValue('q') ?: Tools::getValue('query')));
	$sort = (string) Tools::getValue('sort');
	$stockOnly = (string) Tools::getValue('stock') === 'in_stock';
	$currentPage = max(1, (int) Tools::getValue('page'));
	$perPage = Pagination::PER_PAGE;
	$products = [];
	$productCount = 0;
	$searchQueryParams = ['q' => $query, 'sort' => $sort !== 'newest' ? $sort : ''];
	if ($stockOnly) {
		$searchQueryParams['stock'] = 'in_stock';
	}
	$pagination = Pagination::build(0, 1, $perPage, $domain . 'search', $searchQueryParams);

	if ($query !== '') {
		$productCount = Product::countSearch($query, $stockOnly);
		$pagination = Pagination::build($productCount, $currentPage, $perPage, $domain . 'search', $searchQueryParams);
		$products = Product::search($query, $perPage, $pagination['offset'], $sort, $stockOnly);
	}

	$searchSeo = Seo::resolvePage('search', $query !== '' ? 'Arama: ' . $query : 'Arama', 'Ürün ara');
	$pageTitle = $query !== '' ? 'Arama: ' . $query : $searchSeo['title'];
	$pageDesc = $searchSeo['description'];

	$smarty->assign([
		'searchQuery' => $query,
		'products' => $products,
		'productCount' => $productCount,
		'listTitle' => $query !== '' ? translate('Search results for') . ': "' . $query . '"' : translate('Search'),
		'pagination' => $pagination,
		'sort' => $sort !== '' ? $sort : 'newest',
		'sortOptions' => Pagination::getSortOptions(),
		'catalogBaseUrl' => $domain . 'search',
		'catalogQuery' => ['q' => $query],
		'catalogStockOnly' => $stockOnly,
		'emptyMessage' => translate('Search empty message'),
		'breadcrumb' => [
			['name' => translate('Home Page'), 'url' => $domain],
			['name' => translate('Search'), 'url' => ''],
		],
	]);
