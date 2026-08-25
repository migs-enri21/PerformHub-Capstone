# PerformHub Admin Pages Study Guide

Your responsibilities include:
1. **Homepage/Landing Page** - Public entry point
2. **Login Page** - User authentication
3. **Register Page** - User account creation
4. **Admin Dashboard** - Admin overview & statistics
5. **Admin User Management** - Managing all users

---

## 1. HOMEPAGE (Landing Page)

### 📍 Route
```
GET / → HomeController@index → landing.blade.php
```

### 🎮 Controller: `HomeController`
**File:** `app/Http/Controllers/HomeController.php`

```php
public function index(): View
{
    $categories = Category::query()->where('is_active', true)->get();
    $featuredPerformers = PerformerProfile::query()
        ->with(['user', 'categories'])
        ->whereHas('user', fn ($q) => $q->where('is_active', true))
        ->latest()
        ->limit(6)
        ->get();

    return view('landing', compact('categories', 'featuredPerformers'));
}
```

**Logic:**
- Fetches all active categories
- Retrieves 6 most recent featured performers
- Passes data to landing view

### 🎨 View: `resources/views/landing.blade.php`

**Structure:**
```
├── Navbar (Fixed top)
│   ├── Logo & Brand
│   ├── Navigation Links (Categories, How It Works)
│   └── Auth Buttons (Sign In, Get Started, or Dashboard link)
│
├── Hero Section
│   ├── Tagline: "Discover Talent. Book the Perfect Performance."
│   ├── Description
│   └── CTA Buttons
│       ├── Find Performers (for organizers)
│       ├── Join as Performer (for performers)
│
├── Browse by Category Section
│   └── Displays all active categories with icons
│
├── Featured Performers Section
│   └── Shows 6 featured performers with:
│       ├── Profile photo
│       ├── Stage name
│       ├── Verified badge
│       ├── Categories & location
│       ├── Bio (limited to 100 chars)
│       └── Rate (₱/event)
│
├── How It Works Section
│   ├── Step 1: Discover (Search & filter performers)
│   ├── Step 2: Request (Send booking requests)
│   └── Step 3: Book (Manage contracts & complete events)
│
├── Why Choose PerformHub Section
│   ├── Verified Performers
│   ├── Smart Scheduling
│   └── Contract Management
│
└── Final CTA Section
    └── Call-to-action to register or go to dashboard
```

**Key Features:**
- Responsive design (mobile, tablet, desktop)
- Displays featured performers & categories
- Guest users see Sign In/Get Started buttons
- Authenticated users see their Dashboard button
- Uses Bootstrap classes & custom `ph-` utility classes

### 📊 Models Used
- `Category` - For categories display
- `PerformerProfile` - For featured performers

---

## 2. LOGIN PAGE

### 📍 Route
```
GET /login → AuthController@showLogin → auth/login.blade.php
POST /login → AuthController@login
```

### 🎮 Controller: `AuthController`
**File:** `app/Http/Controllers/Auth/AuthController.php`

```php
public function showLogin(Request $request): View
{
    return view('auth.login');
}

public function login(Request $request): RedirectResponse
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account has been deactivated.'])->onlyInput('email');
        }

        // Redirect to user's dashboard based on their role
        return redirect()->intended($user->dashboardRoute());
    }

    return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
}
```

**Validation Rules:**
- Email: required, must be valid email format
- Password: required (minimum 8 chars enforced at registration)

**Logic:**
1. Validates email and password
2. Attempts authentication
3. Checks if account is active
4. Redirects to appropriate dashboard (performer, organizer, or admin)
5. Falls back with error message if credentials invalid

### 🎨 View: `resources/views/auth/login.blade.php`

**Structure:**
```
├── Split Layout (Hero + Form)
│   ├── Left Side (Large screens only)
│   │   ├── Logo & "Welcome back to the stage."
│   │   └── Tagline
│   │
│   └── Right Side (Form Panel)
│       ├── Back to Home link
│       ├── Form Title & Description
│       ├── Error Messages (if any)
│       ├── Login Form
│       │   ├── Email Input
│       │   ├── Password Input
│       │   └── Remember Me Checkbox
│       ├── Submit Button
│       └── Link to Register
```

