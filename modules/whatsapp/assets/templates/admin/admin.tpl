{if $waFlash}
<div class="alert alert-{$waFlashType|default:'info'} py-2">{$waFlash|escape}</div>
{/if}

<div class="admin-panel">
	<h2 class="h5 mb-2">WhatsApp İletişim</h2>
	<p class="text-muted mb-3">Mağaza sağ altta sohbet widget’ı gösterir. Mesai saatleri dışında uyarı verir; tıklanınca <code>wa.me</code> açılır.</p>

	{if $waStatusPreview}
	<div class="alert alert-light border mb-3 py-2">
		<strong>Şu an:</strong>
		{if $waStatusPreview.is_open}
			<span class="badge bg-success">Online</span>
		{else}
			<span class="badge bg-secondary">Mesai dışı</span>
			— cevap penceresi: {$waStatusPreview.reply_window|escape}
		{/if}
		{if $waLinkPreview}
		 · <a href="{$waLinkPreview|escape}" target="_blank" rel="noopener">wa.me önizleme</a>
		{/if}
	</div>
	{/if}

	<form method="post">
		<input type="hidden" name="token" value="{$adminToken|escape}">
		<input type="hidden" name="saveWhatsapp" value="1">

		<div class="row g-3">
			<div class="col-md-4">
				<label class="form-label">Durum</label>
				<div class="form-check form-switch">
					<input class="form-check-input" type="checkbox" name="wa_enabled" id="wa_enabled" value="1"{if $waEnabled} checked{/if}>
					<label class="form-check-label" for="wa_enabled">Widget aktif</label>
				</div>
			</div>

			<div class="col-md-4">
				<label class="form-label" for="wa_phone">WhatsApp numarası</label>
				<input type="text" class="form-control" id="wa_phone" name="wa_phone" value="{$waPhone|escape}" placeholder="905xxxxxxxxx" inputmode="tel" autocomplete="off">
				<div class="form-text">Ülke kodu ile, boşluksuz. Örn. 905551112233</div>
			</div>

			<div class="col-md-4">
				<label class="form-label" for="wa_name">Görünen ad</label>
				<input type="text" class="form-control" id="wa_name" name="wa_name" value="{$waName|escape}" maxlength="64">
			</div>

			<div class="col-12">
				<label class="form-label" for="wa_greeting">Mesai içi karşılama mesajı</label>
				<input type="text" class="form-control" id="wa_greeting" name="wa_greeting" value="{$waGreeting|escape}" maxlength="255">
			</div>

			<div class="col-12">
				<label class="form-label">Mesai saatleri (gün gün)</label>
				<div class="table-responsive border rounded">
					<table class="table table-sm align-middle mb-0">
						<thead class="table-light">
							<tr>
								<th style="width:28%">Gün</th>
								<th style="width:16%">Açık</th>
								<th>Başlangıç</th>
								<th>Bitiş</th>
							</tr>
						</thead>
						<tbody>
							{foreach $waDays as $day}
							<tr>
								<td>{$day.label|escape}</td>
								<td>
									<div class="form-check form-switch mb-0">
										<input class="form-check-input" type="checkbox" name="wa_open[{$day.id}]" id="wa_open_{$day.id}" value="1"{if $day.open} checked{/if}>
										<label class="form-check-label" for="wa_open_{$day.id}">Açık</label>
									</div>
								</td>
								<td>
									<input type="time" class="form-control form-control-sm" name="wa_start[{$day.id}]" value="{$day.start|escape}">
								</td>
								<td>
									<input type="time" class="form-control form-control-sm" name="wa_end[{$day.id}]" value="{$day.end|escape}">
								</td>
							</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
				<div class="form-text mt-2">Mesai dışında ziyaretçiye uyarı gösterilir; yine de WhatsApp’a mesaj gönderebilir. Cevap penceresi olarak o günün (veya sonraki açık günün) saatleri yazılır.</div>
			</div>
		</div>

		<div class="mt-4">
			<button type="submit" class="btn btn-primary">Kaydet</button>
		</div>
	</form>
</div>
