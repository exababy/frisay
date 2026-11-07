{assign var=t4IsAuthScreen value=(isset($authMode) && ($authMode == 'gate' || $authMode == 'login' || $authMode == 'register' || $authMode == 'forgot' || $authMode == 'reset'))}
{assign var=_t4js value=$domain|cat:'templates/theme4/js/'}
{if !$t4IsAuthScreen}
{if $pageName != 'home' && $pageName != 'category' && $pageName != 'brand' && $pageName != 'product'}</div>{/if}
</main>

{include file='theme4/plugin/schema-jsonld.tpl'}

<footer class="cv-footer" role="contentinfo">
	<svg class="cv-footer__drip" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
		<path d="M0,0 H1440 V38 C1400,38 1392,92 1360,92 C1328,92 1330,40 1290,40 C1250,40 1252,70 1220,70 C1188,70 1190,36 1150,36 C1110,36 1104,104 1068,104 C1032,104 1030,42 990,42 C950,42 954,66 922,66 C890,66 892,34 852,34 C812,34 806,86 772,86 C738,86 742,40 702,40 C662,40 664,112 626,112 C588,112 592,44 552,44 C512,44 516,72 484,72 C452,72 454,36 414,36 C374,36 370,94 336,94 C302,94 304,40 264,40 C224,40 228,64 196,64 C164,64 166,38 126,38 C86,38 82,100 48,100 C14,100 18,38 0,38 Z"/>
	</svg>
	<div class="cv-container cv-footer__inner">
		<div class="cv-footer__brand">
			<a href="{$domain}" class="cv-footer__logo" title="{$siteName|escape}"><img src="{$antepli.logo_url|default:($domain|cat:'templates/antepli/img/logo.png')|escape}" alt="{$siteName|escape}" width="120" height="113" loading="lazy"></a>
			<p>{if $antepli.footer_text|default:'' != ''}{$antepli.footer_text|escape}{elseif $footerDescription|default:'' != ''}{$footerDescription|escape|truncate:160}{else}Antep'in köklü fırın geleneğini; kerebiçten köylü kahkesine, şekerli ve tuzlu kuru pastalara kadar her gün taze üretip özenle kutuluyoruz.{/if}</p>
			<div class="cv-footer__social">
				{if $socialLinks.instagram}<a href="{$socialLinks.instagram|escape}" target="_blank" rel="noopener" aria-label="Instagram"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg></a>{/if}
				{if $socialLinks.facebook}<a href="{$socialLinks.facebook|escape}" target="_blank" rel="noopener" aria-label="Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M14 8h3V4h-3a4 4 0 0 0-4 4v2H8v4h2v8h4v-8h3l1-4h-4V8.5c0-.3.2-.5.5-.5Z"/></svg></a>{/if}
				{if $socialLinks.tiktok}<a href="{$socialLinks.tiktok|escape}" target="_blank" rel="noopener" aria-label="TikTok"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M16.6 5.8A4.3 4.3 0 0 1 15.5 3h-3.1v12.4a2.6 2.6 0 1 1-1.8-2.5V9.7a5.7 5.7 0 1 0 4.9 5.7V9a7.3 7.3 0 0 0 4.3 1.4V7.3a4.3 4.3 0 0 1-3.2-1.5Z"/></svg></a>{/if}
				{if $socialLinks.youtube}<a href="{$socialLinks.youtube|escape}" target="_blank" rel="noopener" aria-label="YouTube"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M23 7.2a3 3 0 0 0-2.1-2.1C19 4.6 12 4.6 12 4.6s-7 0-8.9.5A3 3 0 0 0 1 7.2 31 31 0 0 0 .6 12a31 31 0 0 0 .4 4.8 3 3 0 0 0 2.1 2.1c1.9.5 8.9.5 8.9.5s7 0 8.9-.5a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .4-4.8 31 31 0 0 0-.4-4.8ZM9.8 15V9l5.8 3Z"/></svg></a>{/if}
				{if $antepli.whatsapp_link|default:'' != ''}<a href="{$antepli.whatsapp_link|escape}" target="_blank" rel="noopener" aria-label="WhatsApp"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.3 13.8c-.2.6-1.3 1.2-1.8 1.3-.5 0-1 .3-3.3-.7a11.4 11.4 0 0 1-4.5-4 5.2 5.2 0 0 1-1.1-2.7 3 3 0 0 1 .9-2.2c.3-.3.6-.3.8-.3h.6c.2 0 .4 0 .6.5l.8 2c.1.2.1.3 0 .5l-.4.6-.4.4c-.1.2-.3.3-.1.6a8.5 8.5 0 0 0 1.6 2 7.7 7.7 0 0 0 2.3 1.4c.3.1.5.1.6-.1l.9-1.1c.2-.3.4-.2.6-.1l1.9.9c.3.1.5.2.5.3a2.4 2.4 0 0 1-.1 1.4Z"/></svg></a>{/if}
			</div>
		</div>

		<div class="cv-footer__col">
			<h3 class="cv-footer__title">Vitrinler</h3>
			<ul>
				{foreach $menuCategories as $cat name=footCats}
				{if $smarty.foreach.footCats.iteration > 7}{break}{/if}
				<li><a href="{$domain}{$cat.category_link|escape}">{$cat.category_name|escape}</a></li>
				{/foreach}
			</ul>
		</div>

		<div class="cv-footer__col">
			<h3 class="cv-footer__title">{'Corporate'|translate}</h3>
			<ul>
				{if $hooks.footer_menu}{$hooks.footer_menu nofilter}{/if}
				{foreach $cmsFooterLinks as $cmsLink}
				<li><a href="{$cmsLink.url|escape}">{$cmsLink.title|escape}</a></li>
				{/foreach}
				<li><a href="{$domain}contact">{'Contact Us'|translate}</a></li>
				<li><a href="{$domain}truck">{'Order Traking'|translate}</a></li>
			</ul>
		</div>

		<div class="cv-footer__col cv-footer__contact">
			<h3 class="cv-footer__title">Bize ulaşın</h3>
			<ul>
				{if $contactPhone}<li><a href="tel:{$contactPhoneTel|default:$contactPhone|escape}">📞 {$contactPhone|escape}</a></li>{/if}
				{if $contactEmail}<li><a href="mailto:{$contactEmail|escape}">✉️ {$contactEmail|escape}</a></li>{/if}
				{if $contactAddress}<li>📍 {$contactAddress|escape}</li>{/if}
				<li>🕘 {$openHour|escape} – {$closeHour|escape}</li>
			</ul>
		</div>
	</div>
	<div class="cv-container cv-footer__bottom">
		<span>&copy; {$year} {$siteName|escape}. {'All rights reserved.'|translate}</span>
		<span class="cv-footer__pay" aria-label="{'Payment logos'|translate}">
			<svg width="34" height="20" viewBox="0 0 36 22" aria-hidden="true"><circle cx="13" cy="11" r="8" fill="#EB001B"/><circle cx="23" cy="11" r="8" fill="#F79E1B"/><path d="M18 5.2a8 8 0 0 1 0 11.6 8 8 0 0 1 0-11.6z" fill="#FF5F00"/></svg>
			<svg width="40" height="14" viewBox="0 0 40 14" aria-hidden="true"><text x="0" y="12" font-family="Arial,sans-serif" font-size="13" font-weight="700" fill="#fff">VISA</text></svg>
			<svg width="36" height="16" viewBox="0 0 36 16" aria-hidden="true"><text x="0" y="13" font-family="Arial,sans-serif" font-size="12" font-weight="700" fill="#7fd3ff">TROY</text></svg>
		</span>
	</div>
