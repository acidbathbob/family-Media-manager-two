# Building macOS App for Family Media Manager Easy Setup

This guide explains how to build the Python GUI installer into a standalone macOS .app that non-technical users can run without Python installed.

## Overview

- **fmm-easy-setup.py** - Cross-platform Python GUI installer (same as Windows/Linux)
- **BUILD_MAC_APP.md** - This file (build instructions)
- **requirements.txt** - Python dependencies (PyInstaller)

## Requirements

### On Mac (for building the .app):
1. Python 3.8 or higher
2. PyInstaller library
3. An Apple computer (Intel or Apple Silicon)

### For Mac end users:
- Just the .app file - no Python needed!
- macOS 10.13 or later

## Step 1: Install Python on Mac

### Option A: Using Homebrew (Recommended)

1. Install Homebrew if you don't have it:
   ```bash
   /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
   ```

2. Install Python:
   ```bash
   brew install python3
   ```

3. Verify installation:
   ```bash
   python3 --version
   ```

### Option B: Download from python.org

1. Go to https://www.python.org/downloads/macos/
2. Download Python 3.11 or 3.12 for your Mac type:
   - **Apple Silicon** (M1/M2/M3): ARM64 version
   - **Intel Mac**: x86-64 version
3. Run the installer and follow the prompts
4. Verify installation:
   ```bash
   python3 --version
   ```

## Step 2: Install PyInstaller

Open Terminal and run:
```bash
pip3 install PyInstaller==6.1.0
```

Or install from requirements.txt:
```bash
pip3 install -r requirements.txt
```

## Step 3: Build the .app

Navigate to the project directory in Terminal:
```bash
cd /path/to/family-media-manager
```

### Option A: Simple Build (larger, easier)
```bash
pyinstaller --onefile --windowed --osx-bundle-identifier=com.familymediamanager.setup fmm-easy-setup.py
```

### Option B: Optimized Build (smaller, recommended)
```bash
pyinstaller --onefile --windowed \
  --osx-bundle-identifier=com.familymediamanager.setup \
  --add-data "family-media-manager.php:." \
  --add-data "includes:includes" \
  --add-data "admin:admin" \
  --add-data "public:public" \
  --add-data "pwa:pwa" \
  fmm-easy-setup.py
```

### Option C: With Custom Icon

