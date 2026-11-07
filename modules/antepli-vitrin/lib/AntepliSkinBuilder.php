<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN') && PHP_SAPI !== 'cli') {
	exit;
}

/**
 * theme4 CSS'indeki sabit renkleri (lacivert, turuncu, mavi, soğuk griler) Antepli Ustalar
 * renklerine çeviren bir "renk katmanı" üretir: templates/antepli/css/antepli-skin.css
 *
 * - theme4 dosyalarına dokunmaz; aynı seçicileri `.cv-body` önekiyle ezer.
 * - Yalnızca renk / gölge / köşe yuvarlaklığı içeren bildirimleri çıkarır.
 * - FriSay güncellemesiyle theme4 CSS'i değişirse modül boot sırasında yeniden üretir
 *   (isStale), böylece güncellemeden sonra da uyum bozulmaz.
 */
class AntepliSkinBuilder
{
	/** theme4'ten okunacak dosyalar (sıra önemli değil) */
	private const SOURCES = [
		'style.css', 'pages.css', 'account-orders.css', 'account-hub.css', 'auth.css',
		'notifications.css', 'cart-modal.css', 'shopmore.css', 'product.css', 'catalog.css',
	];

	/** Renk eşlemesi: soğuk / yabancı renk → marka tonu */
	private const MAP = [
		// koyu lacivert / kömür metin ve zeminler → kakao
		'#17345f' => '#3b1f0f', '#0f172a' => '#3b1f0f', '#1f2937' => '#3b1f0f', '#111827' => '#3b1f0f',
		'#212529' => '#3b1f0f', '#2d2d2d' => '#3b1f0f', '#334155' => '#4a3426', '#343a40' => '#3b1f0f',
		'#1a1a1a' => '#3b1f0f', '#07355f' => '#24130a', '#0a4a86' => '#3b1f0f', '#000000' => '#24130a',
		// turuncu çağrı butonları → marka kahvesi
		'#f58220' => '#3b1f0f', '#e06e12' => '#24130a', '#fb3d02' => '#3b1f0f', '#fdba74' => '#e3c28d',
		'#ff8040' => '#3b1f0f',
		// maviler / morlar → kahve (vurgu) veya bakır
		'#0d6efd' => '#3b1f0f', '#2563eb' => '#3b1f0f', '#6366f1' => '#3b1f0f', '#4f46e5' => '#3b1f0f',
		'#3d47d9' => '#3b1f0f', '#3239c4' => '#24130a', '#3b82f6' => '#a8804a', '#2f38b8' => '#24130a',
		'#38bdf8' => '#a8804a', '#0dcaf0' => '#a8804a', '#7c3aed' => '#8a6a86', '#0080c0' => '#3b1f0f',
		'#0f766e' => '#5f7a3a',
		// soğuk gri metinler → sıcak kahve-gri
		'#64748b' => '#7a6555', '#475569' => '#6a5546', '#7a8a95' => '#7a6555', '#6b7280' => '#7a6555',
		'#6c757d' => '#7a6555', '#8a939e' => '#8c7766', '#9aa8b5' => '#a8927f', '#6b7c8f' => '#7a6555',
		'#4b5563' => '#6a5546', '#374151' => '#4a3426', '#94a3b8' => '#a8927f', '#9ca3af' => '#a8927f',
		'#adb5bd' => '#b9a693', '#495057' => '#5c4636', '#7e7e7e' => '#7a6555', '#a3a3a3' => '#a8927f',
		'#b0b0b0' => '#b9a693', '#444444' => '#4a3426', '#444' => '#4a3426',
		// soğuk açık zeminler → sıcak kum
		'#f8fafc' => '#f6efe3', '#f1f5f9' => '#f3eadb', '#eef2f6' => '#f3eadb', '#f8fbff' => '#f8f2e8',
		'#eef2ff' => '#f3eadb', '#edf2f7' => '#f3eadb', '#e8eef7' => '#efe4d2', '#f3f6f9' => '#f6efe3',
		'#fafbfc' => '#faf6ef', '#f4f6f8' => '#f6efe3', '#f4f6f9' => '#f6efe3', '#eef1f5' => '#f3eadb',
		'#f3f4f6' => '#f3eadb', '#f8f9fa' => '#f8f2e8', '#e9ecef' => '#efe4d2', '#f3f4f8' => '#f3eadb',
		'#f5f7fa' => '#faf6ef', '#eef4fb' => '#f3eadb', '#ebebff' => '#f3eadb', '#fff8f2' => '#fbf4e8',
		'#f0fdf4' => '#eef1e6', '#dcfce7' => '#e2e8d4',
		// soğuk kenarlıklar → sıcak çizgi
		'#e2e8f0' => '#e7dccb', '#e8edf3' => '#e7dccb', '#e5e7eb' => '#e7dccb', '#dee2e6' => '#e2d5c1',
		'#cbd5e1' => '#d6c6ad', '#e6edf5' => '#e7dccb', '#e8ebf0' => '#e7dccb', '#e8ecf1' => '#e7dccb',
		'#c4c9d4' => '#d6c6ad', '#e8edf2' => '#e7dccb', '#e2e4f0' => '#e7dccb', '#dddddd' => '#e2d5c1',
		'#ddd' => '#e2d5c1', '#eeeeee' => '#eee5d6', '#eee' => '#eee5d6', '#ededed' => '#e7dccb',
		// durum renkleri → yumuşatılmış marka uyumlu
		'#16a34a' => '#5f7a3a', '#198754' => '#5f7a3a', '#22c55e' => '#6f8a45', '#15803d' => '#4f6a2e',
		'#dc3545' => '#a33a32', '#ef4444' => '#a33a32', '#c0392b' => '#a33a32', '#dc2626' => '#a33a32',
		'#f87171' => '#c46a5f', '#f59e0b' => '#b8873a', '#ffc107' => '#c9a14a',
	];

