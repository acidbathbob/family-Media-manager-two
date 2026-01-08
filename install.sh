#!/bin/bash
###############################################################################
# Family Media Manager - Installation Script
# Automated setup for WordPress plugin and PWA
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Helper functions
print_header() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

# Welcome
clear
print_header "Family Media Manager - Installation"
echo "This script will help you install the WordPress plugin and PWA."
echo ""

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    print_warning "Running as root. Make sure file permissions are correct."
fi

# Step 1: WordPress Plugin Installation
print_header "Step 1: WordPress Plugin Installation"

read -p "Enter your WordPress installation path (e.g., /var/www/html/wordpress): " WP_PATH

if [ ! -d "$WP_PATH" ]; then
    print_error "WordPress path not found: $WP_PATH"
    exit 1
fi

if [ ! -f "$WP_PATH/wp-config.php" ]; then
    print_error "wp-config.php not found. Is this a valid WordPress installation?"
    exit 1
fi

print_success "WordPress found at: $WP_PATH"

# Copy plugin files
PLUGIN_DIR="$WP_PATH/wp-content/plugins/family-media-manager"

echo "Installing plugin to: $PLUGIN_DIR"

if [ -d "$PLUGIN_DIR" ]; then
    read -p "Plugin directory already exists. Overwrite? (y/n): " OVERWRITE
    if [ "$OVERWRITE" != "y" ]; then
        print_warning "Skipping plugin installation"
    else
        rm -rf "$PLUGIN_DIR"
        mkdir -p "$PLUGIN_DIR"
        cp -r family-media-manager.php includes/ admin/ public/ "$PLUGIN_DIR/"
        print_success "Plugin files updated"
    fi
else
    mkdir -p "$PLUGIN_DIR"
    cp -r family-media-manager.php includes/ admin/ public/ "$PLUGIN_DIR/"
    print_success "Plugin files copied"
fi

# Set permissions
chown -R www-data:www-data "$PLUGIN_DIR" 2>/dev/null || true
print_success "Permissions set"

# Step 2: PWA Installation
print_header "Step 2: PWA Installation"

read -p "Do you want to install the PWA? (y/n): " INSTALL_PWA

if [ "$INSTALL_PWA" = "y" ]; then
    read -p "Enter PWA installation path (e.g., /var/www/gallery): " PWA_PATH
    
    if [ ! -d "$PWA_PATH" ]; then
        mkdir -p "$PWA_PATH"
        print_success "Created directory: $PWA_PATH"
    fi
    
    cp -r pwa/* "$PWA_PATH/"
    print_success "PWA files copied"
    
    # Update API base URL in app.js
    read -p "Enter your WordPress URL (e.g., https://yoursite.com): " WP_URL
    
    sed -i "s|window.location.origin|'$WP_URL'|g" "$PWA_PATH/js/app.js"
    print_success "API URL configured"
    
    # Set permissions
    chown -R www-data:www-data "$PWA_PATH" 2>/dev/null || true
    print_success "PWA permissions set"
    
    # Create .htaccess for HTTPS redirect
    cat > "$PWA_PATH/.htaccess" << 'EOF'
# Force HTTPS (required for PWA)
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Service Worker
<Files "service-worker.js">
    Header set Service-Worker-Allowed "/"
    Header set Cache-Control "max-age=0, no-cache, no-store, must-revalidate"
</Files>

# Cache static assets
<FilesMatch "\.(css|js|jpg|jpeg|png|gif|webp)$">
    Header set Cache-Control "max-age=31536000, public"
</FilesMatch>
EOF
    print_success "Created .htaccess for HTTPS"
fi

# Step 3: Database Setup
print_header "Step 3: Database Configuration"

echo "The plugin will create the necessary database tables on activation."
print_warning "Make sure to activate the plugin in WordPress Admin → Plugins"

# Step 4: Google Drive API Setup
print_header "Step 4: Google Drive API Configuration"

echo "To complete setup, you need to:"
echo ""
echo "1. Go to https://console.cloud.google.com"
echo "2. Create a new project (or use existing)"
echo "3. Enable Google Drive API"
echo "4. Create OAuth 2.0 credentials"
echo "5. Add redirect URI: $WP_URL/wp-admin/admin.php?page=family-media-manager-settings&action=oauth_callback"
echo ""
print_warning "Save your Client ID and Client Secret!"

# Step 5: Final Instructions
print_header "Installation Complete!"

echo "Next steps:"
echo ""
echo "1. Activate plugin in WordPress:"
echo "   ${BLUE}→ WordPress Admin → Plugins → Family Media Manager${NC}"
echo ""
echo "2. Configure Google Drive:"
echo "   ${BLUE}→ Family Gallery → Settings${NC}"
echo "   ${BLUE}→ Enter Client ID and Secret${NC}"
echo "   ${BLUE}→ Click 'Connect Google Drive'${NC}"
echo ""
echo "3. Add family members:"
echo "   ${BLUE}→ Family Gallery → Family Members${NC}"
echo ""

if [ "$INSTALL_PWA" = "y" ]; then
    echo "4. Access PWA:"
    echo "   ${BLUE}→ https://your-pwa-domain.com${NC}"
    echo ""
    print_warning "Make sure HTTPS is enabled (required for PWA)"
fi

echo ""
print_success "Installation script completed!"
echo ""
echo "Need help? Check:"
echo "  - README.md for detailed documentation"
echo "  - pwa/README.md for PWA deployment guide"
echo ""
