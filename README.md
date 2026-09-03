# Kitonga Farm Villas Management Platform

Welcome to the Kitonga Farm Villas master booking engine and business operating system.

## Project Classification
Production-grade luxury farm-stay website + booking engine + business management system.

---

## Technical Stack
- **Backend**: Laravel 11 / PHP 8.2+
- **Frontend**: Vue 3 / Inertia.js / Tailwind CSS / Vite
- **Database**: SQLite (configured for `database/kitonga_villas_prod.sqlite` to avoid name conflicts)

---

## Quick Start Setup

### 1. Installation
Clone the repository and run the setup commands in the workspace:
```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Database Fresh Migration & Seeding
This creates the SQLite file and populates it with default villas, tours, products, user accounts, and settings:
```bash
php artisan migrate:fresh --seed
```

### 3. Local Development Servers
Run the Laravel local web server and Vite dev server in parallel:
```bash
# Start Laravel server (defaults to http://127.0.0.1:8000)
php artisan serve

# Start Vite dev server for compiling assets
npm run dev
```

### 4. Running the Test Suite
Run the automated unit/feature tests to verify that booking concurrency, pricing, and inventory integrity are functioning properly:
```bash
php artisan test
```

---

## Default Seeded Accounts
You can log in to the admin panel using the following seeded credentials (password for all is `password`):
- **Owner**: `owner@kitongafarm.com`
- **Manager**: `manager@kitongafarm.com`
- **Reception**: `reception@kitongafarm.com`
- **Cashier**: `cashier@kitongafarm.com`

---

## Key Directories
- **Models**: [app/Models/](file:///C:/Users/steve/Music/project/app/Models)
- **Services**: [app/Services/](file:///C:/Users/steve/Music/project/app/Services) (contains `BookingService.php`, `InventoryService.php`, `POSService.php`)
- **Controllers**: [app/Http/Controllers/](file:///C:/Users/steve/Music/project/app/Http/Controllers)
- **Vue Templates**: [resources/js/Pages/](file:///C:/Users/steve/Music/project/resources/js/Pages)
- **Feature Tests**: [tests/Feature/](file:///C:/Users/steve/Music/project/tests/Feature)
