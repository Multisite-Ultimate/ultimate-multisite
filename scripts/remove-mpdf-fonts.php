<?php

$fonts = [
	'AboriginalSansREGULAR.ttf',
	'Aegean.otf',
	'Aegyptus.otf',
	'Akkadian.otf',
	'ayar.ttf',
	'damase_v.2.ttf',
	'DBSILBR.ttf',
	'DejaVuSerif.ttf',
	'Dhyana-Regular.ttf',
	'DejaVuSansMono-Oblique.ttf',
	'DejaVuSerif-BoldItalic.ttf',
	'DhyanaOFL.txt',
	'DejaVuSerifCondensed-BoldItalic.ttf',
	'DejaVuSansMono-Bold.ttf',
	'DejaVuSerif-Italic.ttf',
	'DejaVuSansMono.ttf',
	'DejaVuSansMono-BoldOblique.ttf',
	'DejaVuSerif-Bold.ttf',
	'Dhyana-Bold.ttf',
	'DejaVuSerifCondensed-Italic.ttf',
	'DejaVuSansCondensed-BoldOblique.ttf',
	'DejaVuSansCondensed-Oblique.ttf',
	'DejaVuSans-Oblique.ttf',
	'DejaVuSans-BoldOblique.ttf',
	'DejaVuSans-Bold.ttf',
	'DejaVuSans.ttf',
	'FreeMonoBoldOblique.ttf',
	'FreeMonoOblique.ttf',
	'FreeSans.ttf',
	'FreeSansBold.ttf',
	'FreeSansBoldOblique.ttf',
	'FreeSansOblique.ttf',
	'FreeSerif.ttf',
	'FreeSerifBold.ttf',
	'FreeSerifBoldItalic.ttf',
	'FreeSerifItalic.ttf',
	'Garuda.ttf',
	'Garuda-Bold.ttf',
	'Garuda-BoldOblique.ttf',
	'Garuda-Oblique.ttf',
	'GNUFreeFontinfo.txt',
	'Jomolhari.ttf',
	'Jomolhari-OFL.txt',
	'kaputaunicode.ttf',
	'KhmerOFL.txt',
	'KhmerOS.ttf',
	'lannaalif-v1-03.ttf',
	'Lateef font OFL.txt',
	'LateefRegOT.ttf',
	'Lohit-Kannada.ttf',
	'LohitKannadaOFL.txt',
	'ocrb10.ttf',
	'ocrbinfo.txt',
	'Padauk-book.ttf',
	'Pothana2000.ttf',
	'Quivira.otf',
	'Sun-ExtA.ttf',
	'Sun-ExtB.ttf',
	'SundaneseUnicode-1.0.5.ttf',
	'SyrCOMEdessa.otf',
	'SyrCOMEdessa_license.txt',
	'TaameyDavidCLM-LICENSE.txt',
	'TaameyDavidCLM-Medium.ttf',
	'TaiHeritagePro.ttf',
	'Tharlon-Regular.ttf',
	'TharlonOFL.txt',
	'UnBatang_0613.ttf',
	'Uthman.otf',
	'XB Riyaz.ttf',
	'XB RiyazBd.ttf',
	'XB RiyazBdIt.ttf',
	'XB RiyazIt.ttf',
	'XW Zar Font Info.txt',
	'ZawgyiOne.ttf',
	'Abyssinica_SIL.ttf',
];

$dir = __DIR__ . '/../vendor/mpdf/mpdf/ttfonts/';

echo "\nRemoving fonts...\n\n";

foreach ($fonts as $font) {
	$filePath = $dir . $font;
	if (file_exists($filePath)) {
		unlink($filePath);
		echo "Deleted: $font\n";
	} else {
		echo "Not found: $font\n";
	}
}
