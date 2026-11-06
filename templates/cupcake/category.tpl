{* Kategori (ve marka) sayfası: tek büyük vitrin dolabı, çok raflı *}
{assign var=cv value=$cupcake|default:[]}
{if isset($category) && $category}
	{assign var=_cid value=$category.id_category}
	{assign var=_st value=$cv.styles[$_cid]|default:[]}
	{include file='./partials/pal.tpl' id=$_cid idx=$_cid assign=_pal}
	{assign var=_emoji value=$_st.emoji|default:'🧁'}
	{assign var=_tagline value=$_st.tagline|default:$category.meta_description|default:''}
	{assign var=_ribbon value=$_st.ribbon|default:''}
{else}
	{assign var=_pal value='karamel'}
	{assign var=_emoji value='🎀'}
	{assign var=_tagline value=''}
	{assign var=_ribbon value=''}
{/if}
{assign var=_hasFilters value=isset($catalogFilter)}

<div class="cv-catpage">
	<section class="cv-cat-hero cv-pal--{$_pal}">
		<span class="cv-cat-hero__dots" aria-hidden="true"></span>
		<div class="cv-container">
			{include file='theme4/partials/breadcrumb.tpl'}
			<div class="cv-cat-hero__row">
				<span class="cv-cat-hero__emoji" aria-hidden="true">{$_emoji|escape}</span>
				<div>
					<h1 class="cv-cat-hero__title" data-cv-split>{$listTitle|escape}</h1>
					{if $_tagline != ''}<p class="cv-cat-hero__tagline cv-anim" style="--d:.35s">{$_tagline|escape}</p>{/if}
				</div>
			</div>
			{if $filterSubcategories|default:[]|@count > 0}
			<div class="cv-subcats cv-anim" style="--d:.45s">
				<a href="{$filterSubcategoryAllUrl|escape}" class="cv-subcats__chip{if !$catalogFilter.subCategoryId} is-active{/if}">Tümü</a>
				{foreach $filterSubcategories as $sub}
				<a href="{$sub.filter_url|escape}" class="cv-subcats__chip{if $catalogFilter.subCategoryId == $sub.id_category} is-active{/if}">{$sub.category_name|escape}</a>
				{/foreach}
			</div>
			{/if}
		</div>
	</section>

	<div class="cv-container cv-catpage__body">
		<div class="cv-toolbar">
			{include file='./partials/toolbar.tpl'}
			{if $_hasFilters}
			<button type="button" class="cv-btn cv-btn--ghost cv-btn--sm" data-bs-toggle="offcanvas" data-bs-target="#cvFilters" aria-controls="cvFilters">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
				Filtrele{if $catalogFilter.hasActive} <span class="cv-dot" aria-hidden="true"></span>{/if}
			</button>
			{/if}
		</div>
		{if $_hasFilters && $catalogFilter.hasActive}
		<p class="cv-toolbar__clear"><a href="{$catalogFilter.clearUrl|escape}">✕ Filtreleri temizle</a></p>
		{/if}

		{if !$products|@count}
			<div class="cv-empty" data-cv-reveal>
				<span aria-hidden="true">🧁</span>
				<p>{$emptyMessage|default:'Bu vitrin şu an boş.'|escape}</p>
				<a href="{$domain}" class="cv-btn cv-btn--primary">{'Home Page'|translate}</a>
			</div>
		{else}
			{capture assign=_countLabel}{$productCount|default:($products|@count)} lezzet bu vitrinde{/capture}
			{include file='./partials/vitrin.tpl' products=$products title=$_countLabel url='' pal=$_pal emoji=$_emoji tagline='' ribbon=$_ribbon vid='vitrin-liste' mode='grid' idx=0 headingTag='h2' eagerFirst=true}
			{include file='theme4/plugin/pagination.tpl'}
		{/if}

		{if $menuCategories|@count > 1}
		<nav class="cv-other" data-cv-reveal aria-label="Diğer vitrinler">
			<h2 class="cv-other__title">Diğer vitrinler</h2>
			<div class="cv-other__list">
				{foreach $menuCategories as $oc name=oth}
					{if isset($category) && $oc.id_category == $category.id_category}{continue}{/if}
					{assign var=_ost value=$cv.styles[$oc.id_category]|default:[]}
					{include file='./partials/pal.tpl' id=$oc.id_category idx=$smarty.foreach.oth.index assign=_opal}
					<a href="{$domain}{$oc.category_link|escape}" class="cv-chip-card cv-pal--{$_opal}" style="--i:{$smarty.foreach.oth.index}">
						<span class="cv-chip-card__emoji" aria-hidden="true">{$_ost.emoji|default:'🧁'|escape}</span>
						<span class="cv-chip-card__name">{$oc.category_name|escape}</span>
					</a>
				{/foreach}
			</div>
		</nav>
		{/if}
	</div>
</div>

{if $_hasFilters}
<div class="offcanvas offcanvas-end cv-filters" tabindex="-1" id="cvFilters" aria-labelledby="cvFiltersTitle">
	<div class="offcanvas-header">
		<h2 class="offcanvas-title h6" id="cvFiltersTitle">Filtreler</h2>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{'Close'|translate}"></button>
	</div>
	<div class="offcanvas-body">
		{include file='theme4/plugin/catalogFilters.tpl'}
	</div>
</div>
{/if}
