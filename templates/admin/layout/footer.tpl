			</div>
		</div>
	</div>
</div>

<div class="cmdk-overlay" id="adminCmdk" hidden>
	<div class="cmdk-modal" role="dialog" aria-modal="true" aria-label="{'Search'|adminT}">
		<div class="cmdk-input-wrap">
			<i data-lucide="search"></i>
			<input type="text" id="adminCmdkInput" class="cmdk-input" placeholder="{'Search products, customers, orders…'|adminT}" autocomplete="off" spellcheck="false">
			<kbd class="cmdk-kbd">esc</kbd>
		</div>
		<div class="cmdk-list" id="adminCmdkList"></div>
		<div class="cmdk-footer">
			<span><kbd>↑↓</kbd> {'Navigate'|adminT}</span>
			<span><kbd>↵</kbd> {'Open'|adminT}</span>
			<span><kbd>esc</kbd> {'Close'|adminT}</span>
		</div>
	</div>
</div>

<div class="modal fade" id="admin-confirm-modal" tabindex="-1" aria-hidden="true" aria-labelledby="admin-confirm-title">
	<div class="modal-dialog modal-dialog-centered admin-confirm-dialog">
		<div class="modal-content admin-confirm-content">
			<div class="modal-header admin-confirm-header">
				<div class="admin-confirm-heading">
					<span class="admin-confirm-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m10.3 3.9-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3.1l-8-14a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg></span>
					<div><span class="admin-confirm-kicker">{'Confirm action'|adminT}</span><h5 class="modal-title" id="admin-confirm-title">{'Confirm action'|adminT}</h5></div>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{'Close'|adminT}"></button>
			</div>
			<div class="modal-body admin-confirm-body" id="admin-confirm-message">
				{'Are you sure you want to perform this action?'|adminT}
			</div>
			<div class="modal-footer admin-confirm-footer">
				<button type="button" class="btn admin-confirm-cancel" data-bs-dismiss="modal">{'Cancel'|adminT}</button>
				<button type="button" class="btn admin-confirm-submit" id="admin-confirm-btn"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m19 6-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>{'Yes, confirm'|adminT}</button>
			</div>
		</div>
	</div>
</div>
<style>
{literal}
.admin-confirm-dialog{max-width:440px}.admin-confirm-content{overflow:hidden;border:0;border-radius:18px;box-shadow:0 24px 70px rgba(15,23,42,.22)}.admin-confirm-header{display:flex;align-items:flex-start;padding:22px 24px 16px;border:0;background:#fff}.admin-confirm-heading{display:flex;align-items:center;gap:14px}.admin-confirm-icon{display:flex;align-items:center;justify-content:center;flex:0 0 46px;width:46px;height:46px;border-radius:15px;background:#fff1f0;color:#d92d20}.admin-confirm-kicker{display:block;margin-bottom:3px;color:#8a929e;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}.admin-confirm-header .modal-title{margin:0;color:#182230;font-size:19px;font-weight:700}.admin-confirm-body{padding:4px 24px 24px 84px;color:#596579;font-size:14px;line-height:1.65}.admin-confirm-footer{display:flex;justify-content:flex-end;gap:10px;padding:16px 24px 22px;border:0;background:#f8f9fb}.admin-confirm-cancel,.admin-confirm-submit{display:inline-flex;align-items:center;justify-content:center;gap:8px;box-sizing:border-box;flex:0 0 138px!important;width:138px!important;min-width:138px!important;height:42px!important;min-height:42px!important;padding:0 12px;border-radius:10px;font-size:13px;font-weight:600;line-height:1}.admin-confirm-cancel{border:1px solid #d9dee7;background:#fff;color:#475467}.admin-confirm-cancel:hover{background:#f2f4f7}.admin-confirm-submit{border:1px solid #d92d20;background:#d92d20;color:#fff;box-shadow:0 3px 8px rgba(217,45,32,.2)}.admin-confirm-submit:hover{border-color:#b42318;background:#b42318;color:#fff}.admin-confirm-submit.is-neutral{border-color:#344054;background:#344054}.admin-confirm-submit.is-neutral:hover{border-color:#182230;background:#182230}.admin-confirm-content{animation:adminConfirmIn .18s ease-out}@keyframes adminConfirmIn{from{opacity:0;transform:translateY(8px) scale(.985)}to{opacity:1;transform:translateY(0) scale(1)}}@media(max-width:575px){.admin-confirm-dialog{margin:16px}.admin-confirm-header{padding:18px 18px 14px}.admin-confirm-body{padding:4px 18px 20px 18px}.admin-confirm-footer{padding:14px 18px 18px}.admin-confirm-cancel,.admin-confirm-submit{flex:1 1 50%!important;width:50%!important;min-width:0!important}}
{/literal}
</style>

{if !empty($adminHooks.admin_footer)}
<div class="admin-layout-hook admin-layout-hook--footer">
	{$adminHooks.admin_footer nofilter}
</div>
{/if}
<p class="admin-footer-meta text-center">
	<a href="https://frisay.com/" target="_blank" rel="noopener">FriSay E-Commerce</a>
	<span class="text-muted"> · {$fshopName|escape} v{$fshopVersion|escape}</span>
</p>
<script src="{$adminJsDir|default:''}popper.min.js"></script>
<script src="{$adminJsDir|default:''}bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lucide@0.469.0/dist/umd/lucide.min.js"></script>
<script>
window.__adminI18n = {$adminI18n|@json_encode nofilter};
window.__adminCmdk = {
	adminUrl: {$adminUrl|@json_encode nofilter},
	storeUrl: {$domain|@json_encode nofilter}
};
window.__adminLivePoll = {
	url: "{$adminUrl|escape:'javascript'}live-poll",
	adminUrl: {$adminUrl|@json_encode nofilter},
	intervalMs: 30000
};
window.__adminFavorites = {
	url: {$adminFavoritesApiUrl|@json_encode nofilter},
	token: {$adminToken|@json_encode nofilter},
	keys: {$adminFavoriteKeys|@json_encode nofilter}
};
</script>
<script src="{$adminJsDir|default:''}admin.js?v={$smarty.now}"></script>
<script>
if (window.lucide) { window.lucide.createIcons(); }
</script>
<script src="{$adminJsDir|default:''}admin-command.js?v={$smarty.now}"></script>
{if $marketplaceAdminAssets.js|@count}
{foreach $marketplaceAdminAssets.js as $marketplaceJs}
<script src="{$marketplaceJs}?v={$smarty.now}"></script>
{/foreach}
{/if}
{if $moduleAdminAssets.js|@count}
{foreach $moduleAdminAssets.js as $moduleJs}
<script src="{$moduleJs}?v={$smarty.now}"></script>
{/foreach}
{/if}
{if $adminUseCharts}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="{$adminJsDir|default:''}admin-charts.js?v={$smarty.now}"></script>
{/if}
{if $adminUseOrderStatus}
<script>
window.__adminOrderStatus = {
	apiUrl: {$orderStatusApiUrl|@json_encode nofilter},
	token: {$adminToken|@json_encode nofilter}
};
</script>
<script src="{$adminJsDir|default:''}order-status.js?v={$smarty.now}"></script>
{/if}
{if $adminUseEditor}
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.5/tinymce.min.js"></script>
<script src="{$adminJsDir|default:''}admin-editor.js?v={$smarty.now}"></script>
{/if}
<script type="text/javascript">
if (window.history.replaceState) { window.history.replaceState(null, null, window.location.href); }
</script>
</body>
</html>
