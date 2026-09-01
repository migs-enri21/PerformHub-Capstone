# Complete File Tracing - Admin, Homepage, Login & Register Pages

## 📁 Directory Structure Overview

```
PerformHub-Capstone/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php                    [Homepage]
│   │   │   ├── Auth/
│   │   │   │   ├── AuthController.php                [Login & Register]
│   │   │   │   └── OnboardingController.php          [Post-Register]
│   │   │   └── Admin/
│   │   │       ├── DashboardController.php           [Admin Dashboard]
│   │   │       ├── UserController.php                [User Management]
│   │   │       ├── CategoryController.php            [Categories - Related]
│   │   │       ├── EventTypeController.php           [Event Types - Related]
│   │   │       ├── EventController.php               [Events - Related]
│   │   │       └── MonitoringController.php          [Monitoring - Related]
│   │   └── Middleware/
│   │       ├── RoleMiddleware.php                    [Role Check]
│   │       └── EnsureFullAccess.php                  [Access Control]
│   ├── Models/
│   │   ├── User.php                                  [Core User Model]
│   │   ├── PerformerProfile.php                      [Performer Profile]
│   │   ├── OrganizerProfile.php                      [Organizer Profile]
│   │   ├── Category.php                              [Categories]
│   │   ├── Booking.php                               [Bookings]
│   │   ├── VerificationDocument.php                  [Verification]
│   │   ├── Notification.php                          [Notifications]
│   │   └── EventType.php                             [Event Types]
│   └── Support/
│       └── PhilippineLocations.php                   [Location Data]
│
├── resources/
│   ├── views/
│   │   ├── landing.blade.php                         [Homepage]
│   │   ├── layouts/
│   │   │   ├── guest.blade.php                       [Guest Layout - Login/Register]
│   │   │   └── app.blade.php                         [Auth Layout - Admin/Dashboard]
│   │   ├── auth/
│   │   │   ├── login.blade.php                       [Login Form]
│   │   │   └── register.blade.php                    [Register Form]
│   │   ├── admin/
│   │   │   ├── dashboard.blade.php                   [Admin Dashboard]
│   │   │   ├── users/
│   │   │   │   ├── index.blade.php                   [User List]
│   │   │   │   ├── show.blade.php                    [User Details]
│   │   │   │   └── all.blade.php                     [All Users List]
│   │   │   ├── partials/
│   │   │   │   └── sidebar.blade.php                 [Admin Sidebar]
│   │   │   ├── categories/                           [Categories Views - Related]
│   │   │   ├── event-types/                          [Event Types Views - Related]
│   │   │   ├── events/                               [Events Views - Related]
│   │   │   └── monitoring/                           [Monitoring Views - Related]
│   │   └── partials/
│   │       ├── stylesheets.blade.php                 [CSS Loader]
│   │       ├── nav-profile-avatar.blade.php          [Profile Nav]
│   │       ├── onboarding-banner.blade.php           [Onboarding Banner]
│   │       └── role-sidebar.blade.php                [Role-based Sidebar]
│   ├── css/
│   │   ├── performhub-base.css                       [Base Styles]
│   │   ├── performhub-light.css                      [Light Styles]
│   │   ├── admin.css                                 [Admin Styles]
│   │   ├── performer.css                             [Performer Styles]
│   │   └── organizer.css                             [Organizer Styles]
│   └── js/
│       └── [Frontend JavaScript - if any]
│
├── routes/
│   └── web.php                                       [All Routes]
│
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_06_29_000001_create_performhub_tables.php
│   │   └── [Other migrations]
│   └── seeders/
│       └── UserFactory.php                           [User Factory for Testing]
│
├── bootstrap/
│   └── app.php                                       [App Configuration]
│
└── public/
    └── css/
        ├── performhub-base.css
        ├── admin.css
        ├── performer.css
        └── organizer.css
```

---

## 🏠 PAGE 1: HOMEPAGE (/landing)

### Route Definition
**File:** `routes/web.php` (Lines 33)
```php
Route::get('/', [HomeController::class, 'index'])->name('home');
```

### Controller Files
1. **`app/Http/Controllers/HomeController.php`**
   - Method: `index()` → View: `landing.blade.php`
   - Fetches active categories
   - Fetches featured performers (latest 6)
   - Loads related models

### View Files
1. **`resources/views/landing.blade.php`** (Main View)
   - Extends: `layouts.guest`
   - Sections: navbar, hero, categories, featured performers, how-it-works, why-choose, final CTA

### Layout File
1. **`resources/views/layouts/guest.blade.php`**
   - Used for public pages (homepage, login, register)
   - Includes: CSS, JS, navbar template
   - Yields: title, content

### Model Files Used
1. **`app/Models/Category.php`**
   - Relationship: hasMany → EventType
   - Fields: id, name, description, icon, is_active, slug
   - Used for: Browse by Category section

