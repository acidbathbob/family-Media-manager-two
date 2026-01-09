# Testing Mac Installer

Guide for testing the Family Media Manager Mac installer with your guinea-pig testers.

## Quick Setup

### For Your Tester (Non-Technical)

These instructions are for someone testing the Mac installer without direct server access.

#### Option A: Pre-Installed (Easiest for Remote Testers)

**What you (Bob) do:**
1. Build the Mac .app on your Mac (see BUILD_MAC_APP.md)
2. Create a DMG file for easy distribution (optional but recommended)
3. Upload to your server or share via link
4. Share with tester via email or download link
5. Give them the WordPress folder path instructions

**What the tester does:**
1. Download `fmm-easy-setup.dmg` or `fmm-easy-setup.app`
2. If DMG: Double-click to mount, drag app to Applications
3. If APP: Double-click to run (may see security warning - click "Open")
4. When asked for WordPress folder:
   - If local network: `/Volumes/[ShareName]/wordpress` or `smb://bob490.co.uk/wordpress`
   - If remote only: You may need to skip this and pre-install (see below)
5. Enter WordPress URL: `https://bob490.co.uk`
6. Continue through wizard
7. Go to WordPress admin and activate plugin

**Your part - Set Up SMB Share on Fedora Server:**

```bash
# SSH into your Fedora server
ssh your_user@bob490.co.uk

# Install Samba if not already installed
sudo dnf install samba samba-client samba-common

# Configure Samba
sudo nano /etc/samba/smb.conf

# Ensure [global] section has:
[global]
   workgroup = WORKGROUP
   server string = Bob Home Server
   security = user

# Add at end of file:
[wordpress]
   path = /path/to/wordpress
   public = yes
   writable = yes
   browsable = yes
   force user = your_user
```

Then:
```bash
# Restart Samba
sudo systemctl restart smb

# Set permissions
sudo chmod 755 /path/to/wordpress
```

Now tester can connect to: `smb://bob490.co.uk/wordpress` from Mac

---

#### Option B: Local Network Testing (Fastest)

If your tester is on your local home network:

**What you do:**
1. Build the Mac .app
2. Share the plugin files folder on your network
3. Give tester:
   - Local server IP address (e.g., `192.168.1.100`)
   - Folder paths to use

**What the tester does:**
1. On their Mac, open Finder
2. Press `Cmd + K` (Connect to Server)
3. Enter: `smb://192.168.1.100/wordpress`
4. Click "Connect"
5. Run the installer from Applications
6. When asked for WordPress path: `/Volumes/wordpress` (mounted share)
7. Enter WordPress URL: `https://bob490.co.uk`

---

#### Option C: Copy Plugin Files Locally (Simplest)

**What you do:**
1. Build the Mac .app on your Mac
2. Copy all plugin files to same folder as the app
3. Create ZIP file: `fmm-easy-setup.zip`
4. Share via email or download link

**What the tester does:**
1. Download and extract the ZIP
2. Move `fmm-easy-setup.app` to Applications folder
3. Run it from Applications
4. When asked for WordPress path:
   - If on network: Use SMB path: `smb://bob490.co.uk/wordpress`
   - If not networked: Skip folder selection for this test

---

## Testing Checklist

Use this checklist for each Mac tester:

### Before Testing
- [ ] Tester has macOS 10.13 or later
- [ ] Tester has internet access
- [ ] Tester has a Google account
- [ ] You've provided clear instructions on WordPress access

### Installation Phase
- [ ] App launches without security warnings (or warning is expected)
- [ ] Welcome screen displays clearly
- [ ] Folder selection works correctly
- [ ] WordPress URL field accepts input
- [ ] Mobile app option is presented clearly
- [ ] Google instructions screen appears

### Post-Installation
- [ ] Configuration file created at: `~/.fmm-setup/config.json`
- [ ] Tester can open WordPress admin at `https://bob490.co.uk/wp-admin`
- [ ] Plugin appears in Plugins list
- [ ] Plugin can be activated without errors
- [ ] Google Drive settings accessible

### Mac-Specific Tests
- [ ] App opens from Applications folder (not Downloads)
- [ ] Window sizes appropriately on their display
- [ ] File browser dialog works correctly
- [ ] Text entry fields respond to input
- [ ] Buttons are clickable and responsive

---

## Troubleshooting During Testing

**"App is damaged" or "cannot be opened":**
- Normal for unsigned apps
- Tell tester to:
  1. Right-click the app
  2. Select "Open"
  3. Click "Open" in dialog
- Or they can run in Terminal:
  ```bash
  /Applications/fmm-easy-setup.app/Contents/MacOS/fmm-easy-setup
  ```

**"Cannot verify the developer" warning:**
- Expected for apps not from App Store
- Click "Open Anyway" to proceed
- This is normal and safe

**Can't see WordPress folder in file browser:**
- If on network: Make sure Samba is running on your server
- If local: Check folder path is correct
- Verify folder contains `wp-config.php`

**"Access denied" errors:**
- Check Samba permissions on server
- Verify `chmod` settings
- Try adding tester's username to Samba users:
  ```bash
  sudo smbpasswd -a username
  ```

