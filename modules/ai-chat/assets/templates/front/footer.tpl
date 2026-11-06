<div class="ai-chat" id="aiChatWidget"
	data-api="{$aiChatApiUrl|escape}"
	data-token="{$csrfToken|escape}"
	data-welcome="{$aiChatWelcome|escape}">
	<button type="button" class="ai-chat__fab" id="aiChatToggle" aria-label="{$aiChatTitle|escape}" aria-expanded="false" aria-controls="aiChatPanel">
		<svg class="ai-chat__fab-icon ai-chat__fab-icon--open" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
		<svg class="ai-chat__fab-icon ai-chat__fab-icon--close" xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
	</button>

	<div class="ai-chat__panel" id="aiChatPanel" role="dialog" aria-label="{$aiChatTitle|escape}" hidden>
		<header class="ai-chat__header">
			<span class="ai-chat__title">{$aiChatTitle|escape}</span>
			<button type="button" class="ai-chat__close" id="aiChatClose" aria-label="Kapat">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
			</button>
		</header>

		<div class="ai-chat__messages" id="aiChatMessages" aria-live="polite"></div>

		<form class="ai-chat__composer" id="aiChatForm" autocomplete="off">
			<input type="text" class="ai-chat__input" id="aiChatInput" maxlength="1000" placeholder="Mesajınızı yazın…" required>
			<button type="submit" class="ai-chat__send" id="aiChatSend" aria-label="Gönder">
				<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
			</button>
		</form>
	</div>
</div>
