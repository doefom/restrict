# Restrict

> A Statamic addon that applies your EntryPolicy's `view` method to entry listings in the control panel.

- ✅ Statamic v4
- ✅ Statamic v5
- ✅ Multisite
- ❌ Eloquent Driver

**Note:** Statamic Pro is required.

## Features

Prevent entries from showing up in the control panel based on the `EntryPolicy` you define.

## Upgrade Guide

### From 0.4.x to 0.5.x

In versions v0.4.x you had to set a restriction closure in the `AppServiceProvider` to restrict entries. This will no
longer work. Instead, you can now set the restriction by extending the `Statamic\Policies\EntryPolicy` class and adjust
the `view` method to your needs, since this policy method will now be respected when querying entries in the control
panel.

Before, you've set your restrictions like this:

```php
// v0.4.x:

use Doefom\Restrict\Facades\Restrict;
use Statamic\Contracts\Auth\User;
use Statamic\Contracts\Entries\Entry;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ...
    }

    public function boot(): void
    {
        Restrict::setRestriction(function (User $user, Entry $entry) {
            // Can view own entries only 
            return $entry->authors()->contains($user->id());
        });
    }
}
```

Now you have to set your restrictions as explained in the [Getting Started](#getting-started) section.

### From 0.3.x to 0.4.x

In versions up to 0.3.x there were hard coded permissions to `view other authors' entries` on a per-collection basis.
Those permissions are now removed and will no longer have any effect by default.

## Getting Started

### Installation

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or
run the following command from your project root:

``` bash
composer require doefom/restrict
```

### Create a Custom Entry Policy

To properly use this addon it's best to create a custom entry policy. **Make sure it extends the
default** `Statamic\Policies\EntryPolicy` and overrides its `view` method. This method will be called to determine if an
entry is listed in the control panel or not.

**Tip:** You can create a custom policy by running:

```shell
php artisan make:policy MyEntryPolicy
```

Here is what `MyEntryPolicy` could look like:

```php
<?php

namespace App\Policies;

use Statamic\Policies\EntryPolicy;

class MyEntryPolicy extends EntryPolicy
{

    public function view($user, $entry)
    {
        // ...
    }

}
```

### Register Your Custom Entry Policy

Make sure to register your custom entry policy in your `AppServiceProvider`:

```php
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \Statamic\Policies\EntryPolicy::class,
            \App\Policies\MyEntryPolicy::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ...
    }
}

```

And that's it! From now on, the `view` method of your custom entry policy will be called to determine if an entry should
be listed in the control panel or not. Your changes will also have an effect on the detail view of an entry and return
a `403` if the user is not allowed to view the entry.

## Usage Examples

### Basic Usage

By default, Statamic ships with the ability to restrict users from editing other authors' entries. Maybe you want your
users to not just not edit other authors' entries but rather not have them listed in the control panel at all. To
achieve this, you could adjust the `view` method of your custom entry policy like so:

```php
use Statamic\Policies\EntryPolicy;

class MyEntryPolicy extends EntryPolicy
{

    public function view($user, $entry)
    {
        $default = parent::view($user, $entry);

        if ($entry->blueprint()->hasField('author')) {
            return $default && $entry->authors()->contains($user->id());
        }

        return $default;
    }

}
```

### Using Permissions

You can check for anything in the `view` method of your policy. For example, you could define custom permissions in your
Statamic application and check for those.

Let's say you run an application where each user belongs to a company and each company has many entries. By default, a
user should only see entries of their own company. However, you might want to allow certain users to view entries of
other companies as well. For that you could add a permission to Statamic in your `AppServiceProvider` and check
for this permission in your custom entry policy.

#### Adding a Permission

```php
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ...
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Add one general permission to Statamic 
        Permission::extend(function () {
            Permission::register('view entries of other companies')
                ->label('View entries of other companies');
        });
    }
    
}
```

#### Checking for the Permission

```php
use Statamic\Policies\EntryPolicy;

class MyEntryPolicy extends EntryPolicy
{

    public function view($user, $entry)
    {
        $default = parent::view($user, $entry);
        
        if ($user->hasPermission('view entries of other companies')) {
            // Can view all entries
            return $default;
        }

        // Can view entries of the same company only
        return $default && $user->get('company') === $entry->get('company');
    }

}
```

### Using Permissions on a Per-Collection Basis

Regarding the example above, you might also add a permission to each collection in your `AppServiceProvider` so that you
can prevent entries from being listed in the control panel if the current user belongs to another company. But this time
on a per-collection basis.

#### Adding the Permissions

```php
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ...
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Add one permission per collection 
        Permission::extend(function () {
            Permission::get('view {collection} entries')->addChild(
                Permission::make("view {collection} entries of other companies")
                    ->label("View entries of other companies")
            );
        });
    }
    
}
```

#### Checking for the Permissions

```php
use Statamic\Policies\EntryPolicy;

class MyEntryPolicy extends EntryPolicy
{

    public function view($user, $entry)
    {
        $default = parent::view($user, $entry);
        
        if ($user->hasPermission("view {$entry->collectionHandle()} entries of other companies")) {
            // Can view all entries in the entry's collection
            return $default;
        }

        // Can view entries of the same company only for this collection
        return $default && $user->get('company') === $entry->get('company');
    }

}
```

## Caveats

### Works with Control Panel Routes Only

The addon only restricts entries from being listed in the control panel and therefore does not restrict entries from
being displayed on the front-end of your site or fetched from your API, that's entirely up to you. To know if the user
is currently on a control panel route we check if the route has the `statamic.cp.authenticated` middleware applied.

### App Running in Console

If you run your app in the console, the addon will not have any effect.

```php
class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        // ...
    }
}
```

### Eloquent Driver

This addon does **not** work with the Eloquent driver. It only works with the default flat file driver. However, it is
planned to support the Eloquent driver in the future.

### Class Bindings

_Restrict_ works by rebinding Statamic's `EntryQueryBuilder` and `EntryPolicy` (this one is done by you). If you were to
use custom bindings or another addon which also rebinds one of those classes, you might run into issues. That's just
something to keep in mind when using this addon.

```php
// ----------------------------------------------------------------
// Rebinding the Entry Policy in your AppServiceProvider
// ----------------------------------------------------------------

$this->app->bind(
    \Statamic\Policies\EntryPolicy::class,
    \App\Policies\MyEntryPolicy::class
);

// ----------------------------------------------------------------
// Rebinding the Entry Query Builder as done
// by the Restrict addon in its ServiceProvider.
// ----------------------------------------------------------------

$this->app->bind(\Statamic\Stache\Query\EntryQueryBuilder::class, function ($app) {
    return new \Doefom\Restrict\Stache\Query\EntryQueryBuilder($app['stache']->store('entries'));
});
```