**Key Features:**
- Responsive split layout
- Shows error messages if login fails
- "Remember Me" option for persistent login
- Link to register page for new users
- Back to home link
- Simple, clean design

### 🔐 Security
- CSRF token required (via `@csrf`)
- Session regeneration after login
- Account deactivation check
- Password never shown in response

---

## 3. REGISTER PAGE

### 📍 Route
```
GET /register?role={performer|organizer} → AuthController@showRegister → auth/register.blade.php
POST /register → AuthController@register
```

### 🎮 Controller: `AuthController`
**File:** `app/Http/Controllers/Auth/AuthController.php`

```php
public function showRegister(Request $request): View
{
    $role = $request->query('role', 'performer');
    return view('auth.register', compact('role'));
}

public function register(Request $request): RedirectResponse
{
    // Generate username from input or auto-create from name
    $username = Str::slug(trim((string) $request->input('username', '')), '_');
    if ($username === '') {
        $username = Str::slug(
            trim($request->input('first_name', '').' '.$request->input('last_name', '')),
            '_'
        );
    }
    $request->merge(['username' => $username]);

    // Validate all fields
    $validated = $request->validate([
        'first_name' => ['required', 'string', 'max:100'],
        'last_name' => ['required', 'string', 'max:100'],
        'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'confirmed', Password::min(8)],
        'role' => ['required', 'in:performer,organizer'],
        'terms_accepted' => ['accepted'],
    ]);

    // Create user
    $user = User::create([
        'first_name' => $validated['first_name'],
        'last_name' => $validated['last_name'],
        'username' => $validated['username'],
        'email' => $validated['email'],
        'password' => $validated['password'],
        'role' => $validated['role'],
        'is_verified' => false,
        'is_active' => true,
        'onboarding_step' => User::ONBOARDING_REGISTERED,
    ]);

    // Create corresponding profile
    if ($user->isPerformer()) {
        PerformerProfile::create([
            'user_id' => $user->id,
            'stage_name' => $user->fullName(),
        ]);
    } else {
        OrganizerProfile::create([
            'user_id' => $user->id,
            'organization_name' => $user->fullName(),
        ]);
    }

    // Auto-login
    Auth::login($user);

    // Notify admins about new registration
    $admins = User::where('role', User::ROLE_ADMIN)->get();
    $type = 'user.registered';
    $title = $user->isOrganizer() ? 'New Organizer Registered' : 'New Performer Registered';
    $message = sprintf('%s: %s', $title, $user->fullName());
    $link = route('admin.users.index');

    if ($admins->isEmpty()) {
        Notification::send($user, $type, $title, $message, $link);
    } else {
        foreach ($admins as $admin) {
            Notification::send($admin, $type, $title, $message, $link);
        }
    }

    return redirect($user->dashboardRoute())
        ->with('success', 'Welcome to PerformHub! Your account is ready — complete sign-up anytime to unlock all features.');
}
```

**Validation Rules:**
```php
'first_name' => ['required', 'string', 'max:100']
'last_name' => ['required', 'string', 'max:100']
'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username']
'email' => ['required', 'email', 'max:255', 'unique:users,email']
'password' => ['required', 'confirmed', Password::min(8)]
'role' => ['required', 'in:performer,organizer']
'terms_accepted' => ['accepted']
```

**Custom Error Messages:**
- Username with spaces auto-converts (underscores used)
- Username must be unique
- Email must be unique
- Terms must be accepted
- Passwords must match
- Password minimum 8 characters

**Post-Registration:**
1. User created with `is_verified: false`, `is_active: true`
2. Corresponding profile created (Performer or Organizer)
3. User auto-logged in
4. Admins notified via notification system
5. Redirect to dashboard with success message

### 🎨 View: `resources/views/auth/register.blade.php`

