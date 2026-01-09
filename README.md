# Family Media Manager

A family-friendly WordPress plugin for sharing photos and videos using cloud storage (Google Drive) as a backend.

## Overview

Family Media Manager creates a beautiful, simple gallery for sharing media with family members. Instead of storing large files on your WordPress server, it integrates with Google Drive to store full-resolution photos and videos, while keeping only small thumbnails locally.

### Key Features

- ✅ **Full Quality Media** - No compression, unlike WhatsApp
- ✅ **Cloud Storage** - Uses Google Drive (free 15GB, expandable)
- ✅ **Always Available** - No need to keep devices online
- ✅ **Organized Gallery** - Find photos by date/person easily
- ✅ **Private** - Only family members you invite can access
- ✅ **Mobile-First** - Optimized for older users with large buttons and simple UI
- ✅ **REST API** - Ready for Progressive Web App integration

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- Google Cloud Platform account (free)
- Google Drive API credentials

## Installation

### 🎯 Easy Setup (Graphical Installer - Recommended for Non-Technical Users)

**Perfect for grandparents and non-technical users!**

Use the graphical setup wizard with clear instructions and input forms:

**Step 1: Install zenity (required for GUI dialogs)**
```bash
# Fedora/RHEL/CentOS
sudo dnf install zenity

# Ubuntu/Debian
sudo apt install zenity
```

**Step 2: Run the Easy Setup wizard**
```bash
cd family-media-manager
./fmm-easy-setup
```

**Features:**
- ✅ Visual step-by-step wizard with dialog boxes
- ✅ Clear instructions for Google Cloud Console setup
- ✅ Input forms for Google credentials (no command line editing)
- ✅ Progress bars and confirmation dialogs
- ✅ Saves configuration for future reference
- ✅ Opens WordPress admin when finished

### Quick Install (Command Line)

Use the automated installation script:

```bash
cd family-media-manager
sudo ./install.sh
```

The script will:
- Install WordPress plugin
- Deploy PWA (optional)
- Configure permissions
- Guide you through Google Drive setup

### Manual Installation

**Option A: Upload to WordPress**
1. Download or clone this repository
2. Zip the `family-media-manager` folder
3. Go to WordPress Admin → Plugins → Add New → Upload Plugin
4. Upload the zip file and activate

**Option B: Manual Installation**
1. Copy the `family-media-manager` folder to `/wp-content/plugins/`
2. Go to WordPress Admin → Plugins
3. Activate "Family Media Manager"

### 2. Set Up Google Drive API

This section walks you through creating a Google Cloud project and obtaining the credentials needed for the plugin to access Google Drive.

#### Step 1: Access Google Cloud Console

