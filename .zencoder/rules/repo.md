---
description: Repository Information Overview
alwaysApply: true
---

# Proforma Management System Information

## Summary
A Laravel 12 web application designed for managing proformas, invoices, and clients. It features a comprehensive admin interface built with **Filament PHP**, supports **Socialite** for authentication (specifically Microsoft and Azure), and includes specialized tools for **Excel imports** and **PDF parsing**.

## Structure
- **app/**: Core application logic.
    - **Filament/**: Admin resources, pages, and widgets for managing entities like Clients, Invoices, and Proformas.
    - **Models/**: Eloquent models for data entities (e.g., `Proforma`, `Invoice`, `Clienti`).
    - **Imports/**: Excel import logic using `Filament Excel Import`.
    - **Mail/**: Email templates like `ProformaMail`.
- **config/**: Application configuration files.
- **database/**: Database schema migrations, factories, and seeders (using SQLite by default).
- **public/**: Entry point (`index.php`) and static assets (images, CSS, JS).
- **resources/**: Raw frontend assets (Blade templates, Tailwind CSS, JavaScript).
- **routes/**: Definition of web and console routes.
- **storage/**: Application-generated files, logs, and cache.
- **tests/**: Automated tests using PHPUnit.

## Language & Runtime
**Language**: PHP  
**Version**: ^8.2  
**Build System**: Vite  
**Package Manager**: Composer (PHP), NPM (Node.js)

## Dependencies
**Main Dependencies**:
- `laravel/framework`: ^12.0
- `filament/filament`: ^4.3 (Admin Panel)
- `laravel/socialite`: ^5.24 (OAuth Authentication)
- `smalot/pdfparser`: ^2.12 (PDF content extraction)
- `eightynine/filament-excel-import`: ^4.0 (Excel data importing)
- `dutchcodingcompany/filament-socialite`: ^3.0 (Socialite integration for Filament)

**Development Dependencies**:
- `phpunit/phpunit`: ^11.5.3 (Testing framework)
- `barryvdh/laravel-debugbar`: ^3.16 (Debugging tool)
- `laravel/pint`: ^1.24 (PHP code style fixer)
- `laravel/sail`: ^1.41 (Docker environment via Laravel Sail)

## Build & Installation
```bash
# Full project setup
composer setup

# Manual installation
composer install
npm install
npm run build
php artisan migrate
```

## Testing

**Framework**: PHPUnit
**Test Location**: `tests/Feature/` and `tests/Unit/`
**Naming Convention**: Files end with `Test.php`
**Configuration**: `phpunit.xml`

**Run Command**:

```bash
php artisan test
# OR
composer test
```
