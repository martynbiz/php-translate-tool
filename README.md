# Cattlog #

## Introduction ##

A CLI tool for Zend Framework to manage translation files and syncing entries with view files. Allows translations to be synced with views, get/set values, and list empty values.

## Installation ##

Install with composer

    composer require martynbiz/php-translate-manager

## Usage ##

### Create config ###

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

### Scan keys ###

This will just check and tell you what keys are new, and what will be removed. However
it doesn't make any changes and is useful to do a dry run or test configuration.

    ./vendor/bin/cattlog update en

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

* convert the pattern to a more human readable string eg. "trans(:key)", "Lang::get(:key)"

* in zend, how to handle plurals e.g "COMMENT_TXT": ["%1$s Comment","%1$s Comments"]. maybe attempt to json_decode(), if null, treat as a string

* in laravel we need to validate key has enough parts (file.name), breaks zend though

converter
https://localise.biz/free/converter/po-to-php
