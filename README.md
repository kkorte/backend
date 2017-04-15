## Hideyo e-commerce backend
Hideyo is a open-source e-commerce solutions build in Laravel. This backend package includes a backend system. Contact us at info@hideyo.io for questions or enterprise solutions. 

It's still beta. The code is not optimal and clean. In the next month we will improve it. 

Author: Matthijs Neijenhuijs


## Installation

First install laravel and have a database connection running up: https://laravel.com/docs/5.4/installation

Install via [composer](https://getcomposer.org/) - In the terminal:
```bash
composer require hideyo/backend
```

Now add the following to the `providers` array in your `config/app.php`
```php
Hideyo\Backend\BackendServiceProvider::class
```

Then you will need to run these commands in the terminal in order to copy the config and migration files
```bash
php artisan vendor:publish --provider="Hideyo\Backend\BackendServiceProvider"
```

Before you run the migration you may want to take a look at `config/hideyo.php` and change the `table` property to a table name that you would like to use. After that run the migration 
```bash
php artisan migrate

put in database/seeds/DatabaseSeerder.php this:

$this->call(ShopTableSeeder::class);
$this->call(UserTableSeeder::class);

after:

Model::unguard();

```

----

go to /resources/assets/vendor/hideyobackend in command line and fire:
```bash
npm install
bower update
gulp 
```

---


Before you can login in the backend you need a user connected to a shop. Seeding will help you 
```bash
php artisan db:seed 
```
