# PerformHub - Admin Pages Architecture Diagram

## User Flow Chart

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           PERFORMHUB USER FLOW                          │
└─────────────────────────────────────────────────────────────────────────┘

                         ┌──────────────────┐
                         │  HOMEPAGE (/)    │
                         │ (Landing Page)   │
                         └────────┬─────────┘
                                  │
                   ┌──────────────┼──────────────┐
                   │              │              │
                   ▼              ▼              ▼
            ┌────────────┐  ┌─────────────┐  ┌──────────────┐
            │  Sign In   │  │ Get Started │  │  Dashboard   │
            │   Link     │  │    Link     │  │    Link      │
            └──────┬─────┘  └──────┬──────┘  (if logged in)
                   │               │
                   ▼               ▼
          ┌──────────────────────────────┐
          │    LOGIN PAGE (/login)       │
          │  - Email + Password          │
          │  - Remember Me checkbox      │
          │  - Link to Register          │
          └──────────┬───────────────────┘
                     │
                     ├─────────────────────────────────┐
                     │                                 │
            ✓ Valid Credentials            ✗ Invalid
                     │                       │
                     ▼                       ▼
          ┌──────────────────────┐    ┌──────────────┐
          │ Check Account Status │    │ Show Error   │
          └──────────┬───────────┘    │ Message      │
                     │                └──────────────┘
                     │
            ┌────────┴────────┐
            │                 │
        is_active          NOT active
            │                 │
            ▼                 ▼
    ┌───────────────┐  ┌──────────────────┐
    │ Redirect to   │  │ Logout & show    │
    │ Role-specific │  │ "Account has been│
    │ Dashboard     │  │ deactivated"     │
    └───────────────┘  └──────────────────┘
            │
            ├──────────────┬──────────────┬──────────────┐
            │              │              │              │
         admin          performer      organizer    (other roles)
            │              │              │
            ▼              ▼              ▼
    ┌──────────────┐  ┌──────────┐  ┌──────────────┐
    │ Admin        │  │Performer │  │ Organizer    │
    │ Dashboard    │  │Dashboard │  │ Dashboard    │
    │(/admin/...)  │  │(/perfo..)│  │(/organizer..)│
    └──────────────┘  └──────────┘  └──────────────┘


                    ┌──────────────────────┐
                    │  REGISTER PAGE       │
                    │  (/register)         │
                    └──────────┬───────────┘
                               │
                               ▼
              ┌────────────────────────────────┐
              │   STAGE 1: Terms & Conditions  │
              │  - Read full T&C text          │
              │  - Read Disclaimer             │
              │  - Read Data Privacy (DPA2012) │
              │  - Check: "I Agree"            │
              │  - Continue button             │
              └────────────┬───────────────────┘
                           │
                      ✓ Accepted
                           │
                           ▼
              ┌────────────────────────────────┐
              │  STAGE 2: Personal Information │
              │  - First Name (req)            │
              │  - Last Name (req)             │
              │  - Username (optional)         │
              │  - Email (req, unique)         │
              │  - Password (req, 8+ chars)    │
              │  - Confirm Password            │
              │  - Role: Performer/Organizer   │
              │  - Submit                      │
              └────────────┬───────────────────┘
                           │
                     Validation
                           │
              ┌────────────┴────────────┐
              │                         │
          ✓ Valid                   ✗ Invalid
              │                         │
              ▼                         ▼
    ┌──────────────────────┐  ┌────────────────┐
    │ 1. Create User row   │  │ Show validation│
    │ 2. Create Profile    │  │ errors on form │
    │ 3. Send notification │  └────────────────┘
    │    to admins         │
    │ 4. Auto-login user   │
    │ 5. Redirect to       │
    │    Onboarding        │
    └──────────────────────┘
