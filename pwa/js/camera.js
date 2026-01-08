/**
 * Camera Module
 * Handles camera access, photo capture, and preview
 */

const Camera = {
    stream: null,
    facingMode: 'environment', // 'user' for front, 'environment' for back
    capturedPhoto: null,
    
    /**
     * Initialize camera module
     */
    init() {
        this.setupEventListeners();
    },
    
    /**
     * Setup camera event listeners
     */
    setupEventListeners() {
        // Cancel camera
        document.getElementById('cancel-camera-btn')?.addEventListener('click', () => {
            this.stop();
            App.showScreen('gallery');
        });
        
        // Capture photo
        document.getElementById('capture-btn')?.addEventListener('click', () => {
            this.capture();
        });
        
        // Switch camera (front/back)
        document.getElementById('switch-camera-btn')?.addEventListener('click', () => {
            this.switchCamera();
        });
        
        // Retake photo
        document.getElementById('retake-btn')?.addEventListener('click', () => {
            App.showScreen('camera');
            this.start();
        });
        
        // Upload photo
        document.getElementById('upload-btn')?.addEventListener('click', () => {
            if (this.capturedPhoto) {
                Upload.uploadPhoto(this.capturedPhoto);
            }
        });
    },
    
    /**
     * Start camera
     */
    async start() {
        try {
            // Check if camera is supported
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('Camera not supported on this device');
            }
            
            // Request camera permission
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: this.facingMode,
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                },
                audio: false
            });
            
            // Show camera screen
            App.showScreen('camera');
            
            // Attach stream to video element
            const videoElement = document.getElementById('camera-preview');
            videoElement.srcObject = this.stream;
            
            console.log('[Camera] Started successfully');
            
        } catch (error) {
            console.error('[Camera] Error starting camera:', error);
            
            if (error.name === 'NotAllowedError') {
                App.showToast('Camera permission denied. Please allow camera access in settings.', 'error');
            } else if (error.name === 'NotFoundError') {
                App.showToast('No camera found on this device.', 'error');
            } else {
                App.showToast('Could not access camera: ' + error.message, 'error');
            }
            
            App.showScreen('gallery');
        }
    },
    
    /**
     * Stop camera
     */
    stop() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
            
            const videoElement = document.getElementById('camera-preview');
            videoElement.srcObject = null;
            
            console.log('[Camera] Stopped');
        }
    },
    
    /**
     * Capture photo from camera
     */
    capture() {
        const videoElement = document.getElementById('camera-preview');
        
        // Create canvas to capture frame
        const canvas = document.createElement('canvas');
        canvas.width = videoElement.videoWidth;
        canvas.height = videoElement.videoHeight;
        
        // Draw current video frame to canvas
        const ctx = canvas.getContext('2d');
        ctx.drawImage(videoElement, 0, 0);
        
        // Convert canvas to blob
        canvas.toBlob((blob) => {
            if (!blob) {
                App.showToast('Failed to capture photo', 'error');
                return;
            }
            
            // Store captured photo
            this.capturedPhoto = blob;
            
            // Stop camera
            this.stop();
            
            // Show preview
            this.showPreview(blob);
            
            console.log('[Camera] Photo captured');
            
        }, 'image/jpeg', 0.92); // 92% quality
    },
    
    /**
     * Show photo preview
     */
    showPreview(blob) {
        const previewImg = document.getElementById('preview-image');
        const url = URL.createObjectURL(blob);
        
        previewImg.onload = () => {
            URL.revokeObjectURL(url);
        };
        
        previewImg.src = url;
        App.showScreen('preview');
    },
    
    /**
     * Switch between front and back camera
     */
    async switchCamera() {
        // Toggle facing mode
        this.facingMode = this.facingMode === 'environment' ? 'user' : 'environment';
        
        // Stop current stream
        this.stop();
        
        // Start with new facing mode
        await this.start();
    },
    
    /**
     * Alternative: Use file input for devices without camera API
     */
    useFileInput() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.capture = 'environment'; // Hint to use camera
        
        input.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                this.capturedPhoto = file;
                this.showPreview(file);
            }
        };
        
        input.click();
    }
};

// Initialize when loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => Camera.init());
} else {
    Camera.init();
}

// Export
window.Camera = Camera;
