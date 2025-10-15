<?php
/**
 * Automated Translation Script using potrans with DeepL and Google Translate
 *
 * This script translates the main POT file to all supported languages
 * using the potrans library with DeepL and Google Translate APIs.
 *
 * Usage: php scripts/translate.php [--deepl-key=key] [--google-key=key] [--pot-file=path/to/file.pot] [--force]
 */

class MultisiteUltimateTranslator {

	/**
	 * WordPress locales mapped to translation service and their codes
	 * Format: 'wp_locale' => ['service' => 'deepl|google', 'code' => 'api_code', 'name' => 'Language Name']
	 */
	private $language_map = [
		// DeepL supported languages
		'ar'    => [
			'service' => 'deepl',
			'code'    => 'ar',
			'name'    => 'Arabic',
		],
		'bg_BG' => [
			'service' => 'deepl',
			'code'    => 'bg',
			'name'    => 'Bulgarian',
		],
		'cs_CZ' => [
			'service' => 'deepl',
			'code'    => 'cs',
			'name'    => 'Czech',
		],
		'da_DK' => [
			'service' => 'deepl',
			'code'    => 'da',
			'name'    => 'Danish',
		],
		'de_DE' => [
			'service' => 'deepl',
			'code'    => 'de',
			'name'    => 'German',
		],
		'de_AT' => [
			'service' => 'deepl',
			'code'    => 'de',
			'name'    => 'German (Austria)',
		],
		'de_CH' => [
			'service' => 'deepl',
			'code'    => 'de',
			'name'    => 'German (Switzerland)',
		],
		'el'    => [
			'service' => 'deepl',
			'code'    => 'el',
			'name'    => 'Greek',
		],
		'en_GB' => [
			'service' => 'deepl',
			'code'    => 'en-GB',
			'name'    => 'English (UK)',
		],
		'en_AU' => [
			'service' => 'deepl',
			'code'    => 'en-GB',
			'name'    => 'English (Australia)',
		],
		'en_CA' => [
			'service' => 'deepl',
			'code'    => 'en-GB',
			'name'    => 'English (Canada)',
		],
		'es_ES' => [
			'service' => 'deepl',
			'code'    => 'es',
			'name'    => 'Spanish (Spain)',
		],
		'es_AR' => [
			'service' => 'deepl',
			'code'    => 'es',
			'name'    => 'Spanish (Argentina)',
		],
		'es_CL' => [
			'service' => 'deepl',
			'code'    => 'es',
			'name'    => 'Spanish (Chile)',
		],
		'es_CO' => [
			'service' => 'deepl',
			'code'    => 'es',
			'name'    => 'Spanish (Colombia)',
		],
		'es_MX' => [
			'service' => 'deepl',
			'code'    => 'es',
			'name'    => 'Spanish (Mexico)',
		],
		'es_PE' => [
			'service' => 'deepl',
			'code'    => 'es',
			'name'    => 'Spanish (Peru)',
		],
		'es_VE' => [
			'service' => 'deepl',
			'code'    => 'es',
			'name'    => 'Spanish (Venezuela)',
		],
		'et'    => [
			'service' => 'deepl',
			'code'    => 'et',
			'name'    => 'Estonian',
		],
		'fi'    => [
			'service' => 'deepl',
			'code'    => 'fi',
			'name'    => 'Finnish',
		],
		'fr_FR' => [
			'service' => 'deepl',
			'code'    => 'fr',
			'name'    => 'French (France)',
		],
		'fr_BE' => [
			'service' => 'deepl',
			'code'    => 'fr',
			'name'    => 'French (Belgium)',
		],
		'fr_CA' => [
			'service' => 'deepl',
			'code'    => 'fr',
			'name'    => 'French (Canada)',
		],
		'hu_HU' => [
			'service' => 'deepl',
			'code'    => 'hu',
			'name'    => 'Hungarian',
		],
		'id_ID' => [
			'service' => 'deepl',
			'code'    => 'id',
			'name'    => 'Indonesian',
		],
		'it_IT' => [
			'service' => 'deepl',
			'code'    => 'it',
			'name'    => 'Italian',
		],
		'ja'    => [
			'service' => 'deepl',
			'code'    => 'ja',
			'name'    => 'Japanese',
		],
		'ko_KR' => [
			'service' => 'deepl',
			'code'    => 'ko',
			'name'    => 'Korean',
		],
		'lt_LT' => [
			'service' => 'deepl',
			'code'    => 'lt',
			'name'    => 'Lithuanian',
		],
		'lv'    => [
			'service' => 'deepl',
			'code'    => 'lv',
			'name'    => 'Latvian',
		],
		'nb_NO' => [
			'service' => 'deepl',
			'code'    => 'nb',
			'name'    => 'Norwegian (Bokmål)',
		],
		'nl_NL' => [
			'service' => 'deepl',
			'code'    => 'nl',
			'name'    => 'Dutch',
		],
		'nl_BE' => [
			'service' => 'deepl',
			'code'    => 'nl',
			'name'    => 'Dutch (Belgium)',
		],
		'pl_PL' => [
			'service' => 'deepl',
			'code'    => 'pl',
			'name'    => 'Polish',
		],
		'pt_BR' => [
			'service' => 'deepl',
			'code'    => 'pt-BR',
			'name'    => 'Portuguese (Brazil)',
		],
		'pt_PT' => [
			'service' => 'deepl',
			'code'    => 'pt-PT',
			'name'    => 'Portuguese (Portugal)',
		],
		'ro_RO' => [
			'service' => 'deepl',
			'code'    => 'ro',
			'name'    => 'Romanian',
		],
		'ru_RU' => [
			'service' => 'deepl',
			'code'    => 'ru',
			'name'    => 'Russian',
		],
		'sk_SK' => [
			'service' => 'deepl',
			'code'    => 'sk',
			'name'    => 'Slovak',
		],
		'sl_SI' => [
			'service' => 'deepl',
			'code'    => 'sl',
			'name'    => 'Slovenian',
		],
		'sv_SE' => [
			'service' => 'deepl',
			'code'    => 'sv',
			'name'    => 'Swedish',
		],
		'tr_TR' => [
			'service' => 'deepl',
			'code'    => 'tr',
			'name'    => 'Turkish',
		],
		'uk'    => [
			'service' => 'deepl',
			'code'    => 'uk',
			'name'    => 'Ukrainian',
		],
		'zh_CN' => [
			'service' => 'deepl',
			'code'    => 'zh',
			'name'    => 'Chinese (Simplified)',
		],
		'zh_TW' => [
			'service' => 'deepl',
			'code'    => 'zh',
			'name'    => 'Chinese (Traditional)',
		],
		'zh_HK' => [
			'service' => 'deepl',
			'code'    => 'zh',
			'name'    => 'Chinese (Hong Kong)',
		],

		// Google Translate only languages (not supported by DeepL)
		'af'    => [
			'service' => 'google',
			'code'    => 'af',
			'name'    => 'Afrikaans',
		],
		'sq'    => [
			'service' => 'google',
			'code'    => 'sq',
			'name'    => 'Albanian',
		],
		'am'    => [
			'service' => 'google',
			'code'    => 'am',
			'name'    => 'Amharic',
		],
		'hy'    => [
			'service' => 'google',
			'code'    => 'hy',
			'name'    => 'Armenian',
		],
		'as'    => [
			'service' => 'google',
			'code'    => 'as',
			'name'    => 'Assamese',
		],
		'az'    => [
			'service' => 'google',
			'code'    => 'az',
			'name'    => 'Azerbaijani',
		],
		'azb'   => [
			'service' => 'google',
			'code'    => 'az',
			'name'    => 'South Azerbaijani',
		],
		'eu'    => [
			'service' => 'google',
			'code'    => 'eu',
			'name'    => 'Basque',
		],
		'bel'   => [
			'service' => 'google',
			'code'    => 'be',
			'name'    => 'Belarusian',
		],
		'bn_BD' => [
			'service' => 'google',
			'code'    => 'bn',
			'name'    => 'Bengali (Bangladesh)',
		],
		'bn_IN' => [
			'service' => 'google',
			'code'    => 'bn',
			'name'    => 'Bengali (India)',
		],
		'bs_BA' => [
			'service' => 'google',
			'code'    => 'bs',
			'name'    => 'Bosnian',
		],
		'ca'    => [
			'service' => 'google',
			'code'    => 'ca',
			'name'    => 'Catalan',
		],
		'ceb'   => [
			'service' => 'google',
			'code'    => 'ceb',
			'name'    => 'Cebuano',
		],
		'ckb'   => [
			'service' => 'google',
			'code'    => 'ku',
			'name'    => 'Kurdish (Sorani)',
		],
		'co'    => [
			'service' => 'google',
			'code'    => 'co',
			'name'    => 'Corsican',
		],
		'hr'    => [
			'service' => 'google',
			'code'    => 'hr',
			'name'    => 'Croatian',
		],
		'cy'    => [
			'service' => 'google',
			'code'    => 'cy',
			'name'    => 'Welsh',
		],
		'eo'    => [
			'service' => 'google',
			'code'    => 'eo',
			'name'    => 'Esperanto',
		],
		'fil'   => [
			'service' => 'google',
			'code'    => 'fil',
			'name'    => 'Filipino',
		],
		'fy'    => [
			'service' => 'google',
			'code'    => 'fy',
			'name'    => 'Frisian',
		],
		'gl_ES' => [
			'service' => 'google',
			'code'    => 'gl',
			'name'    => 'Galician',
		],
		'ka_GE' => [
			'service' => 'google',
			'code'    => 'ka',
			'name'    => 'Georgian',
		],
		'gu'    => [
			'service' => 'google',
			'code'    => 'gu',
			'name'    => 'Gujarati',
		],
		'haw'   => [
			'service' => 'google',
			'code'    => 'haw',
			'name'    => 'Hawaiian',
		],
		'he_IL' => [
			'service' => 'google',
			'code'    => 'he',
			'name'    => 'Hebrew',
		],
		'hi_IN' => [
			'service' => 'google',
			'code'    => 'hi',
			'name'    => 'Hindi',
		],
		'hmn'   => [
			'service' => 'google',
			'code'    => 'hmn',
			'name'    => 'Hmong',
		],
		'is_IS' => [
			'service' => 'google',
			'code'    => 'is',
			'name'    => 'Icelandic',
		],
		'ig'    => [
			'service' => 'google',
			'code'    => 'ig',
			'name'    => 'Igbo',
		],
		'ga'    => [
			'service' => 'google',
			'code'    => 'ga',
			'name'    => 'Irish',
		],
		'jv'    => [
			'service' => 'google',
			'code'    => 'jv',
			'name'    => 'Javanese',
		],
		'kn'    => [
			'service' => 'google',
			'code'    => 'kn',
			'name'    => 'Kannada',
		],
		'kk'    => [
			'service' => 'google',
			'code'    => 'kk',
			'name'    => 'Kazakh',
		],
		'km'    => [
			'service' => 'google',
			'code'    => 'km',
			'name'    => 'Khmer',
		],
		'ky_KY' => [
			'service' => 'google',
			'code'    => 'ky',
			'name'    => 'Kyrgyz',
		],
		'lo'    => [
			'service' => 'google',
			'code'    => 'lo',
			'name'    => 'Lao',
		],
		'la'    => [
			'service' => 'google',
			'code'    => 'la',
			'name'    => 'Latin',
		],
		'mk_MK' => [
			'service' => 'google',
			'code'    => 'mk',
			'name'    => 'Macedonian',
		],
		'mg_MG' => [
			'service' => 'google',
			'code'    => 'mg',
			'name'    => 'Malagasy',
		],
		'ms_MY' => [
			'service' => 'google',
			'code'    => 'ms',
			'name'    => 'Malay',
		],
		'ml_IN' => [
			'service' => 'google',
			'code'    => 'ml',
			'name'    => 'Malayalam',
		],
		'mt_MT' => [
			'service' => 'google',
			'code'    => 'mt',
			'name'    => 'Maltese',
		],
		'mi'    => [
			'service' => 'google',
			'code'    => 'mi',
			'name'    => 'Maori',
		],
		'mr'    => [
			'service' => 'google',
			'code'    => 'mr',
			'name'    => 'Marathi',
		],
		'mn'    => [
			'service' => 'google',
			'code'    => 'mn',
			'name'    => 'Mongolian',
		],
		'my_MM' => [
			'service' => 'google',
			'code'    => 'my',
			'name'    => 'Myanmar (Burmese)',
		],
		'ne_NP' => [
			'service' => 'google',
			'code'    => 'ne',
			'name'    => 'Nepali',
		],
		'nn_NO' => [
			'service' => 'google',
			'code'    => 'no',
			'name'    => 'Norwegian (Nynorsk)',
		],
		'or'    => [
			'service' => 'google',
			'code'    => 'or',
			'name'    => 'Odia',
		],
		'ps'    => [
			'service' => 'google',
			'code'    => 'ps',
			'name'    => 'Pashto',
		],
		'fa_IR' => [
			'service' => 'google',
			'code'    => 'fa',
			'name'    => 'Persian',
		],
		'pa_IN' => [
			'service' => 'google',
			'code'    => 'pa',
			'name'    => 'Punjabi',
		],
		'sm'    => [
			'service' => 'google',
			'code'    => 'sm',
			'name'    => 'Samoan',
		],
		'gd'    => [
			'service' => 'google',
			'code'    => 'gd',
			'name'    => 'Scottish Gaelic',
		],
		'sr_RS' => [
			'service' => 'google',
			'code'    => 'sr',
			'name'    => 'Serbian',
		],
		'st'    => [
			'service' => 'google',
			'code'    => 'st',
			'name'    => 'Sesotho',
		],
		'sn'    => [
			'service' => 'google',
			'code'    => 'sn',
			'name'    => 'Shona',
		],
		'sd'    => [
			'service' => 'google',
			'code'    => 'sd',
			'name'    => 'Sindhi',
		],
		'si_LK' => [
			'service' => 'google',
			'code'    => 'si',
			'name'    => 'Sinhala',
		],
		'so_SO' => [
			'service' => 'google',
			'code'    => 'so',
			'name'    => 'Somali',
		],
		'su'    => [
			'service' => 'google',
			'code'    => 'su',
			'name'    => 'Sundanese',
		],
		'sw'    => [
			'service' => 'google',
			'code'    => 'sw',
			'name'    => 'Swahili',
		],
		'tg'    => [
			'service' => 'google',
			'code'    => 'tg',
			'name'    => 'Tajik',
		],
		'ta_IN' => [
			'service' => 'google',
			'code'    => 'ta',
			'name'    => 'Tamil (India)',
		],
		'ta_LK' => [
			'service' => 'google',
			'code'    => 'ta',
			'name'    => 'Tamil (Sri Lanka)',
		],
		'tt_RU' => [
			'service' => 'google',
			'code'    => 'tt',
			'name'    => 'Tatar',
		],
		'te'    => [
			'service' => 'google',
			'code'    => 'te',
			'name'    => 'Telugu',
		],
		'th'    => [
			'service' => 'google',
			'code'    => 'th',
			'name'    => 'Thai',
		],
		'bo'    => [
			'service' => 'google',
			'code'    => 'bo',
			'name'    => 'Tibetan',
		],
		'ti'    => [
			'service' => 'google',
			'code'    => 'ti',
			'name'    => 'Tigrinya',
		],
		'tuk'   => [
			'service' => 'google',
			'code'    => 'tk',
			'name'    => 'Turkmen',
		],
		'ug_CN' => [
			'service' => 'google',
			'code'    => 'ug',
			'name'    => 'Uyghur',
		],
		'ur'    => [
			'service' => 'google',
			'code'    => 'ur',
			'name'    => 'Urdu',
		],
		'uz_UZ' => [
			'service' => 'google',
			'code'    => 'uz',
			'name'    => 'Uzbek',
		],
		'vi'    => [
			'service' => 'google',
			'code'    => 'vi',
			'name'    => 'Vietnamese',
		],
		'xh'    => [
			'service' => 'google',
			'code'    => 'xh',
			'name'    => 'Xhosa',
		],
		'yi'    => [
			'service' => 'google',
			'code'    => 'yi',
			'name'    => 'Yiddish',
		],
		'yo'    => [
			'service' => 'google',
			'code'    => 'yo',
			'name'    => 'Yoruba',
		],
		'zu_ZA' => [
			'service' => 'google',
			'code'    => 'zu',
			'name'    => 'Zulu',
		],
	];

