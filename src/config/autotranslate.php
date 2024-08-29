<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Reset Patterns
    |--------------------------------------------------------------------------
    |
    | Reset the default patterns, if true we will use only the extra patterns
    | if false we will use the default patterns and the extra patterns.
    |
    */
    'reset_patterns' => false,

    /*
    |--------------------------------------------------------------------------
    | Extra Patterns
    |--------------------------------------------------------------------------
    |
    | Add extra patterns to be scanned.
    |
    */
    'patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Directories To Scan
    |--------------------------------------------------------------------------
    |
    | Add extra directories to be scanned.
    | If you remove a directory from the list, it will not be scanned.
    |
    */
    'directories' => ['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'storage', 'tests'],

    /*
    |--------------------------------------------------------------------------
    | Default Directory To Store Translations
    |--------------------------------------------------------------------------
    |
    | The default directory to store the translations.
    | lang is the default directory.
    | You can change it to resources/lang or any other directory.
    | If the directory is not language directory or resources directory, it will be created in base_path.
    |
    */
    'default_directory' => 'lang',
];