	/** rgba(r,g,b,…) taban eşlemesi (alfa korunur) */
	private const RGB_MAP = [
		'23,52,95' => '59,31,15', '15,23,42' => '36,19,10', '13,110,253' => '168,128,74',
		'37,99,235' => '59,31,15', '61,71,217' => '59,31,15', '245,130,32' => '59,31,15',
		'26,26,26' => '36,19,10', '252,211,77' => '245,215,161', '22,163,74' => '95,122,58',
	];

	private const COLOR_PROPS = [
		'color', 'background', 'background-color', 'background-image', 'border', 'border-color',
		'border-top', 'border-right', 'border-bottom', 'border-left', 'border-top-color',
		'border-right-color', 'border-bottom-color', 'border-left-color', 'box-shadow', 'outline',
		'outline-color', 'fill', 'stroke', 'text-decoration-color', 'caret-color', 'accent-color',
		'--bs-btn-bg', '--bs-btn-border-color', '--bs-btn-hover-bg', '--bs-btn-hover-border-color',
		'--bs-btn-active-bg', '--bs-btn-active-border-color', '--bs-btn-color', '--bs-link-color',
		'--bs-link-hover-color', '--bs-primary', '--bs-primary-rgb', '--bs-focus-ring-color',
	];

	public static function themeCssDir(): string
	{
		return dirname(__DIR__, 3) . '/templates/theme4/css';
	}

	public static function outputPath(): string
	{
		return dirname(__DIR__, 3) . '/templates/antepli/css/antepli-skin.css';
	}

	/** theme4 CSS'lerinden biri katmandan yeniyse true */
	public static function isStale(): bool
	{
		$out = self::outputPath();

		if (!is_file($out)) {
			return true;
		}

		$built = (int) filemtime($out);

		foreach (self::SOURCES as $file) {
			$path = self::themeCssDir() . '/' . $file;

			if (is_file($path) && (int) filemtime($path) > $built) {
				return true;
			}
		}

		return false;
	}

	/** @return array{success: bool, message: string, rules?: int} */
	public static function build(): array
	{
		$chunks = [];
		$total = 0;

		foreach (self::SOURCES as $file) {
			$path = self::themeCssDir() . '/' . $file;

			if (!is_file($path)) {
				continue;
			}

			$css = (string) file_get_contents($path);
			$css = preg_replace('#/\*.*?\*/#s', '', $css) ?? '';
			$count = 0;
			$out = self::processBlock($css, $count);

			if (trim($out) !== '') {
				$chunks[] = "/* --- theme4/css/{$file} --- */\n" . $out;
				$total += $count;
			}
		}

		$header = "/**\n * Antepli Ustalar — theme4 renk katmanı (OTOMATİK ÜRETİLİR, elle düzenlemeyin).\n"
			. " * Kaynak: templates/theme4/css/*  ·  Üretici: modules/antepli-vitrin/lib/AntepliSkinBuilder.php\n"
			. " * theme4 değiştiğinde Antepli Vitrin modülü bu dosyayı kendiliğinden yeniler.\n */\n";

		$written = @file_put_contents(self::outputPath(), $header . implode("\n", $chunks));

		if ($written === false) {
			return ['success' => false, 'message' => 'antepli-skin.css yazılamadı (templates/antepli/css yazılabilir olmalı).'];
		}

		return ['success' => true, 'message' => 'Renk katmanı oluşturuldu (' . $total . ' kural).', 'rules' => $total];
	}

