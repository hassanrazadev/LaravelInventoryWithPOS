# Changelog

All notable changes to this project are documented in this file.

## Unreleased / Open-Source Release

### Milestone 5 — POS UX Polish, ESC/POS Thermal Printing & Community Readiness
- Flattened sidebar navigation (`Sidebar.php`, `sidebar.blade.php`) into single-click groups (`MAIN`, `SALES & OPERATIONS`, `INVENTORY`, `CONTACTS`, `ADMINISTRATION`) with `POS Terminal` pinned near top, refined mini-rail alignment, and hid duplicate header POS CTA on `/orders/create`.
- Redesigned `/orders/create` POS workspace (`pos.js`, `modern-theme.css`) with a compact `430px` sticky checkout rail, `In Stock` filter toggle, 2-row card cart items, `$5 / $10 / $20 / $50 / $100` quick cash chips, payment method pills, aligned tender controls, and side-by-side `Complete Sale` + `Pay & Print` actions.
- Added `ThermalReceiptService`, `config/pos.php`, and `/orders/pos/printer/test` + `/orders/{order}/print-thermal` endpoints supporting:
  - Raw ESC/POS TCP socket printing (`127.0.0.1:9100` default, compatible with LAN thermal printers and Virtual Printer Suite)
  - Optional ESC/POS Pin 2 cash drawer solenoid pulse (`ESC p 0 25 250`) on Cash sales
  - HTTP POST bridge (`escpos` raw bytes or plain text) and standalone `80mm` / `58mm` browser thermal receipt print preview (`/orders/{order}/receipt`)
  - SSRF host allowlist restricting network targets to localhost and RFC1918 private IPs (`isAllowedPrinterHost`)
- Added `ThermalReceiptServiceTest` and `ThermalPrintTest`, expanding automated PHPUnit coverage to 52 tests.
- Added `CODE_OF_CONDUCT.md`, MIT metadata in `package.json`, `composer.json` keywords, README architecture/thermal-printer docs, and UI screenshots in `docs/screenshots/`.

### Milestone 4 — Open-Source Readiness
- Added `LICENSE` (MIT), updated `composer.json` metadata/scripts, clean `.env.example`, and `.gitignore`.
- Installed `laravel/pint` (`v1.32`) with `pint.json` (`laravel` preset) and formatted the PHP codebase.
- Removed legacy AdminCAST demo JS (`dashboard_1_demo.js`) and dead helper `AppUtils::isImage()`.
- Rewrote `README.md` and added `CONTRIBUTING.md`, `SECURITY.md`, `.github/ISSUE_TEMPLATE/*`, `.github/PULL_REQUEST_TEMPLATE.md`, `.github/dependabot.yml`, and `.github/workflows/ci.yml` (PHP 8.3 & 8.4).

### Milestone 3 — Dashboard Analytics, Expenses & Sales Returns
- Replaced placeholder Dashboard cards and charts with real database KPIs, 7-day Sales vs. Purchases bar chart, Payment Breakdown doughnut chart, Recent Orders, Low Stock Alerts (`<= 5`), and Top Selling Products.
- Added full **Expenses** CRUD (`category`, `expense_date`, `notes`, KPI cards, category filters, soft-delete toggles).
- Added full **Sales Returns** CRUD linked to completed orders with dynamic item loading, remaining-quantity validation, optional inventory restocking, and order detail integration.
- Added PHPUnit tests (`DashboardTest`, `ExpenseTest`, `SaleReturnTest`) and updated `DemoDataSeeder`.

### Milestone 2 — Suppliers, Customers & POS Checkout
- Enhanced `orders` and `order_details` tables (`order_no` unique index, `payment_method`, `paid_amount`, `change_amount`, `status`, `notes`, `unit_price`, `sub_total`, decimal `(12, 2)`).
- Implemented full **Suppliers** and **Customers** CRUD (with POS JSON quick-add support and deletion safeguards).
- Built live **POS Terminal** (`/orders/create`), atomic transactional checkout with `lockForUpdate()`, **Orders** history/filtering/cancellation, and standalone printable receipts (`/orders/{id}/receipt`).
- Added PHPUnit tests (`SupplierTest`, `CustomerTest`, `OrderPosTest`).

### Milestone 1 — Modern UI Redesign, Auth & RBAC
- Redesigned application shell (`modern-theme.css`) and fixed browser UI/JS glitches across categories, products, and purchases.
- Added full **Auth** workflow (Remember Me, `POST /user/logout`, forgot/reset password, user profile & password change).
- Built **Users**, **Roles** (grouped permission matrix), and **Permissions** CRUD with Spatie RBAC middleware enforcement across controllers.

### Framework Modernization — Laravel 8 to Laravel 13
- Upgraded framework stack from Laravel 8 (`PHP 7.3`) to **Laravel 13 (`PHP 8.3+`)**, Spatie Laravel Permission 8, PHPUnit 12, and Vite 7 using the slim directory structure.
- Fixed pre-existing category, product, purchase, and routing bugs and introduced PSR-4 factories and idempotent demo seeders.
