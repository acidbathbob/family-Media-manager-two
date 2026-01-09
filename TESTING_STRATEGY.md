# Family Media Manager Testing Strategy

Master guide for coordinating comprehensive testing of the Windows and Mac installers using your bob490.co.uk server.

## Overview

You'll test the installers using your Fedora home server. This guide helps you:
- Set up the testing environment on your server
- Coordinate with guinea-pig testers
- Collect and analyze feedback
- Iterate on the installers

## Your Testing Setup

### Server Infrastructure (bob490.co.uk)

```
bob490.co.uk (Fedora Home Server)
├── WordPress Installation (existing)
│   └── Family Media Manager Plugin v1 (existing)
├── Samba Shares (NEW - for testing)
│   ├── /wordpress - for installer file access
│   └── /testing - for feedback collection
└── Configuration (NEW - for security)
```

### Why This Works

✅ **HTTPS enabled** - WordPress at `https://bob490.co.uk` is live
✅ **Plugin installed** - Testers can activate v1 and test updates
✅ **Plenty of disk space** - Room for multiple test installations
✅ **Fedora server** - Supports Samba for Windows/Mac file access
✅ **You have control** - Can debug issues directly
✅ **Real-world testing** - Not a sandbox, actual deployment scenario

## Phase 1: Prepare Your Server

### Step 1: Install and Configure Samba

```bash
# SSH into your server
ssh your_user@bob490.co.uk

# Install Samba
sudo dnf install samba samba-client samba-common

# Backup original config
sudo cp /etc/samba/smb.conf /etc/samba/smb.conf.backup

# Edit configuration
sudo nano /etc/samba/smb.conf
```

Add these sections (modify paths to match your setup):

```ini
[global]
   workgroup = WORKGROUP
   server string = Bob Home Server
   security = user
   passdb backend = tdbsam
   log file = /var/log/samba/log.%m
   max log size = 50

[wordpress]
   path = /path/to/your/wordpress
   public = yes
   writable = yes
   browsable = yes
   force user = your_username
   comment = WordPress Installation

[testing]
   path = /home/bob/fmm-testing
   public = yes
   writable = yes
   browsable = yes
   comment = Test Results and Feedback
```

Then:

```bash
# Create testing directory
mkdir -p /home/bob/fmm-testing/{windows,mac,feedback}
chmod 777 /home/bob/fmm-testing

# Restart Samba
sudo systemctl restart smb
sudo systemctl enable smb

# Verify it's running
sudo systemctl status smb

# Set WordPress permissions
sudo chmod 755 /path/to/wordpress
```

### Step 2: Verify Samba Access

**From Windows:**
```
\\bob490.co.uk\wordpress
\\bob490.co.uk\testing
```

**From Mac:**
```
smb://bob490.co.uk/wordpress
smb://bob490.co.uk/testing
```

### Step 3: Create Test Subdirectories

```bash
# Windows testing
mkdir -p /home/bob/fmm-testing/windows/{pre-installed,network-path,local-zip}

# Mac testing
mkdir -p /home/bob/fmm-testing/mac/{dmg,app-direct,network-path}

# General feedback
mkdir -p /home/bob/fmm-testing/feedback/{windows,mac}

# Create README files
cat > /home/bob/fmm-testing/README.txt << 'EOF'
FMM Testing Directory
====================

Windows Testers:
- Feedback here: testing/feedback/windows/
- Upload screenshots, config files, feedback forms

Mac Testers:
- Feedback here: testing/feedback/mac/
- Upload screenshots, config files, feedback forms

General Notes:
- Keep credentials sanitized when uploading config files
- Use clear filenames with date and tester name
- Include any error messages or logs
EOF
```

## Phase 2: Recruit and Brief Testers

### Finding Guinea-Pig Testers

Candidates:
- Non-technical family members (ideal for UX feedback)
- One Windows user
- One Mac user
- If possible, get more: 2 Windows + 2 Mac + 1 Linux

### What to Send Them

Send each tester a package with:

**Windows Testers - Email Package:**
```
Subject: Need Your Help Testing Family Media Manager Installer

Hi [Name],

I need your help testing a new Windows installer for My Family Media 
Manager plugin. It should only take you 15-20 minutes.

Please follow the guide: README_WINDOWS.md (attached)

Then fill out: FEEDBACK_FORM_WINDOWS.txt (attached)

Upload your feedback to: \\bob490.co.uk\testing\feedback\windows\
From Windows file explorer: File Explorer → Type in address bar:
                          \\bob490.co.uk\testing\feedback\windows\
Questions? Email me!
Whatsapp me:
message or phone!

Attachments:
- README_WINDOWS.md
- fmm-easy-setup.exe
- FEEDBACK_FORM_WINDOWS.txt

Thanks!
Bob
```

