# Download Request Feature - Design Summary

## Core Principle

**Media ownership and control stays with the admin/owner.**

All downloads require explicit admin approval. No automatic approvals, no exceptions.

---

## Feature Overview

### What Changes

**Before:**
```
Family Member → [Download Button] → File downloads immediately
```

**After:**
```
Family Member → [Request Download Button] → Status: Pending
                                                    ↓
                                            Admin reviews request
                                                    ↓
                                    Admin approves/denies/messages
                                                    ↓
                                    Family member notified
```

---

## Admin Approval Process

### Standard Path (No Messaging)
1. Family member clicks "Request Download"
2. Request appears in admin panel
3. Admin reviews and clicks "Approve" or "Deny"
4. Family member gets notification

### Extended Path (With Messaging)
1. Family member clicks "Request Download"
2. Request appears in admin panel
3. Admin reads the request and has questions
4. Admin clicks "Request More Info"
5. Admin sends message: "Why do you need this file?"
6. Family member gets notification and replies
7. Admin sees the conversation and understands context
8. Admin clicks "Approve" (now with full information)
9. Family member gets approval notification
10. Complete message history stays logged

---

## Key Design Points

### ✅ Admin Control (Non-Negotiable)
- Every download request requires explicit admin approval
- No auto-approve timers
- No "trusted user" bypass
- No bulk auto-approval
- Admin maintains complete control

### ✅ Optional Communication
- Admins CAN ask questions before approving
- Family members MUST respond to clarifications
- Conversation history is preserved
- Helps admin make informed decisions
- Builds transparency

### ✅ Complete Audit Trail
- Every request is logged
- Every approval/denial is recorded
- Every message is stored
- Admin can see who downloaded what, when
- Reports available for review

### ✅ Flexible Denial
- Admin can deny requests for any reason
- Admin can provide reason or stay silent
- Admin can revoke previously approved downloads
- No appeals process needed (admin is final decision)

---

## Technical Implementation

### Database Tables Needed

```sql
fmm_download_requests
├── id (request ID)
├── media_id (which file)
├── requester_id (who wants it)
├── request_date (when requested)
├── status (pending/approved/denied/expired)
├── admin_id (who approved)
├── approval_date (when approved)
├── download_token (unique link)
├── token_expiry (link expires when)
├── admin_message (reason for denial)
├── requester_message (why they want it)
└── ...

fmm_download_messages
├── id
├── request_id
├── sender_id (admin or family member)
├── message (the actual message)
├── sent_date
└── ...

fmm_download_history
├── id
├── request_id
├── download_date
├── ip_address
├── ...
```

---

## User Interface Changes

### For Family Members

**On Media Page:**
```
[Video Thumbnail]
Title: "Beach Day 2025"

[⏯ Watch] [📥 Request Download]

Status area:
├─ ⏳ Pending (since 2 hours ago)
│  [You requested this on Jan 9 at 3:00pm]
│
├─ ✅ Approved (expires in 18 hours)
│  [Download Now]
│
├─ 💬 Awaiting Your Response
│  Admin: "Why do you need this file?"
│  [Your Reply ▼]
│
└─ ❌ Not Approved
   Admin: "This file is too large for this month"
```

### For Admin

**New Menu Section: "Download Requests"**
```
[Download Requests]
├─ Pending (3) ← Shows count badge
├─ Approved
├─ Denied
└─ Reports

[Pending Tab]
├─ Request from: Sarah Johnson
│  Media: "Beach Day Video" (Thumbnail)
│  Status: Pending since 2 hours ago
│  Message from Sarah: (if provided)
│  
│  [Approve] [Deny] [Request More Info] [View Details]
│
├─ Request from: Tom Wilson
│  Media: "Family Dinner" (Thumbnail)
│  Status: Awaiting Sarah's Response
│  Your message: "What will you use this for?"
│  (Shows conversation so far)
│  
│  [View Full Conversation] [Approve] [Deny]
│
└─ ...

[Details View for Request]
├─ Requester: Sarah Johnson
├─ Media: "Beach Day Video" (preview)
├─ File Size: 2.4 GB
├─ Requested: Jan 9, 2:45 PM
├─ Sarah's reason: "For backup"
│
├─ [Conversation History]
│  Admin (2:50 PM): "What will you use this for?"
│  Sarah (3:15 PM): "I want to back it up to my external drive"
│  Admin (just now): "That makes sense"
│
├─ Admin Actions:
│  [Approve] [Deny] [Request More Info]
│  [Preview Media] [View Request Details]
│
└─ [Close Details]
```

