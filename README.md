# Restrict

> Restrict is a Statamic addon that lets you choose from which collection a user can view other authors' entries.

## Features

Restrict entry listings from being visible to other authors in the control panel. There are times where it might be
useful when not every user in the control panel can view all entries of collections they're permitted to view.
That's when _Restrict_ comes in.

After installing the addon, by default a user (who is not a super admin), can only see the entries they've created
themselves. A user is able to view an entry when one of the following is true:

- the user is a super admin
- the value of the field `author` of the entry matches the user's `id`
- the permission to `View other authors' entries` is given for the collection of the entry

## How to Use

The addon will add a "View other authors' entries" permission to each collection's permissions. You can permit a
user to view other authors' entries by granting this permission for the respective collection for the user's role.

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or
run the following command from your project root:

``` bash
composer require doefom/restrict
```

## Caveats

### Eloquent Driver

This addon does **not** work with the Eloquent driver. It only works with the default flat file driver. However, it is 
planned to support the Eloquent driver in the future.

### Class Bindings

_Restrict_ works by overriding and rebinding some classes in the addon's ServiceProvider:

```php
Statamic::repository(
    \Statamic\Contracts\Entries\EntryRepository::class,
    \Doefom\Restrict\Contracts\Entries\EntryRepository::class
);

$this->app->bind(
    \Statamic\Policies\EntryPolicy::class,
    \Doefom\Restrict\Policies\EntryPolicy::class
);
```

All of those classes extend the respective original class and perform minor changes on it. If you were to override or
use another addon which rebinds one or more of those classes, you might run into issues. That's just something to keep
in mind when using this addon.
