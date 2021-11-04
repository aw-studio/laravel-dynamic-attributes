# Laravel Dynamic Attributes

A package for adding dynamic attributes to [Elqouent Models](https://laravel.com/docs/eloquent).

See also: [Laravel Dynamic Relations](https://github.com/aw-studio/laravel-dynamic-relations)

## Setup

Install the package via composer:

```php
composer require aw-studio/laravel-dynamic-attributes
```

Publish the migrations:

```php
php artisan vendor:publish --tag="dynamic-attributes:migrations"
```

## Usage

Just add the `HasDynamicAttributes` to a Model:

```php
use Illuminate\Database\Eloquent\Model;
use AwStudio\DynamicAttributes\HasDynamicAttributes;

class Page extends Model
{
    use HasDynamicAttributes;
}
```

And voila:

```php
$page = Page::create([
    'headline' => 'Hello World!',
    'text'     => 'Lorem Ipsum...',
]);

echo $page->headline; // "Hello World!"
```

### Set Attribute Cast Manually

Usually casts should be set correctly depending on the attribute value:

```php
Page::create(['released_at' => now()->addWeek()]);

dd($page->released_at); // Is an instance of Illuminate\Support\Carbon
```

However you may want to set an attribute cast manually:

```php
$page = Page::create(['is_active' => 1]);

dump($page->is_active); // output: 1

$page->setDynamicAttributeCast('is_active', 'boolean')->save();

dd($page->is_active); // output: true
```

### Query Scopes

```php
Page::whereAttribute('foo', 'bar');
```
