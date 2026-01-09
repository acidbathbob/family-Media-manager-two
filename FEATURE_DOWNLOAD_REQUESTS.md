# Feature: Download Request & Approval System

## Overview

Convert the direct "Download" button to a "Request Download" system where family members request permission to download media, and an admin must approve the request before the download is available.

## User Workflow

### For Family Members (Requesters)

**Before:** Click "Download" button → File downloads immediately

**After:** 
1. Click "Request Download" button
2. Optional: Add a message explaining why they want the download (e.g., "For printing", "For backup")
3. Request is submitted and shows status: "⏳ Pending Approval"
4. Once approved, they get:
   - Notification (email or in-app)
   - Direct download link (time-limited, e.g., 24 hours)
   - Or "Download Now" button becomes active

### For Family Admin (Approvers)

**New Admin Panel Section:**
- "Download Requests" menu
- Shows list of pending requests with:
  - Requester name
  - Media title/thumbnail
  - Requested date/time
  - Reason/message (if provided)
  - Approve button
  - Deny button (optional)
  - View media preview

**Approval Actions:**
- ✅ Approve → Download link generated, requester notified
- ❌ Deny → Requester notified (admin can include reason)
- 💬 Request More Info → Send message to requester, see their response
- 🔄 Revoke → Take back previously approved download
- 📋 View Conversation → See full message history with requester

## Benefits

✅ **Privacy Control** - Admin knows who's downloading what
✅ **Parental Control** - Can monitor teen/child requests
✅ **Backup Awareness** - See if someone is archiving content
✅ **Audit Trail** - Log of all downloads
✅ **Selective Sharing** - Only approve appropriate requests
✅ **Bandwidth Management** - Control when large downloads happen

## Technical Implementation

### Database Schema Changes

```sql
-- New table for download requests
CREATE TABLE fmm_download_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    media_id INT NOT NULL,
    requester_id INT NOT NULL,
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'denied', 'expired') DEFAULT 'pending',
    admin_id INT,
    approval_date DATETIME,
    admin_message TEXT,
    requester_message TEXT,
    download_token VARCHAR(255),
    token_expiry DATETIME,
    download_count INT DEFAULT 0,
    downloaded_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (media_id) REFERENCES fmm_media(id),
    FOREIGN KEY (requester_id) REFERENCES wp_users(ID),
    FOREIGN KEY (admin_id) REFERENCES wp_users(ID)
);

-- Track download history
CREATE TABLE fmm_download_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT NOT NULL,
    download_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (request_id) REFERENCES fmm_download_requests(id) ON DELETE CASCADE
);
```

### Frontend Changes

**Family Member View:**
```
[Media Card]
├── Title: "Video Name"
├── Thumbnail
├── Watch Button (unchanged)
└── [Request Download] Button (replaces Download)
    └── On Click: Modal appears
        ├── "Why do you want to download this?"
        ├── Text area for optional message
        └── [Submit Request] button
```

**Download Request Status:**
```
⏳ Pending Approval (shows elapsed time: "Requested 2 hours ago")
or
✅ Approved (shows countdown: "Link expires in: 18 hours")
or  
❌ Denied (shows admin message if provided)
```

### Admin Panel Changes

**New Section: Download Requests**
```
[Download Requests] (Admin Menu)
├── Tab: "Pending" (shows count badge)
│   └── List of pending requests
│       ├── Requester name
│       ├── Media name + thumbnail
│       ├── Request time
│       ├── Reason message
│       ├── [Approve] [Deny] [More Info] buttons
│       └── Expand to see full details
│
├── Tab: "Approved" (archive)
│   └── Shows approved requests
│       ├── Download link
│       ├── Download count
│       ├── Last downloaded: [date]
│       └── [Revoke] button
│
├── Tab: "Denied"
│   └── Shows denied requests
│       └── Reason admin gave
│
└── Tab: "Reports"
    ├── Most requested media
    ├── Most frequent requesters
    ├── Download trends
    └── Export CSV
```

### Email Notifications

**When request submitted:**
```
Subject: New Download Request - Media Title

Hi [Admin Name],

[Family Member Name] has requested to download "Media Title"

Reason: [Their message or "No reason provided"]

[Approve] [Deny] [View in Admin Panel]
```

**When request approved:**
```
Subject: Your Download Request Has Been Approved!

Hi [Family Member Name],

Your request to download "Media Title" has been approved!

[Download Now] (link valid for 24 hours)

Download link expires: [Date/Time]
```

**When request denied:**
```
Subject: Download Request Status Update

Hi [Family Member Name],

Your request to download "Media Title" was not approved.

[Optional message from admin about why]

If you have questions, please contact [Admin].
```

## Configuration Options

**Admin Settings:**
- [ ] Auto-approve downloads (disable the feature)
- [ ] Time limit for download links (default: 24 hours)
- [ ] Max downloads per approved link (default: unlimited)
- [ ] Require reason message (yes/no)
- [ ] Auto-approve for certain users (trusted)
- [ ] Notification method (email, in-app, both)
- [ ] Request expiry time - how long pending request lasts (default: 30 days)

## User Experience Flows

### Happy Path (Approve)
1. Family member clicks "Request Download"
2. Provides optional reason
3. Request submitted → Status shows "⏳ Pending"
4. Admin sees notification/pending count badge
5. Admin clicks Approve
6. Family member gets email: "Approved! Download here"
7. Member downloads within time limit
8. Request marked as "Completed"

