# Testing Windows Installer

Guide for testing the Family Media Manager Windows installer with your guinea-pig testers.

## Quick Setup

### For Your Tester (Non-Technical)

These instructions are for someone testing the Windows installer without direct server access.

#### Option A: Pre-Installed (Easiest for Remote Testers)

**What you (Bob) do:**
1. Build the Windows .exe on your machine (see BUILD_WINDOWS_EXE.md)
2. SSH into your Fedora server
3. Copy the plugin files to a temporary location
4. Share the .exe file with your tester via email/download link
5. Give them the WordPress folder path instructions below

**What the tester does:**
1. Download `fmm-easy-setup.exe`
2. Run it on their Windows machine
3. When asked for WordPress folder: Use the UNC path you provide
   - Example: `\\192.168.1.100\wordpress` or `\\bob490.co.uk\wordpress`
4. Enter WordPress URL: `https://bob490.co.uk`
5. Continue through the wizard
6. Go to WordPress admin and activate the plugin

**Your part - Prepare UNC Share on Fedora Server:**

```bash
# SSH into your Fedora server
ssh your_user@bob490.co.uk

# Install Samba if not already installed
sudo dnf install samba samba-client samba-common

# Create a share for WordPress folder (if not already shared)
sudo nano /etc/samba/smb.conf

# Add this to the [global] section:
[global]
   workgroup = WORKGROUP
   server string = Bob Home Server
   security = user

# Add this at the end of the file:
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

# Set proper permissions
sudo chmod 755 /path/to/wordpress
```

Now your tester can access: `\\bob490.co.uk\wordpress` from Windows

---

#### Option B: Local Network Testing (Fastest)

If your tester is on your local home network:

**What you do:**
1. Build the Windows .exe
2. Share the folder containing the plugin files on your network
3. Give tester: 
   - Windows filename to download from network share
   - Local IP and folder path (e.g., `\\192.168.1.100\plugins`)

**What the tester does:**
1. On their Windows machine, go to File Explorer
2. Click "This PC" → "Map network drive..."
3. Enter: `\\192.168.1.100\plugins` (your server's local IP)
4. Run the installer from the mapped drive
5. When asked for WordPress path: `\\192.168.1.100\wordpress`
6. Enter WordPress URL: `https://bob490.co.uk`

---

### Option C: Copy Plugin Files Locally (Simplest)

**What you do:**
1. Build the Windows .exe on your Windows machine
2. Copy all plugin files to the same folder as the .exe
3. ZIP everything: `fmm-easy-setup.zip`
4. Send to tester via email or download link

**What the tester does:**
1. Extract the ZIP file
2. Copy the extracted folder to their C: drive (or any folder)
3. Run `fmm-easy-setup.exe` from that folder
4. When asked for WordPress folder path:
   - Option 1: Enter UNC path if server is networked: `\\bob490.co.uk\wordpress`
   - Option 2: If they don't have server access, skip this test and move to "Pre-Installed" option

---

## Testing Checklist

Use this checklist for each Windows tester:

### Before Testing
- [ ] Tester has Windows 10 or later
- [ ] Tester has internet access
- [ ] Tester has a Google account
- [ ] You've provided clear instructions on how to access WordPress folder

### Installation Phase
- [ ] Installer runs without errors
- [ ] Folder selection works correctly
- [ ] WordPress URL is entered correctly
- [ ] Mobile app option is presented
- [ ] Google credentials screen appears

### Post-Installation
- [ ] Configuration file created at: `C:\Users\[username]\.fmm-setup\config.json`
- [ ] Tester can open WordPress admin at `https://bob490.co.uk/wp-admin`
- [ ] Plugin appears in Plugins list
- [ ] Plugin can be activated
- [ ] Google Drive settings accessible

### Troubleshooting During Testing

**"Access denied" when selecting folder:**
- Check Samba/network share permissions
- Verify tester's username has access to the share
- Try giving "Everyone" read/write permissions temporarily

**"WordPress not found" error:**
- Verify path contains `wp-config.php`
- Check path format matches their system
- Provide full UNC path or local path clearly

**Plugin won't activate:**
- Check WordPress error logs at: `https://bob490.co.uk/wp-admin/admin-ajax.php`
- Verify file permissions on your server
- Ensure plugin folder copied completely

**Google Drive steps don't work:**
- Verify redirect URL format: `https://bob490.co.uk/wp-admin/admin.php?page=family-media-manager-settings&action=oauth_callback`
- Check that all scopes enabled in Google Cloud Console
- Ensure HTTPS is working on bob490.co.uk

---

## Feedback Form for Testers

Send this to your Windows testers after testing:

```
Windows Installer Feedback Form
================================

1. Was the installer easy to understand?
   [ ] Very easy  [ ] Easy  [ ] Neutral  [ ] Difficult  [ ] Very difficult

2. Did you have trouble finding your WordPress folder?
   [ ] No  [ ] A little  [ ] A lot

3. Did the Google Drive setup instructions make sense?
   [ ] Yes  [ ] Mostly  [ ] No

4. Did the plugin work after activation?
   [ ] Yes  [ ] No  [ ] Partially

5. What was the most confusing part?
   _____________________________________________________________________

6. What was the easiest part?
   _____________________________________________________________________

7. Any other comments:
   _____________________________________________________________________

Contact: bob490.co.uk
```

---

## Running Multiple Tests

If you want several Windows testers:

1. **Test 1:** UNC network path access (tests server integration)
2. **Test 2:** Pre-installed (tests activation flow)
3. **Test 3:** ZIP file local (tests standalone operation)

This covers all common scenarios Windows users will encounter.

---

## Collecting Test Results

Create a shared folder for feedback:

```bash
# On your Fedora server
mkdir -p /home/bob/fmm-testing
chmod 777 /home/bob/fmm-testing

# Add to Samba config:
[testing]
   path = /home/bob/fmm-testing
   public = yes
   writable = yes
   browsable = yes
```

Testers can upload:
- Screenshots of any errors
- Their `config.json` file (sanitize credentials first)
- Feedback forms

---

## Testing on bob490.co.uk

The good news: Your setup is perfect for this!

- ✅ WordPress running and accessible at `https://bob490.co.uk`
- ✅ HTTPS enabled
- ✅ Plugin already version 1 installed (they can test plugin updates)
- ✅ Plenty of disk space for testing multiple installs
- ✅ Fedora server with proper permissions

Just make sure:
1. Samba/network sharing is configured (if testing remote file access)
2. Testers have the correct URL and folder paths
3. You collect their feedback systematically

---

## Version Control for Tests

Keep track of test results:

```bash
# Create test log
cat > /home/bob/fmm-testing/TEST_LOG.txt << EOF
Date: 2026-01-09
Tester: [Name]
Platform: Windows 10/11
Method: [UNC/Local/ZIP]
Status: [Pass/Fail/Partial]
Issues: [List any problems]
Feedback: [General comments]
EOF
```

---

## Next Steps After Testing

1. **Collect feedback** from all testers
2. **Document issues** found
3. **Update installer** if needed
4. **Update guides** based on tester confusion points
5. **Build final versions** for distribution

Good luck with your Windows testing!
