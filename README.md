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
  the old default password (redacted here on purpose - see the note below), which must not be used again.
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
| **Hard-coded default admin password.** | Replaced with a random password generated on first run and shown once. |

### Hardening and fixes applied (2026-09-23)

Found by running the site locally against a copy of the live database and
probing the live host.

| Issue | Fix |
|---|---|
| **Soft 404s on every detail route.** A missing slug answered `header('Location: /')` — a 302 to the homepage. Google treats "redirect to the homepage" as a soft 404, so `/tour/anything-at-all` looked like a valid page and burnt crawl budget. Six pages did this (`tours`, `services`, `countries`, `destinations`, `tours/category`, `page`). | New `render_not_found()` helper returns a real 404 with `<meta name="robots" content="noindex, follow">`. `pages/blog-detail.php` already did this correctly and was the model. |
| **Dev router hid the difference.** `router.php` fell back to the homepage for any unmatched URL, so junk URLs returned 200 locally but 404 in production. | Fallback now returns a real 404; `/` is served explicitly. |
| **Resume uploads trusted the extension** (`in_array($ext, ['pdf','doc','docx'])`), had **no size limit**, and used the client's filename. | `uploadDocument()` / `validate_document_upload()`: PDF (`%PDF`), DOC (OLE2 header) and DOCX (`PK\x03\x04`) magic bytes verified against the claimed extension, 5 MB cap, server-generated filename. |
| **`PDOException` escaped to the public API.** A bad `career_id` printed a stack trace to the applicant as `Fatal error: Uncaught PDOException...`. `contact` and `volunteer` had the same gap, as did the `/book` and `/contact` forms. | Every public write is wrapped in `try/catch` and returns a clean JSON error (HTTP 500) or an inline form message. |
| **Orphaned uploads.** The resume was moved before the row was inserted, so a failed insert left the file on disk forever. | The file is unlinked if the insert fails. |
| **`uploads/` could execute code.** Uploads are content-validated, so a `.php` should never land there — but nothing stopped one running if it did. | `uploads/.htaccess` denies script extensions and strips the PHP handler. Deliberately avoids `Options` and any unguarded `php_flag`, which some hosts reject with a 500. |
| **No throttle on the public HTML forms.** The JSON API was rate-limited; `/book` and `/contact` were not, and both write to the database. | 5 submissions per IP per 10 minutes, alongside the existing CSRF check. |

**Login lockout, worth knowing:** 5 failed logins lock that IP **and** the
username out for 15 minutes. The counter is "IP or username", which is good
against brute force but also means someone can deliberately lock the admin out.
There is no self-service unlock — either wait 15 minutes or clear the row:

```sql
DELETE FROM login_attempts WHERE username = 'admin';
```

Run `php tests/security-test.php` after any change to `includes/functions.php`.
It covers CSRF, session flags, escaping, rate limiting, image uploads, document
uploads, the soft-404 regression (it greps the controllers for
`header('Location: /')`) and `uploads/.htaccess`. Currently **48 passing**.

### The `.htaccess` security block

The first rules in `.htaccess` deny direct access to `config/`, `includes/`,
dotfiles (`.env`, `.git`), and any `.log` / `.sql` / backup file. **They must stay
at the top of the file.** The "serve an existing file directly" rule ends with
`[L]`, so protection placed after it never runs — that is how
`config/database.php` was reachable by URL. `.well-known` is explicitly exempted
so SSL certificate renewal keeps working.

---

## File naming: which file is the homepage

Two files matter at the web root, and on the production server they arrived
under confusing names:

| File | What it is |
|---|---|
| `index.php` | **The site.** This is the homepage. On production it was named `index1.php` (a copy of `index.php` was sitting next to it, byte-identical - sha256 `4ed0b157d5da`). It is `index.php` here. |
| `coming-soon.html` | The standalone "We'll Be Back Soon" holding page. On production this was `index2.html`. It is self-contained (inline CSS), links the logo and a WhatsApp button, and is not linked from anywhere in the site. Use it by pointing the domain at it, or drop it if it is no longer wanted. |

Neither of the production leftovers (`index1.php`, `index2.html`) belongs on the
server: `index1.php` is a duplicate of `index.php`, and `index2.html` is a
second crawlable page at the root announcing that the site is down. Delete both.
`DirectoryIndex index.php index.html` in `.htaccess` means neither could shadow
the real homepage, which is why the duplication was harmless in practice - but
it is still two files to keep in sync by hand.

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
