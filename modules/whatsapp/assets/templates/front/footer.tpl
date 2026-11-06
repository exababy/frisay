<div id="wa-widget" class="wa-widget"
	data-base-url="{$waBaseUrl|escape}"
	data-open="{if $waIsOpen}1{else}0{/if}"
	data-offline="{$waOfflineMessage|escape}">
	<div class="wa-panel" role="dialog" aria-label="WhatsApp sohbet" hidden>
		<header class="wa-panel__head">
			<div class="wa-panel__identity">
				<div class="wa-avatar" aria-hidden="true">
					<span class="wa-avatar__face">
						{if $waAvatar|default:'' != ''}
						<img src="{$waAvatar|escape}" alt="">
						{else}
						<span class="wa-avatar__letter">{$waInitial|escape}</span>
						{/if}
					</span>
					<span class="wa-avatar__dot{if !$waIsOpen} is-away{/if}"></span>
				</div>
				<div class="wa-panel__meta">
					<div class="wa-panel__name" title="{$waName|escape}">{$waName|escape}</div>
					<div class="wa-panel__status" id="wa-status">
						<span class="wa-panel__status-dot{if !$waIsOpen} is-away{/if}"></span>
						{$waStatusLabel|escape}
					</div>
				</div>
			</div>
			<button type="button" class="wa-panel__close" id="wa-close" aria-label="Kapat">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
			</button>
		</header>

		<div class="wa-panel__body">
			<div class="wa-bubble{if !$waIsOpen} wa-bubble--warn{/if}">
				<p class="wa-bubble__text" id="wa-greeting">{if $waIsOpen}{$waGreeting|escape}{else}{$waOfflineMessage|escape}{/if}</p>
				<span class="wa-bubble__time">{$waNow|escape}</span>
			</div>
		</div>

		<div class="wa-panel__compose">
			<input type="text" id="wa-input" class="wa-input" placeholder="Mesaj yazın…" autocomplete="off" maxlength="500" enterkeyhint="send">
			<button type="button" id="wa-send" class="wa-send" aria-label="Gönder" title="WhatsApp’ta gönder">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M3.4 20.6L21 12 3.4 3.4l.1 6.7L15 12l-11.5 1.9.1 6.7z" fill="currentColor"/>
				</svg>
			</button>
		</div>
	</div>

	<button type="button" id="wa-launcher" class="wa-launcher" aria-label="WhatsApp ile yazın" aria-expanded="false">
		<span class="wa-launcher__ring" aria-hidden="true"></span>
		<svg class="wa-launcher__icon" viewBox="0 0 24 24" width="26" height="26" aria-hidden="true">
			<path fill="currentColor" d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm5.25 14.16c-.22.62-1.29 1.13-1.8 1.21-.48.07-1.09.1-1.76-.11a9.1 9.1 0 01-1.6-.59c-2.82-1.22-4.66-4.06-4.8-4.25-.14-.19-1.15-1.53-1.15-2.92 0-1.39.73-2.07.99-2.36.26-.29.57-.36.76-.36h.55c.18 0 .41-.06.64.49.23.56.78 1.92.85 2.06.07.14.11.31.02.49-.09.18-.13.3-.27.46-.13.16-.28.36-.4.49-.13.14-.27.29-.12.57.16.28.7 1.16 1.5 1.88 1.04.92 1.91 1.21 2.19 1.35.28.14.45.12.61-.07.17-.19.7-.82.89-1.1.19-.28.38-.23.64-.14.26.1 1.66.78 1.94.92.28.14.47.21.54.33.07.12.07.69-.15 1.35z"/>
		</svg>
	</button>
</div>