**Structure:**
```
├── Split Layout (Hero + Form)
│   ├── Left Side (Large screens only)
│   │   ├── Logo & "Join the stage!"
│   │   └── Tagline
│   │
│   └── Right Side (Form Panel)
│       ├── Back to Home link
│       ├── Form Title & Description
│       ├── Error Messages (if any)
│       │
│       ├── Stage 1: Terms & Conditions
│       │   ├── Large scrollable terms section
│       │   ├── Terms & Conditions text (7 sections)
│       │   ├── Disclaimer section
│       │   ├── Data Privacy (DPA 2012 compliance)
│       │   ├── Checkbox to agree
│       │   └── Continue to Registration button (disabled until checked)
│       │
│       └── Stage 2: Registration Form
│           ├── Hidden role input (set from query param)
│           ├── First Name input
│           ├── Last Name input
│           ├── Username input (optional - auto-generated)
│           ├── Email input
│           ├── Password input
│           ├── Confirm Password input
│           └── Submit button
```

**Key Features:**
- Two-step registration process
- Step 1: Terms & Conditions must be read and accepted
- Step 2: Personal information
- Username auto-generated from first+last name if not provided
- Spaces in username converted to underscores
- Responsive design
- Show/hide password toggle

### 📊 Models Created
- `User` - Main user account
- `PerformerProfile` - If role is 'performer'
- `OrganizerProfile` - If role is 'organizer'
- `Notification` - Sent to admins

### 📋 Terms & Conditions Content
The register page includes comprehensive terms covering:
1. **Acceptance of Terms** - Agreement to comply
2. **User Responsibilities** - Accurate info & security
3. **Organizer Responsibilities** - Event accuracy, professionalism
4. **Performer Responsibilities** - Profile honesty, fulfillment
5. **Prohibited Activities** - False info, harassment, fraud
6. **Account Suspension** - Right to ban violators
7. **Changes to Terms** - Future updates

**Disclaimer Section:**
- Platform acts as facilitator only
- No guarantee on performance quality
- No liability for cancellations, disputes, damages
- Data processing under Data Privacy Act of 2012 (PH)

---

## 4. ADMIN DASHBOARD

### 📍 Route
```
GET /admin/dashboard → AdminDashboardController@index → admin/dashboard.blade.php
```
**Middleware:** `auth`, `role:admin`

### 🎮 Controller: `AdminDashboardController`
**File:** `app/Http/Controllers/Admin/DashboardController.php`

```php
public function index(): View
{
    $stats = [
        'users' => User::count(),
        'performers' => User::where('role', 'performer')->count(),
        'organizers' => User::where('role', 'organizer')->count(),
        'bookings' => Booking::count(),
        'pending_verifications' => User::where('is_verified', false)
            ->whereIn('role', ['performer', 'organizer'])
            ->count(),
    ];

    $recentBookings = Booking::with(['organizer', 'performer'])->latest()->limit(5)->get();

    $organizersForFilter = User::where('role', 'organizer')->orderBy('first_name')->get();

    return view('admin.dashboard', compact('stats', 'recentBookings', 'organizersForFilter'));
}
```

**Data Provided:**
1. **Statistics:**
   - Total users
   - Number of performers
   - Number of organizers
   - Total bookings
   - Pending verifications (unverified performers/organizers)

2. **Recent Bookings:** Last 5 bookings with organizer & performer info

### 🎨 View: `resources/views/admin/dashboard.blade.php`

**Structure:**
```
├── Page Title
├── Statistics Cards (5 cards)
│   ├── Total Users
│   ├── Performers Count
│   ├── Organizers Count
│   ├── Bookings Count
│   └── Pending Verifications (highlighted)
│
└── Recent Bookings Section
    ├── Section Header
    ├── View All Bookings Link
    └── List of Last 5 Bookings
        ├── Event name
        ├── Organizer & Performer names
        └── Status badge
```

**Key Features:**
- Quick overview statistics
- Color-coded badges for booking status
- Link to view all bookings in monitoring
- Clean card-based layout
- Links to detailed pages for exploration

### 📊 Models Used
- `User` - For counting users by role
- `Booking` - For statistics and recent bookings

---

## 5. ADMIN USER MANAGEMENT

### 📍 Routes
```
GET /admin/users → AdminUserController@index → admin/users/index.blade.php
GET /admin/users/{user} → AdminUserController@show → admin/users/show.blade.php
POST /admin/users/{user}/verify → AdminUserController@verify
POST /admin/users/{user}/toggle → AdminUserController@toggleActive
GET /admin/users/all → AdminUserController@all → admin/users/all.blade.php
```
**Middleware:** `auth`, `role:admin`

