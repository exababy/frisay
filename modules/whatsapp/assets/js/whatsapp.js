(function () {
	'use strict';

	var root = document.getElementById('wa-widget');
	if (!root) return;

	root.hidden = false;

	var panel = root.querySelector('.wa-panel');
	var launcher = document.getElementById('wa-launcher');
	var closeBtn = document.getElementById('wa-close');
	var sendBtn = document.getElementById('wa-send');
	var input = document.getElementById('wa-input');
	var baseUrl = root.getAttribute('data-base-url') || '';

	if (!baseUrl) {
		root.hidden = true;
		return;
	}

	function openPanel() {
		if (!panel) return;
		panel.hidden = false;
		if (launcher) launcher.setAttribute('aria-expanded', 'true');
		if (input) {
			setTimeout(function () { input.focus(); }, 80);
		}
	}

	function closePanel() {
		if (!panel) return;
		panel.hidden = true;
		if (launcher) launcher.setAttribute('aria-expanded', 'false');
	}

	function togglePanel() {
		if (!panel) return;
		if (panel.hidden) openPanel();
		else closePanel();
	}

	function openWhatsApp() {
		var text = input ? String(input.value || '').trim() : '';
		var url = baseUrl;
		if (text) {
			url += (url.indexOf('?') >= 0 ? '&' : '?') + 'text=' + encodeURIComponent(text);
		}
		window.open(url, '_blank', 'noopener,noreferrer');
	}

	if (launcher) launcher.addEventListener('click', togglePanel);
	if (closeBtn) closeBtn.addEventListener('click', closePanel);
	if (sendBtn) sendBtn.addEventListener('click', openWhatsApp);
	if (input) {
		input.addEventListener('keydown', function (e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				openWhatsApp();
			}
		});
	}

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && panel && !panel.hidden) {
			closePanel();
		}
	});
})();
