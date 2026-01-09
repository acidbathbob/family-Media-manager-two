# Family Media Manager - Windows Installation Guide

Welcome! This guide will help you install the Family Media Manager plugin on your Windows computer.

## What is This?

Family Media Manager is a WordPress plugin that lets your family share and manage photos together using Google Drive. This installer will set everything up for you automatically.

## System Requirements

- **Windows 10 or later** (Windows 11 recommended)
- **WordPress website** already set up
- **Google account** (for Google Drive connection)
- An internet connection

## What You'll Need Before Starting

1. **Your WordPress folder location** - Where WordPress is installed on your computer
   - Usually: `C:\xampp\htdocs\wordpress` or similar
   - Or: Ask your web host where your WordPress files are

2. **Your WordPress website URL** - The web address of your WordPress site
   - Example: `https://myfamilyphotos.com`

3. **Google account** - You'll need this to connect Google Drive
   - If you don't have one, create a free account at https://accounts.google.com

## Installation Steps

### Step 1: Download the Installer

1. Download `fmm-easy-setup.exe` from your installation package
2. Keep it in a folder with all the plugin files:
   - `family-media-manager.php`
   - `includes/` folder
   - `admin/` folder
   - `public/` folder
   - `pwa/` folder (optional, for mobile app)

### Step 2: Run the Installer

1. **Double-click** `fmm-easy-setup.exe`
2. Windows may show a warning - this is normal. Click "Run anyway" or "More info" → "Run anyway"
3. A setup window will open

### Step 3: Select Your WordPress Folder

1. Click "Browse Folder..." button
2. Navigate to where WordPress is installed on your computer
3. Look for a folder that contains `wp-config.php` file
4. Select it and click "OK"

**Can't find it?** 
- If you're using XAMPP: `C:\xampp\htdocs\wordpress`
- If you're using a web host: Contact your host and ask for your WordPress directory path
- If you're using WAMP: `C:\wamp\www\wordpress`

### Step 4: Enter Your WordPress URL

1. Type your WordPress website address
2. Examples:
   - `https://myfamilyphotos.com`
   - `https://mysite.com/wordpress`
   - `http://localhost/wordpress` (if testing locally)

**Important:** Include `http://` or `https://` at the beginning!

### Step 5: Install Mobile App (Optional)

The installer will ask if you want to install the mobile app.

- **Yes** - Family members can use phones to upload photos
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

### The installer won't start

**"Windows protected your PC" warning:**
- Click "More info"
- Click "Run anyway"
- This is normal for programs not distributed widely

**"Python error" or similar:**
- Make sure all plugin files are in the same folder as the .exe
- Check that files weren't corrupted during download
- Try right-clicking the .exe and selecting "Run as administrator"

### Can't find WordPress folder

1. Check your XAMPP/WAMP installation folder
2. Look in `C:\xampp\htdocs` or `C:\wamp\www`
3. Contact your web host - they'll tell you where WordPress is located

### "Access denied" errors during installation

1. Make sure you have permission to modify the WordPress folder
2. If WordPress is on a web server (not local), you may need FTP/SFTP access
3. Contact your web host for help

### Google Drive connection fails

1. Make sure you used the exact redirect URL provided by the installer
2. Check that you selected "External" user type in OAuth consent screen
3. Make sure you enabled the Google Drive API
4. Check that `.../auth/drive.file` scope is enabled

## Getting Help

If you get stuck:

1. **Check the detailed guide** - Open `BUILD_WINDOWS_EXE.md` for technical details
2. **Read plugin documentation** - Check `README.md` in the plugin folder
3. **Contact support** - Reach out to the plugin developer

## Important Notes

- **Keep your Google credentials safe** - They're saved on your computer in `C:\Users\YourName\.fmm-setup\config.json`
- **Backup first** - Before installing, make a backup of your WordPress installation
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
1. Delete the folder where you installed it
2. Or ask your web host to remove it

## What's Next?

Once everything is set up:
1. Add family members in WordPress
2. They can start uploading photos
3. Everyone can view and manage the family photo gallery
4. Access from phones (if mobile app is installed) or computers (WordPress)

## Questions?

If something isn't working or you have questions:
1. Make sure all steps were completed
2. Check that your WordPress is working properly
3. Verify your Google credentials are correct
4. Try running the installer again if something failed

Good luck! Your family photo sharing system should now be ready to use.