### 🎮 Controller: `AdminUserController`
**File:** `app/Http/Controllers/Admin/UserController.php`

#### Method 1: `index()` - List users with filtering
```php
public function index(Request $request): View
{
    $query = User::query()->whereIn('role', ['performer', 'organizer']);

    // Filter by role
    if ($request->filled('role')) {
        $query->where('role', $request->role);
    }

    // Filter by status
    if ($request->filled('status')) {
        $query->where('is_active', $request->status === 'active');
    }

    $users = $query
        ->with(['performerProfile', 'organizerProfile'])
        ->orderBy('first_name')
        ->orderBy('last_name')
        ->paginate(15);  // 15 users per page

    return view('admin.users.index', compact('users'));
}
```

**Filtering Options:**
- **Role:** All, Performer, Organizer
- **Status:** All, Active, Inactive

**Sorting:** By first name, then last name (ascending)

#### Method 2: `show()` - View user details
```php
public function show(User $user): View
{
    abort_unless(in_array($user->role, ['performer', 'organizer']), 404);

    $user->load(['performerProfile', 'organizerProfile', 'verificationDocuments']);

    return view('admin.users.show', compact('user'));
}
```

**Displays:**
- Basic user details
- Role-specific profile info
- Verification documents
- Account status

#### Method 3: `verify()` - Mark user as verified
```php
public function verify(User $user): RedirectResponse
{
    abort_unless(in_array($user->role, ['performer', 'organizer']), 400);

    $user->update(['is_verified' => true]);

    if ($user->isPerformer() && $user->performerProfile) {
        $user->performerProfile->update(['is_verified_badge' => true]);
    }

    return back()->with('success', 'Account verified successfully.');
}
```

**Effects:**
- Sets `User.is_verified = true`
- If performer: also sets `PerformerProfile.is_verified_badge = true`

#### Method 4: `toggleActive()` - Suspend/Activate account
```php
public function toggleActive(User $user): RedirectResponse
{
    abort_unless($user->role !== 'admin', 400);

    $user->update(['is_active' => ! $user->is_active]);

    return back()->with('success', 'Account status updated.');
}
```

**Effects:**
- Toggles `User.is_active` boolean
- Cannot toggle admin accounts
- Users must be active to login

#### Method 5: `all()` - Simple user list
```php
public function all(): View
{
    $users = User::orderBy('first_name')->orderBy('last_name')->get();
    return view('admin.users.all', compact('users'));
}
```

**Returns:** All users (name + role only)

### 🎨 View 1: `admin/users/index.blade.php` - User List

**Structure:**
```
├── Page Title: "User Management"
│
├── Filter Panel
│   ├── Role Dropdown (All, Performer, Organizer)
│   ├── Status Dropdown (All, Active, Inactive)
│   └── Filter Button
│
└── Users Table (Paginated - 15 per page)
    ├── Headers: Name | Role | Verified | Status | Actions
    ├── Rows:
    │   ├── Full Name
    │   ├── Role Badge (gray)
    │   ├── Verified Badge (blue if yes, warning if no)
    │   ├── Status Badge (green if active, red if suspended)
    │   └── View Button (links to show page)
    │
    └── Pagination Controls
```

**Key Features:**
- Real-time filtering by role & status
- Color-coded badges
- Pagination for large user lists
- Quick view access to each user

### 🎨 View 2: `admin/users/show.blade.php` - User Details

**Structure:**
```
├── Header
│   ├── Page Title: "User Preview"
│   └── Back to Users Button
│
├── Basic Details Card
│   ├── Name
│   ├── Username
│   ├── Email
│   ├── Role
│   ├── Status (Active/Suspended)
│   ├── Verified Status
│   └── Onboarding Step
│
├── Profile Info Card
│   │
│   ├── If Performer Profile:
│   │   ├── Stage Name
│   │   ├── Genre
│   │   └── Location
│   │
│   └── If Organizer Profile:
│       ├── Organization Name
│       ├── Organization Type
│       └── Location
│
├── Verification Documents Card
│   ├── Document List
│   │   ├── Document Type
│   │   ├── Upload Date
│   │   ├── Status Badge
│   │   └── Document Preview/Link
│   │
│   └── Action Buttons
│       ├── Approve Button (if pending)
│       └── Reject Button (if pending)
│
└── Account Actions (Floating/Sticky)
    ├── Verify Account Button (if not verified)
    ├── Suspend/Activate Account Button
    └── Back to Users Link
```

