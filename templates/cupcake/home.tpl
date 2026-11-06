{assign var=cv value=$cupcake|default:[]}
<div class="cv-home">

	{* ================= HERO ================= *}
	<section class="cv-hero" data-cv-hero>
		<span class="cv-hero__blob cv-hero__blob--a" aria-hidden="true"></span>
		<span class="cv-hero__blob cv-hero__blob--b" aria-hidden="true"></span>
		<span class="cv-hero__blob cv-hero__blob--c" aria-hidden="true"></span>
		{if $cv.show_sprinkles|default:true}
		<div class="cv-hero__sprinkles" aria-hidden="true">
			{for $n=0 to 21}
			<i class="cv-sprinkle" style="--x:{($n * 37 + 11) % 100}%;--y:{($n * 53 + 7) % 92}%;--r:{($n * 47) % 180}deg;--d:{($n % 5) + 1};--c:{$n % 6};--t:{($n % 7) * 0.6}s"></i>
			{/for}
		</div>
		{/if}

		<div class="cv-container cv-hero__inner">
			<div class="cv-hero__copy">
				<p class="cv-hero__eyebrow cv-anim" style="--d:.05s"><span class="cv-dot" aria-hidden="true"></span>{$cv.hero_eyebrow|default:'Her sabah taze, el yapımı'|escape}</p>
				<h1 class="cv-hero__title" data-cv-split>{$cv.hero_title|default:'Mutluluğun en tatlı hali'|escape}</h1>
				<p class="cv-hero__text cv-anim" style="--d:.55s">{$cv.hero_text|default:'Islak keklerden çikolatalı cupcake\'lere kadar her lezzet kendi vitrininde. Kaydır, seç, sepete at.'|escape}</p>
				<div class="cv-hero__cta cv-anim" style="--d:.7s">
					<a href="#cvVitrinler" class="cv-btn cv-btn--primary cv-btn--lg" data-cv-scroll>
						{$cv.hero_cta|default:'Vitrine göz at'|escape}
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
					</a>
					{if $cv.hero_cta2|default:'' != ''}
					{assign var=_cta2 value=$cv.hero_cta2_url|default:'contact'}
					<a href="{if $_cta2|substr:0:4 == 'http'}{$_cta2|escape}{else}{$domain}{$_cta2|escape}{/if}" class="cv-btn cv-btn--ghost cv-btn--lg">{$cv.hero_cta2|escape}</a>
					{/if}
				</div>
				{assign var=_badges value=$cv.badges|default:['%100 tereyağı','Katkısız','Aynı gün teslimat']}
				{if $_badges|@count}
				<ul class="cv-hero__badges">
					{foreach $_badges as $b name=hb}
					<li class="cv-anim" style="--d:{0.85 + $smarty.foreach.hb.index * 0.12}s"><span aria-hidden="true">✓</span>{$b|escape}</li>
					{/foreach}
				</ul>
				{/if}
			</div>

			<div class="cv-hero__visual" aria-hidden="true" data-cv-parallax>
				<span class="cv-hero__halo"></span>
				<span class="cv-orbit cv-orbit--1">🍓</span>
				<span class="cv-orbit cv-orbit--2">🍫</span>
				<span class="cv-orbit cv-orbit--3">🍒</span>
				<span class="cv-orbit cv-orbit--4">✨</span>
				<svg class="cv-hero__art" viewBox="0 0 400 440">
					<defs>
						<linearGradient id="cvWrapG" x1="0" x2="1">
							<stop offset="0" style="stop-color:var(--cv-primary-dark,#c43d63)"/>
							<stop offset=".5" style="stop-color:var(--cv-primary,#e2557b)"/>
							<stop offset="1" style="stop-color:var(--cv-primary-dark,#c43d63)"/>
						</linearGradient>
						<linearGradient id="cvCreamG" x1="0" y1="0" x2="0" y2="1">
							<stop offset="0" stop-color="#fff6fa"/>
							<stop offset="1" stop-color="#ffc9d8"/>
						</linearGradient>
						<linearGradient id="cvCreamG2" x1="0" y1="0" x2="0" y2="1">
							<stop offset="0" stop-color="#ffe1ea"/>
							<stop offset="1" stop-color="#ffb3c8"/>
						</linearGradient>
						<radialGradient id="cvCherryG" cx=".35" cy=".35" r=".7">
							<stop offset="0" stop-color="#ff6b81"/>
							<stop offset="1" stop-color="#b3122e"/>
						</radialGradient>
						<linearGradient id="cvDomeG" x1="0" x2="1">
							<stop offset="0" stop-color="#fff" stop-opacity=".55"/>
							<stop offset=".35" stop-color="#fff" stop-opacity=".08"/>
							<stop offset="1" stop-color="#fff" stop-opacity=".35"/>
						</linearGradient>
					</defs>
					<g class="cv-art__stand">
						<ellipse cx="200" cy="392" rx="70" ry="12" fill="#e9d3c4"/>
						<rect x="186" y="352" width="28" height="42" rx="8" fill="#f3e3d7"/>
						<ellipse cx="200" cy="350" rx="160" ry="22" fill="#fff"/>
						<ellipse cx="200" cy="346" rx="160" ry="18" fill="#fbf2ec"/>
					</g>
					<g class="cv-art__cake">
						<ellipse cx="200" cy="344" rx="84" ry="10" fill="rgba(59,36,24,.18)"/>
						<g class="cv-art__wrap">
							<path d="M112 238h176l-22 104H134z" fill="url(#cvWrapG)"/>
							<path d="M138 238l10 104M164 238l5 104M200 238v104M236 238l-5 104M262 238l-10 104" stroke="rgba(255,255,255,.35)" stroke-width="7"/>
						</g>
						<ellipse class="cv-art__sponge" cx="200" cy="238" rx="92" ry="18" fill="#7a4a2f"/>
						<path class="cv-art__cream cv-art__cream--1" d="M100 236c-14-22 6-44 30-40 18 14 42 16 70 16s52-2 70-16c24-4 44 18 30 40z" fill="url(#cvCreamG2)"/>
						<path class="cv-art__cream cv-art__cream--2" d="M116 200c-10-22 10-40 34-34 14 10 32 12 50 12s36-2 50-12c24-6 44 12 34 34z" fill="url(#cvCreamG)"/>
						<path class="cv-art__cream cv-art__cream--3" d="M134 166c-6-20 12-34 32-28 10 8 22 10 34 10s24-2 34-10c20-6 38 8 32 28z" fill="url(#cvCreamG2)"/>
						<path class="cv-art__cream cv-art__cream--4" d="M156 134c-2-22 18-34 44-34s46 12 44 34c-12 6-28 8-44 8s-32-2-44-8z" fill="url(#cvCreamG)"/>
						<path class="cv-art__cream cv-art__cream--5" d="M178 104c0-18 10-30 22-30s22 12 22 30c-6 4-14 6-22 6s-16-2-22-6z" fill="url(#cvCreamG2)"/>
						<g class="cv-art__sprinkles">
							<rect x="130" y="210" width="16" height="5" rx="2.5" fill="#ffd166" transform="rotate(-25 138 212)"/>
							<rect x="250" y="214" width="16" height="5" rx="2.5" fill="#06d6a0" transform="rotate(30 258 216)"/>
							<rect x="168" y="176" width="16" height="5" rx="2.5" fill="#118ab2" transform="rotate(15 176 178)"/>
							<rect x="222" y="182" width="16" height="5" rx="2.5" fill="#8338ec" transform="rotate(-40 230 184)"/>
							<rect x="196" y="140" width="16" height="5" rx="2.5" fill="#ffd166" transform="rotate(60 204 142)"/>
							<rect x="150" y="146" width="16" height="5" rx="2.5" fill="#ef476f" transform="rotate(-10 158 148)"/>
							<rect x="236" y="148" width="16" height="5" rx="2.5" fill="#118ab2" transform="rotate(20 244 150)"/>
							<rect x="186" y="214" width="16" height="5" rx="2.5" fill="#ef476f" transform="rotate(70 194 216)"/>
						</g>
						<g class="cv-art__cherry">
							<path d="M204 56c6-18 18-26 34-30" stroke="#5b8a2f" stroke-width="5" fill="none" stroke-linecap="round"/>
							<circle cx="200" cy="66" r="20" fill="url(#cvCherryG)"/>
							<ellipse cx="193" cy="58" rx="6" ry="4" fill="#fff" opacity=".7"/>
						</g>
					</g>
					<g class="cv-art__dome">
						<path d="M46 346C46 140 116 30 200 30s154 110 154 316" fill="url(#cvDomeG)" stroke="rgba(255,255,255,.9)" stroke-width="3"/>
						<path d="M84 300C84 180 124 92 172 64" stroke="#fff" stroke-width="8" stroke-linecap="round" fill="none" opacity=".75"/>
						<circle cx="200" cy="20" r="13" fill="#fff" stroke="#f1e2d6" stroke-width="3"/>
					</g>
				</svg>
				<span class="cv-hero__stamp">
					<svg viewBox="0 0 120 120" aria-hidden="true"><defs><path id="cvStampPath" d="M60 60m-44 0a44 44 0 1 1 88 0a44 44 0 1 1-88 0"/></defs><text><textPath href="#cvStampPath">TAZE • EL YAPIMI • GÜNLÜK • </textPath></text></svg>
					<b>🧁</b>
				</span>
			</div>
		</div>

		<a href="#cvVitrinler" class="cv-hero__scroll" data-cv-scroll aria-label="Vitrinlere kaydır"><span></span></a>
	</section>

	{* ================= KAYAN BANT ================= *}
	{if $cv.show_marquee|default:true}
	{assign var=_mq value=$cv.marquee|default:['Islak Kek','Çikolatalı Cupcake','Red Velvet','Frambuazlı','Karamelli','Fıstıklı','Doğum Günü Kutuları']}
	<div class="cv-marquee" aria-hidden="true">
		<div class="cv-marquee__track">
			{for $rep=1 to 3}
			{foreach $_mq as $word}<span>{$word|escape}</span><b>🧁</b>{/foreach}
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
				<span class="cv-section-head__kicker">🧁 {$categoryBlocks|@count} vitrin</span>
				<h2 class="cv-section-head__title">{$cv.showcase_title|default:'Vitrinlerimiz'|escape}</h2>
				<p class="cv-section-head__text">{$cv.showcase_text|default:'Her kategori ayrı bir vitrin. Rafları kaydırarak tüm lezzetleri keşfet.'|escape}</p>
			</header>
		</div>

		{if $categoryBlocks|@count > 1}
		<nav class="cv-picker" data-cv-picker aria-label="Vitrin seçici">
			<div class="cv-picker__track" data-cv-picker-track>
				<span class="cv-picker__indicator" data-cv-picker-indicator aria-hidden="true"></span>
				{foreach $categoryBlocks as $block name=pick}
					{assign var=_cid value=$block.category.id_category}
					{assign var=_st value=$cv.styles[$_cid]|default:[]}
					{include file='./partials/pal.tpl' id=$_cid idx=$smarty.foreach.pick.index assign=_pal}
					<a href="#vitrin-{$_cid}" class="cv-picker__pill cv-pal--{$_pal}" data-cv-pill="vitrin-{$_cid}">
						<span aria-hidden="true">{$_st.emoji|default:'🧁'|escape}</span>{$block.category.category_name|escape}
					</a>
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
					emoji=$_st.emoji|default:'🧁'
					tagline=$_st.tagline|default:''
					ribbon=$_st.ribbon|default:''
					vid="vitrin-{$_cid}"
					mode='shelf'
					idx=$smarty.foreach.vit.index
					eagerFirst=$smarty.foreach.vit.first}
			{foreachelse}
				{if isset($featuredProducts) && $featuredProducts|@count > 0}
					{include file='./partials/vitrin.tpl' products=$featuredProducts title='Günün vitrini' url=$domain|cat:'special' pal='cilek' emoji='🧁' tagline='Kategorilerinizi “Ana sayfada göster” olarak işaretleyince her biri ayrı vitrin olur.' vid='vitrin-gunun' mode='shelf' idx=0 eagerFirst=true}
				{else}
					<div class="cv-empty" data-cv-reveal>
						<span aria-hidden="true">🧁</span>
						<p>Vitrinler hazırlanıyor. Yönetim panelinden kategorileri “Ana sayfada göster” olarak işaretleyin.</p>
					</div>
				{/if}
			{/foreach}

			{if isset($newProducts) && $newProducts|@count > 0 && $categoryBlocks|@count > 0}
				{include file='./partials/vitrin.tpl' products=$newProducts title='Fırından yeni çıktı' url='' pal='vanilya' emoji='🔥' tagline='Vitrine en son giren lezzetler' vid='vitrin-yeni' mode='shelf' idx=$categoryBlocks|@count}
			{/if}

			{if isset($dealProducts) && $dealProducts|@count > 0}
				{include file='./partials/vitrin.tpl' products=$dealProducts title='Günün fırsatları' url=$domain|cat:'special' pal='limon' emoji='🏷️' tagline='İndirimli tatlılar tükenmeden' ribbon='Sınırlı' vid='vitrin-firsat' mode='shelf' idx=$categoryBlocks|@count + 1}
			{/if}
		</div>
	</div>

	{if $hooks.home_promo_slider}
	<div class="cv-container cv-home__hook" data-cv-reveal>{$hooks.home_promo_slider nofilter}</div>
	{/if}

	{* ================= NASIL SİPARİŞ ================= *}
	<section class="cv-steps">
		<div class="cv-container">
			<header class="cv-section-head" data-cv-reveal>
				<span class="cv-section-head__kicker">3 adımda mutluluk</span>
				<h2 class="cv-section-head__title">Nasıl sipariş verilir?</h2>
			</header>
			<ol class="cv-steps__list">
				<li class="cv-step" data-cv-reveal style="--i:0">
					<span class="cv-step__icon" aria-hidden="true">🧁</span>
					<strong>Vitrinden seç</strong>
					<p>Rafları kaydır, canının çektiği lezzeti sepete at.</p>
				</li>
				<li class="cv-step" data-cv-reveal style="--i:1">
					<span class="cv-step__icon" aria-hidden="true">🎀</span>
					<strong>Özenle kutulayalım</strong>
					<p>Siparişin aynı gün pişirilir ve özel kutusuna yerleştirilir.</p>
				</li>
				<li class="cv-step" data-cv-reveal style="--i:2">
					<span class="cv-step__icon" aria-hidden="true">🛵</span>
					<strong>Kapına gelsin</strong>
					<p>Soğuk zincirle taze taze teslim edilir. Afiyet olsun!</p>
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

	{* ================= ÖZEL SİPARİŞ ================= *}
	<section class="cv-container">
		<div class="cv-cta" data-cv-reveal>
			<div class="cv-cta__copy">
				<h2>Doğum günü, nişan, baby shower…</h2>
				<p>Kutlamana özel renk, isim ve süslemeyle cupcake kutusu hazırlayalım.</p>
			</div>
			<div class="cv-cta__actions">
				{if $cv.whatsapp_link|default:'' != ''}
				<a href="{$cv.whatsapp_link|escape}" class="cv-btn cv-btn--wa cv-btn--lg" target="_blank" rel="noopener">WhatsApp'tan yaz</a>
				{/if}
				<a href="{$domain}contact" class="cv-btn cv-btn--light cv-btn--lg">Özel sipariş formu</a>
			</div>
			<span class="cv-cta__deco" aria-hidden="true">🎂</span>
		</div>
	</section>
</div>
