# SIPTU.V3 – Project Notes (Living Memory)

These notes capture decisions and conventions so future sessions quickly regain context.

## Layouts & Views
- Admin layout: `resources/views/layouts/admin.blade.php` (includes sidebar, header, toasts, `@yield('content')`).
- Breeze layout: `resources/views/layouts/app.blade.php` (originally component-style with `$slot`). Updated to support both:
  - Uses `@isset($slot) {{ $slot }} @else @yield('content') @endisset`.
- Guest layout: `resources/views/layouts/guest.blade.php` (auth pages).
- BMN views should use the admin layout for sidebar:
  - `resources/views/bmn/index.blade.php` → `@extends('layouts.admin')`.
  - `resources/views/bmn/laporan.blade.php` → `@extends('layouts.admin')`.
  - `resources/views/bmn/create.blade.php`, `edit.blade.php`, `import.blade.php` already extend `layouts.admin`.

## Modules / Features
- BMN CRUD: controller `app/Http/Controllers/BmnController.php`, views under `resources/views/bmn/`.
- Peminjaman BMN: controller `app/Http/Controllers/PeminjamanBmnController.php`, routes named `peminjaman-bmn.*`.
- Routes: defined in `routes/web.php` (BMN, peminjaman BMN, laporan).

## Recent Fixes
- Fixed Blade error “Undefined variable $slot” by adding fallback to `@yield('content')` in `layouts/app.blade.php`.
- Standardized BMN listing and report pages to use `layouts.admin` so the sidebar appears.
- SPA (Inertia + Vue + Naive UI) integrated; `/spa` is the authenticated dashboard.
- Post-login redirect points to SPA dashboard; `/dashboard` now redirects to `/spa`.
- Login page redesigned (`resources/views/auth/login.blade.php`) with modern UI.

## Conventions
- Use `layouts.admin` for all admin/module pages needing the sidebar.
- Reserve `layouts.app` for Breeze component pages or simple pages (now also works with `@extends`).
- New work should target SPA pages under `resources/js/Pages` and routes prefixed with `/spa/*`.

## Useful Commands
- Clear compiled views if needed: `php artisan view:clear`.
- Rebuild assets: `npm run build` (or `vite` dev per project setup).

## Next Steps (Optional)
- Standardize related pages (e.g., peminjaman BMN) to `layouts.admin` for consistency.
- Add small view partial for page titles/actions to reduce duplication.
- Consider adding advanced filters in SPA lists and chart dashboards.

## Session Bootstrap (Read Me First)
- After opening this repo in Codex, read this file (docs/PROJECT_NOTES.md).
- Primary entry after auth is `/spa`. All new features should be added as Inertia pages.
- Key files:
  - Routes: `routes/web.php` (`/spa` routes + redirects)
  - SPA entry: `resources/js/spa.js`
  - Layout: `resources/js/Layouts/AdminLayout.vue` (sidebar, theme, toasts)
  - Common UI: `resources/js/Components` (PageHeader, Toolbar, DatePicker)
  - Modules: `resources/js/Pages/*/*`