**Key Features:**
- Comprehensive user profile view
- Role-specific information
- Verification document preview
- Quick action buttons for admin operations
- Status indicators

### 📊 Models Used
- `User` - Main user model
- `PerformerProfile` - For performer-specific info
- `OrganizerProfile` - For organizer-specific info
- `VerificationDocument` - For document verification

### 🔐 Security & Validation

**Access Control:**
- Must be authenticated and have `admin` role
- Can only view/manage performer & organizer accounts
- Cannot modify admin accounts

**Validation:**
- Role must be 'performer' or 'organizer' (not admin)
- All data validated before database update
- Admin cannot toggle their own account

---

## Database Tables Reference

### `users` Table
```php
id
first_name
last_name
username (unique)
email (unique)
password (hashed)
role (performer|organizer|admin)
is_verified (boolean)
is_active (boolean)
onboarding_step
created_at
updated_at
```

### `performer_profiles` Table
```php
id
user_id (foreign key → users.id)
stage_name
genre
rate
bio
location
is_verified_badge
created_at
updated_at
```

### `organizer_profiles` Table
```php
id
user_id (foreign key → users.id)
organization_name
organization_type
location
created_at
updated_at
```

### `verification_documents` Table
```php
id
user_id
document_type
document_url
status (pending|approved|rejected)
uploaded_at
reviewed_at
created_at
updated_at
```

### `bookings` Table
```php
id
organizer_id
performer_id
event_name
event_date
status
contract_url
created_at
updated_at
```

### `categories` Table
```php
id
name
description
icon
is_active (boolean)
created_at
updated_at
```

### `notifications` Table
```php
id
user_id
type
title
message
link
read_at
created_at
```

---

## Common Authentication Flow

### Guest → Authenticated User

1. **Homepage** (Landing page)
   - Guest sees "Sign In" & "Get Started" buttons
   - Authenticated users see dashboard button

2. **Register** (Two-step process)
   - Accept terms & conditions
   - Enter personal information
   - Select role (performer or organizer)
   - Auto-login after successful registration
   - Redirect to onboarding

3. **Login**
   - Enter email & password
   - Account must be active
   - Redirect to role-specific dashboard

4. **Dashboard Routing**
   ```php
   // In User model
   public function dashboardRoute()
   {
       return match($this->role) {
           'admin' => 'admin.dashboard',
           'performer' => 'performer.dashboard',
           'organizer' => 'organizer.dashboard',
       };
   }
   ```

---

## Styling & Layout

### Layout Files
- `layouts/guest.blade.php` - For auth & landing pages
- `layouts/app.blade.php` - For authenticated pages (includes sidebar)

### CSS Classes
- `ph-card` - Card container
- `ph-btn-primary` - Primary button (brand color)
- `ph-btn-outline` - Outline button
- `ph-input` - Styled input field

### Theme
- Primary Color: `#6346ff` (purple)
- Uses Bootstrap 5
- Dark theme with white text
- Responsive grid system

---

## Key Takeaways for Study

1. **Homepage** is public, shows categories & featured performers
2. **Login** validates credentials and checks account status
3. **Register** creates user + profile, notifies admins, auto-logs in
4. **Admin Dashboard** shows quick statistics and recent activity
5. **User Management** allows filtering, verification, and suspension

---

## Related Admin Pages (Your Colleagues Handle These)

- **Categories Management** - CRUD operations on categories
- **Event Types Management** - Create/edit/delete event types
- **Monitoring** - View all bookings and events
- **Admin Sidebar** - Navigation menu for all admin sections

---

## Helpful Commands

```bash
# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Create admin user (for testing)
php artisan tinker
# Then: User::create([...])

# Clear cache
php artisan cache:clear

# Run tests
php artisan test
```

---

**Good luck with your studies! Feel free to explore the models and controllers to understand the relationships better.**
