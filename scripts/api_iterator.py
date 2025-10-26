#!/usr/bin/env python3
"""
Script to iterate over API endpoints and fetch content from all available resources and languages.
"""

import requests
import json
import sys
from typing import Dict, Any

# Base API URL
BASE_URL = "https://fetch-laravel-test-main-bh3tju.laravel.cloud"

def fetch_json(url: str) -> Dict[Any, Any]:
    """
    Fetch JSON data from a URL.
    
    Args:
        url: The URL to fetch
        
    Returns:
        Parsed JSON data as a dictionary
    """
    headers = {"Accept": "application/json"}
    try:
        response = requests.get(url, headers=headers)
        response.raise_for_status()
        return response.json()
    except requests.exceptions.RequestException as e:
        print(f"Error fetching {url}: {e}", file=sys.stderr)
        return {}

def main():
    """Main function to iterate over all API endpoints."""
    
    # Fetch the main endpoint to get available resources
    print(f"Fetching main endpoint: {BASE_URL}")
    print("=" * 80)
    
    main_data = fetch_json(BASE_URL)
    
    if not main_data:
        print("Failed to fetch main endpoint data")
        return
    
    # Display available resources
    print(f"\nFound {len(main_data)} resources:")
    for resource_key, resource_info in main_data.items():
        print(f"  - {resource_key}: {resource_info.get('name', 'Unknown')}")
        print(f"    Languages: {len(resource_info.get('languages', {}))} available")
    
    print("\n" + "=" * 80)
    print("Fetching content from all endpoints...")
    print("=" * 80 + "\n")
    
    # Iterate over each resource and language
    for resource_key, resource_info in main_data.items():
        resource_name = resource_info.get('name', resource_key)
        base_url = resource_info.get('base_url', f"/{resource_key}")
        languages = resource_info.get('languages', {})
        
        print(f"\n{'#' * 80}")
        print(f"# Resource: {resource_name} ({resource_key})")
        print(f"{'#' * 80}\n")
        
        for lang_code, lang_name in languages.items():
            endpoint_url = f"{BASE_URL}{base_url}/{lang_code}"
            
            print(f"\nLanguage: {lang_name} ({lang_code})")
            print(f"URL: {endpoint_url}")
            print("-" * 80)
            
            content_data = fetch_json(endpoint_url)
            
            if content_data:
                # Display key information from the response
                if 'date' in content_data:
                    print(f"Date: {content_data.get('date')}")
                if 'title' in content_data:
                    print(f"Title: {content_data.get('title')}")
                if 'page' in content_data:
                    print(f"Page: {content_data.get('page')}")
                
                # Show a preview of the content
                if 'content' in content_data and isinstance(content_data['content'], list):
                    print(f"\nContent preview ({len(content_data['content'])} paragraphs):")
                    if content_data['content']:
                        preview = content_data['content'][0]
                        if len(preview) > 150:
                            preview = preview[:150] + "..."
                        print(f"  {preview}")
                
                # Option to save full response
                # Uncomment the following lines to save each response to a file:
                # filename = f"{resource_key}_{lang_code}.json"
                # with open(filename, 'w', encoding='utf-8') as f:
                #     json.dump(content_data, f, indent=2, ensure_ascii=False)
                # print(f"\nSaved to: {filename}")
                
            else:
                print("Failed to fetch content")
            
            print()
    
    print("\n" + "=" * 80)
    print("Iteration complete!")
    print("=" * 80)

if __name__ == "__main__":
    main()
