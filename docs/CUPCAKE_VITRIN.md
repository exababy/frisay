# Cupcake Vitrin — Tema + Modül + Plesk Kurulumu

Bu dal, FriSay 3.0.2 üzerine şunları ekler:

| Parça | Konum | Ne yapar |
|-------|-------|----------|
| **Cupcake teması** | `templates/cupcake/` | Mobil öncelikli vitrin teması. Her kategori ayrı bir cam vitrin: tente, asılı tabela, spot ışıkları, raflar, asılı fiyat etiketleri. Bol animasyon. |
| **Cupcake Vitrin modülü** | `modules/cupcake-vitrin/` | Kategori başına vitrin rengi / emoji / slogan / kurdele, ana sayfa metinleri, WhatsApp butonu, tek tıkla tema etkinleştirme, **tüm modülleri toplu kurma**, demo vitrin. |
| **frisay.com'daki eksik modüller** | `modules/ai-chat`, `barcode-label`, `image-optimizer`, `product-files`, `story`, `whatsapp` | frisay.com/modules sayfasında olup repoda bulunmayan modüller. |

frisay.com'daki diğer 25 modül zaten repoda vardı ve repodaki sürümleri sitedekilerden **daha yeni** (ör. `pos` 1.6.4 ↔ 1.4.0, `ai-assistant` 1.2.0 ↔ 1.0.0). Bu yüzden onlara dokunulmadı.

> **Trendyol modülü eklenmedi.** FriSay 3.x'te Trendyol entegrasyonu çekirdeğe gömülü (Admin → **Marketplace**). frisay.com'daki eski `trendyol` modülü (v1.0.0) çekirdekteki sınıflarla aynı adı kullandığı için yüklendiği anda admin panelini fatal hataya düşürüyor.

---

## 1. Tasarım özeti

**Ana sayfa (mobil öncelikli)**

1. Duyuru bandı (mobilde kayan yazı)
2. Hero: cam fanus altında, katman katman oluşan animasyonlu cupcake (kalıp → kek → krema katmanları → şekerler → zıplayarak düşen kiraz → inen cam fanus), uçuşan şeker taneleri, fare veya telefon eğimiyle paralaks, kelime kelime yükselen başlık
3. Eğik, kayan lezzet bandı
4. Slider / Story modülleri (isteğe bağlı)
5. **Vitrinlerimiz**: yapışkan vitrin seçici (kayan renkli gösterge, scrollspy), ardından her kategori için ayrı vitrin:
   - Kategorinin paletinde, sallanan çizgili tente
   - Zincirle asılı, sallanarak yerine oturan tabela (emoji, ad, slogan, kurdele)
   - Vitrin açılınca sırayla yanan spot ışıkları
   - Camda periyodik ışık yansıması, masaüstünde fareyi takip eden 3D eğim
   - Rafa sırayla zıplayarak yerleşen kekler ve sallanan fiyat etiketleri
   - Mobilde parmakla kaydırılan raf (scroll-snap), masaüstünde oklar ve sürükleyerek kaydırma
6. "Fırından yeni çıktı" ve "Günün fırsatları" vitrinleri
7. 3 adımda sipariş, özel sipariş CTA'sı, krema damlası kenarlı footer

**Sepete ekleme:** Rafta `+` butonuna basınca şeker taneleri patlar, ürün görseli kavis çizerek sepet ikonuna uçar, sepet "jöle" gibi zıplar, buton ✓ olur ve alttan küçük bir bildirim çıkar.

**Mobil:** Uygulama hissi veren yüzen alt menü (ortada yükseltilmiş sepet), aşağı kaydırınca gizlenir, yukarı kaydırınca geri gelir. Renkli kategori kartlarıyla açılır menü.

**Erişilebilirlik:** İşletim sisteminde "hareketi azalt" açıksa tüm animasyonlar kapanır, içerik doğrudan görünür.

**10 vitrin paleti:** Çilekli Pembe, Bitter Çikolata, Vanilya Kreması, Antep Fıstığı, Yaban Mersini, Tuzlu Karamel, Red Velvet, Limonlu Ferah, Lavanta, Naneli Pastel. Palet seçilmezse kategori adına göre otomatik atanır ("Islak Kek" → çikolata, "Çilekli" → pembe, "Red Velvet" → red velvet …).

**Görseli olmayan ürünler** için vitrin renginde SVG cupcake çizimi gösterilir; vitrin hiçbir zaman boş görünmez.

---

## 2. Plesk üzerinde kurulum

### 2.1 PHP ve alan adı

