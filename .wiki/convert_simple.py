#!/usr/bin/env python3
"""
Simple script to convert HTML documentation to Markdown for GitHub Wiki.
"""

import os
import re
import glob
from bs4 import BeautifulSoup
import html2text

# Configuration
HTML_DIR = os.path.expanduser("~/Git/wp-multisite-waas/wiki-conversion/help.wpultimo.com/hc/wp-ultimo/articles/")
OUTPUT_DIR = os.path.expanduser("~/Git/wp-multisite-waas/.wiki/")

# Convert HTML to Markdown
def convert_html_to_markdown(html_content):
    """Convert HTML content to Markdown."""
    # Configure html2text
    h = html2text.HTML2Text()
    h.ignore_links = False
    h.ignore_images = False
    h.ignore_emphasis = False
    h.ignore_tables = False
    h.body_width = 0  # Don't wrap text

    # Convert HTML to Markdown
    markdown = h.handle(html_content)

    # Clean up the markdown
    markdown = re.sub(r'\n{3,}', '\n\n', markdown)  # Remove excessive newlines

    return markdown

# Process HTML files
def process_html_files():
    """Process HTML files and convert them to Markdown."""
    # Create a Home.md file for the wiki
    with open(os.path.join(OUTPUT_DIR, 'Home.md'), 'w', encoding='utf-8') as home_file:
        home_file.write("# Multisite Ultimate Documentation\n\n")
        home_file.write("Welcome to the Multisite Ultimate documentation. This wiki contains all the information you need to get started with Multisite Ultimate.\n\n")
        home_file.write("## Articles\n\n")

    # Create a sidebar file for the wiki
    with open(os.path.join(OUTPUT_DIR, '_Sidebar.md'), 'w', encoding='utf-8') as sidebar_file:
        sidebar_file.write("# Documentation\n\n")
        sidebar_file.write("- [Home](Home)\n")
        sidebar_file.write("- **Articles**\n")

    # Process article HTML files
    article_count = 0
    for html_file in glob.glob(os.path.join(HTML_DIR, "*.html")):
        file_name = os.path.basename(html_file)

        try:
            # Read the HTML file
            with open(html_file, 'r', encoding='utf-8') as f:
                html_content = f.read()

                # Parse HTML
                soup = BeautifulSoup(html_content, 'html.parser')

                # Get the article title
                title = None
                title_elem = soup.find('h1')
                if title_elem:
                    title = title_elem.text.strip()
                else:
                    title_elem = soup.find('title')
                    if title_elem:
                        title = title_elem.text.strip()
                    else:
                        # Use the filename as a fallback
                        title = os.path.basename(html_file).replace('.html', '').replace('-', ' ').title()

                # Get the article content
                content = None
                content_elem = soup.find('article')
                if content_elem:
                    content = content_elem
                else:
                    content_elem = soup.find('div', class_='article-body')
                    if content_elem:
                        content = content_elem
                    else:
                        content_elem = soup.find('div', class_='content')
                        if content_elem:
                            content = content_elem
                        else:
                            content_elem = soup.find('body')
                            if content_elem:
                                content = content_elem
                            else:
                                print(f"No content found in {file_name}")
                                continue

                # Convert to Markdown
                markdown = convert_html_to_markdown(str(content))

                # Create a sanitized file name
                md_file_name = re.sub(r'[^a-zA-Z0-9-]', '-', title.lower())
                md_file_name = re.sub(r'-+', '-', md_file_name)
                md_file_name = md_file_name.strip('-')

                # Write the Markdown file
                with open(os.path.join(OUTPUT_DIR, f"{md_file_name}.md"), 'w', encoding='utf-8') as md_file:
                    md_file.write(f"# {title}\n\n")
                    md_file.write(markdown)

                # Add the article to the home page
                with open(os.path.join(OUTPUT_DIR, 'Home.md'), 'a', encoding='utf-8') as home_file:
                    home_file.write(f"- [{title}]({md_file_name})\n")

                # Add the article to the sidebar
                with open(os.path.join(OUTPUT_DIR, '_Sidebar.md'), 'a', encoding='utf-8') as sidebar_file:
                    sidebar_file.write(f"  - [{title}]({md_file_name})\n")

                article_count += 1
                print(f"Converted: {title} -> {md_file_name}.md")
        except Exception as e:
            print(f"Error processing {file_name}: {e}")

    print(f"Converted {article_count} articles")

# Main function
def main():
    """Main function."""
    print("Processing HTML files...")
    process_html_files()

    print("Conversion complete!")

if __name__ == "__main__":
    main()