</footer>

{include file='./partials/bottom-nav.tpl'}

{if $antepli.whatsapp_link|default:'' != ''}
<a href="{$antepli.whatsapp_link|escape}" class="cv-wa-fab" target="_blank" rel="noopener" aria-label="WhatsApp ile sipariş ver">
	<svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.3 13.8c-.2.6-1.3 1.2-1.8 1.3-.5 0-1 .3-3.3-.7a11.4 11.4 0 0 1-4.5-4 5.2 5.2 0 0 1-1.1-2.7 3 3 0 0 1 .9-2.2c.3-.3.6-.3.8-.3h.6c.2 0 .4 0 .6.5l.8 2c.1.2.1.3 0 .5l-.4.6-.4.4c-.1.2-.3.3-.1.6a8.5 8.5 0 0 0 1.6 2 7.7 7.7 0 0 0 2.3 1.4c.3.1.5.1.6-.1l.9-1.1c.2-.3.4-.2.6-.1l1.9.9c.3.1.5.2.5.3a2.4 2.4 0 0 1-.1 1.4Z"/></svg>
</a>
{/if}

{include file='theme4/plugin/cart.tpl'}
{include file='theme4/plugin/auth-modal.tpl'}
{if $hooks.footer}{$hooks.footer nofilter}{/if}
{/if}
<script src="{($_t4js|cat:'jquery-3.2.1.min.js')|asset_url}"></script>
<script src="{($_t4js|cat:'bootstrap.bundle.min.js')|asset_url}"></script>
<script src="{($_t4js|cat:'style.js')|asset_url}"></script>
{if !$t4IsAuthScreen}
<script src="{($_t4js|cat:'shopmore.js')|asset_url}"></script>
{/if}
{if $recaptchaConfigJson}
<script>window.fshopRecaptcha={$recaptchaConfigJson nofilter};</script>
{/if}
{if !$t4IsAuthScreen}
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
{/if}
{foreach $moduleAssets.js as $moduleJs}
<script src="{$moduleJs}"></script>
{/foreach}
{if $js}
<script src="{($_t4js|cat:$js)|asset_url}"></script>
{/if}
{if $isLoggedIn && !$t4IsAuthScreen}
<script src="{($_t4js|cat:'notifications.js')|asset_url}"></script>
{/if}
{if !$isLoggedIn}
<script src="{($_t4js|cat:'auth-pages.js')|asset_url}"></script>
{if !$t4IsAuthScreen}
<script src="{($_t4js|cat:'auth-modal.js')|asset_url}"></script>
{/if}
{/if}
<script src="{($js_dir|cat:'antepli.js')|asset_url}"></script>
{if !$t4IsAuthScreen}
{include file='theme4/_mini/priceAllert.tpl'}
{/if}
<div id="tostAlert" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
	<div class="d-flex">
		<div class="toast-body"></div>
		<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
	</div>
</div>
</body>
</html>
