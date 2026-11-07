{assign var=cv value=$antepli|default:[]}
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
				<p class="cv-hero__eyebrow cv-anim" style="--d:.05s"><span class="cv-dot" aria-hidden="true"></span>{$cv.hero_eyebrow|default:'Sıcak Fırından · Patisserie'|escape}</p>
				<h1 class="cv-hero__title" data-cv-split>{$cv.hero_title|default:'Antep\'in ustalığı her gün taze'|escape}</h1>
				<p class="cv-hero__text cv-anim" style="--d:.55s">{$cv.hero_text|default:'Fıstıklı kerebiçten köylü kahkesine, şekerli ve tuzlu kuru pastadan İzmir bombasına; her lezzet kendi vitrininde. Kaydır, seç, sepete at.'|escape}</p>
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
				{assign var=_badges value=$cv.badges|default:['Bol Antep fıstığı','Günlük üretim','Kiloyla & kutuyla']}
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
				<span class="cv-orbit cv-orbit--1">🌾</span>
				<span class="cv-orbit cv-orbit--2">🍪</span>
				<span class="cv-orbit cv-orbit--3">🫖</span>
				<span class="cv-orbit cv-orbit--4">✨</span>
				<svg class="cv-hero__art" viewBox="0 0 400 440">
					<defs>
						<linearGradient id="auTray" x1="0" y1="0" x2="0" y2="1">
							<stop offset="0" stop-color="#ffffff"/>
							<stop offset=".45" stop-color="#e3e7ea"/>
							<stop offset="1" stop-color="#9ea5ab"/>
						</linearGradient>
						<radialGradient id="auDough" cx=".4" cy=".3" r=".8">
							<stop offset="0" stop-color="#ffe9b8"/>
							<stop offset=".6" stop-color="#f2c77c"/>
							<stop offset="1" stop-color="#d79a4a"/>
						</radialGradient>
						<radialGradient id="auRing" cx=".4" cy=".3" r=".8">
							<stop offset="0" stop-color="#f3d08f"/>
							<stop offset="1" stop-color="#c4833c"/>
						</radialGradient>
						<linearGradient id="auStick" x1="0" y1="0" x2="0" y2="1">
							<stop offset="0" stop-color="#e8b86c"/>
							<stop offset="1" stop-color="#b8782f"/>
						</linearGradient>
						<symbol id="auKerebic" viewBox="0 0 64 46">
							<path d="M0 40C0 16 14 0 32 0s32 16 32 40c-9 4-20 6-32 6S9 44 0 40Z" fill="url(#auDough)"/>
							<path d="M13 41c0-14 4-27 12-35M23 44c0-16 3-30 9-39M32 45V5M41 44c0-16-3-30-9-39M51 41c0-14-4-27-12-35" stroke="#c98a3d" stroke-width="2.2" stroke-linecap="round" fill="none" opacity=".75"/>
							<ellipse cx="20" cy="12" rx="7" ry="3" fill="#fff" opacity=".45" transform="rotate(-25 20 12)"/>
						</symbol>
						<symbol id="auRingS" viewBox="0 0 64 48">
							<path fill-rule="evenodd" d="M32 0c18 0 32 10 32 24S50 48 32 48 0 38 0 24 14 0 32 0Zm0 15c-7 0-12 4-12 9s5 9 12 9 12-4 12-9-5-9-12-9Z" fill="url(#auRing)"/>
							<g fill="#fff6e0"><ellipse cx="10" cy="20" rx="2" ry="1.1" transform="rotate(-30 10 20)"/><ellipse cx="18" cy="9" rx="2" ry="1.1"/><ellipse cx="30" cy="5" rx="2" ry="1.1" transform="rotate(20 30 5)"/><ellipse cx="44" cy="7" rx="2" ry="1.1" transform="rotate(-20 44 7)"/><ellipse cx="54" cy="16" rx="2" ry="1.1" transform="rotate(40 54 16)"/><ellipse cx="56" cy="30" rx="2" ry="1.1"/><ellipse cx="46" cy="40" rx="2" ry="1.1" transform="rotate(-40 46 40)"/><ellipse cx="30" cy="43" rx="2" ry="1.1"/><ellipse cx="14" cy="36" rx="2" ry="1.1" transform="rotate(30 14 36)"/><ellipse cx="8" cy="28" rx="2" ry="1.1"/></g>
							<g fill="#2b1a10"><circle cx="24" cy="8" r="1.1"/><circle cx="50" cy="24" r="1.1"/><circle cx="22" cy="40" r="1.1"/><circle cx="38" cy="10" r="1.1"/></g>
						</symbol>
						<symbol id="auStickS" viewBox="0 0 120 24">
							<rect width="120" height="24" rx="12" fill="url(#auStick)"/>
							<g fill="#fff6e0"><ellipse cx="12" cy="8" rx="2" ry="1.1"/><ellipse cx="26" cy="14" rx="2" ry="1.1" transform="rotate(40 26 14)"/><ellipse cx="40" cy="7" rx="2" ry="1.1"/><ellipse cx="54" cy="13" rx="2" ry="1.1" transform="rotate(-30 54 13)"/><ellipse cx="68" cy="8" rx="2" ry="1.1"/><ellipse cx="82" cy="15" rx="2" ry="1.1" transform="rotate(30 82 15)"/><ellipse cx="96" cy="8" rx="2" ry="1.1"/><ellipse cx="108" cy="14" rx="2" ry="1.1"/></g>
							<rect x="6" y="3" width="108" height="5" rx="2.5" fill="#fff" opacity=".25"/>
						</symbol>
					</defs>

					{* Mağazadaki yeşil kemer ve kutu rafları *}
					<g class="cv-art__arch">
						<path d="M52 336V176a148 148 0 0 1 296 0v160z" fill="#a9cdb5"/>
						<path d="M74 336V180a126 126 0 0 1 252 0v156z" fill="#fff8ec"/>
						<g fill="#f3e6cf" stroke="#e2cfae" stroke-width="2">
							<rect x="104" y="150" width="56" height="30" rx="4"/><rect x="172" y="132" width="56" height="30" rx="4"/><rect x="240" y="150" width="56" height="30" rx="4"/>
							<rect x="96" y="196" width="56" height="30" rx="4"/><rect x="172" y="186" width="56" height="30" rx="4"/><rect x="248" y="196" width="56" height="30" rx="4"/>
						</g>
						<path d="M86 188h228M80 236h240" stroke="#c9a978" stroke-width="5" stroke-linecap="round"/>
					</g>

					<g class="cv-art__wheat cv-art__wheat--l">
						<path d="M64 336c6-60 18-110 40-150" stroke="#e2bd7f" stroke-width="4" fill="none" stroke-linecap="round"/>
						<g fill="#f0cf93"><ellipse cx="100" cy="196" rx="6" ry="13" transform="rotate(30 100 196)"/><ellipse cx="88" cy="214" rx="6" ry="13" transform="rotate(-30 88 214)"/><ellipse cx="92" cy="226" rx="6" ry="13" transform="rotate(35 92 226)"/><ellipse cx="80" cy="244" rx="6" ry="13" transform="rotate(-25 80 244)"/><ellipse cx="84" cy="258" rx="6" ry="13" transform="rotate(35 84 258)"/></g>
					</g>
					<g class="cv-art__wheat cv-art__wheat--r">
						<path d="M336 336c-6-60-18-110-40-150" stroke="#e2bd7f" stroke-width="4" fill="none" stroke-linecap="round"/>
						<g fill="#f0cf93"><ellipse cx="300" cy="196" rx="6" ry="13" transform="rotate(-30 300 196)"/><ellipse cx="312" cy="214" rx="6" ry="13" transform="rotate(30 312 214)"/><ellipse cx="308" cy="226" rx="6" ry="13" transform="rotate(-35 308 226)"/><ellipse cx="320" cy="244" rx="6" ry="13" transform="rotate(25 320 244)"/><ellipse cx="316" cy="258" rx="6" ry="13" transform="rotate(-35 316 258)"/></g>
					</g>

					{* Alüminyum tepsi *}
					<g class="cv-art__tray">
						<ellipse cx="200" cy="356" rx="176" ry="46" fill="rgba(59,31,15,.18)"/>
						<ellipse cx="200" cy="344" rx="172" ry="44" fill="url(#auTray)"/>
						<ellipse cx="200" cy="338" rx="156" ry="34" fill="#f4f6f7"/>
						<ellipse cx="200" cy="341" rx="150" ry="30" fill="#e6eaed"/>
					</g>

					{* Tepsiye düşen ürünler (arkadan öne) *}
					<g transform="translate(76 300) rotate(-6)"><g class="cv-art__drop cv-art__drop--1"><use href="#auStickS" width="110" height="22"/></g></g>
					<g transform="translate(214 296) rotate(7)"><g class="cv-art__drop cv-art__drop--2"><use href="#auStickS" width="110" height="22"/></g></g>
					<g transform="translate(70 312)"><g class="cv-art__drop cv-art__drop--3"><use href="#auRingS" width="74" height="56"/></g></g>
					<g transform="translate(258 310)"><g class="cv-art__drop cv-art__drop--4"><use href="#auRingS" width="74" height="56"/></g></g>
					<g transform="translate(146 270)"><g class="cv-art__drop cv-art__drop--5"><use href="#auKerebic" width="108" height="78"/></g></g>
					<g transform="translate(232 330)"><g class="cv-art__drop cv-art__drop--6"><use href="#auKerebic" width="70" height="50"/></g></g>
					<g transform="translate(118 332)">
						<g class="cv-art__drop cv-art__drop--7">
							<path d="M0 34C0 14 11 0 26 0c4 0 8 1 11 3L30 18l8 8-6 10C22 38 10 38 0 34Z" fill="url(#auDough)"/>
							<path d="M30 18l8 8-6 10c-4 1-6-2-6-6 0-5 2-9 4-12Z" fill="#8db043"/>
							<circle cx="44" cy="30" r="2.6" fill="#8db043"/><circle cx="50" cy="36" r="1.8" fill="#8db043"/>
						</g>
					</g>

					<g class="cv-art__steam" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round">
						<path class="cv-art__steam-1" d="M182 258c-10-12 10-22 0-34s10-22 0-34"/>
						<path class="cv-art__steam-2" d="M204 252c-10-12 10-22 0-34s10-22 0-34"/>
						<path class="cv-art__steam-3" d="M226 258c-10-12 10-22 0-34s10-22 0-34"/>
					</g>
				</svg>
				<span class="cv-hero__stamp">
					<svg viewBox="0 0 120 120" aria-hidden="true"><defs><path id="cvStampPath" d="M60 60m-44 0a44 44 0 1 1 88 0a44 44 0 1 1-88 0"/></defs><text><textPath href="#cvStampPath">SICAK FIRINDAN • HER GÜN TAZE • </textPath></text></svg>
					<b>🔥</b>
				</span>
			</div>
		</div>

		<a href="#cvVitrinler" class="cv-hero__scroll" data-cv-scroll aria-label="Vitrinlere kaydır"><span></span></a>
	</section>

	{* ================= KAYAN BANT ================= *}
	{if $cv.show_marquee|default:true}
	{assign var=_mq value=$cv.marquee|default:['Fıstıklı Kerebiç','Köylü Kahkesi','Çubuk Kahke','Tahinli','Tarsus Çöreği','Kömbe','İzmir Bombası','Şekerli Kuru Pasta','Tuzlu Kuru Pasta']}
	<div class="cv-marquee" aria-hidden="true">
		<div class="cv-marquee__track">
			{for $rep=1 to 3}
			{foreach $_mq as $word}<span>{$word|escape}</span><b>🍪</b>{/foreach}
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
				<span class="cv-section-head__kicker">🍪 {$categoryBlocks|@count} vitrin</span>
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
						<span aria-hidden="true">{$_st.emoji|default:'🍪'|escape}</span>{$block.category.category_name|escape}
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
					emoji=$_st.emoji|default:'🍪'
					tagline=$_st.tagline|default:''
					ribbon=$_st.ribbon|default:''
					vid="vitrin-{$_cid}"
					mode='shelf'
					idx=$smarty.foreach.vit.index
					eagerFirst=$smarty.foreach.vit.first}
			{foreachelse}
				{if isset($featuredProducts) && $featuredProducts|@count > 0}
					{include file='./partials/vitrin.tpl' products=$featuredProducts title='Günün vitrini' url=$domain|cat:'special' pal='susam' emoji='🍪' tagline='Kategorilerinizi “Ana sayfada göster” olarak işaretleyince her biri ayrı vitrin olur.' vid='vitrin-gunun' mode='shelf' idx=0 eagerFirst=true}
				{else}
					<div class="cv-empty" data-cv-reveal>
						<span aria-hidden="true">🍪</span>
						<p>Vitrinler hazırlanıyor. Yönetim panelinden kategorileri “Ana sayfada göster” olarak işaretleyin.</p>
					</div>
				{/if}
			{/foreach}

			{if isset($newProducts) && $newProducts|@count > 0 && $categoryBlocks|@count > 0}
				{include file='./partials/vitrin.tpl' products=$newProducts title='Fırından yeni çıktı' url='' pal='tereyagi' emoji='🔥' tagline='Tepsiden vitrine yeni gelenler' vid='vitrin-yeni' mode='shelf' idx=$categoryBlocks|@count}
			{/if}

			{if isset($dealProducts) && $dealProducts|@count > 0}
				{include file='./partials/vitrin.tpl' products=$dealProducts title='Günün fırsatları' url=$domain|cat:'special' pal='bayram' emoji='🏷️' tagline='İndirimli lezzetler tükenmeden' ribbon='Sınırlı' vid='vitrin-firsat' mode='shelf' idx=$categoryBlocks|@count + 1}
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
					<span class="cv-step__icon" aria-hidden="true">🍪</span>
					<strong>Vitrinden seç</strong>
					<p>Rafları kaydır; kerebiç, kahke, kuru pasta… canının çektiğini sepete at.</p>
				</li>
				<li class="cv-step" data-cv-reveal style="--i:1">
					<span class="cv-step__icon" aria-hidden="true">📦</span>
					<strong>Ustalarımız kutulasın</strong>
					<p>Siparişin sıcak fırından alınır, ezilmeden özel kutusuna yerleştirilir.</p>
				</li>
				<li class="cv-step" data-cv-reveal style="--i:2">
					<span class="cv-step__icon" aria-hidden="true">🛵</span>
					<strong>Kapına gelsin</strong>
					<p>Taze taze kapına teslim edilir. Afiyet olsun!</p>
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
				<h2>Bayram, nişan, toplantı, misafir…</h2>
				<p>Kiloyla kuru pasta, karışık bayram paketleri ve kurumsal ikram kutuları için bize yazın.</p>
			</div>
			<div class="cv-cta__actions">
				{if $cv.whatsapp_link|default:'' != ''}
				<a href="{$cv.whatsapp_link|escape}" class="cv-btn cv-btn--wa cv-btn--lg" target="_blank" rel="noopener">WhatsApp'tan yaz</a>
				{/if}
				<a href="{$domain}contact" class="cv-btn cv-btn--light cv-btn--lg">Özel sipariş formu</a>
			</div>
			<span class="cv-cta__deco" aria-hidden="true">🎁</span>
		</div>
	</section>
</div>