**Plugin won't activate:**
- Check WordPress error logs
- Verify file permissions on server
- Ensure plugin folder copied completely
- Check WordPress file ownership

**Google Drive setup doesn't work:**
- Verify redirect URL: `https://bob490.co.uk/wp-admin/admin.php?page=family-media-manager-settings&action=oauth_callback`
- Check that Google Drive API is enabled
- Verify OAuth scopes include `.../auth/drive.file`
- Ensure HTTPS certificate is valid

---

## Feedback Form for Testers

Send this to your Mac testers after testing:

```
Mac Installer Feedback Form
============================

1. Was the installer easy to understand?
   [ ] Very easy  [ ] Easy  [ ] Neutral  [ ] Difficult  [ ] Very difficult

2. Did you see any security warnings?
   [ ] No  [ ] Yes (expected)  [ ] Yes (unexpected)

3. Did you have trouble finding your WordPress folder?
   [ ] No  [ ] A little  [ ] A lot

4. Did the Google Drive setup make sense?
   [ ] Yes  [ ] Mostly  [ ] No

5. Did the plugin work after activation?
   [ ] Yes  [ ] No  [ ] Partially

6. How responsive was the app?
   [ ] Very responsive  [ ] Fine  [ ] Slow  [ ] Crashed

7. Most confusing part:
   _____________________________________________________________________

8. Easiest part:
   _____________________________________________________________________

9. Would you use this on another Mac?
   [ ] Yes  [ ] Maybe  [ ] No

10. Other comments:
    _____________________________________________________________________

Contact: bob490.co.uk
```

---

## Running Multiple Tests

For comprehensive Mac testing:

1. **Test 1:** SMB network path (tests server integration)
2. **Test 2:** DMG mount (tests distribution format)
3. **Test 3:** Direct app (tests security handling)

This covers all common Mac user scenarios.

---

## Collecting Test Results

Create test results folder on your server:

```bash
# On your Fedora server
mkdir -p /home/bob/fmm-testing/mac
chmod 777 /home/bob/fmm-testing/mac

# Add to Samba config:
[testing]
   path = /home/bob/fmm-testing
   public = yes
   writable = yes
   browsable = yes
```

Testers can upload:
- Screenshots of any issues
- Their `~/.fmm-setup/config.json` (credentials sanitized!)
- Feedback forms
- Error messages from Console app

---

## Testing on bob490.co.uk

Your setup is ideal for Mac testing!

- ✅ WordPress at `https://bob490.co.uk` (HTTPS ready)
- ✅ Fedora server (can run Samba)
- ✅ Plugin already installed (test version 1 updates)
- ✅ Plenty of disk space
- ✅ Full control for debugging

Make sure:
1. Samba is properly configured for SMB access
2. Testers have clear instructions for their scenario
3. You collect feedback systematically

---

## Mac-Specific Notes for Testing

**Application Bundle:**
- The .app is a folder (bundle), not a single file
- When copying, always copy the entire `.app` folder
- Dragging to Applications works best

**Quarantine Attribute:**
- macOS may mark downloaded apps as quarantined
- To remove: `xattr -d com.apple.quarantine /Applications/fmm-easy-setup.app`
- Normal behavior, not a security issue

**Code Signing:**
- Current app is self-signed (expected for testing)
- Users may see warnings on first run (normal)
- For distribution, consider signing with your certificate

**System Preferences:**
- Mac may request permission to access folders
- Click "Allow" when prompted
- Normal and necessary for installation

---

## Version Control for Tests

Track your Mac tests:

```bash
# Create test log
cat > /home/bob/fmm-testing/mac/TEST_LOG.txt << EOF
Date: 2026-01-09
Tester: [Name]
Mac Model: [MacBook, iMac, etc.]
macOS Version: [10.13, 11, 12, 13, 14, 15]
Method: [DMG/APP/Network]
Status: [Pass/Fail/Partial]
Issues: [List any problems]
Feedback: [Comments]
EOF
```

---

## Building for Different Mac Types

**Intel vs Apple Silicon:**
- Current build may only work on one architecture
- For universal support, build with: `--target-architecture universal2`
- Test on both if possible:
  - Intel Mac (older, x86_64)
  - Apple Silicon Mac (M1/M2/M3, arm64)

---

## Next Steps After Testing

1. **Collect feedback** from all testers
2. **Document issues** and bugs found
3. **Update installer** based on problems
4. **Update guides** if testers found confusing sections
5. **Build final DMG** for distribution
6. Consider signing and notarizing for App Store

---

## Troubleshooting Reference

| Problem | Solution |
|---------|----------|
| App won't open | Right-click → Open, then click Open again |
| Security warning | Expected for unsigned apps, click Open Anyway |
| Can't find WordPress | Verify SMB share is running on server |
| Plugin won't activate | Check WordPress logs, verify file permissions |
| Google Drive fails | Check redirect URL matches exactly |
| First run is slow | Normal - Python initializing, subsequent runs faster |
| Configuration lost | Config saved to `~/.fmm-setup/config.json` |

---

Good luck with your Mac testing!