**Mac Testers - Email Package:**
```
Subject: Need Your Help Testing Family Media Manager Installer

Hi [Name],

I need your help testing a new Mac installer for my Family Media 
Manager plugin. It should only take 15-20 minutes.

Please follow the guide: README_MACOS.md (attached)

Then fill out: FEEDBACK_FORM_MAC.txt (attached)

Upload your feedback to: smb://bob490.co.uk/testing/feedback/mac/
              Finder → Cmd+K (Connect to Server) → Type:
              smb://bob490.co.uk/testing/feedback/windows/

Questions? Email me!

Attachments:
- README_MACOS.md
- fmm-easy-setup.dmg (or .app)
- FEEDBACK_FORM_MAC.txt

Thanks!
Bob
```

## Phase 3: Run the Tests

### Timeline

**Week 1:**
- Brief testers and send testing packages
- Have them download/receive installers

**Week 2:**
- Testers run installers (15-20 min each)
- They test plugin activation
- They fill out feedback forms
- They upload results

**Week 3:**
- Analyze feedback
- Debug any issues found
- Update installers if needed
- Re-test if needed

### During Testing - Your Role

**Monitor for issues:**
```bash
# Check Samba logs for access issues
sudo tail -f /var/log/samba/log.*

# Watch WordPress error logs
tail -f /path/to/wordpress/wp-content/debug.log

# Monitor disk space
df -h
```

**Support testers:**
- Check feedback uploads regularly
- Help with any WordPress folder location questions
- Provide credential access if needed
- Note any recurring issues

## Phase 4: Collect and Analyze Feedback

### Feedback Organization

```bash
# Each tester creates a folder with their results
/home/bob/fmm-testing/
├── feedback/
│   ├── windows/
│   │   ├── jane_doe_2026-01-15/
│   │   │   ├── feedback_form.txt
│   │   │   ├── config.json (sanitized)
│   │   │   └── screenshots/
│   │   └── john_smith_2026-01-16/
│   │       └── ...
│   └── mac/
│       ├── sarah_jones_2026-01-15/
│       │   ├── feedback_form.txt
│       │   ├── config.json (sanitized)
│       │   └── screenshots/
│       └── ...
```

### Analysis Template

Create a summary document:

```bash
cat > /home/bob/fmm-testing/ANALYSIS.txt << 'EOF'
TESTING ANALYSIS SUMMARY
========================

Test Date: [Date]
Total Testers: [Number]
  - Windows: [Number] (Success: [Number])
  - Mac: [Number] (Success: [Number])

ISSUES FOUND
============

Critical Issues:
1. [Issue description]
   - Severity: Critical
   - Testers affected: [Names]
   - Fix needed: Yes/No

2. ...

Minor Issues:
1. [Issue description]
   - Severity: Minor
   - Testers affected: [Names]
   - Fix needed: Yes/No

FEEDBACK SUMMARY
================

Most Confusing Parts:
- [Item 1] (mentioned by [X] testers)
- [Item 2] (mentioned by [X] testers)

Easiest Parts:
- [Item 1] (mentioned by [X] testers)

RECOMMENDATIONS
===============

1. [Action item] - Priority: High/Medium/Low
2. [Action item] - Priority: High/Medium/Low

NEXT STEPS
==========

1. Fix critical issues
2. Update documentation
3. Re-test if major changes made
4. Build final versions for distribution
EOF
```

## Phase 5: Iterate and Improve

### If Issues Found

**Critical issues found:**
```bash
# 1. Fix the code
# Edit fmm-easy-setup.py, rebuild .exe/.app
# Test your fix first

# 2. Rebuild installers
# Windows: pyinstaller --onefile --windowed fmm-easy-setup.py
# Mac: pyinstaller --onefile --windowed fmm-easy-setup.py

# 3. Re-test with one tester
# Send updated installer to single tester
# Quick validation before full re-test
```

**Minor issues found:**
```bash
# Update documentation/guides
# Edit README_WINDOWS.md or README_MACOS.md
# Add clarifications to confusing sections
# Rebuild and re-distribute for review
```

### Documentation Updates

If testers found things confusing:

1. Identify the section in the guide
2. Simplify the language
3. Add examples or screenshots
4. Have the same tester re-test the updated guide
5. Confirm it's clearer now

## Test Scenarios to Cover

### Windows Testing

- [ ] **Test 1:** UNC network path
  - Tester: [Name]
  - Method: Network share access
  - Status: Pass/Fail/Partial
  - Issues: [List any]

- [ ] **Test 2:** Pre-installed plugin
  - Tester: [Name]
  - Method: Folder location provided
  - Status: Pass/Fail/Partial
  - Issues: [List any]

- [ ] **Test 3:** Standalone ZIP
  - Tester: [Name]
  - Method: Downloaded files
  - Status: Pass/Fail/Partial
  - Issues: [List any]

### Mac Testing

- [ ] **Test 1:** DMG distribution
  - Tester: [Name]
  - Method: DMG mount + app install
  - Status: Pass/Fail/Partial
  - Issues: [List any]

