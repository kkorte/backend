[![Code Climate](https://codeclimate.com/github/hideyo/backend.png)](https://codeclimate.com/github/hideyo/backend)

## Hideyo e-commerce backend
Hideyo is an open-source e-commerce solution built in Laravel. This backend package includes a backend system. Contact us at info@hideyo.io for questions or enterprise solutions. 

It's still beta. The code is not yet optimal and clean. In the next month we will improve it. 

Author: Matthijs Neijenhuijs

## Installation

First install laravel and have a database connection running on: https://laravel.com/docs/5.4/installation

Install via [composer](https://getcomposer.org/) - In the terminal:
```bash
composer require hideyo/backend
```

Now add the following to the `providers` array in your `config/app.php`
```php
Hideyo\Backend\BackendServiceProvider::class
```

## Publish configuration in Laravel

You need to run these commands in the terminal in order to copy the config, migration files and views
```bash
php artisan vendor:publish --provider="Hideyo\Backend\BackendServiceProvider"
```

## Database migration & seeding
Before you run the migration you may want to take a look at `config/hideyo.php` and change the `table` property to a table name that you would like to use. After that run the migration 
```bash
php artisan migrate

Put this in database/seeds/DatabaseSeerder.php:

$this->call(ShopTableSeeder::class);
$this->call(UserTableSeeder::class);

After command line:

Model::unguard();
```

----

## Generate stylesheet and JavaScript

go to /resources/assets/vendor/hideyobackend in command line generate the stylesheet and javascript with:
```bash
npm install
bower update
gulp 
```

---


Before you can login to the backend you need a default user. Laravel seeding will help you: 
```bash
php artisan db:seed 
```

## License

GNU AFFERO GENERAL PUBLIC LICENSE
