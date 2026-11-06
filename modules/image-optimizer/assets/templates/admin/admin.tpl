<script>
    window.optDomain = "{$domain}";
    window.optAdminUrl = "{$adminUrl}";
    window.optApiBase = "{$domain}api/module.php?m=image-optimizer&action=";
</script>

<div class="container-fluid py-3" id="image-optimizer-app">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Görsel Optimizasyon Yönetimi (WebP)</h1>
            <p class="text-muted mb-0 small">Sitenizdeki tüm görselleri otomatik veya manuel olarak optimize edin, disk alanından tasarruf edin.</p>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="ImageOptimizer.rescan()">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                Yeniden Tara
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4" id="optimizerTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-dashboard" type="button">Dashboard</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-library" type="button">Görsel Kütüphanesi</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-settings" type="button">Ayarlar</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-logs" type="button">İşlem Günlükleri</button>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="optimizerTabsContent">
        
        <!-- Tab 1: Dashboard -->
        <div class="tab-pane fade show active" id="tab-dashboard">
            <!-- Alert Info -->
            <div id="conversion-progress-card" class="card border-left-primary shadow mb-4 d-none">
                <div class="card-body">
                    <h5 class="card-title text-primary d-flex justify-content-between align-items-center">
                        <span>Görseller Dönüştürülüyor...</span>
                        <small id="progress-eta" class="text-muted" style="font-size: 13px;"></small>
                    </h5>
                    <div class="progress mb-2" style="height: 20px;">
                        <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;">0%</div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span id="progress-current">Dosya: -</span>
                        <span id="progress-counts">Tamamlanan: 0 / Kalan: 0</span>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-sm btn-warning" onclick="ImageOptimizer.pauseConversion()">Duraklat</button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="ImageOptimizer.stopConversion()">Durdur</button>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="text-muted small text-uppercase fw-bold">Toplam Görsel</div>
                        <div class="h3 fw-bold my-1" id="stat-total">-</div>
                        <div class="text-xs text-muted" id="stat-pending-label">Kuyrukta Bekleyen: -</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="text-muted small text-uppercase fw-bold text-success">Optimize Edilen</div>
                        <div class="h3 fw-bold my-1 text-success" id="stat-converted">-</div>
                        <div class="text-xs text-muted" id="stat-ratio-label">Sıkıştırma Oranı: -</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="text-muted small text-uppercase fw-bold text-info">Kazanılan Disk Alanı</div>
                        <div class="h3 fw-bold my-1 text-info" id="stat-disk-saved">-</div>
                        <div class="text-xs text-muted" id="stat-today-savings">Bugünkü Tasarruf: -</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="text-muted small text-uppercase fw-bold text-danger">Başarısız / Atlanan</div>
                        <div class="h3 fw-bold my-1 text-danger" id="stat-failed-skipped">-</div>
                        <div class="text-xs text-muted">Hatalı: <span id="stat-failed">-</span> / Atlanan: <span id="stat-skipped">-</span></div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Engines Info -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3">Toplu İşlemler</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-primary" onclick="ImageOptimizer.startConversion()">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                Optimizasyonu Başlat
                            </button>
                            <button type="button" class="btn btn-outline-warning" onclick="ImageOptimizer.retryFailed()">Başarısızları Yeniden Dene</button>
                            <button type="button" class="btn btn-outline-danger" onclick="ImageOptimizer.confirmRestore()">Yedekten Geri Yükle</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="ImageOptimizer.confirmClean('delete_webp')">WebP Dosyalarını Sil</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="ImageOptimizer.confirmClean('delete_originals')">Orijinalleri Temizle</button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <h5 class="fw-bold mb-3">Sunucu Durumu</h5>
                        <ul class="list-group list-group-flush small" style="margin-bottom: 0;">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                Aktif Dönüştürücü Motor:
                                <strong id="active-engine" class="text-primary">-</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                Desteklenen Motorlar:
                                <span id="server-engines" class="badge bg-secondary">-</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                Bellek Kullanımı:
                                <span id="php-memory-usage" class="text-muted">-</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Stats Charts -->
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3">En Büyük Görsel Ayrıntıları</h5>
                        <div id="largest-img-details" class="p-3 bg-light rounded text-muted small">Yükleniyor...</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3">Hızlı Durum Özeti</h5>
                        <div class="progress mb-2" style="height: 25px;">
                            <div id="summary-bar-converted" class="progress-bar bg-success" role="progressbar" style="width: 0%;">0% Optimize</div>
                            <div id="summary-bar-skipped" class="progress-bar bg-warning" role="progressbar" style="width: 0%;">0% Atlanan</div>
                            <div id="summary-bar-pending" class="progress-bar bg-secondary" role="progressbar" style="width: 0%;">0% Kuyrukta</div>
                        </div>
                        <p class="text-muted small mt-2">Dönüştürme işlemlerini başlatarak tüm görsellerinizi tek tıkla WebP standardına kavuşturabilirsiniz.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Library -->
        <div class="tab-pane fade" id="tab-library">
            <div class="card border-0 shadow-sm p-4">
                <!-- Library Filter Header -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" id="lib-search" class="form-control" placeholder="Görsel adında veya yolunda ara...">
                    </div>
                    <div class="col-md-3">
                        <select id="lib-status" class="form-select">
                            <option value="">Tüm Durumlar</option>
                            <option value="pending">Bekleyen</option>
                            <option value="converted">Optimize Edilen</option>
                            <option value="skipped">Atlanan</option>
                            <option value="failed">Hatalı</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="lib-sort" class="form-select">
                            <option value="id DESC">En Yeni</option>
                            <option value="original_size DESC">En Büyük Dosyalar</option>
                            <option value="original_size ASC">En Küçük Dosyalar</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary w-100" onclick="ImageOptimizer.loadLibrary(1)">Filtrele</button>
                    </div>
                </div>

                <!-- Library Grid Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th>Görsel</th>
                                <th>Dosya Yolu</th>
                                <th>Orijinal Boyut</th>
                                <th>Optimize Boyut</th>
                                <th>Kazanç %</th>
                                <th>Motor</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="library-table-body">
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Yükleniyor...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small" id="lib-pagination-info">Gösterilen: 0 - 0 / Toplam: 0</div>
                    <ul class="pagination pagination-sm mb-0" id="lib-pagination-list">
                        <!-- Dynamic pagination buttons -->
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Tab 3: Settings -->
        <div class="tab-pane fade" id="tab-settings">
            <form id="optimizerSettingsForm" onsubmit="ImageOptimizer.saveSettings(event)">
                <input type="hidden" name="token" value="{$adminToken}">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm p-4 mb-4">
                            <h5 class="fw-bold mb-3 border-bottom pb-2">Genel Sıkıştırma Ayarları</h5>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Sıkıştırma Kalitesi (Quality)</label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="range" name="compression_quality" class="form-range" min="10" max="100" value="85" oninput="document.getElementById('quality-bubble').innerText = this.value">
                                    <span class="badge bg-primary fs-6" id="quality-bubble" style="width: 45px;">85</span>
                                </div>
                                <div class="form-text">Düşük kalite daha küçük dosya boyutu üretir. Önerilen varsayılan değer 85'tir.</div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Maksimum Genişlik (Resize Width)</label>
                                    <input type="number" name="max_resize_width" class="form-control" placeholder="Örn: 1920 (Boşsa sınırlamaz)">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Maksimum Yükseklik (Resize Height)</label>
                                    <input type="number" name="max_resize_height" class="form-control" placeholder="Örn: 1080 (Boşsa sınırlamaz)">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label d-block fw-bold">Şeffaflığı Koru (PNG Transparency)</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="keep_transparency" id="trans-yes" value="yes" checked>
                                    <label class="form-check-label" for="trans-yes">Evet (PNG şeffaf kalsın)</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="keep_transparency" id="trans-no" value="no">
                                    <label class="form-check-label" for="trans-no">Hayır (Beyaz arka plan ekle)</label>
                                </div>
                            </div>

                            <h5 class="fw-bold mb-3 border-bottom pb-2 pt-2">Meta Veri & EXIF Ayarları</h5>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="strip_metadata" id="meta-strip" value="yes" checked>
                                        <label class="form-check-label" for="meta-strip">Tüm Meta Verileri Temizle</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="strip_exif" id="meta-exif" value="yes" checked>
                                        <label class="form-check-label" for="meta-exif">EXIF Bilgilerini Sil</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="strip_gps" id="meta-gps" value="yes" checked>
                                        <label class="form-check-label" for="meta-gps">Görsel GPS Konumunu Sil</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="strip_camera_info" id="meta-camera" value="yes" checked>
                                        <label class="form-check-label" for="meta-camera">Kamera Ayrıntılarını Sil</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm p-4">
                            <h5 class="fw-bold mb-3 border-bottom pb-2">Taranacak Klasörler & Hariç Tutmalar</h5>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Taranacak Alanlar</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="scan_locations[]" id="loc-img" value="img" checked>
                                        <label class="form-check-label" for="loc-img">img / (Ürünler, logolar)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="scan_locations[]" id="loc-modules" value="modules" checked>
                                        <label class="form-check-label" for="loc-modules">modules / (Modül dosyaları)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="scan_locations[]" id="loc-templates" value="templates" checked>
                                        <label class="form-check-label" for="loc-templates">templates / (Temalar)</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Hariç Tutulacak Klasörler</label>
                                <input type="text" name="exclude_folders" class="form-control" value="cache,logs,backup,vendor,node_modules,tmp,temp,.git">
                                <div class="form-text">Virgülle ayırarak hariç tutulacak klasör adlarını yazın.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Hariç Tutulacak Görsel Adları</label>
                                <input type="text" name="exclude_files" class="form-control" placeholder="Örn: favicon.ico, placeholder.png">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Regex Hariç Tutma Filtresi</label>
                                <input type="text" name="regex_exclusions" class="form-control" placeholder="Örn: /\/banners\//">
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm p-4 mb-4">
                            <h5 class="fw-bold mb-3 border-bottom pb-2">Kütüphane Tercihi</h5>
                            <div class="mb-3">
                                <label class="form-label">Görsel Motoru Önceliği</label>
                                <select name="engine_preference" class="form-select">
                                    <option value="auto">Otomatik Algıla (Önerilen)</option>
                                    <option value="Imagick">Imagick</option>
                                    <option value="GD">GD Library</option>
                                    <option value="ImageMagick CLI">ImageMagick CLI</option>
                                </select>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm p-4 mb-4">
                            <h5 class="fw-bold mb-3 border-bottom pb-2">Dosya Boyutu Sınırları</h5>
                            <div class="mb-3">
                                <label class="form-label">Minimum Dosya Boyutu (KB)</label>
                                <input type="number" name="min_kb" class="form-control" value="0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Maksimum Dosya Boyutu (MB)</label>
                                <input type="number" name="max_mb" class="form-control" value="100">
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm p-4 mb-4">
                            <h5 class="fw-bold mb-3 border-bottom pb-2">Yedekleme & Temizlik</h5>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="backup_originals" id="backup-orig" value="yes" checked>
                                <label class="form-check-label" for="backup-orig">Orijinalleri yedekle (Önerilen)</label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="delete_originals_after_success" id="delete-orig" value="yes">
                                <label class="form-check-label" for="delete-orig">Orijinal görseli sunucudan sil</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Yığın Boyutu (Batch Size)</label>
                                <select name="batch_size" class="form-select">
                                    <option value="25">25 Görsel</option>
                                    <option value="50" selected>50 Görsel</option>
                                    <option value="100">100 Görsel</option>
                                    <option value="250">250 Görsel</option>
                                    <option value="500">500 Görsel</option>
                                    <option value="1000">1000 Görsel</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2">Ayarları Kaydet</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tab 4: Logs -->
        <div class="tab-pane fade" id="tab-logs">
            <div class="card border-0 shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">İşlem Günlükleri Listesi</h5>
                    <div class="d-flex gap-2">
                        <a href="{$domain}api/module.php?m=image-optimizer&action=logs&export=csv" class="btn btn-sm btn-outline-secondary">CSV Dışa Aktar</a>
                        <a href="{$domain}api/module.php?m=image-optimizer&action=logs&export=json" class="btn btn-sm btn-outline-secondary">JSON Dışa Aktar</a>
                        <a href="{$domain}api/module.php?m=image-optimizer&action=logs&export=txt" class="btn btn-sm btn-outline-secondary">TXT Dışa Aktar</a>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="ImageOptimizer.clearLogs()">Logları Temizle</button>
                    </div>
                </div>

                <!-- Logs Box -->
                <div class="p-3 bg-dark text-light rounded font-monospace" style="height: 450px; overflow-y: scroll; font-size: 12px; line-height: 1.6;" id="live-logs-container">
                    <!-- Dynamic logs will be loaded here -->
                    [SYSTEM] Günlük kayıtları yükleniyor...
                </div>
            </div>
        </div>
    </div>

    <!-- Image Details Modal -->
    <div class="modal fade" id="imgDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="detail-modal-title">Görsel Ayrıntıları</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6 text-center bg-light p-3 rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                            <img src="" id="detail-preview-img" class="img-fluid rounded shadow-sm" style="max-height: 100%;">
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-striped small">
                                <tbody>
                                    <tr>
                                        <td><strong>Dosya Yolu:</strong></td>
                                        <td id="detail-file-path" class="text-break">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Orijinal Boyut:</strong></td>
                                        <td id="detail-orig-size">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Optimize Boyut:</strong></td>
                                        <td id="detail-opt-size">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Kazanılan Sıkıştırma:</strong></td>
                                        <td id="detail-compression">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Boyutlar (px):</strong></td>
                                        <td id="detail-dimensions">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Kullanılan Motor:</strong></td>
                                        <td id="detail-engine">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Durum:</strong></td>
                                        <td id="detail-status">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>İşlem Tarihi:</strong></td>
                                        <td id="detail-date">-</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-danger w-100" id="detail-btn-restore" onclick="">Görseli Geri Yükle</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{literal}
<script>
    if (typeof runImageOptimizerInit === 'function') {
        runImageOptimizerInit();
    }
</script>
{/literal}
