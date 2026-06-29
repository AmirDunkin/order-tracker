# IIS + XAMPP Setup (Windows)

Typical local setup on this machine:

| Service | Port | Document root |
|---------|------|----------------|
| **IIS** | 80 | `C:\inetpub\wwwroot\` (default) |
| **XAMPP Apache** | **8080** | `D:\xampp\htdocs\` |

**For OrderTrack development, use XAMPP Apache on port 8080.**

---

## ✅ Recommended — XAMPP Apache (port 8080)

1. XAMPP Control Panel → **Start Apache** (port 8080)
2. Open:

```
http://localhost:8080/order-tracker/public/login
```

3. Customer orders:

```
http://localhost:8080/order-tracker/public/customer/orders
```

No IIS changes needed. The app auto-detects `localhost:8080` for links and redirects.

---

## IIS (port 80) — only if you want to use IIS instead

Your 404 happened because IIS looked at:

```
C:\inetpub\wwwroot\order-tracker\public\customer\orders   ❌ wrong drive/path
```

Project files are at:

```
D:\xampp\htdocs\order-tracker\public   ✅ correct
```

### Fix IIS physical path

1. Install **IIS URL Rewrite Module**: https://www.iis.net/downloads/microsoft/url-rewrite
2. IIS Manager → Sites → your site → **Basic Settings**
3. Set **Physical path** to: `D:\xampp\htdocs\order-tracker\public`
4. Ensure `D:\xampp\htdocs\order-tracker\public\web.config` exists (included in project)
5. Configure PHP FastCGI for IIS (or use Apache on 8080 instead)

### IIS URLs (after path fix)

| Page | URL |
|------|-----|
| Login | `http://localhost/order-tracker/login` |
| Customer orders | `http://localhost/order-tracker/customer/orders` |

> When IIS document root = `public` folder, URLs do **not** include `/public`.

---

## Port summary

| What you want | URL |
|---------------|-----|
| **XAMPP Apache (use this)** | `http://localhost:8080/order-tracker/public/login` |
| IIS (port 80, path fixed) | `http://localhost/order-tracker/login` |
| ❌ Wrong (IIS + old inetpub path) | `http://localhost/order-tracker/public/...` → 404 |

---

## Demo accounts

Password for all: **Password123!**

| Email | Role |
|-------|------|
| alice@example.com | Customer |
| bob@example.com | Customer |
| sarah@example.com | Personal Shopper |
