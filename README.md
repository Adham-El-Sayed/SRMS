# SRMS — Smart Restaurant Management System

A Laravel web app that runs a restaurant floor end to end: guests scan a QR code on their table and order from their phone, the kitchen works through orders on a live board, and managers look after the menu, tables, cash, shifts and monthly reports. The whole interface works in **English and Arabic** (full right-to-left layout).

![Restaurant dashboard](docs/screenshots/dashboard.jpg)

---

## Contents

- [Features](#features)
- [Screenshots](#screenshots)
- [Tech stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Accounts and access](#accounts-and-access)
- [How it works](#how-it-works)
- [Languages](#languages)
- [Project structure](#project-structure)
- [Troubleshooting](#troubleshooting)

---

## Features

### For guests (no account needed)

- **QR menu per table.** Every table has its own QR code that opens the menu already tied to that table.
- **Order from the phone.** Add items, change quantities, add a note per item ("no onions"), and leave a name and phone number if you want.
- **Pay by cash or card.** Card payment is a built-in simulation. See [Payments](#payments).
- **Edit after ordering.** Guests can change a submitted order for **2½ minutes**. After that, a **Request Waiter** button alerts the staff instead.

### For the kitchen

- **Live order board.** Every open order shows its table, items, notes and total.
- **One-tap status changes.** Move each order through `Pending → Confirmed → Preparing → Ready → Completed`, or cancel it before it's served.
- **Live board with a chime.** New orders appear on their own, without reloading. A chime plays and a notice pops up, and the **Kitchen** tab shows how many orders are open.

### For managers (admin)

- **Dashboard.** Order counts, completed revenue, table status and the latest orders at a glance.
- **Tables.** Create tables, set capacity and status, and print each table's QR code.
- **Menu management.** Categories and products with photos, descriptions and prices. Switch any item on or off without deleting it.
- **Waiter alerts.** Guest requests appear under *Alerts* the moment they're made, with their own sound and a count in the navigation.
- **Payments.** Staff confirm each payment once the money is actually in hand (cash in the drawer, or a card on the terminal) and can print an invoice. Nothing counts as paid until then.
- **Shifts.** Open a shift, then close it by entering the cash you counted. The system compares that with the cash it recorded and flags any **surplus** or **shortage**. A closed shift's figures are frozen, so they never change afterwards. Past shifts can be browsed and exported to Excel.
- **Reports.** A monthly report with revenue split by cash and card, average order value and the top 5 products, downloadable as PDF. Orders for any date range can also be exported to Excel.

---

## Screenshots

| Guest menu (phone) | Guest menu — Arabic |
|---|---|
| <img src="docs/screenshots/guest-menu-phone.jpg" width="300" alt="Guest menu on a phone"> | <img src="docs/screenshots/guest-menu-ar.jpg" alt="Guest menu in Arabic"> |

**Kitchen board**

![Kitchen orders](docs/screenshots/kitchen.jpg)

**Menu management**

![Menu management](docs/screenshots/menu-management.jpg)

**Tables**

![Tables](docs/screenshots/tables.jpg)

<details>
<summary>More screenshots</summary>

**Landing page**

![Landing page](docs/screenshots/landing.jpg)

**Sign in**

![Sign in](docs/screenshots/login.jpg)

**Guest menu (desktop)**

![Guest menu](docs/screenshots/guest-menu.jpg)

**Kitchen board — Arabic**

![Kitchen in Arabic](docs/screenshots/kitchen-ar.jpg)

</details>

---

## Tech stack

| Layer | Used |
|---|---|
| Backend | Laravel 13, PHP 8.3+ |
| Database | MySQL / MariaDB |
| Auth | Laravel Breeze |
| Roles | spatie/laravel-permission (`super-admin`, `admin`, `cashier`, `kitchen`) |
| QR codes | simplesoftwareio/simple-qrcode |
| PDF reports | barryvdh/laravel-dompdf |
| Excel exports | maatwebsite/excel |
| Front end | Blade, vanilla JavaScript, Vite, Tailwind (sign-in and profile pages only) |
| Styling | A custom design system in `public/css/srms-theme.css` |

---

## Requirements

- **PHP 8.3 or newer** with the `gd`, `zip`, `mbstring`, `intl`, `fileinfo`, `openssl` and `pdo_mysql` extensions
- **Composer**
- **Node.js 18+** and npm
- **MySQL 8 or MariaDB 10.4+** (XAMPP's MySQL works)

> **Use MySQL, not SQLite.** One migration changes the order-status column with MySQL-specific SQL, and SQLite also blocks the `confirmed` status, so orders can't move through the kitchen on SQLite.

---

## Installation

```bash
git clone https://github.com/Adham-El-Sayed/SRMS.git
cd SRMS

composer install
npm install
npm run build

cp .env.example .env          # Windows CMD: copy .env.example .env
php artisan key:generate
```

Create an empty database (for example `srms`), then set these lines in `.env`:

```ini
APP_NAME=SRMS
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=srms
DB_USERNAME=root
DB_PASSWORD=
```

Then build the tables, link the image folder and start the server:

```bash
php artisan migrate
php artisan storage:link
php artisan serve
```

Open **http://127.0.0.1:8000**.

For development with hot reload, run `composer dev` instead of `php artisan serve`. It starts the server, queue worker, log viewer and Vite together.

---

## Accounts and access

Every staff page requires signing in, and what each account may open depends on its role.

| Role | Can open |
|---|---|
| **super-admin** | Everything, and switches between the admin, cashier and kitchen views |
| **admin** | Menu, tables, alerts, payments, shifts, reports and staff |
| **cashier** | Payments and shifts — the money side |
| **kitchen** | The kitchen board |
| *no role yet* | Nothing — a "waiting for access" page until a manager sets the role |

The navigation only shows what the signed-in account can actually open, and a
page that isn't theirs is refused by the server as well, not just hidden.
Signing in lands each account where it works: the kitchen on its board, the
cashier on payments, an admin on the dashboard.

### The super admin's workspace switcher

A super admin may do everything, which would mean every page at once. So the
top bar offers **Admin / Cashier / Kitchen**: picking one decides what the
navigation shows and where signing in lands. It is a convenience, not a
restriction — a super admin is never refused a page, whichever view is active.

### The first administrator

```bash
php artisan srms:admin
```

It asks for a name, email and password and creates a **super admin** — the
owner account. Pass an email to promote an existing account instead, and
`--role` to make any other kind of account:

```bash
php artisan srms:admin someone@example.com            # make them super admin
php artisan srms:admin chef@example.com --role=kitchen
php artisan srms:admin till@example.com --role=cashier
```

### Everyone else

The administrator adds accounts from **Staff & Access** in the navigation: add
an account, or change anyone's role from the dropdown next to their name. An
administrator can't remove their own access or delete their own account there,
so the restaurant can never be left without an administrator. Only a super
admin can create another super admin, or change one.

New accounts made through public sign-up start with no role and can't reach
anything until a manager gives them one. Once your staff accounts exist, you
can switch public sign-up off entirely with `APP_REGISTRATION=false` in `.env`.

---

## How it works

### The order journey

1. An admin creates a table. SRMS gives it a unique token and a QR code (**Tables → QR Code**, ready to print).
2. A guest scans it and lands on `/menu/{token}`, a menu that already knows which table they're at.
3. The guest submits the order. It appears on the **Kitchen** board as *Pending*.
4. The kitchen moves it to *Confirmed*, then *Preparing*, then *Ready*, and finally *Completed* once it's served.
5. Cash orders show up under **Cash Payments**, where staff confirm the money was received and can print an invoice.
6. Everything lands in the current **Shift** and in the **Reports**.

### Editing and waiter requests

After submitting, the guest sees a countdown and can change the order for 150 seconds, as long as the kitchen hasn't confirmed it yet. The limit is `OrderService::EDIT_WINDOW_SECONDS` in `app/Services/OrderService.php`. Once the kitchen starts, or the time runs out, **Request Waiter** creates an alert that staff handle from the **Alerts** page.

Guests at a table can keep ordering during the meal (dessert, another drink). The table shows as occupied from the first order and becomes available again once every order there is completed or cancelled.

### Payments

Card payment on the menu is **a simulation**. The card form checks the number, expiry and CVV, then waits briefly and reports success. The camera "scan" fills in a test card. No money is charged.

Because of that, the server never takes the page's word for a payment. Every order, cash or card, starts **unpaid** and appears under **Payments** until staff confirm the money was received. Confirming needs an open shift, and the payment is booked to that shift. To take real card payments, replace the marked block in `resources/views/menu/index.blade.php` (search for *"Replace this block with a real gateway call"*) with a payment provider, and mark the order paid from that provider's server-side confirmation.

### Live updates and sound

Staff pages check with the server every 5 seconds. The Kitchen, Alerts and Payments pages refresh their lists in place, and the navigation shows live counts. A new order plays a two-note chime and a waiter call plays three short beeps, with a pop-up notice either way.

The bell in the top bar turns sound on or off. Browsers only allow a page to play sound after someone has clicked it, so while the bell shows an orange dot, click anywhere on the page once. If the session ends, the page takes you back to sign in.

### Guest API

The QR menu talks to a small JSON API in `routes/api.php`:

| Method | Endpoint | Purpose |
|---|---|---|
| `POST` | `/api/orders` | Place an order (needs the table's QR token) |
| `GET` | `/api/orders/{order}` | Read your order |
| `PUT` | `/api/orders/{order}/items` | Change items during the edit window |
| `POST` | `/api/orders/{order}/request-help` | Ask for a waiter |

Placing an order returns an `order_token`, which is sent only once. Every other call must send it back in an `X-Order-Token` header. Without it, the order doesn't exist as far as the API is concerned (404). There's no endpoint for listing orders or changing their status: that's staff work, done behind the login.

---

## Security

What protects the system:

- **Guests** can reach a table only through its QR token, and an order only through its own secret token. Order numbers can't be guessed into.
- **Prices, totals and payment status** are always decided by the server. Whatever the page sends for them is ignored.
- **Roles**: `admin` sees everything, and `kitchen` sees the dashboard and the kitchen board. An account with no role reaches nothing but its own profile.
- **Every form and staff action** carries Laravel's CSRF token.
- **Rate limits**: 10 orders a minute and 60 other guest requests a minute per device, 5 sign-ups a minute, and Laravel's standard sign-in lockout after repeated failures.
- **Excel exports** store guest-typed text as plain text, so a name like `=HYPERLINK(...)` can't run as a formula on the manager's computer.
- **Response headers** block the pages from being framed by other sites, stop content-type guessing and keep QR links out of referrers.

Before putting it on the internet:

1. In `.env`, set `APP_ENV=production` and `APP_DEBUG=false`. Debug mode shows code and settings on every error page.
2. Serve it over **HTTPS**, and set `SESSION_SECURE_COOKIE=true` and `SESSION_ENCRYPT=true`.
3. Once your staff accounts exist, set `APP_REGISTRATION=false` to close public sign-up.
4. Connect a real card payment provider before accepting cards (see [Payments](#payments)).

---

## Languages

Every page, message and button is available in English and Arabic. The **EN / ع** switch in the top bar changes the language, and Arabic flips the whole layout to right-to-left. The choice is kept for the visitor's session.

- Arabic text lives in **`lang/ar.json`**, one entry per English phrase. Edit a line there to change a wording.
- New text in a Blade view should be wrapped as `{{ __('Your text') }}`. In page scripts, use `__t('Your text')`. Then add the Arabic line to `lang/ar.json`.
- Menu content (category and product names and descriptions) is stored in the database as typed, so it isn't translated automatically.
- The monthly **PDF** stays in English, because the PDF library can't lay out Arabic letters correctly.

---

## Project structure

```
app/
├── Enums/                   Table statuses
├── Http/Controllers/        One controller per area (tables, kitchen, shifts, reports…)
├── Http/Middleware/SetLocale.php   Applies the chosen language
├── Models/                  Order, OrderItem, Product, Category, RestaurantTable, Shift…
└── Services/                Business logic: OrderService (ordering rules, edit window), MenuService, ShiftService…
lang/ar.json                 Arabic translations
public/css/srms-theme.css    Colours, type, and every shared component style
resources/views/
├── layouts/                 app (staff), menu (guests), guest (sign-in)
├── menu/                    The QR ordering page
├── kitchen/  tables/  menu-management/  cash-payments/
├── shifts/   reports/  order-change-requests/  invoices/
routes/web.php               Pages, grouped by role
routes/api.php               The guest ordering API
```

The look is controlled from one place. Colours, fonts, corner radius and shadows are CSS variables at the top of `public/css/srms-theme.css`, so changing `--accent` recolours the whole app.

---

## Troubleshooting

**`Your Composer dependencies require a PHP version ">= 8.3.0"`**
Your terminal is using an older PHP (often XAMPP's 8.2). Check with `php -v`, and put a PHP 8.3+ folder ahead of XAMPP's in your system `PATH`.

**`requires ext-gd * -> it is missing from your system`**
Open the `php.ini` shown by `php --ini` and remove the `;` in front of `extension=gd` (also `zip` and `intl` if they're listed as missing).

**`SQLSTATE[HY000]: General error: 1 near "MODIFY": syntax error`**
The app is pointed at SQLite. Switch `.env` to MySQL as shown in [Installation](#installation).

**`Access denied for user 'root'@'localhost'`**
`DB_PASSWORD` in `.env` doesn't match your MySQL. XAMPP's default root password is empty, so leave the line as `DB_PASSWORD=`.

**Product photos don't show**
Run `php artisan storage:link`. If you copied the project from another machine, also copy its `storage/app/public` folder, because uploaded photos aren't stored in Git.

**A page still shows the old design after pulling changes**
Run `php artisan view:clear` and reload with Ctrl+F5.
