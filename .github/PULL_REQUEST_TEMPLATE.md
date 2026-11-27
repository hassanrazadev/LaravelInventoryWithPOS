## Summary

Describe the changes introduced in this pull request and link any related issues.

## Type of Change

- [ ] Bug fix
- [ ] New feature
- [ ] UI / UX improvement
- [ ] Documentation / Chore
- [ ] Database migration included

## Verification Checklist

- [ ] `composer test` (`php artisan test`) passes locally
- [ ] `composer lint` (`./vendor/bin/pint --test`) passes cleanly
- [ ] Cache verification (`php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan optimize:clear`) succeeds
- [ ] Verified in browser with zero console/network errors (for UI/JS changes)
- [ ] Any new routes/views enforce Spatie permissions (`HasMiddleware` / `@can`)

## Screenshots (if applicable)

Attach screenshots or screen recordings for UI changes.