If you have an app icon (create one at https://convertio.co/icns-converter/):

1. Save your icon as `app_icon.icns`
2. Run:
```bash
pyinstaller --onefile --windowed \
  --osx-bundle-identifier=com.familymediamanager.setup \
  --icon=app_icon.icns \
  --add-data "family-media-manager.php:." \
  --add-data "includes:includes" \
  --add-data "admin:admin" \
  --add-data "public:public" \
  --add-data "pwa:pwa" \
  fmm-easy-setup.py
```

## Step 4: Find Your .app

After the build completes, your app will be in:
```
dist/fmm-easy-setup.app
```

To verify it was built correctly:
```bash
ls -la dist/fmm-easy-setup.app
```

## Step 5: Test the App

### Option A: Double-click from Finder
1. Open Finder
2. Navigate to the `dist` folder
3. Double-click `fmm-easy-setup.app`

### Option B: Run from Terminal
```bash
dist/fmm-easy-setup.app/Contents/MacOS/fmm-easy-setup
```

## Step 6: Make It User-Friendly

### Create a DMG (Disk Image) for Distribution

1. Create a folder with the app and supporting files:
   ```bash
   mkdir -p FMM_Installer
   cp -r dist/fmm-easy-setup.app FMM_Installer/
   cp family-media-manager.php FMM_Installer/
   cp -r includes admin public pwa FMM_Installer/
   cp README_MACOS.md FMM_Installer/README.md
   ```

2. Create a DMG file:
   ```bash
   hdiutil create -volname "Family Media Manager" \
     -srcfolder FMM_Installer \
     -ov -format UDZO FMM_Installer.dmg
   ```

3. The resulting `FMM_Installer.dmg` can be distributed to Mac users

## Step 7: Sign the App (Optional but Recommended)

To avoid macOS security warnings, you can sign the app with your Apple Developer account:

```bash
codesign --deep --force --verify --verbose --sign - dist/fmm-easy-setup.app
```

For production distribution, use your actual signing certificate.

## Troubleshooting

### "command not found: pyinstaller"
- Make sure you installed PyInstaller: `pip3 install PyInstaller==6.1.0`
- Or use the full path: `/usr/local/bin/pyinstaller`

### App won't open / "damaged" error
1. Check that all plugin files are in the same location as the .app
2. Make sure the .app was built with the `--onefile` flag
3. Try removing quarantine attribute: `xattr -d com.apple.quarantine dist/fmm-easy-setup.app`

### "macOS cannot verify the developer" warning
This is normal for unsigned apps. Users can:
1. Right-click the app
2. Click "Open"
3. Click "Open" in the dialog

Or sign the app yourself (see Step 7).

### File not found errors when running
- Ensure all these directories are in the same folder as the .app:
  - `family-media-manager.php`
  - `includes/`
  - `admin/`
  - `public/`
  - `pwa/`

### App crashes on startup
1. Run from Terminal to see error messages:
   ```bash
   dist/fmm-easy-setup.app/Contents/MacOS/fmm-easy-setup
   ```
2. Check that tkinter is properly bundled
3. Try rebuilding with the simple build option

## Creating a Complete Distribution Package

Here's how to package everything for Mac users:

```bash
#!/bin/bash

# Clean previous builds
rm -rf dist build FMM_Installer FMM_Installer.dmg

# Build the app
pyinstaller --onefile --windowed \
  --osx-bundle-identifier=com.familymediamanager.setup \
  --add-data "family-media-manager.php:." \
  --add-data "includes:includes" \
  --add-data "admin:admin" \
  --add-data "public:public" \
  --add-data "pwa:pwa" \
  fmm-easy-setup.py

# Create distribution folder
mkdir -p FMM_Installer
cp -r dist/fmm-easy-setup.app FMM_Installer/
cp family-media-manager.php FMM_Installer/
cp -r includes admin public pwa FMM_Installer/
cp README_MACOS.md FMM_Installer/README.md

# Create DMG
hdiutil create -volname "Family Media Manager" \
  -srcfolder FMM_Installer \
  -ov -format UDZO FMM_Installer.dmg

echo "Done! DMG created: FMM_Installer.dmg"
```

Save as `build_mac.sh` and run with:
```bash
chmod +x build_mac.sh
./build_mac.sh
```

## Distribution Methods

### Method 1: DMG File (Recommended)
- Users download `FMM_Installer.dmg`
- Double-click to mount
- Drag `fmm-easy-setup.app` to Applications folder
- Launch from Applications

### Method 2: Direct .app
- Users download `fmm-easy-setup.app`
- Double-click to run (or drag to Applications)
- May see security warning on first run

### Method 3: ZIP File
- Compress the FMM_Installer folder into a ZIP
- Users extract and run the app

## Notes for Mac Users

- The .app is larger than the Python script (typically 50-100MB) because it includes Python
- First run may take a few seconds as Python initializes
- Configuration is saved to `~/.fmm-setup/config.json`
- The app works on both Intel and Apple Silicon Macs (if built on the correct architecture)

## Universal Binary (Intel + Apple Silicon)

To create a universal app that works on all Macs:

1. Build on both architectures (or use a build server):
   ```bash
   # On Intel Mac
   pyinstaller ... fmm-easy-setup.py
   
   # On Apple Silicon Mac
   pyinstaller ... fmm-easy-setup.py
   ```

2. Combine them:
   ```bash
   lipo -create intel_app arm64_app -output universal_app
   ```

Or use PyInstaller with universal support (Python 3.11+):
```bash
pyinstaller --target-architecture universal2 fmm-easy-setup.py
```

## Notarization (for App Store distribution)

For distribution on the Mac App Store or via Gatekeeper:

1. Get an Apple Developer account
2. Notarize your app:
   ```bash
   xcrun notarytool submit dist/fmm-easy-setup.app
   ```
3. Staple the notarization:
   ```bash
   xcrun stapler staple dist/fmm-easy-setup.app
   ```

This is optional but recommended for wide distribution.

## Updating the Script

If you update `fmm-easy-setup.py`:
1. Just rebuild the .app with the build command
2. Create a new DMG if using that distribution method
3. Distribute the new .app or DMG to users

## Cross-Platform Summary

Same Python script, different builds:
- **Windows**: Build .exe with PyInstaller (see BUILD_WINDOWS_EXE.md)
- **macOS**: Build .app with PyInstaller (this guide)
- **Linux**: Run directly with `python3 fmm-easy-setup.py`

## Support Resources

- PyInstaller docs: https://pyinstaller.org/
- Python on macOS: https://www.python.org/downloads/macos/
- macOS security: https://support.apple.com/en-us/HT202491

## Final Notes

- Keep your build scripts in version control
- Test each build on actual target systems if possible
- Create a changelog when updating the app
- Consider a version number in your app name (e.g., `fmm-easy-setup-v1.0.app`)
