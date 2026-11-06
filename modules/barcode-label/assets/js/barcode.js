/* Barkod & Etiket — bağımsız barkod üreticisi (EAN-13 + Code 128 → SVG) */
(function (root) {
	'use strict';

	var C128 = [
		'212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
		'221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
		'221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
		'212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
		'231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
		'231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
		'314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
		'112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
		'111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
		'214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
		'114131', '311141', '411131', '211412', '211214', '211232', '2331112'
	];
	var START_B = 104;
	var START_C = 105;
	var CODE_B = 100;
	var CODE_C = 99;
	var STOP = 106;

	var EAN_L = ['0001101', '0011001', '0010011', '0111101', '0100011', '0110001', '0101111', '0111011', '0110111', '0001011'];
	var EAN_G = ['0100111', '0110011', '0011011', '0100001', '0011101', '0111001', '0000101', '0010001', '0001001', '0010111'];
	var EAN_R = ['1110010', '1100110', '1101100', '1000010', '1011100', '1001110', '1010000', '1000100', '1001000', '1110100'];
	var EAN_PARITY = ['LLLLLL', 'LLGLGG', 'LLGGLG', 'LLGGGL', 'LGLLGG', 'LGGLLG', 'LGGGLL', 'LGLGLG', 'LGLGGL', 'LGGLGL'];

	function widthsToBits(widths) {
		var bits = '';
		for (var i = 0; i < widths.length; i++) {
			var w = parseInt(widths.charAt(i), 10);
			var ch = i % 2 === 0 ? '1' : '0';
			for (var j = 0; j < w; j++) bits += ch;
		}
		return bits;
	}

	function code128(text) {
		var clean = '';
		for (var i = 0; i < text.length; i++) {
			var cc = text.charCodeAt(i);
			clean += cc >= 32 && cc <= 126 ? text.charAt(i) : '?';
		}
		if (clean === '') return null;

		var codes = [];
		if (/^\d+$/.test(clean) && clean.length >= 2) {
			var evenPart = clean.length % 2 === 0 ? clean : clean.slice(0, -1);
			codes.push(START_C);
			for (var p = 0; p < evenPart.length; p += 2) {
				codes.push(parseInt(evenPart.substr(p, 2), 10));
			}
			if (evenPart.length !== clean.length) {
				codes.push(CODE_B);
				codes.push(clean.charCodeAt(clean.length - 1) - 32);
			}
		} else {
			codes.push(START_B);
			for (var k = 0; k < clean.length; k++) {
				codes.push(clean.charCodeAt(k) - 32);
			}
		}

		var sum = codes[0];
		for (var n = 1; n < codes.length; n++) sum += codes[n] * n;
		codes.push(sum % 103);
		codes.push(STOP);

		var bits = '';
		for (var m = 0; m < codes.length; m++) bits += widthsToBits(C128[codes[m]]);

		return { bits: bits, text: clean, type: 'code128' };
	}

	function eanCheckDigit(twelve) {
		var sum = 0;
		for (var i = 0; i < 12; i++) {
			sum += parseInt(twelve.charAt(i), 10) * (i % 2 === 0 ? 1 : 3);
		}
		return String((10 - (sum % 10)) % 10);
	}

	function ean13(text) {
		if (!/^\d{12,13}$/.test(text)) return null;
		var twelve = text.slice(0, 12);
		var check = eanCheckDigit(twelve);
		if (text.length === 13 && text.charAt(12) !== check) return null;
		var full = twelve + check;
		var parity = EAN_PARITY[parseInt(full.charAt(0), 10)];
		var bits = '101';
		for (var i = 1; i <= 6; i++) {
			var d = parseInt(full.charAt(i), 10);
			bits += parity.charAt(i - 1) === 'L' ? EAN_L[d] : EAN_G[d];
		}
		bits += '01010';
		for (var j = 7; j <= 12; j++) {
			bits += EAN_R[parseInt(full.charAt(j), 10)];
		}
		bits += '101';
		return { bits: bits, text: full, type: 'ean13' };
	}

	function encode(value, format) {
		value = String(value == null ? '' : value).trim();
		if (value === '') return null;
		if (format === 'ean13') return ean13(value) || code128(value);
		if (format === 'code128') return code128(value);
		return ean13(value) || code128(value);
	}

	function svg(encoded) {
		if (!encoded) return '';
		var bits = encoded.bits;
		var rects = '';
		var i = 0;
		while (i < bits.length) {
			if (bits.charAt(i) === '1') {
				var start = i;
				while (i < bits.length && bits.charAt(i) === '1') i++;
				rects += '<rect x="' + start + '" y="0" width="' + (i - start) + '" height="1"/>';
			} else {
				i++;
			}
		}
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + bits.length + ' 1" preserveAspectRatio="none" shape-rendering="crispEdges" fill="#000">' + rects + '</svg>';
	}

	root.BLBarcode = { encode: encode, svg: svg, ean13: ean13, code128: code128 };
})(window);
