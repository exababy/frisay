/* Barkod & Etiket — admin tasarımcı */
(function () {
	'use strict';

	var app = document.getElementById('blApp');
	if (!app || !window.BLLabel || !window.BLBarcode) return;

	var PX_PER_MM = 96 / 25.4;
	var ORDER = ['name', 'variant', 'brand', 'price', 'old_price', 'discount', 'sku', 'custom', 'barcode'];
	var LABELS = {
		name: 'Ürün adı',
		variant: 'Varyasyon',
		brand: 'Marka',
		price: 'Fiyat',
		old_price: 'Eski fiyat',
		discount: 'İndirim oranı',
		sku: 'Stok kodu',
		custom: 'Serbest yazı',
		barcode: 'Barkod'
	};
	var STORE_KEY = 'blSelectedItems';

	var cfg = parse(app.getAttribute('data-config'), {});
	var sample = parse(app.getAttribute('data-sample'), {});
	var searchUrl = app.getAttribute('data-search-url') || '';

	var stage = document.getElementById('blStage');
	var canvas = document.getElementById('blCanvas');
	var elementList = document.getElementById('blElementList');
	var props = document.getElementById('blProps');
	var propsTitle = document.getElementById('blPropsTitle');
	var zoomLabel = document.getElementById('blZoomLabel');
	var gridToggle = document.getElementById('blGrid');
	var previewName = document.getElementById('blPreviewName');

	var state = {
		selectedKey: null,
		zoom: 1,
		autoZoom: true,
		dirty: false,
		preview: null,
		items: loadItems()
	};

	/* ------------------------------------------------------------------ */
	/* Yardımcılar                                                         */
	/* ------------------------------------------------------------------ */

	function parse(json, fallback) {
		try {
			var v = JSON.parse(json || '');
			return v && typeof v === 'object' ? v : fallback;
		} catch (e) {
			return fallback;
		}
	}

	function round(n, step) {
		step = step || 0.5;
		return Math.round(Math.round(n / step) * step * 100) / 100;
	}

	function clamp(n, min, max) {
		return Math.min(max, Math.max(min, n));
	}

	function h(tag, attrs, children) {
		var node = document.createElement(tag);
		Object.keys(attrs || {}).forEach(function (k) {
			if (k === 'text') node.textContent = attrs[k];
			else if (k === 'class') node.className = attrs[k];
			else if (k.indexOf('on') === 0 && typeof attrs[k] === 'function') node.addEventListener(k.slice(2), attrs[k]);
			else if (attrs[k] === true) node.setAttribute(k, '');
			else if (attrs[k] !== false && attrs[k] != null) node.setAttribute(k, attrs[k]);
		});
		(children || []).forEach(function (c) {
			if (c) node.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
		});
		return node;
	}

	function getPath(obj, path) {
		return path.split('.').reduce(function (o, k) { return o ? o[k] : undefined; }, obj);
	}

	function setPath(obj, path, value) {
		var parts = path.split('.');
		var last = parts.pop();
		var target = parts.reduce(function (o, k) { return o[k]; }, obj);
		target[last] = value;
	}

	function markDirty() {
		state.dirty = true;
	}

	function el(key) {
		return cfg.elements[key];
	}

	/* ------------------------------------------------------------------ */
	/* Önizleme / sahne                                                    */
	/* ------------------------------------------------------------------ */

	function fitZoom() {
		var avail = stage.clientWidth - 48;
		var availH = 420;
		var z = Math.min(avail / (cfg.width * PX_PER_MM), availH / (cfg.height * PX_PER_MM));
		state.zoom = clamp(Math.floor(z * 4) / 4, 1, 10);
	}

	function render() {
		if (state.autoZoom) fitZoom();

		var z = state.zoom;
		var item = state.preview || sample;
		var label = BLLabel.render(cfg, item, { showEmpty: true });

		label.style.transform = 'scale(' + z + ')';
		label.style.setProperty('--bl-inv', String(1 / z));

		label.querySelectorAll('.bl-el').forEach(function (node) {
			var key = node.getAttribute('data-key');
			node.setAttribute('title', LABELS[key] || key);
			if (key === state.selectedKey) node.classList.add('is-selected');
			node.appendChild(h('span', { class: 'bl-handle', 'data-handle': '1' }));
		});

		canvas.innerHTML = '';
		canvas.style.width = (cfg.width * PX_PER_MM * z) + 'px';
		canvas.style.height = (cfg.height * PX_PER_MM * z) + 'px';
		canvas.style.setProperty('--bl-mm', (PX_PER_MM * z) + 'px');
		canvas.classList.toggle('has-grid', gridToggle.checked);
		canvas.appendChild(label);

		zoomLabel.textContent = Math.round(z * 100) + '%';
		previewName.textContent = 'Önizleme: ' + (state.preview ? state.preview.name + (state.preview.variant ? ' (' + state.preview.variant + ')' : '') : 'örnek ürün');
	}

	function nodeFor(key) {
		return canvas.querySelector('.bl-el[data-key="' + key + '"]');
	}

	function applyGeometry(key) {
		var node = nodeFor(key);
		var e = el(key);
		if (!node) return;
		node.style.left = e.x + 'mm';
		node.style.top = e.y + 'mm';
		node.style.width = e.w + 'mm';
		if (key === 'barcode') node.style.height = e.h + 'mm';
	}

	/* Sürükle & boyutlandır */
	var drag = null;

	canvas.addEventListener('pointerdown', function (ev) {
		var node = ev.target.closest('.bl-el');

		if (!node) {
			select(null);
			return;
		}

		ev.preventDefault();
		var key = node.getAttribute('data-key');
		if (state.selectedKey !== key) select(key);

		var e = el(key);
		drag = {
			key: key,
			resize: !!ev.target.closest('[data-handle]'),
			sx: ev.clientX,
			sy: ev.clientY,
			x: e.x,
			y: e.y,
			w: e.w,
			h: e.h || 0
		};
		stage.focus({ preventScroll: true });
	});

	document.addEventListener('pointermove', function (ev) {
		if (!drag) return;

		var e = el(drag.key);
		var step = ev.altKey ? 0.1 : 0.5;
		var dx = (ev.clientX - drag.sx) / (PX_PER_MM * state.zoom);
		var dy = (ev.clientY - drag.sy) / (PX_PER_MM * state.zoom);

		if (drag.resize) {
			e.w = clamp(round(drag.w + dx, step), drag.key === 'barcode' ? 5 : 2, 200);
			if (drag.key === 'barcode') e.h = clamp(round(drag.h + dy, step), 3, 200);
		} else {
			e.x = clamp(round(drag.x + dx, step), -50, 200);
			e.y = clamp(round(drag.y + dy, step), -50, 200);
		}

		applyGeometry(drag.key);
		syncProps();
		markDirty();
	});

	document.addEventListener('pointerup', function () {
		if (!drag) return;
		drag = null;
		render();
	});

	stage.addEventListener('keydown', function (ev) {
		if (!state.selectedKey) return;

		var moves = { ArrowLeft: [-1, 0], ArrowRight: [1, 0], ArrowUp: [0, -1], ArrowDown: [0, 1] };
		var m = moves[ev.key];

		if (m) {
			ev.preventDefault();
			var step = ev.shiftKey ? 2 : (ev.altKey ? 0.1 : 0.5);
			var e = el(state.selectedKey);
			e.x = round(e.x + m[0] * step, 0.1);
			e.y = round(e.y + m[1] * step, 0.1);
			markDirty();
			render();
			syncProps();
		} else if (ev.key === 'Delete') {
			ev.preventDefault();
			el(state.selectedKey).show = false;
			markDirty();
			select(null);
		} else if (ev.key === 'Escape') {
			select(null);
		}
	});

	document.querySelectorAll('[data-zoom]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var dir = parseInt(btn.getAttribute('data-zoom'), 10);
			if (dir === 0) {
				state.autoZoom = true;
			} else {
				state.autoZoom = false;
				state.zoom = clamp(state.zoom + dir * 0.5, 1, 12);
			}
			render();
		});
	});

	gridToggle.addEventListener('change', render);
	window.addEventListener('resize', function () {
		if (state.autoZoom) render();
	});

	/* ------------------------------------------------------------------ */
	/* Öğe listesi                                                         */
	/* ------------------------------------------------------------------ */

	function renderElementList() {
		elementList.innerHTML = '';

		ORDER.forEach(function (key) {
			var e = el(key);
			var check = h('input', { type: 'checkbox', class: 'form-check-input m-0', title: 'Göster / gizle' });
			check.checked = !!e.show;
			check.addEventListener('change', function () {
				e.show = check.checked;
				markDirty();
				select(check.checked ? key : (state.selectedKey === key ? null : state.selectedKey));
			});

			var btn = h('button', { type: 'button', class: 'bl-elements__name', text: LABELS[key] });
			btn.addEventListener('click', function () {
				if (!e.show) {
					e.show = true;
					markDirty();
				}
				select(key);
			});

			var meta = key === 'barcode'
				? e.w + '×' + e.h + ' mm'
				: e.size + ' pt';

			elementList.appendChild(h('li', { class: (state.selectedKey === key ? 'is-active' : '') + (e.show ? '' : ' is-hidden') }, [
				check,
				btn,
				h('small', { class: 'text-muted', text: meta })
			]));
		});
	}

	function select(key) {
		state.selectedKey = key;
		render();
		renderElementList();
		renderProps();
	}

	/* ------------------------------------------------------------------ */
	/* Özellik paneli                                                      */
	/* ------------------------------------------------------------------ */

	function numField(label, prop, opts) {
		opts = opts || {};
		var input = h('input', {
			type: 'number',
			class: 'form-control form-control-sm',
			step: opts.step || 0.5,
			min: opts.min != null ? opts.min : '',
			max: opts.max != null ? opts.max : '',
			'data-prop': prop
		});
		input.value = el(state.selectedKey)[prop];
		input.addEventListener('input', function () {
			var v = parseFloat(input.value);
			if (isNaN(v)) return;
			if (opts.min != null) v = Math.max(opts.min, v);
			if (opts.max != null) v = Math.min(opts.max, v);
			el(state.selectedKey)[prop] = opts.int ? Math.round(v) : v;
			markDirty();
			render();
			renderElementList();
		});

		var group = [input];

		if (opts.stepper) {
			var minus = h('button', { type: 'button', class: 'btn btn-light btn-sm', text: '−', title: 'Küçült' });
			var plus = h('button', { type: 'button', class: 'btn btn-light btn-sm', text: '+', title: 'Büyüt' });
			var bump = function (d) {
				var e = el(state.selectedKey);
				var v = Math.round((Number(e[prop]) + d) * 100) / 100;
				if (opts.min != null) v = Math.max(opts.min, v);
				if (opts.max != null) v = Math.min(opts.max, v);
				e[prop] = v;
				input.value = v;
				markDirty();
				render();
				renderElementList();
			};
			minus.addEventListener('click', function () { bump(-opts.stepper); });
			plus.addEventListener('click', function () { bump(opts.stepper); });
			group = [h('div', { class: 'input-group input-group-sm' }, [minus, input, plus])];
		}

		return h('div', { class: opts.col || 'col-6' }, [h('label', { class: 'form-label small mb-1', text: label })].concat(group));
	}

	function checkField(label, prop) {
		var id = 'blp_' + prop;
		var input = h('input', { type: 'checkbox', class: 'form-check-input', id: id, 'data-prop': prop });
		input.checked = !!el(state.selectedKey)[prop];
		input.addEventListener('change', function () {
			el(state.selectedKey)[prop] = input.checked;
			markDirty();
			render();
		});
		return h('div', { class: 'form-check form-check-inline' }, [input, h('label', { class: 'form-check-label small', for: id, text: label })]);
	}

	function textField(label, prop, placeholder, col) {
		var input = h('input', { type: 'text', class: 'form-control form-control-sm', maxlength: prop === 'text' ? 120 : 40, placeholder: placeholder || '', 'data-prop': prop });
		input.value = el(state.selectedKey)[prop] || '';
		input.addEventListener('input', function () {
			el(state.selectedKey)[prop] = input.value;
			markDirty();
			render();
		});
		return h('div', { class: col || 'col-6' }, [h('label', { class: 'form-label small mb-1', text: label }), input]);
	}

	function alignField() {
		var e = el(state.selectedKey);
		var group = h('div', { class: 'btn-group btn-group-sm w-100' });
		[['left', 'Sol'], ['center', 'Orta'], ['right', 'Sağ']].forEach(function (a) {
			var b = h('button', { type: 'button', class: 'btn ' + (e.align === a[0] ? 'btn-dark' : 'btn-light'), text: a[1] });
			b.addEventListener('click', function () {
				e.align = a[0];
				markDirty();
				render();
				renderProps();
			});
			group.appendChild(b);
		});
		return h('div', { class: 'col-6' }, [h('label', { class: 'form-label small mb-1', text: 'Hizalama' }), group]);
	}

	function actionButtons() {
		var key = state.selectedKey;
		var e = el(key);
		var wrap = h('div', { class: 'col-12 bl-prop-actions' });

		var center = h('button', { type: 'button', class: 'btn btn-outline-secondary btn-sm', text: 'Yatay ortala' });
		center.addEventListener('click', function () {
			e.x = round((cfg.width - e.w) / 2, 0.1);
			markDirty();
			render();
			syncProps();
		});

		var full = h('button', { type: 'button', class: 'btn btn-outline-secondary btn-sm', text: 'Tam genişlik' });
		full.addEventListener('click', function () {
			e.x = 1.5;
			e.w = round(cfg.width - 3, 0.1);
			markDirty();
			render();
			syncProps();
			renderElementList();
		});

		var hide = h('button', { type: 'button', class: 'btn btn-outline-danger btn-sm ms-auto', text: 'Gizle' });
		hide.addEventListener('click', function () {
			e.show = false;
			markDirty();
			select(null);
		});

		wrap.appendChild(center);
		wrap.appendChild(full);
		wrap.appendChild(hide);
		return wrap;
	}

	function renderProps() {
		props.innerHTML = '';
		var key = state.selectedKey;

		if (!key) {
			propsTitle.textContent = 'Öğe özellikleri';
			props.appendChild(h('p', { class: 'small text-muted mb-0', text: 'Düzenlemek için etiketten veya listeden bir öğe seçin.' }));
			return;
		}

		propsTitle.textContent = LABELS[key] + ' — özellikler';
		var row = h('div', { class: 'row g-2' });

		row.appendChild(numField('Soldan (mm)', 'x', { min: -50, max: 200 }));
		row.appendChild(numField('Üstten (mm)', 'y', { min: -50, max: 200 }));

		if (key === 'barcode') {
			row.appendChild(numField('Barkod genişliği (mm)', 'w', { min: 5, max: 200, stepper: 1 }));
			row.appendChild(numField('Barkod yüksekliği (mm)', 'h', { min: 3, max: 200, stepper: 1 }));
			row.appendChild(numField('Rakam yazı boyutu (pt)', 'textSize', { min: 3, max: 30, stepper: 0.5 }));

			var fmt = h('select', { class: 'form-select form-select-sm', 'data-prop': 'format' }, [
				h('option', { value: 'auto', text: 'Otomatik (EAN-13 / Code128)' }),
				h('option', { value: 'ean13', text: 'EAN-13' }),
				h('option', { value: 'code128', text: 'Code128' })
			]);
			fmt.value = el(key).format;
			fmt.addEventListener('change', function () {
				el(key).format = fmt.value;
				markDirty();
				render();
			});
			row.appendChild(h('div', { class: 'col-6' }, [h('label', { class: 'form-label small mb-1', text: 'Barkod tipi' }), fmt]));
			row.appendChild(h('div', { class: 'col-12' }, [checkField('Barkod rakamlarını göster', 'showText')]));
		} else {
			row.appendChild(numField('Genişlik (mm)', 'w', { min: 2, max: 200 }));
			row.appendChild(numField('Yazı boyutu (pt)', 'size', { min: 3, max: 96, step: 0.5, stepper: 0.5 }));
			row.appendChild(numField('En fazla satır', 'lines', { min: 1, max: 6, step: 1, int: true }));
			row.appendChild(alignField());

			if (key === 'custom') {
				row.appendChild(textField('Yazı', 'text', 'Örn: mağaza adı, KDV dahildir', 'col-12'));
			} else {
				row.appendChild(textField('Ön ek', 'prefix', key === 'price' ? 'Örn: Fiyat: ' : ''));
			}

			row.appendChild(h('div', { class: 'col-12' }, [
				checkField('Kalın', 'bold'),
				checkField('İtalik', 'italic'),
				checkField('Üstü çizili', 'strike'),
				checkField('Siyah zemin (ters)', 'invert')
			]));
		}

		row.appendChild(actionButtons());
		props.appendChild(row);
	}

	function syncProps() {
		if (!state.selectedKey) return;
		var e = el(state.selectedKey);
		props.querySelectorAll('input[type="number"][data-prop]').forEach(function (input) {
			if (document.activeElement === input) return;
			input.value = e[input.getAttribute('data-prop')];
		});
	}

	/* ------------------------------------------------------------------ */
	/* Etiket ayarları                                                     */
	/* ------------------------------------------------------------------ */

	var cfgInputs = app.querySelectorAll('[data-cfg]');

	function fillCfgInputs() {
		cfgInputs.forEach(function (input) {
			var v = getPath(cfg, input.getAttribute('data-cfg'));
			if (input.type === 'checkbox') input.checked = !!v;
			else input.value = v;
		});
		updateSheetInfo();
	}

	cfgInputs.forEach(function (input) {
		input.addEventListener(input.tagName === 'SELECT' || input.type === 'checkbox' ? 'change' : 'input', function () {
			var path = input.getAttribute('data-cfg');
			var v;

			if (input.type === 'checkbox') v = input.checked;
			else if (input.type === 'number') {
				v = parseFloat(input.value);
				if (isNaN(v)) return;
				if (input.min !== '') v = Math.max(parseFloat(input.min), v);
				if (input.max !== '') v = Math.min(parseFloat(input.max), v);
			} else v = input.value;

			setPath(cfg, path, v);
			markDirty();
			updateSheetInfo();
			render();
		});
	});

	function scaleTo(width, height) {
		var rx = width / cfg.width;
		var ry = height / cfg.height;
		var rs = Math.min(rx, ry);

		ORDER.forEach(function (key) {
			var e = el(key);
			e.x = round(e.x * rx, 0.1);
			e.y = round(e.y * ry, 0.1);
			e.w = round(e.w * rx, 0.1);
			if (key === 'barcode') {
				e.h = round(e.h * ry, 0.1);
				e.textSize = clamp(round(e.textSize * rs, 0.5), 3, 30);
			} else {
				e.size = clamp(round(e.size * rs, 0.5), 3, 96);
			}
		});

		cfg.width = width;
		cfg.height = height;
	}

	app.querySelectorAll('[data-preset]').forEach(function (btn) {
		btn.setAttribute('title', 'Etiketi bu boyuta getirir, öğeleri orantılı uyarlar');
		btn.addEventListener('click', function () {
			var p = btn.getAttribute('data-preset').split('x');
			scaleTo(parseFloat(p[0]), parseFloat(p[1]));
			markDirty();
			fillCfgInputs();
			render();
			renderElementList();
			renderProps();
		});
	});

	function updateSheetInfo() {
		var fields = document.getElementById('blSheetFields');
		var info = document.getElementById('blSheetInfo');
		var isA4 = cfg.mode === 'a4';
		fields.style.display = isA4 ? '' : 'none';
		if (!isA4) return;

		var s = cfg.sheet;
		var usedW = s.marginLeft + s.cols * cfg.width + (s.cols - 1) * s.gapX;
		var usedH = s.marginTop + s.rows * cfg.height + (s.rows - 1) * s.gapY;
		var msg = 'Sayfa başına ' + (s.cols * s.rows) + ' etiket. Kullanılan alan: ' + round(usedW, 0.1) + ' × ' + round(usedH, 0.1) + ' mm (A4: 210 × 297).';

		if (usedW > 210 || usedH > 297) {
			msg += ' Uyarı: etiketler sayfaya sığmıyor!';
			info.classList.add('text-danger');
		} else {
			info.classList.remove('text-danger');
		}
		info.textContent = msg;
	}

	/* ------------------------------------------------------------------ */
	/* Şablon kaydet                                                       */
	/* ------------------------------------------------------------------ */

	var saveForm = document.getElementById('blSaveForm');
	var saveAsNew = document.getElementById('blSaveAsNew');
	var submitting = false;

	saveAsNew.addEventListener('click', function () {
		document.getElementById('blTemplateId').value = '0';
	});

	saveForm.addEventListener('submit', function () {
		document.getElementById('blConfigInput').value = JSON.stringify(cfg);
		submitting = true;
	});

	document.getElementById('blTemplateSelect').addEventListener('change', function () {
		if (state.dirty && !confirm('Kaydedilmemiş değişiklikler kaybolacak. Devam edilsin mi?')) {
			return;
		}
		submitting = true;
		window.location.href = this.value;
	});

	window.addEventListener('beforeunload', function (ev) {
		if (state.dirty && !submitting) {
			ev.preventDefault();
			ev.returnValue = '';
		}
	});

	/* ------------------------------------------------------------------ */
	/* Ürün arama ve yazdırma listesi                                      */
	/* ------------------------------------------------------------------ */

	var searchInput = document.getElementById('blSearch');
	var results = document.getElementById('blResults');
	var selectedBox = document.getElementById('blSelected');
	var printForm = document.getElementById('blPrintForm');
	var printBtn = document.getElementById('blPrintBtn');
	var printCount = document.getElementById('blPrintCount');
	var searchTimer = null;
	var lastResults = [];
	var searchSeq = 0;

	function loadItems() {
		try {
			var list = JSON.parse(localStorage.getItem(STORE_KEY) || '[]');
			return Array.isArray(list) ? list.filter(function (r) { return r && r.item && r.item.key; }) : [];
		} catch (e) {
			return [];
		}
	}

	function saveItems() {
		try {
			localStorage.setItem(STORE_KEY, JSON.stringify(state.items));
		} catch (e) { /* depolama kapalı olabilir */ }
	}

	function doSearch(q) {
		var seq = ++searchSeq;
		return fetch(searchUrl + '&q=' + encodeURIComponent(q), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
			.then(function (r) { return r.json(); })
			.then(function (data) {
				if (seq !== searchSeq) return null;
				lastResults = data && data.items ? data.items : [];
				renderResults();
				return lastResults;
			})
			.catch(function () {
				results.innerHTML = '';
				results.appendChild(h('p', { class: 'small text-danger mb-0 mt-2', text: 'Arama yapılamadı.' }));
				return null;
			});
	}

	function renderResults() {
		results.innerHTML = '';

		if (!lastResults.length) {
			results.appendChild(h('p', { class: 'small text-muted mb-0 mt-2', text: 'Sonuç bulunamadı.' }));
			return;
		}

		lastResults.forEach(function (item) {
			var add = h('button', { type: 'button', class: 'btn btn-sm btn-outline-success', text: 'Ekle' });
			add.addEventListener('click', function () { addItem(item); });

			var info = h('button', { type: 'button', class: 'bl-result__info', title: 'Etikette önizle' }, [
				h('strong', { text: item.name }),
				item.variant ? h('span', { text: item.variant }) : null,
				h('small', { text: item.barcode + (item.barcode_fallback ? ' (barkod yok, yedek)' : '') + ' · ' + item.price })
			]);
			info.addEventListener('click', function () { setPreview(item); });

			results.appendChild(h('div', { class: 'bl-result' }, [info, add]));
		});
	}

	function addItem(item, qty) {
		var row = state.items.filter(function (r) { return r.item.key === item.key; })[0];
		if (row) row.qty = Math.min(500, row.qty + (qty || 1));
		else state.items.push({ item: item, qty: qty || 1 });
		if (!state.preview) setPreview(item);
		saveItems();
		renderSelected();
	}

	function setPreview(item) {
		state.preview = item;
		render();
		renderSelected();
	}

	function renderSelected() {
		selectedBox.innerHTML = '';

		if (!state.items.length) {
			selectedBox.appendChild(h('p', { class: 'small text-muted mb-0', text: 'Henüz ürün eklenmedi. Arama yapın veya barkod okutup Enter\'a basın.' }));
		}

		state.items.forEach(function (row, idx) {
			var qty = h('input', { type: 'number', class: 'form-control form-control-sm bl-qty', min: 1, max: 500, title: 'Adet' });
			qty.value = row.qty;
			qty.addEventListener('input', function () {
				var v = parseInt(qty.value, 10);
				if (isNaN(v)) return;
				row.qty = clamp(v, 1, 500);
				saveItems();
				updatePrintButton();
			});

			var preview = h('button', { type: 'button', class: 'btn btn-sm btn-light', title: 'Etikette önizle', text: '👁' });
			preview.addEventListener('click', function () { setPreview(row.item); });

			var remove = h('button', { type: 'button', class: 'btn btn-sm btn-light text-danger', title: 'Kaldır', text: '×' });
			remove.addEventListener('click', function () {
				state.items.splice(idx, 1);
				if (state.preview && state.preview.key === row.item.key) setPreview(null);
				saveItems();
				renderSelected();
			});

			var isPreview = state.preview && state.preview.key === row.item.key;
			selectedBox.appendChild(h('div', { class: 'bl-sel' + (isPreview ? ' is-preview' : '') }, [
				h('div', { class: 'bl-sel__info' }, [
					h('strong', { text: row.item.name }),
					row.item.variant ? h('span', { text: row.item.variant }) : null,
					h('small', { text: row.item.barcode + ' · ' + row.item.price })
				]),
				qty,
				preview,
				remove
			]));
		});

		updatePrintButton();
	}

	function updatePrintButton() {
		var total = state.items.reduce(function (s, r) { return s + r.qty; }, 0);
		printBtn.disabled = total === 0;
		printCount.textContent = total ? '(' + total + ' adet)' : '';
	}

	searchInput.addEventListener('input', function () {
		clearTimeout(searchTimer);
		var q = searchInput.value.trim();
		if (q.length < 2) {
			results.innerHTML = '';
			return;
		}
		searchTimer = setTimeout(function () { doSearch(q); }, 300);
	});

	searchInput.addEventListener('keydown', function (ev) {
		if (ev.key !== 'Enter') return;
		ev.preventDefault();
		clearTimeout(searchTimer);
		var q = searchInput.value.trim();
		if (q.length < 2) return;

		doSearch(q).then(function (list) {
			if (!list || !list.length) return;
			var exact = list.filter(function (i) { return i.barcode === q || i.sku === q; });
			var pick = exact[0] || (list.length === 1 ? list[0] : null);
			if (pick) {
				addItem(pick);
				searchInput.value = '';
				searchInput.focus();
			}
		});
	});

	document.getElementById('blClear').addEventListener('click', function () {
		if (!state.items.length || !confirm('Yazdırma listesi temizlensin mi?')) return;
		state.items = [];
		saveItems();
		setPreview(null);
	});

	printForm.addEventListener('submit', function (ev) {
		if (!state.items.length) {
			ev.preventDefault();
			return;
		}
		document.getElementById('blPrintItems').value = JSON.stringify(state.items.map(function (r) {
			return { id_product: r.item.id_product, id_variation: r.item.id_variation, qty: r.qty };
		}));
		document.getElementById('blPrintConfig').value = JSON.stringify(cfg);
	});

	/* ------------------------------------------------------------------ */

	stage.setAttribute('tabindex', '0');
	fillCfgInputs();
	render();
	renderElementList();
	renderProps();
	renderSelected();
})();
