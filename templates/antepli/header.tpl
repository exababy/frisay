<!DOCTYPE html>
<html lang="{$selectLang|escape}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title>{$documentTitle|default:$siteName|escape}</title>
	{if $pageDesc}
	<meta name="description" content="{$pageDesc|escape}">
	<meta property="og:description" content="{$pageDesc|escape}">
	{/if}
	<meta property="og:title" content="{$documentTitle|default:$siteName|escape}">
	<meta property="og:type" content="website">
	<meta property="og:site_name" content="{$siteName|escape}">
	<meta name="application-name" content="{$siteName|escape}">
	<meta property="og:url" content="{$domain|escape}">
	<meta name="twitter:title" content="{$documentTitle|default:$siteName|escape}">
	{if $pageDesc}
	<meta name="twitter:description" content="{$pageDesc|escape}">
	{/if}
	<link rel="canonical" href="{$domain|escape}">
	<meta name="robots" content="index, follow">
	<meta name="publisher" content="{$siteName|escape}" />
	<meta name="language" content="{$selectLang}">
	<link rel="icon" type="image/x-icon" href="{$domain}img/favicon.ico?v=1">
	{if $lcpImage}
	<link rel="preload" as="image" href="{$lcpImage|escape}" fetchpriority="high">
	{/if}
	{if $hooks.head.top}
	{$hooks.head.top nofilter}
	{else}
	<meta name="theme-color" content="#2b160a">
	<link rel="manifest" href="{$domain}manifest.php">
	<script>
		if ('serviceWorker' in navigator) {
			window.addEventListener('load', () => {
				navigator.serviceWorker.register({($domain|cat:'sw.php')|js nofilter});
			});
		}
	</script>
	{/if}
	<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500..800&family=Instrument+Serif:ital@0;1&family=Manrope:wght@400;500;600;700;800&display=swap">
	{include file='./partials/stylesheets.tpl'}
	{foreach $moduleAssets.css as $moduleCss}
	<link rel="stylesheet" href="{$moduleCss}" />
	{/foreach}

	<script>
		var domain = {$domain|js nofilter};
		var csrfToken = {$token|js nofilter};
		window.csrfToken = csrfToken;
		var cartApiUrl = domain + 'api/cart.php';
		var couponApiUrl = domain + 'api/coupon.php';
		var authApiUrl = domain + 'api/auth.php';
		var favoriteApiUrl = domain + 'api/favorite.php';
		var accountApiUrl = domain + 'api/account.php';
		var isLoggedIn = {if $isLoggedIn}true{else}false{/if};
		var baseDir = domain;
		window.baseDir = domain;
		window.imgDir = domain + 'img/';
		window.cssDir = {$css_dir|js nofilter};
		window.searchSuggestUrl = domain + 'api/search-suggest.php';
		document.documentElement.classList.add('cv-js');
		// antepli.js yüklenemezse animasyon bekleyen içerik gizli kalmasın
		setTimeout(function () { if (!window.cvReady) document.documentElement.classList.remove('cv-js'); }, 4000);
	</script>
</head>
{assign var=t4IsAuthScreen value=(isset($authMode) && ($authMode == 'gate' || $authMode == 'login' || $authMode == 'register' || $authMode == 'forgot' || $authMode == 'reset'))}
{assign var=cv value=$antepli|default:[]}
<body id="{$pageName}" class="sm-body prime-body cv-body cv-page--{$pageName|escape}{if $t4IsAuthScreen} t4-auth-screen{/if}">
{if $adminImpersonationActive|default:false}
<div class="cv-impersonate">
	<span>Yönetici önizlemesi: <strong>{$adminImpersonationName|escape}</strong> hesabındasınız.</span>
	<form method="post" action="{$domain}admin-impersonation-exit"><input type="hidden" name="token" value="{$token|escape}"><button type="submit" class="btn btn-sm btn-light">Yönetici oturumuna dön</button></form>
</div>
{/if}
{if !$t4IsAuthScreen}
{include file='./partials/drawer.tpl'}

{assign var=cvAnnounce value=$cv.announce|default:'Sıcak fırından, her gün taze · Kerebiç, kahke ve kuru pasta'}
{if $cvAnnounce != ''}
<div class="cv-announce" role="note">
	<div class="cv-announce__track">
		<span>{$cvAnnounce|escape}</span>
		<span aria-hidden="true">{$cvAnnounce|escape}</span>
		<span aria-hidden="true">{$cvAnnounce|escape}</span>
	</div>
