<?php
/**
 * Automated Translation Script using potrans and DeepL
 * 
 * This script translates the main POT file to all supported DeepL languages
 * using the potrans library.
 * 
 * Usage: php scripts/translate.php [--api-key=your-deepl-api-key] [--force]
 */

class MultisiteUltimateTranslator {

    /**
     * DeepL supported languages with their locale codes
     * Based on DeepL API documentation as of 2025
     */
    private $deepl_languages = [
        'ar' => 'Arabic',
        'bg' => 'Bulgarian', 
        'cs' => 'Czech',
        'da' => 'Danish',
        'de' => 'German',
        'el' => 'Greek',
        'en-GB' => 'English (British)',
        'en-US' => 'English (American)',
        'es' => 'Spanish',
        'et' => 'Estonian',
        'fi' => 'Finnish',
        'fr' => 'French',
        'hu' => 'Hungarian',
        'id' => 'Indonesian',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'lt' => 'Lithuanian',
        'lv' => 'Latvian',
        'nb' => 'Norwegian (Bokmål)',
        'nl' => 'Dutch',
        'pl' => 'Polish',
        'pt-BR' => 'Portuguese (Brazilian)',
        'pt-PT' => 'Portuguese (European)',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'sk' => 'Slovak',
        'sl' => 'Slovenian',
        'sv' => 'Swedish',
        'tr' => 'Turkish',
        'uk' => 'Ukrainian',
        'zh-CN' => 'Chinese (Simplified)',
        'zh-TW' => 'Chinese (Traditional)'
    ];

    /**
     * WordPress locale mapping for DeepL language codes
     */
    private $wp_locale_mapping = [
        'ar' => 'ar',
        'bg' => 'bg_BG', 
        'cs' => 'cs_CZ',
        'da' => 'da_DK',
        'de' => 'de_DE',
        'el' => 'el',
        'en-GB' => 'en_GB',
        'en-US' => 'en_US',
        'es' => 'es_ES',
        'et' => 'et',
        'fi' => 'fi',
        'fr' => 'fr_FR',
        'hu' => 'hu_HU',
        'id' => 'id_ID',
        'it' => 'it_IT',
        'ja' => 'ja',
        'ko' => 'ko_KR',
        'lt' => 'lt_LT',
        'lv' => 'lv',
        'nb' => 'nb_NO',
        'nl' => 'nl_NL',
        'pl' => 'pl_PL',
        'pt-BR' => 'pt_BR',
        'pt-PT' => 'pt_PT',
        'ro' => 'ro_RO',
        'ru' => 'ru_RU',
        'sk' => 'sk_SK',
        'sl' => 'sl_SI',
        'sv' => 'sv_SE',
        'tr' => 'tr_TR',
        'uk' => 'uk',
        'zh-CN' => 'zh_CN',
        'zh-TW' => 'zh_TW'
    ];

    private $source_pot_file;
    private $lang_dir;
    private $api_key;
    private $force;
    private $vendor_dir;

    public function __construct($api_key = null, $force = false) {
        $this->source_pot_file = dirname(__DIR__) . '/lang/multisite-ultimate.pot';
        $this->lang_dir = dirname(__DIR__) . '/lang';
        $this->vendor_dir = dirname(__DIR__) . '/vendor';
        $this->api_key = $api_key ?: getenv('DEEPL_API_KEY');
        $this->force = $force;

        if (!$this->api_key) {
            throw new Exception('DeepL API key is required. Set DEEPL_API_KEY environment variable or use --api-key parameter.');
        }

        if (!file_exists($this->source_pot_file)) {
            throw new Exception("Source POT file not found: {$this->source_pot_file}");
        }

        if (!is_dir($this->vendor_dir)) {
            throw new Exception("Vendor directory not found. Run 'composer install' first.");
        }
    }

    public function translateAll() {
        echo "Starting translation process for Multisite Ultimate\n";
        echo "Source POT: {$this->source_pot_file}\n";
        echo "Output directory: {$this->lang_dir}\n\n";

        $success_count = 0;
        $total_count = count($this->deepl_languages);

        foreach ($this->deepl_languages as $deepl_code => $language_name) {
            // Skip English since that's our source language
            if (strpos($deepl_code, 'en') === 0) {
                continue;
            }

            $wp_locale = $this->wp_locale_mapping[$deepl_code];
            $output_file = $this->lang_dir . '/multisite-ultimate-' . $wp_locale . '.po';

            echo "Translating to {$language_name} ({$deepl_code} -> {$wp_locale})... ";

            // Skip if file exists and we're not forcing re-translation
            if (file_exists($output_file) && !$this->force) {
                echo "SKIPPED (file exists, use --force to overwrite)\n";
                continue;
            }

            try {
                $result = $this->translateLanguage($deepl_code, $output_file);
                if ($result) {
                    echo "SUCCESS\n";
                    $success_count++;
                } else {
                    echo "FAILED\n";
                }
            } catch (Exception $e) {
                echo "ERROR: " . $e->getMessage() . "\n";
            }
        }

        echo "\nTranslation complete!\n";
        echo "Successfully translated: {$success_count}/" . ($total_count - 2) . " languages\n"; // -2 for English variants
    }