	private $source_pot_file;
	private $lang_dir;
	private $deepl_api_key;
	private $google_api_key;
	private $force;
	private $vendor_dir;

	public function __construct($deepl_key = null, $google_key = null, $force = false, $pot_file = null) {
		$this->deepl_api_key  = $deepl_key ?: getenv('DEEPL_API_KEY');
		$this->google_api_key = $google_key ?: getenv('GOOGLE_API_KEY');
		$this->force          = $force;
		$this->vendor_dir     = dirname(__DIR__) . '/vendor';

		if (! $this->deepl_api_key && ! $this->google_api_key) {
			throw new Exception('At least one API key is required. Set DEEPL_API_KEY or GOOGLE_API_KEY environment variable or use --deepl-key or --google-key parameter.');
		}

		// Find the POT file
		if ($pot_file) {
			// Use the provided POT file path
			$this->source_pot_file = $pot_file;
		} else {
			// Auto-discover POT file
			$this->source_pot_file = $this->findPotFile();
		}

		if (! file_exists($this->source_pot_file)) {
			throw new Exception("Source POT file not found: {$this->source_pot_file}");
		}

		// Determine lang directory based on POT file location
		$pot_dir = dirname($this->source_pot_file);
		if (basename($pot_dir) === 'lang' || basename($pot_dir) === 'languages') {
			$this->lang_dir = $pot_dir;
		} else {
			// If POT is not in a lang directory, check if lang directory exists
			$lang_candidates = [
				$pot_dir . '/lang',
				$pot_dir . '/languages',
			];
			foreach ($lang_candidates as $candidate) {
				if (is_dir($candidate)) {
					$this->lang_dir = $candidate;
					break;
				}
			}
			// If no lang directory found, use the same directory as POT file
			if (! isset($this->lang_dir)) {
				$this->lang_dir = $pot_dir;
			}
		}

		if (! is_dir($this->vendor_dir)) {
			throw new Exception("Vendor directory not found. Run 'composer install' first.");
		}
	}

