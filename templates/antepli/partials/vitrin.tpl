{*
	Vitrin dolabı.
	  products, title, url, pal (palet), tagline, ribbon, vid, mode ('shelf' | 'grid'), idx,
	  headingTag (h1/h2), showHead (varsayılan: evet), eagerFirst
*}
{assign var=_mode value=$mode|default:'shelf'}
{assign var=_h value=$headingTag|default:'h2'}
<section class="cv-vitrin cv-pal--{$pal|default:'susam'|escape} cv-vitrin--{$_mode}" id="{$vid|default:''|escape}" data-cv-vitrin data-cv-reveal style="--vi:{$idx|default:0}">
	{if $showHead|default:true}
	<header class="cv-vitrin__head">
		<div class="cv-vitrin__titles">
			<p class="cv-kicker">Vitrin {(($idx|default:0) + 1)|string_format:'%02d'}{if $ribbon|default:'' != ''}<span class="cv-vitrin__ribbon">{$ribbon|escape}</span>{/if}</p>
			<{$_h} class="cv-vitrin__title">{$title|escape}</{$_h}>
			{if $tagline|default:'' != ''}<p class="cv-vitrin__tagline">{$tagline|escape}</p>{/if}
		</div>
		{if $url|default:'' != ''}
		<a href="{$url|escape}" class="cv-link cv-vitrin__all">Tümünü gör<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
		{/if}
	</header>
	{/if}

	<div class="cv-vitrin__case">
		<span class="cv-vitrin__light" aria-hidden="true"></span>
		<span class="cv-vitrin__glass" aria-hidden="true"></span>
		<div class="cv-shelf" data-cv-shelf>
			<div class="cv-shelf__track" data-cv-track>
				{foreach $products as $p name=vitrinProducts}
					{include file='./product-card.tpl' p=$p i=$smarty.foreach.vitrinProducts.index eager=($eagerFirst|default:false && $smarty.foreach.vitrinProducts.first)}
				{/foreach}
				{if $_mode == 'shelf' && $url|default:'' != ''}
				<a href="{$url|escape}" class="cv-shelf__more" style="--i:{$products|@count}">
					<span class="cv-shelf__more-ring" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
					<span>Tüm<br>{$title|escape}</span>
				</a>
				{/if}
			</div>
		</div>
		{if $_mode == 'shelf'}
		<button type="button" class="cv-shelf__nav cv-shelf__nav--prev" data-cv-prev aria-label="{'Previous'|translate}">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
		</button>
		<button type="button" class="cv-shelf__nav cv-shelf__nav--next" data-cv-next aria-label="{'Next'|translate}">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
		</button>
		{/if}
	</div>
</section>
