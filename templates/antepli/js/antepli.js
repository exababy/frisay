/*!
 * Antepli Ustalar — tema etkileşimleri ve animasyonlar (sade, yavaş geçişler)
 * Bağımlılık yok (jQuery yalnızca varsa sepet olaylarını dinlemek için kullanılır).
 */
(function () {
	'use strict';

	window.cvReady = true;

	var doc = document;
	var root = doc.documentElement;
	var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var canHover = window.matchMedia && window.matchMedia('(hover: hover) and (min-width: 992px)').matches;
	var raf = window.requestAnimationFrame || function (cb) { return setTimeout(cb, 16); };

	function $all(sel, ctx) { return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel)); }
	function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }

	/* ------------------------------------------------------------------
	 * 1) Başlık kelimelerini parçala (kelime kelime yükselme animasyonu)
	 * ------------------------------------------------------------------ */
	$all('[data-cv-split]').forEach(function (el) {
		if (reduceMotion || el.getAttribute('data-cv-split-done')) return;
		var words = (el.textContent || '').trim().split(/\s+/);
		el.setAttribute('aria-label', el.textContent.trim());
		el.textContent = '';
		words.forEach(function (w, i) {
			var outer = doc.createElement('span');
			outer.className = 'cv-word';
			outer.setAttribute('aria-hidden', 'true');
			var inner = doc.createElement('span');
			inner.style.setProperty('--i', i);
			inner.textContent = w;
			outer.appendChild(inner);
			el.appendChild(outer);
			if (i < words.length - 1) el.appendChild(doc.createTextNode(' '));
		});
		el.setAttribute('data-cv-split-done', '1');
	});

	/* ------------------------------------------------------------------
	 * 2) Scroll ile görünür olma (vitrin koreografisi)
	 * ------------------------------------------------------------------ */
	var revealEls = $all('[data-cv-reveal]');
	if ('IntersectionObserver' in window && !reduceMotion) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-in');
					io.unobserve(entry.target);
				}
			});
		}, { rootMargin: '0px 0px -10% 0px', threshold: 0.12 });
		revealEls.forEach(function (el) { io.observe(el); });
	} else {
		revealEls.forEach(function (el) { el.classList.add('is-in'); });
	}

	/* ------------------------------------------------------------------
	 * 3) Header: kaydırınca gölge, alt menü gizle/göster, gerçek yükseklik
	 * ------------------------------------------------------------------ */
	var header = doc.querySelector('[data-cv-header]');
	var bottomNav = doc.querySelector('.cv-bottom-nav');
	var hero = doc.querySelector('[data-cv-hero]');
	var lastY = window.pageYOffset || 0;
	var ticking = false;

	function syncHeaderHeight() {
		if (header) root.style.setProperty('--cv-header-h', header.offsetHeight + 'px');
	}
	syncHeaderHeight();
	window.addEventListener('resize', syncHeaderHeight);

	function onScroll() {
		var y = window.pageYOffset || 0;
		if (header) {
			var was = header.classList.contains('is-scrolled');
			header.classList.toggle('is-scrolled', y > 8);
			if (was !== (y > 8)) setTimeout(syncHeaderHeight, 560);
		}
		if (bottomNav) {
			var goingDown = y > lastY + 6;
			var goingUp = y < lastY - 6;
			if (goingDown && y > 240) bottomNav.classList.add('is-hidden');
			else if (goingUp || y < 120) bottomNav.classList.remove('is-hidden');
		}
		if (hero && !reduceMotion) {
			var h = hero.offsetHeight || 1;
			hero.style.setProperty('--sy', clamp(y / h, 0, 1).toFixed(3));
		}
		lastY = y;
		ticking = false;
	}
	window.addEventListener('scroll', function () {
		if (!ticking) { ticking = true; raf(onScroll); }
	}, { passive: true });
	onScroll();

	/* ------------------------------------------------------------------
	 * 4) Arama paneli
	 * ------------------------------------------------------------------ */
	var searchBtn = doc.querySelector('[data-cv-search-toggle]');
	var searchPanel = doc.querySelector('[data-cv-search-panel]');
	if (searchBtn && searchPanel) {
		searchBtn.addEventListener('click', function () {
			var open = searchPanel.hasAttribute('hidden');
			if (open) searchPanel.removeAttribute('hidden'); else searchPanel.setAttribute('hidden', '');
			searchBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
			if (open) {
				var input = searchPanel.querySelector('input[type="search"]');
				if (input) setTimeout(function () { input.focus(); }, 60);
			}
			syncHeaderHeight();
		});
	}

	/* ------------------------------------------------------------------
	 * 5) Raflar: oklar + durum
	 * ------------------------------------------------------------------ */
	$all('[data-cv-vitrin]').forEach(function (vitrin) {
		var track = vitrin.querySelector('[data-cv-track]');
		var prev = vitrin.querySelector('[data-cv-prev]');
		var next = vitrin.querySelector('[data-cv-next]');
		if (!track || (!prev && !next)) return;

		function update() {
			var max = track.scrollWidth - track.clientWidth - 4;
			if (prev) prev.disabled = track.scrollLeft <= 4;
			if (next) next.disabled = track.scrollLeft >= max;
		}
		function step(dir) {
			track.scrollBy({ left: dir * track.clientWidth * 0.8, behavior: reduceMotion ? 'auto' : 'smooth' });
		}
		if (prev) prev.addEventListener('click', function () { step(-1); });
		if (next) next.addEventListener('click', function () { step(1); });
		track.addEventListener('scroll', function () { raf(update); }, { passive: true });
		window.addEventListener('resize', update);
		update();

		// Masaüstünde fare ile sürükleyerek kaydırma
		if (canHover) {
			var down = false, startX = 0, startLeft = 0, moved = false;
			track.addEventListener('pointerdown', function (e) {
				if (e.pointerType !== 'mouse' || e.button !== 0) return;
				down = true; moved = false; startX = e.clientX; startLeft = track.scrollLeft;
			});
			window.addEventListener('pointermove', function (e) {
				if (!down) return;
				var dx = e.clientX - startX;
				if (Math.abs(dx) > 5) { moved = true; track.style.scrollSnapType = 'none'; track.classList.add('is-dragging'); }
				track.scrollLeft = startLeft - dx;
			});
			window.addEventListener('pointerup', function () {
				if (!down) return;
				down = false;
				track.style.scrollSnapType = '';
				track.classList.remove('is-dragging');
			});
			track.addEventListener('click', function (e) {
				if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; }
			}, true);
		}
	});

	/* ------------------------------------------------------------------
	 * 6) Vitrin seçici (yapışkan pill'ler + kayan gösterge + scrollspy)
	 * ------------------------------------------------------------------ */
	var picker = doc.querySelector('[data-cv-picker]');
	if (picker) {
		var pickerTrack = picker.querySelector('[data-cv-picker-track]');
		var indicator = picker.querySelector('[data-cv-picker-indicator]');
		var pills = $all('[data-cv-pill]', picker);
		var sections = pills.map(function (p) { return doc.getElementById(p.getAttribute('data-cv-pill')); }).filter(Boolean);
		var activeId = null;

		function setActive(id, scrollPill) {
			if (!id || id === activeId) return;
			activeId = id;
			pills.forEach(function (p) {
				var on = p.getAttribute('data-cv-pill') === id;
				p.classList.toggle('is-active', on);
				if (on) {
					if (indicator) {
						indicator.style.width = p.offsetWidth + 'px';
						indicator.style.transform = 'translateX(' + p.offsetLeft + 'px)';
					}
					if (scrollPill !== false && pickerTrack) {
						var target = p.offsetLeft - (pickerTrack.clientWidth - p.offsetWidth) / 2;
						pickerTrack.scrollTo({ left: Math.max(0, target), behavior: reduceMotion ? 'auto' : 'smooth' });
					}
				}
			});
		}

		pills.forEach(function (p) {
			p.addEventListener('click', function (e) {
				var id = p.getAttribute('data-cv-pill');
				var sec = doc.getElementById(id);
				if (!sec) return;
				e.preventDefault();
				setActive(id);
				var offset = (header ? header.offsetHeight : 64) + picker.offsetHeight + 6;
				var top = sec.getBoundingClientRect().top + window.pageYOffset - offset;
				window.scrollTo({ top: top, behavior: reduceMotion ? 'auto' : 'smooth' });
				if (history.replaceState) history.replaceState(null, '', '#' + id);
			});
		});

		if ('IntersectionObserver' in window && sections.length) {
			var spy = new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) setActive(entry.target.id);
				});
			}, { rootMargin: '-45% 0px -50% 0px', threshold: 0 });
			sections.forEach(function (s) { spy.observe(s); });
		}
		if (pills[0]) setActive(pills[0].getAttribute('data-cv-pill'), false);
		window.addEventListener('resize', function () {
			var cur = activeId; activeId = null; setActive(cur, false);
		});
	}

	/* ------------------------------------------------------------------
	 * 7) Hero paralaks + vitrin 3D eğim (yalnızca fare olan cihazlarda)
	 * ------------------------------------------------------------------ */
	if (hero && !reduceMotion) {
		var heroRect = null;
		hero.addEventListener('pointermove', function (e) {
			if (e.pointerType !== 'mouse') return;
			heroRect = heroRect || hero.getBoundingClientRect();
			var px = ((e.clientX - heroRect.left) / heroRect.width - 0.5) * 2;
			var py = ((e.clientY - heroRect.top) / heroRect.height - 0.5) * 2;
			hero.style.setProperty('--px', px.toFixed(3));
			hero.style.setProperty('--py', py.toFixed(3));
		});
		hero.addEventListener('pointerleave', function () {
			heroRect = null;
			hero.style.setProperty('--px', 0);
			hero.style.setProperty('--py', 0);
		});
		window.addEventListener('resize', function () { heroRect = null; });

		// Telefonda cihaz eğimine göre hafif paralaks (izin gerektirmeyen tarayıcılarda)
		if (!canHover && window.DeviceOrientationEvent && typeof DeviceOrientationEvent.requestPermission !== 'function') {
			window.addEventListener('deviceorientation', function (e) {
				if (e.gamma == null) return;
				hero.style.setProperty('--px', clamp(e.gamma / 30, -1, 1).toFixed(3));
				hero.style.setProperty('--py', clamp((e.beta - 40) / 30, -1, 1).toFixed(3));
			}, { passive: true });
		}
	}

	if (canHover && !reduceMotion) {
		$all('[data-cv-tilt]').forEach(function (cas) {
			cas.addEventListener('pointermove', function (e) {
				var r = cas.getBoundingClientRect();
				var x = (e.clientX - r.left) / r.width - 0.5;
				var y = (e.clientY - r.top) / r.height - 0.5;
				cas.style.setProperty('--ry', (x * 3).toFixed(2) + 'deg');
				cas.style.setProperty('--rx', (-y * 2.4).toFixed(2) + 'deg');
			});
			cas.addEventListener('pointerleave', function () {
				cas.style.setProperty('--ry', '0deg');
				cas.style.setProperty('--rx', '0deg');
			});
		});
	}

	/* ------------------------------------------------------------------
	 * 8) Sepete uçan kek + şeker patlaması + küçük bildirim
	 * ------------------------------------------------------------------ */
	var lastAddBtn = null;
	var lastAddAt = 0;

	doc.addEventListener('click', function (e) {
		var btn = e.target.closest ? e.target.closest('.addtocart') : null;
		if (!btn) return;
		lastAddBtn = btn;
		lastAddAt = Date.now();
	}, true);

	function visibleCartTarget() {
		var targets = $all('[data-cv-cart-target]');
		for (var i = targets.length - 1; i >= 0; i--) {
			var r = targets[i].getBoundingClientRect();
			var st = getComputedStyle(targets[i]);
			if (r.width > 0 && r.height > 0 && st.visibility !== 'hidden' && r.bottom > 0 && r.top < window.innerHeight) return targets[i];
		}
		return targets[0] || null;
	}

	function sourceVisual(btn) {
		var card = btn.closest('.cv-cake, .t4-product-card, .t4-product, .product-showcase');
		if (!card) return null;
		return card.querySelector('.cv-cake__img img, .cv-cake__img svg, .t4-product__media img, .product-media-card img, img');
	}

	function flyToCart(btn) {
		var target = visibleCartTarget();
		var src = sourceVisual(btn);
		if (!target) return;
		var bump = function () {
			target.classList.remove('is-bump');
			void target.offsetWidth;
			target.classList.add('is-bump');
			$all('.sm-cart-count-badge').forEach(function (b) {
				b.classList.remove('is-bump');
				void b.offsetWidth;
				b.classList.add('is-bump');
			});
			setTimeout(function () { target.classList.remove('is-bump'); }, 900);
		};
		if (!src || reduceMotion || !Element.prototype.animate) { bump(); return; }

		var from = src.getBoundingClientRect();
		var to = (target.querySelector('svg') || target).getBoundingClientRect();
		if (from.width === 0) { bump(); return; }

		var size = Math.min(from.width, from.height, 140);
		var fly = doc.createElement('div');
		fly.className = 'cv-fly';
		fly.style.width = size + 'px';
		fly.style.height = size + 'px';
		fly.style.left = (from.left + from.width / 2 - size / 2) + 'px';
		fly.style.top = (from.top + from.height / 2 - size / 2) + 'px';
		fly.appendChild(src.cloneNode(true));
		doc.body.appendChild(fly);

		var dx = (to.left + to.width / 2) - (from.left + from.width / 2);
		var dy = (to.top + to.height / 2) - (from.top + from.height / 2);
		var lift = Math.min(160, Math.abs(dy) * 0.5 + 60);

		var anim = fly.animate([
			{ transform: 'translate(0,0) scale(1)', opacity: 1 },
			{ transform: 'translate(' + dx * 0.4 + 'px,' + (dy * 0.4 - lift * 0.5) + 'px) scale(.6)', opacity: 1, offset: 0.5 },
			{ transform: 'translate(' + dx + 'px,' + dy + 'px) scale(.08)', opacity: 0 }
		], { duration: 1000, easing: 'cubic-bezier(.65,0,.35,1)' });
		anim.onfinish = function () { fly.remove(); bump(); };
	}

	function markDone(btn) {
		if (!btn || !btn.classList.contains('cv-cake__add')) return;
		btn.classList.add('is-done');
		setTimeout(function () { btn.classList.remove('is-done'); }, 1600);
	}

	var toastEl = null;
	var toastTimer = null;
	function cvToast(name) {
		if (!toastEl) {
			toastEl = doc.createElement('div');
			toastEl.className = 'cv-toast';
			toastEl.setAttribute('role', 'status');
			doc.body.appendChild(toastEl);
		}
		toastEl.innerHTML = '';
		var icon = doc.createElement('span');
		icon.className = 'cv-toast__icon';
		icon.setAttribute('aria-hidden', 'true');
		var text = doc.createElement('span');
		text.className = 'cv-toast__text';
		var strong = doc.createElement('strong');
		strong.textContent = name || 'Ürün';
		text.appendChild(strong);
		text.appendChild(doc.createTextNode(' sepete eklendi'));
		var link = doc.createElement('a');
		link.href = '#';
		link.className = 'cv-toast__btn js-show-cart';
		link.textContent = 'Sepet';
		toastEl.appendChild(icon);
		toastEl.appendChild(text);
		toastEl.appendChild(link);
		toastEl.classList.remove('is-show');
		void toastEl.offsetWidth;
		toastEl.classList.add('is-show');
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () { toastEl.classList.remove('is-show'); }, 2600);
	}

	// theme4 "sepete eklendi" penceresi: vitrinden hızlı eklemede yerine küçük bildirim göster
	var originalShowCartAdded = window.showCartAdded;
	if (typeof originalShowCartAdded === 'function') {
		window.showCartAdded = function (data, idProduct, idVariation) {
			var fromShelf = lastAddBtn && lastAddBtn.classList.contains('cv-cake__add') && (Date.now() - lastAddAt) < 15000;
			if (fromShelf) {
				var card = lastAddBtn.closest('.cv-cake');
				var nameEl = card ? card.querySelector('.cv-cake__name') : null;
				flyToCart(lastAddBtn);
				markDone(lastAddBtn);
				cvToast(nameEl ? nameEl.textContent.trim() : '');
				lastAddBtn = null;
				return;
			}
			if (lastAddBtn && (Date.now() - lastAddAt) < 15000) {
				flyToCart(lastAddBtn);
				lastAddBtn = null;
			}
			return originalShowCartAdded.apply(this, arguments);
		};
	}
})();
