# Using Monolog

- [Installation](#installation)
- [Core Concepts](#core-concepts)
- [Addons](#addons)
  1. [Get available addons](#get-available-addons)
  2. [Set addon](#set-addon)
- [App Model](#app-model)
  1. [Set App Model](#set-app-model)
  2. [Render](#render)
- [Databases method](#databases-method)
  1. [Available Database Rule](#available-database-rule)
  2. [Create Database](#create-database)
  3. [Exists Database](#exists-database)
  4. [Drop Database](#drop-database)
  5. [Rename Database](#rename-database)
  6. [List Databases](#list-databases)
- [Tables method](#databases-method)
  1. [Available Table Rule](#available-table-rule)
  2. [Create Table](#create-table)
  3. [Exists Table](#exists-table)
  4. [Drop Table](#drop-table)
  5. [Rename Table](#rename-table)
  6. [Clean Table](#clean-table)
  7. [List Tables](#list-tables)
- [Columns method](#columns-method)
  1. [Available Column Rule](#available-column-rule)
  2. [Create Column](#create-column)
  3. [Exists Column](#exists-column)
  4. [Drop Column](#drop-column)
  5. [List Columns](#list-columns)
- [Data methods](#data-methods)
  1. [Insert](#insert)
  2. [Update](#update)
  3. [Delete](#delete)
  4. [Find](#find)

## Installation

olive-cms/udms is available on Packagist ([olive-cms/udms](http://packagist.org/packages/olive-cms/udms)) and as such installable via [Composer](http://getcomposer.org/).

```bash
composer require olive-cms/udms
```

If you do not use Composer, you can grab the code from GitHub, and use any PSR-0 compatible autoloader (e.g. the [Symfony2 ClassLoader component](https://github.com/symfony/ClassLoader)) to load Monolog classes.

## Core Concepts

coming soon!

### Get udms

``` php
// load autoload
require_once 'vendor/autoload.php';
use Olive\UDMS\Core as udms;

// create a udms
$udms = new udms('/path/to/database/dir');
```

## Addons

### Get available addons

``` php
// array available addons
$list = $udms->getAddonsList();
```

### Set addon

Set you Selection addon

``` php
$udms->setAddon('json');
```

## App Model

### Set App Model

``` php
$udms->setAppDataModel(
  [
    'school' // database name
    => [
      'student' // table name
      => [
        'id' // column name
        => [
          'type' => 'int',
          'index' => 'primary',
          'auto' => [
              'start' => 93000000,
              'add' => 43
          ]
        ],
        'fullname' => [
          'type' => 'text',
          '__udms_config'
          => [
            'mysql_mysql' => [
              'charset' => [
                  'utf8' => 'utf8_persian_ci'
              ]
            ]
          ]
        ]
      ]
    ]
  ]
);
```

### Render

Render App model for Your selection addon:

``` php
// important: first set addon and next render!
$udms->render();
```

## Databases method

### Available Database Rule

Some addons (example mysql) with some config, Does not allow for Create Database. this method for test available create database.

``` php
if($udms->availableDatabaseRule()) {
  // available
} else {
  // not available
}
```

### Create Database

``` php
$udms->createDatabase('school',
  [] // config
);
```

### Exists Database

Check exists Database

``` php
if($udms->existsDatabase('school')) {
  // exists
} else {
  // not exists
}
```

### Drop Database

``` php
// Warning: delete databse dir in udms chache dir
$udms->dropDatabase('school');
```

### Rename Database

``` php
$udms->renameDatabase('school', 'to');
```

### List Databases

``` php
$list = $udms->listDatabases();
```

## Tables method

### Available Table Rule

Check available table rule

``` php
if($udms->school->availableTableRule()) {
  // available
} else {
  // not available
}
```

### Create Table

``` php
$udms->school->createTable('studnet',
  [] // config
);
```

### Exists Table

Check exist table

``` php
if($udms->school->existsTable('studnet')) {
  // exists
} else {
  // not exists
}
```

### Drop Table

``` php
$udms->school->dropTable('studnet');
```

### Rename Table

``` php
// Warning: delete databse dir in udms chache dir
$udms->school->renameTable('studnet', 'to');
```

### Clean Table

``` php
$udms->school->cleanTable('studnet');
```

### List Tables

``` php
$list = $udms->school->listTables();
```

### Columns method

### Available Column Rule

Check available columns rule

``` php
if($udms->school->availableColumnRule()) {
  // available
} else {
  // not available
}
```

### Create Column

``` php
$udms->school->student->createColumn('studnet',
  [] // config
);
```

### Exists Column

Check exists column

``` php
if($udms->school->student->existsColumn('studnet')) {
  // exists
} else {
  // not exists
}
```

### Drop Column

``` php
$udms->school->student->dropColumn('studnet');
```

### List Columns

``` php
$list = $udms->school->student->listColumns();
```

## Data methods

### Insert

``` php
$uid = $udms->school->student->insert(
  [
    'fname' => 'mehdi',
    'lname' => 'hosseinzade'
  ]
);
```

### Update

``` php
$udms->school->student->update($uid,
  [
    'fname' => 'arshen'
  ]
);
```

### Delete

``` php
$udms->school->student->delete($uid);
```

### Find

``` php
$list_1 = $udms->school->student->find(
  [
    'id' => [
      '<' => 9300000,
      '>' => 9200000
    ],
    'lname' => [
      'match' => '(zade)+'
    ]
  ],
  // options
  [
    'relation' => true,
    'sort' => [
      'name' => SORT_DESC
    ]
  ]
);
```
