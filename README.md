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