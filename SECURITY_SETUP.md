# Secure database configuration

The application no longer stores database credentials in tracked PHP files. Configure this before deploying the security update.

1. In cPanel **MySQL Databases**, change the password for the database user and confirm that the user still has the required privileges on the Deucritic database. Treat the previous password as exposed because it appeared in Git history; changing the current file does not erase old commits.
2. In cPanel Terminal, create a private configuration outside `public_html`:

   ```sh
   mkdir -p /home/deucditiccom/private
   chmod 700 /home/deucditiccom/private
   nano /home/deucditiccom/private/deucritic.php
   ```

   Save this PHP array with the new database values:

   ```php
   <?php
   return [
       'host' => 'localhost',
       'name' => 'YOUR_DATABASE_NAME',
       'user' => 'YOUR_DATABASE_USER',
       'pass' => 'YOUR_NEW_DATABASE_PASSWORD',
   ];
   ```

   Then restrict its permissions:

   ```sh
   chmod 600 /home/deucditiccom/private/deucritic.php
   ```

3. For local development, create `includes/config.local.php` with the same array. This file is ignored by Git.
4. Deploy from cPanel only after the private file exists. The deployment script checks for it before copying application files.