	/**
	 * Düz CSS veya @media içi bloğu işler.
	 */
	private static function processBlock(string $css, int &$count): string
	{
		$out = '';
		$len = strlen($css);
		$i = 0;

		while ($i < $len) {
			$open = strpos($css, '{', $i);

			if ($open === false) {
				break;
			}

			$prelude = trim(substr($css, $i, $open - $i));

			// "@charset ...;" / "@import ...;" gibi önceki ifadeleri at
			if (($semi = strrpos($prelude, ';')) !== false) {
				$prelude = trim(substr($prelude, $semi + 1));
			}

			$close = self::matchBrace($css, $open);

			if ($close === -1) {
				break;
			}

			$body = substr($css, $open + 1, $close - $open - 1);
			$i = $close + 1;

			if ($prelude === '') {
				continue;
			}

			if ($prelude[0] === '@') {
				$lower = strtolower($prelude);

				if (strpos($lower, '@media') === 0 || strpos($lower, '@supports') === 0) {
					$inner = self::processBlock($body, $count);

					if (trim($inner) !== '') {
						$out .= $prelude . " {\n" . $inner . "}\n";
					}
				}

				// @keyframes, @font-face, @import vb. atlanır
				continue;
			}

			$decls = self::transformDeclarations($body);

			if ($decls === '') {
				continue;
			}

			$selector = self::prefixSelectors($prelude);

			if ($selector === '') {
				continue;
			}

			$out .= $selector . " { " . $decls . " }\n";
			$count++;
		}

		return $out;
	}

	private static function matchBrace(string $css, int $open): int
	{
		$depth = 0;
		$len = strlen($css);

		for ($j = $open; $j < $len; $j++) {
			if ($css[$j] === '{') {
				$depth++;
			} elseif ($css[$j] === '}') {
				$depth--;

				if ($depth === 0) {
					return $j;
				}
			}
		}

		return -1;
	}

	private static function prefixSelectors(string $prelude): string
	{
		$parts = [];

		foreach (explode(',', $prelude) as $sel) {
			$sel = trim(preg_replace('/\s+/', ' ', $sel) ?? '');

			if ($sel === ':root' || $sel === '[data-bs-theme=light]' || $sel === ':root,[data-bs-theme=light]') {
				// Bootstrap renk değişkenleri: body.cv-body üzerinde yeniden tanımlanır
				$parts[] = 'body.cv-body';
				continue;
			}

			if ($sel === '' || strpos($sel, ':root') === 0 || strpos($sel, '[data-bs-theme=dark]') !== false || $sel === 'html' || strpos($sel, '::-webkit') !== false) {
				continue;
			}

			if (preg_match('/^body\b/i', $sel)) {
				$parts[] = preg_replace('/^body/i', 'body.cv-body', $sel);
			} elseif (preg_match('/^html\b/i', $sel)) {
				continue;
			} else {
				$parts[] = '.cv-body ' . $sel;
			}
		}

		return implode(', ', array_unique($parts));
	}

	private static function transformDeclarations(string $body): string
	{
		$result = [];

		foreach (explode(';', $body) as $decl) {
			$pos = strpos($decl, ':');

			if ($pos === false) {
				continue;
			}

			$prop = strtolower(trim(substr($decl, 0, $pos)));
			$value = trim(substr($decl, $pos + 1));

			if ($value === '') {
				continue;
			}

			$isColorVar = strpos($prop, '--') === 0 && preg_match('/#[0-9a-f]{3,6}\b|rgba?\(|^\s*\d+\s*,\s*\d+\s*,\s*\d+\s*$/i', $value);

			if ($isColorVar || in_array($prop, self::COLOR_PROPS, true)) {
				$new = self::mapColors($value, $prop);

				if ($new !== $value) {
					$result[] = $prop . ': ' . $new;
				}

				continue;
			}

			if (in_array($prop, ['border-radius', 'border-top-left-radius', 'border-top-right-radius', 'border-bottom-left-radius', 'border-bottom-right-radius'], true)) {
				$new = self::mapRadius($value);

				if ($new !== $value) {
					$result[] = $prop . ': ' . $new;
				}
			}
		}

		return implode('; ', $result) . ($result !== [] ? ';' : '');
	}