	private function findPotFile() {
		$cwd = getcwd();

		// 1. Check current working directory for *.pot files
		$pot_files = glob($cwd . '/*.pot');
		if (! empty($pot_files)) {
			return $pot_files[0];
		}

		// 2. Check lang/*.pot
		$pot_files = glob($cwd . '/lang/*.pot');
		if (! empty($pot_files)) {
			return $pot_files[0];
		}

		// 3. Check languages/*.pot
		$pot_files = glob($cwd . '/languages/*.pot');
		if (! empty($pot_files)) {
			return $pot_files[0];
		}

		// 4. Fallback to the default location (for backward compatibility)
		$default_pot = dirname(__DIR__) . '/lang/ultimate-multisite.pot';
		if (file_exists($default_pot)) {
			return $default_pot;
		}

		throw new Exception("No POT file found. Searched in:\n  - {$cwd}/*.pot\n  - {$cwd}/lang/*.pot\n  - {$cwd}/languages/*.pot\n\nPlease specify the POT file path using --pot-file parameter.");
	}

	public function translateAll() {
		echo "Starting translation process\n";
		echo "Source POT: {$this->source_pot_file}\n";
		echo "Output directory: {$this->lang_dir}\n";
		echo 'DeepL API: ' . ($this->deepl_api_key ? 'Available' : 'Not available') . "\n";
		echo 'Google API: ' . ($this->google_api_key ? 'Available' : 'Not available') . "\n\n";

		$success_count = 0;
		$skip_count    = 0;
		$fail_count    = 0;

		foreach ($this->language_map as $wp_locale => $lang_info) {
			// Skip if we don't have the required API key
			if ($lang_info['service'] === 'deepl' && ! $this->deepl_api_key) {
				continue;
			}
			if ($lang_info['service'] === 'google' && ! $this->google_api_key) {
				continue;
			}

			$text_domain = basename($this->source_pot_file, '.pot');
			$output_file = $this->lang_dir . '/' . $text_domain . '-' . $wp_locale . '.po';

			echo "Translating to {$lang_info['name']} ({$wp_locale}) via {$lang_info['service']}... ";

			// Skip if file exists and we're not forcing re-translation
			if (file_exists($output_file) && ! $this->force) {
				echo "SKIPPED (file exists, use --force to overwrite)\n";
				++$skip_count;
				continue;
			}

			try {
				$result = $this->translateLanguage($lang_info['service'], $lang_info['code'], $output_file, $wp_locale);
				if ($result) {
					echo "SUCCESS\n";
					++$success_count;
				} else {
					echo "FAILED\n";
					++$fail_count;
				}
			} catch (Exception $e) {
				echo 'ERROR: ' . $e->getMessage() . "\n";
				++$fail_count;
			}
		}

		echo "\nTranslation complete!\n";
		echo "Successfully translated: {$success_count}\n";
		echo "Skipped: {$skip_count}\n";
		echo "Failed: {$fail_count}\n";
	}

