<?php
	if (!defined('IN_ADMIN')) { exit; }

	Order::ensureSchema();
	Customer::ensureSchema();
	Address::ensureSchema();
	$flash = '';
	$selectedCustomer = null;
	$addresses = [];
	$products = [];
	$paymentMethods = Module::getPaymentMethods();
	if (isset($_GET['lookup'])) {
		header('Content-Type: application/json; charset=utf-8');
		$q = trim((string) ($_GET['q'] ?? ''));
		if ((string) $_GET['lookup'] === 'totals') {
			$oldCart = $_SESSION[Cart::SESSION_KEY] ?? [];
			$oldMeta = $_SESSION[Cart::META_KEY] ?? [];
			$oldCoupon = $_SESSION[Coupon::SESSION_KEY] ?? '';
			$oldPayment = Order::getSelectedPaymentMethod();
			$oldCargo = Cargo::getSelectedId();
			$idCargo = (int) ($_GET['cargo'] ?? 0);
			if ($idCargo <= 0) { $idCargo = Cargo::ensureSelected(); }
			Cart::clear();
			$cartErrors = [];
			foreach ((array) json_decode((string) ($_GET['items'] ?? '[]'), true) as $item) {
				if (!is_array($item)) { continue; }
				$added = Cart::add((int) ($item['id'] ?? 0), (float) ($item['qty'] ?? 1));
				if (empty($added['success'])) { $cartErrors[] = (string) ($added['message'] ?? 'Ürün sepete eklenemedi'); }
			}
			Order::setSelectedPaymentMethod((string) ($_GET['payment'] ?? ''));
			Cargo::setSelectedId($idCargo);
			$code = trim((string) ($_GET['coupon'] ?? ''));
			if ($code !== '') { $_SESSION[Coupon::SESSION_KEY] = $code; } else { unset($_SESSION[Coupon::SESSION_KEY]); }
			$cart = Cart::getSummary();
			$summary = Coupon::getCheckoutSummary((float) ($cart['total'] ?? 0), $cart);
			$manualValue = max(0, (float) ($_GET['manual'] ?? 0));
			$manual = (string) ($_GET['manual_type'] ?? '') === 'percent'
				? min((float) $summary['total'], round((float) $summary['subtotal'] * min(100, $manualValue) / 100, 2))
				: min((float) $summary['total'], $manualValue);
			$_SESSION[Cart::SESSION_KEY] = $oldCart; $_SESSION[Cart::META_KEY] = $oldMeta;
			if ($oldCoupon !== '') { $_SESSION[Coupon::SESSION_KEY] = $oldCoupon; } else { unset($_SESSION[Coupon::SESSION_KEY]); }
			Order::setSelectedPaymentMethod($oldPayment); Cargo::setSelectedId($oldCargo);
			$total = max(0, (float) $summary['total'] - $manual);
			echo json_encode(['subtotal'=>Tools::displayPrice((float)$summary['subtotal']),'promotion'=>Tools::displayPrice((float)$summary['promotion_discount']),'coupon'=>Tools::displayPrice((float)$summary['coupon_discount']),'payment'=>Tools::displayPrice((float)$summary['payment_discount']),'payment_label'=>(string)$summary['payment_discount_label'],'shipping'=>Tools::displayPrice((float)$summary['shipping']),'manual'=>Tools::displayPrice($manual),'total'=>Tools::displayPrice($total),'error'=>implode(' · ', $cartErrors)], JSON_UNESCAPED_UNICODE); exit;
		}
		if ((string) $_GET['lookup'] === 'customers') {
			$terms = preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];
			$where = []; $params = [];
			foreach ($terms as $term) { $where[] = '(user_full_name LIKE ? OR email LIKE ? OR phone LIKE ?)'; $like = '%' . $term . '%'; array_push($params, $like, $like, $like); }
			$rows = $terms ? (DB::execute('SELECT id_user,user_full_name,email,phone FROM users WHERE ' . implode(' AND ', $where) . ' ORDER BY user_full_name LIMIT 12', $params) ?: []) : [];
			echo json_encode($rows, JSON_UNESCAPED_UNICODE); exit;
		}
		$rows = DB::execute('SELECT id_product,product_name,price,stock,sale_unit,sale_qty_min,sale_qty_step FROM products WHERE active = 1 AND product_name LIKE ? ORDER BY product_name LIMIT 12', ['%' . $q . '%']) ?: [];
		echo json_encode($rows, JSON_UNESCAPED_UNICODE); exit;
	}
	if (!empty($_GET['customer_id'])) {
		$selectedCustomer = DB::execute('SELECT id_user, user_full_name, email, phone FROM users WHERE id_user = ? LIMIT 1', [(int) $_GET['customer_id']])[0] ?? null;
		if ($selectedCustomer) { $addresses = Address::getListForUser((int) $selectedCustomer['id_user']); }
	} elseif (isset($_GET['email']) && filter_var((string) $_GET['email'], FILTER_VALIDATE_EMAIL)) {
		$selectedCustomer = DB::execute('SELECT id_user, user_full_name, email, phone FROM users WHERE email = ? LIMIT 1', [strtolower(trim((string) $_GET['email']))])[0] ?? null;
		if ($selectedCustomer) { $addresses = Address::getListForUser((int) $selectedCustomer['id_user']); }
	}
	if (Tools::isSubmit('createCustomer')) {
		if (!hash_equals((string) $adminToken, (string) Tools::getValue('token'))) { $flash = 'Geçersiz istek'; }
		else {
			$made = Customer::createByAdmin((string) Tools::getValue('customer_name'), (string) Tools::getValue('customer_phone'), (string) Tools::getValue('customer_email'));
			if (!empty($made['success'])) { header('Location: ' . Admin::url('order-create?email=' . rawurlencode((string) Tools::getValue('customer_email')))); exit; }
			$flash = $made['message'];
		}
	}
	if (Tools::isSubmit('saveAdminOrder')) {
		if (!hash_equals((string) $adminToken, (string) Tools::getValue('token'))) { $flash = 'Geçersiz istek'; }
		else {
			$idUser = (int) Tools::getValue('id_user');
			$user = Customer::getByIdAdmin($idUser);
			$items = [];
			$oldCart = $_SESSION[Cart::SESSION_KEY] ?? [];
			$oldMeta = $_SESSION[Cart::META_KEY] ?? [];
			Cart::clear();
			foreach ((array) Tools::getValue('product_id', []) as $i => $pid) {
				$pid = (int) $pid; $qty = max(0.001, (float) ((array) Tools::getValue('qty', []))[$i] ?? 1);
				if ($pid > 0) { $added = Cart::add($pid, $qty); if (empty($added['success'])) { $flash = $added['message']; break; } }
			}
			$cart = Cart::getSummary();
			$_SESSION[Cart::SESSION_KEY] = $oldCart; $_SESSION[Cart::META_KEY] = $oldMeta;
			if ($flash === '' && $user && !empty($cart['items'])) {
				$addrId = (int) Tools::getValue('id_address');
				$addr = $addrId ? Address::getForUser($addrId, $idUser) : null;
				if (!$addr && Tools::isSubmit('save_address')) {
					$addressData = ['label'=>'Sipariş adresi','full_name'=>(string) (Tools::getValue('recipient_name') ?: $user['user_full_name']),'phone'=>(string) (Tools::getValue('recipient_phone') ?: $user['phone']),'city'=>(string) Tools::getValue('city'),'district'=>(string) Tools::getValue('district'),'address_text'=>(string) Tools::getValue('address_text')];
					$saved = Address::save($idUser, $addressData); if (!empty($saved['success'])) { $addrId=(int)$saved['id_address']; $addr=Address::getForUser($addrId,$idUser); } else { $flash=$saved['message']; }
				}
				$method = (string) Tools::getValue('payment_method');
				$payment = isset($paymentMethods[$method]) ? $method : Order::resolveDefaultPaymentMethod($paymentMethods);
				Order::setSelectedPaymentMethod($payment);
				if ($flash === '') {
					$result = Order::place(['_cart_snapshot'=>$cart,'_stored_id_user'=>$idUser,'_stored_coupon_code'=>trim((string)Tools::getValue('coupon_code')),'customer_name'=>(string)($addr['full_name']??$user['user_full_name']),'customer_phone'=>(string)($addr['phone']??$user['phone']),'recipient_name'=>(string)(Tools::getValue('recipient_name')?:($addr['full_name']??$user['user_full_name'])),'recipient_phone'=>(string)(Tools::getValue('recipient_phone')?:($addr['phone']??$user['phone'])),'customer_email'=>(string)$user['email'],'address_city'=>(string)($addr['city']??Tools::getValue('city')),'address_district'=>(string)($addr['district']??Tools::getValue('district')),'address_text'=>(string)($addr['address_text']??Tools::getValue('address_text')),'id_address'=>$addrId,'payment_method'=>$payment,'id_cargo'=>(int)Tools::getValue('id_cargo'),'manual_discount'=>max(0,(float)Tools::getValue('manual_discount')),'manual_discount_type'=>(string)Tools::getValue('manual_discount_type'),'accept_terms'=>1]);
					$_SESSION[Cart::SESSION_KEY] = $oldCart; $_SESSION[Cart::META_KEY] = $oldMeta;
					if (!empty($result['success'])) { header('Location: ' . Admin::url('order?id=' . (int)$result['id_order'])); exit; }
					$flash=$result['message'];
				}
			}
			if ($flash === '') { $flash = 'Müşteri, ürün ve adres bilgilerini kontrol edin.'; }
		}
	}
	$defaultCargoId = class_exists('Cargo') ? Cargo::ensureSelected() : 0;
	$smarty->assign(['flash'=>$flash,'adminToken'=>$adminToken,'selectedCustomer'=>$selectedCustomer,'addresses'=>$addresses,'orderProducts'=>$products,'paymentMethods'=>$paymentMethods,'cargoOptions'=>class_exists('Cargo')?Cargo::getList(true):[],'defaultCargoId'=>$defaultCargoId,'orderCreateUrl'=>Admin::url('order-create')]);
	AdminPage::add('order-create','Yeni sipariş oluştur');
