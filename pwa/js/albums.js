/**
 * Albums Module
 * Handles album creation, viewing, and management
 */

const Albums = {
    albums: [],
    currentAlbum: null,
    
    /**
     * Initialize albums module
     */
    init() {
        this.setupEventListeners();
    },
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // View albums button (to be added to menu)
        document.getElementById('albums-btn')?.addEventListener('click', () => {
            this.showAlbums();
        });
        
        // Create album button
        document.getElementById('create-album-btn')?.addEventListener('click', () => {
            this.showCreateAlbumDialog();
        });
    },
    
    /**
     * Load albums from API
     */
    async loadAlbums() {
        try {
            const response = await fetch(
                `${App.API_BASE}/albums`,
                {
                    headers: {
                        ...Auth.getAuthHeader()
                    }
                }
            );
            
            if (!response.ok) {
                throw new Error('Failed to load albums');
            }
            
            const data = await response.json();
            this.albums = data;
            
            return data;
            
        } catch (error) {
            console.error('[Albums] Error loading:', error);
            App.showToast('Failed to load albums', 'error');
            return [];
        }
    },
    
    /**
     * Show albums screen
     */
    async showAlbums() {
        App.showToast('Albums feature available! Loading...', 'info');
        await this.loadAlbums();
        
        // For now, show in console
        // Full UI implementation would go here
        console.log('[Albums] Loaded albums:', this.albums);
    },
    
    /**
     * Create new album
     */
    async createAlbum(name, description = '') {
        try {
            const response = await fetch(
                `${App.API_BASE}/albums`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        ...Auth.getAuthHeader()
                    },
                    body: JSON.stringify({
                        name: name,
                        description: description
                    })
                }
            );
            
            if (!response.ok) {
                throw new Error('Failed to create album');
            }
            
            const data = await response.json();
            
            App.showToast('Album created!');
            await this.loadAlbums();
            
            return data.album_id;
            
        } catch (error) {
            console.error('[Albums] Error creating:', error);
            App.showToast('Failed to create album', 'error');
            return false;
        }
    },
    
    /**
     * View album photos
     */
    async viewAlbum(albumId) {
        try {
            const response = await fetch(
                `${App.API_BASE}/albums/${albumId}`,
                {
                    headers: {
                        ...Auth.getAuthHeader()
                    }
                }
            );
            
            if (!response.ok) {
                throw new Error('Failed to load album');
            }
            
            const album = await response.json();
            this.currentAlbum = album;
            
            // Update gallery with album photos
            Gallery.photos = album.photos;
            Gallery.renderGallery();
            
            App.showScreen('gallery');
            App.showToast(`Viewing album: ${album.name}`);
            
        } catch (error) {
            console.error('[Albums] Error viewing:', error);
            App.showToast('Failed to load album', 'error');
        }
    },
    
    /**
     * Show create album dialog (placeholder)
     */
    showCreateAlbumDialog() {
        const name = prompt('Enter album name:');
        if (name) {
            const description = prompt('Enter description (optional):');
            this.createAlbum(name, description || '');
        }
    }
};

// Initialize when loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => Albums.init());
} else {
    Albums.init();
}

// Export
window.Albums = Albums;
