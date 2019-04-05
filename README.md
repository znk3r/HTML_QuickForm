HTML_QuickForm
==============

The HTML_QuickForm package provides methods to dynamically create, validate and render HTML forms.

This package is based in the [original PEAR library](http://pear.php.net/package/HTML_QuickForm), but the code has been 
updated to be compatible with newer versions of PHP.

The intention of this project is not to work as a modern alternative, but to provide support for legacy projects still
using this library. Please, consider migrating to newer alternatives if possible.

Change Log
----------

* Compatible with PHP 7 and newer versions.
* Use of composer autoloader to replace includes.


Install
-------

Install library in your legacy project for migration to other library:

```
composer require "znk3r/html_quickform:^4.0.0" "friendsofpear/pear_exception:0.0.*" "znk3r/html_common:*" "pear/pear:^1.10"
```

Migration from version 3.x to 4.0
---------------------------------

## Create form

**Before:**

```php
$form = new &HTML_QuickForm(...);
```

**After:**

```php
$form = new HTML_QuickForm(...);
```


## Create element

**Before:**

```php
$element = &HTML_QuickForm::createElement(...);
```

**After:**

```php
$element = $form->createElement(...);
```