### Deny Path
1. Family member requests
2. Admin denies with reason: "Too large for bandwidth today, please request tomorrow"
3. Member gets email with reason
4. Status shows "❌ Denied - Admin reason provided"

### Expired Path
1. Request sits for 30 days without approval
2. System auto-marks as "expired"
3. Member needs to submit new request if still needed

### Pre-Approval Messaging Path (Optional)
1. Family member submits request to download video
2. Admin sees request, has questions about why
3. Admin clicks "Request More Info" and sends message:
   - "Hi Sarah, this is a large video file. What will you use it for?"
4. Family member gets notification with admin's question
5. Member replies in the same conversation:
   - "I want to print stills from it for the photo album"
6. Admin sees the response and has full context
7. Admin clicks "Approve" with full understanding
8. Member gets approval notification with download link
9. Full message history stays logged with the request

## Security Considerations

✅ **Token-based downloads:** Generate unique token for each approved request
✅ **Expiring links:** Links expire after X hours
✅ **IP verification:** Optional - verify download comes from same IP
✅ **Rate limiting:** Prevent abuse of request system
✅ **Audit logging:** Log all requests, approvals, denials, downloads
✅ **GDPR compliance:** Include download consent notice

## Phase Implementation

### Phase 1 (MVP - Essential)
- [x] Request button replaces download button
- [x] Simple approve/deny admin interface
- [x] Email notifications
- [x] Download token system
- [x] Basic audit log

### Phase 2 (Enhancement)
- [ ] Request reason/message
- [ ] Detailed admin panel with filters
- [ ] Download history per request
- [ ] Reports/analytics
- [ ] Auto-approve for trusted users

### Phase 3 (Advanced)
- [ ] Time-based auto-approve (e.g., after 24 hours, auto-approve)
- [ ] Bulk approve/deny
- [ ] Request templates ("For printing", "For backup", etc.)
- [ ] Integration with media storage (show file size before approval)
- [ ] Conditional approval (e.g., "Approve only if < 1GB")

## Testing Checklist

### Functional Testing
- [ ] Request button appears instead of download button
- [ ] Request submission works
- [ ] Admin sees new requests in panel
- [ ] Approve action generates download link
- [ ] Deny action sends notification
- [ ] Download link expires correctly
- [ ] Expired links show error message
- [ ] Download counter increments
- [ ] Request history is logged

### User Experience
- [ ] Clear status indicators (pending/approved/denied)
- [ ] Notifications arrive promptly
- [ ] Error messages are helpful
- [ ] Mobile-friendly request flow
- [ ] Admin panel is intuitive

### Security
- [ ] Token is unique per request
- [ ] Link expiration works
- [ ] Invalid tokens rejected
- [ ] Rate limiting prevents spam
- [ ] Audit log is complete

### Edge Cases
- [ ] Admin deletes media while request pending
- [ ] User tries to use expired link
- [ ] Multiple requests for same media
- [ ] Admin approves then revokes
- [ ] System behavior with no approved requests

## Mockups/Screenshots

### Family Member View
```
┌─────────────────────────────────┐
│  Video: "Family Beach Day 2025" │
│  [Thumbnail Image]              │
│                                 │
│  ⏯ [Watch]                      │
│  📥 [Request Download]           │
│                                 │
│  Status: ⏳ Pending (since 2h)   │
└─────────────────────────────────┘
```

### Admin Panel
```
Download Requests
┌──────────────────────────────────────┐
│ ⏳ Pending (3)  ✅ Approved  ❌ Denied │
├──────────────────────────────────────┤
│ Sarah Johnson                        │
│ Media: "Beach Day Video"             │
│ Reason: "For family video montage"   │
│ Requested: 2 hours ago               │
│ [Approve] [Deny] [Info]              │
├──────────────────────────────────────┤
│ [more requests...]                   │
└──────────────────────────────────────┘
```

## Admin Control Philosophy

✅ **Owner Maintains Full Control**
- Admin must explicitly approve every download
- No auto-approval - all requests require human decision
- Admin can deny without providing reason
- Admin can revoke previously approved downloads if needed
- All approval decisions are logged and auditable

✅ **Optional: Pre-Approval Messaging**
Admin can optionally contact family member BEFORE approving:
- "Hi Sarah, can you tell me more about why you need this video?"
- Family member responds with clarification
- Admin reviews response, then makes final decision
- Complete conversation history is logged

This ensures:
- Administrator controls all media access
- Contact/discussion is optional (not required)
- No surprises - approvals only after understanding
- Full transparency and communication

## Questions for Bob

1. **Messaging feature:** Should admins be able to request more info from the requester before approving?
2. **Contact history:** Should the full message conversation be visible in the approval record?
3. **Rate limiting:** Should there be a limit on how many downloads per person per month/year?
4. **Revocation:** Should admins be able to revoke previously approved downloads?
5. **Pending timeout:** How long should a pending request stay open (default: 30 days) before expiring?

## Related Issues

This feature addresses:
- Privacy concerns about media downloads
- Parental control needs
- Bandwidth management
- Audit trail requirements
- Selective sharing capabilities

## Documentation Needed

- Admin guide: How to approve/deny requests
- Family member guide: How to request downloads
- Settings documentation: Configuration options
- Report interpretation guide
