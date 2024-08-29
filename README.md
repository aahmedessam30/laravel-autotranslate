# Laravel AutoTranslate

**Laravel AutoTranslate** is a package that automates the process of collecting translation keys throughout your Laravel project and organizing them into the appropriate translation files. This tool simplifies multilingual development by ensuring that all translation keys are efficiently managed and up-to-date.

## Installation

You can install the package via Composer:

```bash
composer require ahmedessam/laravel-autotranslate
```

# Usage
## Step 1: Publish Translation Files
Before running the AutoTranslate command, you must publish the translation files using Laravel's built-in command:

```bash
php artisan lang:publish
```
This will create the necessary language files in your lang directory.

## Step 2: Synchronize Translation Keys
Once the translation files are published, you can run the AutoTranslate command to automatically collect and organize translation keys:

```bash
php artisan sync:translations
```

This command will scan your entire Laravel project for translation keys and ensure they are properly placed in the corresponding translation files.

# Configuration
The AutoTranslate package provides a configuration file that allows you to customize the behavior of the translation synchronization process. You can publish the configuration file using the following command:

```bash
php artisan vendor:publish --tag=autotranslate-config
```

This will create a config file named `autotranslate.php` in your `config` directory. The configuration file contains the following options:

- **reset_patterns**:  If set to true, only your extra patterns will be used. If set to false, both the default and extra patterns will be used.
- **patterns**:  An array of extra patterns to search for translation keys in PHP files. You can add your own patterns to capture translation keys in different formats.

# Custom Patterns
You can define custom patterns to search for translation keys in PHP files. To add a custom pattern, simply add it to the `patterns` array in the `autotranslate.php` configuration file. Each pattern should be a regular expression that captures the translation key.

Here is an example of adding a custom pattern to capture translation keys in the format `trans('key')`:

```php
'patterns' => [
    '/trans\([\'\"](.*?)[\'\"]\)/',
],
```

This pattern will capture translation keys in the format `trans('key')` and extract the key value.

# Custom Directories

By default, the AutoTranslate package scans the default Laravel directories for translation keys. If you have additional directories that contain translation keys, you can specify them in the `directories` array in the `autotranslate.php` configuration file.

Here is an example of adding a custom directory to search for translation keys:

```php
'directories' => [
    'custom_directory',
],
```

This will include the `custom_directory` in the search for translation keys and remove the default Laravel directories.

# Directories to save translations

By default, the AutoTranslate package saves the translation keys in the `resources/lang` directory. If you want to save the translation keys in a different directory, you can specify the directory in the `default_directory` option in the `autotranslate.php` configuration file.

Here is an example of saving the translation keys in a custom directory named `custom_lang`:

```php
'default_directory' => 'custom_lang',
```

This will save the translation keys in the `custom_lang` directory instead of the default `lang` directory.

# Features

## Translation Key Extraction

The AutoTranslate package scans your Laravel project for translation keys and extracts them from various sources, including:

- Blade templates
- PHP files

The package identifies translation keys based on the Laravel `__()`, `trans()` helper functions and Blade `@lang` directive.

# Requirements
- PHP 8.2 or higher
- Laravel 10.0 or higher
- Composer

# License
The Laravel AutoTranslate package is open-sourced software licensed under the MIT license.

## Author

- **Ahmed Essam**
    - [GitHub Profile](https://github.com/aahmedessam30)
    - [Packagist](https://packagist.org/packages/ahmedessam/laravel-autotranslate) 
    - [LinkedIn](https://www.linkedin.com/in/aahmedessam30/)
    - [Email](mailto:aahmedessam30@gmail.com)

## Contributing
Contributions are welcome! Please feel free to submit a Pull Request.

## Issues
If you find any issues with the package or have any questions, please feel free to open an issue on the GitHub repository.

Enjoy building your multilingual applications with Laravel AutoTranslate!
