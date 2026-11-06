{* Arama / favoriler / genel liste — cupcake vitrin ızgarası *}
<div class="cv-listpage">
	{if $listTitle|default:'' != ''}
	<header class="cv-section-head cv-section-head--left">
		<h1 class="cv-section-head__title" data-cv-split>{$listTitle|escape}</h1>
	</header>
	{/if}
	<div class="cv-toolbar">{include file='./partials/toolbar.tpl'}</div>
	{if !$products|@count}
		<div class="cv-empty" data-cv-reveal>
			<span aria-hidden="true">🧁</span>
			<p>{$emptyMessage|default:'Ürün bulunamadı.'|escape}</p>
			<a href="{$domain}" class="cv-btn cv-btn--primary">{'Home Page'|translate}</a>
		</div>
	{else}
		{include file='./productGrid.tpl' products=$products}
		{include file='theme4/plugin/pagination.tpl'}
	{/if}
</div>