1. **Websites & Domains → (alan adınız) → PHP Settings**
   - PHP sürümü: **8.1, 8.2 veya 8.3** (7.4+ çalışır, 8.x önerilir)
   - Handler: **FPM application served by Apache** (`.htaccess` kurallarının çalışması için Apache gerekli)
   - Eklentiler: `pdo_mysql`, `mbstring`, `gd`, `zip`, `curl` (Plesk'te varsayılan olarak açık gelir)
   - `memory_limit` 256M, `upload_max_filesize` / `post_max_size` 32M önerilir
2. **Apache & nginx Settings**: "Proxy mode" **açık** kalsın (nginx önde, Apache arkada). Böylece `.htaccess` geçerli olur.
3. **Additional nginx directives** alanına güvenlik için şunu ekleyin (`nginx.conf.example` dosyasının özeti):

   ```nginx
   location ~ ^/(logs|upgrade|cache|backups|config|core|container|storage|lang|tools)(/|$) { deny all; return 403; }
   location ~ ^/(modules|img)/.*\.php$ { deny all; return 403; }
   location ~* \.(sql|tpl|env)$ { deny all; return 403; }
   location ~ /\.(git|svn|ht) { deny all; return 403; }
   ```

4. **SSL/TLS Certificates** → Let's Encrypt ile ücretsiz sertifika alın ve "Redirect from HTTP to HTTPS" seçeneğini açın.
5. **Databases → Add Database**: veritabanı ve kullanıcı oluşturun (utf8mb4).

### 2.2 Dosyaları Git ile çekme (önerilen)

1. **Websites & Domains → Git → Add Repository**
   - Remote repository: `git@github.com:exababy/frisay.git` (özel repo ise Plesk'in gösterdiği SSH public key'ini GitHub → repo → **Settings → Deploy keys** bölümüne ekleyin)
   - Branch: bu dal (`ccr-1bcefba2-njrrf9`; isterseniz `cupcake` gibi bir isimle yeniden adlandırabilirsiniz)
   - Deployment mode: **Automatic**, Deployment path: `httpdocs`
2. İlk çekmeden sonra **File Manager**'dan şu klasörlere yazma izni olduğundan emin olun (Plesk'te genelde zaten öyledir): `config/`, `cache/`, `img/`, `logs/`, `backups/`, `storage/`, `templates/cupcake/css/`, `modules/story/assets/uploads/`, `modules/product-files/uploads/`.

> Git kullanmak istemezseniz: bu dalı GitHub'dan ZIP olarak indirip Plesk File Manager ile `httpdocs` içine yükleyip açabilirsiniz.

### 2.3 FriSay kurulumu

