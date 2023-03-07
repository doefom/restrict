# Restrict

> Restrict is a Statamic addon that adds a permissions for collections to limit entry listings to the author of an
> entry within a given collection.

## Features

Restrict entry listings from being visible to other authors in the control panel. There are times where it might be
useful when not every user in the control panel can view all entries of collections they're permitted to view.
That's when _Restrict_ comes in.

By default a user, that's not a super admin, can only see the entries they've created themselves. Choose for which
collections a user is able to view all entries within the collection.

A user will be able to view an entry when one of the following is true:

- the value of the field `author` of the entry matches the user's `id`
- the permission to `View other authors' entries` is given
- the user is a super admin

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or
run the following command from your project root:

``` bash
composer require doefom/restrict
```

## How to Use

_Restrict_ works by taking a [Users Fieldtype](https://statamic.dev/fieldtypes/users) with the handle `author` into
account when querying entries. Statamic's `Edit other authors' entries` permission functions the exact same way.

Therefore, if the permission `View other authors' entries` is not granted for a given collection AND an entry within
that collection does not have an `author` field, the entry cannot be viewed unless you're a super admin.

## Caveats

_Restrict_ works by overriding and rebinding some classes in the addon's ServiceProvider:

```php
protected $policies = [
    \Statamic\Entries\Entry::class => \Doefom\Restrict\Policies\EntryPolicy::class,
];

public function register()
{

    $this->app->bind(
        \Statamic\Entries\Collection::class,
        \Doefom\Restrict\Entries\Collection::class
    );

    $this->app->bind(
        \Statamic\Fieldtypes\Entries::class,
        \Doefom\Restrict\Fieldtypes\Entries::class
    );

}
```

All of those classes extend the respective original class and perform minor changes on it. If you were to override one
or more of those classes yourself or use another addon which rebinds one or more of those, you might run into issues.
That's just something to keep in mind when using this addon.
