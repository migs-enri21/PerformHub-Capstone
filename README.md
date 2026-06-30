# PerformHub

Web-based platform connecting performers and event organizers for talent discovery, auditions, and entertainment booking.

## Stack

- Laravel 12
- MySQL 8
- Bootstrap 5, Font Awesome
- Jitsi Meet (online interviews)

## Requirements

- PHP 8.2+
- Composer
- MySQL 8.0+

## Installation

1. Clone or copy the project, then install dependencies:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

2. Create the database and configure `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=performhub
DB_USERNAME=performhub
DB_PASSWORD=performhub_secret
```

3. Run migrations and start the server:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Open http://127.0.0.1:8000

## Demo Accounts

| Role      | Email                     | Password |
|-----------|---------------------------|----------|
| Admin     | admin@performhub.test     | password |
| Performer | performer@performhub.test | password |
| Organizer | organizer@performhub.test | password |

## Features

- Multi-role authentication (Performer, Organizer, Admin)
- Landing page with categories, featured performers, and testimonials
- Performer profiles, portfolio, availability, and booking management
- Organizer search, booking requests, contracts, and interviews
- Admin dashboard, user verification, and category management

## Proponents

Erico P. Blaza, Ralph Steven M. Escosio, Miguel Enrico B. Cardenas
