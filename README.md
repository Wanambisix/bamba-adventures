# Bamba Adventures

Website and content-management system for **bambaadventures.co.ke** — a tours,
safaris and travel site covering Kenya, Tanzania, Zanzibar, Dubai, Thailand and
other destinations.

Plain PHP 8 + MySQL. No framework, no build step, no Composer dependencies.

---

## Requirements

| | |
|---|---|
| PHP | 8.1 or newer (production runs 8.2) |
| Extensions | `pdo_mysql`, `mbstring`, `json`, `fileinfo` |
| Database | MySQL 5.7+ or MariaDB 10.3+ |
| Web server | Apache with `mod_rewrite` (the `.htaccess` handles clean URLs) |

---

## Local setup

1. **Create the database and import the schema**

   ```bash
   mysql -u root -p -e "CREATE DATABASE bamba_db CHARACTER SET utf8mb4;"
   mysql -u root -p bamba_db < setup.sql
   mysql -u root -p bamba_db < migrate.sql
   ```

   `setup.sql` creates the tables and seeds default settings, destinations,
   countries, services, pages and categories. `migrate.sql` applies later schema
   additions. Both are safe to re-run — they use `IF NOT EXISTS` / `INSERT IGNORE`.

2. **Create the config file**

   ```bash
   cp config/database.example.php config/database.php
   ```

   Then edit `config/database.php` with your credentials. That file is
   **git-ignored** and must never be committed.

3. **Serve it.** Either use Apache, or the bundled router which mirrors the
   `.htaccess` rules for PHP's built-in server:

   ```bash
   php -S localhost:8090 router.php
   ```

   Open <http://localhost:8090>. The admin panel is at `/admin/`.

4. **First login.** The first visit to `/admin/` creates the `admin` account and
   **displays the password once** — copy it, then change it under Settings.

---

## Layout

```
index.php              homepage (hero video, featured tours, destinations)
router.php             dev-only router for `php -S`; mirrors .htaccess
.htaccess              clean URLs + security rules (see the block at the top)
config/                database credentials          <- git-ignored
includes/              functions.php, header, footer, nav (shared)
pages/                 about, faq, blog, booking, cancellation, tour listing
tours/                 tour listing + detail, categories
destinations/          destination listing + detail
countries/             country listing + detail
services/              service listing + detail
admin/                 CMS - tours, bookings, inquiries, blog, gallery, settings
api/api.php            JSON endpoints used by the front end
assets/                css, js, logos, favicon
uploads/hero/          hero video and poster (tracked design assets)
uploads/               everything else is admin-uploaded and git-ignored
setup.sql / migrate.sql  database schema
sitemap.php            generates /sitemap.xml
```

---

## Security

**This repository must stay private.** It is the source for a live site. The
following are deliberate:

- **`config/database.php` is git-ignored.** Only `config/database.example.php`
  with placeholders is committed. Cloning this repo does not give you the
  credentials — create the real config file yourself.
- **No hard-coded admin password.** `admin/index.php` generates a random
  bootstrap password on first run and shows it once. It was previously
  `OLD_DEFAULT_PASSWORD_REDACTED`, which must not be used again.
- **No password-reset tool.** An `admin/reset-password.php` used to exist. It
  reset any admin's password with **no authentication at all** — anyone who
  found it could take over the admin panel. It has been deleted and is listed in
  `.gitignore` so it cannot reappear. Use **Admin → Settings → Change Password**.
- **No debug scripts.** `debug.php`, `test.php` and `diagnose.php` used to sit in
  the web root and printed the PHP version, absolute server paths and database
  status to anyone who requested them. They are gone and git-ignored.
- **No server logs in git.** `error_log` files are ignored — they leak absolute
  paths and stack traces.
- **Uploads are split.** Admin-uploaded media is ignored (it is content, not
  source, and it is large). The hand-placed `uploads/hero/` assets are tracked
  because the homepage references them directly.

### Hardening applied (2026-09-20)

