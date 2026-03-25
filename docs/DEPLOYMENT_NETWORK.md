# Smart Shopping System

## Network and deployment guide

This guide describes how the application is accessed **on the internet (production)** and **on a local network (LAN)**. Use it for documentation, handoffs, or presentations.

Readers: administrators, developers, and anyone supporting **dashboard** or **POS tablet** use in store or office networks.

---

## Production URL

Set the Laravel base URL to:

**https://smart-shop-with-barcode.shop**

In `.env`, use `APP_URL=https://smart-shop-with-barcode.shop`. For a live site, also use `APP_ENV=production` and `APP_DEBUG=false`.

---

## Production vs. LAN — quick comparison

**Production (Hostinger, public internet)**

- Users connect over the public internet.
- Address: **https://smart-shop-with-barcode.shop**
- Connection is secured with **HTTPS** (SSL on the VPS, e.g. Let’s Encrypt).
- Database: **MySQL** on the VPS or Hostinger managed MySQL.
- Payment webhooks (e.g. HitPay) need **public HTTPS** URLs on this same domain.

**LAN (office or store Wi‑Fi)**

- Tablets and phones use the **same Wi‑Fi** as the computer that runs the app.
- Address: **http://YOUR-LAN-IP:PORT**  
  Example: `http://192.168.1.13:18000`. The correct IP and port are printed when you run **`./lan-start`**.
- Often **HTTP** for local testing; HTTPS is optional.
- Database: **MySQL** on that same computer (or as configured in `.env`).
- **LAN-only URLs cannot receive internet webhooks.** Use payment **sandbox** + a tunnel, or point webhooks at **production** (`smart-shop-with-barcode.shop`).

---

## Production: Hostinger (domain and VPS)

**Summary:** The domain **smart-shop-with-barcode.shop** is pointed to a **Hostinger VPS** with DNS. The VPS runs Laravel. The website root folder must be the **`public`** directory inside the project—not the whole repository.

**Domain and DNS**

- Manage DNS in **Hostinger hPanel** (or point the domain’s nameservers to Hostinger).
- Create an **A record** so the root name (`@`) points to the **VPS public IPv4** address.
- Optionally add **www** (A or CNAME) so **www.smart-shop-with-barcode.shop** works the same way.
- Optionally add **AAAA** records if you use IPv6.

**Web server and Laravel**

- Use **nginx** or **Apache** on the VPS.
- Set the site **document root** to:  
  **`…/smart_shopping_system/public`**
- Use **PHP 8.1 or newer** (see `composer.json`).
- On deploy, run migrations and cache config/routes as needed; keep **`.env` only on the server** (never commit secrets).

**Environment variables (examples)**

- `APP_URL=https://smart-shop-with-barcode.shop`
- For HitPay (or similar), redirect and webhook URLs should use HTTPS and the same host, for example:
  - `HITPAY_REDIRECT_URL=https://smart-shop-with-barcode.shop/payments/redirect`
  - `HITPAY_WEBHOOK_URL=https://smart-shop-with-barcode.shop/payments/hitpay/webhook`  
  Exact paths may vary; URLs must be **HTTPS** and reachable from the payment provider’s servers.

**Database**

- Run **MySQL** or **MariaDB** on the VPS (`DB_HOST=127.0.0.1`) or use a Hostinger database hostname.
- Store database user and password only in **`.env`**.

**Why a VPS**

- A **VPS** gives SSH, full control of the web server, **cron**, and queues—typical needs for Laravel.
- **Shared hosting alone** often limits shell access, long-running tasks, or custom server layout.

---

## LAN: local server and POS devices

**Summary:** One machine runs Laravel and MySQL. The app is started so it listens on **all network interfaces** (`0.0.0.0`), not only localhost. Other devices open the app using the computer’s **LAN IP** and **port**.

**How it works**

- Start with: `php artisan serve --host=0.0.0.0 --port=18000` (port can be changed).
- On a tablet or phone, open a browser to **http://YOUR-LAN-IP:18000** (use the IP shown by `./lan-start`).
- **`./lan-start`** prints the correct URL for your network and starts the server.
- All devices using that server share **one database** on the host.

**Network and security**

- Put all devices on the **same Wi‑Fi** (or VLAN). Avoid **guest Wi‑Fi** or routers with **client isolation**, which block device-to-device access.
- If the OS firewall asks to allow **PHP** or the dev server, **allow** it for LAN testing.
- **HTTP on LAN** is only appropriate on a **trusted** network. Do not expose the dev server to the whole internet without proper HTTPS and hardening.

**Repository scripts**

- **`./setup`** — Installs dependencies, prepares `.env`, runs migrations and builds assets (first-time setup).
- **`./lan-start [port]`** — Shows your LAN URL and binds the server to `0.0.0.0`.

---

## Browser app and API (CORS)

- API **CORS** rules are in **`config/cors.php`**.
- The POS interface loads from the **same website address** as the Laravel app (production URL or LAN URL), which keeps normal browser security rules simple for that site.

---

## Security checklist

- **Production:** Enforce **HTTPS**, use strong database passwords, turn off debug mode, keep server and Laravel updated.
- **LAN:** Treat HTTP as **internal only**; do not rely on it for public exposure.
- **Secrets:** Keep **`.env`** on servers and developer machines; do not commit it to Git.

---

## Three-point summary

1. **Live site:** **https://smart-shop-with-barcode.shop** on Hostinger (**DNS → VPS**, site root = **`public/`**).
2. **Local store / lab:** Same app at **http://YOUR-LAN-IP:PORT** with tablets on the **same Wi‑Fi**.
3. **Payments:** Live provider **redirect and webhook URLs** must be **public HTTPS** on the production domain.