	private static function mapColors(string $value, string $prop): string
	{
		$isBackground = strpos($prop, 'background') === 0;

		$value = preg_replace_callback('/#([0-9a-fA-F]{6}|[0-9a-fA-F]{3})\b/', static function (array $m) use ($isBackground): string {
			$hex = '#' . strtolower($m[1]);

			// Beyaz kart zeminleri krem beyaza (kremle uyumlu); beyaz metin olduğu gibi kalır
			if ($isBackground && ($hex === '#fff' || $hex === '#ffffff')) {
				return '#fffcf7';
			}

			if (isset(self::MAP[$hex])) {
				return self::MAP[$hex];
			}

			return self::warmHex($hex) ?? $m[0];
		}, $value) ?? $value;

		$value = preg_replace_callback('/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(,\s*[\d.]+\s*)?\)/i', static function (array $m): string {
			$key = $m[1] . ',' . $m[2] . ',' . $m[3];

			$alpha = isset($m[4]) ? $m[4] : '';

			if (isset(self::RGB_MAP[$key])) {
				return ($alpha !== '' ? 'rgba(' : 'rgb(') . self::RGB_MAP[$key] . $alpha . ')';
			}

			$warm = self::warmRgb((int) $m[1], (int) $m[2], (int) $m[3]);

			if ($warm === null) {
				return $m[0];
			}

			return ($alpha !== '' ? 'rgba(' : 'rgb(') . implode(',', $warm) . $alpha . ')';
		}, $value) ?? $value;

		// --bs-*-rgb değişkenleri ("13, 110, 253" biçimi)
		if (preg_match('/^\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*$/', $value, $t)) {
			$key = $t[1] . ',' . $t[2] . ',' . $t[3];

			if ($key === '13,110,253') {
				return '59, 31, 15';
			}

			$warm = isset(self::RGB_MAP[$key]) ? explode(',', self::RGB_MAP[$key]) : self::warmRgb((int) $t[1], (int) $t[2], (int) $t[3]);

			return $warm === null ? $value : implode(', ', $warm);
		}

		return $value;
	}

	/** Haritada olmayan renkler için genel kural: soğuk tonları sıcak kahve/bej ailesine kaydır */
	private static function warmHex(string $hex): ?string
	{
		$h = ltrim($hex, '#');

		if (strlen($h) === 3) {
			$h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
		}

		$warm = self::warmRgb(hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2)));

		return $warm === null ? null : sprintf('#%02x%02x%02x', $warm[0], $warm[1], $warm[2]);
	}

	/** @return array{0:int,1:int,2:int}|null  Değişmesi gerekmiyorsa null */
	private static function warmRgb(int $r, int $g, int $b): ?array
	{
		$max = max($r, $g, $b);
		$min = min($r, $g, $b);

		// saf beyaz / siyah dokunulmaz
		if ($max === $min && ($max === 255 || $max === 0)) {
			return null;
		}

		[$hue, $sat, $light] = self::rgbToHsl($r, $g, $b);

		// Nötr / soğuk griler: hafifçe sıcak gri
		if ($sat < 0.12) {
			if ($b <= $r) {
				return null;
			}

			return self::hslToRgb(32, 0.14, $light);
		}

		// Camgöbeği, mavi, mor, eflatun (170°–320°) → kahve/bej (aynı açıklıkta)
		if ($hue >= 170 && $hue <= 320) {
			return self::hslToRgb(28, min(0.45, $sat * 0.55), $light);
		}

		return null;
	}

	/** @return array{0:float,1:float,2:float} */
	private static function rgbToHsl(int $r, int $g, int $b): array
	{
		$r /= 255; $g /= 255; $b /= 255;
		$max = max($r, $g, $b);
		$min = min($r, $g, $b);
		$l = ($max + $min) / 2;

		if ($max === $min) {
			return [0.0, 0.0, $l];
		}

		$d = $max - $min;
		$s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

		if ($max === $r) {
			$h = fmod((($g - $b) / $d) + 6, 6);
		} elseif ($max === $g) {
			$h = (($b - $r) / $d) + 2;
		} else {
			$h = (($r - $g) / $d) + 4;
		}

		return [$h * 60, $s, $l];
	}

	/** @return array{0:int,1:int,2:int} */
	private static function hslToRgb(float $h, float $s, float $l): array
	{
		$c = (1 - abs(2 * $l - 1)) * $s;
		$x = $c * (1 - abs(fmod($h / 60, 2) - 1));
		$m = $l - $c / 2;

		if ($h < 60) { [$r, $g, $b] = [$c, $x, 0]; }
		elseif ($h < 120) { [$r, $g, $b] = [$x, $c, 0]; }
		elseif ($h < 180) { [$r, $g, $b] = [0, $c, $x]; }
		elseif ($h < 240) { [$r, $g, $b] = [0, $x, $c]; }
		elseif ($h < 300) { [$r, $g, $b] = [$x, 0, $c]; }
		else { [$r, $g, $b] = [$c, 0, $x]; }

		return [(int) round(($r + $m) * 255), (int) round(($g + $m) * 255), (int) round(($b + $m) * 255)];
	}

	/** Büyük yumuşak köşeler (8–40px) → sade 4px; hap (999px) ve daire (50%) korunur */
	private static function mapRadius(string $value): string
	{
		return preg_replace_callback('/(\d+(?:\.\d+)?)px/', static function (array $m): string {
			$px = (float) $m[1];

			if ($px >= 8 && $px <= 40) {
				return '4px';
			}

			return $m[0];
		}, $value) ?? $value;
	}
}
