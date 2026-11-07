<div class="offcanvas offcanvas-start cv-drawer" tabindex="-1" id="cvDrawer" aria-labelledby="cvDrawerLabel">
	<div class="cv-drawer__head">
		<img src="{$antepli.logo_url|default:($domain|cat:'templates/antepli/img/logo.png')|escape}" alt="{$siteName|escape}" width="64" height="60" loading="lazy" class="cv-drawer__logo">
		<span class="visually-hidden" id="cvDrawerLabel">{$siteName|escape}</span>
		<button type="button" class="cv-icon-btn cv-drawer__close" data-bs-dismiss="offcanvas" aria-label="{'Close'|translate}">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
		</button>
	</div>
	<div class="cv-drawer__body">
		<div class="cv-drawer__search">
			{include file='theme4/plugin/header-search.tpl' smSearchInputId='cvDrawerSearch'}
		</div>

		<p class="cv-kicker">Vitrinler</p>
		<ul class="cv-drawer__cats">
			{foreach $menuCategories as $cat name=drawerCats}
				<li style="--i:{$smarty.foreach.drawerCats.index}">
					<a href="{$domain}{$cat.category_link|escape}">
						<span class="cv-drawer__num">{($smarty.foreach.drawerCats.iteration)|string_format:'%02d'}</span>
						<span class="cv-drawer__name">{$cat.category_name|escape}</span>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
					</a>
				</li>
			{/foreach}
		</ul>

		<p class="cv-kicker">Menü</p>
		<ul class="cv-drawer__links">
			<li><a href="{$domain}">{'Home Page'|translate}</a></li>
			<li><a href="{$domain}special">Fırsatlar</a></li>
			<li><a href="{$domain}favorites">{'Favorites'|translate}</a></li>
			<li><a href="{$domain}{if $isLoggedIn}orders{else}login{/if}">{if $isLoggedIn}Siparişlerim{else}{'Login'|translate}{/if}</a></li>
			<li><a href="{$domain}truck">{'Order Traking'|translate}</a></li>
			<li><a href="{$domain}contact">{'Contact Us'|translate}</a></li>
			{foreach $cmsFooterLinks as $cmsLink name=drawerCms}
				{if $smarty.foreach.drawerCms.iteration > 4}{break}{/if}
				<li><a href="{$cmsLink.url|escape}">{$cmsLink.title|escape}</a></li>
			{/foreach}
		</ul>

		{if $hooks.mobile_menu}
		<div class="cv-drawer__hook">{$hooks.mobile_menu nofilter}</div>
		{/if}

		<div class="cv-drawer__contact">
			{if $antepli.whatsapp_link|default:'' != ''}
			<a href="{$antepli.whatsapp_link|escape}" class="cv-btn cv-btn--primary cv-btn--block" target="_blank" rel="noopener">WhatsApp ile sipariş</a>
			{/if}
			{if $contactPhone}
			<a href="tel:{$contactPhoneTel|default:$contactPhone|escape}" class="cv-btn cv-btn--line cv-btn--block">{$contactPhone|escape}</a>
			{/if}
		</div>
	</div>
</div>