- [ ] **Test 2:** Direct .app
  - Tester: [Name]
  - Method: Direct application launch
  - Status: Pass/Fail/Partial
  - Issues: [List any]

- [ ] **Test 3:** Network SMB path
  - Tester: [Name]
  - Method: SMB share access
  - Status: Pass/Fail/Partial
  - Issues: [List any]

## Communication with Testers

### Email Templates

**Before Testing:**
```
Subject: Installer Testing Instructions

Hi [Name],

Thank you for helping me test the Family Media Manager installer!

Here's what I need:
1. Download the attached installer
2. Follow the README guide step by step
3. Note any confusing parts or errors
4. Fill out the feedback form
5. Upload results to the shared folder

Expected time: 15-20 minutes

Questions? Don't hesitate to email me.

Thanks!
Bob
```

**During Testing:**
```
Subject: Need Help with Testing?

Hi [Name],

I saw you started testing the installer. How's it going?

Do you have any questions or run into any issues?

Let me know if you need clarification on any steps.

Thanks!
Bob
```

**After Testing:**
```
Subject: Thanks for Testing!

Hi [Name],

Thank you so much for testing the installer and providing feedback!

Your input really helps make it better for everyone.

If you discover any additional issues, feel free to email me.

Thanks again!
Bob
```

## Tracking Checklist

```bash
# Create master tracking document
cat > /home/bob/fmm-testing/TESTING_LOG.md << 'EOF'
# Testing Log

## Windows Testers
- [ ] Tester 1: [Name] - Method: [Option] - Status: [Pending/In Progress/Complete]
- [ ] Tester 2: [Name] - Method: [Option] - Status: [Pending/In Progress/Complete]
- [ ] Tester 3: [Name] - Method: [Option] - Status: [Pending/In Progress/Complete]

## Mac Testers
- [ ] Tester 1: [Name] - Method: [Option] - Status: [Pending/In Progress/Complete]
- [ ] Tester 2: [Name] - Method: [Option] - Status: [Pending/In Progress/Complete]
- [ ] Tester 3: [Name] - Method: [Option] - Status: [Pending/In Progress/Complete]

## Issues Found
- [ ] Issue 1: [Description] - Severity: [Critical/High/Medium/Low]
- [ ] Issue 2: [Description] - Severity: [Critical/High/Medium/Low]

## Actions Taken
- [ ] Action 1: [Description] - Completed: [Date]
- [ ] Action 2: [Description] - Completed: [Date]

## Final Status
- Windows Installer: [Ready for Distribution/Needs Work]
- Mac Installer: [Ready for Distribution/Needs Work]
- Documentation: [Ready for Distribution/Needs Work]
EOF
```

## Success Criteria

Your testing is successful when:

✅ **Installation Phase**
- Installer runs without crashes
- Users can select WordPress folder
- URL entry works correctly
- Plugin files copy without errors

✅ **Activation Phase**
- Plugin appears in WordPress Plugins list
- Plugin can be activated
- No PHP errors on activation
- Settings page is accessible

✅ **Google Drive Phase**
- Instructions are clear
- Redirect URL format is correct
- Google authentication works
- Credentials are saved

✅ **User Experience**
- No confusion about file locations
- Clear error messages
- Helpful troubleshooting hints
- Non-technical users can complete it

✅ **Documentation**
- Users can follow the README without help
- Screenshots match actual interface
- Troubleshooting section covers issues
- Support contact information is clear

## Post-Testing Deliverables

After successful testing:

```
fmm-easy-setup-windows-v1.0.zip
├── fmm-easy-setup.exe
├── family-media-manager.php
├── includes/
├── admin/
├── public/
├── pwa/
└── README_WINDOWS.md

fmm-easy-setup-mac-v1.0.dmg
(Contains same structure as Windows)

BUILD_WINDOWS_EXE.md (build instructions)
BUILD_MAC_APP.md (build instructions)
TESTING_STRATEGY.md (this file)
```

## Timeline Example

```
Week of Jan 9:
- Mon: Prepare server, install Samba
- Tue: Brief testers, send installers
- Wed: Testers download/receive packages
- Thu: First testers start testing

Week of Jan 16:
- Mon-Wed: Most testing completes
- Thu: Analyze feedback
- Fri: Plan any fixes needed

Week of Jan 23:
- Mon-Tue: Make fixes if needed
- Wed: Quick re-test if critical fixes made
- Thu: Build final distribution versions
- Fri: Documentation review

Week of Jan 30:
- Ready for wider distribution!
```

## Good Luck!

You're set up for success:
- ✅ Home server with real WordPress
- ✅ HTTPS domain (bob490.co.uk)
- ✅ Network file sharing capability
- ✅ Plenty of disk space
- ✅ Full control for debugging

Now go recruit some guinea-pig testers and get that feedback rolling in!
