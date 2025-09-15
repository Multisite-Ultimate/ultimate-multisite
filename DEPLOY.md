# Multisite Ultimate - WordPress.org Deploy Tool

This document explains how to use the automated deploy tool for deploying Multisite Ultimate to the WordPress.org plugin repository.

## Prerequisites

Before using the deploy tool, ensure you have:

1. **SVN Access**: Access to the WordPress.org SVN repository for `ultimate-multisite`
2. **Required Software**:
   - `svn` command line tool
   - `node` and `npm` (for building assets)
   - `composer` (for PHP dependencies)
   - `rsync` (for file copying)

3. **Environment Variables**:
   - `SVN_USERNAME`: Your WordPress.org username (`superdav42`)
   - `SVN_PASSWORD`: Your WordPress.org password

## Setup

1. **Set Environment Variables**:
   ```bash
   export SVN_USERNAME="superdav42"
   export SVN_PASSWORD="your-wordpress-org-password"
   ```

   For security, consider adding these to your shell profile (`.bashrc`, `.zshrc`, etc.) or use a `.env` file.

2. **Verify Access**:
   Test SVN access manually:
   ```bash
   svn checkout https://plugins.svn.wordpress.org/ultimate-multisite/trunk temp-test --username $SVN_USERNAME --password $SVN_PASSWORD
   rm -rf temp-test
   ```

## Version Management

Before deployment, ensure version consistency across all files:

- `multisite-ultimate.php` - Line 7: `Version: 2.4.3`
- `readme.txt` - Line 9: `Stable tag: 2.4.3`  
- `package.json` - Line 2: `"version": "2.4.3"`

The deploy script will validate these versions match before proceeding.

## Usage

### Basic Deployment

Deploy the current version (auto-detected from plugin files):
```bash
./deploy.sh
```

### Version-Specific Deployment

Deploy a specific version (must match plugin file versions):
```bash
./deploy.sh 2.4.4
```

## Deployment Process

The script performs these steps automatically:

1. **Environment Validation**
   - Checks for required commands (svn, node, npm, composer)
   - Validates environment variables
   - Confirms running from correct directory

2. **Version Validation**
   - Extracts versions from all relevant files
   - Ensures version consistency across:
     - `multisite-ultimate.php`
     - `readme.txt`
     - `package.json`
   - Validates provided version argument (if any)

3. **Plugin Build**
   - Installs npm dependencies
   - Runs `npm run build` to:
     - Install Composer dependencies (production only)
     - Minify JavaScript and CSS assets
     - Generate translation files
     - Create optimized plugin archive

4. **SVN Repository Preparation**
   - Creates temporary directory
   - Checks out WordPress.org SVN repository
   - Prepares trunk and tag directories

5. **File Copy**
   - Copies plugin files excluding development files:
     - Git files and directories
     - `node_modules/`
     - Test directories and files
     - Development scripts and tools
     - Docker and environment files
     - Build artifacts

6. **SVN Commit**
   - Adds new files to SVN
   - Removes deleted files
   - Shows status for review
   - **Prompts for confirmation** before committing
   - Commits to both trunk and version tag

7. **Cleanup**
   - Removes temporary directories and files

## Files Excluded from Deployment

The following files and directories are automatically excluded:

- `.git*` - Git files and directories
- `node_modules/` - npm dependencies
- `vendor/bin/` - Composer binary files
- `vendor/*/tests/` and `vendor/*/test/` - Vendor test files
- `tests/` - Plugin test directory
- `cypress/` - End-to-end test files
- Development configuration files
- Build scripts and utilities
- Docker and environment files
- Archive files (*.zip, *.tar.gz)

## Safety Features

1. **Version Validation**: Prevents deployment with mismatched versions
2. **Environment Checks**: Validates all required tools and credentials
3. **Confirmation Prompt**: Requires manual confirmation before SVN commit
4. **Cleanup**: Automatically cleans temporary files
5. **Error Handling**: Exits safely on any error

## Troubleshooting

### Common Issues

**"SVN_USERNAME environment variable is required"**
- Set the environment variable: `export SVN_USERNAME="superdav42"`

**"Version mismatch detected!"**
- Update version numbers in all files to match before deploying

**"Main plugin file not found"**
- Run the script from the `/home/dave/multisite/multisite-ultimate/` directory

**"SVN authentication failed"**
- Verify your WordPress.org credentials
- Check that your account has commit access to `ultimate-multisite`

**"Command not found: svn/node/composer"**
- Install missing required software

### Manual Recovery

If deployment fails partway through:

1. Check the SVN status:
   ```bash
   cd /tmp/multisite-ultimate-deploy/svn
   svn status
   ```

2. Manually commit if needed:
   ```bash
   svn commit -m "Deploy version X.X.X" --username $SVN_USERNAME --password $SVN_PASSWORD
   ```

3. Clean up:
   ```bash
   rm -rf /tmp/multisite-ultimate-deploy
   ```

## Security Notes

- Store credentials securely (use environment variables, not hardcoded)
- Consider using SSH keys for SVN if available
- The script never logs or stores passwords
- Temporary files are cleaned up automatically

## Release Workflow

Recommended workflow for releases:

1. **Update Version Numbers**:
   - Update `multisite-ultimate.php`
   - Update `readme.txt` (stable tag and changelog)
   - Update `package.json`

2. **Test Locally**:
   - Run `npm run build` 
   - Test plugin functionality
   - Run unit tests: `vendor/bin/phpunit`

3. **Commit to Git**:
   ```bash
   git add .
   git commit -m "Version 2.4.4 release"
   git tag v2.4.4
   git push origin main --tags
   ```

4. **Deploy to WordPress.org**:
   ```bash
   export SVN_USERNAME="superdav42"
   export SVN_PASSWORD="your-password"
   ./deploy.sh
   ```

5. **Verify Deployment**:
   - Check https://wordpress.org/plugins/ultimate-multisite/
   - Verify new version appears in admin

## Support

For issues with the deploy script:
- Check the GitHub repository: https://github.com/superdav42/wp-multisite-waas
- Open an issue with deployment logs and error messages