	private function translateLanguage($service, $lang_code, $output_file, $wp_locale) {
		// Try to find potrans binary in multiple locations
		$potrans_binary = $this->findPotrancBinary();

		if (! $potrans_binary) {
			throw new Exception("potrans binary not found. Install it globally via 'composer global require om/potrans' or set POTRANS_PATH environment variable.");
		}

		// Build command based on service
		if ($service === 'deepl') {
			$cmd = sprintf(
				'php %s deepl %s %s --apikey=%s --to=%s --from=EN',
				escapeshellarg($potrans_binary),
				escapeshellarg($this->source_pot_file),
				escapeshellarg(dirname($output_file)),
				escapeshellarg($this->deepl_api_key),
				escapeshellarg($lang_code)
			);
		} else {
			// Google Translate
			$cmd = sprintf(
				'php %s google %s %s --credentials=%s --to=%s --from=en',
				escapeshellarg($potrans_binary),
				escapeshellarg($this->source_pot_file),
				escapeshellarg(dirname($output_file)),
				escapeshellarg($this->google_api_key),
				escapeshellarg($lang_code)
			);
		}

		if ($this->force) {
			$cmd .= ' --force';
		}

		// Retry logic for rate limiting
		$max_retries = 10; // Max number of retries before giving up on this language
		$retry_count = 0;
		$base_sleep  = 5; // Base sleep time in seconds
		$success     = false;

		while (! $success && $retry_count < $max_retries) {
			// Execute the translation command
			$output      = [];
			$return_code = 0;
			exec($cmd . ' 2>&1', $output, $return_code);

			// Check if the command succeeded
			if ($return_code === 0) {
				$success = true;
				break;
			}

			// Check for rate limiting errors
			$output_text = implode("\n", $output);
			if (strpos($output_text, 'Too many requests') !== false ||
				strpos($output_text, 'high load') !== false ||
				strpos($output_text, '429') !== false ||
				strpos($output_text, 'quota') !== false) {
				++$retry_count;
				// Exponential backoff: 5s, 10s, 20s, 40s, etc., capped at 120s
				$sleep_time = min($base_sleep * pow(2, $retry_count - 1), 120);

				echo "\nRate limit hit. Waiting {$sleep_time} seconds before retry {$retry_count}/{$max_retries}...\n";
				sleep($sleep_time);
				continue;
			}

			// For other errors, throw exception immediately
			throw new Exception('potrans command failed: ' . $output_text);
		}

		if (! $success) {
			throw new Exception("Failed after {$max_retries} retries due to rate limiting. Please try again later.");
		}

		// potrans outputs with different naming, we need to rename the file
		$text_domain    = basename($this->source_pot_file, '.pot');
		$potrans_output = dirname($output_file) . '/' . $text_domain . '-' . $wp_locale . '.po';

		// Check various possible output filenames from potrans
		$possible_outputs = [
			dirname($output_file) . '/' . $text_domain . '-' . $wp_locale . '.po',
			dirname($output_file) . '/' . $text_domain . '-' . str_replace('_', '-', strtolower($wp_locale)) . '.po',
			dirname($output_file) . '/' . $text_domain . '-' . strtolower($lang_code) . '.po',
			dirname($output_file) . '/' . $text_domain . '.po',
		];

		foreach ($possible_outputs as $possible_output) {
			if (file_exists($possible_output) && $possible_output !== $output_file) {
				rename($possible_output, $output_file);
				break;
			}
		}

		return file_exists($output_file);
	}

