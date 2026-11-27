# Laravel Inventory Management with POS (IMS-POS)

[![CI](https://github.com/hassanrazadev/LaravelInventoryWithPOS/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/hassanrazadev/LaravelInventoryWithPOS/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Code Style: Laravel Pint](https://img.shields.io/badge/Code%20Style-Laravel%20Pint-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/docs/pint)
[![License: MIT](https://img.shields.io/badge/License-MIT-10b981.svg)](LICENSE)

An open-source Inventory Management System and Point of Sale (POS) web application built on **Laravel 13**, **Spatie Laravel Permission**, **MySQL / SQLite**, a clean modern UI with flat single-click navigation, and direct **ESC/POS thermal receipt printing + cash drawer control**.

---

## Screenshots

### Real-Time Analytics Dashboard & Flat Single-Click Sidebar
![Analytics Dashboard and Flat Sidebar](docs/screenshots/dashboard.png)

### Modern POS Terminal & Sticky Checkout Rail
![POS Terminal with Live Product Grid and Checkout Rail](docs/screenshots/pos-terminal.png)

### Live ESC/POS Thermal Receipt & Cash Drawer Output (Virtual Printer Suite)
![Live Thermal Receipt and Cash Drawer Output on Port 9100](docs/screenshots/thermal-printer-output.png)

### Standalone 80mm / 58mm Thermal Receipt Preview
![Standalone 80mm and 58mm Thermal Receipt Preview](docs/screenshots/receipt-preview.png)

### Product Inventory Management & Stock Indicators
![Products Management View](docs/screenshots/products-management.png)

### Role-Based Access Control (RBAC) Permission Matrix
![Roles and Permissions Matrix](docs/screenshots/rbac-roles.png)

---

## Features

- **Modern Application Shell & Analytics Dashboard**
  - Flat single-click grouped sidebar (`MAIN`, `SALES & OPERATIONS`, `INVENTORY`, `CONTACTS`, `ADMINISTRATION`) with pinned **POS Terminal** access and collapsible mini-rail on POS screens.
  - Real-time KPIs for completed sales, inventory stock units, low-stock alerts (`<= 5` units with direct Restock links), purchases, expenses, and sales returns.
  - Interactive 7-day Sales vs. Purchases bar chart and Payment Breakdown doughnut chart (Chart.js).
- **Point of Sale (POS) Terminal**
  - Responsive product catalog grid with category filter pills & item counts, **In Stock** toggle, live name/SKU search, and Enter-to-add barcode/SKU scanning.
  - Sticky `430px` checkout rail with compact 2-row card cart items, quantity steppers, discount (`$`), percentage tax (`%`), collapsible counter note, payment method pills (`Cash`, `Card`, `Bank`, `Other`), quick `$5 / $10 / $20 / $50 / $100` tender chips, live change-due calculation, and paired **Complete Sale** + **Pay & Print** actions.
  - Inline Quick-Add Customer modal without losing the active cart.
  - Atomic database checkout with row-level locking (`lockForUpdate`), standalone printable receipts (`/orders/{id}/receipt`), order cancellation with stock restoration, and soft-delete safeguards.
- **Thermal Receipt Printing & Hardware Support**
  - Configurable POS Printer Settings modal (`127.0.0.1:9100 · 80mm` default) with persisted preferences, `80mm` (`48` cols) / `58mm` (`32` cols) layouts, and one-click **Send Test Print**.
  - Supports **Raw ESC/POS TCP Sockets** (compatible with LAN thermal printers and [Virtual Printer Suite](http://localhost:8000)), **HTTP POST Bridge** (`escpos` raw bytes or plain text), and **Browser Print Dialog** (`80mm`/`58mm` `@page` thermal CSS).
  - Optional **ESC/POS Cash Drawer Pin 2 kick** (`ESC p 0 25 250` / `\x1B\x70\x00\x19\xFA`) triggered on Cash sales.
- **Inventory, Purchasing, Contacts & Operations**
  - **Categories** and **Products** CRUD with parent/child hierarchy, unique SKUs, stock tracking, and Cropper.js image uploads.
  - **Purchases** CRUD linked to **Suppliers** with transactional stock updates and delete protection when purchases exist.
  - **Customers**, **Expenses** (category filters & soft-delete toggles), and **Sales Returns** (remaining-quantity validation & optional inventory restocking).
- **Authentication & Role-Based Access Control (RBAC)**
  - Remember Me login, `POST /user/logout`, forgot/reset password flow, and User Profile management (`current_password` validation).
  - Full **Users**, **Roles** (grouped permission matrix), and **Permissions** CRUD backed by Spatie Laravel Permission (`44` permissions across `11` modules) with self-deletion and last-admin protections.

---

## Thermal Receipt Printing & Hardware Setup

POS thermal printing is managed by `App\Services\ThermalReceiptService` and configured in [config/pos.php](config/pos.php):

| Environment Variable | Default | Description |
| --- | --- | --- |
| `POS_STORE_NAME` | `"${APP_NAME}"` | Store title printed at the top of ESC/POS and browser receipts |
| `POS_PRINTER_DRIVER` | `tcp` | Default transport: `tcp` (raw ESC/POS socket) or `http` (POST bridge) |
| `POS_PRINTER_HOST` | `127.0.0.1` | Target thermal printer IP / localhost |
| `POS_PRINTER_PORT` | `9100` | ESC/POS TCP socket port (`9100` standard; matches Virtual Printer Suite while its UI runs on `:8000`) |
| `POS_PRINTER_WIDTH` | `80` | Thermal paper roll width in mm: `80` (`48` columns) or `58` (`32` columns) |
| `POS_OPEN_CASH_DRAWER` | `true` | Append ESC/POS Pin 2 solenoid pulse (`1B 70 00 19 FA`) on Cash sales |

### Testing with a Virtual Thermal Printer

1. Start your virtual thermal printer (for example **Virtual Printer Suite** serving its web UI on `http://localhost:8000` and listening for raw ESC/POS jobs on `127.0.0.1:9100`).
2. Open `/orders/create`, click the printer status pill (`127.0.0.1:9100 · 80mm`) in the POS topbar, verify the transport/host/port/width settings, and click **Send Test Print**.
3. Use **Pay & Print** at checkout or **Thermal Print** on any order or receipt page to emit ESC/POS receipts and trigger the cash drawer pulse on Cash transactions.

> **SSRF Security:** `ThermalReceiptService::isAllowedPrinterHost()` permits only loopback hosts (`127.0.0.1`, `localhost`, `::1`), the configured `POS_PRINTER_HOST`, and private RFC1918 LAN IPv4 subnets (`10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`). Public IPs, link-local metadata ranges (`169.254.0.0/16`), and arbitrary domains are rejected with HTTP `422`.

---

## Architecture & Modules

| Module | Controllers / Services | Key Routes |
| --- | --- | --- |
| **Dashboard & Profile** | `HomeController`, `ProfileController` | `/`, `/profile` |
| **POS, Orders & Thermal Print** | `OrderController`, `ThermalReceiptService` | `/orders`, `/orders/create`, `/orders/{order}/receipt`, `/orders/{order}/print-thermal`, `/orders/pos/printer/test` |
| **Catalog & Inventory** | `CategoryController`, `ProductController` | `/categories`, `/products` |
| **Purchasing & Contacts** | `PurchaseController`, `SupplierController`, `CustomerController` | `/purchases`, `/suppliers`, `/customers` |
| **Operations & Adjustments** | `ExpenseController`, `SaleReturnController` | `/expenses`, `/returns` |
| **Auth & RBAC** | `AuthController`, `UserController`, `RoleController`, `PermissionController` | `/user/login`, `/users`, `/roles`, `/permissions` |

---

## Tech Stack

- **Backend:** PHP 8.3+, Laravel 13 (slim directory structure), Spatie Laravel Permission 8
- **Database:** MySQL 8+ (local/production) & SQLite (automated testing)
- **Frontend:** Blade templates, Bootstrap 4, jQuery, DataTables, Select2, Cropper.js, Chart.js 2.7, Vite 7
- **Quality & Testing:** PHPUnit 12 (`52` automated tests), Laravel Pint (`laravel` preset), GitHub Actions CI (`PHP 8.3` & `8.4`)

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
   Update `DB_*` credentials in `.env` for your local MySQL database (or switch `DB_CONNECTION=sqlite`), and adjust `POS_*` settings if your thermal printer uses a non-default port or paper width.

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
   Open [http://127.0.0.1:8000](http://127.0.0.1:8000) (or `--port=8001` if running Virtual Printer Suite on `:8000`).

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
composer lint     # Dry-run check (enforced in CI)
composer format   # Auto-fix formatting issues
```

---

## Community, Documentation & License

- [Contributing Guide](CONTRIBUTING.md)
- [Code of Conduct](CODE_OF_CONDUCT.md)
- [Security Policy](SECURITY.md)
- [Changelog](CHANGELOG.md)
- [MIT License](LICENSE)

---

## Author

- **Hassan Raza** — [hassanraza.net](https://hassanraza.net) · `info@hassanraza.net`
