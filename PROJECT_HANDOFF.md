# PerformHub — Project Handoff

Use this file when opening the project in a new Cursor window.

---

## Project Location

```
C:\Users\USER\Documents\performhub
```

**Do not use XAMPP.** Use WinGet PHP + standalone MySQL 8.0.

---

## Tech Stack

| Item | Details |
|------|---------|
| Framework | Laravel 12 (PHP 8.2; Laravel 13 needs PHP 8.3+) |
| Database | MySQL 8.0 (`MySQL80` Windows service) |
| Frontend | Bootstrap 5, Font Awesome, custom dark theme (`public/css/performhub.css`) |
| Auth | Custom multi-role auth (performer, organizer, admin) |
| Interviews | Jitsi Meet API (`meet.jit.si`) |
| PHP | WinGet PHP 8.2 — **not** `C:\xampp\php` |

---

## Quick Start

```powershell
# Use WinGet PHP (prepend to PATH so XAMPP PHP is not used)
$env:PATH = "C:\Users\USER\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe;C:\composer;" + ($env:PATH -replace 'C:\\xampp\\php;?','')

cd C:\Users\USER\Documents\performhub
php artisan serve
```

Open: **http://127.0.0.1:8000**

### If database is missing

```powershell
& "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -u root -proot -e "CREATE DATABASE IF NOT EXISTS performhub; CREATE USER IF NOT EXISTS 'performhub'@'localhost' IDENTIFIED BY 'performhub_secret'; GRANT ALL PRIVILEGES ON performhub.* TO 'performhub'@'localhost'; FLUSH PRIVILEGES;"

php artisan migrate:fresh --seed
php artisan storage:link
```

### PHP MySQL extension

If you see `could not find driver`, enable in WinGet `php.ini`:

```
extension=mysqli
extension=pdo_mysql
```

Path: `C:\Users\USER\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe\php.ini`

---

## Demo Accounts

| Role | Email | Password | Login role selector |
|------|-------|----------|---------------------|
| Admin | admin@performhub.test | password | Admin |
| Performer | performer@performhub.test | password | Performer |
| Organizer | organizer@performhub.test | password | Organizer |

---

## What Was Built (Capstone 1 MVP)

### Authentication
- Login page (split-screen, dark theme, purple accents — matches mockup)
- Register with role selection (performer / organizer)
- Role-based middleware + dashboard redirects

### Landing Page (`/`)
- Hero, browse by category, featured performers
- How it works, testimonials, why choose, CTA

### Performer (`/performer/*`)
- Dashboard, profile (photo, bio, genre, category, rate, location, social links)
- Portfolio upload (photos/videos)
- Availability calendar
- Booking requests: accept / reject / history
- Contract confirmation
- Verification badge (set by admin)

### Organizer (`/organizer/*`)
- Dashboard with performer recommendations (no ratings yet)
- Profile management
- Search/filter performers (category, genre, rating, availability date)
- Browse performer profiles
- Send booking requests, upload contracts
- Schedule Jitsi interviews
- Booking history

### Admin (`/admin/*`)
- Dashboard statistics
- Verify performer/organizer accounts
- Suspend/activate users
- Category management
- Monitor bookings and interviews

### Shared
- Messaging between users
- Notifications (booking, interview, contract, message)
- Booking workflow: `pending → interview_scheduled → accepted → completed` or `rejected`

---

## Database Tables

- `users` (role, username, is_verified, is_active)
- `performer_profiles`
- `organizer_profiles`
- `portfolios`
- `availability_schedules`
- `bookings`
- `interviews`
- `messages`
- `notifications`
- `reviews`
- `categories`

Migrations:
- `database/migrations/0001_01_01_000000_create_users_table.php` (extended with role fields)
- `database/migrations/2026_06_29_000001_create_performhub_tables.php`

Seeder: `database/seeders/DatabaseSeeder.php` (admin, sample performer, organizer, 6 categories)

---

## Key Files

```
app/
  Http/Controllers/
    Auth/AuthController.php
    HomeController.php
    Performer/          (Dashboard, Profile, Portfolio, Availability, Booking)
    Organizer/          (Dashboard, Profile, PerformerSearch, Booking, Interview)
    Admin/              (Dashboard, User, Category, Monitoring)
    InterviewController.php
    MessageController.php
    NotificationController.php
  Http/Middleware/RoleMiddleware.php
  Models/               (User, PerformerProfile, OrganizerProfile, Booking, etc.)
  Services/PerformerRecommendationService.php

resources/views/
  landing.blade.php
  auth/login.blade.php
  auth/register.blade.php
  layouts/guest.blade.php
  layouts/app.blade.php
  performer/ organizer/ admin/ messages/ notifications/ interviews/

routes/web.php
public/css/performhub.css
docker-compose.yml      (optional MySQL via Docker on port 3307)
.env                    (MySQL on port 3306 for standalone MySQL)
```

---

## .env Database Config

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=performhub
DB_USERNAME=performhub
DB_PASSWORD=performhub_secret
```

---

## UI Design Notes

- Dark background: `#0b0e14`
- Purple accent: `#6346ff`
- Card-based layout, responsive
- Login: left hero image + “Welcome back to the stage”, right form with role cards
- No Cursor branding in the app (footer: “© PerformHub. All rights reserved.”)

---

## Proponents

Erico P. Blaza, Ralph Steven M. Escosio, Miguel Enrico B. Cardenas

---

## Not Yet Implemented (Full MVP / Capstone 2)

- Ratings/reviews submission UI
- Full audition workflow beyond Jitsi join
- Email notifications (currently in-app only)
- Advanced recommender with ratings
- Posts/feed, disputes, system reports
- Laravel 13 upgrade (needs PHP 8.3+)

---

## Open in New Cursor Window

1. **File → Open Folder**
2. Select: `C:\Users\USER\Documents\performhub`
3. Read this file: `PROJECT_HANDOFF.md`
