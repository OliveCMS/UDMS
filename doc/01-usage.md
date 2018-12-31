# Using UDMS

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
  4. [Get](#get)

## Installation

olive-cms/udms is available on Packagist ([olive-cms/udms](http://packagist.org/packages/olive-cms/udms)) and as such installable via [Composer](http://getcomposer.org/).

```bash
composer require olive-cms/udms
```

If you do not use Composer, you can grab the code from GitHub, and use any PSR-0 compatible autoloader (e.g. the [Symfony2 ClassLoader component](https://github.com/symfony/ClassLoader)) to load Monolog classes.

## Core Concepts

*Coming soon!*

### Get udms

``` php
// load autoload
require_once '/path/to/vendor/autoload.php';
use Olive\UDMS\Core as udms;

// create a udms
$udms = new udms('/path/to/vendor/', '/path/to/database/dir');
```

## Addons

### Get available addons

``` php
$list = $udms->getAddonsList();
/* output array
 * example:
  Array
  (
      [0] => MySQL
      [1] => MongoDB
      [2] => Json
  )
 */
```

### Set addon

Set you Selection addon

``` php
// output void
$udms->setAddon('json');
```

## App Model

### Set App Model

``` php
$udms->setAppModel(
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
// output void
```

### Render

Render App model for Your selection addon:

``` php
/*
 * important: first set addon and next render!
 * output void
 */
$udms->render();
```

## Databases method

### Available Database Rule

Some addons (example mysql) with some config, Does not allow for Create Database. this method for test available create database.

``` php
// output Boolean
if($udms->availableDatabaseRule()) {
  // available
} else {
  // not available
}
```

### Create Database

``` php
// output void
$udms->createDatabase('school',
  [] // config
);
```

### Exists Database

Check exists Database

``` php
// output Boolean
if($udms->existsDatabase('school')) {
  // exists
} else {
  // not exists
}
```

### Drop Database

``` php
/*
 * Warning: delete databse dir in udms chache dir
 * output void
 */
$udms->dropDatabase('school');
```

### Rename Database

``` php
// output void
$udms->renameDatabase('school', 'to');
```

### List Databases

``` php
$list = $udms->listDatabases();
/* output array
 * example:
  Array
  (
      [0] => school
  )
 */
```

## Tables method

### Available Table Rule

Check available table rule

``` php
// output Boolean
if($udms->school->availableTableRule()) {
  // available
} else {
  // not available
}
```

### Create Table

``` php
// output void
$udms->school->createTable('studnet',
  [] // config
);
```

### Exists Table

Check exist table

``` php
// output Boolean
if($udms->school->existsTable('studnet')) {
  // exists
} else {
  // not exists
}
```

### Drop Table

``` php
// output void
$udms->school->dropTable('studnet');
```

### Rename Table

``` php
/*
 * Notice: rename database dir in udms cache dir
 * output void
 */
$udms->school->renameTable('studnet', 'to');
```

### Clean Table

``` php
// output void
$udms->school->cleanTable('studnet');
```

### List Tables

``` php
$list = $udms->school->listTables();
/* output array
 * example:
  Array
  (
      [0] => class
      [1] => students
      [2] => teacher
  )
 */
```

### Columns method

### Available Column Rule

Check available columns rule

``` php
// output Boolean
if($udms->school->availableColumnRule()) {
  // available
} else {
  // not available
}
```

### Create Column

``` php
// output void
$udms->school->student->createColumn('studnet',
  [] // config
);
/**
 * config available:
 * @param type -> [int, text]
 * @param length -> (0==ultimate)
 * @param index -> [primary]
 * @param auto -> [start => int, add => int]
 * @param __udms_rel -> [table => column]
 */
/*
# example 1:
$udms->school->classes->createColumn('id',
  [
    'type' => 'int',
    'lenght' => 8,
    'index' => 'primary',
    'auto' => [
      'start' => 1000000,
      'add' => 367
    ]
  ]
);

# example 2:

$udms->school->classes->createColumn('t_id',
  [
    '__udms_rel' => [
      'teacher' => 'id'
    ]
  ]
);
 */
```

### Exists Column

Check exists column

``` php
// output Boolean
if($udms->school->student->existsColumn('studnet')) {
  // exists
} else {
  // not exists
}
```

### Drop Column

``` php
// output void
$udms->school->student->dropColumn('studnet');
```

### List Columns

``` php
$list = $udms->school->student->listColumns();
/* output array
 * example:
  Array
  (
      [0] => id
      [1] => first_name
      [2] => last_name
  )
 */
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
/* output String
 * string "c4ca4238a0b923820dcc509a6f75849b"
 */
```

### Update

``` php
// output void
$udms->school->student->update($uid,
  [
    'fname' => 'arshen'
  ]
);
```

### Delete

``` php
// output void
$udms->school->student->delete($uid);
```

### Find

``` php
$list_1 = $udms->school->student->find(
  [
    'id' => [
      '<' => 9400000,
      '>' => 9200000
    ],
    'lname' => [
      'match' => '/(zade)+/'
    ]
  ],
  // options
  [
    'relation' => false,
    'sort' => [
      'name' => SORT_DESC
    ]
  ]
);
/* output array
 * example
 Array
 (
     [0] => Array
         (
             [fname] => mehdi
             [lname] => hosseinzade
             [id] => 93000000
             [__udms_id] => 529d1ff34a86af52e136858a8e7efc40
         )
 )
 */
```

### Get

alias find method without filter

``` php
$list_1 = $udms->school->student->get(
  [
    'relation' => false,
    'sort' => [
      'lname' => SORT_ASC
    ],
    'limit' => 5
  ]
);
/* output array
 * example:
 Array
 (
     [0] => Array
         (
             [fname] => alireza
             [lname] => aghaeipour
             [id] => 93000172
             [__udms_id] => 92277596d3fe0baaf6814caaa2f9fb17
         )

     [1] => Array
         (
             [fname] => mehdi
             [lname] => hosseinzade
             [id] => 93000000
             [__udms_id] => 529d1ff34a86af52e136858a8e7efc40
         )

     [2] => Array
         (
             [fname] => abolfazl
             [lname] => nazerpanah
             [id] => 93000129
             [__udms_id] => 390aa37bf6aabfa8af16bb729f02d207
         )

     [3] => Array
         (
             [fname] => mehrzad
             [lname] => poureghbal
             [id] => 93000043
             [__udms_id] => 64df99136dc56fc4ba306a1fffe75231
         )

     [4] => Array
         (
             [fname] => mohammad
             [lname] => rezaei
             [id] => 93000086
             [__udms_id] => 1fa3d2ab103b82e368c534f87720f8e2
         )
 )
 */
```
