# Algebra BOX

[![Build Status](https://travis-ci.org/tkescec/AlgebraBox.svg?branch=master)](https://travis-ci.org/tkescec/AlgebraBox)

Algebra BOX is a cloud storage

## Installation
**Install the Package Via Composer:**
```shell
$ composer install
```

**Create ENV file:**
Rename .env.example to .env

**Generate App Key:**
```shell
$ php artisan key:generate
```

**Setup ENV file:**
Add all relevant data like database name, etc

**Run migrations:**
```shell
$ php artisan migrate
```

**Run seedes:**
Run the Database Seeder. You may need to re-generate the autoloader before this will work:
```shell
$ composer dump-autoload
$ php artisan db:seed --class="SentinelDatabaseSeeder"
```

**Start server:**
```shell
$ php artisan serve
```

After you start server type http://localhost:8000 to your browser

## License

Algebra BOX is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

Tatjana Antic
