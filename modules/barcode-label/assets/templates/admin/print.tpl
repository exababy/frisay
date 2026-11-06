<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<title>Etiket Yazdır ({$printCount} adet)</title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="{$labelCssUrl|escape}">
<style>
	html, body { margin: 0; padding: 0; }
	body { background: #e9ebee; font-family: Arial, Helvetica, sans-serif; }
	.bl-toolbar {
		position: sticky; top: 0; z-index: 10;
		display: flex; align-items: center; justify-content: space-between; gap: 12px;
		padding: 12px 20px; background: #1f2328; color: #fff; font-size: 14px;
	}
	.bl-toolbar button {
		border: 0; border-radius: 6px; padding: 9px 18px; font-size: 14px; font-weight: 700; cursor: pointer;
		background: #2f9e6e; color: #fff;
	}
	.bl-toolbar small { opacity: .7; }
	.bl-pages { display: flex; flex-direction: column; align-items: center; gap: 14px; padding: 20px; }
	.bl-page { position: relative; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,.15); overflow: hidden; }
	.bl-page--thermal { width: {$printConfig.width}mm; height: {$printConfig.height}mm; }
	.bl-page--a4 { width: 210mm; height: 297mm; }
	.bl-page--a4 .bl-label { position: absolute; }
	.bl-page--a4 .bl-label.is-guide { outline: 0.1mm dashed #bbb; }

	@media print {
		body { background: #fff; }
		.bl-toolbar { display: none; }
		.bl-pages { display: block; padding: 0; }
		.bl-page { box-shadow: none; margin: 0; page-break-after: always; break-after: page; }
		.bl-page:last-child { page-break-after: auto; break-after: auto; }
		.bl-page--a4 .bl-label.is-guide { outline: none; }
	}
	{if $printConfig.mode == 'a4'}
	@page { size: A4 portrait; margin: 0; }
	{else}
	@page { size: {$printConfig.width}mm {$printConfig.height}mm; margin: 0; }
	{/if}
</style>
</head>
<body>
<div class="bl-toolbar">
	<div>
		<strong>{$printCount} etiket</strong>
		<small>&middot; {$printConfig.width} &times; {$printConfig.height} mm &middot; {if $printConfig.mode == 'a4'}A4 etiket kağıdı ({$printConfig.sheet.cols} &times; {$printConfig.sheet.rows}){else}Termal / tek etiket{/if}</small>
	</div>
	<div>
		<small>Yazdırma penceresinde ölçek: <b>%100 (Gerçek boyut)</b>, kenar boşluğu: <b>Yok</b> seçin.</small>
		<button type="button" onclick="window.print()">Yazdır</button>
	</div>
</div>
<div class="bl-pages" id="blPages"></div>

<script src="{$barcodeJsUrl|escape}"></script>
<script src="{$renderJsUrl|escape}"></script>
<script>
(function () {
	var config = {$printConfigJson nofilter};
	var items = {$printItemsJson nofilter};
	var host = document.getElementById('blPages');

	if (config.mode === 'a4') {
		var s = config.sheet;
		var perPage = Math.max(1, s.cols * s.rows);
		var page = null;
		items.forEach(function (item, i) {
			var slot = i % perPage;
			if (slot === 0) {
				page = document.createElement('div');
				page.className = 'bl-page bl-page--a4';
				host.appendChild(page);
			}
			var col = slot % s.cols;
			var row = Math.floor(slot / s.cols);
			var label = BLLabel.render(config, item);
			label.classList.add('is-guide');
			label.style.left = (s.marginLeft + col * (config.width + s.gapX)) + 'mm';
			label.style.top = (s.marginTop + row * (config.height + s.gapY)) + 'mm';
			page.appendChild(label);
		});
	} else {
		items.forEach(function (item) {
			var page = document.createElement('div');
			page.className = 'bl-page bl-page--thermal';
			page.appendChild(BLLabel.render(config, item));
			host.appendChild(page);
		});
	}

	window.addEventListener('load', function () {
		setTimeout(function () { window.print(); }, 400);
	});
})();
</script>
</body>
</html>
