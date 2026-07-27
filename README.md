# MobilePOS

A mobile-friendly Web POS (Point of Sale) built with Laravel + MySQL, designed to run on
Hostinger **shared** hosting (a plan with SSH + Composer access). Blade + Alpine.js
frontend, installable as a PWA, with camera and Bluetooth/HID barcode scanning.

## Stack

- Laravel 12, targets PHP 8.3+ (dev machine may run newer PHP locally; avoid 8.3+-only features)
- MySQL via Eloquent migrations
- Blade + Alpine.js, Tailwind CSS v4, bundled with Vite
- `html5-qrcode` for in-browser camera scanning
- `barryvdh/laravel-dompdf` for PDF receipts (pure-PHP, needs only `ext-dom` + `ext-mbstring`)
- PIN-based session auth (no email/password, no third-party auth package)
- Only two Composer dependencies beyond Laravel itself, chosen deliberately to keep
  `composer install` reliable on shared hosting

## Feature overview

- **Scan** — continuous camera scanning (EAN-13/8, UPC-A/E, Code128, Code39, QR) with
  beep + flash feedback, plus automatic detection of Bluetooth/USB "keyboard wedge"
  scanners and a manual barcode/SKU entry fallback. Unknown barcodes prompt to create a
  new product (barcode pre-filled) or search manually.
- **Cart** — client-side state persisted to `localStorage` (survives an accidental
  reload), quantity steppers, per-line discounts, running subtotal/tax/total.
- **Checkout** — order-level discount/coupon, Cash (tendered/change) or Card/Other
  (via a swappable `PaymentGateway` contract, currently bound to an always-approve mock).
  Stock decrement + transaction insert happen inside one `DB::transaction()`.
- **Receipts** — print-friendly Blade view, downloadable PDF, Web Share API button.
- **Inventory** — stock levels, manual adjustments (restock/damage/correction), low-stock
  indicator.
- **Reports** — sales totals (today/week/month), 14-day trend, top-selling products,
  transaction history with reprint, CSV export.
- **Auth & roles** — PIN login (hashed with `Hash::make`), Cashier vs Admin enforced by
  middleware (`role:admin`) on the server, not just hidden UI. Idle sessions
  auto-logout (`SESSION_LIFETIME`, default 20 minutes).

## Local setup

```bash
composer install
cp .env.example .env   # then edit DB_* for your local MySQL
php artisan key:generate
npm install
npm run build           # or `npm run dev` while developing
```

Create a local MySQL database matching your `.env` `DB_DATABASE`, then:

```bash
php artisan migrate --seed
php artisan serve
```

Visit `http://127.0.0.1:8000` (redirects to `/scan`, which redirects to `/login` since
every business route requires auth).

### Running tests

```bash
php artisan test
```

Covers the cart total/tax/discount math (`CartCalculator`), the stock-decrement/rollback
logic (`StockService`), and the checkout flow end-to-end.

## Default seed data

- **Store settings**: currency LKR (`Rs.`), 0% default tax rate — editable at `/settings`
  as Admin, or directly in the `store_settings` table. Currency is a per-installation
  setting, not hardcoded, so any currency can be configured.
- **Users**:
  - Admin — PIN `1234`
  - Cashier — PIN `1111`
  - ⚠️ **Change these PINs (Settings → Manage users) before any real deployment.**
- **Categories**: Beverages, Snacks, Dairy & Eggs, Bakery, Household, Personal Care
- **Products**: 15 sample products with valid EAN-13 barcodes (prefix `89012345600xx`) —
  scan any of them with a real phone camera once deployed over HTTPS to test end-to-end.

## Project structure

- `app/Models` — `Product`, `Category`, `StoreSetting`, `Transaction`, `TransactionItem`,
  `StockAdjustment`, `User`
- `app/Http/Controllers` — one controller per feature area (`ScanController`,
  `CheckoutController`, `ReceiptController`, `InventoryController`, `ReportController`,
  `SettingsController`, `UserController`, `Auth/AuthController`)
- `app/Services` — `CartCalculator` (pure money math, unit-tested), `StockService`
  (row-locked stock decrement), `Services/Payments/PaymentGateway` contract +
  `MockPaymentGateway` (swap this binding in `AppServiceProvider` for a real gateway)
- `app/Http/Middleware/EnsureUserHasRole.php` — `role:admin` middleware
- `resources/js/pos/` — `cart.js` (Alpine store, localStorage-backed), `scanner.js`
  (camera + keyboard-wedge detection + beep), `scan-page.js`, `cart-page.js`,
  `checkout-page.js`, `login-page.js`, `idle-logout.js`, `printers/` (optional Bluetooth
  thermal printer stub, not wired in by default — see file header)
- `resources/views/components/layout.blade.php` — mobile-first shell with bottom nav
  (Scan/Cart always visible; Products/Reports/Settings admin-only)
