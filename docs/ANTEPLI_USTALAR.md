# Antepli Ustalar — Tema + Modül + Plesk Kurulumu

Bu dal, FriSay 3.0.2 üzerine **Antepli Ustalar** (Sıcak Fırından · Patisserie) için şunları ekler:

| Parça | Konum | Ne yapar |
|-------|-------|----------|
| **Antepli Ustalar teması** | `templates/antepli/` | Mobil öncelikli vitrin teması. Mağazadaki gibi her ürün grubu (kerebiç, kahke, kuru pasta, İzmir bombası…) ayrı bir cam vitrinde, alüminyum tepsilerde. Marka renkleri: kahverengi `#3B1F0F`, bej `#F5D7A1`, krem beyaz. |
| **Antepli Vitrin modülü** | `modules/antepli-vitrin/` | Kategori başına vitrin rengi / emoji / slogan / kurdele, ana sayfa metinleri, WhatsApp butonu, tek tıkla tema etkinleştirme, **tüm modülleri toplu kurma**, demo vitrin. |
| **frisay.com'daki eksik modüller** | `modules/ai-chat`, `barcode-label`, `image-optimizer`, `product-files`, `story`, `whatsapp` | frisay.com/modules sayfasında olup repoda bulunmayan modüller. |

frisay.com'daki diğer 25 modül zaten repoda vardı ve repodaki sürümleri sitedekilerden **daha yeni** (ör. `pos` 1.6.4 ↔ 1.4.0, `ai-assistant` 1.2.0 ↔ 1.0.0). Bu yüzden onlara dokunulmadı.

> **Trendyol modülü eklenmedi.** FriSay 3.x'te Trendyol entegrasyonu çekirdeğe gömülü (Admin → **Marketplace**). frisay.com'daki eski `trendyol` modülü (v1.0.0) çekirdekteki sınıflarla aynı adı kullandığı için yüklendiği anda admin panelini fatal hataya düşürüyor.

---

## 1. Tasarım özeti