2. **`app/Models/PerformerProfile.php`**
   - Relationship: belongsTo → User, hasMany → Category
   - Fields: id, user_id, stage_name, genre, rate, bio, location, is_verified_badge
   - Used for: Featured Performers section

3. **`app/Models/User.php`**
   - Relationship: hasOne → PerformerProfile, hasOne → OrganizerProfile
   - Fields: id, first_name, last_name, username, email, password, role, is_active, is_verified
   - Used for: Auth check, featured performers

### CSS Files
1. **`public/css/performhub-base.css`** - Base styling
2. **`public/css/performhub-light.css`** - Light theme
3. **`public/css/organizer.css`** - Default guest styling

### Partial Files
1. **`resources/views/partials/stylesheets.blade.php`**
   - Dynamically loads CSS based on user role/auth status

### Dependencies
- Bootstrap 5.3.3 (CDN)
- Font Awesome 6.5.2 (CDN)
- Google Fonts (Inter)

---

## 🔐 PAGE 2: LOGIN PAGE (/login)

### Route Definitions
**File:** `routes/web.php` (Lines 37-38)
```php
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
```
**Middleware:** `guest` (only unauthenticated users)

### Controller Files
1. **`app/Http/Controllers/Auth/AuthController.php`**
   - Methods:
     - `showLogin()` → View: `auth.login`
     - `login()` → POST handler, validates & authenticates

### View Files
1. **`resources/views/auth/login.blade.php`** (Main View)
   - Extends: `layouts.guest`
   - Template: Split layout (hero + form)
   - Elements: email input, password input, remember me checkbox, submit button

### Layout File
1. **`resources/views/layouts/guest.blade.php`**
   - Parent layout for login page

### Model Files Used
1. **`app/Models/User.php`**
   - Methods: attempt(), findByEmail(), dashboardRoute()
   - Auth validation happens here

### CSS Files
1. **`public/css/performhub-base.css`**
2. **`public/css/performhub-light.css`**
3. **`public/css/organizer.css`** (default for guests)

### Middleware Files
1. **`app/Http/Middleware/RoleMiddleware.php`**
   - Used after login to check user role

### Bootstrap Files
1. **`bootstrap/app.php`**
   - Configures: redirectGuestsTo('login'), redirectUsersTo(dashboardRoute)
   - Defines middleware aliases

### Dependencies
- Laravel Authentication (built-in)
- Bootstrap 5.3.3
- Font Awesome 6.5.2

---

## 📝 PAGE 3: REGISTER PAGE (/register)

### Route Definitions
**File:** `routes/web.php` (Lines 39-40)
```php
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
```
**Middleware:** `guest` (only unauthenticated users)

### Controller Files
1. **`app/Http/Controllers/Auth/AuthController.php`**
   - Methods:
     - `showRegister()` → View: `auth.register`
     - `register()` → POST handler, creates user & profile

2. **`app/Http/Controllers/Auth/OnboardingController.php`** (Related)
   - Used after registration for onboarding steps
   - Methods: index(), showRole(), storeRole(), etc.

### View Files
1. **`resources/views/auth/register.blade.php`** (Main View)
   - Extends: `layouts.guest`
   - Two-stage form:
     - Stage 1: Terms & Conditions acceptance
     - Stage 2: Personal information (name, email, password, role)
   - JavaScript: Toggle between stages

### Layout File
1. **`resources/views/layouts/guest.blade.php`**
   - Parent layout for register page

### Model Files Used
1. **`app/Models/User.php`**
   - Methods: create(), isPerformer(), isOrganizer()
   - Stores: first_name, last_name, username, email, password (hashed), role, is_verified, is_active, onboarding_step

2. **`app/Models/PerformerProfile.php`**
   - Created if user role is 'performer'
   - Stores: user_id, stage_name (auto-filled with full name)

3. **`app/Models/OrganizerProfile.php`**
   - Created if user role is 'organizer'
   - Stores: user_id, organization_name (auto-filled with full name)

4. **`app/Models/Notification.php`**
   - Sends notification to all admins about new registration
   - Methods: send()

### CSS Files
1. **`public/css/performhub-base.css`**
2. **`public/css/performhub-light.css`**
3. **`public/css/organizer.css`** (default for guests)

### JavaScript Files
- Inline in `register.blade.php`:
  - Stage toggle functionality
  - Form validation
  - Terms checkbox enablement

### Dependencies
- Laravel Validation (built-in)
- Illuminate\Support\Str (for username generation)
- Bootstrap 5.3.3
- Font Awesome 6.5.2

### Data Flow
Register → Create User → Create Profile → Send Notification → Auto-login → Redirect to Onboarding

---

## 👨‍💼 PAGE 4: ADMIN DASHBOARD & USER MANAGEMENT

