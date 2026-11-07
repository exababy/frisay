{* Görseli olmayan ürünler için ince çizgi illüstrasyon (vitrin renginde).
   variant 0: köylü kahkesi · 1: kerebiç · 2: çubuk kahke *}
{assign var=_v value=$variant|default:0}
<svg class="cv-svgp" viewBox="0 0 200 200" role="img" aria-label="{$label|default:''|escape}">
	{if $_v == 0}
		<ellipse cx="100" cy="104" rx="66" ry="52"/>
		<ellipse cx="100" cy="104" rx="22" ry="17"/>
		<path d="M52 82c10-16 28-26 48-26M148 128c-10 14-26 22-48 22" class="cv-svgp__soft"/>
		<g class="cv-svgp__dots"><circle cx="64" cy="94" r="1.6"/><circle cx="78" cy="72" r="1.6"/><circle cx="104" cy="66" r="1.6"/><circle cx="130" cy="74" r="1.6"/><circle cx="146" cy="96" r="1.6"/><circle cx="142" cy="122" r="1.6"/><circle cx="118" cy="142" r="1.6"/><circle cx="88" cy="144" r="1.6"/><circle cx="64" cy="128" r="1.6"/></g>
	{elseif $_v == 1}
		<path d="M32 148c0-56 30-92 68-92s68 36 68 92c-18 8-42 12-68 12s-50-4-68-12Z"/>
		<path d="M60 152c-2-34 8-64 26-86M82 158c0-38 6-72 18-96M100 160V60M118 158c0-38-6-72-18-96M140 152c2-34-8-64-26-86" class="cv-svgp__soft"/>
	{else}
		<rect x="26" y="74" width="148" height="30" rx="15" transform="rotate(-12 100 89)"/>
		<rect x="30" y="112" width="148" height="30" rx="15" transform="rotate(-12 104 127)"/>
		<g class="cv-svgp__dots"><circle cx="54" cy="94" r="1.6"/><circle cx="84" cy="86" r="1.6"/><circle cx="114" cy="80" r="1.6"/><circle cx="144" cy="74" r="1.6"/><circle cx="60" cy="134" r="1.6"/><circle cx="92" cy="126" r="1.6"/><circle cx="124" cy="120" r="1.6"/><circle cx="154" cy="114" r="1.6"/></g>
	{/if}
	<path d="M70 182h60" class="cv-svgp__soft"/>
</svg>