1. Open your web browser and go to [Google Cloud Console](https://console.cloud.google.com)
2. Sign in with your Google account (use the same account you want to use for storing family photos)

#### Step 2: Create a New Project

1. At the top of the page, click the **project dropdown** (next to "Google Cloud")
2. In the popup, click **"NEW PROJECT"** (top right)
3. Enter a project name (e.g., "Family Media Manager")
4. Leave the organization field as default (or select your organization if applicable)
5. Click **"CREATE"**
6. Wait a few seconds for the project to be created
7. Click the **project dropdown** again and select your newly created project

#### Step 3: Enable Google Drive API

1. In the left sidebar, click **"APIs & Services"** (or use the hamburger menu ☰ if sidebar is collapsed)
2. Click **"Library"** (or **"Enabled APIs & services"** → **"+ ENABLE APIS AND SERVICES"**)
3. In the search bar, type: **"Google Drive API"**
4. Click on **"Google Drive API"** from the results
5. Click the blue **"ENABLE"** button
6. Wait for the API to be enabled (you'll see a dashboard page)

#### Step 4: Configure OAuth Consent Screen

**Important**: You must configure this before creating credentials.

1. In the left sidebar, go to **"APIs & Services"** → **"OAuth consent screen"**
2. Select **"External"** user type (unless you have a Google Workspace account, then choose "Internal")
3. Click **"CREATE"**

**App Information:**
- **App name**: `Family Media Manager` (or your preferred name)
- **User support email**: Your email address (select from dropdown)
- **App logo**: (Optional) Upload a logo if you have one
- **Application home page**: Your WordPress site URL (e.g., `https://yoursite.com`)
- **Authorized domains**: Your domain (e.g., `yoursite.com`) - click **"+ ADD DOMAIN"**
- **Developer contact information**: Your email address

4. Click **"SAVE AND CONTINUE"**

**Scopes:**
5. Click **"ADD OR REMOVE SCOPES"**
6. Scroll down or search for:
   - `https://www.googleapis.com/auth/drive.file` (View and manage Google Drive files)
7. Check the box next to this scope
8. Click **"UPDATE"** at the bottom
9. Click **"SAVE AND CONTINUE"**

**Test Users** (if using External mode):
10. Click **"+ ADD USERS"**
11. Add email addresses of family members who will use the app
12. Click **"ADD"**
13. Click **"SAVE AND CONTINUE"**

**Summary:**
14. Review the summary page
15. Click **"BACK TO DASHBOARD"**

#### Step 5: Create OAuth 2.0 Credentials

1. In the left sidebar, go to **"APIs & Services"** → **"Credentials"**
2. At the top, click **"+ CREATE CREDENTIALS"**
3. Select **"OAuth client ID"** from the dropdown

**Configure OAuth Client:**
4. **Application type**: Select **"Web application"**
5. **Name**: Enter a name (e.g., "Family Media Manager Web Client")

**Authorized JavaScript origins** (optional but recommended):
6. Click **"+ ADD URI"**
7. Enter your WordPress site URL: `https://yoursite.com` (replace with your actual domain)
   - **Note**: Use `https://` (not `http://`) for production
   - For local testing, you can use: `http://localhost`

**Authorized redirect URIs** (required):
8. Click **"+ ADD URI"** under "Authorized redirect URIs"
9. Enter: `https://yoursite.com/wp-admin/admin.php?page=family-media-manager-settings&action=oauth_callback`
   - **Important**: Replace `yoursite.com` with your actual WordPress domain
   - Keep the `/wp-admin/admin.php?page=family-media-manager-settings&action=oauth_callback` part exactly as shown
   - Example for localhost: `http://localhost/wordpress/wp-admin/admin.php?page=family-media-manager-settings&action=oauth_callback`

10. Click **"CREATE"**

#### Step 6: Copy Your Credentials

A popup will appear with your credentials:

1. **Client ID**: A long string like `123456789-abc123def456.apps.googleusercontent.com`
   - Click the **copy icon** or select and copy the entire string
   - Save this somewhere safe (e.g., a text file)

2. **Client Secret**: A shorter string like `GOCSPX-abc123def456`
   - Click the **copy icon** or select and copy the entire string
   - Save this somewhere safe

3. Click **"OK"** to close the popup

**Note**: You can always retrieve these credentials later:
- Go to **"APIs & Services"** → **"Credentials"**
- Find your OAuth 2.0 Client ID in the list
- Click the **pencil icon** (edit) to view the Client ID and Secret

### 3. Configure the Plugin in WordPress

Now that you have your Google credentials, configure them in WordPress:

#### Step 1: Access Plugin Settings

1. Log in to your **WordPress Admin Dashboard**
2. In the left sidebar, find and click **"Family Gallery"**
3. Click **"Settings"** (submenu under Family Gallery)

#### Step 2: Enter Google Credentials

1. Find the **"Google Drive Settings"** section
2. **Google Client ID**: Paste the Client ID you copied earlier
3. **Google Client Secret**: Paste the Client Secret you copied earlier
4. Click **"Save Changes"** button at the bottom

#### Step 3: Connect Google Drive

1. After saving, you'll see a **"Connect Google Drive"** button
2. Click **"Connect Google Drive"**
3. You'll be redirected to Google's authorization page

#### Step 4: Authorize the Application (Google Authorization Flow)

1. **Choose an account**: Select the Google account you want to use for storing photos
2. **Review permissions**: Google will show what access the app is requesting
   - "See, edit, create, and delete only the specific Google Drive files you use with this app"
3. **Warning (if in testing mode)**: If you see "Google hasn't verified this app", this is normal for personal projects:
   - Click **"Advanced"** (bottom left)
   - Click **"Go to Family Media Manager (unsafe)"** - Don't worry, this is YOUR app
4. Click **"Continue"** or **"Allow"** to grant permission

#### Step 5: Confirmation

1. You'll be redirected back to your WordPress settings page
2. You should see a success message: **"Google Drive connected successfully"**
3. The settings page will now show:
   - ✅ **Connected Google Account**: Your email address
   - **Disconnect** button (if you need to reconnect later)

### 4. Verify the Connection

1. Go to **Family Gallery** → **Dashboard**
2. Check the **"Cloud Storage Status"** widget
3. It should show: **"Connected to Google Drive"** with your account email
4. Try uploading a test photo to verify everything works

## Usage

### For Administrators

1. **Connect Cloud Storage**: Go to Settings and connect your Google Drive
2. **Add Family Members**: Go to Family Members to add new users
3. **Manage Gallery**: View statistics and uploaded media from Dashboard

### For Family Members

1. Log in to WordPress
2. Connect their Google Drive account (one-time setup)
3. Upload photos/videos through the WordPress interface
4. View shared family photos in the gallery

### REST API Endpoints

The plugin provides REST API endpoints for PWA integration:

- `POST /wp-json/family-gallery/v1/upload` - Upload media
- `GET /wp-json/family-gallery/v1/gallery` - Get gallery photos
- `GET /wp-json/family-gallery/v1/media/{id}` - Get single media item
- `GET /wp-json/family-gallery/v1/media/{id}/download` - Get download URL
- `DELETE /wp-json/family-gallery/v1/media/{id}` - Delete media

## Database Schema

The plugin creates four custom tables:

- `wp_family_media` - Media items (photos/videos)
- `wp_family_sharing` - Sharing permissions
- `wp_family_cloud_tokens` - Cloud storage OAuth tokens
- `wp_family_albums` - Photo albums

## Architecture

```
WordPress Plugin (Catalog)
    ↓
Google Drive (Storage)
    ↓
Family Members (Access)
```

- **WordPress**: Stores thumbnails, metadata, permissions
- **Google Drive**: Stores full-resolution media files
- **Users**: Each has their own Google Drive for their uploads

## Storage Considerations

- **WordPress Server**: Only thumbnails (~50KB each)
  - 1,000 photos ≈ 50MB
- **Google Drive**: Full-resolution files
  - Free tier: 15GB
  - Paid tier: 100GB for £1.59/month

## Development Roadmap

See `DECENTRALIZED-MEDIA-CONCEPT.md` in the Downloads folder for full planning document.

### Phase 1: Core Plugin (Current)
- ✅ Database schema
- ✅ Cloud storage integration
- ✅ Upload handler
- ✅ REST API endpoints
- ✅ Admin interface

### Phase 2: PWA Interface (Next)
- [ ] Mobile-optimized gallery
- [ ] Camera access
- [ ] Offline support
- [ ] Push notifications

### Phase 3: Enhanced Features
- [ ] Albums
- [ ] Facial recognition
- [ ] Video thumbnails
- [ ] Multiple cloud providers

## Contributing

This is a personal/family project, but suggestions and contributions are welcome!

## License

GPL v2 or later

## Support

For issues or questions, please open an issue on GitHub.

## Credits

Developed for family photo sharing with older, non-technical users in mind.

---

**Note**: This plugin is currently in development (v0.1.0). Use in production at your own risk.
