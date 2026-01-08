/**
 * Upload Module
 * Handles photo uploads to WordPress REST API
 */

const Upload = {
    /**
     * Upload photo to WordPress
     */
    async uploadPhoto(blob) {
        const progressDiv = document.getElementById('upload-progress');
        const progressFill = document.getElementById('progress-fill');
        const statusText = document.getElementById('upload-status');
        
        try {
            // Show progress
            progressDiv.style.display = 'block';
            progressFill.style.width = '0%';
            statusText.textContent = 'Preparing upload...';
            
            // Create form data
            const formData = new FormData();
            formData.append('photo', blob, 'photo_' + Date.now() + '.jpg');
            
            // Optional: Add caption
            // formData.append('caption', 'Photo from PWA');
            
            // Upload with progress tracking
            await this.uploadWithProgress(formData, (progress) => {
                progressFill.style.width = progress + '%';
                statusText.textContent = `Uploading... ${Math.round(progress)}%`;
            });
            
            // Success
            progressFill.style.width = '100%';
            statusText.textContent = 'Upload complete! ✓';
            
            // Hide progress and return to gallery
            setTimeout(() => {
                progressDiv.style.display = 'none';
                App.showScreen('gallery');
                App.showToast('Photo uploaded successfully!');
                
                // Refresh gallery to show new photo
                Gallery.refreshGallery();
            }, 1500);
            
        } catch (error) {
            console.error('[Upload] Error:', error);
            
            progressDiv.style.display = 'none';
            App.showToast('Upload failed. Please try again.', 'error');
            
            // Stay on preview screen so user can retry
        }
    },
    
    /**
     * Upload with progress tracking using XMLHttpRequest
     */
    uploadWithProgress(formData, onProgress) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            
            // Track upload progress
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const progress = (e.loaded / e.total) * 100;
                    onProgress(progress);
                }
            });
            
            // Handle completion
            xhr.addEventListener('load', () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        resolve(response);
                    } catch (e) {
                        reject(new Error('Invalid response format'));
                    }
                } else {
                    reject(new Error('Upload failed with status: ' + xhr.status));
                }
            });
            
            // Handle errors
            xhr.addEventListener('error', () => {
                reject(new Error('Network error during upload'));
            });
            
            xhr.addEventListener('abort', () => {
                reject(new Error('Upload cancelled'));
            });
            
            // Open connection and send
            xhr.open('POST', App.API_BASE + '/upload');
            
            // Add authentication header
            const authHeaders = Auth.getAuthHeader();
            Object.keys(authHeaders).forEach(key => {
                xhr.setRequestHeader(key, authHeaders[key]);
            });
            
            xhr.send(formData);
        });
    },
    
    /**
     * Alternative: Upload using fetch (no progress tracking)
     */
    async uploadWithFetch(formData) {
        const response = await fetch(App.API_BASE + '/upload', {
            method: 'POST',
            headers: {
                ...Auth.getAuthHeader()
            },
            body: formData
        });
        
        if (!response.ok) {
            throw new Error('Upload failed: ' + response.statusText);
        }
        
        return await response.json();
    },
    
    /**
     * Queue upload for background sync (when offline)
     */
    async queueForSync(blob) {
        // This would use IndexedDB to store photos for upload when back online
        // Requires background sync API support
        
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            try {
                // Store in IndexedDB (implementation needed)
                // await this.storeInIndexedDB(blob);
                
                // Register background sync
                const registration = await navigator.serviceWorker.ready;
                await registration.sync.register('upload-photos');
                
                App.showToast('Photo queued for upload when online', 'info');
                
            } catch (error) {
                console.error('[Upload] Background sync failed:', error);
                throw error;
            }
        } else {
            throw new Error('Background sync not supported');
        }
    }
};

// Export
window.Upload = Upload;
