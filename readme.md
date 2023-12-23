# Packages

### Utilities
- `sudo apt install curl git unzip`

### Nginx
- `sudo apt-get install nginx`
- For Laravel apps, refer to [this](https://www.digitalocean.com/community/tutorials/how-to-deploy-a-laravel-application-with-nginx-on-ubuntu-16-04)

### PostgreSQL
- `sudo apt install postgresql postgresql-contrib`

Refer to [this](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-postgresql-on-ubuntu-18-04)

### PHP
- `sudo apt install php-fpm php-pgsql php-cli php-zip php-mbstring php-xml php-curl php-json php-bcmath php-gd`

### Composer
- `cd ~`
- `curl -sS https://getcomposer.org/installer -o composer-setup.php`
- `sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer`

### Node.js & NPM
- `sudo apt install npm`

### Redis
- Install following [this](https://www.digitalocean.com/community/tutorials/how-to-install-redis-from-source-on-ubuntu-18-04)
- Secure following [this](https://www.digitalocean.com/community/tutorials/how-to-secure-your-redis-installation-on-ubuntu-18-04)

# Environment Setup
- `sudo mkdir -p /opt/liveapps`
- `sudo chown -R www-data:www-data /opt/liveapps/`
- `sudo chmod -R ug+rw /opt/liveapps/`
- Add other users to www-data group : `sudo usermod -a -G www-data <username>`

# Database Setup

### Initial Setup
- `CREATE USER <db_username> WITH PASSWORD '<db_password>';`
- `ALTER ROLE <db_username> SET client_encoding TO 'utf8';`
- `ALTER ROLE <db_username> SET default_transaction_isolation TO 'read committed';`
- `ALTER ROLE <db_username> SET timezone TO 'GMT';`
- `CREATE DATABASE <db_name>;`
- `GRANT ALL PRIVILEGES ON DATABASE <db_name> TO <db_username>;`
- `\q`

### Change password
- `sudo -u postgres psql <db_username>;`
- `\password <db_username>;`
- Type in new password (twice)
- `\q`

# Web Application Setup
### Environnement Setup
- Setup config values in `env` file
- `npm install`
- `composer install`
- `php artisan storage:link`
- `php artisan passport:install`
- Increase `client_max_body_size` in `/etc/nginx/nginx.conf` (i.e. `client_max_body_size 32m;`)
- Increase limits in `php.ini` (find loaded config file with `phpinfo()`, probably `/etc/php/7.2/fpm/php.ini`) :
    - `upload_max_filesize = 32M`
    - `post_max_size = 32M`
    - `date.timezone = "America/New_York"`
    - (make sure to uncoment entries if commented, reboot)
- Optionally give PHP more ram to run `memory_limit=128M`
- When done editing `php.ini`, restart fpm `sudo systemctl restart php7.x-fpm` where `x` is your php minor version.
- Table for failed queue jobs `php artisan queue:failed-table`

### Migrations
- `php artisan migrate` (or to wipeDB and migrate, `php artisan migrate:fresh`)

### Webhooks
- `php artisan webhook:dwolla` to create a Dwolla webhook endpoints.

### Queues
- Setup worker following [this](https://laravel.com/docs/5.8/queues#supervisor-configuration)

### Development
- Use Telescope.
- `composer require laravel/telescope --dev`
- `php artisan telescope:install`
- `php artisan migrate`
- On update run `php artisan telescope:publish`
- Use Redis Desktop Manager `sudo snap install redis-desktop-manager`

### Tools
Under the `.tools` folder, several utility scripts are included :
- `perms.sh` will recursively set proper permissions in the app directory.
- `codewatch.sh` start Visual Studio Code in cwd and start `npm run watch`
- `fresh.sh` runs a fresh migration, seeds he database and runs `php artisan passport:install`

### 3rd Party Packages Used
- [Laravel Phone](https://github.com/Propaganistas/Laravel-Phone)
- [Laravel Intl](https://github.com/Propaganistas/Laravel-Intl)
- [This Vue.js Datepicker](https://www.npmjs.com/package/vuejs-datepicker#date-formatting)
- [Snappy PDF/Image Wrapper](https://github.com/barryvdh/laravel-snappy)

# Let's Encrypt SSL
Read [this](https://support.cloudflare.com/hc/en-us/articles/214820528-Validating-a-Let-s-Encrypt-Certificate-on-a-Site-Already-Active-on-Cloudflare)

- Disable TLSv1.0 and TLSv1.1 in `/etc/letsencrypt/options-ssl-nginx.conf`

# Further Troubleshooting

Refer to [this](https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-ubuntu-18-04)

