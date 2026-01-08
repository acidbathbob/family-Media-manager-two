/**
 * Gallery Module
 * Handles photo gallery display, loading, and viewing
 */

const Gallery = {
    photos: [],
    currentPage: 1,
    photosPerPage: 20,
    hasMore: false,
    isLoading: false,
    currentPhotoIndex: 0,
    
    /**
     * Initialize gallery
     */
    init() {
        this.setupEventListeners();
    },
    
    /**
     * Setup gallery event listeners
     */
    setupEventListeners() {
        // Close viewer
        document.getElementById('close-viewer-btn')?.addEventListener('click', () => {
            App.showScreen('gallery');
        });
        
        // Previous photo
        document.getElementById('prev-photo-btn')?.addEventListener('click', () => {
            this.showPreviousPhoto();
        });
        
        // Next photo
        document.getElementById('next-photo-btn')?.addEventListener('click', () => {
            this.showNextPhoto();
        });
        
        // Swipe gestures for photo viewer
        this.setupSwipeGestures();
    },
    
    /**
     * Load photos from API
     */
    async loadPhotos(page = 1) {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.currentPage = page;
        
        try {
            const response = await fetch(
                `${App.API_BASE}/gallery?page=${page}&per_page=${this.photosPerPage}`,
                {
                    headers: {
                        ...Auth.getAuthHeader()
                    }
                }
            );
            
            if (!response.ok) {
                throw new Error('Failed to load photos');
            }
            
            const data = await response.json();
            
            // Store photos
            if (page === 1) {
                this.photos = data.photos || [];
            } else {
                this.photos = [...this.photos, ...(data.photos || [])];
            }
            
            this.hasMore = data.pages > page;
            
            // Update UI
            this.renderGallery();
            
            console.log('[Gallery] Loaded', data.photos?.length || 0, 'photos');
            
        } catch (error) {
            console.error('[Gallery] Error loading photos:', error);
            App.showToast('Failed to load photos', 'error');
        } finally {
            this.isLoading = false;
        }
    },
    
    /**
     * Render gallery grid
     */
    renderGallery() {
        const grid = document.getElementById('gallery-grid');
        const emptyState = document.getElementById('empty-state');
        const loadMoreSection = document.getElementById('load-more-section');
        
        if (this.photos.length === 0) {
            grid.innerHTML = '';
            emptyState.style.display = 'block';
            loadMoreSection.style.display = 'none';
            return;
        }
        
        emptyState.style.display = 'none';
        
        // Clear grid if first page
        if (this.currentPage === 1) {
            grid.innerHTML = '';
        }
        
        // Render photos
        this.photos.forEach((photo, index) => {
            // Skip if already rendered
            if (grid.querySelector(`[data-photo-id="${photo.id}"]`)) {
                return;
            }
            
            const item = this.createGalleryItem(photo, index);
            grid.appendChild(item);
        });
        
        // Show/hide load more button
        loadMoreSection.style.display = this.hasMore ? 'block' : 'none';
    },
    
    /**
     * Create gallery item element
     */
    createGalleryItem(photo, index) {
        const item = document.createElement('div');
        item.className = 'gallery-item';
        item.dataset.photoId = photo.id;
        
        const img = document.createElement('img');
        img.src = photo.thumbnail_url;
        img.alt = photo.caption || 'Family photo';
        img.loading = 'lazy'; // Native lazy loading
        
        // Add click handler to view full photo
        item.addEventListener('click', () => {
            this.viewPhoto(index);
        });
        
        item.appendChild(img);
        
        return item;
    },
    
    /**
     * View photo in fullscreen viewer
     */
    async viewPhoto(index) {
        this.currentPhotoIndex = index;
        const photo = this.photos[index];
        
        if (!photo) return;
        
        // Show viewer screen
        App.showScreen('viewer');
        
        // Load full-resolution image
        try {
            const response = await fetch(
                `${App.API_BASE}/media/${photo.id}/download`,
                {
                    headers: {
                        ...Auth.getAuthHeader()
                    }
                }
            );
            
            if (!response.ok) {
                throw new Error('Failed to load photo');
            }
            
            const data = await response.json();
            
            // Update viewer
            const viewerImg = document.getElementById('viewer-image');
            const viewerCaption = document.getElementById('viewer-caption');
            const viewerDate = document.getElementById('viewer-date');
            
            viewerImg.src = data.download_url;
            viewerCaption.textContent = photo.caption || '';
            viewerDate.textContent = this.formatDate(photo.upload_date);
            
        } catch (error) {
            console.error('[Gallery] Error loading full photo:', error);
            App.showToast('Failed to load full photo', 'error');
        }
    },
    
    /**
     * Show previous photo
     */
    showPreviousPhoto() {
        if (this.currentPhotoIndex > 0) {
            this.viewPhoto(this.currentPhotoIndex - 1);
        }
    },
    
    /**
     * Show next photo
     */
    showNextPhoto() {
        if (this.currentPhotoIndex < this.photos.length - 1) {
            this.viewPhoto(this.currentPhotoIndex + 1);
        }
    },
    
    /**
     * Load more photos (pagination)
     */
    loadMorePhotos() {
        this.loadPhotos(this.currentPage + 1);
    },
    
    /**
     * Refresh gallery (reload from start)
     */
    refreshGallery() {
        this.photos = [];
        this.currentPage = 1;
        this.loadPhotos(1);
        App.showToast('Gallery refreshed');
    },
    
    /**
     * Setup swipe gestures for photo viewer
     */
    setupSwipeGestures() {
        const viewerScreen = document.getElementById('viewer-screen');
        let touchStartX = 0;
        let touchEndX = 0;
        
        viewerScreen.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        
        viewerScreen.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe();
        }, { passive: true });
        
        const handleSwipe = () => {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swiped left - next photo
                    this.showNextPhoto();
                } else {
                    // Swiped right - previous photo
                    this.showPreviousPhoto();
                }
            }
        };
        
        this.handleSwipe = handleSwipe;
    },
    
    /**
     * Format date for display
     */
    formatDate(dateString) {
        if (!dateString) return '';
        
        const date = new Date(dateString);
        const now = new Date();
        const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) {
            return 'Today';
        } else if (diffDays === 1) {
            return 'Yesterday';
        } else if (diffDays < 7) {
            return `${diffDays} days ago`;
        } else {
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    }
};

// Initialize when loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => Gallery.init());
} else {
    Gallery.init();
}

// Export
window.Gallery = Gallery;