```

---

## Page Structure Diagram

```
┌─────────────────────────────────────────────────────────┐
│                    HOMEPAGE (Landing)                   │
├─────────────────────────────────────────────────────────┤
│  NAVBAR (Fixed)                                         │
│  ├─ Logo & Brand                                        │
│  ├─ Nav Links (Categories, How It Works)               │
│  └─ Auth Buttons (Sign In | Get Started | Dashboard)   │
├─────────────────────────────────────────────────────────┤
│  HERO SECTION                                           │
│  ├─ Tagline: "Discover Talent. Book the Perfect       │
│  │   Performance."                                      │
│  └─ CTA Buttons (Find Performers | Join as Performer) │
├─────────────────────────────────────────────────────────┤
│  CATEGORIES SECTION                                     │
│  └─ Grid of active categories with icons               │
├─────────────────────────────────────────────────────────┤
│  FEATURED PERFORMERS SECTION                           │
│  └─ Cards: Photo | Name | Verified | Categories |      │
│     Location | Bio | Rate                              │
├─────────────────────────────────────────────────────────┤
│  HOW IT WORKS SECTION                                  │
│  ├─ Step 1: Discover                                   │
│  ├─ Step 2: Request                                    │
│  └─ Step 3: Book                                       │
├─────────────────────────────────────────────────────────┤
│  WHY CHOOSE PERFORMHUB SECTION                         │
│  ├─ Verified Performers                               │
│  ├─ Smart Scheduling                                   │
│  └─ Contract Management                                │
├─────────────────────────────────────────────────────────┤
│  FINAL CTA SECTION                                     │
│  └─ "Get Started Free" | "Go to Dashboard"            │
└─────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────┐
│               LOGIN PAGE (/login)                       │
├─────────────────────────────────────────────────────────┤
│  LEFT SIDE (Hero - Desktop only)         RIGHT SIDE    │
│  ├─ Logo                                  (Form Panel)  │
│  ├─ "Welcome back to the stage."         ├─ Back link  │
│  └─ Tagline                               ├─ Title      │
│                                           ├─ Description│
│                                           ├─ Email      │
│                                           ├─ Password   │
│                                           ├─ Remember Me│
│                                           ├─ Sign In    │
│                                           └─ Register   │
│                                              link       │
└─────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────┐
│            REGISTER PAGE (/register)                    │
├─────────────────────────────────────────────────────────┤
│  LEFT SIDE (Hero)           RIGHT SIDE (Form)          │
│  ├─ Logo                    ┌──────────────────────┐   │
│  ├─ "Join the stage!"       │ STAGE 1: T&C        │   │
│  └─ Tagline                 ├─ Scrollable T&C    │   │
│                             ├─ Checkbox: I Agree  │   │
│                             └─ Continue Button    │   │
│                                                    │   │
│                             ┌──────────────────────┐   │
│                             │ STAGE 2: Registration│   │
│                             ├─ First Name         │   │
│                             ├─ Last Name          │   │
│                             ├─ Username (opt)     │   │
│                             ├─ Email              │   │
│                             ├─ Password           │   │
│                             ├─ Confirm Password   │   │
│                             ├─ Role (hidden)      │   │
│                             └─ Submit Button      │   │
└─────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────┐
│          ADMIN DASHBOARD (/admin/dashboard)             │
├─────────────────────────────────────────────────────────┤
│  SIDEBAR                     MAIN CONTENT              │
│  ├─ Dashboard                ├─ Title: Admin Dashboard│
│  ├─ Users                    ├─ Statistics Cards:     │
│  ├─ Categories               │  ├─ Total Users       │
│  ├─ Event Types              │  ├─ Performers        │
│  └─ Monitoring               │  ├─ Organizers        │
│                              │  ├─ Bookings          │
│                              │  └─ Pending Verif.    │
│                              ├─ Recent Bookings      │
│                              │  ├─ Event Name        │
│                              │  ├─ Organizer & Perf. │
│                              │  ├─ Status Badge      │
│                              │  └─ View All Link     │
│                              └─                       │
└─────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────┐
│       ADMIN USERS LIST (/admin/users)                   │
├─────────────────────────────────────────────────────────┤
│  SIDEBAR                     MAIN CONTENT              │
│  ├─ Dashboard                ├─ Title: User Mgmt      │
│  ├─ Users (active)           ├─ Filters Panel:       │
│  ├─ Categories               │  ├─ Role Dropdown     │
│  ├─ Event Types              │  ├─ Status Dropdown   │
│  └─ Monitoring               │  └─ Filter Button     │
│                              ├─ Users Table:         │
│                              │  ├─ Headers: Name|Role│
│                              │  │         |Verified   │
│                              │  │         |Status|Act │
│                              │  ├─ Row data x15      │
│                              │  └─ Pagination        │
│                              └─                       │
└─────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────┐
│         ADMIN USER DETAIL (/admin/users/{id})           │
├─────────────────────────────────────────────────────────┤
│  SIDEBAR                     MAIN CONTENT              │
│  ├─ Dashboard                ├─ Title & Back Button   │
│  ├─ Users (active)           ├─ Basic Details Card:   │
│  ├─ Categories               │  ├─ Name              │
│  ├─ Event Types              │  ├─ Username          │
│  └─ Monitoring               │  ├─ Email             │
│                              │  ├─ Role              │
│                              │  ├─ Status            │
│                              │  └─ Verified          │
│                              ├─ Profile Info Card:   │
│                              │  ├─ Stage Name (if P) │
│                              │  ├─ Organization (O)  │
│                              │  └─ Location          │
│                              ├─ Verification Docs:   │
│                              │  ├─ Doc List          │
│                              │  └─ Approve/Reject    │
│                              ├─ Action Buttons:      │
│                              │  ├─ Verify            │
│                              │  └─ Suspend/Activate  │
│                              └─                       │
└─────────────────────────────────────────────────────────┘
```

---

## Database Relationships

```
                    ┌──────────────┐
                    │    USERS     │
                    │ (id, email..)│
                    └──────┬───────┘
                           │
            ┌──────────────┼──────────────┐
            │              │              │
            ▼              ▼              ▼
      ┌─────────────┐ ┌──────────────┐ ┌──────────────┐
      │ PERFORMER   │ │  ORGANIZER   │ │ VERIFICATION │
      │ PROFILES    │ │   PROFILES   │ │  DOCUMENTS   │
      └─────────────┘ └──────────────┘ └──────────────┘
            │
            └─────────────────────────────┬──────────────┐
                                          │              │
                                          ▼              ▼
                                    ┌──────────┐   ┌──────────┐
                                    │ BOOKINGS │   │CATEGORIES│
                                    └──────────┘   └──────────┘
                                          │
                                          ▼
                                    ┌──────────────┐
                                    │EVENT TYPES   │
                                    └──────────────┘