---

## Configuration Options

Admin can customize:

- ⏱️ **How long requests stay pending** (default: 30 days before expiry)
- 🔗 **Download link expiry time** (default: 24 hours after approval)
- 📧 **Notification method** (email, in-app, both)
- 💬 **Optional: Messaging enabled/disabled**
- 📊 **Keep reports/audit trail** (yes/no)

---

## Workflow Examples

### Scenario 1: Quick Approval
```
Sarah: "I'd like to download 'Family Reunion Video'"
Admin: [Reads request] "Looks fine" [Clicks Approve]
Sarah: [Gets email] "Your download has been approved!"
Sarah: [Downloads within 24 hours]
Done ✓
```

### Scenario 2: Need Clarification
```
Tom: "I'd like to download 'Our Vacation 4K Video'"
Admin: [Sees it's 8GB] "Need to ask about this"
Admin: [Clicks "Request More Info"]
Admin: [Sends message] "This is a large file. What's it for?"
Tom: [Gets notification] [Replies] "Printing photos for album"
Admin: [Sees response] "Got it, makes sense" [Approves]
Tom: [Gets email] "Download approved!"
Tom: [Downloads file]
Done ✓
```

### Scenario 3: Denial
```
Child: "I'd like to download this"
Admin: [Reviews] [Is concerned about content]
Admin: [Clicks Deny] [Optional: Adds reason] "Not appropriate for this age"
Child: [Gets email] "Request was not approved. Talk to me if you have questions."
Done ✓
```

### Scenario 4: Revocation
```
Sarah: [Had approval, downloaded file]
Admin: [Later decides] "Actually, this shouldn't be downloadable"
Admin: [Finds the approved request] [Clicks Revoke]
Sarah: [Download link stops working]
If Sarah tries again: [Has to submit new request]
Done ✓
```

---

## Benefits Summary

✅ **Privacy** - Know who's downloading what
✅ **Control** - Maintain complete authority over media
✅ **Communication** - Optional pre-approval discussion
✅ **Transparency** - Full audit trail and conversation history
✅ **Flexibility** - Approve, deny, or ask for more info
✅ **Security** - Time-limited download links
✅ **Parental** - Monitor what family members want to access
✅ **Bandwidth** - Control when large downloads happen

---

## Questions Still Open

1. Should messaging be enabled by default, or admin-optional?
2. Should family members be able to see the reason for denial?
3. Should there be a limit on downloads per person per month?
4. Should admins be notified of all requests immediately, or in a digest?
5. Should revoked links show a helpful message, or generic error?

---

## Implementation Priority

### Phase 1: MVP (Must Have)
- Request button replaces download
- Basic approve/deny in admin panel
- Email notifications
- Download tokens that expire
- Audit log of all actions

### Phase 2: Enhancement
- Optional messaging between admin and requester
- Conversation history in request details
- More detailed admin panel
- Reports and analytics

### Phase 3: Advanced
- Conditional approvals ("Approve only if < 5GB")
- Bulk operations
- Download history per family member
- Advanced filtering and search

---

## Success Metrics

After implementation, admins should be able to:
- ✅ See all pending download requests at a glance
- ✅ Review and approve/deny in under 30 seconds
- ✅ Communicate with family members about specific requests
- ✅ View complete history of who downloaded what
- ✅ Revoke access if needed
- ✅ Export reports for record-keeping

