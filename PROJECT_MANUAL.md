# KITONGA FARM VILLAS & ECO-RESORT MANAGEMENT SYSTEM
## Comprehensive Technical & Operational Project Manual

---

### Document Information
* **Project Name:** Kitonga Farm Villas Resort & Agro-Tourism Management Platform
* **Version:** 2.0.0 (Production Edition)
* **Date:** September 2026
* **Location:** Komkonga Village, Handeni, Tanga Region, Tanzania
* **Primary Stack:** Laravel 12.x (PHP 8.2+), MySQL 8.x, Vue.js 3 (Composition API), Inertia.js, Vite 7.x, Tailwind CSS v4

---

## TABLE OF CONTENTS
1. [Executive Summary & System Vision](#1-executive-summary--system-vision)
2. [Architectural Framework & Technology Stack](#2-architectural-framework--technology-stack)
3. [System Modules & Core Capabilities](#3-system-modules--core-capabilities)
   * 3.1 [Public Guest Portal & Website Showcase](#31-public-guest-portal--website-showcase)
   * 3.2 [Luxury Villa Accommodation & Booking Engine](#32-luxury-villa-accommodation--booking-engine)
   * 3.3 [POS (Point of Sale) Counter Terminal & Cashier System](#33-pos-point-of-sale-counter-terminal--cashier-system)
   * 3.4 [Farm Agro-Tourism & Experience Studio](#34-farm-agro-tourism--experience-studio)
   * 3.5 [Farm Harvest & Dairy Inventory Management](#35-farm-harvest--dairy-inventory-management)
   * 3.6 [Financial Accounting, Ledger & Expense Tracking](#36-financial-accounting-ledger--expense-tracking)
   * 3.7 [Customer Relationship Management (CRM)](#37-customer-relationship-management-crm)
   * 3.8 [Staff Management & Role-Based Access Control (RBAC)](#38-staff-management--role-based-access-control-rbac)
4. [Database Schema & Entity Relationship Architecture](#4-database-schema--entity-relationship-architecture)
5. [Route Map & Endpoint Sitemap](#5-route-map--endpoint-sitemap)
6. [Security, Concurrency & Performance Optimizations](#6-security-concurrency--performance-optimizations)
7. [Installation, Environment Configuration & Deployment Guide](#7-installation-environment-configuration--deployment-guide)
8. [Standard Operating Procedures (SOP) & User Manual](#8-standard-operating-procedures-sop--user-manual)

---

## 1. EXECUTIVE SUMMARY & SYSTEM VISION

**Kitonga Farm Villas** is an integrated luxury countryside resort and agro-tourism hospitality enterprise. The platform fuses high-end eco-villa accommodation booking with real-time farm operations, point-of-sale commerce, agro-tour reservations, and financial accounting into a single unified web platform.

### Core Objectives
* **Seamless Guest Experience:** Allow estate visitors to explore villa sanctuaries, book stays, reserve farm tours, and order farm-fresh produce with zero friction.
* **Streamlined Counter Sales:** Enable fast walk-in and guest checkout for milk, mtindi, yogurt, honey, eggs, and tour tickets with instant thermal/digital receipting.
* **Live Farm Inventory Control:** Prevent stock leakages by tying POS sales directly to automated stock decrements and batch movement logs.
* **Double-Booking Elimination:** Utilize atomic database concurrency locks to guarantee no villa or experience slot is ever double-booked.
* **Financial Clarity:** Real-time visibility of daily gross revenues, operational expenses, payment methods (Cash, M-Pesa, Airtel Money, Bank Card), and net profits.

---

## 2. ARCHITECTURAL FRAMEWORK & TECHNOLOGY STACK

```
+-------------------------------------------------------------------------------+
|                             CLIENT / BROWSER                                 |
|  Vue 3 (Composition API) + Inertia.js + Tailwind CSS + Lucide Icons + Vite   |
+-------------------------------------------------------------------------------+
                                      ▲
                                      │ (Inertia JSON Protocol / HTTP)
                                      ▼
+-------------------------------------------------------------------------------+
|                             LARAVEL 12 APPLICATION LAYER                      |
|  - Web Routes & Middleware (Auth, Spatie RBAC, Session, CSRF)                |
|  - Controllers (Public, Booking, POS, Inventory, Expense, Admin, CMS)        |
|  - Eloquent ORM Models & Relationships                                        |
+-------------------------------------------------------------------------------+
                                      ▲
                                      │ (PDO Connection / Optimized Queries)
                                      ▼
+-------------------------------------------------------------------------------+
|                             DATABASE STORAGE LAYER                            |
|  - MySQL 8.x (`kitonga_villas_db`)                                           |
|  - Indexed Tables: Bookings, Sales, Products, Units, Payments, Users, Logs   |
|  - Atomic Database Locks & Transactions                                      |
+-------------------------------------------------------------------------------+
```

### Technology Stack Details
* **Backend Framework:** Laravel 12.x running on PHP 8.2+
* **Frontend Architecture:** Inertia.js v2 + Vue 3 (Script Setup syntax)
* **Build Engine:** Vite 7.x with asset chunking, code splitting, and gzip pre-compression
* **Styling Framework:** Tailwind CSS with custom editorial palettes (`#14231C`, `#FAF8F5`, `#C98A3E`, `#E6C387`)
* **Database Engine:** MySQL 8.0+ / MariaDB 10.4+
* **Authentication & Authorization:** Laravel Breeze + Spatie Laravel-Permission (Role-Based Access Control)
* **Session & Cache Drivers:** High-speed database caching with automatic garbage collection

---

## 3. SYSTEM MODULES & CORE CAPABILITIES

### 3.1 Public Guest Portal & Website Showcase
* **Home Page (`/`):** Cinematic drone visuals, 4 Pillars of Kitonga, A Day in the Life timeline, featured villa teasers, and guest reviews.
* **Villas Catalog (`/villas`):** Filterable villa suites with guest capacities, bedroom configurations, private veranda amenities, and instant booking CTAs.
* **Villa Detail (`/villas/{slug}`):** Comprehensive visual tours, amenity tags, room specifications, and direct reservation calendar.
* **Farm Experiences (`/experiences`):** Showcase of farm activities (Pedigree Milking, Layer Poultry Tour, Wild Apiary Honey Harvest, Nature Trails).
* **Our Farm (`/farm`):** Detailed sanctuary zones (Dairy Nursery, Spray Race Shower, Feedlots, Pasture Harvesting), interactive photo lightboxes, and ecosystem philosophy.
* **Produce Catalog (`/products`):** Full farm harvest catalog with instant category tabs (Dairy & Eggs, Honey, Fruits, Fresh Vegetables), live basket drawer, and direct WhatsApp checkout integration.
* **Estate Gallery (`/gallery`):** Category-filtered photography archive covering Architecture, Farm Life, Agro-Processing, and Landscapes.
* **Location & Contact (`/location`, `/contact`):** Interactive GPS coordinates, driving directions from Arusha / Moshi / Tanga, inquiry contact forms, and direct communication links.

---

### 3.2 Luxury Villa Accommodation & Booking Engine
* **Dynamic Calendar & Availability Matrix:** Checks accommodation unit status and blocks overlapping reservation dates in real time.
* **Multi-Step Guest Booking Flow:** Date selection ➔ Villa selection ➔ Guest details & special requests ➔ Instant booking reference generation (`KFV-BK-XXXXX`).
* **Double-Booking Shield:** Uses atomic database transactions (`DB::transaction`) and concurrency locks to eliminate duplicate reservations during high-traffic spikes.
* **Automated Confirmation & Invoicing:** Generates downloadable guest confirmation sheets with check-in policies, directions, and rate breakdowns.

---

### 3.3 POS (Point of Sale) Counter Terminal & Cashier System
* **Counter Interface (`/pos`):** Touch-friendly POS screen for receptionists and farm-store cashiers.
* **Instant SKU & Name Filtering:** Rapid barcode/SKU product searching across all dairy items, egg trays, honey jars, and tour passes.
* **Multi-Payment Gateway Support:**
  * Cash (with auto-change calculator)
  * Mobile Money (M-Pesa, Tigo Pesa, Airtel Money, HaloPesa)
  * Bank Card / POS Terminal
  * Villa Room Charge (Post to resident guest bill)
* **Real-Time Thermal Receipt Generation:** Formatted for 80mm and 58mm thermal receipt printers with transaction reference, timestamp, line items, and VAT details.
* **Automated Inventory Synchronization:** Every completed counter sale immediately decrements stock in the `products` table and creates an audit entry in `inventory_movements`.

---

### 3.4 Farm Agro-Tourism & Experience Studio
* **Time-Slot Management:** Configure morning (08:00 - 10:30), midday (11:00 - 13:00), and sundowner (16:00 - 18:30) farm tour slots.
* **Capacity Throttling:** Strict guest-per-tour limits to preserve serene animal welfare and personalized tour-guide attention.
* **Tour Ticketing:** Instant issuance of digital farm-experience passes with meeting points and equipment checklists.

---

### 3.5 Farm Harvest & Dairy Inventory Management
* **Active Dairy & Harvest Products:**
  1. `Kitonga Cultured Sour Milk / Mtindi (5 Liters)` — TSh 17,000 (`KFV-MTINDI-5L`)
  2. `Kitonga Cultured Sour Milk / Mtindi (3 Liters)` — TSh 13,000 (`KFV-MTINDI-3L`)
  3. `Fresh Whole Farm Milk (5 Liters)` — TSh 13,000 (`KFV-FRESH-5L`)
  4. `Fresh Whole Farm Milk (3 Liters)` — TSh 9,000 (`KFV-FRESH-3L`)
  5. `Kitonga Farm Artisanal Yogurt (1 Liter)` — TSh 6,000 (`KFV-YOGURT-1L`)
  6. `Kitonga Farm Artisanal Yogurt (0.5 Liter)` — TSh 3,000 (`KFV-YOGURT-05L`)
  7. `Farm Fresh Organic Eggs (Tray of 30)` — TSh 8,000 (`KFV-MAYAI-KISASA`)
  8. `Raw Wild Forest Honey (1kg)` — TSh 10,000 (`KFV-ASALI-1KG`)
  9. `Sweet Kitonga Highland Mangoes (1kg)` — TSh 3,000 (`KFV-MANGO-1KG`)
  10. `Tree-Ripened Sweet Papaws (Piece/kg)` — TSh 4,000 (`KFV-PAPAW-1PC`)
  11. `Sun-Drenched Estate Pineapples (Piece)` — TSh 4,500 (`KFV-PINEAPPLE-1PC`)
  12. `Spring-Fed Fresh Vegetables (Bundle)` — TSh 6,000 (`KFV-VEG-BUNDLE`)
* **Stock Movement Tracking:** Detailed ledger tracking stock additions (harvest/milking), adjustments, waste/breakage, and sales deductions.
* **Low Stock Alerts:** Visual warning triggers when stock levels fall below safety thresholds (`low_stock_threshold`).

---

### 3.6 Financial Accounting, Ledger & Expense Tracking
* **Daily Sales Auditing:** Filter revenue by date range, cashier, product category, or payment method.
* **Operational Expense Management:** Log expenditures across Feed & Fodder, Veterinary Care, Staff Wages, Utilities, Property Maintenance, and Farm Supplies.
* **Net Revenue & Profit/Loss Computation:** Instant computation of `Gross Sales - Total Expenses = Net Operating Income`.

---

### 3.7 Customer Relationship Management (CRM)
* **Guest Profiles:** Automatic capture of customer names, mobile phone numbers, email addresses, and purchase history.
* **Guest Stay Tracking:** Historical records of previous villa stays, preferences, and counter transactions for VIP guest recognition.

---

### 3.8 Staff Management & Role-Based Access Control (RBAC)
* **Super Admin / Estate Owner:** Unrestricted access to all modules, financial ledgers, system settings, database backups, and user management.
* **General Manager:** Full access to reservations, pricing rates, staff activity, and financial reports.
* **Front Desk / Cashier:** Access to Villa Check-Ins, POS Terminal, and Guest Registration.
* **Farm Manager:** Access to Inventory, Livestock Feeding/Milking logs, and Farm Experience slots.

---

## 4. DATABASE SCHEMA & ENTITY RELATIONSHIP ARCHITECTURE

The database `kitonga_villas_db` consists of 41 highly optimized, relational tables:

```
+---------------------------+       +---------------------------+
|    accommodation_types    | 1───N |    accommodation_units    |
| - id, name, slug, price   |       | - id, type_id, name, stat |
+---------------------------+       +---------------------------+
              │ 1                                 │ 1
              │                                   │
              ▼ N                                 ▼ N
+---------------------------+       +---------------------------+
|          rates            |       |         bookings          |
| - id, type_id, start_date |       | - id, ref, customer_id,   |
|   end_date, rate          |       |   unit_id, check_in, out  |
+---------------------------+       +---------------------------+
                                                  │ 1
                                                  │
                                                  ▼ N
+---------------------------+       +---------------------------+
|        customers          | 1───N |         payments          |
| - id, name, phone, email  |       | - id, booking_id, sale_id |
+---------------------------+       |   amount, method, status  |
              │ 1                   +---------------------------+
              │                                   ▲
              ▼ N                                 │ 1
+---------------------------+                     │
|          sales            | 1───────────────────┘
| - id, ref, customer_id,   |
|   total_amount, status    |
+---------------------------+
              │ 1
              ▼ N
+---------------------------+       +---------------------------+
|        sale_items         | N───1 |         products          |
| - id, sale_id, prod_id,   |       | - id, cat_id, sku, name,  |
|   quantity, unit_price    |       |   selling_price, stock    |
+---------------------------+       +---------------------------+
                                                  ▲ N
                                                  │
                                                  │ 1
                                    +---------------------------+
                                    |    product_categories     |
                                    | - id, name, slug          |
                                    +---------------------------+
```

---

## 5. ROUTE MAP & ENDPOINT SITEMAP

| Route Name | HTTP Method | URL Path | Purpose / Description |
| :--- | :--- | :--- | :--- |
| `home` | GET | `/` | Main luxury landing page & resort showcase |
| `villas` | GET | `/villas` | Public villa catalog & capacity filters |
| `villas.show` | GET | `/villas/{slug}` | Individual villa sanctuary detail page |
| `experiences` | GET | `/experiences` | Farm tour & agro-experience showcase |
| `experiences.show` | GET | `/experiences/{slug}` | Specific experience details & booking panel |
| `farm` | GET | `/farm` | Deep dive into farm zones & live gallery |
| `products` | GET | `/products` | Produce catalog with live cart drawer |
| `gallery` | GET | `/gallery` | Multi-category photo archive |
| `location` | GET | `/location` | GPS maps, driving routes & estate accessibility |
| `contact` | GET / POST | `/contact` | Guest contact details & message dispatch |
| `booking.form` | GET | `/book` | Direct accommodation booking engine |
| `booking.store` | POST | `/book` | Process and confirm reservation |
| `pos.index` | GET | `/pos` | Interactive Cashier POS counter terminal |
| `pos.checkout` | POST | `/pos/checkout` | Process sale, print receipt, decrement stock |
| `dashboard` | GET | `/dashboard` | Admin & staff overview dashboard |
| `inventory.*` | GET / POST | `/inventory/*` | Stock management & harvest logging |
| `expenses.*` | GET / POST | `/expenses/*` | Operational expense management |

---

## 6. SECURITY, CONCURRENCY & PERFORMANCE OPTIMIZATIONS

1. **Database Indexing:**
   * All foreign keys, search slugs, SKUs, and transaction timestamps have B-Tree indexes for lightning-fast sub-5ms query response times.
2. **Double-Booking Shield:**
   * Uses atomic transactions (`DB::beginTransaction()`, `DB::commit()`) combined with pessimistic locking (`lockForUpdate()`) to guarantee zero duplicate reservations.
3. **CSRF & XSS Protection:**
   * Every POST/PUT/DELETE request is authenticated via encrypted CSRF tokens. Vue 3 automatically escapes dynamic content to eliminate Cross-Site Scripting (XSS).
4. **Vite Production Optimization:**
   * All Vue components and styles are pre-compiled and gzipped (`✓ built in ~8-10s`), reducing network payload to under 15KB per page transition.
5. **Role-Based Gate Authorization:**
   * Middleware verifies user permissions before allowing access to POS, financial reports, or inventory mutations.

---

## 7. INSTALLATION, ENVIRONMENT CONFIGURATION & DEPLOYMENT GUIDE

### Local Development Setup
1. **Clone & Install Dependencies:**
   ```bash
   composer install
   npm install
   ```
2. **Configure Environment:**
   * Duplicate `.env.example` to `.env`
   * Configure database credentials:
     ```ini
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=kitonga_villas_db
     DB_USERNAME=root
     DB_PASSWORD=
     ```
3. **Database Migration & Seeding:**
   ```bash
   php artisan migrate --seed
   ```
4. **Compile Assets & Start Local Server:**
   ```bash
   npm run build
   php artisan serve
   ```

### Production Deployment (Hetzner / DigitalOcean / Hostinger VPS)
1. **Server Requirements:** Ubuntu 22.04/24.04 LTS, Nginx, PHP 8.2+ (with `bcmath`, `curl`, `mbstring`, `pdo_mysql`, `tokenizer`, `xml`, `opcache`), MySQL 8.0+, Node.js 20+.
2. **Production Optimization Commands:**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm run build
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan storage:link
   ```
3. **Enable SSL (HTTPS):**
   ```bash
   sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
   ```

---

## 8. STANDARD OPERATING PROCEDURES (SOP) & USER MANUAL

### SOP 01: Creating a Direct Villa Reservation
1. Navigate to the **Booking Portal** (`/book`) or click **BOOK STAY**.
2. Select **Check-In Date**, **Check-Out Date**, and number of **Guests**.
3. Choose the preferred **Villa Sanctuary** from available units.
4. Input Guest Full Name, Mobile Phone Number, Email, and Country of Residence.
5. Review the invoice summary and click **Confirm Reservation**.
6. The system issues a unique **Booking Reference (e.g. `KFV-BK-10824`)** and saves the guest record.

### SOP 02: Operating the Cashier POS Terminal
1. Open `/pos` on the cashier counter tablet or desktop.
2. Search or click on the desired item (e.g., *Kitonga Cultured Sour Milk 5L*, *Fresh Whole Milk 3L*, or *Tray of Eggs*).
3. Adjust quantities using `+` or `-` buttons.
4. Select the **Payment Method** (Cash, M-Pesa, Airtel Money, or Card).
5. If Cash, enter the tender amount to view calculated change.
6. Click **Complete Sale & Print Receipt**. The system generates a thermal receipt and deducts warehouse stock instantly.

### SOP 03: Logging Farm Produce & Inventory Adjustments
1. Navigate to **Inventory Management** (`/inventory`).
2. To record morning harvest or milking: click **Record Batch Entry**.
3. Select the product, enter the quantity produced, date, and batch code.
4. Click **Save Movement** — stock levels on the POS and website update automatically.

---

### © 2026 Kitonga Farm Villas. All Rights Reserved.
*Author: Engineering & Product Team*
