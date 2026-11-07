{* Vitrin rafındaki tek ürün. Parametreler: p (ürün), i (sıra), eager (ilk görsel) *}
{assign var=_img value=$p.image_url|default:''}
{assign var=_isPlaceholder value=($_img == '' || ($_img|replace:'favicon.png':'') != $_img)}
<article class="cv-cake{if !$p.in_stock} is-oos{/if}" style="--i:{$i|default:0}" data-cv-cake>
	<div class="cv-cake__stage">
		{if $p.has_discount}
			<span class="cv-cake__sticker cv-cake__sticker--sale">-%{Tools::getDiscount($p.old_price, $p.price)}</span>
		{elseif $p.label}
			<span class="cv-cake__sticker">{$p.label|escape}</span>
		{/if}
		{if !$p.in_stock}
			<span class="cv-cake__sticker cv-cake__sticker--oos">Tükendi</span>
		{/if}
		<button type="button" class="cv-cake__fav like-button toggle-favorite" data-id="{$p.id_product}" aria-label="{'Favorites'|translate}">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
		</button>
		<a href="{$p.url|escape}" class="cv-cake__img" title="{$p.product_name|escape}" tabindex="-1">
			{if $_isPlaceholder}
				{include file='./product-svg.tpl' variant=($p.id_product % 3) label=$p.product_name}
			{else}
				<img src="{$p.image_url|escape}" alt="{$p.product_name|escape}" decoding="async"{if $eager|default:false} fetchpriority="high"{else} loading="lazy"{/if} width="400" height="400" data-cv-fly-src>
			{/if}
		</a>
		<span class="cv-cake__plate" aria-hidden="true"></span>
		{if $p.in_stock && !$p.has_variations|default:false}
			<button type="button" class="cv-cake__add addtocart" data-id="{$p.id_product}" aria-label="{'Add to Cart'|translate}: {$p.product_name|escape}">
				<svg class="cv-cake__add-plus" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
				<svg class="cv-cake__add-ok" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
			</button>
		{else}
			<a href="{$p.url|escape}" class="cv-cake__add cv-cake__add--link" aria-label="{'View'|translate}: {$p.product_name|escape}">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
			</a>
		{/if}
	</div>
	<div class="cv-cake__tag">
		<span class="cv-cake__pin" aria-hidden="true"></span>
		<a href="{$p.url|escape}" class="cv-cake__name">{$p.product_name|escape}</a>
		{if $p.review_count|default:0 > 0}
		<span class="cv-cake__stars" aria-label="{$p.rating|string_format:'%.1f'} / 5">{for $s=1 to 5}<i class="{if $s <= ($p.rating|string_format:'%.0f')}is-on{/if}">★</i>{/for}</span>
		{/if}
		<div class="cv-cake__price">
			{if $p.has_discount}<del>{$p.old_price_formatted}</del>{/if}
			<strong>{$p.price_formatted}</strong>
		</div>
	</div>
</article>
