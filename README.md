# Cattlog #

## Introduction ##

A CLI tool for Laravel 5 to manage translation files and syncing entries with view files. Allows translations to be synced with views, get/set values, and list empty values.

## Installation ##

Install with composer

    composer require martynbiz/cattlog-l5

## Usage ##

### Initialize config ###

Cattlog requires a cattlog.json config file at the root of the project to know where to scan source, save to file, filter to use etc:

    ./vendor/bin/cattlog init

This will create the following file (/path/to/project/cattlog.json):

```json
{
    "src": [...],
    "dest": [...],
    "pattern": [...],
    "valid_languages": [...]
}
```

### Update keys ###

Running this command will update all the keys from source files for the en file. If it finds new keys in source, it will save keys with empty values. It will also remove any keys which as no longer used. It will run `scan` first so you can see what keys will be added/removed before updating the destination file.

    ./vendor/bin/cattlog update en

### List keys ###

List all key / value combinations in a file

    ./vendor/bin/cattlog list en

### Count keys ###

Output total number of keys stored for a given language.

    ./vendor/bin/cattlog list en

### Setting values ###

Set an existing key value. If a key doesn't exist, run "cattlog update <lang>" to populate empty keys.

    ./vendor/bin/cattlog set_value en MY_KEY=something

### List options ###

Passing no parameters will list all options

    ./vendor/bin/cattlog

## TODO ##

* Break up: cattlog-l5, cattlog-zend
* convert the pattern to a more human readable string eg. "trans(:key)", "Lang::get(:key)"

* in laravel we need to validate key has enough parts (file.name), breaks zend though
