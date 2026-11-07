{* Görseli olmayan ürünler için çizim (vitrin paletinin hamur / susam / iç renkleriyle).
   variant 0: köylü kahkesi halkası · 1: fıstıklı kerebiç · 2: çubuk kahke *}
{assign var=_v value=$variant|default:0}
<svg class="cv-svgp cv-svgp--v{$_v}" viewBox="0 0 200 200" role="img" aria-label="{$label|default:''|escape}">
	<ellipse class="cv-svgp__shadow" cx="100" cy="176" rx="70" ry="9"/>
	{if $_v == 0}
		<path class="cv-svgp__dough" fill-rule="evenodd" d="M100 46c40 0 70 24 70 58s-30 58-70 58-70-24-70-58 30-58 70-58Zm0 38c-14 0-24 9-24 20s10 20 24 20 24-9 24-20-10-20-24-20Z"/>
		<path class="cv-svgp__crust" d="M44 86c10-22 32-34 56-34s46 12 56 34" fill="none"/>
		<g class="cv-svgp__seeds">
			<ellipse cx="62" cy="80" rx="3.2" ry="1.8" transform="rotate(-30 62 80)"/><ellipse cx="80" cy="66" rx="3.2" ry="1.8" transform="rotate(20 80 66)"/>
			<ellipse cx="104" cy="62" rx="3.2" ry="1.8" transform="rotate(-10 104 62)"/><ellipse cx="128" cy="68" rx="3.2" ry="1.8" transform="rotate(40 128 68)"/>
			<ellipse cx="146" cy="84" rx="3.2" ry="1.8" transform="rotate(-50 146 84)"/><ellipse cx="54" cy="108" rx="3.2" ry="1.8" transform="rotate(70 54 108)"/>
			<ellipse cx="150" cy="114" rx="3.2" ry="1.8" transform="rotate(10 150 114)"/><ellipse cx="70" cy="136" rx="3.2" ry="1.8" transform="rotate(-20 70 136)"/>
			<ellipse cx="96" cy="146" rx="3.2" ry="1.8" transform="rotate(30 96 146)"/><ellipse cx="126" cy="140" rx="3.2" ry="1.8" transform="rotate(-40 126 140)"/>
			<ellipse cx="86" cy="76" rx="3.2" ry="1.8" transform="rotate(60 86 76)"/><ellipse cx="116" cy="80" rx="3.2" ry="1.8" transform="rotate(-60 116 80)"/>
			<ellipse cx="140" cy="128" rx="3.2" ry="1.8" transform="rotate(25 140 128)"/><ellipse cx="60" cy="122" rx="3.2" ry="1.8" transform="rotate(-35 60 122)"/>
		</g>
		<g class="cv-svgp__dark-seeds">
			<circle cx="72" cy="92" r="2"/><circle cx="134" cy="96" r="2"/><circle cx="110" cy="132" r="2"/><circle cx="82" cy="126" r="2"/><circle cx="120" cy="70" r="2"/>
		</g>
	{elseif $_v == 1}
		<path class="cv-svgp__dough" d="M28 150c0-58 32-96 72-96s72 38 72 96c-20 10-46 14-72 14s-52-4-72-14Z"/>
		<path class="cv-svgp__ridge" d="M60 152c-2-34 8-64 26-86M82 158c0-38 6-72 18-96M100 160V60M118 158c0-38-6-72-18-96M140 152c2-34-8-64-26-86" fill="none"/>
		<path class="cv-svgp__crust" d="M36 128c12-42 36-66 64-66s52 24 64 66" fill="none"/>
		<path class="cv-svgp__fill" d="M128 140c10-14 30-16 40-6-6 14-24 22-40 18-4-4-4-8 0-12Z"/>
		<g class="cv-svgp__crumbs"><circle cx="164" cy="160" r="3"/><circle cx="154" cy="166" r="2"/><circle cx="172" cy="150" r="2.2"/></g>
	{else}
		<g transform="rotate(-18 100 110)">
			<rect class="cv-svgp__dough" x="22" y="78" width="156" height="34" rx="17"/>
			<rect class="cv-svgp__dough cv-svgp__dough--2" x="30" y="118" width="150" height="32" rx="16"/>
			<path class="cv-svgp__crust" d="M36 86h128M44 126h124" fill="none"/>
			<g class="cv-svgp__seeds">
				<ellipse cx="44" cy="92" rx="3.2" ry="1.8"/><ellipse cx="62" cy="98" rx="3.2" ry="1.8" transform="rotate(40 62 98)"/><ellipse cx="80" cy="90" rx="3.2" ry="1.8" transform="rotate(-30 80 90)"/>
				<ellipse cx="98" cy="100" rx="3.2" ry="1.8"/><ellipse cx="118" cy="92" rx="3.2" ry="1.8" transform="rotate(50 118 92)"/><ellipse cx="136" cy="100" rx="3.2" ry="1.8"/>
				<ellipse cx="156" cy="92" rx="3.2" ry="1.8" transform="rotate(-40 156 92)"/><ellipse cx="54" cy="134" rx="3.2" ry="1.8"/><ellipse cx="74" cy="140" rx="3.2" ry="1.8" transform="rotate(30 74 140)"/>
				<ellipse cx="94" cy="130" rx="3.2" ry="1.8"/><ellipse cx="114" cy="138" rx="3.2" ry="1.8" transform="rotate(-30 114 138)"/><ellipse cx="134" cy="132" rx="3.2" ry="1.8"/>
				<ellipse cx="154" cy="140" rx="3.2" ry="1.8" transform="rotate(60 154 140)"/><ellipse cx="168" cy="130" rx="3.2" ry="1.8"/>
			</g>
		</g>
	{/if}
	<ellipse class="cv-svgp__shine" cx="74" cy="78" rx="14" ry="5" transform="rotate(-20 74 78)"/>
</svg>
