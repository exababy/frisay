<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		http_response_code(405);
		header('Allow: POST');
		exit;
	}

	$postToken = (string) Tools::getValue('token');
	if (!hash_equals((string) $token, $postToken)) {
		http_response_code(403);
		exit('Geçersiz istek.');
	}

	Customer::stopAdminImpersonation();
	header('Location: ' . rtrim((string) $domain, '/') . '/');
	exit;
