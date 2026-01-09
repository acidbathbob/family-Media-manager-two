#!/usr/bin/env python3
###############################################################################
# Family Media Manager - Easy Setup Wizard (GUI)
# Cross-platform graphical installer for Windows, Linux, and macOS
###############################################################################

import tkinter as tk
from tkinter import filedialog, simpledialog, messagebox, ttk
import os
import sys
import shutil
import re
import json
from pathlib import Path


class FMMSetupWizard:
    def __init__(self, root):
        self.root = root
        self.root.title("Family Media Manager - Easy Setup")
        self.root.geometry("700x600")
        self.root.resizable(False, False)
        
        # Configuration variables
        self.wp_path = ""
        self.pwa_path = ""
        self.wp_url = ""
        self.google_client_id = ""
        self.google_client_secret = ""
        self.install_pwa = False
        self.script_dir = Path(__file__).parent
        
        # Style
        self.configure_style()
        
        # Current step tracking
        self.current_step = 0
        self.total_steps = 7
        
        # Start wizard
        self.show_welcome()
    
    def configure_style(self):
        """Configure visual style for the application"""
        self.root.configure(bg="#f0f0f0")
        self.bg_color = "#f0f0f0"
        self.title_color = "#0066cc"
        self.text_color = "#333333"
    
    def clear_window(self):
        """Clear all widgets from the window"""
        for widget in self.root.winfo_children():
            widget.destroy()
    
    def create_header(self, title):
        """Create a header frame with title and step indicator"""
        header_frame = tk.Frame(self.root, bg=self.title_color, height=80)
        header_frame.pack(fill=tk.X)
        
        title_label = tk.Label(
            header_frame,
            text=title,
            font=("Arial", 18, "bold"),
            bg=self.title_color,
            fg="white"
        )
        title_label.pack(pady=10)
        
        step_label = tk.Label(
            header_frame,
            text=f"Step {self.current_step + 1} of {self.total_steps}",
            font=("Arial", 10),
            bg=self.title_color,
            fg="white"
        )
        step_label.pack()
    
    def create_content_frame(self):
        """Create main content frame"""
        content_frame = tk.Frame(self.root, bg=self.bg_color)
        content_frame.pack(fill=tk.BOTH, expand=True, padx=20, pady=20)
        return content_frame
    
    def create_button_frame(self):
        """Create frame for navigation buttons"""
        button_frame = tk.Frame(self.root, bg=self.bg_color)
        button_frame.pack(fill=tk.X, padx=20, pady=10)
        return button_frame
    
    def show_welcome(self):
        """Welcome screen"""
        self.current_step = 0
        self.clear_window()
        self.create_header("Welcome to Family Media Manager Setup")
        
        content = self.create_content_frame()
        
        welcome_text = tk.Label(
            content,
            text="This wizard will guide you through installing your family photo sharing system.",
            font=("Arial", 12),
            bg=self.bg_color,
            fg=self.text_color,
            wraplength=600,
            justify=tk.LEFT
        )
        welcome_text.pack(pady=10)
        
        what_we_setup = tk.Label(
            content,
            text="What we'll set up:",
            font=("Arial", 11, "bold"),
            bg=self.bg_color,
            fg=self.text_color
        )
        what_we_setup.pack(anchor=tk.W, pady=(20, 5))
        
        features = [
            "• WordPress plugin installation",
            "• Google Drive connection",
            "• Mobile app (optional)"
        ]
        for feature in features:
            tk.Label(
                content,
                text=feature,
                font=("Arial", 10),
                bg=self.bg_color,
                fg=self.text_color
            ).pack(anchor=tk.W)
        
        info_text = tk.Label(
            content,
            text="\nWhat you'll need:\n• WordPress website\n• Google account\n• Google Cloud credentials (we'll show you how)",
            font=("Arial", 10),
            bg=self.bg_color,
            fg=self.text_color,
            justify=tk.LEFT
        )
        info_text.pack(pady=20)
        
        time_text = tk.Label(
            content,
            text="Time needed: 5-10 minutes",
            font=("Arial", 10, "italic"),
            bg=self.bg_color,
            fg="#666666"
        )
        time_text.pack()
        
        # Buttons
        button_frame = self.create_button_frame()
        
        tk.Button(
            button_frame,
            text="Cancel",
            command=self.root.quit,
            width=15
        ).pack(side=tk.LEFT, padx=5)
        
        tk.Button(
            button_frame,
            text="Continue →",
            command=self.show_wordpress_path,
            width=15,
            bg="#0066cc",
            fg="white"
        ).pack(side=tk.RIGHT, padx=5)
    
    def show_wordpress_path(self):
        """Step 1: Select WordPress installation path"""
        self.current_step = 1
        self.clear_window()
        self.create_header("Step 1: Select Your WordPress Folder")
        
        content = self.create_content_frame()
        
        instructions = tk.Label(
            content,
            text="Select the folder where WordPress is installed.\n\nUsually this is:\n• C:\\\\xampp\\\\htdocs\\\\wordpress\n• C:\\\\wordpress\n• /var/www/html/wordpress",
            font=("Arial", 10),
            bg=self.bg_color,
            fg=self.text_color,
            justify=tk.LEFT
        )
        instructions.pack(pady=10)
        
        path_frame = tk.Frame(content, bg=self.bg_color)
        path_frame.pack(fill=tk.X, pady=20)
        
        self.wp_path_var = tk.StringVar(value="Not selected")
        tk.Label(
            path_frame,
            text="Selected path:",
            font=("Arial", 10),
            bg=self.bg_color,
            fg=self.text_color
        ).pack(anchor=tk.W, pady=(0, 5))
        
        path_label = tk.Label(
            path_frame,
            textvariable=self.wp_path_var,
            font=("Arial", 9, "italic"),
            bg="#ffffff",
            fg="#666666",
            relief=tk.SUNKEN,
            padx=10,
            pady=10
        )
        path_label.pack(fill=tk.X)
        
        # Buttons
        button_frame = self.create_button_frame()
        
        def browse_folder():
            folder = filedialog.askdirectory(
                title="Select WordPress Installation Folder",
                initialdir=os.path.expanduser("~")
            )
            if folder:
                if os.path.isfile(os.path.join(folder, "wp-config.php")):
                    self.wp_path = folder
                    self.wp_path_var.set(folder)
                else:
                    messagebox.showerror(
                        "Invalid WordPress Folder",
                        "We couldn't find wp-config.php in this folder.\n\nPlease select the correct WordPress installation folder."
                    )
        
        tk.Button(
            button_frame,
            text="← Back",
            command=self.show_welcome,
            width=15
        ).pack(side=tk.LEFT, padx=5)
        
        tk.Button(
            button_frame,
            text="Browse Folder...",
            command=browse_folder,
            width=15
        ).pack(side=tk.LEFT, padx=5)
        
        tk.Button(
            button_frame,
            text="Continue →",
            command=self.validate_wp_and_continue,
            width=15,
            bg="#0066cc",
            fg="white"
        ).pack(side=tk.RIGHT, padx=5)
    
    def validate_wp_and_continue(self):
        """Validate WordPress path and continue"""
        if not self.wp_path:
            messagebox.showerror("Required", "Please select a WordPress folder")
            return
        
        if not os.path.isfile(os.path.join(self.wp_path, "wp-config.php")):
            messagebox.showerror(
                "Invalid WordPress Installation",
                "wp-config.php not found. Please select the correct folder."
            )
            return
        
        self.show_plugin_installation()
    
    def show_plugin_installation(self):
        """Step 2: Install WordPress plugin"""
        self.current_step = 2
        self.clear_window()
        self.create_header("Installing WordPress Plugin")
        
        content = self.create_content_frame()
        
        progress_label = tk.Label(
            content,
            text="Installing plugin files...",
            font=("Arial", 11),
            bg=self.bg_color,
            fg=self.text_color
        )
        progress_label.pack(pady=20)
        
        progress = ttk.Progressbar(
            content,
            length=400,
            mode='determinate',
            value=0
        )
        progress.pack(pady=10)
        
        self.root.update()
        
        try:
            # Step 1: Create plugin directory
            progress_label.config(text="Creating plugin folder...")
            self.root.update()
            progress['value'] = 10
            self.root.update()
            
            plugin_dir = os.path.join(self.wp_path, "wp-content", "plugins", "family-media-manager")
            if os.path.exists(plugin_dir):
                shutil.rmtree(plugin_dir)
            os.makedirs(plugin_dir, exist_ok=True)
            
            # Step 2: Copy plugin files
            progress_label.config(text="Copying plugin files...")
            self.root.update()
            progress['value'] = 40
            self.root.update()
            
            files_to_copy = [
                "family-media-manager.php",
                "includes",
                "admin",
                "public"
            ]
            
            for file_item in files_to_copy:
                src = os.path.join(self.script_dir, file_item)
                dst = os.path.join(plugin_dir, file_item)
                
                if os.path.exists(src):
                    if os.path.isdir(src):
                        if os.path.exists(dst):
                            shutil.rmtree(dst)
                        shutil.copytree(src, dst)
                    else:
                        shutil.copy2(src, dst)
            
            # Step 3: Set permissions (Windows doesn't need this, but we can try)
            progress_label.config(text="Setting permissions...")
            self.root.update()
            progress['value'] = 70
            self.root.update()
            
            progress_label.config(text="Plugin installed successfully!")
            self.root.update()
            progress['value'] = 100
            self.root.update()
            
            messagebox.showinfo(
                "Plugin Installed",
                "Plugin files have been installed successfully!\n\nNext: You'll need to activate it in WordPress."
            )
            
            self.show_wordpress_url()
        
        except Exception as e:
            messagebox.showerror("Installation Error", f"Error installing plugin:\n\n{str(e)}")
    
    def show_wordpress_url(self):
        """Step 3: Get WordPress URL"""
        self.current_step = 3
        self.clear_window()
        self.create_header("Step 2: Your WordPress Website Address")
        
        content = self.create_content_frame()
        
        instructions = tk.Label(
            content,
            text="Enter your WordPress website address (URL).\n\nExamples:\n• https://myfamilyphotos.com\n• https://www.mysite.com/wordpress\n• http://localhost/wordpress (for testing)\n\nImportant: Include http:// or https://",
            font=("Arial", 10),
            bg=self.bg_color,
            fg=self.text_color,
            justify=tk.LEFT
        )
        instructions.pack(pady=10)
        
        url_frame = tk.Frame(content, bg=self.bg_color)
        url_frame.pack(fill=tk.X, pady=20)
        
        tk.Label(
            url_frame,
            text="WordPress URL:",
            font=("Arial", 10),
            bg=self.bg_color,
            fg=self.text_color
        ).pack(anchor=tk.W, pady=(0, 5))
        
        self.wp_url_entry = tk.Entry(url_frame, font=("Arial", 10), width=50)
        self.wp_url_entry.insert(0, "https://")
        self.wp_url_entry.pack(fill=tk.X)
        
        button_frame = self.create_button_frame()
        
        tk.Button(
            button_frame,
            text="← Back",
            command=self.show_wordpress_path,
            width=15
        ).pack(side=tk.LEFT, padx=5)
        
        tk.Button(
            button_frame,
            text="Continue →",
            command=self.validate_url_and_continue,
            width=15,
            bg="#0066cc",
            fg="white"
        ).pack(side=tk.RIGHT, padx=5)
    
    def validate_url_and_continue(self):
        """Validate URL and continue"""
        url = self.wp_url_entry.get().strip()
        
        if not url or url == "https://":
            messagebox.showerror("Required", "Please enter your WordPress URL")
            return
        
        # Remove trailing slash
        url = url.rstrip("/")
        
        self.wp_url = url
        self.show_pwa_question()
    
    def show_pwa_question(self):
        """Step 4: Ask about PWA installation"""
        self.current_step = 4
        self.clear_window()
        self.create_header("Step 3: Mobile App (Optional)")
        
        content = self.create_content_frame()
        
        question = tk.Label(
            content,
            text="Do you want to install the mobile app?",
            font=("Arial", 12, "bold"),
            bg=self.bg_color,
            fg=self.text_color
        )
        question.pack(pady=10)
        
        description = tk.Label(
            content,
            text="The mobile app allows family members to:\n• Take photos directly from their phone\n• Upload pictures easily\n• View the family gallery\n• Install as a home screen app\n\nRecommended: Yes, unless you only want to use WordPress.",
            font=("Arial", 10),
            bg=self.bg_color,
            fg=self.text_color,
            justify=tk.LEFT
        )
        description.pack(pady=20)
        
        button_frame = self.create_button_frame()
        
        tk.Button(
            button_frame,
            text="← Back",
            command=self.show_wordpress_url,
            width=15
        ).pack(side=tk.LEFT, padx=5)
        
        tk.Button(
            button_frame,
            text="No, Skip PWA",
            command=self.skip_pwa,
            width=15
        ).pack(side=tk.LEFT, padx=5)
        
        tk.Button(
            button_frame,
            text="Yes, Install PWA →",
            command=self.show_pwa_path,
            width=15,
            bg="#0066cc",
            fg="white"
        ).pack(side=tk.RIGHT, padx=5)
    
    def skip_pwa(self):
        """Skip PWA installation and continue to Google setup"""
        self.install_pwa = False
        self.show_google_setup()
    
    def show_pwa_path(self):
        """Step 4b: Select PWA installation path"""
        self.current_step = 4
        self.clear_window()
        self.create_header("Where should we install the mobile app?")
        
        content = self.create_content_frame()
        
        instructions = tk.Label(
            content,
            text="Select a folder for the mobile app.\n\nThis should be a web-accessible folder, like:\n• C:\\\\xampp\\\\htdocs\\\\gallery\n• C:\\\\inetpub\\\\wwwroot\\\\gallery",
            font=("Arial", 10),
            bg=self.bg_color,
            fg=self.text_color,
            justify=tk.LEFT
        )
        instructions.pack(pady=10)
        
        path_frame = tk.Frame(content, bg=self.bg_color)
        path_frame.pack(fill=tk.X, pady=20)
        
        self.pwa_path_var = tk.StringVar(value="Not selected")
        tk.Label(
            path_frame,
            text="Selected path:",
            font=("Arial", 10),
            bg=self.bg_color,
            fg=self.text_color
        ).pack(anchor=tk.W, pady=(0, 5))
        
        path_label = tk.Label(
            path_frame,
            textvariable=self.pwa_path_var,
            font=("Arial", 9, "italic"),
            bg="#ffffff",
            fg="#666666",
            relief=tk.SUNKEN,
            padx=10,
            pady=10
        )
        path_label.pack(fill=tk.X)
        
        button_frame = self.create_button_frame()
        
        def browse_folder():
            folder = filedialog.askdirectory(
                title="Select PWA Installation Folder",
                initialdir=os.path.expanduser("~")
            )
            if folder:
                self.pwa_path = folder
                self.pwa_path_var.set(folder)
        
        tk.Button(
            button_frame,
            text="← Back",
            command=self.show_pwa_question,
            width=15
        ).pack(side=tk.LEFT, padx=5)
        
        tk.Button(
            button_frame,
            text="Browse Folder...",
            command=browse_folder,
            width=15
        ).pack(side=tk.LEFT, padx=5)
        
        tk.Button(
            button_frame,
            text="Continue →",
            command=self.install_pwa_files,
            width=15,
            bg="#0066cc",
            fg="white"
        ).pack(side=tk.RIGHT, padx=5)
    
    def install_pwa_files(self):
        """Install PWA files"""
        if not self.pwa_path:
            messagebox.showerror("Required", "Please select a PWA installation folder")
            return
        
        self.current_step = 4
        self.clear_window()
        self.create_header("Installing Mobile App")
        
        content = self.create_content_frame()
        
        progress_label = tk.Label(
            content,
            text="Installing app files...",
            font=("Arial", 11),
            bg=self.bg_color,
            fg=self.text_color
        )
        progress_label.pack(pady=20)
        
        progress = ttk.Progressbar(
            content,
            length=400,
            mode='determinate',
            value=0
        )
        progress.pack(pady=10)
        
        self.root.update()
        
        try:
            progress_label.config(text="Creating mobile app folder...")
            self.root.update()
            progress['value'] = 10
            self.root.update()
            
            os.makedirs(self.pwa_path, exist_ok=True)
            
            progress_label.config(text="Copying app files...")
            self.root.update()
            progress['value'] = 40
            self.root.update()
            
            pwa_src = os.path.join(self.script_dir, "pwa")
            if os.path.exists(pwa_src):
                for item in os.listdir(pwa_src):
                    src = os.path.join(pwa_src, item)
                    dst = os.path.join(self.pwa_path, item)
                    if os.path.isdir(src):
                        if os.path.exists(dst):
                            shutil.rmtree(dst)
                        shutil.copytree(src, dst)
                    else:
                        shutil.copy2(src, dst)
            
            progress_label.config(text="Configuring API connection...")
            self.root.update()
            progress['value'] = 60
            self.root.update()
            
            # Update app.js with WordPress URL
            app_js_path = os.path.join(self.pwa_path, "js", "app.js")
            if os.path.exists(app_js_path):
                with open(app_js_path, 'r') as f:
                    content = f.read()
                content = content.replace("window.location.origin", f"'{self.wp_url}'")
                with open(app_js_path, 'w') as f:
                    f.write(content)
            
            # Create .htaccess for HTTPS redirect
            progress_label.config(text="Creating .htaccess file...")
            self.root.update()
            progress['value'] = 80
            self.root.update()
            
            htaccess_content = """# Force HTTPS (required for PWA)
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Service Worker
<Files "service-worker.js">
    Header set Service-Worker-Allowed "/"
    Header set Cache-Control "max-age=0, no-cache, no-store, must-revalidate"
</Files>

# Cache static assets
<FilesMatch "\\.(css|js|jpg|jpeg|png|gif|webp)$">
    Header set Cache-Control "max-age=31536000, public"
</FilesMatch>
"""
            
            htaccess_path = os.path.join(self.pwa_path, ".htaccess")
            with open(htaccess_path, 'w') as f:
                f.write(htaccess_content)
            
            progress_label.config(text="Mobile app installed successfully!")
            self.root.update()
            progress['value'] = 100
            self.root.update()
            
            self.install_pwa = True
            
            messagebox.showinfo(
                "PWA Installed",
                f"Mobile app installed to:\n{self.pwa_path}\n\nMake sure HTTPS is enabled (required for PWA)"
            )
            
            self.show_google_setup()
        
        except Exception as e:
            messagebox.showerror("Installation Error", f"Error installing PWA:\n\n{str(e)}")
    
    def show_google_setup(self):
        """Step 5: Google Drive setup instructions"""
        self.current_step = 5
        self.clear_window()
        self.create_header("Step 4: Google Drive Setup")
        
        content = self.create_content_frame()
        
        intro = tk.Label(
            content,
            text="Now let's connect Google Drive",
            font=("Arial", 12, "bold"),
            bg=self.bg_color,
            fg=self.text_color
        )
        intro.pack(pady=10)
        
        instructions = tk.Label(
            content,
            text="We need to get credentials from Google. Don't worry, we'll guide you!\n\n1. We'll show you what to do in Google Cloud Console\n2. You'll create OAuth credentials\n3. Paste them here",
            font=("Arial", 10),
            bg=self.bg_color,
            fg=self.text_color,
            justify=tk.LEFT
        )
        instructions.pack(pady=20)
        
        button_frame = self.create_button_frame()
        
        tk.Button(
            button_frame,
            text="← Back",
            command=self.show_pwa_question if not self.install_pwa else self.show_pwa_path,
            width=15
        ).pack(side=tk.LEFT, padx=5)
        
        tk.Button(
            button_frame,
            text="Show Me How →",
            command=self.show_google_instructions,
            width=15,
            bg="#0066cc",
            fg="white"
        ).pack(side=tk.RIGHT, padx=5)
    
    def show_google_instructions(self):
        """Show detailed Google Cloud setup instructions"""
        instructions_text = """STEP 1: Create a Google Cloud Project
1. Go to: https://console.cloud.google.com
2. Sign in with your Google account
3. Click the project dropdown (top left)
4. Click "NEW PROJECT"
5. Name it: Family Media Manager
6. Click "CREATE"

STEP 2: Enable Google Drive API
1. Click "APIs & Services" in the left menu
2. Click "Library"
3. Search for: "Google Drive API"
4. Click on it
5. Click the blue "ENABLE" button

STEP 3: Configure OAuth Consent Screen
1. Go to "APIs & Services" → "OAuth consent screen"
2. Select "External" user type
3. Click "CREATE"
4. Fill in:
   • App name: Family Media Manager
   • User support email: (your email)
   • Developer contact: (your email)
5. Click "SAVE AND CONTINUE"
6. Click "ADD OR REMOVE SCOPES"
7. Find and check: .../auth/drive.file
8. Click "UPDATE", then "SAVE AND CONTINUE"
9. Click "BACK TO DASHBOARD"

STEP 4: Create OAuth Credentials
1. Go to "APIs & Services" → "Credentials"
2. Click "+ CREATE CREDENTIALS"
3. Select "OAuth client ID"
4. Application type: "Web application"
5. Name: Family Media Manager Web
6. Under "Authorized redirect URIs", click "+ ADD URI"
7. Paste this URL exactly:
   """ + self.wp_url + """/wp-admin/admin.php?page=family-media-manager-settings&action=oauth_callback
8. Click "CREATE"

A popup will show your credentials. Copy them and come back here!
"""
        
        messagebox.showinfo(
            "Google Cloud Setup Instructions",
            instructions_text
        )
        
        self.show_google_credentials()
    
    def show_google_credentials(self):
        """Step 6: Enter Google credentials"""
        self.current_step = 6
        self.clear_window()
        self.create_header("Step 5: Enter Your Google Credentials")
        
        content = self.create_content_frame()
        
        instructions = tk.Label(
            content,
            text="Copy and paste your credentials from Google Cloud Console.\n\nYou should see them in the popup from the previous step.",
            font=("Arial", 10),
            bg=self.bg_color,
            fg=self.text_color,
            justify=tk.LEFT
        )
        instructions.pack(pady=10)
        
        # Client ID
        tk.Label(
            content,
            text="Client ID:",
            font=("Arial", 10),
            bg=self.bg_color,
            fg=self.text_color
        ).pack(anchor=tk.W, pady=(10, 0))
        
        self.client_id_entry = tk.Entry(content, font=("Arial", 10), width=60)
        self.client_id_entry.pack(fill=tk.X, pady=(0, 10))
        
        client_id_hint = tk.Label(
            content,
            text="Looks like: 123456-abc.apps.googleusercontent.com",
            font=("Arial", 8, "italic"),
            bg=self.bg_color,
            fg="#999999"
        )
        client_id_hint.pack(anchor=tk.W)
        
        # Client Secret
        tk.Label(
            content,
            text="Client Secret:",
            font=("Arial", 10),
            bg=self.bg_color,
            fg=self.text_color
        ).pack(anchor=tk.W, pady=(15, 0))
        
        self.client_secret_entry = tk.Entry(content, font=("Arial", 10), width=60, show="•")
        self.client_secret_entry.pack(fill=tk.X, pady=(0, 10))
        
        client_secret_hint = tk.Label(
            content,
            text="Looks like: GOCSPX-abc123...",
            font=("Arial", 8, "italic"),
            bg=self.bg_color,
            fg="#999999"
        )
        client_secret_hint.pack(anchor=tk.W)
        
        button_frame = self.create_button_frame()
        
        tk.Button(
            button_frame,
            text="← Back",
            command=self.show_google_setup,
            width=15
        ).pack(side=tk.LEFT, padx=5)
        
        tk.Button(
            button_frame,
            text="Continue →",
            command=self.validate_credentials_and_save,
            width=15,
            bg="#0066cc",
            fg="white"
        ).pack(side=tk.RIGHT, padx=5)
    
    def validate_credentials_and_save(self):
        """Validate credentials and save configuration"""
        client_id = self.client_id_entry.get().strip()
        client_secret = self.client_secret_entry.get().strip()
        
        if not client_id or not client_secret:
            messagebox.showerror(
                "Required Fields",
                "Both Client ID and Client Secret are required!"
            )
            return
        
        self.google_client_id = client_id
        self.google_client_secret = client_secret
        
        self.save_configuration()
    
    def save_configuration(self):
        """Step 7: Save configuration"""
        self.current_step = 7
        self.clear_window()
        self.create_header("Saving Configuration")
        
        content = self.create_content_frame()
        
        progress_label = tk.Label(
            content,
            text="Saving configuration...",
            font=("Arial", 11),
            bg=self.bg_color,
            fg=self.text_color
        )
        progress_label.pack(pady=20)
        
        progress = ttk.Progressbar(
            content,
            length=400,
            mode='determinate',
            value=0
        )
        progress.pack(pady=10)
        
        self.root.update()
        
        try:
            progress['value'] = 50
            self.root.update()
            
            # Create configuration file
            config_dir = Path.home() / ".fmm-setup"
            config_dir.mkdir(exist_ok=True)
            
            config_file = config_dir / "config.json"
            
            config = {
                "wp_path": self.wp_path,
                "wp_url": self.wp_url,
                "pwa_path": self.pwa_path,
                "install_pwa": self.install_pwa,
                "google_client_id": self.google_client_id,
                "google_client_secret": self.google_client_secret,
                "redirect_uri": f"{self.wp_url}/wp-admin/admin.php?page=family-media-manager-settings&action=oauth_callback"
            }
            
            with open(config_file, 'w') as f:
                json.dump(config, f, indent=2)
            
            progress['value'] = 100
            self.root.update()
            progress_label.config(text="Configuration saved!")
            self.root.update()
            
            self.show_completion()
        
        except Exception as e:
            messagebox.showerror("Error", f"Error saving configuration:\n\n{str(e)}")
    
    def show_completion(self):
        """Show completion screen"""
        self.current_step = 7
        self.clear_window()
        self.create_header("🎉 Setup Complete!")
        
        content = self.create_content_frame()
        
        success_text = tk.Label(
            content,
            text="Congratulations! Your installation is ready.",
            font=("Arial", 12, "bold"),
            bg=self.bg_color,
            fg="#00aa00"
        )
        success_text.pack(pady=10)
        
        what_we_did = tk.Label(
            content,
            text="What we did:",
            font=("Arial", 11, "bold"),
            bg=self.bg_color,
            fg=self.text_color
        )
        what_we_did.pack(anchor=tk.W, pady=(20, 5))
        
        items = ["✓ Installed WordPress plugin"]
        if self.install_pwa:
            items.append("✓ Installed mobile app")
        items.append("✓ Saved Google Drive credentials")
        
        for item in items:
            tk.Label(
                content,
                text=item,
                font=("Arial", 10),
                bg=self.bg_color,
                fg=self.text_color
            ).pack(anchor=tk.W)
        
        next_steps = tk.Label(
            content,
            text="\nNext Steps (Important!):",
            font=("Arial", 11, "bold"),
            bg=self.bg_color,
            fg=self.text_color
        )
        next_steps.pack(anchor=tk.W, pady=(15, 5))
        
        steps_text = f"""1. Activate the Plugin in WordPress:
   Go to: {self.wp_url}/wp-admin
   Click "Plugins" → Find "Family Media Manager" → Click "Activate"

2. Connect Google Drive:
   Go to "Family Gallery" → "Settings"
   Enter your credentials and click "Connect Google Drive"
"""
        
        if self.install_pwa:
            steps_text += "\n3. Access the Mobile App:\n   Set up your web server to serve files from the PWA folder\n   Access it from your phone's browser\n   Install it to your home screen"
        
        steps_label = tk.Label(
            content,
            text=steps_text,
            font=("Arial", 9),
            bg=self.bg_color,
            fg=self.text_color,
            justify=tk.LEFT
        )
        steps_label.pack(anchor=tk.W, pady=10)
        
        button_frame = self.create_button_frame()
        
        tk.Button(
            button_frame,
            text="Finish",
            command=self.root.quit,
            width=30,
            bg="#0066cc",
            fg="white"
        ).pack(side=tk.RIGHT, padx=5)


def main():
    root = tk.Tk()
    app = FMMSetupWizard(root)
    root.mainloop()


if __name__ == "__main__":
    main()
