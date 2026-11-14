{assign var=cv value=$antepli|default:[]}
{* Hiçbir kategori "Ana sayfada göster" ile işaretli değilse ürünlü tüm kategoriler vitrin olur *}
{if isset($antepli) && (!isset($categoryBlocks) || !$categoryBlocks|@count)}
	{assign var=categoryBlocks value=AntepliVitrinService::fallbackHomeBlocks()}
{/if}
{* Hero görselleri: admin'den girilenler, yoksa her vitrinin ilk ürün fotoğrafı *}
{assign var=_heroImgs value=$cv.hero_images|default:[]}
{* Görseller vitrinlerden geliyorsa her birinin altına vitrin adı yazılır *}
{assign var=_heroCaps value=[]}
{assign var=_heroOwn value=$_heroImgs|@count}
{if $_heroImgs|@count < 3 && isset($categoryBlocks)}
	{foreach $categoryBlocks as $block}
		{if $_heroImgs|@count >= 3}{break}{/if}
		{foreach $block.products as $p}
			{assign var=_u value=$p.image_url|default:''}
			{if $_u != '' && ($_u|replace:'favicon.png':'') == $_u}
				{append var='_heroImgs' value=$_u}
				{append var='_heroCaps' value=['t'=>$block.category.category_name,'u'=>$block.url]}
				{break}
			{/if}
		{/foreach}
	{/foreach}
{/if}

<div class="cv-home">

	{* ================= HERO ================= *}
	<section class="cv-hero" data-cv-hero>
		<div class="cv-container">
			<div class="cv-bento">
				<div class="cv-tile cv-tile--line cv-bento__main">
					<svg class="cv-bento__mark" viewBox="0 0 200 200" aria-hidden="true"><path d="M100 4 196 100 100 196 4 100Z"/><path d="M100 22 178 100 100 178 22 100Z"/></svg>
					<p class="cv-kicker cv-anim" style="--d:.05s"><span class="cv-rule" aria-hidden="true"></span>{$cv.hero_eyebrow|default:'Sıcak Fırından · Patisserie'|escape}</p>
					<h1 class="cv-hero__title" data-cv-split>{$cv.hero_title|default:'Antep\'in ustalığı, her gün taze.'|escape}</h1>
					<p class="cv-hero__text cv-anim" style="--d:.5s">{$cv.hero_text|default:'Fıstıklı kerebiçten köylü kahkesine, şekerli ve tuzlu kuru pastadan İzmir bombasına; ustalarımızın elinden, her gün sıcak fırından.'|escape}</p>
					<div class="cv-hero__cta cv-anim" style="--d:.6s">
						<a href="#cvVitrinler" class="cv-btn cv-btn--primary" data-cv-scroll>{$cv.hero_cta|default:'Vitrini keşfet'|escape}</a>
						{if $cv.hero_cta2|default:'' != ''}
						{assign var=_cta2 value=$cv.hero_cta2_url|default:'contact'}
						<a href="{if $_cta2|substr:0:4 == 'http'}{$_cta2|escape}{else}{$domain}{$_cta2|escape}{/if}" class="cv-hero__more">{$cv.hero_cta2|escape}</a>
						{/if}
					</div>
					{assign var=_badges value=$cv.badges|default:['Bol Antep fıstığı','Günlük üretim','Kiloyla & kutuyla']}
					{if $_badges|@count}
					<ul class="cv-hero__facts cv-anim" style="--d:.7s">
						{foreach $_badges as $b}<li>{$b|escape}</li>{/foreach}
					</ul>
					{/if}
				</div>

				{section name=hg loop=3}
					{assign var=_hi value=$smarty.section.hg.index}
					{assign var=_cap value=$_heroCaps[$_hi - $_heroOwn]|default:null}
					<figure class="cv-tile cv-bento__img cv-bento__img--{$_hi}" style="--d:{0.2 + $_hi * 0.12}s">
						{if isset($_heroImgs[$_hi])}
							<img src="{$_heroImgs[$_hi]|escape}" alt="{if $_cap}{$_cap.t|escape}{/if}" {if $_hi == 0}fetchpriority="high"{else}loading="lazy"{/if} decoding="async">
						{else}
							<span class="cv-bento__art">{include file='./partials/product-svg.tpl' variant=$_hi label=''}</span>
						{/if}
						{if $_cap}
						<a href="{$_cap.u|escape}" class="cv-bento__cap"><span>{$_cap.t|escape}</span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg></a>
						{/if}
					</figure>
				{/section}

				<div class="cv-tile cv-tile--accent cv-bento__seal">
					<span class="cv-seal" aria-hidden="true">
						<svg viewBox="0 0 120 120"><defs><path id="cvSealPath" d="M60 60m-46 0a46 46 0 1 1 92 0a46 46 0 1 1-92 0"/></defs><text><textPath href="#cvSealPath">SICAK FIRINDAN · ANTEPLİ USTALAR · </textPath></text></svg>
						<svg class="cv-seal__mark" viewBox="0 0 40 40"><path d="M20 3 37 20 20 37 3 20Z" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M20 11 29 20 20 29 11 20Z" fill="currentColor"/></svg>
					</span>
					<p class="cv-bento__seal-text"><strong>Her gün</strong> taze üretim</p>
				</div>

				<a href="#cvVitrinler" class="cv-tile cv-tile--line cv-bento__go" data-cv-scroll>
					<span class="cv-bento__go-num">{if isset($categoryBlocks)}{$categoryBlocks|@count}{else}0{/if}</span>
					<span class="cv-bento__go-label">vitrin, tepsi tepsi</span>
					<span class="cv-bento__go-ico" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M6 13l6 6 6-6"/></svg></span>
				</a>
			</div>
		</div>
	</section>

	{* ================= ÜRÜN BANDI ================= *}
	{if $cv.show_marquee|default:true}
	{assign var=_mq value=$cv.marquee|default:['Fıstıklı Kerebiç','Köylü Kahkesi','Çubuk Kahke','Tahinli','Tarsus Çöreği','Kömbe','İzmir Bombası','Şekerli Kuru Pasta','Tuzlu Kuru Pasta']}
	<div class="cv-marquee" aria-hidden="true">
		<div class="cv-marquee__track">
			{for $rep=1 to 3}
			{foreach $_mq as $word}<span>{$word|escape}</span><i></i>{/foreach}
			{/for}
		</div>
	</div>
	{/if}

	{if $cv.show_home_slider|default:true && $hooks.home_slider}
	<div class="cv-container cv-home__hook cv-home__hook--slider" data-cv-reveal>{$hooks.home_slider nofilter}</div>
	{/if}

	{* ================= VİTRİNLER ================= *}
	<div class="cv-showcases" id="cvVitrinler">
		<div class="cv-container">
			<header class="cv-section-head" data-cv-reveal>
				<p class="cv-kicker"><span class="cv-rule" aria-hidden="true"></span>{$categoryBlocks|@count} vitrin</p>
				<h2 class="cv-section-head__title">{$cv.showcase_title|default:'Vitrinlerimiz'|escape}</h2>
				<p class="cv-section-head__text">{$cv.showcase_text|default:'Mağazamızdaki gibi: her çeşit kendi vitrininde, tepsi tepsi.'|escape}</p>
			</header>
		</div>

		{if $categoryBlocks|@count > 1}
		<nav class="cv-picker" data-cv-picker aria-label="Vitrin seçici">
			<div class="cv-picker__track" data-cv-picker-track>
				<span class="cv-picker__indicator" data-cv-picker-indicator aria-hidden="true"></span>
				{foreach $categoryBlocks as $block name=pick}
					{assign var=_cid value=$block.category.id_category}
					<a href="#vitrin-{$_cid}" class="cv-picker__pill" data-cv-pill="vitrin-{$_cid}">{$block.category.category_name|escape}</a>
				{/foreach}
			</div>
		</nav>
		{/if}

		<div class="cv-container cv-showcases__list">
			{foreach $categoryBlocks as $block name=vit}
				{assign var=_cid value=$block.category.id_category}
				{assign var=_st value=$cv.styles[$_cid]|default:[]}
				{include file='./partials/pal.tpl' id=$_cid idx=$smarty.foreach.vit.index assign=_pal}
				{include file='./partials/vitrin.tpl'
					products=$block.products
					title=$block.category.category_name
					url=$block.url
					pal=$_pal
					tagline=$_st.tagline|default:''
					ribbon=$_st.ribbon|default:''
					vid="vitrin-{$_cid}"
					mode='shelf'
					idx=$smarty.foreach.vit.index
					eagerFirst=false}
			{foreachelse}
				{if isset($featuredProducts) && $featuredProducts|@count > 0}
					{include file='./partials/vitrin.tpl' products=$featuredProducts title='Günün vitrini' url=$domain|cat:'special' pal='susam' tagline='Kategorilerinizi “Ana sayfada göster” olarak işaretleyince her biri ayrı vitrin olur.' vid='vitrin-gunun' mode='shelf' idx=0}
				{else}
					<div class="cv-empty" data-cv-reveal>
						<p>Vitrinler hazırlanıyor. Yönetim panelinden kategorileri “Ana sayfada göster” olarak işaretleyin.</p>
					</div>
				{/if}
			{/foreach}

			{if isset($newProducts) && $newProducts|@count > 0 && $categoryBlocks|@count > 0}
				{include file='./partials/vitrin.tpl' products=$newProducts title='Fırından yeni çıktı' url='' pal='tereyagi' tagline='Tepsiden vitrine yeni gelenler' vid='vitrin-yeni' mode='shelf' idx=$categoryBlocks|@count}
			{/if}

			{if isset($dealProducts) && $dealProducts|@count > 0}
				{include file='./partials/vitrin.tpl' products=$dealProducts title='Günün fırsatları' url=$domain|cat:'special' pal='bayram' tagline='İndirimli lezzetler tükenmeden' vid='vitrin-firsat' mode='shelf' idx=$categoryBlocks|@count + 1}
			{/if}
		</div>
	</div>

	{if $hooks.home_promo_slider}
	<div class="cv-container cv-home__hook" data-cv-reveal>{$hooks.home_promo_slider nofilter}</div>
	{/if}

	{* ================= USTALIK ================= *}
	<section class="cv-craft">
		<div class="cv-container">
			<header class="cv-section-head" data-cv-reveal>
				<p class="cv-kicker"><span class="cv-rule" aria-hidden="true"></span>Sipariş</p>
				<h2 class="cv-section-head__title">Fırından kapınıza</h2>
			</header>
			<ol class="cv-craft__list">
				<li class="cv-craft__item" data-cv-reveal style="--i:0">
					<span class="cv-craft__num">01</span>
					<svg class="cv-craft__icon" viewBox="0 0 48 48" aria-hidden="true"><path d="M6 30h36M10 30c0-9 6-16 14-16s14 7 14 16"/><path d="M18 30c0-5 2.5-9 6-11M30 30c0-5-2.5-9-6-11M24 19v11"/><path d="M8 36h32"/></svg>
					<h3>Vitrinden seçin</h3>
					<p>Kerebiç, kahke, kuru pasta… Rafları gezin, dilediğinizi sepete ekleyin.</p>
				</li>
				<li class="cv-craft__item" data-cv-reveal style="--i:1">
					<span class="cv-craft__num">02</span>
					<svg class="cv-craft__icon" viewBox="0 0 48 48" aria-hidden="true"><path d="M8 18h32v22H8z"/><path d="M8 18l4-8h24l4 8M24 18v22M18 26h12"/></svg>
					<h3>Özenle kutulanır</h3>
					<p>Siparişiniz sıcak fırından alınır, ezilmeden özel kutusuna yerleştirilir.</p>
				</li>
				<li class="cv-craft__item" data-cv-reveal style="--i:2">
					<span class="cv-craft__num">03</span>
					<svg class="cv-craft__icon" viewBox="0 0 48 48" aria-hidden="true"><path d="M4 14h24v18H4zM28 20h8l6 6v6H28z"/><circle cx="12" cy="35" r="4"/><circle cx="35" cy="35" r="4"/></svg>
					<h3>Taze teslim</h3>
					<p>Paketiniz aynı gün yola çıkar, tazeliğinden ödün vermeden kapınıza gelir.</p>
				</li>
			</ol>
		</div>
	</section>

	{if $hooks.home_text|default:''}
	<div class="cv-container cv-home__hook" data-cv-reveal>{$hooks.home_text nofilter}</div>
	{/if}
	{if $hooks.home_bottom}
	<div class="cv-container cv-home__hook" data-cv-reveal>{$hooks.home_bottom nofilter}</div>
	{/if}

	{* ================= TOPLU SİPARİŞ ================= *}
	<section class="cv-cta" data-cv-reveal>
		<div class="cv-container cv-cta__inner">
			<div class="cv-cta__copy">
				<p class="cv-kicker"><span class="cv-rule" aria-hidden="true"></span>Kurumsal &amp; özel gün</p>
				<h2>Bayram, misafir ve kurumsal <em>ikramlarınız</em> için</h2>
				<p>Kiloyla kuru pasta, karışık bayram paketleri ve firmanıza özel ikram kutuları hazırlıyoruz.</p>
			</div>
			<div class="cv-cta__actions">
				<a href="{$domain}contact" class="cv-btn cv-btn--light">Teklif isteyin</a>
				{if $cv.whatsapp_link|default:'' != ''}
				<a href="{$cv.whatsapp_link|escape}" class="cv-btn cv-btn--ghost-light" target="_blank" rel="noopener">WhatsApp</a>
				{/if}
			</div>
		</div>
	</section>
</div>
