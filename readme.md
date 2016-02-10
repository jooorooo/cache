Laravel 5 replace default cache 

In "config/app.php" replace 

* Illuminate\Cache\CacheServiceProvider::class,

with

* Simexis\Cache\CacheServiceProvider::class,

## Usage

```php
//set cache
\Cache::set('category.list', $categories);

//get cache
$categories = \Cache::get('category.list');

//delete cache
\Cache::forget('category.list');
//or 
\Cache::forget('category.*'); // delete all cache where key is category.{somename}