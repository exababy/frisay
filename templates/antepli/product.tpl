{* Ürün sayfası: theme4 ürün altyapısı (varyasyon, kişiselleştirme, modül hook'ları) + Antepli Ustalar vitrin sahnesi *}
{assign var=cv value=$antepli|default:[]}
{assign var=_pcid value=$product.id_category|default:0}
{include file='./partials/pal.tpl' id=$_pcid idx=$_pcid assign=_pal}
{assign var=_pst value=$cv.styles[$_pcid]|default:[]}
<div class="cv-product cv-pal--{$_pal}">
	<span class="cv-product__backdrop" aria-hidden="true"></span>
	<div class="cv-container cv-product__inner">
		{include file='theme4/product.tpl'}
	</div>
	<div class="cv-product__promise cv-container" data-cv-reveal>
		<div class="cv-promise"><span aria-hidden="true">🔥</span><strong>Sıcak fırından</strong><small>Her gün taze üretim</small></div>
		<div class="cv-promise"><span aria-hidden="true">🌰</span><strong>Antep usulü</strong><small>Ustalarımızın elinden</small></div>
		<div class="cv-promise"><span aria-hidden="true">📦</span><strong>Özel kutuda</strong><small>Ezilmeden teslim</small></div>
	</div>
</div>