</div>
{/if}

{assign var=cvLogo value=$cv.logo_url|default:($domain|cat:'templates/antepli/img/logo.png')}
<header class="cv-header sm-header" data-cv-header>
	<div class="cv-header__inner">
		<div class="cv-header__left">
			<button type="button" class="cv-icon-btn cv-header__menu" data-bs-toggle="offcanvas" data-bs-target="#cvDrawer" aria-controls="cvDrawer" aria-label="{'Menu'|translate}">
				<span class="cv-burger" aria-hidden="true"><i></i><i></i></span>
			</button>
			<button type="button" class="cv-icon-btn cv-header__search-btn" data-cv-search-toggle aria-label="{'Search'|translate}" aria-expanded="false">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
				<span class="cv-header__search-label">Ara</span>
			</button>
		</div>

		<a href="{$domain}" class="cv-logo" title="{$siteName|escape}">
			<img src="{$cvLogo|escape}" alt="{$siteName|escape}" class="cv-logo__badge" width="96" height="90" decoding="async" fetchpriority="high">
			<span class="visually-hidden">{$siteName|escape}</span>
		</a>

		<nav class="cv-nav" aria-label="{'Menu'|translate}">
					<ul class="cv-nav__list">
						<li class="cv-nav__mega">
							<button type="button" class="cv-nav__link" aria-haspopup="true">Vitrinler</button>
							<div class="cv-mega">
								<div class="cv-mega__inner">
									<p class="cv-kicker">Ürün grupları</p>
									<ul class="cv-mega__list">
									{foreach $menuCategories as $cat}
										<li><a href="{$domain}{$cat.category_link|escape}" class="cv-mega__item"><span>{$cat.category_name|escape}</span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
									{/foreach}
									</ul>
								</div>
							</div>
						</li>
						{if $hooks.main_menu}
							{$hooks.main_menu nofilter}
						{else}
						<li><a href="{$domain}special" class="cv-nav__link{if $pageName == 'special'} is-active{/if}">Fırsatlar</a></li>
						<li><a href="{$domain}contact" class="cv-nav__link{if $pageName == 'contact'} is-active{/if}">{'Contact Us'|translate}</a></li>
						{/if}
					</ul>
				</nav>

		<div class="cv-header__tools">
			{if $isLoggedIn}
			<div class="cv-header__notify sm-header__action-slot cv-hide-sm">
				{include file='theme4/plugin/notifications-dropdown.tpl'}
			</div>
			{/if}
			<a href="{$domain}favorites" class="cv-icon-btn cv-hide-sm" title="{'Favorites'|translate}" aria-label="{'Favorites'|translate}">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
				{if $favoriteCount|default:0 > 0}<span class="cv-badge">{$favoriteCount}</span>{/if}
			</a>
			<a href="{$domain}{if $isLoggedIn}my-account{else}login{/if}" class="cv-icon-btn cv-hide-sm" title="{'My Account'|translate}" aria-label="{'My Account'|translate}">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
			</a>
			<a href="#" class="cv-cart-btn js-show-cart" data-cv-cart-target title="{'Cart'|translate}" aria-label="{'Cart'|translate}">
				<span class="cv-cart-btn__icon">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 8h14l-1.2 11.1a2 2 0 0 1-2 1.9H8.2a2 2 0 0 1-2-1.9Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
					<span class="cv-badge sm-cart-count-badge{if $cart.count|default:0 <= 0} d-none{/if}">{$cart.count|default:0}</span>
				</span>
				<span class="cv-cart-btn__total t4-cart-btn__total">{$cart.total_formatted|default:'0,00 ₺'}</span>
			</a>
		</div>
	</div>
	<div class="cv-header__search" data-cv-search-panel hidden>
		<div class="cv-container">
			{include file='theme4/plugin/header-search.tpl' smSearchInputId='cvSearchInput'}
		</div>
	</div>
</header>

<main class="page cv-main" role="main" id="cvMain">
{if $pageName != 'home' && $pageName != 'category' && $pageName != 'brand' && $pageName != 'product'}
<div class="cv-container sm-container sm-page-wrap cv-page-wrap">
{include file='theme4/partials/breadcrumb.tpl'}
{/if}
{/if}