Yeni nesil, uygulama hissi veren bir pastane vitrini: Bricolage Grotesque kalın başlıklar, vurgu kelimelerinde Instrument Serif italik, Manrope gövde metni; büyük yuvarlak köşeler, hap biçimli butonlar, cam efektli yüzen başlık; espresso / krem / bej renkleri. Emoji ve çizgi film çizimi yok; hareketler yumuşak ve yaylı (sırayla beliren bento kutuları, kayan sekme göstergesi, hover'da yakınlaşan görseller, destekleyen tarayıcılarda sayfalar arası geçiş).

**Ana sayfa**

1. İnce espresso duyuru bandı
2. Header: yüzen cam "ada". Masaüstünde solda logo ve menü (Vitrinler açılır listesi + Ana Menü modülü), sağda arama / favori / hesap ve koyu sepet butonu; mobilde menü, arama, ortada logo, sepet
3. Hero: bento düzeni. Büyük koyu kutuda başlık ("Antep'in ustalığı, *her gün taze.*"), butonlar ve rozetler; yanında üç ürün fotoğrafı kutusu (admin'den girilen adresler, yoksa her vitrinin ilk ürün fotoğrafı ve vitrin adı etiketi), dönen "Sıcak Fırından · Antepli Ustalar" mührü ve vitrin sayısını gösteren kutu
4. Büyük harfli kayan ürün bandı
5. **Vitrinlerimiz**: yüzen hap sekmeler (kayan dolgu göstergesi, scrollspy) ve her kategori için "Vitrin 01, 02…" başlıklı ayrı vitrin. Her vitrin kendi tonunda yuvarlak bir panel; mobilde kaydırılan raf, masaüstünde oklar ve sürükleme
6. "Fırından kapınıza" üç kart, kurumsal / bayram siparişi paneli, yuvarlak köşeli koyu footer
7. Mobilde ekranın altında yüzen koyu menü çubuğu (Ana sayfa, Vitrinler, Sepet, Favoriler, Hesap)

**10 vitrin tonu:** Susam, Fıstık (adaçayı), Kakao (koyu), Tereyağı, Ceviz, Mağaza yeşili, Bordo (koyu), Tahin, Mürdüm, Espresso (koyu). Ton seçilmezse kategori adına göre otomatik atanır ("Kerebiç" → fıstık, "Kahke" → susam, "İzmir Bombası" → bordo, "Bayram" → espresso…).

**Görseli olmayan ürünler** için vitrin renginde ince çizgi illüstrasyon (kahke, kerebiç veya çubuk kahke) gösterilir.

**Kiloyla satış:** FriSay çekirdeğinde "kg" satış birimi yok (adet ve m² var). Kiloyla satılan ürünleri "500 g / 1 kg" paketi olarak tanımlayın ya da ürüne gramaj varyasyonu ekleyin (Ürün → Varyasyonlar: 250 g / 500 g / 1 kg).

**Logo:** Tema, Antepli Ustalar logosuyla gelir (`templates/antepli/img/logo.png`). Admin → Ayarlar → Logo'dan yeni bir logo yüklerseniz otomatik olarak o kullanılır.

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
   - Branch: bu dal (`ccr-1bcefba2-njrrf9`; isterseniz `antepli` gibi bir isimle yeniden adlandırabilirsiniz)
   - Deployment mode: **Automatic**, Deployment path: `httpdocs`
2. İlk çekmeden sonra **File Manager**'dan şu klasörlere yazma izni olduğundan emin olun (Plesk'te genelde zaten öyledir): `config/`, `cache/`, `img/`, `logs/`, `backups/`, `storage/`, `templates/antepli/css/`, `modules/story/assets/uploads/`, `modules/product-files/uploads/`.

> Git kullanmak istemezseniz: bu dalı GitHub'dan ZIP olarak indirip Plesk File Manager ile `httpdocs` içine yükleyip açabilirsiniz.

### 2.3 FriSay kurulumu

1. `https://alanadiniz.com/install/` adresini açın, veritabanı ve yönetici bilgilerini girin.
2. Kurulum bitince `config/env.php` ve `config/installed.lock` oluşur (bunlar repoda yoktur, deploy'da ezilmez).

### 2.4 Antepli Vitrin'i etkinleştirme (tek seferlik)

1. Admin → **Modüller** → **Antepli Vitrin** → **Kur**
   - Kurulum, `antepli` temasını otomatik olarak aktif yapar ve Instagram linki boşsa `instagram.com/antepliustalar` olarak doldurur.
2. Admin → **Modüller → Antepli Vitrin → Yapılandır**:
   - **Modüller (toplu kurulum)** sekmesi → "Seçili modülleri kur": frisay.com'daki tüm modüller tek seferde kurulur.
     - Ödeme (PayTR, iyzico, KuveytTürk, ParamPOS…) ve API anahtarı isteyen modüller (Google, Akakçe, e-Fatura, kargo, reCAPTCHA, yapay zeka…) **pasif** kurulur. Ayarlarını girdikten sonra Modüller sayfasından etkinleştirin. Böylece bilgisi girilmemiş bir ödeme yöntemi müşteriye görünmez.
   - **Kategori vitrinleri** sekmesi: her kategori için ana sayfada gösterme, palet, emoji, slogan, kurdele.
   - **Ana sayfa & genel** sekmesi: duyuru bandı, hero metinleri ve butonları, rozetler, kayan yazı, WhatsApp numarası, footer metni, animasyon anahtarları.
   - Demo istiyorsanız: "Demo vitrini yükle" (8 kategori, videolarınızdan alınmış görsellerle 26 örnek ürün; fiyatlar örnektir). Gerçek ürünlerinizi ekleyince demo ürünleri Ürünler sayfasından silebilirsiniz.
3. Renkleri değiştirmek için: Admin → **Temalar → Antepli Ustalar → Özelleştir** (kahverengi, bej, krem, bakır vurgu, fıstık yeşili).
4. Logo: tema Antepli Ustalar logosuyla gelir; Admin → Ayarlar → Logo'dan yüklerseniz o kullanılır.

### 2.4.1 Ürünleri XML ile aktarma (görseller dahil)

`modules/antepli-vitrin/assets/demo/antepli-urunler.xml`: 8 kategori, 26 ürün, ürün başına 1–2 görsel (videolardan alınmış kareler). Fiyatlar örnektir.

1. Admin → Modüller → **XML Ürün Aktarım** (kurulu değilse Antepli Vitrin → Modüller sekmesinden kurup etkinleştirin)
2. Dosyayı yükleyin **veya** feed adresi olarak girin: `https://alanadiniz.com/modules/antepli-vitrin/assets/demo/antepli-urunler.xml`
3. Eşleştirme: **Stok kodu**. Stok kodları demo ürünleriyle aynıdır; demo yüklüyse ürünler kopyalanmaz, güncellenir.
4. Kategoriler `Katalog >>> …` yolu ile ana kategorinin altına açılır. Yeni açılan kategorileri ana sayfada vitrin olarak göstermek için Antepli Vitrin → Kategori vitrinleri → "Ana sayfada" anahtarını açın.

Notlar: KDV `0.01` (= %1) olarak girildi (modül 0–1 arası değeri oran sayar), muhasebecinizle teyit edin. Görseller sitenizden HTTPS ile indirilir; geçerli bir SSL sertifikası gerekir. Ürün etiketleri ("Çok Satan", "Yeni") XML modülüyle aktarılmaz, ürün düzenleme ekranından eklenir.

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
| **FriSay çekirdek güncellemesi** (Admin → Güncelleme) | Güncelleyici dosyaları üzerine kopyalar ama silmez; `templates/antepli/` ve `modules/antepli-vitrin/` FriSay paketinde olmadığı için aynen kalır. Ayarlar veritabanında olduğu için korunur. |
| **Sepet / ödeme / hesap sayfalarının renkleri** | Bu sayfalar theme4 şablonlarını kullanır; theme4'ün lacivert, turuncu ve soğuk gri renkleri `templates/antepli/css/antepli-skin.css` renk katmanıyla marka tonlarına çevrilir. Bu dosya otomatik üretilir; FriSay güncellemesiyle theme4 CSS'i değişirse Antepli Vitrin modülü onu ilk sayfa açılışında kendiliğinden yeniler. Antepli teması aktifken Tema Edit (theme4) modülünün global renk / yazı tipi CSS'i yüklenmez. |
| **Sepet / ödeme / hesap sayfası düzeltmeleri** | Antepli teması bu sayfalarda `theme4` şablonlarını kullanır. FriSay `theme4`'ü güncellediğinde düzeltmeler antepli temasına da kendiliğinden yansır. |
| **FriSay yeni bir sayfa eklerse** | Modül, antepli temasında bulunmayan şablonu otomatik olarak `theme4` klasöründen yükler. "Şablon bulunamadı" hatası oluşmaz. |
| **Antepli Vitrin modülünün yeni sürümü** | Şema sürümü ayarlarda tutulur. Yeni dosyalar gelince ilk sayfa açılışında eksik tablo, kolon ve varsayılan ayarlar kendiliğinden eklenir, modül sürümü güncellenir. |
| **Git ile yeni sürüm çekme** (Plesk → Git → Pull) | `colors.css`, `custom.css` ve `user.css` (admin panelinde değiştirdiğiniz renk ve CSS'ler) repoda değildir, deploy'da ezilmez. `config/env.php`, ürün görselleri ve story/ürün dosyası yüklemeleri de repoda değildir. |

**Git ile çalışırken dikkat edilecekler**

- Çekirdeği güncellemenin iki yolu var, birini seçin:
  1. **Admin → Güncelleme** butonu (en kolayı). Antepli dosyaları korunur. Ancak sonra Git'ten yeni bir şey çekerseniz Plesk çekirdek dosyalarını repodaki eski haline döndürür. Git ile deploy kullanıyorsanız 2. yolu tercih edin.
  2. **Git üzerinden**: FriSay'in yeni sürümünü bu dala birleştirin (`git merge`), Plesk'te **Pull** + **Deploy** yapın, ardından `https://alanadiniz.com/admin/upgrade` sayfasından bekleyen veritabanı göçlerini çalıştırın. (Antepli Vitrin modülünün kendi göçleri buna gerek kalmadan otomatik çalışır.)
- `.htaccess` repoda bulunur. `config/env.php` içindeki `ADMIN_URI` değerini değiştirdiyseniz her deploy'dan sonra `/admin` adresinden bir kez giriş yapın; FriSay `.htaccess`'i kendiliğinden yeniden senkronlar.

---

## 4. Geliştirici notları

```
templates/antepli/
├── theme.schema.json        # ad, renkler (admin renk editörü), theme4 değişken eşlemesi
├── header.tpl / footer.tpl  # antepli başlık, mobil drawer, alt menü, scriptler
├── home.tpl                 # hero + vitrinler
├── category.tpl             # tek büyük vitrin (çok raflı); brand.tpl bunu kullanır
├── catalog.tpl, productGrid.tpl, search.tpl, special.tpl, favorites.tpl
├── product.tpl              # theme4 ürün altyapısı + antepli sahnesi
├── partials/
│   ├── vitrin.tpl           # tente + tabela + cam + raf (shelf | grid)
│   ├── cake-card.tpl        # raftaki ürün + fiyat etiketi
│   ├── antepli-svg.tpl      # görselsiz ürün çizimi
│   ├── pal.tpl              # kategori → palet çözümü
│   ├── drawer.tpl, bottom-nav.tpl, stylesheets.tpl, toolbar.tpl
├── css/antepli.css          # tüm stil + animasyonlar (paletler: .cv-pal--*)
├── js/antepli.js            # reveal, raf okları, scrollspy, paralaks, uçan kek
└── (diğer .tpl)             # {include file='theme4/…'} — theme4'e devreder
```

- Şablonlar modül kurulu olmasa da çalışır (`{$antepli}` yoksa varsayılan metinler ve otomatik paletler kullanılır).
- Yeni bir palet eklemek için: `css/antepli.css` içine `.cv-pal--yeni { --v-wall …}` bloğu, `AntepliVitrinService::palettes()` içine bir satır.
- Modül sürümü yükseltirken `AntepliVitrinService::VERSION` (ve şema değişiyorsa `SCHEMA_VERSION`) değerini artırın; yeni kolonları `migrate()` içindeki `$columns` listesine ekleyin.
