/**
 * Authentication Module
 * Handles user login, session management, and token storage
 */

const Auth = {
    // Storage keys
    TOKEN_KEY: 'family_gallery_token',
    USER_KEY: 'family_gallery_user',
    
    /**
     * Initialize authentication
     */
    init() {
        this.setupLoginForm();
    },
    
    /**
     * Setup login form event listener
     */
    setupLoginForm() {
        const form = document.getElementById('login-form');
        if (!form) return;
        
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            await this.login(username, password);
        });
    },
    
    /**
     * Login user with WordPress credentials
     */
    async login(username, password) {
        const errorDiv = document.getElementById('login-error');
        errorDiv.style.display = 'none';
        
        try {
            // Use WordPress REST API with Application Passwords or JWT
            // For now, we'll use WordPress nonce-based authentication
            const response = await fetch(window.location.origin + '/wp-json/jwt-auth/v1/token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    username: username,
                    password: password
                })
            });
            
            if (!response.ok) {
                throw new Error('Login failed');
            }
            
            const data = await response.json();
            
            // Store token and user info
            this.setToken(data.token);
            this.setUser({
                id: data.user_id,
                name: data.user_display_name,
                email: data.user_email
            });
            
            // Show success and redirect to gallery
            App.showToast('Login successful!');
            App.showScreen('gallery');
            Gallery.loadPhotos();
            
        } catch (error) {
            console.error('[Auth] Login error:', error);
            
            // Try basic auth fallback
            try {
                const fallbackResponse = await fetch(window.location.origin + '/wp-json/wp/v2/users/me', {
                    method: 'GET',
                    headers: {
                        'Authorization': 'Basic ' + btoa(username + ':' + password)
                    }
                });
                
                if (!fallbackResponse.ok) {
                    throw new Error('Authentication failed');
                }
                
                const userData = await fallbackResponse.json();
                
                // Store credentials for basic auth
                this.setToken(btoa(username + ':' + password));
                this.setUser({
                    id: userData.id,
                    name: userData.name,
                    email: userData.email || ''
                });
                
                App.showToast('Login successful!');
                App.showScreen('gallery');
                Gallery.loadPhotos();
                
            } catch (fallbackError) {
                console.error('[Auth] Fallback login error:', fallbackError);
                errorDiv.textContent = 'Invalid username or password. Please try again.';
                errorDiv.style.display = 'block';
            }
        }
    },
    
    /**
     * Logout user
     */
    logout() {
        localStorage.removeItem(this.TOKEN_KEY);
        localStorage.removeItem(this.USER_KEY);
        App.showToast('Logged out successfully');
    },
    
    /**
     * Check if user is authenticated
     */
    checkAuth() {
        return this.getToken() !== null;
    },
    
    /**
     * Get stored token
     */
    getToken() {
        return localStorage.getItem(this.TOKEN_KEY);
    },
    
    /**
     * Set token
     */
    setToken(token) {
        localStorage.setItem(this.TOKEN_KEY, token);
    },
    
    /**
     * Get stored user info
     */
    getUser() {
        const userJson = localStorage.getItem(this.USER_KEY);
        return userJson ? JSON.parse(userJson) : null;
    },
    
    /**
     * Set user info
     */
    setUser(user) {
        localStorage.setItem(this.USER_KEY, JSON.stringify(user));
    },
    
    /**
     * Get authorization header for API requests
     */
    getAuthHeader() {
        const token = this.getToken();
        if (!token) return {};
        
        // Check if it's a JWT token or Basic auth
        if (token.includes(':') || token.length < 100) {
            // Basic auth
            return {
                'Authorization': 'Basic ' + token
            };
        } else {
            // JWT token
            return {
                'Authorization': 'Bearer ' + token
            };
        }
    }
};

// Initialize when loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => Auth.init());
} else {
    Auth.init();
}

// Export
window.Auth = Auth;