- `public/manifest.json`, `public/sw.js`, `public/offline.html` — PWA shell. The service
  worker cache-firsts static build assets and icons, and only falls back to a static
  offline notice for page navigation — it deliberately does **not** cache product/price
  data, since stock and prices must always be fetched fresh.

## Deploying to Hostinger shared hosting

These steps assume a Hostinger plan with SSH + Composer access (Premium/Business shared
hosting), not a VPS.

### 1. Create the database

In hPanel: **Databases → MySQL Databases** → create a database, a user, and attach the
user to the database with all privileges. Note the database name, username, password, and
host (usually `localhost`).

### 2. Build frontend assets locally

Hostinger shared hosting won't run a persistent Node process, but a Vite production build
is just static files — build them on your machine before uploading:

```bash
npm install
npm run build
```

This produces `public/build/`, which you upload along with everything else.

### 3. Upload the project

Upload the whole project (excluding `node_modules/` and `.git/`) to your hosting account,
e.g. via SFTP or `git clone` over SSH if you're using version control. A common layout is
to put the project outside the public web root, e.g. `~/mobilepos-app/`, since the actual
document root handling is done in step 5.

### 4. Configure `.env`

SSH in, copy `.env.example` to `.env`, and set:

```
APP_NAME="Your Store Name"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_hostinger_db_name
DB_USERNAME=your_hostinger_db_user
DB_PASSWORD=your_hostinger_db_password
```

`APP_DEBUG=false` and a real `APP_KEY` are required for production — never deploy with
debug mode on, since it leaks stack traces (including config values) to visitors.

### 5. SSH commands

```bash
cd ~/mobilepos-app   # wherever you uploaded it

composer install --optimize-autoloader --no-dev
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`--no-dev` skips Pail/Sail/PHPUnit and other dev-only packages — they're not needed in
production and skipping them avoids installing anything shared hosting might not support.

### 6. Point the domain at `/public`

In hPanel, most shared hosting plans let you set a domain's **document root** to a
subfolder. Point it at `~/mobilepos-app/public`. If your specific plan doesn't expose that
option for the domain in question, use this standard workaround instead:

1. Move (or copy) everything **inside** `mobilepos-app/public/` up into the domain's actual
   web root folder (commonly `public_html/`) — so `public_html/index.php`,
   `public_html/build/`, `public_html/manifest.json`, etc.
2. Edit the two `require` lines in the relocated `public_html/index.php` to point at the
   real project location, since they're no longer one level above the app root:

   ```php
   // Before (assumes vendor/ and bootstrap/ are one level up):
   require __DIR__.'/../vendor/autoload.php';
   $app = require_once __DIR__.'/../bootstrap/app.php';

   // After (adjust the path to wherever mobilepos-app actually lives):
   require __DIR__.'/../mobilepos-app/vendor/autoload.php';
   $app = require_once __DIR__.'/../mobilepos-app/bootstrap/app.php';
   ```

Either approach works; the document-root option is preferred when available since it
keeps the app code outside the public web root entirely.

### 7. Enable free SSL

In hPanel: **SSL** → enable the free SSL certificate for the domain. **This is required**
— camera access (`getUserMedia`) is blocked by browsers on non-HTTPS origins, so barcode
scanning will not work until SSL is active.

### 8. Test on a real phone

Once SSL is live:

1. Visit `https://yourdomain.com` on a phone browser.
2. Log in with the seeded Admin/Cashier PINs (then change them immediately via Settings).
3. Go to **Scan**, grant camera permission when prompted, and scan one of the seeded
   products' barcodes (any EAN-13 starting `89012345600`) or scan a real product's barcode
   — it'll show the unknown-barcode prompt if it's not in the catalog.
4. Optionally add the site to the home screen (share/menu → "Add to Home Screen") to test
   the PWA install.

If the camera doesn't start, check: SSL is actually active (padlock in the address bar),
and that camera permission wasn't previously denied for the site (browser site settings →
Camera). A denied permission is handled gracefully in-app with instructions to re-enable it.

## Known extension points (not built, deliberately)

- **Real payment gateway**: bind your provider's implementation of
  `App\Services\Payments\PaymentGateway` in `AppServiceProvider::register()` in place of
  `MockPaymentGateway` — no controller or view changes needed.
- **Bluetooth thermal receipt printer**: `resources/js/pos/printers/bluetooth-receipt-printer.js`
  has a documented Web Bluetooth stub. Not wired into the receipt UI by default since the
  phone's own print/share dialog covers most cashiers without any pairing step.
- **Product images**: the `products.image_path` column exists, but there's no upload UI
  yet — add a file input to the product form and store it on the `public` disk if needed.

## Phase status

- [x] Phase 1 — Project setup, schema, product catalog
- [x] Phase 2 — Barcode scanning + cart
- [x] Phase 3 — Checkout & payments
- [x] Phase 4 — Receipts
- [x] Phase 5 — Inventory & reporting
- [x] Phase 6 — Auth & roles
