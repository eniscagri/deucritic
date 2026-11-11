# Secure database configuration

The application no longer stores database credentials in tracked PHP files. Configure this before deploying the security update. As requested, the existing database password can remain unchanged; put it in this private file rather than in the application or Git repository. The old password is still visible in earlier Git commits, so retaining it carries a security risk.

1. In cPanel Terminal, create a private configuration outside `public_html`:

   ```sh
   mkdir -p /home/deucditiccom/private
   chmod 700 /home/deucditiccom/private
   nano /home/deucditiccom/private/deucritic.php
   ```

   Save this PHP array with the current database values:

   ```php
   <?php
   return [
       'host' => 'localhost',
       'name' => 'YOUR_DATABASE_NAME',
       'user' => 'YOUR_DATABASE_USER',
       'pass' => 'YOUR_CURRENT_DATABASE_PASSWORD',
   ];
   ```

   Then restrict its permissions:

   ```sh
   chmod 600 /home/deucditiccom/private/deucritic.php
   ```

2. For local development, create `includes/config.local.php` with the same array. This file is ignored by Git.
3. Deploy from cPanel only after the private file exists. The deployment script checks for it before copying application files.

## Private admin dashboard

The admin area uses the same private configuration file and is disabled until a password hash is configured. Add these values to the returned array in `/home/deucditiccom/private/deucritic.php`; do not commit them or place the plaintext password in PHP source:

```php
'admin_password_hash' => 'PASTE_A_PASSWORD_HASH_HERE',
'admin_allowed_ips' => ['YOUR_CURRENT_PUBLIC_IP'],
```

Generate a hash on the server with PHP CLI, then copy its output into the private file:

```sh
php -r "echo password_hash('choose-a-long-unique-password', PASSWORD_DEFAULT), PHP_EOL;"
```

The IP allowlist is optional, but recommended when the address observed by the web server is stable. Values are matched exactly against `REMOTE_ADDR`; if a trusted reverse proxy is in use, configure the web server to set `REMOTE_ADDR` safely before choosing the allowlist value. Admin sessions expire after 30 minutes of inactivity, and five failed password attempts trigger a 15-minute lockout for that session. The dashboard reads only stored sign-in location data; it does not send visitor IP addresses to a third-party lookup service.

Apply `migrations/20261111_admin_login_history.sql` before deploying the updated login page. Successful logins are stored with the server-observed IP address and user-agent; forwarded-IP headers are not trusted. City/country values remain empty unless supplied by a future, explicitly configured trusted GeoIP source.
