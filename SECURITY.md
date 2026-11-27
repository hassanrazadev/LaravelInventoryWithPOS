# Security Policy

## Supported Versions

Only the latest `master` branch (Laravel 13 / PHP 8.3+) receives security fixes and updates.

| Version | Supported |
| --- | --- |
| Laravel 13 (`master`) | Yes |
| Legacy Laravel 7/8 tags | No |

## Reporting a Vulnerability

If you discover a security vulnerability in this project, **please do not open a public GitHub issue**.

Instead, send details privately to:

- **Email:** `info@hassanraza.net`

Please include:
- A clear description of the vulnerability and potential impact
- Steps to reproduce or a minimal proof of concept
- Any suggested remediation if known

We aim to acknowledge reports within **72 hours** and coordinate a fix before public disclosure.

## Production Hardening Checklist

- Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
- Use a strong, randomly generated `APP_KEY` and unique database credentials.
- Change or remove seeded demo accounts (`admin@test.com`, `manager@test.com`, `cashier@test.com`) immediately after setup.
- Enforce HTTPS behind a trusted reverse proxy and configure `SESSION_SECURE_COOKIE=true`.
- Keep dependencies updated via `composer update` and monitor Dependabot alerts.
