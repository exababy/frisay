/* Barkod & Etiket — etiket çizici (mm / pt birimleri; tasarımcı ve yazdırma ortak) */
(function (root) {
	'use strict';

	var TEXT_KEYS = ['name', 'variant', 'brand', 'price', 'old_price', 'discount', 'sku', 'custom'];
	var LINE_HEIGHT = 1.15;

	function valueFor(key, el, item) {
		var v = '';
		switch (key) {
			case 'custom': v = el.text || ''; break;
			case 'name': v = item.name || ''; break;
			case 'variant': v = item.variant || ''; break;
			case 'brand': v = item.brand || ''; break;
			case 'price': v = item.price || ''; break;
			case 'old_price': v = item.old_price || ''; break;
			case 'discount': v = item.discount || ''; break;
			case 'sku': v = item.sku || ''; break;
		}
		if (v === '') return '';
		return (el.prefix ? el.prefix : '') + v;
	}

	function mm(n) {
		return (Math.round(Number(n) * 100) / 100) + 'mm';
	}

	function buildText(key, el, item, opts) {
		var text = valueFor(key, el, item);
		if (text === '' && !opts.showEmpty) return null;

		var node = document.createElement('div');
		node.className = 'bl-el bl-el--text' + (el.invert ? ' is-invert' : '');
		node.setAttribute('data-key', key);
		node.style.left = mm(el.x);
		node.style.top = mm(el.y);
		node.style.width = mm(el.w);
		node.style.fontSize = el.size + 'pt';
		node.style.lineHeight = String(LINE_HEIGHT);
		node.style.fontWeight = el.bold ? '700' : '400';
		node.style.fontStyle = el.italic ? 'italic' : 'normal';
		node.style.textAlign = el.align || 'left';
		node.style.textDecoration = el.strike ? 'line-through' : 'none';

		var inner = document.createElement('span');
		inner.className = 'bl-el__txt';
		inner.style.webkitLineClamp = String(el.lines || 1);
		inner.style.maxHeight = ((el.lines || 1) * el.size * LINE_HEIGHT) + 'pt';
		inner.textContent = text !== '' ? text : '—';
		if (text === '') node.classList.add('is-empty');
		node.appendChild(inner);
		return node;
	}

	function buildBarcode(el, item, font) {
		var node = document.createElement('div');
		node.className = 'bl-el bl-el--barcode';
		node.setAttribute('data-key', 'barcode');
		node.style.left = mm(el.x);
		node.style.top = mm(el.y);
		node.style.width = mm(el.w);
		node.style.height = mm(el.h);

		var encoded = root.BLBarcode ? root.BLBarcode.encode(item.barcode || '', el.format) : null;
		var bars = document.createElement('div');
		bars.className = 'bl-bars';
		bars.innerHTML = encoded ? root.BLBarcode.svg(encoded) : '<span class="bl-bars__err">Barkod yok</span>';
		node.appendChild(bars);

		if (el.showText && encoded) {
			var txt = document.createElement('div');
			txt.className = 'bl-bars__text';
			txt.style.fontSize = el.textSize + 'pt';
			txt.style.fontFamily = font;
			txt.textContent = encoded.text;
			node.appendChild(txt);
		}
		return node;
	}

	/**
	 * @param {object} config Şablon ayarları
	 * @param {object} item   Ürün verisi (name, price, old_price, discount, barcode…)
	 * @param {object} [opts] { showEmpty: bool }
	 */
	function render(config, item, opts) {
		opts = opts || {};
		var font = (config.font || 'Arial') + ', Helvetica, sans-serif';
		var label = document.createElement('div');
		label.className = 'bl-label' + (config.border ? ' has-border' : '');
		label.style.width = mm(config.width);
		label.style.height = mm(config.height);
		label.style.fontFamily = font;

		var els = config.elements || {};

		TEXT_KEYS.forEach(function (key) {
			var el = els[key];
			if (!el || !el.show) return;
			var node = buildText(key, el, item, opts);
			if (node) label.appendChild(node);
		});

		if (els.barcode && els.barcode.show) {
			label.appendChild(buildBarcode(els.barcode, item, font));
		}

		return label;
	}

	root.BLLabel = { render: render, TEXT_KEYS: TEXT_KEYS };
})(window);
