# OrderTrack

**PWA order tracking system** for customers and personal shoppers.

PHP 8.2 MVC application with customer order submission, personal shopper fulfilment, session-based auth, and progressive web app (PWA) support.

---

## URL structure

### Local development — XAMPP Apache (port **8080**)

Use this URL when IIS is on port 80 and XAMPP Apache is on port **8080**:

| Page | URL |
|------|-----|
| Home (redirects to login) | `http://localhost:8080/order-tracker/public/` |
| Login | `http://localhost:8080/order-tracker/public/login` |
| Register | `http://localhost:8080/order-tracker/public/register` |
| Customer — My Orders | `http://localhost:8080/order-tracker/public/customer/orders` |
| Customer — New Order | `http://localhost:8080/order-tracker/public/customer/orders/create` |
| Customer — Order Detail | `http://localhost:8080/order-tracker/public/customer/orders/{id}` |
| Shopper — Dashboard | `http://localhost:8080/order-tracker/public/shopper/dashboard` |
| Shopper — All Orders | `http://localhost:8080/order-tracker/public/shopper/orders` |
| Shopper — Manage Order | `http://localhost:8080/order-tracker/public/shopper/orders/{id}` |
| API — AI Suggestion | `POST http://localhost:8080/order-tracker/public/api/ai-suggest` |

> The app auto-detects `localhost:8080` from your browser address bar. No config change needed if you always open the app via port 8080.

### Local development — IIS (port **80**)

Only if IIS physical path is set to `D:\xampp\htdocs\order-tracker\public` (see `docs/IIS-SETUP.md`):

| Page | URL |
|------|-----|
| Login | `http://localhost/order-tracker/login` |

*(No `/public` in the URL when IIS document root is already the `public` folder.)*

### Production (cPanel subdomain example)

Replace `order-tracker.yourdomain.com` with your actual subdomain:

| Page | URL |
|------|-----|
| Login | `https://order-tracker.yourdomain.com/login` |
| Customer orders | `https://order-tracker.yourdomain.com/customer/orders` |
| Shopper dashboard | `https://order-tracker.yourdomain.com/shopper/dashboard` |

> **Recommended:** Point the subdomain **document root** to the `public/` folder so URLs do not include `/public`.

---

## Demo accounts

**Password for all accounts:** `Password123!`

| Name | Email | Role | Login as | After login |
|------|-------|------|----------|-------------|
| Alice Johnson | `alice@example.com` | Customer | Customer | `/customer/orders` |
| Bob Smith | `bob@example.com` | Customer | Customer | `/customer/orders` |
| Sarah Shopper | `sarah@example.com` | Personal Shopper | Personal Shopper | `/shopper/dashboard` |

On the login page, select the matching **role** (Customer or Personal Shopper) before signing in.

---

## Requirements

- PHP **8.1+** (8.2 recommended)
- MySQL **5.7+** or MariaDB **10.3+**
- Apache with `mod_rewrite` enabled
- PHP extensions: `pdo_mysql`, `json`, `mbstring`, `curl` (optional, for OpenAI)

---

## Local setup (XAMPP)

### 1. Clone / copy project

Place the project in your web root, e.g. `C:\xampp\htdocs\order-tracker`

### 2. Import database

**Option A — phpMyAdmin**

1. Open `http://localhost/phpmyadmin`
2. Create database `order_tracker` (utf8mb4_unicode_ci)
3. Import `database/order_tracker.sql`

