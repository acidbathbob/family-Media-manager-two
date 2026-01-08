/**
 * Main App Controller
 * Manages PWA initialization, screen navigation, and service worker
 */

// Configuration
const API_BASE = window.location.origin + '/wp-json/family-gallery/v1';

// App State
const AppState = {
    currentScreen: 'loading',
    isAuthenticated: false,
    authToken: null,
    currentPhoto: null,
    photos: [],
    currentPage: 1,
    hasMorePhotos: false
};

// Screen Management
function showScreen(screenId) {
    // Hide all screens
    document.querySelectorAll('.screen').forEach(screen => {
        screen.style.display = 'none';
    });
    
    // Show requested screen
    const screen = document.getElementById(screenId + '-screen');
    if (screen) {
        screen.style.display = 'block';
        AppState.currentScreen = screenId;
    }
    
    // Hide loading screen
    document.getElementById('loading-screen').style.display = 'none';
}

// Toast Notifications
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    
    const container = document.getElementById('toast-container');
    container.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Initialize Service Worker
async function initServiceWorker() {
    if ('serviceWorker' in navigator) {
        try {
            const registration = await navigator.serviceWorker.register('/service-worker.js');
            console.log('[App] Service Worker registered:', registration);
            
            // Listen for updates
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        showToast('New version available! Refresh to update.', 'info');
                    }
                });
            });
        } catch (error) {
            console.error('[App] Service Worker registration failed:', error);
        }
    }
}

// Initialize App
async function initApp() {
    console.log('[App] Initializing...');
    
    // Register service worker
    await initServiceWorker();
    
    // Check authentication
    const isAuth = Auth.checkAuth();
    
    if (isAuth) {
        AppState.isAuthenticated = true;
        AppState.authToken = Auth.getToken();
        showScreen('gallery');
        Gallery.loadPhotos();
    } else {
        showScreen('login');
    }
    
    // Setup event listeners
    setupEventListeners();
}

// Setup Event Listeners
function setupEventListeners() {
    // Menu Button
    document.getElementById('menu-btn')?.addEventListener('click', () => {
        document.getElementById('menu-overlay').style.display = 'flex';
    });
    
    // Close Menu
    document.getElementById('close-menu-btn')?.addEventListener('click', () => {
        document.getElementById('menu-overlay').style.display = 'none';
    });
    
    // Refresh Gallery
    document.getElementById('refresh-btn')?.addEventListener('click', () => {
        document.getElementById('menu-overlay').style.display = 'none';
        Gallery.refreshGallery();
    });
    
    // Logout
    document.getElementById('logout-btn')?.addEventListener('click', () => {
        Auth.logout();
        document.getElementById('menu-overlay').style.display = 'none';
        showScreen('login');
    });
    
    // Settings (placeholder)
    document.getElementById('settings-btn')?.addEventListener('click', () => {
        showToast('Settings coming soon!', 'info');
        document.getElementById('menu-overlay').style.display = 'none';
    });
    
    // Add Photo Button
    document.getElementById('add-photo-btn')?.addEventListener('click', () => {
        Camera.start();
    });
    
    // Load More
    document.getElementById('load-more-btn')?.addEventListener('click', () => {
        Gallery.loadMorePhotos();
    });
    
    // Install PWA prompt
    handleInstallPrompt();
}

// Handle PWA Install Prompt
let deferredPrompt;

function handleInstallPrompt() {
    window.addEventListener('beforeinstallprompt', (e) => {
        console.log('[App] Install prompt available');
        e.preventDefault();
        deferredPrompt = e;
        
        // You can show a custom install button here
        // For now, we'll just log it
    });
    
    window.addEventListener('appinstalled', () => {
        console.log('[App] PWA installed');
        showToast('App installed successfully!');
        deferredPrompt = null;
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}

// Export for use in other modules
window.App = {
    showScreen,
    showToast,
    state: AppState,
    API_BASE
};
