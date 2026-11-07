{* Ortak ürün ızgarası (arama, favoriler, fırsatlar, modüller) *}
{assign var="listProducts" value=$products|default:[]}
{if $listProducts|@count == 0 && isset($product)}
	{assign var="listProducts" value=$product}
{/if}
{if $listProducts|@count > 0}
<div class="cv-vitrin cv-vitrin--grid cv-vitrin--plain cv-pal--{$gridPalette|default:'vanilya'}" data-cv-vitrin data-cv-reveal>
	<div class="cv-vitrin__case">
		<span class="cv-vitrin__glass" aria-hidden="true"></span>
		<div class="cv-shelf">
			<div class="cv-shelf__track">
				{foreach $listProducts as $p name=gridProducts}
					{include file='./partials/product-card.tpl' p=$p i=$smarty.foreach.gridProducts.index eager=$smarty.foreach.gridProducts.first}
				{/foreach}
			</div>
		</div>
	</div>
</div>
{/if}
