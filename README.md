# Pearl Creamery (PHP 8 + MySQL + Bootstrap 5)

## Run locally (XAMPP)
1. Copy this folder to `htdocs/pearl-creamery`; start Apache and MySQL.
2. In phpMyAdmin, import `database.sql` (creates DB `pearl_creamery`, tables, placeholder products).
3. Open http://localhost/pearl-creamery/setup_admin.php (or follow the link on the admin login page) and create the first admin (the owner). The page deletes itself afterwards; if it cannot, delete it by hand. On a live server, set the `PC_SETUP_KEY` environment variable first and enter that key on the setup page.
4. Shop: http://localhost/pearl-creamery/  Admin: http://localhost/pearl-creamery/admin/

DB settings default to root / empty password. Override with DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS.

## Admin
- Orders: revenue, order count, pending dispatch, top sellers; order feed with status updates, Google Maps / OpenStreetMap links, "Mark paid". Delivering a cash-on-delivery order marks it paid.
- Inventory: edit price, size, description, stock; add products (products are hidden, never deleted).
- Settings: order-alert email, contact phone, change your own password. The owner can also add staff accounts.

## Before going live
- Replace placeholder prices in the Inventory page.
- Serve over HTTPS. Use a non-root MySQL user.
- Order emails use PHP mail(); SMTP and Mobile Money (Flutterwave/Pesapal) are not built yet.
