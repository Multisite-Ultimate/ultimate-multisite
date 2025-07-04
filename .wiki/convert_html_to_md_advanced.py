#!/usr/bin/env python3
"""
Advanced script to convert HTML documentation to Markdown for GitHub Wiki.
This script attempts to detect categories and organize articles accordingly.
"""

import os
import re
import glob
from bs4 import BeautifulSoup
import html2text
import shutil

# Configuration
HTML_DIR = "../help.wpultimo.com/hc/wp-ultimo/articles/"
CATEGORIES_DIR = "../help.wpultimo.com/hc/wp-ultimo/en/categories/"
OUTPUT_DIR = "./"

# Create directories for categories
def create_category_directories():
    """Create directories for each category and return a mapping."""
    categories = {}
    
    # Process category HTML files
    for category_file in glob.glob(os.path.join(CATEGORIES_DIR, "*.html")):
        category_name = os.path.basename(category_file).replace(".html", "")
        
        # Read the category file
        with open(category_file, 'r', encoding='utf-8') as f:
            soup = BeautifulSoup(f.read(), 'html.parser')
            
            # Get the category title
            title_elem = soup.find('h1')
            if title_elem:
                title = title_elem.text.strip()
                
                # Create a sanitized directory name
                dir_name = category_name.lower().replace(" ", "-")
                
                # Create the directory
                os.makedirs(os.path.join(OUTPUT_DIR, dir_name), exist_ok=True)
                
                # Create a README.md for the category
                with open(os.path.join(OUTPUT_DIR, dir_name, "README.md"), 'w', encoding='utf-8') as readme:
                    readme.write(f"# {title}\n\n")
                    readme.write(f"This section contains documentation related to {title.lower()}.\n\n")
                    readme.write("## Articles\n\n")
                
                # Store the mapping
                categories[category_name] = {
                    'title': title,
                    'dir': dir_name,
                    'articles': []
                }
    
    return categories

# Extract category from HTML
def extract_category(soup):
    """Try to extract the category from the HTML."""
    # Look for category links or breadcrumbs
    breadcrumbs = soup.find_all('a', class_='breadcrumb')
    for breadcrumb in breadcrumbs:
        href = breadcrumb.get('href', '')
        if 'categories' in href:
            category = href.split('/')[-1].replace('.html', '')
            return category
    
    # If no category found, return default
    return "uncategorized"

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
def process_html_files(categories):
    """Process HTML files and convert them to Markdown."""
    # Create a Home.md file for the wiki
    with open(os.path.join(OUTPUT_DIR, 'Home.md'), 'w', encoding='utf-8') as home_file:
        home_file.write("# Multisite Ultimate Documentation\n\n")
        home_file.write("Welcome to the Multisite Ultimate documentation. This wiki contains all the information you need to get started with Multisite Ultimate.\n\n")
        home_file.write("## Categories\n\n")
        
        # Add categories to the home page
        for category_info in categories.values():
            home_file.write(f"- [{category_info['title']}]({category_info['dir']}/README)\n")
    
    # Create a sidebar file for the wiki
    with open(os.path.join(OUTPUT_DIR, '_Sidebar.md'), 'w', encoding='utf-8') as sidebar_file:
        sidebar_file.write("# Documentation\n\n")
        sidebar_file.write("- [Home](Home)\n")
        
        # Add categories to the sidebar
        for category_info in categories.values():
            sidebar_file.write(f"- **{category_info['title']}**\n")
    
    # Process article HTML files
    for html_file in glob.glob(os.path.join(HTML_DIR, "*.html")):
        file_name = os.path.basename(html_file)
        
        # Read the HTML file
        with open(html_file, 'r', encoding='utf-8') as f:
            html_content = f.read()
            
            # Parse HTML
            soup = BeautifulSoup(html_content, 'html.parser')
            
            # Get the article title
            title_elem = soup.find('h1')
            if not title_elem:
                continue
                
            title = title_elem.text.strip()
            
            # Get the article content
            content_elem = soup.find('article')
            if not content_elem:
                continue
                
            # Convert to Markdown
            markdown = convert_html_to_markdown(str(content_elem))
            
            # Try to determine the category
            category_name = extract_category(soup)
            
            # If category exists in our mapping, use it, otherwise use uncategorized
            if category_name in categories:
                category_info = categories[category_name]
            else:
                category_info = categories.get('uncategorized', {
                    'dir': 'uncategorized',
                    'title': 'Uncategorized',
                    'articles': []
                })
                if 'uncategorized' not in categories:
                    categories['uncategorized'] = category_info
                    os.makedirs(os.path.join(OUTPUT_DIR, 'uncategorized'), exist_ok=True)
                    with open(os.path.join(OUTPUT_DIR, 'uncategorized', "README.md"), 'w', encoding='utf-8') as readme:
                        readme.write("# Uncategorized\n\n")
                        readme.write("This section contains documentation that hasn't been categorized yet.\n\n")
                        readme.write("## Articles\n\n")
            
            # Create a sanitized file name
            md_file_name = re.sub(r'[^a-zA-Z0-9-]', '-', title.lower())
            md_file_name = re.sub(r'-+', '-', md_file_name)
            md_file_name = md_file_name.strip('-')
            
            # Write the Markdown file
            output_path = os.path.join(OUTPUT_DIR, category_info['dir'], f"{md_file_name}.md")
            with open(output_path, 'w', encoding='utf-8') as md_file:
                md_file.write(f"# {title}\n\n")
                md_file.write(markdown)
            
            # Add the article to the category's README
            with open(os.path.join(OUTPUT_DIR, category_info['dir'], "README.md"), 'a', encoding='utf-8') as readme:
                readme.write(f"- [{title}]({md_file_name})\n")
            
            # Add the article to the category's list
            category_info['articles'].append({
                'title': title,
                'file': md_file_name
            })
            
            print(f"Converted: {title} -> {category_info['dir']}/{md_file_name}.md")
    
    # Update the sidebar with articles
    with open(os.path.join(OUTPUT_DIR, '_Sidebar.md'), 'a', encoding='utf-8') as sidebar_file:
        for category_info in categories.values():
            if category_info['articles']:
                sidebar_file.write(f"  - [{category_info['title']}]({category_info['dir']}/README)\n")
                for article in category_info['articles']:
                    sidebar_file.write(f"    - [{article['title']}]({category_info['dir']}/{article['file']})\n")

# Main function
def main():
    """Main function."""
    print("Creating category directories...")
    categories = create_category_directories()
    
    print("Processing HTML files...")
    process_html_files(categories)
    
    print("Conversion complete!")

if __name__ == "__main__":
    main()
