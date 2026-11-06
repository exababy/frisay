function getApiUrl(action) {
    if (window.optApiBase) {
        return window.optApiBase + action;
    }
    let base = window.location.origin + '/';
    if (typeof domain !== 'undefined' && domain) {
        base = domain;
    } else if (typeof adminUrl !== 'undefined' && adminUrl) {
        base = adminUrl.split('/admin')[0] + '/';
    }
    return base.replace(/\/+$/, '/') + 'api/module.php?m=image-optimizer&action=' + action;
}

var ImageOptimizer = window.ImageOptimizer || {
    isProcessing: false,
    isPaused: false,
    processedCount: 0,
    startTime: null,
    totalToProcess: 0,
    currentPage: 1,
    initialScanned: false,

    init() {
        this.bindEvents();
        this.loadDashboard();
        this.loadLibrary(1);
        this.loadLogs();
        this.startStatsPoll();
    },

    bindEvents() {
        const self = this;
        
        // Search & Filter listeners
        const searchInput = document.getElementById('lib-search');
        if (searchInput) {
            searchInput.addEventListener('input', () => self.loadLibrary(1));
        }

        const statusSelect = document.getElementById('lib-status');
        if (statusSelect) {
            statusSelect.addEventListener('change', () => self.loadLibrary(1));
        }

        const sortSelect = document.getElementById('lib-sort');
        if (sortSelect) {
            sortSelect.addEventListener('change', () => self.loadLibrary(1));
        }
    },

    loadDashboard() {
        fetch(getApiUrl('stats'))
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.updateStatsUI(data.stats);

                    // If database is completely empty on load, run initial scan automatically
                    if (data.stats.total === 0 && !this.initialScanned) {
                        this.initialScanned = true;
                        fetch(getApiUrl('scan'))
                            .then(res => res.json())
                            .then(scanData => {
                                this.loadDashboard();
                                this.loadLibrary(1);
                                this.loadLogs();
                            });
                    }
                }
            })
            .catch(err => console.error('Stats load error:', err));
    },

    updateStatsUI(stats) {
        document.getElementById('stat-total').innerText = stats.total;
        document.getElementById('stat-converted').innerText = stats.converted;
        document.getElementById('stat-disk-saved').innerText = this.formatBytes(stats.disk_saved);
        document.getElementById('stat-failed-skipped').innerText = stats.failed + stats.skipped;
        
        document.getElementById('stat-pending-label').innerText = 'Kuyrukta Bekleyen: ' + stats.pending;
        document.getElementById('stat-ratio-label').innerText = 'Sıkıştırma Oranı: %' + stats.average_compression;
        document.getElementById('stat-today-savings').innerText = 'Bugünkü Tasarruf: ' + this.formatBytes(stats.today_savings);
        
        document.getElementById('stat-failed').innerText = stats.failed;
        document.getElementById('stat-skipped').innerText = stats.skipped;

        // Update active engines
        document.getElementById('active-engine').innerText = stats.server_engines.length > 0 ? stats.server_engines[0] : 'Yok';
        document.getElementById('server-engines').innerText = stats.server_engines.join(', ') || 'Yok';

        // Update Largest details
        const largestContainer = document.getElementById('largest-img-details');
        if (largestContainer) {
            if (stats.largest) {
                largestContainer.innerHTML = `
                    <strong>Dosya:</strong> ${stats.largest.file_path}<br>
                    <strong>Orijinal Boyut:</strong> ${this.formatBytes(stats.largest.original_size)}
                `;
            } else {
                largestContainer.innerHTML = 'Görsel bulunamadı.';
            }
        }

        // Update progress bar helper summary
        const total = stats.total || 1;
        const convPct = ((stats.converted / total) * 100).toFixed(1);
        const skipPct = ((stats.skipped / total) * 100).toFixed(1);
        const pendPct = ((stats.pending / total) * 100).toFixed(1);

        const convBar = document.getElementById('summary-bar-converted');
        if (convBar) {
            convBar.style.width = convPct + '%';
            convBar.innerText = convPct + '% Optimize';
        }
        const skipBar = document.getElementById('summary-bar-skipped');
        if (skipBar) {
            skipBar.style.width = skipPct + '%';
            skipBar.innerText = skipPct + '% Atlanan';
        }
        const pendBar = document.getElementById('summary-bar-pending');
        if (pendBar) {
            pendBar.style.width = pendPct + '%';
            pendBar.innerText = pendPct + '% Bekleyen';
        }
    },

    loadLibrary(page = 1) {
        this.currentPage = page;
        const searchInput = document.getElementById('lib-search');
        const statusSelect = document.getElementById('lib-status');
        const sortSelect = document.getElementById('lib-sort');

        const search = searchInput ? searchInput.value : '';
        const status = statusSelect ? statusSelect.value : '';
        const sort = sortSelect ? sortSelect.value : 'id DESC';

        fetch(getApiUrl('images&page=' + page + '&search=' + encodeURIComponent(search) + '&status=' + status + '&sort=' + sort))
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.renderLibraryTable(data.images);
                    this.renderLibraryPagination(data.page, data.total_pages, data.total, data.limit);
                }
            })
            .catch(err => console.error('Library load error:', err));
    },

    renderLibraryTable(images) {
        const tbody = document.getElementById('library-table-body');
        if (!tbody) return;

        if (images.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">Arama kriterlerine uygun görsel bulunamadı.</td></tr>';
            return;
        }

        const domainUrl = window.optDomain || (typeof domain !== 'undefined' ? domain : '/');
        tbody.innerHTML = '';
        images.forEach(img => {
            let statusBadge = '';
            if (img.status === 'converted') {
                statusBadge = '<span class="badge badge-status-converted">Optimize</span>';
            } else if (img.status === 'pending') {
                statusBadge = '<span class="badge badge-status-pending">Bekliyor</span>';
            } else if (img.status === 'skipped') {
                statusBadge = '<span class="badge badge-status-skipped">Atlandı</span>';
            } else {
                statusBadge = '<span class="badge badge-status-failed">Hata</span>';
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div style="width: 40px; height: 40px; border-radius: 6px; overflow:hidden; background:#f1f5f9; display:flex; align-items:center; justify-content:center; cursor: pointer;" onclick="ImageOptimizer.showDetails(${img.id})">
                        <img src="${domainUrl + img.file_path}" style="max-width:100%; max-height:100%; object-fit:cover;">
                    </div>
                </td>
                <td class="text-break" style="max-width: 250px;"><code>${img.file_path}</code></td>
                <td>${img.original_size_formatted}</td>
                <td>${img.optimized_size_formatted}</td>
                <td>${img.status === 'converted' ? '%' + img.saved_percentage : '-'}</td>
                <td><small class="text-muted">${img.engine_used || '-'}</small></td>
                <td>${statusBadge}</td>
                <td>
                    <button type="button" class="btn btn-xs btn-outline-dark" style="font-size:11px; padding: 2px 6px;" onclick="ImageOptimizer.showDetails(${img.id})">Detay</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    },

    renderLibraryPagination(current, totalPages, totalCount, limit) {
        const list = document.getElementById('lib-pagination-list');
        const info = document.getElementById('lib-pagination-info');
        if (!list) return;

        list.innerHTML = '';
        const start = (current - 1) * limit + 1;
        const end = Math.min(current * limit, totalCount);
        if (info) {
            info.innerText = `Gösterilen: ${totalCount > 0 ? start : 0} - ${end} / Toplam: ${totalCount}`;
        }

        if (totalPages <= 1) return;

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = 'page-item ' + (current === 1 ? 'disabled' : '');
        prevLi.innerHTML = `<a class="page-link" href="#" onclick="ImageOptimizer.loadLibrary(${current - 1}); return false;">«</a>`;
        list.appendChild(prevLi);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= current - 2 && i <= current + 2)) {
                const li = document.createElement('li');
                li.className = 'page-item ' + (i === current ? 'active' : '');
                li.innerHTML = `<a class="page-link" href="#" onclick="ImageOptimizer.loadLibrary(${i}); return false;">${i}</a>`;
                list.appendChild(li);
            } else if (i === current - 3 || i === current + 3) {
                const li = document.createElement('li');
                li.className = 'page-item disabled';
                li.innerHTML = `<a class="page-link" href="#">...</a>`;
                list.appendChild(li);
            }
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = 'page-item ' + (current === totalPages ? 'disabled' : '');
        nextLi.innerHTML = `<a class="page-link" href="#" onclick="ImageOptimizer.loadLibrary(${current + 1}); return false;">»</a>`;
        list.appendChild(nextLi);
    },

    loadLogs() {
        fetch(getApiUrl('logs&limit=100'))
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const container = document.getElementById('live-logs-container');
                    if (container) {
                        if (data.logs.length === 0) {
                            container.innerHTML = '[SYSTEM] Henüz işlem kaydı bulunmuyor.';
                            return;
                        }
                        container.innerHTML = data.logs.map(log => {
                            let color = '#ffffff';
                            if (log.level === 'success') color = '#10b981';
                            if (log.level === 'warning') color = '#f59e0b';
                            if (log.level === 'error') color = '#ef4444';

                            return `<div style="color: ${color}">[${log.created_at}] [${log.action.toUpperCase()}] ${log.message}</div>`;
                        }).join('');
                        container.scrollTop = container.scrollHeight;
                    }
                }
            })
            .catch(err => console.error('Logs load error:', err));
    },

    clearLogs() {
        if (!confirm('Tüm günlük kayıtlarını silmek istediğinize emin misiniz?')) return;
        fetch(getApiUrl('clean&type=clear_logs'))
            .then(res => res.json())
            .then(data => {
                this.loadLogs();
            });
    },

    saveSettings(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);

        fetch(getApiUrl('settings'), {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                this.loadDashboard();
            })
            .catch(err => alert('Ayarlar kaydedilirken hata oluştu.'));
    },

    rescan() {
        const btn = event ? event.currentTarget : null;
        if (btn) btn.disabled = true;

        fetch(getApiUrl('scan'))
            .then(res => res.json())
            .then(data => {
                if (btn) btn.disabled = false;
                alert(data.message);
                this.loadDashboard();
                this.loadLibrary(1);
                this.loadLogs();
            })
            .catch(err => {
                if (btn) btn.disabled = false;
                alert('Tarama sırasında hata oluştu.');
            });
    },

    startConversion() {
        if (this.isProcessing) return;

        // Fetch remaining count first
        fetch(getApiUrl('stats'))
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.totalToProcess = data.stats.pending;
                    if (this.totalToProcess === 0) {
                        // Automatically perform a scan first if pending is 0
                        document.getElementById('conversion-progress-card').classList.remove('d-none');
                        document.getElementById('progress-bar').innerText = 'Görseller taranıyor...';
                        document.getElementById('progress-bar').style.width = '100%';
                        document.getElementById('progress-eta').innerText = 'Sistemdeki görseller algılanıyor';

                        fetch(getApiUrl('scan'))
                            .then(res => res.json())
                            .then(scanData => {
                                this.loadDashboard();
                                this.loadLibrary(1);
                                this.loadLogs();

                                // Re-check stats after scan
                                fetch(getApiUrl('stats'))
                                    .then(res => res.json())
                                    .then(newStatsData => {
                                        if (newStatsData.success && newStatsData.stats.pending > 0) {
                                            this.totalToProcess = newStatsData.stats.pending;
                                            this.isProcessing = true;
                                            this.isPaused = false;
                                            this.processedCount = 0;
                                            this.startTime = Date.now();
                                            fetch(getApiUrl('resume')).then(() => this.runConversionBatch());
                                        } else {
                                            document.getElementById('conversion-progress-card').classList.add('d-none');
                                            alert('Sitenizde optimize edilecek uygun görsel bulunamadı veya tüm görseller zaten optimize edilmiş.');
                                        }
                                    });
                            })
                            .catch(err => {
                                document.getElementById('conversion-progress-card').classList.add('d-none');
                                alert('Görsel tarama sırasında hata oluştu.');
                            });
                        return;
                    }

                    this.isProcessing = true;
                    this.isPaused = false;
                    this.processedCount = 0;
                    this.startTime = Date.now();

                    // Show progress panel
                    document.getElementById('conversion-progress-card').classList.remove('d-none');
                    
                    // Resume queue on server
                    fetch(getApiUrl('resume'))
                        .then(() => {
                            this.runConversionBatch();
                        });
                }
            });
    },

    runConversionBatch() {
        if (!this.isProcessing || this.isPaused) return;

        fetch(getApiUrl('convert'))
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const stats = data.stats;
                    this.processedCount += stats.processed;
                    
                    // Update progress UI
                    const remaining = stats.remaining;
                    const total = this.totalToProcess;
                    const completed = total - remaining;
                    const pct = Math.min(100, Math.round((completed / total) * 100));

                    const bar = document.getElementById('progress-bar');
                    bar.style.width = pct + '%';
                    bar.innerText = pct + '%';

                    document.getElementById('progress-counts').innerText = `Tamamlanan: ${completed} / Toplam: ${total}`;
                    
                    // Calculate speed & ETA
                    const elapsedSec = (Date.now() - this.startTime) / 1000;
                    const speed = completed / elapsedSec; // images per second
                    const etaSec = speed > 0 ? remaining / speed : 0;
                    
                    document.getElementById('progress-eta').innerText = `Hız: ${speed.toFixed(1)} görsel/sn | Kalan Süre: ${this.formatTime(etaSec)}`;

                    // Reload stats
                    this.loadDashboard();
                    this.loadLogs();

                    if (remaining > 0) {
                        setTimeout(() => this.runConversionBatch(), 200);
                    } else {
                        this.stopConversion();
                        alert('Tüm dönüştürme işlemleri başarıyla tamamlandı!');
                    }
                } else if (data.paused) {
                    this.isPaused = true;
                    document.getElementById('progress-eta').innerText = 'Kuyruk Duraklatıldı';
                }
            })
            .catch(err => {
                console.error('Batch convert error:', err);
                // Attempt retry after delay
                setTimeout(() => this.runConversionBatch(), 2000);
            });
    },

    pauseConversion() {
        this.isPaused = true;
        fetch(getApiUrl('pause'));
    },

    stopConversion() {
        this.isProcessing = false;
        this.isPaused = false;
        document.getElementById('conversion-progress-card').classList.add('d-none');
        this.loadDashboard();
        this.loadLibrary(1);
    },

    retryFailed() {
        fetch(getApiUrl('retry'))
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                this.loadDashboard();
                this.loadLogs();
            });
    },

    confirmRestore() {
        if (!confirm('Tüm optimize edilmiş görselleri yedeklerinden geri yüklemek istediğinize emin misiniz? Bu işlem üretilen WebP dosyalarını kaldıracaktır.')) return;
        fetch(getApiUrl('restore&type=all'))
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                this.loadDashboard();
                this.loadLibrary(1);
                this.loadLogs();
            });
    },

    confirmClean(type) {
        let msg = '';
        if (type === 'delete_webp') {
            msg = 'Tüm üretilmiş WebP dosyalarını silmek ve görselleri yeniden optimize edilmeye hazır hale getirmek istediğinize emin misiniz?';
        } else if (type === 'delete_originals') {
            msg = 'Yedeği bulunan tüm orijinal görselleri kalıcı olarak silmek istediğinize emin misiniz? (Geri döndürülemez!)';
        }

        if (!confirm(msg)) return;

        fetch(getApiUrl('clean&type=' + type))
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                this.loadDashboard();
                this.loadLibrary(1);
                this.loadLogs();
            });
    },

    showDetails(id) {
        fetch(getApiUrl('images&id=' + id))
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const img = data.image;
                    document.getElementById('detail-modal-title').innerText = 'Görsel Detayları';
                    document.getElementById('detail-preview-img').src = img.url;
                    document.getElementById('detail-file-path').innerText = img.file_path;
                    document.getElementById('detail-orig-size').innerText = img.original_size_formatted;
                    document.getElementById('detail-opt-size').innerText = img.optimized_size_formatted;
                    document.getElementById('detail-compression').innerText = img.status === 'converted' ? '%' + img.saved_percentage + ' Tasarruf' : '-';
                    document.getElementById('detail-dimensions').innerText = (img.original_width && img.original_height) ? `${img.original_width}x${img.original_height} px` : 'Bilinmiyor';
                    document.getElementById('detail-engine').innerText = img.engine_used || '-';
                    document.getElementById('detail-status').innerText = img.status.toUpperCase();
                    document.getElementById('detail-date').innerText = img.updated_at;

                    const btnRestore = document.getElementById('detail-btn-restore');
                    if (img.status === 'converted') {
                        btnRestore.style.display = 'block';
                        btnRestore.onclick = () => {
                            if (confirm('Bu görseli orijinal haline geri yüklemek istiyor musunuz?')) {
                                fetch(getApiUrl('restore&image_id=' + img.id))
                                    .then(res => res.json())
                                    .then(resData => {
                                        alert(resData.message);
                                        bootstrap.Modal.getInstance(document.getElementById('imgDetailsModal')).hide();
                                        ImageOptimizer.loadDashboard();
                                        ImageOptimizer.loadLibrary(ImageOptimizer.currentPage);
                                    });
                            }
                        };
                    } else {
                        btnRestore.style.display = 'none';
                    }

                    const modal = new bootstrap.Modal(document.getElementById('imgDetailsModal'));
                    modal.show();
                }
            });
    },

    startStatsPoll() {
        // Poll stats and memory info every 15 seconds
        setInterval(() => {
            if (!this.isProcessing) {
                this.loadDashboard();
            }
        }, 15000);
    },

    formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    },

    formatTime(seconds) {
        if (seconds <= 0 || isNaN(seconds)) return 'Hesaplanıyor...';
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = Math.floor(seconds % 60);

        let out = '';
        if (h > 0) out += h + 'sa ';
        if (m > 0) out += m + 'dk ';
        out += s + 'sn';
        return out;
    }
};

function runImageOptimizerInit() {
    if (window._imageOptimizerInitialized) return;
    if (document.getElementById('image-optimizer-app')) {
        window._imageOptimizerInitialized = true;
        ImageOptimizer.init();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runImageOptimizerInit);
} else {
    runImageOptimizerInit();
}
