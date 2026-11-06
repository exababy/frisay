<nav class="cv-bottom-nav" aria-label="{'Menu'|translate}">
	<a href="{$domain}" class="cv-bottom-nav__item{if $pageName == 'home'} is-active{/if}">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1Z"/></svg>
		<span>{'Home Page'|translate}</span>
	</a>
	<button type="button" class="cv-bottom-nav__item{if $pageName == 'category'} is-active{/if}" data-bs-toggle="offcanvas" data-bs-target="#cvDrawer" aria-controls="cvDrawer">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M3 9h18M3 15h18M12 3v18"/></svg>
		<span>Vitrinler</span>
	</button>
	<a href="#" class="cv-bottom-nav__cart js-show-cart" data-cv-cart-target aria-label="{'Cart'|translate}">
		<span class="cv-bottom-nav__cart-bubble">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 8h14l-1.2 11.1a2 2 0 0 1-2 1.9H8.2a2 2 0 0 1-2-1.9Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
			<span class="cv-badge sm-cart-count-badge{if $cart.count|default:0 <= 0} d-none{/if}">{$cart.count|default:0}</span>
		</span>
	</a>
	<a href="{$domain}favorites" class="cv-bottom-nav__item{if $pageName == 'favorites'} is-active{/if}">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
		<span>{'Favorites'|translate}</span>
	</a>
	<a href="{$domain}{if $isLoggedIn}my-account{else}login{/if}" class="cv-bottom-nav__item{if $pageName == 'my-account' || $pageName == 'login'} is-active{/if}">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
		<span>{if $isLoggedIn}Hesabım{else}{'Login'|translate}{/if}</span>
	</a>
</nav>
