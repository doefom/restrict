# Restrict

> Restrict is a Statamic addon that lets you choose which user can view other authors' entries of a given collection in
> the control panel.

- ✅ Statamic v4
- ✅ Statamic v5
- ✅ Multisite
- ❌ Eloquent Driver

**Note:** Statamic Pro is required.

## Features

Prevent entries from showing up in the control panel based on the restrictions you define.

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
Those permissions are now removed and will no longer have any effect by default. To achieve the similar behavior as
before, you could take a look at
the ["Using Permissions on a Per-Collection Basis"](#using-permissions-on-a-per-collection-basis) section and fine tune
it to your needs.

Also, make sure to check the `hasAnotherAuthor` function in Statamic's
default [EntryPolicy on GitHub](https://github.com/statamic/cms/blob/46b4d39cc99f3be8a12cbb7958e3caf14b01a1ba/src/Policies/EntryPolicy.php#L108)
to see how to properly check for another author in an entry.

## Getting Started

### Installation

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or
run the following command from your project root:

``` bash
composer require doefom/restrict
```

### Publish the Configuration File

```shell
php artisan vendor:publish --tag=restrict-config
```

### Create a Custom Entry Policy

Your custom entry policy should extend the default `Statamic\Policies\EntryPolicy` and override its `view` method. This
method will be called to determine if an entry is listed in the control panel or not.

**Hint:** You can create a custom policy by running:

```shell
php artisan make:policy MyEntryPolicy
```

**Important:** Ensure your custom policy extends the `Statamic\Policies\EntryPolicy`.

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

After running the publish command you should see the `config/restrict.php` configuration file. Adjust the `entry_policy`
key by providing the class name of your custom entry policy.

```php
<?php

return [
    'entry_policy' => \App\Policies\MyEntryPolicy::class,
];
```

And that's it! From now on, the `view` method of your custom entry policy will be called to determine if an entry should
be listed in the control panel or not.

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

### Eloquent Driver

This addon does **not** work with the Eloquent driver. It only works with the default flat file driver. However, it is
planned to support the Eloquent driver in the future.

### Class Bindings

_Restrict_ works by rebinding the Query Builder and the Entry Policy classes. This is done in the `ServiceProvider` of
the addon. If you were to use custom bindings or another addon which also rebinds one of those classes, you might run
into issues. That's just something to keep in mind when using this addon.

```php
// Rebinding the Entry Policy
$this->app->bind(
    \Statamic\Policies\EntryPolicy::class,
    // Your custom entry policy as set in /config/restrict.php
);

// Rebinding the Entry Query Builder
$this->app->bind(\Statamic\Stache\Query\EntryQueryBuilder::class, function ($app) {
    return new \Doefom\Restrict\Stache\Query\EntryQueryBuilder($app['stache']->store('entries'));
});
```
