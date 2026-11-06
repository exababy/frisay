(function () {
	'use strict';

	var root = document.getElementById('aiChatWidget');
	if (!root) return;

	var apiUrl = root.getAttribute('data-api') || '';
	var token = root.getAttribute('data-token') || '';
	var welcome = root.getAttribute('data-welcome') || '';
	var panel = document.getElementById('aiChatPanel');
	var toggle = document.getElementById('aiChatToggle');
	var closeBtn = document.getElementById('aiChatClose');
	var messagesEl = document.getElementById('aiChatMessages');
	var form = document.getElementById('aiChatForm');
	var input = document.getElementById('aiChatInput');
	var sendBtn = document.getElementById('aiChatSend');
	var history = [];
	var busy = false;
	var started = false;

	function openPanel() {
		root.classList.add('is-open');
		if (panel) panel.hidden = false;
		if (toggle) toggle.setAttribute('aria-expanded', 'true');
		if (!started) {
			started = true;
			if (welcome) appendBubble(welcome, 'in');
		}
		setTimeout(function () {
			if (input) input.focus();
		}, 50);
	}

	function closePanel() {
		root.classList.remove('is-open');
		if (panel) panel.hidden = true;
		if (toggle) toggle.setAttribute('aria-expanded', 'false');
	}

	function appendBubble(text, side, extraClass) {
		if (!messagesEl) return null;
		var row = document.createElement('div');
		row.className = 'ai-chat__row ai-chat__row--' + side;
		var bubble = document.createElement('div');
		bubble.className = 'ai-chat__bubble' + (extraClass ? ' ' + extraClass : '');
		bubble.textContent = text;
		row.appendChild(bubble);
		messagesEl.appendChild(row);
		messagesEl.scrollTop = messagesEl.scrollHeight;
		return bubble;
	}

	function setBusy(state) {
		busy = !!state;
		if (sendBtn) sendBtn.disabled = busy;
		if (input) input.disabled = busy;
	}

	function sendMessage(text) {
		if (!apiUrl || busy) return;
		text = String(text || '').trim();
		if (!text) return;

		appendBubble(text, 'out');
		history.push({ role: 'user', content: text });
		if (input) input.value = '';

		var typing = appendBubble('Yazıyor…', 'in', 'ai-chat__bubble--typing');
		setBusy(true);

		var body = new FormData();
		body.append('token', token);
		body.append('message', text);
		body.append('history', JSON.stringify(history.slice(0, -1)));

		fetch(apiUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		})
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (typing && typing.parentNode) {
					typing.parentNode.parentNode.removeChild(typing.parentNode);
				}
				var reply = (res && res.reply) ? String(res.reply) : ((res && res.message) ? String(res.message) : 'Yanıt alınamadı.');
				appendBubble(reply, 'in');
				if (res && res.success) {
					history.push({ role: 'assistant', content: reply });
				}
			})
			.catch(function () {
				if (typing && typing.parentNode) {
					typing.parentNode.parentNode.removeChild(typing.parentNode);
				}
				appendBubble('Bağlantı hatası. Lütfen tekrar deneyin.', 'in');
			})
			.finally(function () {
				setBusy(false);
				if (input) input.focus();
			});
	}

	if (toggle) {
		toggle.addEventListener('click', function () {
			if (root.classList.contains('is-open')) closePanel();
			else openPanel();
		});
	}

	if (closeBtn) {
		closeBtn.addEventListener('click', closePanel);
	}

	if (form) {
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			sendMessage(input ? input.value : '');
		});
	}

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && root.classList.contains('is-open')) {
			closePanel();
		}
	});
})();
