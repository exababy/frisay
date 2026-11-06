<div data-product-files data-api="{$api_url|escape}" data-product="{$id_product}" data-token="{$admin_token|escape}">
	<div class="mb-4">
		<h3 class="h6">İndirilebilir dosyalar</h3>
		<p class="small text-muted">Ürüne birden fazla PDF, resim veya TXT dosyası ekleyin. Her dosyaya müşterinin göreceği başlığı yazın.</p>
		{if $id_product > 0}
		<div data-document-rows>
			<div class="row g-2 mb-2" data-document-row><div class="col-md-4"><input class="form-control" type="text" data-document-title placeholder="Örn. Kullanım Kılavuzu"></div><div class="col-md-6"><input class="form-control" type="file" data-document-file accept=".pdf,.png,.jpg,.jpeg,.gif,.webp,.txt"></div><div class="col-md-2"><button type="button" class="btn btn-outline-secondary" data-remove-row aria-label="Satırı kaldır">Kaldır</button></div></div>
		</div>
		<div class="d-flex gap-2 mt-2"><button type="button" class="btn btn-sm btn-outline-secondary" data-add-document>Başka dosya ekle</button><button type="button" class="btn btn-sm btn-primary" data-save-documents>Seçilen dosyaları yükle</button><span class="small align-self-center" data-documents-status></span></div>
		{if $documents}<div class="mt-3 small"><strong>Yüklü dosyalar:</strong><ul class="mb-0">{foreach $documents as $doc}<li>{$doc.title|escape} — {$doc.file_name|escape}</li>{/foreach}</ul></div>{/if}
		{else}<div class="alert alert-info mb-0">Önce ürünü kaydedin, ardından dosya yükleyin.</div>{/if}
	</div>
	<hr>
	<div class="mt-3">
		<h3 class="h6">Ürün logosu</h3>
		<p class="small text-muted">Ürün içine logo yüklenebilir. Logo ürün sayfasındaki ürün bilgi alanında gösterilir.</p>
		{if $id_product > 0}<div class="d-flex flex-wrap gap-2 align-items-center"><input class="form-control" style="max-width:420px" type="file" data-logo-file accept=".png,.jpg,.jpeg,.gif,.webp"><button type="button" class="btn btn-outline-primary" data-save-logo>Logoyu yükle</button><span class="small" data-logo-status></span></div>{if $logo_name}<div class="form-text">Mevcut logo: {$logo_name|escape}</div>{/if}{/if}
	</div>
	{if $id_product > 0}
	{literal}
	<script>
	(function () {
		var box = document.querySelector('[data-product-files]'); if (!box || box.dataset.ready) return; box.dataset.ready = '1';
		function send(kind, fill, status) {
			var data = new FormData(); data.set('kind', kind); data.set('id_product', box.dataset.product); data.set('token', box.dataset.token); fill(data);
			status.textContent = 'Yükleniyor…';
			fetch(box.dataset.api, {method:'POST', body:data, credentials:'same-origin'}).then(function(r){return r.json();}).then(function(r){status.textContent=r.message||''; if(r.success) setTimeout(function(){location.reload();},700);}).catch(function(){status.textContent='İstek başarısız oldu';});
		}
		box.querySelector('[data-add-document]').addEventListener('click', function(){var row=box.querySelector('[data-document-row]').cloneNode(true); row.querySelectorAll('input').forEach(function(i){i.value='';}); box.querySelector('[data-document-rows]').appendChild(row);});
		box.addEventListener('click', function(e){if(e.target.matches('[data-remove-row]')){var rows=box.querySelectorAll('[data-document-row]'); if(rows.length>1)e.target.closest('[data-document-row]').remove();}});
		box.querySelector('[data-save-documents]').addEventListener('click', function(){send('documents',function(data){var rows=box.querySelectorAll('[data-document-row]'); rows.forEach(function(row){var file=row.querySelector('[data-document-file]').files[0]; if(file){data.append('documents[]',file); data.append('document_titles[]',row.querySelector('[data-document-title]').value);}});},box.querySelector('[data-documents-status]'));});
		box.querySelector('[data-save-logo]').addEventListener('click', function(){send('logo',function(data){var file=box.querySelector('[data-logo-file]').files[0]; if(file)data.set('logo_file',file);},box.querySelector('[data-logo-status]'));});
	})();
	</script>
	{/literal}
	{/if}
</div>
