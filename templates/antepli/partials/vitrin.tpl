{*
	Cam vitrin dolabı.
	Parametreler:
	  products  — ürün listesi
	  title     — vitrin başlığı
	  url       — "tümünü gör" linki (boş olabilir)
	  pal       — palet anahtarı (cilek, cikolata, …)
	  emoji, tagline, ribbon — opsiyonel
	  vid       — HTML id (kategori pill'leri için)
	  mode      — 'shelf' (yatay kayan raf) | 'grid' (çok raflı)
	  idx       — sıra (animasyon gecikmesi)
	  headingTag — h1 / h2
*}
{assign var=_mode value=$mode|default:'shelf'}
{assign var=_h value=$headingTag|default:'h2'}
<section class="cv-vitrin cv-pal--{$pal|default:'cilek'|escape} cv-vitrin--{$_mode}" id="{$vid|default:''|escape}" data-cv-vitrin data-cv-reveal style="--vi:{$idx|default:0}">
	<div class="cv-vitrin__awning" aria-hidden="true">
		<span class="cv-vitrin__awning-cloth"></span>
		<span class="cv-vitrin__awning-scallop"></span>
	</div>

	<div class="cv-vitrin__sign">
		<span class="cv-vitrin__chain cv-vitrin__chain--l" aria-hidden="true"></span>
		<span class="cv-vitrin__chain cv-vitrin__chain--r" aria-hidden="true"></span>
		<span class="cv-vitrin__emoji" aria-hidden="true">{$emoji|default:'🍪'|escape}</span>
		<div class="cv-vitrin__sign-text">
			<{$_h} class="cv-vitrin__title">{$title|escape}</{$_h}>
			{if $tagline|default:'' != ''}<p class="cv-vitrin__tagline">{$tagline|escape}</p>{/if}
		</div>
		{if $ribbon|default:'' != ''}<span class="cv-vitrin__ribbon">{$ribbon|escape}</span>{/if}
	</div>

	<div class="cv-vitrin__case" data-cv-tilt>
		<span class="cv-vitrin__lights" aria-hidden="true"><i></i><i></i><i></i></span>
		<span class="cv-vitrin__glass" aria-hidden="true"></span>

		<div class="cv-shelf" data-cv-shelf>
			<div class="cv-shelf__track" data-cv-track>
				{foreach $products as $p name=vitrinProducts}
					{include file='./product-card.tpl' p=$p i=$smarty.foreach.vitrinProducts.index eager=($eagerFirst|default:false && $smarty.foreach.vitrinProducts.first)}
				{/foreach}
				{if $_mode == 'shelf' && $url|default:'' != ''}
				<a href="{$url|escape}" class="cv-shelf__more" style="--i:{$products|@count}">
					<span class="cv-shelf__more-ring" aria-hidden="true">→</span>
					<span>Vitrinin<br>tamamı</span>
				</a>
				{/if}
			</div>
		</div>

		{if $_mode == 'shelf'}
		<button type="button" class="cv-shelf__nav cv-shelf__nav--prev" data-cv-prev aria-label="{'Previous'|translate}">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
		</button>
		<button type="button" class="cv-shelf__nav cv-shelf__nav--next" data-cv-next aria-label="{'Next'|translate}">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
		</button>
		{/if}
	</div>
	<div class="cv-vitrin__counter" aria-hidden="true"></div>

	{if $url|default:'' != '' && $_mode == 'shelf'}
	<div class="cv-vitrin__foot">
		<a href="{$url|escape}" class="cv-vitrin__all">{$title|escape} vitrinine git <span aria-hidden="true">→</span></a>
	</div>
	{/if}
</section>
