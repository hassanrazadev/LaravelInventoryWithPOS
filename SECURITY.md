# Security Policy

## Supported Versions

Only the latest `master` branch (Laravel 13 / PHP 8.4+) receives security fixes and updates.

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
- Keep thermal receipt printing scoped to trusted local printers: `App\Services\ThermalReceiptService::isAllowedPrinterHost()` rejects public internet IPs, DNS hostnames (unless matching `POS_PRINTER_HOST`), and cloud metadata ranges (`169.254.0.0/16`), permitting only localhost (`127.0.0.1`, `localhost`, `::1`) and private RFC1918 LAN subnets (`10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`). Do not expose `POS_PRINTER_HOST` to untrusted external endpoints.
- Keep dependencies updated via `composer update` and monitor Dependabot alerts.
