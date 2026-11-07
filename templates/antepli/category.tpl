{* Kategori (ve marka) sayfası: tek büyük vitrin, çok raflı *}
{assign var=cv value=$antepli|default:[]}
{assign var=_catImg value=''}
{if isset($category) && $category}
	{assign var=_cid value=$category.id_category}
	{assign var=_st value=$cv.styles[$_cid]|default:[]}
	{include file='./partials/pal.tpl' id=$_cid idx=$_cid assign=_pal}
	{assign var=_tagline value=$_st.tagline|default:$category.meta_description|default:''}
	{assign var=_ribbon value=$_st.ribbon|default:''}
	{assign var=_catImg value=Category::getImageUrl((int) $_cid)}
	{assign var=_kicker value='Vitrin'}
{else}
	{assign var=_pal value='karamel'}
	{assign var=_tagline value=''}
	{assign var=_ribbon value=''}
	{assign var=_kicker value='Marka'}
{/if}
{if $_catImg == '' && $products|@count}
	{foreach $products as $p}
		{assign var=_u value=$p.image_url|default:''}
		{if $_u != '' && ($_u|replace:'favicon.png':'') == $_u}{assign var=_catImg value=$_u}{break}{/if}
	{/foreach}
{/if}
{assign var=_hasFilters value=isset($catalogFilter)}

<div class="cv-catpage">
	<section class="cv-cat-hero cv-pal--{$_pal}">
		<div class="cv-container cv-cat-hero__inner">
			<div class="cv-cat-hero__copy">
				{include file='theme4/partials/breadcrumb.tpl'}
				<p class="cv-kicker cv-anim" style="--d:.05s"><span class="cv-rule" aria-hidden="true"></span>{$_kicker}{if $_ribbon != ''} · {$_ribbon|escape}{/if}</p>
				<h1 class="cv-cat-hero__title" data-cv-split>{$listTitle|escape}</h1>
				{if $_tagline != ''}<p class="cv-cat-hero__tagline cv-anim" style="--d:.45s">{$_tagline|escape}</p>{/if}
				<p class="cv-cat-hero__count cv-anim" style="--d:.55s">{$productCount|default:($products|@count)} ürün</p>
			</div>
			{if $_catImg != ''}
			<figure class="cv-arch cv-arch--hero" style="--d:.2s"><img src="{$_catImg|escape}" alt="{$listTitle|escape}" fetchpriority="high" decoding="async"></figure>
			{/if}
		</div>
		{if $filterSubcategories|default:[]|@count > 0}
		<div class="cv-container">
			<div class="cv-subcats">
				<a href="{$filterSubcategoryAllUrl|escape}" class="cv-subcats__chip{if !$catalogFilter.subCategoryId} is-active{/if}">Tümü</a>
				{foreach $filterSubcategories as $sub}
				<a href="{$sub.filter_url|escape}" class="cv-subcats__chip{if $catalogFilter.subCategoryId == $sub.id_category} is-active{/if}">{$sub.category_name|escape}</a>
				{/foreach}
			</div>
		</div>
		{/if}
	</section>

	<div class="cv-container cv-catpage__body">
		<div class="cv-toolbar">
			{include file='./partials/toolbar.tpl'}
			{if $_hasFilters}
			<button type="button" class="cv-btn cv-btn--line cv-btn--sm" data-bs-toggle="offcanvas" data-bs-target="#cvFilters" aria-controls="cvFilters">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
				Filtrele{if $catalogFilter.hasActive} <span class="cv-dot" aria-hidden="true"></span>{/if}
			</button>
			{/if}
		</div>
		{if $_hasFilters && $catalogFilter.hasActive}
		<p class="cv-toolbar__clear"><a href="{$catalogFilter.clearUrl|escape}">Filtreleri temizle</a></p>
		{/if}

		{if !$products|@count}
			<div class="cv-empty" data-cv-reveal>
				<p>{$emptyMessage|default:'Bu vitrin şu an boş.'|escape}</p>
				<a href="{$domain}" class="cv-btn cv-btn--primary">{'Home Page'|translate}</a>
			</div>
		{else}
			{include file='./partials/vitrin.tpl' products=$products title=$listTitle url='' pal=$_pal vid='vitrin-liste' mode='grid' idx=0 showHead=false eagerFirst=true}
			{include file='theme4/plugin/pagination.tpl'}
		{/if}

		{if $menuCategories|@count > 1}
		<nav class="cv-other" data-cv-reveal aria-label="Diğer vitrinler">
			<p class="cv-kicker"><span class="cv-rule" aria-hidden="true"></span>Keşfetmeye devam</p>
			<h2 class="cv-other__title">Diğer vitrinler</h2>
			<ul class="cv-other__list">
				{foreach $menuCategories as $oc name=oth}
					{if isset($category) && $oc.id_category == $category.id_category}{continue}{/if}
					{include file='./partials/pal.tpl' id=$oc.id_category idx=$smarty.foreach.oth.index assign=_opal}
					<li><a href="{$domain}{$oc.category_link|escape}" class="cv-other__item cv-pal--{$_opal}"><span>{$oc.category_name|escape}</span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></li>
				{/foreach}
			</ul>
		</nav>
		{/if}
	</div>
</div>

{if $_hasFilters}
<div class="offcanvas offcanvas-end cv-filters" tabindex="-1" id="cvFilters" aria-labelledby="cvFiltersTitle">
	<div class="offcanvas-header">
		<h2 class="offcanvas-title" id="cvFiltersTitle">Filtreler</h2>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{'Close'|translate}"></button>
	</div>
	<div class="offcanvas-body">
		{include file='theme4/plugin/catalogFilters.tpl'}
	</div>
</div>
{/if}
