# Addons Connect Instructions

- [MySQL](#mysql)
- [MongoDB](#mongodb)
- [json](#json)

## MySQL

By host:

``` php
$udms->setAddon('mysql' ,[
  'type' => 'mysql',
  'host' => 'localhost',
  'charset' => 'utf8mb4',
  'login' =>[
    'username' => 'root',
    'password' => ''
  ]
]);
```

By Unix socket:

``` php
$udms->setAddon('mysql' ,[
  'type' => 'mysql',
  'socket' => '/var/run/mysqld/mysqld.sock',
  'charset' => 'utf8mb4',
  'login' =>[
    'username' => 'root',
    'password' => ''
  ]
]);
```

## MongoDB

By host:

``` php
$udms->setAddon('mongodb' ,[
  'type' => 'mongodb',
  'host' => 'localhost'
]);
```

## json

By host:

``` php
$udms->setAddon('json');
```
