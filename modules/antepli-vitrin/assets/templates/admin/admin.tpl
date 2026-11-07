{if $cvFlash}
<div class="alert alert-{$cvFlashType|escape} cv-admin-flash">{$cvFlash|escape}</div>
{/if}

<div class="cv-admin">
	<div class="cv-admin__head">
		<div class="cv-admin__brand">
			<span class="cv-admin__logo" aria-hidden="true"><img src="{$cvLogoUrl|escape}" alt=""></span>
			<div>
				<h2 class="cv-admin__title">Antepli Vitrin <small>v{$cvVersion|escape}</small></h2>
				<p class="cv-admin__lead">Her ürün grubu ayrı bir vitrin. Vitrin tonlarını, sloganları ve ana sayfa metinlerini buradan yönetin.</p>
			</div>
		</div>
		<div class="cv-admin__status">
			{if !$cvThemeExists}
				<span class="badge bg-danger">templates/antepli bulunamadı</span>
			{elseif $cvThemeActive}
				<span class="badge bg-success">Antepli Ustalar teması aktif</span>
			{else}
				<form method="post" class="d-inline">
					<input type="hidden" name="token" value="{$adminToken}">
					<button type="submit" name="cvActivateTheme" value="1" class="btn btn-sm btn-primary">Antepli Ustalar temasını etkinleştir</button>
				</form>
			{/if}
		</div>
	</div>

	<ul class="nav nav-tabs cv-admin__tabs" role="tablist">
		<li class="nav-item"><a class="nav-link{if $cvTab == 'vitrin'} active{/if}" href="{$moduleConfigUrl}?tab=vitrin">Kategori vitrinleri</a></li>
		<li class="nav-item"><a class="nav-link{if $cvTab == 'genel'} active{/if}" href="{$moduleConfigUrl}?tab=genel">Ana sayfa &amp; genel</a></li>
		<li class="nav-item"><a class="nav-link{if $cvTab == 'moduller'} active{/if}" href="{$moduleConfigUrl}?tab=moduller">Modüller (toplu kurulum)</a></li>
	</ul>

	{if $cvTab == 'vitrin'}
	<div class="admin-panel p-3 cv-admin__panel">
		<p class="text-muted small mb-3">
			Ana sayfada yalnızca <strong>“Ana sayfada göster”</strong> işaretli kategoriler vitrin olarak listelenir (sıra: Kategoriler sayfasındaki ana sayfa sırası).
			Palet seçmezseniz kategori adına göre otomatik renk atanır (ör. “Kerebiç” → fıstık yeşili, “Kahke” → susam, “İzmir Bombası” → vişne).
		</p>
		<form method="post">
			<input type="hidden" name="token" value="{$adminToken}">
			<div class="table-responsive">
				<table class="table align-middle cv-admin__table">
					<thead>
						<tr>
							<th>Kategori</th>
							<th class="text-center">Ana sayfada</th>
							<th>Vitrin paleti</th>
							<th>Slogan</th>
							<th>Kurdele</th>
						</tr>
					</thead>
					<tbody>
					{foreach $cvCategories as $c}
						{assign var=_pal value=$c.style.palette|default:''}
						{assign var=_effective value=$_pal}
						{if $_effective == ''}{assign var=_effective value=$c.auto_palette}{/if}
						<tr{if !$c.active} class="opacity-50"{/if}>
							<td>
								<div class="d-flex align-items-center gap-2">
									<span class="cv-swatch" style="background:{$cvPalettes[$_effective].swatch|default:'#eee'|escape};border-color:{$cvPalettes[$_effective].accent|default:'#ccc'|escape}"></span>
									<div>
										<strong>{$c.category_name|escape}</strong>
										<div class="small text-muted">/{$c.category_link|escape}{if !$c.active} · pasif{/if}</div>
									</div>
								</div>
							</td>
							<td class="text-center">
								<input type="hidden" name="styles[{$c.id_category}][_home_present]" value="1">
								<div class="form-check form-switch d-inline-block">
									<input class="form-check-input" type="checkbox" name="styles[{$c.id_category}][show_on_home]" value="1"{if $c.show_on_home} checked{/if}>
								</div>
							</td>
							<td>
								<select name="styles[{$c.id_category}][palette]" class="form-select form-select-sm">
									<option value="">Otomatik ({$cvPalettes[$c.auto_palette].label|default:$c.auto_palette|escape})</option>
									{foreach $cvPalettes as $key => $p}
									<option value="{$key|escape}"{if $_pal == $key} selected{/if}>{$p.label|escape}</option>
									{/foreach}
								</select>
							</td>
							<td><input type="text" name="styles[{$c.id_category}][tagline]" value="{$c.style.tagline|escape}" class="form-control form-control-sm" maxlength="190" placeholder="Kısa vitrin açıklaması"></td>
							<td><input type="text" name="styles[{$c.id_category}][ribbon]" value="{$c.style.ribbon|escape}" class="form-control form-control-sm" maxlength="64" placeholder="Günün favorisi"></td>
						</tr>
					{foreachelse}
						<tr><td colspan="5" class="text-muted">Henüz kategori yok. “Ana sayfa &amp; genel” sekmesinden demo vitrini yükleyebilirsiniz.</td></tr>
					{/foreach}
					</tbody>
				</table>
			</div>
			<div class="cv-palette-legend">
				{foreach $cvPalettes as $key => $p}
				<span class="cv-palette-legend__item"><span class="cv-swatch" style="background:{$p.swatch|escape};border-color:{$p.accent|escape}"></span>{$p.label|escape}</span>
				{/foreach}
			</div>
			<button type="submit" name="cvSaveStyles" value="1" class="btn btn-dark mt-3">Vitrinleri kaydet</button>
		</form>
	</div>
	{/if}

	{if $cvTab == 'genel'}
	<div class="row g-4">
		<div class="col-lg-8">
			<div class="admin-panel p-3 cv-admin__panel">
				<form method="post" class="row g-3">
					<input type="hidden" name="token" value="{$adminToken}">
					<div class="col-12"><h3 class="h6 mb-0">Duyuru bandı</h3></div>
					<div class="col-12">
						<input type="text" name="AU_ANNOUNCE" value="{$cvSettings.AU_ANNOUNCE|escape}" class="form-control" placeholder="Boş bırakırsanız gizlenir">
					</div>

					<div class="col-12 mt-4"><h3 class="h6 mb-0">Ana sayfa hero</h3></div>
					<div class="col-md-6">
						<label class="form-label">Üst küçük başlık</label>
						<input type="text" name="AU_HERO_EYEBROW" value="{$cvSettings.AU_HERO_EYEBROW|escape}" class="form-control">
					</div>
					<div class="col-md-6">
						<label class="form-label">Başlık</label>
						<input type="text" name="AU_HERO_TITLE" value="{$cvSettings.AU_HERO_TITLE|escape}" class="form-control">
					</div>
					<div class="col-12">
						<label class="form-label">Açıklama</label>
						<textarea name="AU_HERO_TEXT" class="form-control" rows="2">{$cvSettings.AU_HERO_TEXT|escape}</textarea>
					</div>
					<div class="col-12">
						<label class="form-label">Hero görselleri (en fazla 3 adres, her satıra bir tane)</label>
						<textarea name="AU_HERO_IMAGES" class="form-control" rows="3" placeholder="https://…/kerebic.jpg">{$cvSettings.AU_HERO_IMAGES|escape}</textarea>
						<div class="form-text">Boş bırakırsanız her vitrinin ilk ürün fotoğrafı kullanılır. Dikey (portre) fotoğraflar en iyi sonucu verir.</div>
					</div>
					<div class="col-md-4">
						<label class="form-label">Ana buton</label>
						<input type="text" name="AU_HERO_CTA" value="{$cvSettings.AU_HERO_CTA|escape}" class="form-control">
					</div>
					<div class="col-md-4">
						<label class="form-label">İkinci buton</label>
						<input type="text" name="AU_HERO_CTA2" value="{$cvSettings.AU_HERO_CTA2|escape}" class="form-control">
					</div>
					<div class="col-md-4">
						<label class="form-label">İkinci buton linki</label>
						<input type="text" name="AU_HERO_CTA2_URL" value="{$cvSettings.AU_HERO_CTA2_URL|escape}" class="form-control" placeholder="contact veya https://…">
					</div>
					<div class="col-md-4">
						<label class="form-label">Rozet 1</label>
						<input type="text" name="AU_BADGE_1" value="{$cvSettings.AU_BADGE_1|escape}" class="form-control">
					</div>
					<div class="col-md-4">
						<label class="form-label">Rozet 2</label>
						<input type="text" name="AU_BADGE_2" value="{$cvSettings.AU_BADGE_2|escape}" class="form-control">
					</div>
					<div class="col-md-4">
						<label class="form-label">Rozet 3</label>
						<input type="text" name="AU_BADGE_3" value="{$cvSettings.AU_BADGE_3|escape}" class="form-control">
					</div>

					<div class="col-12 mt-4"><h3 class="h6 mb-0">Vitrin bölümü</h3></div>
					<div class="col-md-5">
						<label class="form-label">Başlık</label>
						<input type="text" name="AU_SHOWCASE_TITLE" value="{$cvSettings.AU_SHOWCASE_TITLE|escape}" class="form-control">
					</div>
					<div class="col-md-7">
						<label class="form-label">Açıklama</label>
						<input type="text" name="AU_SHOWCASE_TEXT" value="{$cvSettings.AU_SHOWCASE_TEXT|escape}" class="form-control">
					</div>
					<div class="col-12">
						<label class="form-label">Kayan yazı (• ile ayırın)</label>
						<input type="text" name="AU_MARQUEE" value="{$cvSettings.AU_MARQUEE|escape}" class="form-control">
					</div>

					<div class="col-12 mt-4"><h3 class="h6 mb-0">WhatsApp sipariş butonu</h3></div>
					<div class="col-md-5">
						<label class="form-label">Numara (ülke koduyla)</label>
						<input type="text" name="AU_WHATSAPP" value="{$cvSettings.AU_WHATSAPP|escape}" class="form-control" placeholder="905xxxxxxxxx">
					</div>
					<div class="col-md-7">
						<label class="form-label">Hazır mesaj</label>
						<input type="text" name="AU_WHATSAPP_TEXT" value="{$cvSettings.AU_WHATSAPP_TEXT|escape}" class="form-control">
					</div>

					<div class="col-12 mt-4"><h3 class="h6 mb-0">Footer</h3></div>
					<div class="col-12">
						<textarea name="AU_FOOTER_TEXT" class="form-control" rows="2">{$cvSettings.AU_FOOTER_TEXT|escape}</textarea>
					</div>

					<div class="col-12 mt-4"><h3 class="h6 mb-2">Görünüm</h3>
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="cvMarquee" name="AU_SHOW_MARQUEE" value="1"{if $cvSettings.AU_SHOW_MARQUEE == '1'} checked{/if}>
							<label class="form-check-label" for="cvMarquee">Kayan ürün bandı</label>
						</div>
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="cvSlider" name="AU_SHOW_HOME_SLIDER" value="1"{if $cvSettings.AU_SHOW_HOME_SLIDER == '1'} checked{/if}>
							<label class="form-check-label" for="cvSlider">Slider / Story modüllerini hero altında göster</label>
						</div>
					</div>

					<div class="col-12">
						<button type="submit" name="cvSaveSettings" value="1" class="btn btn-dark">Kaydet</button>
					</div>
				</form>
			</div>
		</div>
		<div class="col-lg-4">
			<div class="admin-panel p-3 cv-admin__panel mb-4">
				<h3 class="h6">Demo vitrin</h3>
				<p class="small text-muted">8 kategori (Kerebiç, Yöresel Kahkeler, Şekerli / Tuzlu Kuru Pasta, Tahinli &amp; Çörekler, İzmir Bombası, Spesiyal &amp; Tartolet, Bayram Paketleri) ve videolarınızdan alınmış görsellerle örnek ürünler ekler. Fiyatlar örnektir. Var olan kayıtlar tekrar eklenmez.</p>
				<form method="post" onsubmit="return confirm('Demo kategoriler ve ürünler eklensin mi?');">
					<input type="hidden" name="token" value="{$adminToken}">
					<button type="submit" name="cvLoadDemo" value="1" class="btn btn-outline-primary w-100">Demo vitrini yükle</button>
				</form>
			</div>
			<div class="admin-panel p-3 cv-admin__panel">
				<h3 class="h6">Güncellemeler</h3>
				<ul class="small text-muted ps-3 mb-0">
					<li>Tema <code>templates/antepli/</code>, modül <code>modules/antepli-vitrin/</code> klasöründedir; FriSay çekirdek güncellemesi bu klasörlere dokunmaz.</li>
					<li>Modül yeni sürüme geçince tablo/ayarlar otomatik güncellenir — yeniden kurmanız gerekmez.</li>
					<li>Temada bulunmayan yeni bir sayfa şablonu gelirse otomatik olarak <code>theme4</code> şablonu kullanılır.</li>
				</ul>
			</div>
		</div>
	</div>
	{/if}

	{if $cvTab == 'moduller'}
	<div class="admin-panel p-3 cv-admin__panel">
		{if $cvBulkLog}
		<div class="alert alert-light border small cv-admin__log">
			{foreach $cvBulkLog as $line}<div>{$line|escape}</div>{/foreach}
		</div>
		{/if}
		<p class="small text-muted">
			Seçili modüller tek seferde kurulur. <strong>Ödeme ve API anahtarı gerektiren modüller pasif kurulur</strong> — ayarlarını girdikten sonra
			Modüller sayfasından etkinleştirin. Böylece kimlik bilgisi girilmemiş bir ödeme yöntemi müşteriye görünmez.
		</p>
		<form method="post">
			<input type="hidden" name="token" value="{$adminToken}">
			<div class="d-flex gap-2 mb-3 flex-wrap">
				<button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.querySelectorAll('.cv-mod-check:not(:disabled)').forEach(function(c){ c.checked = true; })">Kurulu olmayanların hepsini seç</button>
				<button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.querySelectorAll('.cv-mod-check').forEach(function(c){ c.checked = false; })">Seçimi temizle</button>
			</div>
			{assign var=_grp value=''}
			<div class="row g-2">
			{foreach $cvModules as $m}
				{if $m.group_label != $_grp}
					{assign var=_grp value=$m.group_label}
					<div class="col-12 mt-3"><h3 class="h6 mb-1">{$_grp|escape}</h3></div>
				{/if}
				<div class="col-md-6 col-xl-4">
					<label class="cv-mod{if $m.installed} is-installed{/if}">
						<input type="checkbox" class="form-check-input cv-mod-check" name="modules[]" value="{$m.name|escape}"{if $m.installed || $m.skip} disabled{elseif !$m.skip} checked{/if}>
						<span class="cv-mod__body">
							<strong>{$m.title|escape}</strong>
							<span class="cv-mod__meta">
								{$m.name|escape} · v{$m.version|escape}
								{if $m.installed}
									· {if $m.active}<span class="text-success">kurulu, etkin</span>{else}<span class="text-warning">kurulu, pasif</span>{/if}
								{elseif $m.skip}
									· <span class="text-muted">atlanır</span>
								{elseif $m.passive_default}
									· <span class="text-warning">pasif kurulacak</span>
								{else}
									· <span class="text-primary">etkin kurulacak</span>
								{/if}
							</span>
						</span>
					</label>
				</div>
			{/foreach}
			</div>
			<button type="submit" name="cvInstallAll" value="1" class="btn btn-dark mt-4" onclick="return confirm('Seçili modüller kurulsun mu?');">Seçili modülleri kur</button>
		</form>
	</div>
	{/if}
</div>
