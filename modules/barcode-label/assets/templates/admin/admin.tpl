{if $flash}
<div class="alert alert-{$flashType|escape}">{$flash|escape}</div>
{/if}

<div class="bl-app" id="blApp"
	data-search-url="{$blSearchUrl|escape}"
	data-config="{$blConfigJson|escape}"
	data-sample="{$blSampleJson|escape}">

	<div class="admin-panel p-3 mb-3 bl-head">
		<div class="bl-head__left">
			<label class="form-label small text-muted mb-1">Şablon</label>
			<select class="form-select" id="blTemplateSelect">
				{foreach $blTemplates as $tpl}
				<option value="{$blBaseUrl|escape}?template={$tpl.id_template}"{if $tpl.id_template == $blCurrent.id_template} selected{/if}>{$tpl.name|escape} — {$tpl.config.width}&times;{$tpl.config.height} mm</option>
				{/foreach}
			</select>
		</div>
		<form method="post" class="bl-head__save" id="blSaveForm">
			<input type="hidden" name="token" value="{$adminToken}">
			<input type="hidden" name="blAction" value="save">
			<input type="hidden" name="id_template" value="{$blCurrent.id_template}" id="blTemplateId">
			<input type="hidden" name="config" id="blConfigInput">
			<div>
				<label class="form-label small text-muted mb-1">Şablon adı</label>
				<input type="text" name="template_name" class="form-control" value="{$blCurrent.name|escape}" maxlength="128" required>
			</div>
			<button type="submit" class="btn btn-dark">Kaydet</button>
			<button type="submit" class="btn btn-outline-dark" id="blSaveAsNew" title="Mevcut tasarımı yeni şablon olarak kaydeder">Yeni olarak kaydet</button>
		</form>
		<div class="bl-head__actions">
			<form method="post" onsubmit="return confirm('Bu şablon silinsin mi?');">
				<input type="hidden" name="token" value="{$adminToken}">
				<input type="hidden" name="blAction" value="delete">
				<input type="hidden" name="id_template" value="{$blCurrent.id_template}">
				<button type="submit" class="btn btn-outline-danger">Sil</button>
			</form>
		</div>
	</div>

	<div class="row g-3">
		<div class="col-xl-3 col-lg-4">
			<div class="admin-panel p-3 mb-3">
				<h2 class="h6 mb-3">Etiket boyutu</h2>
				<div class="row g-2">
					<div class="col-6">
						<label class="form-label small">Genişlik (mm)</label>
						<input type="number" class="form-control form-control-sm" data-cfg="width" min="10" max="200" step="0.5">
					</div>
					<div class="col-6">
						<label class="form-label small">Yükseklik (mm)</label>
						<input type="number" class="form-control form-control-sm" data-cfg="height" min="10" max="200" step="0.5">
					</div>
					<div class="col-12">
						<div class="bl-presets">
							<button type="button" class="btn btn-sm btn-light" data-preset="30x20">30×20</button>
							<button type="button" class="btn btn-sm btn-light" data-preset="40x20">40×20</button>
							<button type="button" class="btn btn-sm btn-light" data-preset="50x25">50×25</button>
							<button type="button" class="btn btn-sm btn-light" data-preset="50x30">50×30</button>
							<button type="button" class="btn btn-sm btn-light" data-preset="58x40">58×40</button>
							<button type="button" class="btn btn-sm btn-light" data-preset="70x40">70×40</button>
							<button type="button" class="btn btn-sm btn-light" data-preset="100x50">100×50</button>
						</div>
					</div>
					<div class="col-7">
						<label class="form-label small">Yazı tipi</label>
						<select class="form-select form-select-sm" data-cfg="font">
							<option>Arial</option>
							<option>Helvetica</option>
							<option>Verdana</option>
							<option>Tahoma</option>
							<option>Georgia</option>
							<option>Courier New</option>
						</select>
					</div>
					<div class="col-5 d-flex align-items-end">
						<div class="form-check mb-1">
							<input type="checkbox" class="form-check-input" id="blBorder" data-cfg="border">
							<label class="form-check-label small" for="blBorder">Çerçeve</label>
						</div>
					</div>
				</div>
			</div>

			<div class="admin-panel p-3 mb-3">
				<h2 class="h6 mb-3">Yazdırma tipi</h2>
				<select class="form-select form-select-sm mb-2" data-cfg="mode">
					<option value="thermal">Termal etiket yazıcı (her sayfa 1 etiket)</option>
					<option value="a4">A4 etiket kağıdı (sayfaya dizer)</option>
				</select>
				<div class="row g-2 bl-sheet" id="blSheetFields">
					<div class="col-6"><label class="form-label small">Sütun</label><input type="number" class="form-control form-control-sm" data-cfg="sheet.cols" min="1" max="10"></div>
					<div class="col-6"><label class="form-label small">Satır</label><input type="number" class="form-control form-control-sm" data-cfg="sheet.rows" min="1" max="30"></div>
					<div class="col-6"><label class="form-label small">Üst boşluk (mm)</label><input type="number" class="form-control form-control-sm" data-cfg="sheet.marginTop" step="0.5" min="0"></div>
					<div class="col-6"><label class="form-label small">Sol boşluk (mm)</label><input type="number" class="form-control form-control-sm" data-cfg="sheet.marginLeft" step="0.5" min="0"></div>
					<div class="col-6"><label class="form-label small">Yatay ara (mm)</label><input type="number" class="form-control form-control-sm" data-cfg="sheet.gapX" step="0.5" min="0"></div>
					<div class="col-6"><label class="form-label small">Dikey ara (mm)</label><input type="number" class="form-control form-control-sm" data-cfg="sheet.gapY" step="0.5" min="0"></div>
					<div class="col-12"><small class="text-muted" id="blSheetInfo"></small></div>
				</div>
			</div>

			<div class="admin-panel p-3">
				<h2 class="h6 mb-2">Etiket öğeleri</h2>
				<p class="small text-muted mb-2">Kutucuk: göster / gizle. Ada tıklayınca öğe seçilir.</p>
				<ul class="bl-elements" id="blElementList"></ul>
			</div>
		</div>

		<div class="col-xl-6 col-lg-8">
			<div class="admin-panel p-3 mb-3">
				<div class="bl-stage-bar">
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-light" data-zoom="-1" title="Uzaklaştır">−</button>
						<button type="button" class="btn btn-light" data-zoom="0" id="blZoomLabel" title="Sığdır">100%</button>
						<button type="button" class="btn btn-light" data-zoom="1" title="Yakınlaştır">+</button>
					</div>
					<div class="form-check form-switch m-0">
						<input type="checkbox" class="form-check-input" id="blGrid" checked>
						<label class="form-check-label small" for="blGrid">Milimetrik ızgara</label>
					</div>
					<span class="small text-muted ms-auto" id="blPreviewName">Önizleme: örnek ürün</span>
				</div>
				<div class="bl-stage" id="blStage">
					<div class="bl-canvas" id="blCanvas"></div>
				</div>
				<p class="small text-muted mb-0 mt-2">
					Öğeleri fareyle sürükleyin. Sağ alt köşedeki tutamaçla genişliği (barkodda yüksekliği de) değiştirin.
					Klavye: ok tuşları 0,5 mm, Shift + ok 2 mm kaydırır.
				</p>
			</div>

			<div class="admin-panel p-3">
				<h2 class="h6 mb-3" id="blPropsTitle">Öğe özellikleri</h2>
				<div id="blProps"><p class="small text-muted mb-0">Düzenlemek için etiketten bir öğe seçin.</p></div>
			</div>
		</div>

		<div class="col-xl-3">
			<div class="admin-panel p-3 mb-3">
				<h2 class="h6 mb-2">Ürün seç</h2>
				<input type="search" class="form-control" id="blSearch" placeholder="Ürün adı, stok kodu veya barkod" autocomplete="off">
				<div class="bl-results" id="blResults"></div>
			</div>

			<div class="admin-panel p-3">
				<div class="d-flex align-items-center justify-content-between mb-2">
					<h2 class="h6 mb-0">Yazdırılacaklar</h2>
					<button type="button" class="btn btn-link btn-sm p-0 text-danger" id="blClear">Temizle</button>
				</div>
				<div class="bl-selected" id="blSelected">
					<p class="small text-muted mb-0">Henüz ürün eklenmedi.</p>
				</div>
				<form method="post" target="_blank" id="blPrintForm" class="mt-3">
					<input type="hidden" name="token" value="{$adminToken}">
					<input type="hidden" name="blAction" value="print">
					<input type="hidden" name="id_template" value="{$blCurrent.id_template}">
					<input type="hidden" name="items" id="blPrintItems">
					<input type="hidden" name="config" id="blPrintConfig">
					<button type="submit" class="btn btn-success w-100" id="blPrintBtn" disabled>
						Etiketleri yazdır <span id="blPrintCount"></span>
					</button>
					<small class="text-muted d-block mt-2">Yazdırma ekranda görünen (kaydedilmemiş olsa bile) tasarımla yapılır.</small>
				</form>
			</div>
		</div>
	</div>
</div>