1. `https://alanadiniz.com/install/` adresini açın, veritabanı ve yönetici bilgilerini girin.
2. Kurulum bitince `config/env.php` ve `config/installed.lock` oluşur (bunlar repoda yoktur, deploy'da ezilmez).

### 2.4 Cupcake Vitrin'i etkinleştirme (tek seferlik)

1. Admin → **Modüller** → **Cupcake Vitrin** → **Kur**
   - Kurulum, `cupcake` temasını otomatik olarak aktif yapar.
2. Admin → **Modüller → Cupcake Vitrin → Yapılandır**:
   - **Modüller (toplu kurulum)** sekmesi → "Seçili modülleri kur": frisay.com'daki tüm modüller tek seferde kurulur.
     - Ödeme (PayTR, iyzico, KuveytTürk, ParamPOS…) ve API anahtarı isteyen modüller (Google, Akakçe, e-Fatura, kargo, reCAPTCHA, yapay zeka…) **pasif** kurulur. Ayarlarını girdikten sonra Modüller sayfasından etkinleştirin. Böylece bilgisi girilmemiş bir ödeme yöntemi müşteriye görünmez.
   - **Kategori vitrinleri** sekmesi: her kategori için ana sayfada gösterme, palet, emoji, slogan, kurdele.
   - **Ana sayfa & genel** sekmesi: duyuru bandı, hero metinleri ve butonları, rozetler, kayan yazı, WhatsApp numarası, footer metni, animasyon anahtarları.
   - Demo istiyorsanız: "Demo cupcake vitrinini yükle" (5 kategori + çizilmiş görselleriyle 24 ürün). Gerçek ürünlerinizi ekleyince demo ürünleri Ürünler sayfasından silebilirsiniz.
3. Renkleri değiştirmek için: Admin → **Temalar → Cupcake Vitrin → Özelleştir** (ana renk, kakao, krema, karamel).
4. Logo: Admin → Ayarlar → Logo. Logo yüklemezseniz site adı 🧁 ile yazılır.

### 2.5 Cron görevleri (Plesk → Scheduled Tasks)

Kurulum ekranında gösterilen `SHOP_TOKEN` ile:

| Görev | Sıklık | Komut / URL |
|-------|--------|-------------|
| Döviz fiyatları | Saatlik | `https://alanadiniz.com/api/cron.php?action=currency&token=SHOP_TOKEN` |
| XML feed'ler (Akakçe, Google, FiyatTrend) | 6 saatte bir | `https://alanadiniz.com/api/cron.php?action=feeds&token=SHOP_TOKEN` |
| Terk edilen sepet e-postası | Saatlik | `https://alanadiniz.com/api/cron.php?action=abandoned-cart&token=SHOP_TOKEN` |

Plesk'te "Fetch a URL" tipini seçmeniz yeterli.

---

## 3. Güncellemeler — yeniden kurulum gerekmez

Tasarım gereği hiçbir güncellemede tema veya modülü kaldırıp yeniden kurmanız gerekmez:

| Durum | Ne olur? |
|-------|----------|
| **FriSay çekirdek güncellemesi** (Admin → Güncelleme) | Güncelleyici dosyaları üzerine kopyalar ama silmez; `templates/cupcake/` ve `modules/cupcake-vitrin/` FriSay paketinde olmadığı için aynen kalır. Ayarlar veritabanında olduğu için korunur. |
| **Sepet / ödeme / hesap sayfası düzeltmeleri** | Cupcake teması bu sayfalarda `theme4` şablonlarını kullanır. FriSay `theme4`'ü güncellediğinde düzeltmeler cupcake temasına da kendiliğinden yansır. |
| **FriSay yeni bir sayfa eklerse** | Modül, cupcake temasında bulunmayan şablonu otomatik olarak `theme4` klasöründen yükler. "Şablon bulunamadı" hatası oluşmaz. |
| **Cupcake Vitrin modülünün yeni sürümü** | Şema sürümü ayarlarda tutulur. Yeni dosyalar gelince ilk sayfa açılışında eksik tablo, kolon ve varsayılan ayarlar kendiliğinden eklenir, modül sürümü güncellenir. |
| **Git ile yeni sürüm çekme** (Plesk → Git → Pull) | `colors.css`, `custom.css` ve `user.css` (admin panelinde değiştirdiğiniz renk ve CSS'ler) repoda değildir, deploy'da ezilmez. `config/env.php`, ürün görselleri ve story/ürün dosyası yüklemeleri de repoda değildir. |

**Git ile çalışırken dikkat edilecekler**

- Çekirdeği güncellemenin iki yolu var, birini seçin:
  1. **Admin → Güncelleme** butonu (en kolayı). Cupcake dosyaları korunur. Ancak sonra Git'ten yeni bir şey çekerseniz Plesk çekirdek dosyalarını repodaki eski haline döndürür. Git ile deploy kullanıyorsanız 2. yolu tercih edin.
  2. **Git üzerinden**: FriSay'in yeni sürümünü bu dala birleştirin (`git merge`), Plesk'te **Pull** + **Deploy** yapın, ardından `https://alanadiniz.com/admin/upgrade` sayfasından bekleyen veritabanı göçlerini çalıştırın. (Cupcake Vitrin modülünün kendi göçleri buna gerek kalmadan otomatik çalışır.)
- `.htaccess` repoda bulunur. `config/env.php` içindeki `ADMIN_URI` değerini değiştirdiyseniz her deploy'dan sonra `/admin` adresinden bir kez giriş yapın; FriSay `.htaccess`'i kendiliğinden yeniden senkronlar.

---

## 4. Geliştirici notları

```
templates/cupcake/
├── theme.schema.json        # ad, renkler (admin renk editörü), theme4 değişken eşlemesi
├── header.tpl / footer.tpl  # cupcake başlık, mobil drawer, alt menü, scriptler
├── home.tpl                 # hero + vitrinler
├── category.tpl             # tek büyük vitrin (çok raflı); brand.tpl bunu kullanır
├── catalog.tpl, productGrid.tpl, search.tpl, special.tpl, favorites.tpl
├── product.tpl              # theme4 ürün altyapısı + cupcake sahnesi
├── partials/
│   ├── vitrin.tpl           # tente + tabela + cam + raf (shelf | grid)
│   ├── cake-card.tpl        # raftaki ürün + fiyat etiketi
│   ├── cupcake-svg.tpl      # görselsiz ürün çizimi
│   ├── pal.tpl              # kategori → palet çözümü
│   ├── drawer.tpl, bottom-nav.tpl, stylesheets.tpl, toolbar.tpl
├── css/cupcake.css          # tüm stil + animasyonlar (paletler: .cv-pal--*)
├── js/cupcake.js            # reveal, raf okları, scrollspy, paralaks, uçan kek
└── (diğer .tpl)             # {include file='theme4/…'} — theme4'e devreder
```

- Şablonlar modül kurulu olmasa da çalışır (`{$cupcake}` yoksa varsayılan metinler ve otomatik paletler kullanılır).
- Yeni bir palet eklemek için: `css/cupcake.css` içine `.cv-pal--yeni { --v-wall …}` bloğu, `CupcakeVitrinService::palettes()` içine bir satır.
- Modül sürümü yükseltirken `CupcakeVitrinService::VERSION` (ve şema değişiyorsa `SCHEMA_VERSION`) değerini artırın; yeni kolonları `migrate()` içindeki `$columns` listesine ekleyin.