	public function generateMoFiles() {
		echo "\nGenerating .mo files from .po files...\n";

		$text_domain   = basename($this->source_pot_file, '.pot');
		$po_files      = glob($this->lang_dir . '/' . $text_domain . '-*.po');
		$success_count = 0;

		foreach ($po_files as $po_file) {
			$mo_file = str_replace('.po', '.mo', $po_file);
			echo 'Generating ' . basename($mo_file) . '... ';

			$cmd         = sprintf('msgfmt %s -o %s', escapeshellarg($po_file), escapeshellarg($mo_file));
			$output      = [];
			$return_code = 0;
			exec($cmd . ' 2>&1', $output, $return_code);

			if ($return_code === 0 && file_exists($mo_file)) {
				echo "SUCCESS\n";
				++$success_count;
			} else {
				echo "FAILED\n";
			}
		}

		echo "Generated {$success_count} .mo files\n";
	}

	private function findPotrancBinary() {
		// 1. Check POTRANS_PATH environment variable
		$env_path = getenv('POTRANS_PATH');
		if ($env_path && file_exists($env_path)) {
			return $env_path;
		}

		// 2. Check vendor/bin/potrans (local installation)
		$local_path = $this->vendor_dir . '/bin/potrans';
		if (file_exists($local_path)) {
			return $local_path;
		}

		// 3. Check global composer installation
		$home = getenv('HOME') ?: getenv('USERPROFILE');
		if ($home) {
			$global_vendor_path = $home . '/.composer/vendor/bin/potrans';
			if (file_exists($global_vendor_path)) {
				return $global_vendor_path;
			}

			// Alternative global composer path
			$global_vendor_path2 = $home . '/.config/composer/vendor/bin/potrans';
			if (file_exists($global_vendor_path2)) {
				return $global_vendor_path2;
			}
		}

		// 4. Check if potrans is in PATH
		$which_result = null;
		$return_code  = 0;
		exec('which potrans 2>/dev/null', $which_result, $return_code);
		if ($return_code === 0 && ! empty($which_result[0]) && file_exists($which_result[0])) {
			return $which_result[0];
		}

		// 5. Try whereis on Linux systems
		$whereis_result = null;
		$return_code    = 0;
		exec('whereis potrans 2>/dev/null', $whereis_result, $return_code);
		if ($return_code === 0 && ! empty($whereis_result[0])) {
			$parts = explode(' ', $whereis_result[0]);
			if (count($parts) > 1 && file_exists($parts[1])) {
				return $parts[1];
			}
		}

		return false;
	}
}

