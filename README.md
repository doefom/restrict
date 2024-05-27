# Restrict

> Restrict is a Statamic addon that lets you choose which user can view other authors' entries of a given collection in
> the control panel.

- ✅ Statamic v4
- ✅ Statamic v5
- ✅ Multisite
- ❌ Eloquent Driver

**Note:** Statamic Pro is required.

## Features

Restrict entries from being listed in the control panel by the rules you define.

## Upgrade Guide

### From 0.3.x to 0.4.x - What's Changed?

In versions up to 0.3.x there were hard coded permissions to `view other authors' entries` on a per-collection basis.
Those permissions are now removed and will no longer have any effect by default. To achieve the similar behavior as
before, you could take a look at
the ["Using Permissions on a Per-Collection Basis"](#using-permissions-on-a-per-collection-basis) section and fine tune
it to your needs.

Also, make sure to check the `hasAnotherAuthor` function in Statamic's
default [EntryPolicy on GitHub](https://github.com/statamic/cms/blob/46b4d39cc99f3be8a12cbb7958e3caf14b01a1ba/src/Policies/EntryPolicy.php#L108)
to see how to properly check for another author in an entry.

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or
run the following command from your project root:

``` bash
composer require doefom/restrict
```

## How to Use

By default, the addon will not restrict anything. To restrict entries, set your restriction logic in
your `AppServiceProvider.php` by providing a closure function that returns a boolean value. The function should
return `true` if the user is allowed to view the given entry, and `false` otherwise.

### Basic Usage

```php
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

### Using Permissions

You can check for anything in your restriction closure. For example, you could define custom permissions in your
Statamic application and check for those:

```php
use Statamic\Facades\Permission;
 
public function boot()
{
    // Add one general permission to Statamic 
    Permission::extend(function () {
        Permission::register('view entries without restriction')
            ->label('View entries without restriction');
    });

    Restrict::setRestriction(function (User $user, Entry $entry) {
        if ($user->hasPermission('view entries without restriction')) {
            // Can view all entries
            return true;
        }

        // Can view own entries only
        return $entry->authors()->contains($user->id());
    });
}
```

### Using Permissions on a Per-Collection Basis

Or to manage restrictions on a per-collection basis:

```php
use Statamic\Facades\Collection;

public function boot()
{
    // Add one permission per collection 
    Permission::extend(function () {
        Permission::get('view {collection} entries')->addChild(
            Permission::make("view {collection} entries without restriction")
                ->label("View entries without restriction")
        );
    });
    
    Restrict::setRestriction(function (User $user, Entry $entry) {
        if ($user->hasPermission("view {$entry->collectionHandle()} entries without restriction")) {
            // Can view all entries in the entry's collection
            return true;
        }
        
        // Can view own entries only
        return $entry->authors()->contains($user->id());
    });
}
```

### Advanced Usage

Let's say each user belongs to a company and each company has many jobs. You could make sure a user can only see jobs
of the company they belong to:

```php
use Statamic\Facades\Permission;
 
public function boot()
{
    Restrict::setRestriction(function (User $user, Entry $entry) {
        if ($entry->collectionHandle() !== 'jobs') {
            // Allow viewing all entries of collections other than 'jobs'
            return true;
        }
        
        // Can view jobs of own company only
        return $user->get('company') === $entry->get('company');
    });
}
```

## Caveats

### Works with Control Panel Routes Only

The addon only restricts entries from being listed in the control panel and therefore does not restrict entries from
being displayed on the front-end of your site or fetched from your API, that's entirely up to you. To know if the user
is currently on a control panel route we check if the route has the `statamic.cp.authenticated` middleware applied. If
so, restrict will do its thing. If not, there won't be any restrictions applied.

### Eloquent Driver

This addon does **not** work with the Eloquent driver. It only works with the default flat file driver. However, it is
planned to support the Eloquent driver in the future.

### Class Bindings

_Restrict_ works by overriding and rebinding some classes in the addon's ServiceProvider:

```php
Statamic::repository(
    \Statamic\Contracts\Entries\EntryRepository::class,
    \Doefom\Restrict\Stache\Repositories\EntryRepository::class
);

$this->app->bind(
    \Statamic\Policies\EntryPolicy::class,
    \Doefom\Restrict\Policies\EntryPolicy::class
);
```

All of those classes extend the respective original class and perform minor changes on it. If you were to override or
use another addon which rebinds one or more of those classes, you might run into issues. That's just something to keep
in mind when using this addon.
