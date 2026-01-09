# Building Windows EXE for Family Media Manager Easy Setup

This guide explains how to build the Python GUI installer into a standalone Windows .exe file that non-technical users can run without Python installed.

## Overview

- **fmm-easy-setup.py** - Cross-platform Python GUI installer (works on Windows, Linux, macOS)
- **BUILD_WINDOWS_EXE.md** - This file (build instructions)
- **requirements.txt** - Python dependencies (only PyInstaller)

## Requirements

### On Windows (for building the .exe):
1. Python 3.8 or higher (https://www.python.org/downloads/)
2. PyInstaller library

### For end users:
- Just the .exe file - no Python needed!

## Step 1: Install Python (Windows)

1. Go to https://www.python.org/downloads/
2. Download Python 3.11 or 3.12
3. Run the installer
4. **IMPORTANT:** Check the box "Add Python to PATH"
5. Click "Install Now"

Verify installation by opening Command Prompt and typing:
```
python --version
```

## Step 2: Install PyInstaller

Open Command Prompt and run:
```
pip install PyInstaller==6.1.0
```

Or install from requirements.txt:
```
pip install -r requirements.txt
```

## Step 3: Build the EXE

Navigate to the project directory in Command Prompt:
```
cd C:\path\to\family-media-manager
```

### Option A: Simple Build (larger file, easier)
```
pyinstaller --onefile --windowed fmm-easy-setup.py
```

### Option B: Optimized Build (smaller file, recommended)
```
pyinstaller --onefile --windowed --add-data "family-media-manager.php:." --add-data "includes:includes" --add-data "admin:admin" --add-data "public:public" --add-data "pwa:pwa" fmm-easy-setup.py
```

### Option C: Custom Build with Icon (if you have an icon file)
```
pyinstaller --onefile --windowed --icon=icon.ico --add-data "family-media-manager.php:." --add-data "includes:includes" --add-data "admin:admin" --add-data "public:public" --add-data "pwa:pwa" fmm-easy-setup.py
```

## Step 4: Find Your EXE

After the build completes, your .exe will be in:
```
dist/fmm-easy-setup.exe
```

## Step 5: Test the EXE

Double-click `dist/fmm-easy-setup.exe` to test it works properly.

## Distribution to Users

1. Copy `dist/fmm-easy-setup.exe` to a folder
2. Make sure this folder also contains:
   - `family-media-manager.php`
   - `includes/` directory
   - `admin/` directory
   - `public/` directory
   - `pwa/` directory

3. You can create a ZIP file with all these contents for easy distribution
4. Users just extract the ZIP and double-click `fmm-easy-setup.exe`

## Troubleshooting

### "python is not recognized"
- Make sure Python was installed with "Add Python to PATH" checked
- Try restarting Command Prompt or your computer

### "PyInstaller not found"
- Run `pip install PyInstaller==6.1.0` again
- Make sure pip is in your PATH (usually installed automatically with Python)

### EXE won't start or gives errors
- Verify all plugin files are in the same directory as the EXE
- Run the EXE from Command Prompt to see error messages:
  ```
  fmm-easy-setup.exe
  ```

### File copy errors when running EXE
- Make sure all source directories exist in the same folder as the EXE:
  - `family-media-manager.php`
  - `includes/`
  - `admin/`
  - `public/`
  - `pwa/`

## Creating an Installer (Advanced)

For a professional installer with an installation wizard, you can use NSIS:

1. Download NSIS: http://nsis.sourceforge.net/
2. Create a `.nsi` script that bundles the .exe and required files
3. NSIS will create a proper installer `.exe` that users run

This is optional but creates a more professional experience.

## Automation Script (Python)

You can also create a Python script to automate the build process:

```python
#!/usr/bin/env python3
import subprocess
import shutil
import os
from pathlib import Path

def build_exe():
    # Clean previous builds
    if Path("dist").exists():
        shutil.rmtree("dist")
    if Path("build").exists():
        shutil.rmtree("build")
    
    # Run PyInstaller
    cmd = [
        "pyinstaller",
        "--onefile",
        "--windowed",
        "--add-data", "family-media-manager.php:.",
        "--add-data", "includes:includes",
        "--add-data", "admin:admin",
        "--add-data", "public:public",
        "--add-data", "pwa:pwa",
        "fmm-easy-setup.py"
    ]
    
    print("Building EXE...")
    subprocess.run(cmd, check=True)
    print("Done! EXE is at: dist/fmm-easy-setup.exe")

if __name__ == "__main__":
    build_exe()
```

Save this as `build_exe.py` and run with `python build_exe.py`

## How It Works

The Python script uses tkinter (built into Python) to create a GUI. PyInstaller bundles Python, tkinter, and your script into a single .exe file that:

1. Runs without requiring Python to be installed
2. Works on any Windows 10+ system
3. Can be distributed as a single file or folder

## Updating the Script

If you update `fmm-easy-setup.py`:
1. Just rebuild the EXE with the same PyInstaller command
2. Distribute the new .exe to users

## Cross-Platform Distribution

The same `fmm-easy-setup.py` script works on:
- **Windows**: Build .exe with PyInstaller (this guide)
- **Linux**: Run directly with `python3 fmm-easy-setup.py`
- **macOS**: Build .app with `pyinstaller --onefile --windowed fmm-easy-setup.py`

## Notes

- The EXE is larger than the Python script (typically 50-100MB) because it includes Python itself
- First run may take a few seconds as Python initializes
- Users may see Windows SmartScreen warning if distributing to many users (harmless)
- Configuration is saved to `~/.fmm-setup/config.json` after each setup

## Support

If users encounter issues:
1. Make sure all plugin files are in the same directory as the EXE
2. Ensure Windows Defender/antivirus isn't blocking the EXE
3. Try running as Administrator (right-click → Run as administrator)
4. Check that plugin files weren't corrupted during distribution
