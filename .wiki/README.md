# Multisite Ultimate Documentation

This folder contains the documentation for the Multisite Ultimate plugin in Markdown format. The documentation is organized as a GitHub Wiki.

## Documentation Structure

The documentation is organized into the following categories:

- **Getting Started**: Basic setup and installation guides
- **Introduction**: Overview of Multisite Ultimate
- **Managing Your Network**: How to manage your multisite network
- **Payment Gateways**: Setting up payment gateways
- **Integrations**: Integrating with other services
- **Addons**: Additional functionality through addons
- **Developers**: Information for developers
- **Your Customers Panel**: Managing customer accounts
- **FAQ**: Frequently asked questions

## How to Use This Documentation

You can browse the documentation directly on GitHub by visiting the [Wiki](https://github.com/superdav42/wp-multisite-waas/wiki).

### Automatic Sync

This documentation is automatically synced to the GitHub wiki whenever changes are pushed to the main branch. The sync is handled by a GitHub Action workflow that copies the content of this directory to the wiki repository.

## Images

The documentation includes placeholder images for screenshots and diagrams. The original images were hosted on external servers that require authentication to access. See the `assets/images/placeholders/README.md` file for more information.

## Converting HTML to Markdown

This folder includes scripts to convert HTML documentation to Markdown format. To run the conversion:

1. Install the required dependencies:
   ```
   pip install beautifulsoup4 html2text
   ```

2. Run the conversion script:
   ```
   python convert_simple.py
   ```

## Contributing

Contributions to improve the documentation are welcome. Please feel free to submit pull requests with corrections or additions.

## License

This documentation is licensed under the same license as the Multisite Ultimate plugin.
