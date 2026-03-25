## Quick Setup

After cloning the repository, run:

```bash
chmod +x setup
./setup
```

What `./setup` does:

- Installs required system tools on macOS or apt-based Linux: PHP 8.1, Composer, Node.js 18, and MySQL.
- Creates `.env` from `.env.example` if needed.
- Installs Composer and npm dependencies.
- Generates the Laravel app key and creates the storage symlink.
- Tries to create the MySQL database and run migrations.
- Builds frontend assets.

Optional flags:

- `./setup --import-sql` to also import `database/pos.sql`.
- `./setup --skip-system-packages` if the machine already has PHP, Composer, Node, npm, and MySQL installed.
- `./setup --skip-migrations` if you want to handle the database manually.

After setup, start the app with:

```bash
php artisan serve
npm run watch
```

If MySQL credentials on the new machine are different, update `.env` and rerun `php artisan migrate`.

## LAN Setup (MacBook + iPad/Tablet)

Use this when you want the dashboard on MacBook and POS on iPad/tablet, sharing one database.

1. On MacBook, run setup once:

```bash
./setup --skip-system-packages
```

2. Start LAN server from the project root:

```bash
chmod +x lan-start
./lan-start 18000
```

3. On both devices (same Wi-Fi), open the URL shown by the script, for example:

```text
http://192.168.1.25:18000
```

Notes:
- Both dashboard and POS use the same backend instance running on the MacBook.
- Because it is one backend, both devices are automatically connected to the same MySQL database configured in MacBook `.env`.
- If macOS firewall prompts for PHP access, allow incoming connections.

### Further reading

- [Network deployment (hosted vs LAN)](docs/DEPLOYMENT_NETWORK.md) — DNS, TLS, `0.0.0.0` bind, webhooks, firewall.
- [Sales and inventory reports](docs/REPORTS_AND_INVENTORY.md) — UI locations, API routes, POS stock notifications.

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
