# Contributing to Laravel Inventory Management with POS

Thank you for your interest in contributing! Whether you are fixing a bug, adding tests, improving documentation, or proposing a new feature, your help is welcome.

## Development Workflow

1. **Fork & Clone** the repository:
   ```bash
   git clone https://github.com/<your-username>/LaravelInventoryWithPOS.git
   cd LaravelInventoryWithPOS
   ```
2. **Create a feature/fix branch** from `master`:
   ```bash
   git checkout -b feat/short-descriptive-name
   ```
3. **Install dependencies & configure local `.env`**:
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan storage:link
   php artisan migrate --seed
   ```

## Documentation & Standards

- Review the [Code of Conduct](CODE_OF_CONDUCT.md) before participating; all contributors are expected to follow it.
- Report suspected security issues privately as described in [SECURITY.md](SECURITY.md).

## Quality Checks Before Opening a PR

All pull requests run automated CI checks on GitHub Actions. Please verify locally before pushing:

1. **Code Style (Laravel Pint):**
   ```bash
   composer lint
   ```
   To automatically fix style issues:
   ```bash
   composer format
   ```
2. **Automated Tests (PHPUnit 12):**
   ```bash
   composer test
   ```
3. **Configuration & Route Caching:**
   ```bash
   php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan optimize:clear
   ```

## Guidelines

- Follow Laravel 13 conventions and the existing slim directory structure.
- Add or update PHPUnit feature/unit tests (`test_` method naming or `#[Test]`) for every bug fix or new feature.
- Ensure migrations work cleanly on both **MySQL 8+** and **SQLite** (keep schema column additions and `->change()` alterations in separate `Schema::table` blocks).
- Keep UI changes clean, accessible, and free of console/network errors.
- Enforce Spatie permissions (`HasMiddleware` + `@can`) on new endpoints and UI actions.
- When changing receipt formatting or optional ESC/POS payloads (`App\Services\ThermalReceiptService`), run unit/feature print tests (`composer test`) and verify `/orders/{id}/receipt` (`80mm`/`58mm` browser preview). Any external virtual printer emulator is strictly for local development/testing only and must remain optional (`POS_PRINTER_DRIVER=browser` default for production).

## Submitting a Pull Request

- Target the `master` branch.
- Fill out the pull request template clearly, noting any database migration steps or UI screenshots when applicable.
- Keep commits focused and descriptive.
