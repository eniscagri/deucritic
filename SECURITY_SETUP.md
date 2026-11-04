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
