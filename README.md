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

### Quick Install (Recommended)

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

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create a new project (or select existing)
3. Enable Google Drive API:
   - Navigate to "APIs & Services" → "Library"
   - Search for "Google Drive API"
   - Click "Enable"
4. Create OAuth 2.0 Credentials:
   - Go to "APIs & Services" → "Credentials"
   - Click "Create Credentials" → "OAuth client ID"
   - Choose "Web application"
   - Add authorized redirect URI: `https://yoursite.com/wp-admin/admin.php?page=family-media-manager-settings&action=oauth_callback`
   - Copy the Client ID and Client Secret

### 3. Configure the Plugin

1. Go to WordPress Admin → Family Gallery → Settings
2. Paste your Google Client ID and Client Secret
3. Save settings
4. Click "Connect Google Drive"
5. Authorize the application to access your Google Drive

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
