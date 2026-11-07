<div class="offcanvas offcanvas-start cv-drawer" tabindex="-1" id="cvDrawer" aria-labelledby="cvDrawerLabel">
	<div class="cv-drawer__head">
		<span class="cv-drawer__title" id="cvDrawerLabel"><img src="{$antepli.logo_url|default:($domain|cat:'templates/antepli/img/logo.png')|escape}" alt="" width="52" height="49" loading="lazy"> {$siteName|escape}</span>
		<button type="button" class="cv-icon-btn cv-drawer__close" data-bs-dismiss="offcanvas" aria-label="{'Close'|translate}">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
		</button>
	</div>
	<div class="cv-drawer__body">
		<div class="cv-drawer__search">
			{include file='theme4/plugin/header-search.tpl' smSearchInputId='cvDrawerSearch'}
		</div>

		<p class="cv-drawer__label">Vitrinler</p>
		<div class="cv-drawer__cats">
			{foreach $menuCategories as $cat name=drawerCats}
				{assign var=_st value=$antepli.styles[$cat.id_category]|default:[]}
				{include file='./pal.tpl' id=$cat.id_category idx=$smarty.foreach.drawerCats.index assign=_pal}
				<a href="{$domain}{$cat.category_link|escape}" class="cv-chip-card cv-pal--{$_pal}" style="--i:{$smarty.foreach.drawerCats.index}">
					<span class="cv-chip-card__emoji" aria-hidden="true">{$_st.emoji|default:'🍪'|escape}</span>
					<span class="cv-chip-card__name">{$cat.category_name|escape}</span>
				</a>
			{/foreach}
		</div>

		<p class="cv-drawer__label">Menü</p>
		<ul class="cv-drawer__links">
			<li><a href="{$domain}"><span aria-hidden="true">🏠</span>{'Home Page'|translate}</a></li>
			<li><a href="{$domain}special"><span aria-hidden="true">🏷️</span>Fırsatlar</a></li>
			<li><a href="{$domain}favorites"><span aria-hidden="true">💗</span>{'Favorites'|translate}</a></li>
			<li><a href="{$domain}{if $isLoggedIn}orders{else}login{/if}"><span aria-hidden="true">📦</span>{if $isLoggedIn}Siparişlerim{else}{'Login'|translate}{/if}</a></li>
			<li><a href="{$domain}truck"><span aria-hidden="true">🚚</span>{'Order Traking'|translate}</a></li>
			<li><a href="{$domain}contact"><span aria-hidden="true">💬</span>{'Contact Us'|translate}</a></li>
			{foreach $cmsFooterLinks as $cmsLink name=drawerCms}
				{if $smarty.foreach.drawerCms.iteration > 4}{break}{/if}
				<li><a href="{$cmsLink.url|escape}"><span aria-hidden="true">📄</span>{$cmsLink.title|escape}</a></li>
			{/foreach}
		</ul>

		{if $hooks.mobile_menu}
		<div class="cv-drawer__hook">{$hooks.mobile_menu nofilter}</div>
		{/if}

		<div class="cv-drawer__contact">
			{if $antepli.whatsapp_link|default:'' != ''}
			<a href="{$antepli.whatsapp_link|escape}" class="cv-btn cv-btn--wa" target="_blank" rel="noopener">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.5 3.5A11.8 11.8 0 0 0 1.9 17.7L.3 23.6l6-1.6A11.8 11.8 0 0 0 20.5 3.5ZM12.1 21.6c-1.8 0-3.5-.5-5-1.4l-.4-.2-3.6.9 1-3.5-.2-.4a9.8 9.8 0 1 1 8.2 4.6Zm5.4-7.3c-.3-.1-1.8-.9-2-1-.3-.1-.5-.1-.7.1l-.9 1.2c-.2.2-.3.2-.6.1a8 8 0 0 1-4-3.5c-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6a1.1 1.1 0 0 0-.8.4 3.4 3.4 0 0 0-1.1 2.5 5.9 5.9 0 0 0 1.2 3.1 13.4 13.4 0 0 0 5.2 4.6c1.9.8 2.7.9 3.6.8a3.1 3.1 0 0 0 2-1.4 2.5 2.5 0 0 0 .2-1.4c-.1-.1-.3-.2-.6-.3Z"/></svg>
				WhatsApp ile sipariş
			</a>
			{/if}
			{if $contactPhone}
			<a href="tel:{$contactPhoneTel|default:$contactPhone|escape}" class="cv-btn cv-btn--ghost">📞 {$contactPhone|escape}</a>
			{/if}
		</div>
	</div>
</div>
