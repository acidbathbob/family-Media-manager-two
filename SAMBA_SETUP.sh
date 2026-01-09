#!/bin/bash
###############################################################################
# Samba Setup for Family Media Manager Testing on Fedora
# Run this script with: bash SAMBA_SETUP.sh
###############################################################################

echo "Family Media Manager - Samba Setup for Fedora"
echo "=============================================="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "ERROR: This script must be run as root (use sudo)"
    exit 1
fi

# Get WordPress path from user
echo "First, let's find your WordPress installation."
read -p "Enter the full path to your WordPress directory (e.g., /home/bob/wordpress): " WP_PATH

if [ ! -d "$WP_PATH" ]; then
    echo "ERROR: WordPress directory not found at $WP_PATH"
    exit 1
fi

if [ ! -f "$WP_PATH/wp-config.php" ]; then
    echo "ERROR: wp-config.php not found. Is this the correct WordPress directory?"
    exit 1
fi

echo "✓ WordPress found at: $WP_PATH"
echo ""

# Step 1: Install Samba
echo "Step 1: Installing Samba..."
dnf install -y samba samba-client samba-common
if [ $? -ne 0 ]; then
    echo "ERROR: Failed to install Samba"
    exit 1
fi
echo "✓ Samba installed"
echo ""

# Step 2: Backup original config
echo "Step 2: Backing up original Samba configuration..."
if [ ! -f /etc/samba/smb.conf.backup ]; then
    cp /etc/samba/smb.conf /etc/samba/smb.conf.backup
    echo "✓ Backup created at /etc/samba/smb.conf.backup"
else
    echo "ℹ Backup already exists"
fi
echo ""

# Step 3: Create testing directories
echo "Step 3: Creating testing directories..."
mkdir -p /home/bob/fmm-testing/{windows,mac,feedback/{windows,mac}}
chmod 777 /home/bob/fmm-testing
echo "✓ Testing directories created"
echo ""

# Step 4: Create new Samba config
echo "Step 4: Creating Samba configuration..."

cat > /etc/samba/smb.conf << 'SAMBACONF'
[global]
   workgroup = WORKGROUP
   server string = Bob Home Server
   security = user
   passdb backend = tdbsam
   log file = /var/log/samba/log.%m
   max log size = 50
   dns proxy = no

[wordpress]
   path = __WPPATH__
   public = yes
   writable = yes
   browsable = yes
   force user = bob
   comment = WordPress Installation
   create mask = 0755
   directory mask = 0755

[testing]
   path = /home/bob/fmm-testing
   public = yes
   writable = yes
   browsable = yes
   comment = FMM Testing Results
   create mask = 0755
   directory mask = 0755
SAMBACONF

# Replace placeholder with actual WordPress path
sed -i "s|__WPPATH__|$WP_PATH|g" /etc/samba/smb.conf

echo "✓ Samba configuration created"
echo ""

# Step 5: Test config
echo "Step 5: Testing Samba configuration..."
testparm -s > /dev/null
if [ $? -ne 0 ]; then
    echo "ERROR: Samba configuration has errors"
    echo "Run: testparm"
    exit 1
fi
echo "✓ Configuration is valid"
echo ""

# Step 6: Enable and start Samba
echo "Step 6: Starting Samba service..."
systemctl enable smb
systemctl enable nmb
systemctl restart smb
systemctl restart nmb

if systemctl is-active --quiet smb; then
    echo "✓ Samba service started successfully"
else
    echo "ERROR: Failed to start Samba service"
    systemctl status smb
    exit 1
fi
echo ""

# Step 7: Set permissions
echo "Step 7: Setting file permissions..."
chmod 755 "$WP_PATH"
chown -R bob:bob /home/bob/fmm-testing
echo "✓ Permissions set"
echo ""

# Step 8: Add Samba user
echo "Step 8: Setting up Samba user..."
echo "You'll be prompted to set a password for Samba user 'bob'"
smbpasswd -a bob

echo ""
echo "=============================================="
echo "✓ Samba Setup Complete!"
echo "=============================================="
echo ""
echo "Your shares are now available at:"
echo "  Windows: \\\\bob490.co.uk\\wordpress"
echo "  Windows: \\\\bob490.co.uk\\testing"
echo "  Mac: smb://bob490.co.uk/wordpress"
echo "  Mac: smb://bob490.co.uk/testing"
echo ""
echo "WordPress path: $WP_PATH"
echo "Testing path: /home/bob/fmm-testing"
echo ""
echo "To verify shares are accessible:"
echo "  smbclient -L localhost -U bob"
echo ""
