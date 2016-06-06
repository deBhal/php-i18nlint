php-i18nlint
============

A tool for checking for common gettext style translation errors in php files.


Usage:
```
php i18nlint.php filename
```

For example:

    $ php i18nlint.php test/some.php 
    filename: test/some.php
    $errors = array (
      0 => 
      array (
        'line' => 4,
        'code' => '__($not_ok);',
        'error' => 'Translate arguments must be plain strings',
      ),
      1 => 
      array (
        'line' => 5,
        'code' => '__(not_ok());',
        'error' => 'Translate arguments must be plain strings',
      ),
    )