### Route Definitions
**File:** `routes/web.php` (Lines 116-140)
```php
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/verify', [AdminUserController::class, 'verify'])->name('users.verify');
    Route::post('/users/{user}/toggle', [AdminUserController::class, 'toggleActive'])->name('users.toggle');
    Route::get('/users/all', [AdminUserController::class, 'all'])->name('users.all');
    // ... other admin routes
});
```
**Middleware:** `auth`, `role:admin`

### Controller Files
1. **`app/Http/Controllers/Admin/DashboardController.php`**
   - Method: `index()` → View: `admin.dashboard`
   - Fetches: total users, performers, organizers, bookings, pending verifications
   - Fetches: last 5 bookings

2. **`app/Http/Controllers/Admin/UserController.php`**
   - Methods:
     - `index()` → List users (paginated, filterable)
     - `show()` → Display user details
     - `verify()` → Verify user account
     - `toggleActive()` → Suspend/activate account
     - `all()` → Simple user list (name + role)

### View Files

#### Dashboard Views
1. **`resources/views/admin/dashboard.blade.php`** (Main Dashboard)
   - Extends: `layouts.app`
   - Includes sidebar: `admin.partials.sidebar`
   - Displays: Statistics cards, recent bookings list

2. **`resources/views/admin/partials/sidebar.blade.php`** (Admin Sidebar)
   - Navigation: Dashboard, Users, Categories, Event Types, Monitoring
   - Profile section: Avatar, name, role
   - Logout form

#### User Management Views
1. **`resources/views/admin/users/index.blade.php`** (User List)
   - Extends: `layouts.app`
   - Includes sidebar: `admin.partials.sidebar`
   - Filter form: role dropdown, status dropdown
   - Paginated table: name, role badge, verified badge, status badge, actions
   - Pagination controls

2. **`resources/views/admin/users/show.blade.php`** (User Details)
   - Extends: `layouts.app`
   - Includes sidebar: `admin.partials.sidebar`
   - Back button to user list
   - Basic Details Card: name, username, email, role, status, verified, onboarding
   - Profile Info Card: role-specific info (performer or organizer)
   - Verification Documents Card: list of uploaded docs with approval buttons
   - Action Buttons: Verify, Suspend/Activate

3. **`resources/views/admin/users/all.blade.php`** (All Users Simple List)
   - Simple list: name + role
   - No filtering/pagination

### Layout File
1. **`resources/views/layouts/app.blade.php`**
   - Used for authenticated pages (admin, performer, organizer)
   - Includes: navbar, sidebar (via @section), CSS loader
   - Yields: title, sidebar, content

### Partial Files
1. **`resources/views/partials/stylesheets.blade.php`**
   - Loads admin.css for admin users
   - Dynamically loads role-specific CSS

2. **`resources/views/admin/partials/sidebar.blade.php`**
   - Admin navigation menu
   - Profile section

### Model Files Used
1. **`app/Models/User.php`**
   - Methods: where(), orderBy(), paginate()
   - Relationships: hasOne → PerformerProfile, hasOne → OrganizerProfile
   - Scopes: active, verified, by role
   - Fields displayed in admin

2. **`app/Models/PerformerProfile.php`**
   - Relationship: belongsTo → User
   - Fields: stage_name, genre, location, is_verified_badge
   - Used in: User details view

3. **`app/Models/OrganizerProfile.php`**
   - Relationship: belongsTo → User
   - Fields: organization_name, organization_type, location
   - Used in: User details view

4. **`app/Models/Booking.php`**
   - Relationship: belongsTo → User (organizer), belongsTo → User (performer)
   - Fields: id, organizer_id, performer_id, event_name, status, created_at
   - Used in: Dashboard recent bookings

5. **`app/Models/VerificationDocument.php`**
   - Relationship: belongsTo → User
   - Fields: id, user_id, document_type, document_url, status, reviewed_at
   - Used in: User details verification docs section

6. **`app/Models/Notification.php`**
   - Used for: Sending admin notifications on new registrations

### CSS Files
1. **`public/css/performhub-base.css`**
2. **`public/css/performhub-light.css`**
3. **`public/css/admin.css`** (Admin-specific styles)

### Middleware Files
1. **`app/Http/Middleware/RoleMiddleware.php`**
   - Checks: user.role === 'admin'
   - Prevents access if not admin

2. **`app/Http/Middleware/EnsureFullAccess.php`**
   - Checks: user is fully onboarded
   - Prevents access if incomplete

### Bootstrap Files
1. **`bootstrap/app.php`**
   - Middleware configuration
   - Role alias definition

### Dependencies
- Laravel Eloquent (ORM)
- Laravel Validation
- Bootstrap 5.3.3
- Font Awesome 6.5.2

---

## 🔗 RELATED PAGES (Not Your Primary Responsibility)

