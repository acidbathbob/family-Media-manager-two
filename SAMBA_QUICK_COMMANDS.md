# Samba Quick Commands for Fedora

## Quick Start (Automated)

Run the automatic setup script:
```bash
sudo bash SAMBA_SETUP.sh
```

---

## Manual Commands (Step by Step)

### 1. Install Samba
```bash
sudo dnf install -y samba samba-client samba-common
```

### 2. Backup Original Config
```bash
sudo cp /etc/samba/smb.conf /etc/samba/smb.conf.backup
```

### 3. Create Testing Directories
```bash
sudo mkdir -p /home/bob/fmm-testing/{windows,mac,feedback/{windows,mac}}
sudo chmod 777 /home/bob/fmm-testing
```

### 4. Find Your WordPress Path
```bash
# Find where WordPress is installed
find / -name "wp-config.php" -type f 2>/dev/null

# Or navigate to it directly
ls /path/to/wordpress/wp-config.php
```

Example output: `/home/bob/wordpress` or `/var/www/html/wordpress`

### 5. Edit Samba Configuration
```bash
sudo nano /etc/samba/smb.conf
```

Replace the entire file with:

```ini
[global]
   workgroup = WORKGROUP
   server string = Bob Home Server
   security = user
   passdb backend = tdbsam
   log file = /var/log/samba/log.%m
   max log size = 50
   dns proxy = no

[wordpress]
   path = /path/to/your/wordpress
   public = yes
   writable = yes
   browsable = yes
   force user = bob
   comment = WordPress Installation
   create mask = 0755
   directory mask = 0755

[testing]
   path = /home/bob/fmm-testing
   public = yes
   writable = yes
   browsable = yes
   comment = FMM Testing Results
   create mask = 0755
   directory mask = 0755
```

**Important:** Replace `/path/to/your/wordpress` with your actual WordPress path

Save and exit: `Ctrl+O`, `Enter`, `Ctrl+X`

### 6. Test Configuration
```bash
sudo testparm
```

Should show no errors. Look for: `Load smb config files from /etc/samba/smb.conf`

### 7. Enable Samba Services
```bash
sudo systemctl enable smb
sudo systemctl enable nmb
```

### 8. Start/Restart Samba
```bash
sudo systemctl restart smb
sudo systemctl restart nmb
```

### 9. Verify Services Running
```bash
sudo systemctl status smb
sudo systemctl status nmb
```

Both should show: `● smb.service - Samba SMB Daemon - ACTIVE (running)`

### 10. Set File Permissions
```bash
sudo chmod 755 /path/to/your/wordpress
sudo chown -R bob:bob /home/bob/fmm-testing
```

### 11. Add Samba User
```bash
sudo smbpasswd -a bob
```

You'll be prompted to set a password (can be same as your system password)

---

## Verification Commands

### Test Samba Access Locally
```bash
smbclient -L localhost -U bob
```

You should see:
```
[wordpress]      - WordPress Installation
[testing]        - FMM Testing Results
```

### Check Share Access
```bash
smbclient //localhost/wordpress -U bob -c "ls"
```

### View Samba Logs
```bash
sudo tail -f /var/log/samba/log.smbd
```

### List Active Samba Connections
```bash
sudo smbstatus
```

---

## Troubleshooting

### Service won't start
```bash
sudo systemctl status smb
sudo journalctl -xe

# Check config syntax
sudo testparm
```

### Can't access shares
```bash
# Verify permissions
ls -la /path/to/your/wordpress
ls -la /home/bob/fmm-testing

# Verify Samba is running
sudo systemctl is-active smb
sudo systemctl is-active nmb

# Check if port 445 is open
sudo firewall-cmd --list-all
```

### "Permission denied" errors
```bash
# Fix WordPress folder permissions
sudo chmod 755 /path/to/your/wordpress

# Fix testing folder permissions
sudo chmod 777 /home/bob/fmm-testing

# Verify Samba user permissions
sudo smbpasswd -L
```

### Firewall blocking access
```bash
# Allow Samba through firewall
sudo firewall-cmd --permanent --add-service=samba
sudo firewall-cmd --permanent --add-service=samba-client
sudo firewall-cmd --reload

# Or disable firewall (for testing only - not recommended for production)
sudo systemctl stop firewalld
```

### Restart Everything
```bash
# Full restart of Samba
sudo systemctl stop smb nmb
sudo systemctl start smb nmb
sudo systemctl status smb nmb
```

---

## Connection Instructions for Testers

### Windows Users
```
Network Path: \\bob490.co.uk\wordpress
or
File Explorer → Map network drive → \\bob490.co.uk\wordpress

Username: bob
Password: [Your Samba password]
```

### Mac Users
```
Finder → Cmd+K → smb://bob490.co.uk/wordpress

Username: bob
Password: [Your Samba password]
```

### Linux Users
```bash
smbclient //bob490.co.uk/wordpress -U bob
```

Or mount it:
```bash
mkdir ~/wordpress-share
sudo mount -t cifs //bob490.co.uk/wordpress ~/wordpress-share -o username=bob
```

---

## Complete Setup in One Script

Run everything at once:

```bash
#!/bin/bash
sudo dnf install -y samba samba-client samba-common
sudo cp /etc/samba/smb.conf /etc/samba/smb.conf.backup
sudo mkdir -p /home/bob/fmm-testing/{windows,mac,feedback/{windows,mac}}
sudo chmod 777 /home/bob/fmm-testing
sudo systemctl enable smb nmb
sudo systemctl restart smb nmb
sudo chmod 755 /path/to/wordpress  # Change this path!
sudo chown -R bob:bob /home/bob/fmm-testing
sudo smbpasswd -a bob
sudo smbclient -L localhost -U bob
```

---

## Regular Maintenance

### Check Samba Health
```bash
# Weekly check
sudo testparm
sudo systemctl status smb nmb
df -h
```

### View Recent Connections
```bash
sudo smbstatus
```

### Clear Old Logs (Monthly)
```bash
sudo truncate -s 0 /var/log/samba/log.*
```

### Monitor in Real-time
```bash
# Watch Samba logs
sudo tail -f /var/log/samba/log.smbd

# In another terminal, monitor usage
watch -n 1 'sudo smbstatus'
```

---

## Security Notes

⚠️ **For testing only:**
- Current config allows all users public access
- Password is required but basic
- For production, implement:
  - IP-based access restrictions
  - Stronger authentication
  - Encryption (SMB3)
  - Regular password updates

⚠️ **Firewall:**
- Make sure only trusted networks can access
- Consider IP whitelisting
- Monitor failed login attempts

---

## Reference

- Samba home: https://www.samba.org/
- Fedora docs: https://docs.fedoraproject.org/
- SMB/CIFS protocol: https://docs.microsoft.com/en-us/openspecs/windows_protocols/ms-smb/