**Option B — command line**

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS order_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root order_tracker < database/order_tracker.sql
```

### 3. Configure database (if needed)

Edit `config/database.php` — local defaults:

- Host: `127.0.0.1`
- Database: `order_tracker`
- User: `root`
- Password: *(empty)*

Environment is auto-detected as **local** when host is `localhost`.

### 4. Open the app

Visit: **http://localhost:8080/order-tracker/public/login**

> **Note:** If IIS uses port 80 and XAMPP Apache uses port **8080**, always use `:8080` in the URL for local development.

---

## cPanel deployment checklist

Use this checklist when deploying to shared hosting (e.g. `order-tracker.yourdomain.com`).

### Pre-upload

- [ ] Confirm hosting supports **PHP 8.1+** (Select PHP Version in cPanel)
- [ ] Enable extensions: `pdo_mysql`, `json`, `mbstring`
- [ ] Ensure `mod_rewrite` is enabled (usually on by default)

### Database

- [ ] cPanel → **MySQL Databases** → Create database (e.g. `cpaneluser_order_tracker`)
- [ ] Create MySQL user with a strong password
- [ ] Add user to database with **ALL PRIVILEGES**
- [ ] phpMyAdmin → select database → **Import** → `database/order_tracker.sql`
- [ ] Verify tables: `users`, `orders`, `order_status_logs`

### Upload files

- [ ] Upload entire `order-tracker` folder via File Manager or FTP
- [ ] Recommended layout on server:
  ```
  /home/username/order-tracker/
    app/
    config/
    core/
    database/
    public/        ← document root should point here
    ...
  ```

### Subdomain / document root

- [ ] cPanel → **Domains** → **Subdomains** (or Addon Domain)
- [ ] Create `order-tracker.yourdomain.com`
- [ ] Set **Document Root** to `/home/username/order-tracker/public`
- [ ] Do **not** expose `app/`, `config/`, or `core/` as web-accessible

### Configuration

- [ ] Edit `config/database.php` production values **OR** set environment variables in `public/.htaccess`:

```apache
<IfModule mod_env.c>
    SetEnv APP_ENV production
    SetEnv APP_URL https://order-tracker.yourdomain.com
    SetEnv DB_HOST localhost
    SetEnv DB_DATABASE cpaneluser_order_tracker
    SetEnv DB_USERNAME cpaneluser_dbuser
    SetEnv DB_PASSWORD your_secure_password
</IfModule>
```

- [ ] Uncomment **Force HTTPS** rules in `public/.htaccess` when SSL is active
- [ ] Set `APP_URL` to your exact public URL (no trailing slash)

### Post-deploy verification

- [ ] Visit `https://order-tracker.yourdomain.com/login` — page loads with styling
- [ ] Log in as `sarah@example.com` / `Password123!` (role: Personal Shopper)
- [ ] Log in as `alice@example.com` / `Password123!` (role: Customer)
- [ ] Create a test order as customer; update status as shopper
- [ ] Check PWA manifest: `https://order-tracker.yourdomain.com/manifest.json`
- [ ] Service worker registers (DevTools → Application → Service Workers)
- [ ] **Change or remove demo passwords before go-live**

### Optional

- [ ] Set `OPENAI_API_KEY` for AI shopping tips (see `.env.example`)
- [ ] Install SSL certificate (Let's Encrypt via cPanel)
- [ ] Set timezone: `SetEnv APP_TIMEZONE Asia/Kuala_Lumpur`

---

## Environment detection

| Setting | Local | Production |
|---------|-------|------------|
| Detection | `localhost` / `127.0.0.1` host | Any other hostname |
| Override | `SetEnv APP_ENV local` | `SetEnv APP_ENV production` |
| Debug mode | ON | OFF |
| Base URL | Auto-detected (`:8080` for XAMPP) or `http://localhost:8080/order-tracker/public` | Auto-detected from request, or `APP_URL` |

Files: `config/env.php`, `config/config.php`, `config/database.php`

---

## Project structure

```
order-tracker/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   └── Views/
├── config/
│   ├── config.php
│   ├── database.php
│   ├── env.php
│   └── routes.php
├── core/
├── database/
│   ├── order_tracker.sql    ← import this on cPanel
│   └── schema.sql           ← local dev (includes CREATE DATABASE)
├── public/                  ← web document root
│   ├── index.php
│   ├── .htaccess
│   ├── css/, js/, icons/
│   ├── manifest.json
│   └── sw.js
└── .htaccess                ← fallback if doc root is project root
```

---

## Security notes (production)

1. Change all demo account passwords before go-live
2. Set `debug` off in production (automatic when not on localhost)
3. Use HTTPS and uncomment force-SSL in `public/.htaccess`
4. Never commit real database passwords — use `SetEnv` or server env vars
5. Keep `app/`, `config/`, `core/`, and `database/` outside the document root

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 404 on all pages | Enable `mod_rewrite`; confirm `.htaccess` in `public/`; check document root |
| Database connection error | Verify cPanel DB name/user/password; host is usually `localhost` |
| CSS/JS not loading | Check `APP_URL` matches your live URL; clear browser cache |
| Redirect loop | Ensure `APP_URL` uses `https://` when SSL is enabled |
| Blank page | Enable `display_errors` temporarily; check PHP error log in cPanel |
| PWA not installing | Site must be served over HTTPS (except localhost) |

---

## License

Internal use — OrderTrack