### Admin-Related Views (Handled by Others)
1. **`resources/views/admin/categories/`** - Category management
2. **`resources/views/admin/event-types/`** - Event type management
3. **`resources/views/admin/events/`** - Event management
4. **`resources/views/admin/monitoring/`** - Booking/event monitoring

### Controllers (Related to Admin)
1. **`app/Http/Controllers/Admin/CategoryController.php`**
2. **`app/Http/Controllers/Admin/EventTypeController.php`**
3. **`app/Http/Controllers/Admin/EventController.php`**
4. **`app/Http/Controllers/Admin/MonitoringController.php`**

---

## 📊 Database Tables Involved

### Primary Tables
1. **users**
   - Columns: id, first_name, last_name, username, email, password, role, is_verified, is_active, onboarding_step, created_at, updated_at

2. **performer_profiles**
   - Columns: id, user_id, stage_name, genre, rate, bio, location, is_verified_badge, created_at, updated_at

3. **organizer_profiles**
   - Columns: id, user_id, organization_name, organization_type, location, created_at, updated_at

4. **verification_documents**
   - Columns: id, user_id, document_type, document_url, status, uploaded_at, reviewed_at, created_at, updated_at

5. **bookings**
   - Columns: id, organizer_id, performer_id, event_name, event_date, status, contract_url, created_at, updated_at

6. **categories**
   - Columns: id, name, description, icon, is_active, slug, created_at, updated_at

7. **notifications**
   - Columns: id, user_id, type, title, message, link, read_at, created_at, updated_at

---

## 🔐 Authentication & Authorization

### Auth Flow
1. **Guest → Login** (via middleware 'guest')
2. **Login → Dashboard** (via middleware 'auth', 'role:admin|performer|organizer')
3. **Logout** (via logout route + POST)

### Middleware Stack
- `auth` - Checks if user is authenticated
- `guest` - Checks if user is NOT authenticated
- `role:admin` - Checks if user role is 'admin'
- `role:performer` - Checks if user role is 'performer'
- `role:organizer` - Checks if user role is 'organizer'

### Route Groups
```php
// Public routes
GET  /              → HomeController@index
GET  /login         → AuthController@showLogin
POST /login         → AuthController@login
GET  /register      → AuthController@showRegister
POST /register      → AuthController@register
POST /logout        → AuthController@logout (auth middleware)

// Admin routes
GET  /admin/dashboard              → AdminDashboardController@index
GET  /admin/users                  → AdminUserController@index
GET  /admin/users/{user}           → AdminUserController@show
POST /admin/users/{user}/verify    → AdminUserController@verify
POST /admin/users/{user}/toggle    → AdminUserController@toggleActive
GET  /admin/users/all              → AdminUserController@all
```

---

## 🎯 Key Features Summary

### Homepage
- ✅ Display active categories
- ✅ Display featured performers (latest 6)
- ✅ Show auth status in navbar
- ✅ CTAs for registration/login

### Login
- ✅ Email + password authentication
- ✅ Remember me checkbox
- ✅ Account status check (is_active)
- ✅ Dashboard redirect based on role
- ✅ Error message display

### Register
- ✅ Two-stage form (T&C → Form)
- ✅ Terms & conditions acceptance
- ✅ Username auto-generation
- ✅ Create User + Profile
- ✅ Send admin notification
- ✅ Auto-login
- ✅ Redirect to onboarding

### Admin Dashboard
- ✅ Display statistics (5 cards)
- ✅ Show recent bookings (5 latest)
- ✅ Links to other admin pages

### User Management
- ✅ Paginated user list (15 per page)
- ✅ Filter by role & status
- ✅ View user details
- ✅ View verification documents
- ✅ Verify accounts
- ✅ Suspend/activate accounts

---

## 📝 Important Notes

### User Model Locations
- **Definition:** `app/Models/User.php`
- **Factory:** `database/factories/UserFactory.php`

### Migration Files
- **Create Users:** `database/migrations/0001_01_01_000000_create_users_table.php`
- **Create Main Tables:** `database/migrations/2026_06_29_000001_create_performhub_tables.php`
- **Add Onboarding:** `database/migrations/2026_06_29_100000_add_onboarding_fields.php`

### Config Files
- **App Config:** `config/app.php`
- **Auth Config:** `config/auth.php`
- **Database Config:** `config/database.php`

### Environment
- **Default:** `.env` file (MySQL on port 3306)
- **Docker:** `docker-compose.yml` (optional, port 3307)

---

## 🚀 Quick Start Commands

```bash
# Create fresh database
php artisan migrate:fresh

# Seed database
php artisan db:seed

# Create test user
php artisan tinker
> User::create([...])

# Run development server
php artisan serve

# Clear caches
php artisan cache:clear
php artisan view:clear
```

---

**This guide traces all files involved in your assigned pages. Use this to understand the complete flow and dependencies!**
