<div class="admin-panel">
	<h2 class="h5">Ürün dosyaları ve logoları</h2>
	<p class="text-muted">Ürün sayfasındaki indirilebilir dosya sekmesinin başlığını belirleyin.</p>
	{if $productFilesSettingsMessage}<div class="alert alert-info">{$productFilesSettingsMessage|escape}</div>{/if}
	<form method="post">
		<input type="hidden" name="saveProductFilesSettings" value="1">
		<input type="hidden" name="token" value="{$productFilesAdminToken|escape}">
		<div class="mb-3"><label class="form-label" for="productFilesTabTitle">Sekme başlığı</label><input class="form-control" id="productFilesTabTitle" type="text" name="tab_title" maxlength="255" value="{$productFilesTabTitle|escape}" placeholder="Detaylı bilgi"></div>
		<button class="btn btn-primary" type="submit">Kaydet</button>
	</form>
</div>
