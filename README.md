# Laravel Inventory Management with POS (IMS-POS)

[![CI](https://github.com/hassanrazadev/LaravelInventoryWithPOS/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/hassanrazadev/LaravelInventoryWithPOS/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-10b981.svg)](LICENSE)

An open-source Inventory Management System and Point of Sale (POS) web application built on **Laravel 13**, **Spatie Laravel Permission**, **MySQL / SQLite**, and a modernized responsive UI.

---

## Features

- **Real-Time Analytics Dashboard**
  - Live KPIs for completed sales, inventory stock units, low-stock count, purchases, expenses, and sales returns.
  - 7-day Sales vs. Purchases bar chart and Payment Breakdown doughnut chart (Chart.js).
  - Recent Orders table, Low Stock Alerts (`<= 5` units) with quick Restock links, and Top Selling Products.
- **Point of Sale (POS) & Orders**
  - Live product catalog grid with category filter pills, SKU/name search, Enter-to-add barcode/SKU scanning, and stock level indicators.
  - Interactive cart with quantity steppers, discount (`$`), percentage tax (`%`), multiple payment methods (`Cash`, `Card`, `Bank Transfer`, `Other`), and live change-due calculation.
  - Inline Quick-Add Customer modal during checkout without leaving the POS screen.
  - Atomic database checkout with row locking (`lockForUpdate`), standalone printable receipts (`/orders/{id}/receipt`), order cancellation with automatic stock restoration, and soft-delete safeguards.
- **Inventory & Purchasing**
  - **Categories** CRUD with parent/child hierarchy, cropped image uploads, and soft-delete toggles.
  - **Products** CRUD with unique SKU codes/slugs, sale pricing, stock levels, image cropping, and purchase history.
  - **Purchases** CRUD linked to suppliers with multi-line product rows and transactional stock increments/reversals.
- **Contacts, Expenses & Sales Returns**
  - **Suppliers** CRUD with linked purchase history and deletion protection when purchases exist.
  - **Customers** CRUD with order counts, total completed spend, order history, and JSON support for POS quick-add.
  - **Expenses** CRUD with category filtering, monthly/total KPIs, notes, and soft-delete toggles.
  - **Sales Returns** CRUD linked to completed orders, dynamic remaining-quantity enforcement, optional inventory restocking, and atomic stock reversal on deletion.
- **Authentication & Role-Based Access Control (RBAC)**
  - Login with Remember Me, POST logout, Forgot/Reset password flow, and User Profile management (including `current_password` verification).
  - Full **Users**, **Roles** (grouped permission matrix), and **Permissions** CRUD backed by Spatie Laravel Permission (`44` permissions across 11 modules) with self-deletion and last-admin safeguards.

---

## Tech Stack

- **Backend:** PHP 8.3+, Laravel 13 (slim directory structure), Spatie Laravel Permission 8
- **Database:** MySQL 8+ (local/production) & SQLite (automated testing)
- **Frontend:** Blade templates, Bootstrap 4, jQuery, DataTables, Select2, Cropper.js, Chart.js 2.7, Vite 7
- **Quality & Testing:** PHPUnit 12, Laravel Pint (`laravel` preset), GitHub Actions CI

---

## Installation

### Prerequisites

- PHP `>= 8.3` with extensions: `gd`, `mbstring`, `pdo_mysql` (or `pdo_sqlite`), `xml`, `zip`
- [Composer](https://getcomposer.org/) `>= 2.x`
- MySQL `8.0+` (or SQLite for quick local evaluation)
- Node.js & npm *(optional, only needed if customizing Vite assets)*

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/hassanrazadev/LaravelInventoryWithPOS.git
   cd LaravelInventoryWithPOS
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Configure your environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Update `DB_*` credentials in `.env` for your local MySQL database (or switch `DB_CONNECTION=sqlite`).

4. **Create storage symlink & migrate + seed database**
   ```bash
   php artisan storage:link
   php artisan migrate --seed
   ```
   > **Note:** `DatabaseSeeder` automatically seeds `PermissionsTableSeeder`, `RolesTableSeeder`, `CreateAdminSeeder`, and `DemoDataSeeder` (categories, products with generated placeholder PNGs, suppliers, customers, purchases, demo orders, expenses, and a sample sales return) in non-production environments.

5. **Start the development server**
   ```bash
   php artisan serve
   ```
   Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser.

---

## Demo Accounts

When seeded locally or with `DemoDataSeeder`:

| Role | Email | Password | Access Scope |
| --- | --- | --- | --- |
| **Admin** | `admin@test.com` | `123456` | Full access (all modules, users, roles & permissions) |
| **Manager** | `manager@test.com` | `123456` | Categories, products, purchases, orders, suppliers, customers, expenses & returns |
| **Cashier** | `cashier@test.com` | `123456` | Read categories/products, POS checkout/orders, add/view customers |

> **Security Notice:** Change all demo credentials or remove `DemoDataSeeder` users immediately before deploying to any public or production server.

---

## Testing & Code Style

Run the automated test suite (uses an in-memory SQLite database via `phpunit.xml`):

```bash
composer test
# or
php artisan test
```

Check or format code style using Laravel Pint:

```bash
composer lint     # Dry-run check (used in CI)
composer format   # Auto-fix formatting issues
```

---

## Documentation & Community

- [Contributing Guide](CONTRIBUTING.md)
- [Security Policy](SECURITY.md)
- [Changelog](CHANGELOG.md)
- [MIT License](LICENSE)

---

## Author

- **Hassan Raza** — [hassanraza.net](https://hassanraza.net) · `info@hassanraza.net`
