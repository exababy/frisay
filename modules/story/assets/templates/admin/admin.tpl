<div class="container-fluid p-0">
	<div class="d-flex align-items-center justify-content-between mb-4">
		<div>
			<h1 class="h3 mb-1 text-gray-800">Story (Hikaye) Yönetimi</h1>
			<p class="text-muted small mb-0">Ana sayfadaki daire story alanına görsel ve video hikayeler ekleyin ve yönetin.</p>
		</div>
		{if $editStory}
		<a href="{$moduleConfigUrl}" class="btn btn-outline-secondary btn-sm">
			<i class="bi bi-plus-circle me-1"></i> Yeni Hikaye Ekle
		</a>
		{/if}
	</div>

	{if $flash}
	<div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
		{$flash|escape}
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
	</div>
	{/if}

	<div class="row g-4">
		{* Sol Kolon: Form *}
		<div class="col-lg-5 col-xl-4">
			<div class="card shadow-sm border-0">
				<div class="card-header bg-white py-3">
					<h6 class="m-0 font-weight-bold text-primary">
						{if $editStory}Hikaye Düzenle (#{$editStory.id_story}){else}Yeni Hikaye Ekle{/if}
					</h6>
				</div>
				<div class="card-body">
					<form method="post" enctype="multipart/form-data">
						<input type="hidden" name="saveStory" value="1">
						<input type="hidden" name="token" value="{$adminToken}">
						<input type="hidden" name="id_story" value="{if $editStory}{$editStory.id_story}{else}0{/if}">

						{* Başlık *}
						<div class="mb-3">
							<label class="form-label fw-bold">Hikaye / Daire Başlığı <span class="text-danger">*</span></label>
							<input type="text" name="title" class="form-control" placeholder="Örn: ELEKTRONİK veya SEZON FIRSATI" value="{if $editStory}{$editStory.title|escape}{/if}" required>
							<div class="form-text">Daire avatarın altında görünecek başlık.</div>
						</div>

						{* Medya Tipi *}
						<div class="mb-3">
							<label class="form-label fw-bold">Medya Tipi</label>
							<select name="media_type" id="storyMediaTypeSelect" class="form-select">
								<option value="image"{if $editStory && $editStory.media_type == 'image'} selected{/if}>Görsel (Resim)</option>
								<option value="video"{if $editStory && $editStory.media_type == 'video'} selected{/if}>Video (MP4 / WebM)</option>
							</select>
						</div>

						{* Kapak Görseli (Avatar) *}
						<div class="mb-3">
							<label class="form-label fw-bold">Kapak Görseli (Daire Avatar)</label>
							{if $editStory && $editStory.cover_url}
							<div class="mb-2 d-flex align-items-center gap-2">
								<img src="{$editStory.cover_url}" class="rounded-circle border" style="width: 50px; height: 50px; object-fit: cover;">
								<span class="small text-muted">Mevcut kapak</span>
							</div>
							{/if}
							<input type="file" name="cover_file" class="form-control" accept="image/*">
							<div class="form-text">Boş bırakılırsa yüklenen medya görseli varsayılan kapak yapılır.</div>
						</div>

						{* Hikaye Medya Dosyası (Tıklanınca Açılacak Medya) *}
						<div class="mb-3">
							<label class="form-label fw-bold">Story Medyası (Tam Ekran Açılan Görsel/Video)</label>
							{if $editStory && $editStory.media_url}
							<div class="mb-2">
								{if $editStory.media_type == 'video'}
								<video src="{$editStory.media_url}" class="rounded border" style="max-height: 80px;" controls muted></video>
								{else}
								<img src="{$editStory.media_url}" class="rounded border" style="max-height: 80px; object-fit: contain;">
								{/if}
							</div>
							{/if}
							<input type="file" name="media_file" id="storyMediaFileInput" class="form-control" accept="image/*,video/mp4,video/webm">
							<div class="form-text" id="storyMediaHelpText">
								Görsel (JPG, PNG, WebP) veya MP4/WebM video yükleyebilirsiniz.
							</div>
						</div>

						{* Yönlendirme Bağlantısı *}
						<div class="mb-3">
							<label class="form-label fw-bold">Hedef Bağlantı (URL)</label>
							<input type="text" name="link_url" class="form-control" placeholder="Örn: kategori-slug veya https://..." value="{if $editStory}{$editStory.link_url|escape}{/if}">
							<div class="form-text">Story tıklandığında veya içindeki butona basılınca gidilecek sayfa.</div>
						</div>

						{* Sıra & Durum *}
						<div class="row g-2 mb-3">
							<div class="col-6">
								<label class="form-label fw-bold">Sıralama</label>
								<input type="number" name="position" class="form-control" value="{if $editStory}{$editStory.position}{else}0{/if}">
							</div>
							<div class="col-6 d-flex align-items-end">
								<div class="form-check form-switch mb-2">
									<input type="checkbox" name="active" value="1" class="form-check-input" id="storyActiveCheck"{if !$editStory || $editStory.active} checked{/if}>
									<label class="form-check-label fw-bold" for="storyActiveCheck">Aktif</label>
								</div>
							</div>
						</div>

						<div class="d-flex gap-2">
							<button type="submit" class="btn btn-primary w-100 fw-bold">
								<i class="bi bi-check-lg me-1"></i> {if $editStory}Güncelle{else}Kaydet{/if}
							</button>
							{if $editStory}
							<a href="{$moduleConfigUrl}" class="btn btn-light border">İptal</a>
							{/if}
						</div>
					</form>
				</div>
			</div>
		</div>

		{* Sağ Kolon: Liste *}
		<div class="col-lg-7 col-xl-8">
			<div class="card shadow-sm border-0">
				<div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
					<h6 class="m-0 font-weight-bold text-dark">Hikaye Listesi ({$stories|@count})</h6>
				</div>
				<div class="card-body p-0">
					{if $stories|@count == 0}
					<div class="text-center py-5 text-muted">
						<i class="bi bi-film fs-1 d-block mb-2 text-secondary"></i>
						Henüz eklenmiş bir hikaye yok.
					</div>
					{else}
					<div class="table-responsive">
						<table class="table table-hover align-middle mb-0">
							<thead class="table-light">
								<tr>
									<th style="width: 50px;">Sıra</th>
									<th style="width: 70px;">Kapak</th>
									<th>Başlık</th>
									<th>Tip</th>
									<th>Bağlantı</th>
									<th>Durum</th>
									<th class="text-end" style="width: 140px;">İşlemler</th>
								</tr>
							</thead>
							<tbody>
								{foreach $stories as $s}
								<tr>
									<td><span class="badge bg-secondary">{$s.position}</span></td>
									<td>
										{if $s.cover_url}
										<img src="{$s.cover_url}" class="rounded-circle border" style="width: 42px; height: 42px; object-fit: cover;">
										{else}
										<div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 42px; height: 42px; font-size: 10px;">
											Yok
										</div>
										{/if}
									</td>
									<td>
										<strong class="text-dark d-block">{$s.title|escape}</strong>
										{if $s.media_url}
										<span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;">Medya Yüklü</span>
										{else}
										<span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 10px;">Sadece Link</span>
										{/if}
									</td>
									<td>
										{if $s.media_type == 'video'}
										<span class="badge bg-purple text-white" style="background-color: #6f42c1;"><i class="bi bi-camera-reels me-1"></i> Video</span>
										{else}
										<span class="badge bg-info text-white"><i class="bi bi-image me-1"></i> Görsel</span>
										{/if}
									</td>
									<td>
										{if $s.link_url}
										<a href="{$s.link_url|escape}" target="_blank" class="text-truncate d-inline-block small" style="max-width: 150px;">{$s.link_url|escape}</a>
										{else}
										<span class="text-muted small">-</span>
										{/if}
									</td>
									<td>
										<form method="post" class="d-inline">
											<input type="hidden" name="toggleStory" value="1">
											<input type="hidden" name="token" value="{$adminToken}">
											<input type="hidden" name="id_story" value="{$s.id_story}">
											<button type="submit" class="btn btn-sm border-0 p-0">
												{if $s.active}
												<span class="badge bg-success">Aktif</span>
												{else}
												<span class="badge bg-secondary">Pasif</span>
												{/if}
											</button>
										</form>
									</td>
									<td class="text-end text-nowrap">
										<a href="{$moduleConfigUrl}?edit={$s.id_story}" class="btn btn-sm btn-outline-primary me-1">Düzenle</a>
										<form method="post" class="d-inline" onsubmit="return confirm('Bu hikayeyi silmek istediğinizden emin misiniz?');">
											<input type="hidden" name="deleteStory" value="1">
											<input type="hidden" name="token" value="{$adminToken}">
											<input type="hidden" name="id_story" value="{$s.id_story}">
											<button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
										</form>
									</td>
								</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
					{/if}
				</div>
			</div>
		</div>
	</div>
</div>
