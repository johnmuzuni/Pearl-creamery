# Deploy Pearl Creamery to InfinityFree (VS Code + SFTP)

## 0. Prerequisites
- InfinityFree account + a hosting account created (gives you a subdomain like `yoursite.epizy.com`).
- VS Code with the **SFTP** extension (Natizyskunk) installed. ✓ already done
- XAMPP locally verified working.

---

## 1. Find your credentials in InfinityFree
1. Log in to the InfinityFree **Client Area**.
2. Click your hosting account → **Manage**.
3. **MySQL Databases** → create a database named `pearl_creamery` (it will be stored as `if0_XXXXXXX_pearl_creamery`). Copy:
   - MySQL Hostname (e.g. `sql207.infinityfree.com`)
   - Database name
   - Username (same as account username, e.g. `if0_XXXXXXX`)
   - Password (your hosting account password)
4. **Files → FTP Details** (or **FTP Accounts**) → copy:
   - FTP Hostname: `ftpupload.net`
   - FTP Username (e.g. `if0_XXXXXXX`)
   - FTP Password (hosting account password)
   - Remote path: `/htdocs`

---

## 2. Fill in deploy values in code
Open `config.php` and edit the `// InfinityFree` block with the MySQL values from step 1. Leave the local block as-is. (The file auto-detects the environment by hostname.)

Optional mobile money: set `PESAPAL_KEY`, `PESAPAL_SECRET`, `PESAPAL_IPN_ID`, and switch `PESAPAL_BASE` to the live URL once your Pesapal merchant account is ready. `SITE_URL` is generated automatically from the visiting domain.

---

## 3. Configure the VS Code SFTP extension
1. Open `.vscode/sftp.json` in this project.
2. Replace `YOUR_FTP_USERNAME (...)` and `YOUR_FTP_PASSWORD (...)` with your real FTP credentials from step 1.
3. Save. (This file contains secrets — do not commit/share it.)

Connect test:
- Open the Command Palette → **SFTP: List** (or right-click in the explorer → SFTP: Upload Project). You should see the remote `/htdocs` folder.

---

## 4. Import the database (remote)
1. In hPanel → **phpMyAdmin** (for your database) → **Import**.
2. Choose `sql/schema.sql` from your local folder and import. (The `sql/` folder is excluded from SFTP upload by default, so do this manual import.)
3. Verify tables exist: `products`, `orders`, `admins`, `payments`, `store_settings`.

---

## 5. Upload the project
- Right-click the project root in VS Code → **SFTP: Upload Project** → uploads everything in `/htdocs` *except* ignored paths (`.vscode`, `.git`, `sql`, `README.md`, `vendor`).
- Subsequent edits upload automatically on save (`uploadOnSave: true`). Check the SFTP output panel for "success".

Note: `vendor/` (PHPMailer) is ignored. If you ran `composer require phpmailer/phpmailer` locally and want email on the server, upload the `vendor/` folder manually once, or run composer on the server (InfinityFree usually has no composer/SSH). Easiest: upload `vendor/` via hPanel File Manager as a zip.

---

## 6. Create the admin (ONCE), then lock down
1. Visit `https://yoursite.epizy.com/admin/setup_admin.php` and create your admin account.
2. Delete `admin/setup_admin.php` immediately (via SFTP or hPanel). The file says so itself — leaving it is a serious security risk.

---

## 7. Optional: force HTTPS
Rename `.htaccess.deploy` → `.htaccess` and upload it (or paste via hPanel). It redirects http → https and blocks direct access to `sql/` and `includes/`. Only do this AFTER your free SSL certificate is active (hPanel → SSL; InfinityFree auto-provisions it).

---

## 8. Verify
- Store: `https://yoursite.epizy.com/`
- Admin: `https://yoursite.epizy.com/admin/login.php`
- Add a product, place a test order, confirm it appears in admin → Orders.

If you see a database error:
- Confirm `DB_HOST` is the InfinityFree hostname (NOT `localhost`).
- Confirm the DB name/username are prefixed (e.g. `if0_XXXXXXX_...`).
- If `Access denied`, copy-paste (don't type) the password from hPanel into `config.php`, then save (auto-uploads).

---

## 9. Pesapal IPN (after first deploy)
Register `https://yoursite.epizy.com/api/payment_webhook.php` as your IPN URL in Pesapal; put returned id in `PESAPAL_IPN_ID`. Update `PESAPAL_BASE` to the live URL and credentials when going live.

---

## Safety notes
- Never commit `.vscode/sftp.json` (contains password) — keep it local only.
- Keep `admin/setup_admin.php` deleted on the server.
- InfinityFree blocks external/remote MySQL access: you can only query the DB from PHP hosted on the same account (fine for this app).