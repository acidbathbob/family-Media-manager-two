# Family Media Manager - Mac Installation Guide

Welcome! This guide will help you install the Family Media Manager plugin on your Mac.

## What is This?

Family Media Manager is a WordPress plugin that lets your family share and manage photos together using Google Drive. This installer will set everything up for you automatically.

## System Requirements

- **macOS 10.13 or later** (macOS 11+ recommended)
- **WordPress website** already set up
- **Google account** (for Google Drive connection)
- An internet connection

## What You'll Need Before Starting

1. **Your WordPress folder location** - Where WordPress files are stored
   - Usually something like: `/Library/WebServer/Documents/wordpress`
   - Or on your web server: `/var/www/html/wordpress`
   - Contact your web host if you're not sure

2. **Your WordPress website URL** - The web address of your WordPress site
   - Example: `https://myfamilyphotos.com`

3. **Google account** - You'll need this to connect Google Drive
   - If you don't have one, create a free account at https://accounts.google.com

## Installation Steps

### Step 1: Download the Installer

1. Download `fmm-easy-setup.app` (or the DMG file if provided)
2. If you downloaded a DMG file:
   - Double-click it to mount
   - Drag the `fmm-easy-setup.app` to your Applications folder
3. Keep the plugin files nearby:
   - `family-media-manager.php`
   - `includes/` folder
   - `admin/` folder
   - `public/` folder
   - `pwa/` folder (optional, for mobile app)

### Step 2: Run the Installer

**Method A: From Finder**
1. Open Finder
2. Go to Applications (or where you downloaded the app)
3. Double-click `fmm-easy-setup.app`
4. Click "Open" if macOS asks to confirm

**Method B: From Download**
1. Open your Downloads folder
2. Double-click `fmm-easy-setup.app`
3. Click "Open" if macOS asks to confirm

### Step 3: Select Your WordPress Folder

1. Click "Browse Folder..." button
2. Navigate to where WordPress is installed
3. Look for a folder containing `wp-config.php` file
4. Select it and click "Open"

**Can't find it?**
- Check your web hosting control panel (cPanel, Plesk, etc.)
- Look in your home folder → public_html or www
- Contact your web hosting support - they can tell you the exact path

### Step 4: Enter Your WordPress URL

1. Type your WordPress website address
2. Examples:
   - `https://myfamilyphotos.com`
   - `https://mysite.com/wordpress`
   - `http://localhost/wordpress` (if testing locally)

**Important:** Include `http://` or `https://` at the beginning!

### Step 5: Install Mobile App (Optional)

The installer will ask if you want to install the mobile app.

- **Yes** - Family members can use their phones to upload photos
- **No** - Just use WordPress on computers

If you choose "Yes", select a web-accessible folder for the app files.

### Step 6: Connect Google Drive

The installer will guide you through getting Google Drive credentials. This takes about 5-10 minutes.

1. The installer will give you step-by-step instructions
2. You'll go to Google Cloud Console (https://console.cloud.google.com)
3. Create a project and get credentials
4. Paste the credentials into the installer

**Don't worry!** The installer explains each step clearly.

## After Installation

Once the installer finishes:

### 1. Activate the Plugin

1. Go to your WordPress admin: `https://yoursite.com/wp-admin`
2. Log in with your WordPress username and password
3. Click "Plugins" in the left menu
4. Find "Family Media Manager"
5. Click "Activate"

### 2. Configure Google Drive

1. In WordPress admin, click "Family Gallery" in the left menu
2. Click "Settings"
3. Enter the Google credentials from the installer
4. Click "Save Changes"
5. Click "Connect Google Drive"
6. Follow the prompts from Google

### 3. Add Family Members

1. In WordPress admin, click "Family Gallery" → "Family Members"
2. Add family members' names and email addresses
3. They can then upload and share photos!

### 4. Set Up Mobile App (If Installed)

1. Ask your web host how to access the folder where you installed the app
2. Usually it's something like: `https://yoursite.com/gallery`
3. Family members can visit this URL on their phones
4. They can "install" it to their home screen

## Troubleshooting

