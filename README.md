# 📚 E-Book — Online Book Store

A full-stack e-commerce bookstore built with **Laravel 13**, **MySQL**, **Tailwind CSS**, and vanilla JS/AJAX. It runs entirely in Docker via **Laravel Sail** — no local PHP, Composer, or Node required.

## Features

- **Authentication & roles** — customers and admins (Laravel Breeze).
- **Catalogue** — search, category filters, sorting, ratings, and **secure PDF previews** (served from a private disk).
- **Shopping cart** — database-backed, works for guests, and **merges into the account on login**.
- **Checkout** — a professional flow with a **sandbox QR payment gateway** (HMAC-signed), a **simulate-approval** step, and a downloadable **PDF receipt**.
- **Reviews & wishlist** — verified-purchase reviews and an AJAX wishlist.
- **Admin** — dashboard, full book/category CRUD (with cover & preview uploads), and order management.

## Requirements

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Linux containers). That's it.

## Quick start

```bash
# 1. Copy the environment file
cp .env.example .env        # Windows: copy .env.example .env

# 2. Install PHP dependencies using a throwaway container (no local PHP needed)
docker run --rm -v "${PWD}:/var/www/html" -w /var/www/html \
  laravelsail/php84-composer:latest composer install

# 3. Start the containers (app + MySQL)
./vendor/bin/sail up -d

# 4. App key, migrations, demo data, and front-end build
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan storage:link
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

The app is now at **http://localhost**.

### Windows (PowerShell) without WSL

Sail's `sail` script assumes a Unix shell. On Windows you can drive Compose directly:

```powershell
$env:WWWGROUP="1000"; $env:WWWUSER="1000"
docker compose -f compose.yaml up -d
docker compose -f compose.yaml exec laravel.test php artisan migrate --seed
docker compose -f compose.yaml exec laravel.test php artisan storage:link
docker compose -f compose.yaml exec laravel.test npm run build
```

## Demo accounts

| Role     | Email                  | Password   |
| -------- | ---------------------- | ---------- |
| Admin    | `admin@ebook.test`     | `password` |
| Customer | `customer@ebook.test`  | `password` |

## Trying the checkout

1. Log in as the customer, add books to the cart, and go to **Checkout**.
2. Enter shipping details → you'll get a **QR code** and amount.
3. Click **Simulate payment approval** → you're taken to a success page.
4. **View receipt** or **Download PDF**, and find the order under **My orders**.

> The payment is a sandbox: no real money moves. The QR encodes an HMAC-signed
> payload that is verified server-side, demonstrating the real-world pattern.

## Running the tests

```bash
./vendor/bin/sail artisan test
```

Feature tests cover the critical money path (cart stock rules, order creation &
snapshotting, payment confirmation, signature verification, idempotency, and
admin authorization).

## Architecture

```
app/
├── Http/Controllers/        Public + Admin/ controllers
├── Models/                  Eloquent models (money stored as integer cents)
├── Services/
│   ├── CartService          cart resolution, stock-safe mutations, guest merge
│   ├── CheckoutService      atomic order creation, stock locking, snapshotting
│   ├── ReceiptService       PDF receipt generation
│   └── Payment/             PaymentGateway interface + SandboxGateway
├── Policies/OrderPolicy     owner-only order/receipt access
└── Http/Middleware/         role gate, wishlist sharing
```

### Key design decisions

- **Money is stored as integer cents**, never floats — no rounding bugs.
- **Order line items are snapshotted** (title + price copied at purchase) so
  editing a book later never rewrites historical invoices.
- **Stock is decremented under `lockForUpdate`** inside a transaction to prevent
  overselling the last copy.
- **The payment gateway sits behind an interface** — going live means writing
  one new implementation (e.g. Stripe) and changing a single binding.

## Going to production

Swap the sandbox gateway, set a strong `APP_KEY`, configure a real mail driver
for order confirmations, and serve over HTTPS. Build assets with
`npm run build` and run `php artisan config:cache route:cache`.
