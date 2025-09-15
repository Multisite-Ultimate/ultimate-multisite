# Multisite Ultimate Translation System

This directory contains the automated translation system for Multisite Ultimate using potrans and DeepL API.

## Overview

The translation system automatically translates the main POT file into all languages supported by DeepL API, generating WordPress-compatible PO and MO files.

## Supported Languages

The script translates to 31 languages supported by DeepL (excluding English variants):

- Arabic (ar)
- Bulgarian (bg_BG)
- Chinese Simplified (zh_CN)
- Chinese Traditional (zh_TW)
- Czech (cs_CZ)
- Danish (da_DK)
- Dutch (nl_NL)
- Estonian (et)
- Finnish (fi)
- French (fr_FR)
- German (de_DE)
- Greek (el)
- Hungarian (hu_HU)
- Indonesian (id_ID)
- Italian (it_IT)
- Japanese (ja)
- Korean (ko_KR)
- Latvian (lv)
- Lithuanian (lt_LT)
- Norwegian (nb_NO)
- Polish (pl_PL)
- Portuguese Brazilian (pt_BR)
- Portuguese European (pt_PT)
- Romanian (ro_RO)
- Russian (ru_RU)
- Slovak (sk_SK)
- Slovenian (sl_SI)
- Spanish (es_ES)
- Swedish (sv_SE)
- Turkish (tr_TR)
- Ukrainian (uk)

## Setup

### 1. Install potrans

You have several options to install potrans:

**Option A: Global installation (recommended)**
```bash
composer global require om/potrans
```

**Option B: Local project installation**
```bash
composer require --dev om/potrans
```

**Option C: Custom path**
Set the `POTRANS_PATH` environment variable to point to your potrans binary:
```bash
export POTRANS_PATH="/path/to/potrans"
```

### 2. Get DeepL API Key

1. Sign up for a DeepL API account at https://www.deepl.com/pro-api
2. Get your API key from the account dashboard
3. Set the API key as an environment variable:

```bash
export DEEPL_API_KEY="your-api-key-here"
```

Or pass it directly to the script using the `--api-key` parameter.

## Usage

### NPM Scripts

```bash
# Translate all languages (skip existing files)
npm run translate

# Force re-translation of all languages
npm run translate:force

# Build with translations included
npm run build:translate
```

### Direct PHP Script Usage

```bash
# Basic translation
php scripts/translate.php

# With API key parameter
php scripts/translate.php --api-key=your-key-here

# Force re-translation of existing files
php scripts/translate.php --force

# Show help
php scripts/translate.php --help
```

## What the Script Does

1. **Reads the source POT file** (`lang/multisite-ultimate.pot`)
2. **Translates to each DeepL language** using the potrans library
3. **Generates PO files** with WordPress-compatible locale names
4. **Creates MO files** from the PO files using msgfmt
5. **Outputs all files** to the `lang/` directory

## File Naming Convention

The script generates files following WordPress naming conventions:

- `multisite-ultimate-{wp_locale}.po` - Translation files
- `multisite-ultimate-{wp_locale}.mo` - Compiled translation files

Examples:
- `multisite-ultimate-fr_FR.po` - French translation
- `multisite-ultimate-de_DE.po` - German translation
- `multisite-ultimate-pt_BR.po` - Brazilian Portuguese translation

## Integration with Build Process

The translation system integrates with the existing build process:

- `npm run build:dev` - Standard development build (no translation)
- `npm run build:translate` - Development build with translations
- Regular `npm run build` - Production build (no translation by default)

## Requirements

- PHP 7.4+ (same as plugin requirement)
- Composer dependencies installed
- DeepL API key
- `msgfmt` command available (part of gettext tools)

## Error Handling

The script includes comprehensive error handling:

- Validates API key presence
- Checks for required files and directories
- Reports translation failures
- Continues processing even if individual languages fail

## Cost Considerations

DeepL API charges per character translated. The script:

- Skips existing files by default (use `--force` to override)
- Only translates when the POT file is updated
- Provides progress feedback to monitor usage

## Troubleshooting

### Common Issues

1. **"DeepL API key is required"**
   - Set the `DEEPL_API_KEY` environment variable
   - Or use `--api-key` parameter

2. **"potrans binary not found"**
   - Install potrans globally: `composer global require om/potrans`
   - Or set `POTRANS_PATH` environment variable to the potrans binary location
   - Or install locally: `composer require --dev om/potrans`

3. **"msgfmt command not found"**
   - Install gettext tools: `sudo apt-get install gettext` (Ubuntu/Debian)

4. **Translation fails for specific language**
   - Check DeepL API limits and quotas
   - Verify the language code is supported
   - Check internet connection

### Debug Output

The script provides detailed output showing:
- Which languages are being processed
- Success/failure status for each translation
- Final statistics

## License

This translation system is part of Multisite Ultimate and follows the same GPL-3.0-or-later license.