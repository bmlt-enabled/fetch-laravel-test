#!/bin/bash
#
# Script to iterate over API endpoints and fetch content from all available resources and languages.
#

set -euo pipefail

# Base API URL
BASE_URL="https://fetch-laravel-test-main-bh3tju.laravel.cloud"

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "Fetching main endpoint: ${BASE_URL}"
echo "================================================================================"

# Fetch main endpoint
main_data=$(curl -sH "Accept: application/json" "${BASE_URL}")

# Parse and display available resources
echo -e "\n${GREEN}Available Resources:${NC}"
echo "$main_data" | jq -r 'to_entries[] | "  - \(.key): \(.value.name) (\(.value.languages | length) languages)"'

echo ""
echo "================================================================================"
echo "Fetching content from all endpoints..."
echo "================================================================================"
echo ""

# Iterate over each resource
echo "$main_data" | jq -c 'to_entries[]' | while IFS= read -r resource; do
    resource_key=$(echo "$resource" | jq -r '.key')
    resource_name=$(echo "$resource" | jq -r '.value.name')
    base_url=$(echo "$resource" | jq -r '.value.base_url')
    
    echo ""
    echo "################################################################################"
    echo "# Resource: ${resource_name} (${resource_key})"
    echo "################################################################################"
    echo ""
    
    # Iterate over each language
    echo "$resource" | jq -c '.value.languages | to_entries[]' | while IFS= read -r language; do
        lang_code=$(echo "$language" | jq -r '.key')
        lang_name=$(echo "$language" | jq -r '.value')
        
        endpoint_url="${BASE_URL}${base_url}/${lang_code}"
        
        echo ""
        echo -e "${YELLOW}Language: ${lang_name} (${lang_code})${NC}"
        echo -e "${BLUE}URL: ${endpoint_url}${NC}"
        echo "--------------------------------------------------------------------------------"
        
        # Fetch the content
        content=$(curl -sH "Accept: application/json" "${endpoint_url}")
        
        # Display key information
        echo "$content" | jq -r '
            if .date then "Date: \(.date)" else empty end,
            if .title then "Title: \(.title)" else empty end,
            if .page then "Page: \(.page)" else empty end,
            if .content and (.content | type == "array") then 
                "\nContent preview (\(.content | length) paragraphs):",
                "  \(.content[0][:150])..."
            else empty end
        ' 2>/dev/null || echo "Failed to parse content"
        
        # Uncomment to save each response to a file:
        # output_file="${resource_key}_${lang_code}.json"
        # echo "$content" | jq '.' > "$output_file"
        # echo -e "\n${GREEN}Saved to: ${output_file}${NC}"
        
        echo ""
    done
done

echo ""
echo "================================================================================"
echo "Iteration complete!"
echo "================================================================================"