    private function translateLanguage($deepl_code, $output_file) {
        // Try to find potrans binary in multiple locations
        $potrans_binary = $this->findPotrancBinary();
        
        if (!$potrans_binary) {
            throw new Exception("potrans binary not found. Install it globally via 'composer global require om/potrans' or set POTRANS_PATH environment variable.");
        }

        $cmd = sprintf(
            'php %s deepl %s %s --apikey=%s --target-lang=%s --source-lang=EN',
            escapeshellarg($potrans_binary),
            escapeshellarg($this->source_pot_file),
            escapeshellarg(dirname($output_file)),
            escapeshellarg($this->api_key),
            escapeshellarg($deepl_code)
        );

        if ($this->force) {
            $cmd .= ' --force';
        }

        // Execute the translation command
        $output = [];
        $return_code = 0;
        exec($cmd . ' 2>&1', $output, $return_code);

        if ($return_code !== 0) {
            throw new Exception("potrans command failed: " . implode("\n", $output));
        }

        // potrans outputs with different naming, we need to rename the file
        $potrans_output = dirname($output_file) . '/' . basename($this->source_pot_file, '.pot') . '-' . strtolower(str_replace('-', '_', $deepl_code)) . '.po';
        
        if (file_exists($potrans_output) && $potrans_output !== $output_file) {
            rename($potrans_output, $output_file);
        }

        return file_exists($output_file);
    }

    public function generateMoFiles() {
        echo "Generating .mo files from .po files...\n";

        $po_files = glob($this->lang_dir . '/multisite-ultimate-*.po');
        $success_count = 0;

        foreach ($po_files as $po_file) {
            $mo_file = str_replace('.po', '.mo', $po_file);
            echo "Generating " . basename($mo_file) . "... ";

            $cmd = sprintf('msgfmt %s -o %s', escapeshellarg($po_file), escapeshellarg($mo_file));
            $output = [];
            $return_code = 0;
            exec($cmd . ' 2>&1', $output, $return_code);

            if ($return_code === 0 && file_exists($mo_file)) {
                echo "SUCCESS\n";
                $success_count++;
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
        $return_code = 0;
        exec('which potrans 2>/dev/null', $which_result, $return_code);
        if ($return_code === 0 && !empty($which_result[0]) && file_exists($which_result[0])) {
            return $which_result[0];
        }

        // 5. Try whereis on Linux systems
        $whereis_result = null;
        $return_code = 0;
        exec('whereis potrans 2>/dev/null', $whereis_result, $return_code);
        if ($return_code === 0 && !empty($whereis_result[0])) {
            $parts = explode(' ', $whereis_result[0]);
            if (count($parts) > 1 && file_exists($parts[1])) {
                return $parts[1];
            }
        }

        return false;
    }
}

// Parse command line arguments
$api_key = null;
$force = false;
$help = false;

for ($i = 1; $i < $argc; $i++) {
    $arg = $argv[$i];
    
    if (strpos($arg, '--api-key=') === 0) {
        $api_key = substr($arg, strlen('--api-key='));
    } elseif ($arg === '--force') {
        $force = true;
    } elseif ($arg === '--help' || $arg === '-h') {
        $help = true;
    }
}

if ($help) {
    echo "Multisite Ultimate Translation Script\n";
    echo "=====================================\n\n";
    echo "Usage: php scripts/translate.php [options]\n\n";
    echo "Options:\n";
    echo "  --api-key=KEY    DeepL API key (or set DEEPL_API_KEY environment variable)\n";
    echo "  --force          Force re-translation of existing files\n";
    echo "  --help           Show this help message\n\n";
    echo "This script will:\n";
    echo "1. Translate the main POT file to all DeepL supported languages\n";
    echo "2. Generate .mo files from the translated .po files\n\n";
    exit(0);
}

try {
    $translator = new MultisiteUltimateTranslator($api_key, $force);
    $translator->translateAll();
    $translator->generateMoFiles();
    
    echo "\nAll done! 🎉\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}