// Parse command line arguments
$deepl_key  = null;
$google_key = null;
$force      = false;
$help       = false;
$pot_file   = null;

for ($i = 1; $i < $argc; $i++) {
	$arg = $argv[ $i ];

	if (strpos($arg, '--deepl-key=') === 0) {
		$deepl_key = substr($arg, strlen('--deepl-key='));
	} elseif (strpos($arg, '--google-key=') === 0) {
		$google_key = substr($arg, strlen('--google-key='));
	} elseif (strpos($arg, '--pot-file=') === 0) {
		$pot_file = substr($arg, strlen('--pot-file='));
	} elseif ($arg === '--force') {
		$force = true;
	} elseif ($arg === '--help' || $arg === '-h') {
		$help = true;
	}
}

if ($help) {
	echo "Ultimate Multisite Translation Script\n";
	echo "=====================================\n\n";
	echo "Usage: php scripts/translate.php [options]\n\n";
	echo "Options:\n";
	echo "  --deepl-key=KEY    DeepL API key (or set DEEPL_API_KEY environment variable)\n";
	echo "  --google-key=KEY   Google Translate API key (or set GOOGLE_API_KEY environment variable)\n";
	echo "  --pot-file=PATH    Path to the POT file to translate (auto-detected if not specified)\n";
	echo "  --force            Force re-translation of existing files\n";
	echo "  --help             Show this help message\n\n";
	echo "POT File Discovery:\n";
	echo "If --pot-file is not specified, the script will search for POT files in this order:\n";
	echo "  1. Current working directory (*.pot)\n";
	echo "  2. Current working directory/lang/*.pot\n";
	echo "  3. Current working directory/languages/*.pot\n";
	echo "  4. Default location: lang/ultimate-multisite.pot\n\n";
	echo "Translation Services:\n";
	echo "  - DeepL: Higher quality translations, supports 47 languages\n";
	echo "  - Google Translate: Supports 100+ languages\n\n";
	echo "This script will:\n";
	echo "  1. Automatically select the best translation service for each language\n";
	echo "  2. Translate the POT file to all supported WordPress locales\n";
	echo "  3. Generate .mo files from the translated .po files\n";
	echo "  4. Handle rate limiting with exponential backoff retry logic\n\n";
	exit(0);
}

try {
	$translator = new MultisiteUltimateTranslator($deepl_key, $google_key, $force, $pot_file);
	$translator->translateAll();
	$translator->generateMoFiles();

	echo "\nAll done! 🎉\n";
} catch (Exception $e) {
	echo 'Error: ' . $e->getMessage() . "\n";
	exit(1);
}