```

---

## Route Map

```
PUBLIC ROUTES (No Auth Required)
├── GET  /                          → HomeController@index
├── GET  /login                     → AuthController@showLogin
├── POST /login                     → AuthController@login
├── GET  /register                  → AuthController@showRegister
├── POST /register                  → AuthController@register
└── POST /logout                    → AuthController@logout

ADMIN ROUTES (Requires auth + role:admin)
├── GET  /admin/dashboard           → AdminDashboardController@index
├── GET  /admin/users               → AdminUserController@index
├── GET  /admin/users/{user}        → AdminUserController@show
├── POST /admin/users/{user}/verify → AdminUserController@verify
└── POST /admin/users/{user}/toggle → AdminUserController@toggleActive
```

---

## Data Flow in Register

```
User Input
    ↓
┌─────────────────────────────┐
│ Validate Input              │
├─────────────────────────────┤
│ • Generate/validate username│
│ • Check email uniqueness    │
│ • Validate password (8+ ch.)│
│ • Confirm password match    │
│ • Check terms accepted      │
└────────────┬────────────────┘
             │
        ✓ Valid
             │
             ▼
    ┌────────────────────┐
    │ Create User record │
    │ (is_verified=false)│
    │ (is_active=true)   │
    └────────────┬───────┘
                 │
                 ▼
    ┌────────────────────────┐
    │ Create Role Profile    │
    │ (Performer or Org.)    │
    └────────────┬───────────┘
                 │
                 ▼
    ┌────────────────────────┐
    │ Notify all admins      │
    │ (Notification system)  │
    └────────────┬───────────┘
                 │
                 ▼
    ┌────────────────────────┐
    │ Auto-login user        │
    │ (Session created)      │
    └────────────┬───────────┘
                 │
                 ▼
    ┌────────────────────────┐
    │ Redirect to Dashboard/ │
    │ Onboarding with        │
    │ success message        │
    └────────────────────────┘
```

---

## Admin User Management Flow

```
User List Page
    │
    ├─ Filter by Role (all/performer/organizer)
    └─ Filter by Status (all/active/inactive)
    
    ↓
    
Show Paginated List (15 users/page)
    │
    └─ Each row has "View" button
    
    ↓
    
Click "View" → User Detail Page
    │
    ├─ Basic Details
    ├─ Profile Info (role-specific)
    ├─ Verification Documents
    │
    └─ Action Buttons:
        ├─ Verify Account
        │   └─ Sets is_verified=true
        │   └─ Sets PerformerProfile.is_verified_badge=true (if performer)
        │
        ├─ Suspend/Activate
        │   └─ Toggles is_active boolean
        │
        └─ Back to List
```