### macOS says the app is damaged or can't be opened

This is normal for apps not distributed through the App Store.

**Solution:**
1. Open System Preferences → Security & Privacy
2. Click "Open Anyway"
3. Or right-click the app and select "Open"

If that doesn't work:
1. Open Terminal
2. Run: `xattr -d com.apple.quarantine /path/to/fmm-easy-setup.app`
3. Replace `/path/to/` with your actual path

### Can't find WordPress folder

1. Open your web host's control panel (cPanel, Plesk, etc.)
2. Look for "File Manager"
3. Find your WordPress installation
4. Note the full path shown in the address bar

Or contact your web hosting support for the exact path.

### App crashes or won't start

1. Make sure all plugin files are in the same location as the app
2. Try moving the app to your Applications folder first
3. Check that you have permission to modify your WordPress folder
4. Try again - sometimes it works on the second attempt

### "Access denied" errors during installation

1. Make sure you selected the correct WordPress folder
2. If WordPress is on a web server, you may need FTP/SFTP access
3. Contact your web host - they can help you get the right permissions
4. You may need to install via FTP instead

### Google Drive connection fails

1. Make sure you used the exact redirect URL from the installer
2. Check that you selected "External" user type in OAuth consent screen
3. Make sure you enabled the Google Drive API
4. Verify that `.../auth/drive.file` scope is enabled
5. Try creating the credentials again if something went wrong

## Getting Help

If you get stuck:

1. **Check the detailed guide** - See `BUILD_MAC_APP.md` for technical details
2. **Read plugin documentation** - Check `README.md` in the plugin folder
3. **Contact support** - Reach out to the plugin developer
4. **Search online** - Many WordPress hosting issues have common solutions

## Important Notes

- **Keep your Google credentials safe** - They're saved in `~/.fmm-setup/config.json`
- **Backup first** - Before installing, make a backup of your WordPress
- **HTTPS recommended** - For security, use HTTPS on your WordPress site
- **Mobile app requires HTTPS** - The mobile app won't work without HTTPS

## What Gets Installed?

The installer copies these files to your WordPress:
- WordPress plugin files to: `wp-content/plugins/family-media-manager/`
- Optionally: Mobile app files to the location you choose

Your database gets updated when you activate the plugin in WordPress.

## Uninstalling

To remove the plugin:
1. Go to WordPress admin → Plugins
2. Find "Family Media Manager"
3. Click "Deactivate"
4. Click "Delete"
5. Choose "Delete files"

To remove the mobile app:
1. Open Finder and navigate to where you installed it
2. Drag the folder to Trash
3. Or contact your web host to remove it

## What's Next?

Once everything is set up:
1. Add family members in WordPress
2. They can start uploading photos
3. Everyone can view and manage the family photo gallery
4. Access from iPhones/iPads (if mobile app is installed) or computers (WordPress)

## Mac-Specific Tips

**Using with Local Development:**
- If testing locally with MAMP or LocalWP, the URL is usually `http://localhost:8888/wordpress`
- Ask the app where your MAMP/LocalWP folder is located
- The app will find it if you select the correct WordPress folder

**File Permissions:**
- macOS may ask for permission to access certain folders
- Click "Allow" when prompted
- This is normal and necessary for the installer to work

**First Run:**
- The first time you run the app, it may take a few seconds to start
- This is normal - Python is initializing
- Subsequent runs will be faster

## Questions?

If something isn't working or you have questions:
1. Make sure all steps were completed
2. Check that your WordPress is working properly
3. Verify your Google credentials are correct
4. Check the detailed guides (BUILD_MAC_APP.md, README.md)
5. Try running the installer again if something failed

Good luck! Your family photo sharing system should now be ready to use.

## Technical Notes

For those interested in how this works:

- The installer is a Python application bundled as a native Mac app
- It uses tkinter (the native Python GUI framework)
- All plugin files are copied to your WordPress installation
- Configuration is saved as JSON for later reference
- The app is code-signed but not notarized (hence the first-run warning)

If you have technical questions, see BUILD_MAC_APP.md for more details.
