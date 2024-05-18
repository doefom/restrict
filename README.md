# Restrict

> Restrict is a Statamic addon that lets you choose which user can view other authors' entries of a given collection in
> the control panel.

- ✅ Statamic v4
- ✅ Statamic v5
- ✅ Statamic API
- ✅ Multisite
- ❌ Eloquent Driver

**Note:** Statamic Pro is required.

## Features

Prevent users from viewing other authors' entries unless they have been explicitly authorized to do so.

### Conditions to view an entry

A user is able to view an entry when one of the following is true:

- the user is a super admin
- the field `author` of the entry matches the current user
- the user's role has permission to `View other authors' entries` for the given collection

### Important Note

If your entry blueprint does not have an `author` field, the addon will have **no effect**.

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or
run the following command from your project root:

``` bash
composer require doefom/restrict
```

## How to Use

### Default Behavior after Installation

The addon behaves very similar to Statamic's default permission
to `Edit other authors' entries` ([https://statamic.dev/users#author-permissions](https://statamic.dev/users#author-permissions)).
It adds a new permission to each collection's permissions called `View other authors' entries`. After installing the
addon, by default a user can only view entries they've created themselves.

### Giving a User Permission to View Other Authors' Entries

To give a user permission to view other authors' entries, you need to assign the permission to the user's role. You can
do this in the control panel by navigating to "Permissions", selecting the role you want to edit, and checking the
permission `View other authors' entries` for the collection you want to give permission for.

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
