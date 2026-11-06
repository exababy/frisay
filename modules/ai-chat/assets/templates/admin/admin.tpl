<div class="container py-4">
	<div class="row">
		<div class="col-lg-8 mx-auto">
			<div class="card shadow-sm">
				<div class="card-header bg-white">
					<h5 class="mb-0">Yapay Zeka Canlı Destek</h5>
				</div>
				<div class="card-body">
					{if $flash}
					<div class="alert alert-{$flashType|default:'success'|escape} alert-dismissible fade show" role="alert">
						{$flash|escape}
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
					{/if}

					<form method="post" id="aiChatSettingsForm">
						<input type="hidden" name="saveAiChat" value="1">
						<input type="hidden" name="token" value="{$adminToken}">

						<div class="form-check form-switch mb-3">
							<input class="form-check-input" type="checkbox" name="enabled" id="aiChatEnabled" value="1"{if $aiChatEnabled} checked{/if}>
							<label class="form-check-label" for="aiChatEnabled">Widget aktif</label>
						</div>

						<div class="mb-3">
							<label class="form-label" for="aiChatTitle">Başlık</label>
							<input type="text" class="form-control" id="aiChatTitle" name="title" value="{$aiChatTitle|escape}" maxlength="64">
						</div>

						<div class="mb-3">
							<label class="form-label" for="aiChatWelcome">Karşılama mesajı</label>
							<textarea class="form-control" id="aiChatWelcome" name="welcome" rows="2" maxlength="500">{$aiChatWelcome|escape}</textarea>
						</div>

						<hr class="my-4">
						<h6 class="mb-2">Yapay zeka sağlayıcısı</h6>
						<p class="small text-muted mb-3">Ücretsiz seçenekler ücretsiz API anahtarı ister (kart gerekmez). “API’siz” ise anahtarsız çalışır.</p>

						<div class="vstack gap-2 mb-3">
							{foreach $aiChatProviders as $p}
							<label class="border rounded p-3 d-block mb-0{if $aiChatProvider == $p.key} border-success bg-success bg-opacity-10{/if}" style="cursor:pointer">
								<div class="form-check m-0">
									<input class="form-check-input ai-chat-provider" type="radio" name="provider" id="provider_{$p.key|escape}" value="{$p.key|escape}"
										data-base="{$p.base_url|escape}"
										data-model="{$p.model|escape}"
										data-free="{if $p.free}1{else}0{/if}"
										data-needs-key="{if $p.key != 'rules'}1{else}0{/if}"
										{if $aiChatProvider == $p.key} checked{/if}>
									<label class="form-check-label w-100" for="provider_{$p.key|escape}">
										<span class="fw-semibold">{$p.label|escape}</span>
										{if $p.free}<span class="badge text-bg-success ms-1">Ücretsiz</span>{/if}
										<span class="d-block small text-muted mt-1">{$p.hint|escape}</span>
										{if $p.key_url}
										<span class="d-block small mt-1"><a href="{$p.key_url|escape}" target="_blank" rel="noopener" onclick="event.stopPropagation()">API anahtarı al →</a></span>
										{/if}
									</label>
								</div>
							</label>
							{/foreach}
						</div>

						<div id="aiChatApiFields"{if !$aiChatNeedsKey} class="d-none"{/if}>
							<div class="mb-3" id="aiChatCustomFields"{if $aiChatProvider != 'custom'} class="d-none"{/if}>
								<label class="form-label" for="aiChatBaseUrl">API Base URL</label>
								<input type="url" class="form-control mb-2" id="aiChatBaseUrl" name="base_url" value="{$aiChatBaseUrl|escape}" placeholder="https://.../v1">
								<label class="form-label" for="aiChatModel">Model</label>
								<input type="text" class="form-control" id="aiChatModel" name="model" value="{$aiChatModel|escape}" placeholder="model-adı">
							</div>

							<div class="mb-3">
								<label class="form-label" for="aiChatApiKey">API Key</label>
								<input type="password" class="form-control" id="aiChatApiKey" name="api_key" value="{$aiChatApiKeyMasked|escape}" autocomplete="new-password" placeholder="{if $aiChatHasKey}Değiştirmek için yeni anahtar yazın{else}Ücretsiz anahtarınızı yapıştırın{/if}">
								<div class="form-text">{if $aiChatHasKey}Kayıtlı anahtar var. Boş bırakırsanız değişmez.{else}Önerilen: Groq veya Gemini ücretsiz anahtar.{/if}</div>
							</div>
						</div>

						<div class="mb-3">
							<label class="form-label" for="aiChatSystem">Sistem talimatı</label>
							<textarea class="form-control font-monospace" id="aiChatSystem" name="system_prompt" rows="6">{$aiChatSystem|escape}</textarea>
							<div class="form-text">Mağaza iletişim bilgileri ve sipariş bağlamı otomatik eklenir.</div>
						</div>

						<button type="submit" class="btn btn-dark">Kaydet</button>
					</form>
				</div>
				<div class="card-footer bg-light small text-muted">
					Sağ altta WhatsApp tarzı sohbet balonu görünür. Sipariş referansı yazılırsa kargo durumu AI’ya iletilir.
				</div>
			</div>
		</div>
	</div>
</div>
<script>
(function () {
	function sync() {
		var selected = document.querySelector('.ai-chat-provider:checked');
		if (!selected) return;
		var needsKey = selected.getAttribute('data-needs-key') === '1';
		var isCustom = selected.value === 'custom';
		var apiFields = document.getElementById('aiChatApiFields');
		var customFields = document.getElementById('aiChatCustomFields');
		if (apiFields) apiFields.classList.toggle('d-none', !needsKey);
		if (customFields) customFields.classList.toggle('d-none', !isCustom);
		document.querySelectorAll('label.border').forEach(function (el) {
			el.classList.remove('border-success', 'bg-success', 'bg-opacity-10');
		});
		var wrap = selected.closest('label.border');
		if (wrap) wrap.classList.add('border-success', 'bg-success', 'bg-opacity-10');
	}
	document.querySelectorAll('.ai-chat-provider').forEach(function (el) {
		el.addEventListener('change', sync);
	});
	sync();
})();
</script>
