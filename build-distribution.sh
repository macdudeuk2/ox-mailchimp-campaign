#!/bin/bash

# Mailchimp Campaign Form Plugin Distribution Builder
# This script creates a clean distribution package of the plugin

# Set variables
PLUGIN_NAME="mailchimp-campaign-form"
VERSION="1.1.2"
DIST_DIR="../${PLUGIN_NAME}-${VERSION}"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Building Mailchimp Campaign Form Plugin Distribution${NC}"
echo "=================================================="

# Check if we're in the right directory
if [ ! -f "mailchimp-campaign-form.php" ]; then
    echo -e "${RED}Error: Please run this script from the plugin directory${NC}"
    exit 1
fi

# Create distribution directory
echo -e "${YELLOW}Creating distribution directory...${NC}"
rm -rf "$DIST_DIR"
mkdir -p "$DIST_DIR"

# Copy plugin files
echo -e "${YELLOW}Copying plugin files...${NC}"
cp -r assets "$DIST_DIR/"
cp -r includes "$DIST_DIR/"
cp -r admin "$DIST_DIR/"
cp -r templates "$DIST_DIR/"
cp *.php "$DIST_DIR/"
cp *.md "$DIST_DIR/"
cp LICENSE "$DIST_DIR/"

# Remove development files
echo -e "${YELLOW}Removing development files...${NC}"
rm -f "$DIST_DIR/build-distribution.sh"
rm -f "$DIST_DIR/debug-api.php"
rm -f "$DIST_DIR/test-page.php"

# Create zip file
echo -e "${YELLOW}Creating zip archive...${NC}"
cd ..
zip -r "${PLUGIN_NAME}-${VERSION}.zip" "${PLUGIN_NAME}-${VERSION}/"

# Clean up
echo -e "${YELLOW}Cleaning up...${NC}"
rm -rf "${PLUGIN_NAME}-${VERSION}"

echo -e "${GREEN}Distribution package created: ${PLUGIN_NAME}-${VERSION}.zip${NC}"
echo -e "${GREEN}Package is ready for distribution!${NC}"

# Display package contents
echo ""
echo -e "${YELLOW}Package contents:${NC}"
unzip -l "${PLUGIN_NAME}-${VERSION}.zip" | head -20
echo "..." 