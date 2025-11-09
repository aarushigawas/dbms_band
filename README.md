# Band Management System (XAMPP / PHP + MySQL)

A minimal, XAMPP-ready starter for managing bands, venues and performances.

## Requirements
- XAMPP (Apache + MySQL)
- PHP 7.4+ (works with XAMPP default)

## Install
1) Copy the entire `band` folder into your XAMPP `htdocs` directory:
   - Windows default: `C:\xampp\htdocs\band`

2) Start Apache and MySQL from the XAMPP Control Panel.

3) Create and import the database:
   - Open phpMyAdmin at http://localhost/phpmyadmin
   - If the `band` database does not exist, it will be created by the script.
   - Click Import, choose the file `C:/xampp/htdocs/band/db_schema.sql`, and run.
   - Alternatively, use CLI:
     - `mysql -u root -p < C:\xampp\htdocs\band\db_schema.sql`

4) Configure DB credentials (if different from XAMPP defaults):
   - Open `band/config.php` and set `$DB_HOST`, `$DB_NAME`, `$DB_USER`, `$DB_PASS`.
   - Default is host `127.0.0.1`, db name `band`, user `root`, empty password.

5) Open the app:
   - http://localhost/band/
   - Register a user, then login.

## Notes
- Uses PDO, prepared statements, `password_hash` and `password_verify`.
- Session-based auth. Protected pages call `require_login()`.
- Client-side validation in `assets/js/main.js`.
- Starter CRUD pages provided; add business rules where marked TODO.

## Structure
- `index.php` – Landing and redirect if logged in.
- `config.php` – PDO connection and helpers. `connect.php` is an alias for convenience.
- `db_schema.sql` – All tables for database `band`.
- `style.css` – Minimal styling.
- `assets/` – JS and placeholder image.
- `pages/` – Shared header/footer and app pages (login, register, dashboard, performances, bands, venues, profile, logout).
- `band/request_form.php` – Quick band creation form.
- `venue/manage_bookings.php` – Booking management scaffold.

## Next Steps (optional)
- Enforce role checks (manager/venue_owner) where marked TODO.
- Add CSRF tokens for forms.
- Add pagination and search.
- Build pages for creating venues and making booking requests.
