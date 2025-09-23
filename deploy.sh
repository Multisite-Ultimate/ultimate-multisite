#!/bin/bash

# Ultimate Multisite - WordPress.org SVN Deploy Script
# Builds the plugin and deploys it to the WordPress.org plugin repository
# 
# Usage: ./deploy.sh [version]
#
# Requirements:
# - SVN_USERNAME environment variable (WordPress.org username)
# - SVN_PASSWORD environment variable (WordPress.org password)
# - svn command line tool installed
#
# Example:
# export SVN_USERNAME="superdav42"
# export SVN_PASSWORD="your-password"
# ./deploy.sh 2.4.4

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Plugin configuration
PLUGIN_SLUG="ultimate-multisite"
SVN_URL="https://plugins.svn.wordpress.org/$PLUGIN_SLUG"
MAIN_PLUGIN_FILE="ultimate-multisite.php"
README_TXT="readme.txt"
PACKAGE_JSON="package.json"

# Directory paths
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TEMP_DIR="/tmp/ultimate-multisite-deploy"
SVN_DIR="$TEMP_DIR/svn"
BUILD_DIR="$TEMP_DIR/build"

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to validate environment
validate_environment() {
    print_status "Validating environment..."
    
    # Check required commands
    if ! command_exists svn; then
        print_error "SVN is not installed. Please install SVN to continue."
        exit 1
    fi
    
    if ! command_exists node; then
        print_error "Node.js is not installed. Please install Node.js to continue."
        exit 1
    fi
    
    if ! command_exists npm; then
        print_error "npm is not installed. Please install npm to continue."
        exit 1
    fi
    
    if ! command_exists composer; then
        print_error "Composer is not installed. Please install Composer to continue."
        exit 1
    fi
    
    # Check environment variables
    if [[ -z "$SVN_USERNAME" ]]; then
        print_error "SVN_USERNAME environment variable is required"
        print_error "Set it with: export SVN_USERNAME=\"superdav42\""
        exit 1
    fi
    
    if [[ -z "$SVN_PASSWORD" ]]; then
        print_error "SVN_PASSWORD environment variable is required"
        print_error "Set it with: export SVN_PASSWORD=\"your-password\""
        exit 1
    fi
    
    # Check if we're in the right directory
    if [[ ! -f "$MAIN_PLUGIN_FILE" ]]; then
        print_error "Main plugin file ($MAIN_PLUGIN_FILE) not found. Please run this script from the plugin root directory."
        exit 1
    fi
    
    print_success "Environment validation passed"
}

# Function to extract version from file
get_version_from_file() {
    local file=$1
    local pattern=$2
    
    if [[ ! -f "$file" ]]; then
        print_error "File $file not found"
        return 1
    fi
    
    local version=$(grep "$pattern" "$file" | head -1 | sed -E "s/.*$pattern([0-9]+\.[0-9]+\.[0-9]+).*/\1/")
    
    if [[ -z "$version" ]]; then
        print_error "Could not extract version from $file using pattern $pattern"
        return 1
    fi
    
    echo "$version"
}

# Function to validate version consistency
validate_versions() {
    print_status "Validating version consistency..."
    
    # Get versions from different files
    local plugin_version=$(get_version_from_file "$MAIN_PLUGIN_FILE" "Version: ")
    local readme_version=$(get_version_from_file "$README_TXT" "Stable tag: ")
    local package_version=$(get_version_from_file "$PACKAGE_JSON" "\"version\": \"")
    
    print_status "Found versions:"
    print_status "  Plugin file: $plugin_version"
    print_status "  readme.txt: $readme_version"
    print_status "  package.json: $package_version"
    
    # Check if all versions match
    if [[ "$plugin_version" != "$readme_version" ]] || [[ "$plugin_version" != "$package_version" ]]; then
        print_error "Version mismatch detected!"
        print_error "All files must have the same version number before deployment."
        print_error "Please update all version numbers to match and try again."
        exit 1
    fi
    
    # If a version was provided as argument, validate it matches
    if [[ -n "$1" ]] && [[ "$1" != "$plugin_version" ]]; then
        print_error "Provided version ($1) doesn't match plugin version ($plugin_version)"
        print_error "Please update the plugin version or use the correct version argument."
        exit 1
    fi
    
    echo "$plugin_version"
}

# Function to run build process
build_plugin() {
    print_status "Building plugin..."
    
    # Clean up any previous builds
    if [[ -d "node_modules" ]]; then
        print_status "Cleaning previous node_modules..."
        rm -rf node_modules
    fi
    
    # Install dependencies and run build
    print_status "Installing npm dependencies..."
    npm install
    
    print_status "Running build process..."
    npm run build
    
    print_success "Plugin build completed"
}