Everything below was a real finding, not a precaution:

| Issue | Fix |
|---|---|
| **No CSRF anywhere.** Every admin delete/status action was a `GET` link, so any page, image tag or link prefetch could delete records or change the admin password. | CSRF tokens on all 20 forms, verified in all 41 POST handlers, and every destructive action converted to a `POST` form via `action_form()`. |
| **Session cookies had no flags.** | `HttpOnly`, `SameSite=Lax` and `Secure` (when HTTPS) set in one place, `bamba_session_start()`. |
| **Session fixation on login.** | `session_regenerate_id(true)` on successful sign-in. |
| **No login throttling** — brute-forceable. | 5 failures per IP or username per 15 minutes, recorded in `login_attempts`. |
| **Uploads trusted the file extension.** | Now verified with `getimagesize()`; the stored extension comes from the real MIME type; double extensions (`shell.php.jpg`) rejected; filename is random + sanitised. |
| **`api/api.php` sent `Access-Control-Allow-Origin: *`** and had no rate limit. | Wildcard CORS removed (same-origin only), 10 requests per action per IP per 10 minutes, and email validation added to the contact and volunteer endpoints. |
| **Stored XSS** — CMS content was echoed straight into `<textarea>`, so `</textarea>` in a saved field could break out. | All 9 textareas escaped with `esc()`. |
| **Client-side call to `api.anthropic.com`** on the homepage, with no API key (so it always failed). It delayed results 600 ms and sent every visitor's search term to a third party. | Removed. Search uses the local index only. |
| **`sitemap.php` fatal error** when the DB is down (`Call to a member function query() on null` — `Error` is not caught by `catch (Exception)`). | Null-guarded, `catch (Throwable)`. |
| **`.htaccess` directory block was unreachable** — it sat after a rule ending in `[L]`, so `config/database.php` was fetchable. | Block moved to the top and widened to dotfiles, logs, SQL dumps and backups. `.well-known` exempted for SSL renewal. |
| **No security headers, errors displayed.** | `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy` via `.htaccess`; `display_errors=Off` and `expose_php=Off` via `.user.ini`. |
| **Hard-coded admin password `OLD_DEFAULT_PASSWORD_REDACTED`.** | Replaced with a random password generated on first run and shown once. |

Run `php tests/security-test.php` after any change to `includes/functions.php`.

### The `.htaccess` security block

The first rules in `.htaccess` deny direct access to `config/`, `includes/`,
dotfiles (`.env`, `.git`), and any `.log` / `.sql` / backup file. **They must stay
at the top of the file.** The "serve an existing file directly" rule ends with
`[L]`, so protection placed after it never runs — that is how
`config/database.php` was reachable by URL. `.well-known` is explicitly exempted
so SSL certificate renewal keeps working.

---

## Deployment (cPanel)

1. Upload the files into `public_html/` (or the domain's document root).
2. Create the database and user in cPanel — both get prefixed with your account
   username, e.g. `account_bamba_db` and `account_bamba_user`.
3. Import `setup.sql`, then `migrate.sql`, through phpMyAdmin.
4. Create `config/database.php` on the server from the example file and fill in
   the prefixed names.
5. Ensure `uploads/` is writable (usually 755 is enough on cPanel).
6. Visit `/admin/`, log in, and immediately change the password.

**Never upload a debug or reset script to the web root**, and never commit
`config/database.php`.

---

## Notes

- Tour, destination, country, service, blog and page content is all
  database-driven and edited through `/admin/`.
- Site settings (name, contact details, social links, hero text, footer) are in
  **Admin → Site Settings**.
- Bookings and enquiries from the front end appear in **Admin → Bookings** and
  **Admin → Inquiries**.
- `sitemap.php` builds the sitemap from the database, so new content is picked up
  automatically. `robots.txt` points at it.
- `uploads/hero/hero-bg.mp4` is 13 MB, which is a lot for git history. If the repo
  ever grows beyond this one asset, move media to Git LFS or an object store.