# Function to prepare SVN repository
prepare_svn_repo() {
    local version=$1
    
    print_status "Preparing SVN repository..."
    
    # Clean up temp directory
    if [[ -d "$TEMP_DIR" ]]; then
        rm -rf "$TEMP_DIR"
    fi
    mkdir -p "$TEMP_DIR"
    
    # Checkout SVN repository
    print_status "Checking out SVN repository..."
    svn checkout "$SVN_URL" "$SVN_DIR" --username "$SVN_USERNAME" --password "$SVN_PASSWORD" --non-interactive
    
    # Create version directory if it doesn't exist
    if [[ ! -d "$SVN_DIR/tags/$version" ]]; then
        mkdir -p "$SVN_DIR/tags/$version"
    fi
    
    print_success "SVN repository prepared"
}

# Function to copy plugin files to SVN
copy_plugin_files() {
    local version=$1
    
    print_status "Copying plugin files..."
    
    # Create build directory
    mkdir -p "$BUILD_DIR"
    
    # Copy all files except excluded ones
    rsync -av \
        --exclude='.git*' \
        --exclude='node_modules/' \
        --exclude='vendor/bin/' \
        --exclude='vendor/*/tests/' \
        --exclude='vendor/*/test/' \
        --exclude='vendor/*/*/tests/' \
        --exclude='vendor/*/*/test/' \
        --exclude='tests/' \
        --exclude='cypress/' \
        --exclude='cypress.config.*' \
        --exclude='.wp-env.json' \
        --exclude='deploy.sh' \
        --exclude='*.log' \
        --exclude='.env*' \
        --exclude='docker-compose.yml' \
        --exclude='Dockerfile' \
        --exclude='.dockerignore' \
        --exclude='bin/' \
        --exclude='scripts/archive.js' \
        --exclude='scripts/post-archive.js' \
        --exclude='scripts/clean-*.js' \
        --exclude='scripts/copy-libs.js' \
        --exclude='scripts/uglify.js' \
        --exclude='scripts/cleancss.js' \
        --exclude='scripts/makepot.js' \
        --exclude='utils/' \
        --exclude='encrypt-sectrets.php' \
        --exclude='*.zip' \
        --exclude='*.tar.gz' \
        ./ "$BUILD_DIR/"
    
    # Copy to trunk
    print_status "Copying to trunk..."
    rsync -av --delete "$BUILD_DIR/" "$SVN_DIR/trunk/"
    
    # Copy to version tag
    print_status "Copying to version tag $version..."
    rsync -av --delete "$BUILD_DIR/" "$SVN_DIR/tags/$version/"
    
    print_success "Plugin files copied"
}

# Function to handle SVN operations
commit_to_svn() {
    local version=$1
    
    print_status "Preparing SVN commit..."
    
    cd "$SVN_DIR"
    
    # Add any new files
    svn add --force .
    
    # Remove any deleted files
    svn status | grep '^!' | awk '{print $2}' | xargs -r svn remove
    
    # Show status
    print_status "SVN status:"
    svn status
    
    # Ask for confirmation
    echo ""
    print_warning "About to commit version $version to WordPress.org repository."
    print_warning "This will make the plugin version publicly available."
    read -p "Do you want to continue? (y/N): " -n 1 -r
    echo ""
    
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_status "Deployment cancelled by user"
        exit 0
    fi
    
    # Commit changes
    print_status "Committing to SVN..."
    svn commit -m "Deploy version $version" --username "$SVN_USERNAME" --password "$SVN_PASSWORD" --non-interactive
    
    print_success "Successfully deployed version $version to WordPress.org!"
}

# Function to cleanup
cleanup() {
    print_status "Cleaning up temporary files..."
    if [[ -d "$TEMP_DIR" ]]; then
        rm -rf "$TEMP_DIR"
    fi
    print_success "Cleanup completed"
}

# Main deployment function
main() {
    local provided_version=$1
    
    print_status "Starting WordPress.org deployment process..."
    print_status "Plugin: Ultimate Multisite"
    print_status "SVN URL: $SVN_URL"
    
    # Validate environment
    validate_environment
    
    # Validate and get version
    local version=$(validate_versions "$provided_version")
    print_success "Using version: $version"
    
    # Build the plugin
    build_plugin
    
    # Prepare SVN repository
    prepare_svn_repo "$version"
    
    # Copy plugin files
    copy_plugin_files "$version"
    
    # Commit to SVN
    commit_to_svn "$version"
    
    # Cleanup
    cleanup
    
    print_success "Deployment completed successfully!"
    print_success "Plugin version $version is now available on WordPress.org"
}

# Handle script interruption
trap cleanup EXIT INT TERM

# Check if version argument is provided
if [[ $# -gt 1 ]]; then
    print_error "Too many arguments. Usage: $0 [version]"
    exit 1
fi

# Run main function
main "$1"