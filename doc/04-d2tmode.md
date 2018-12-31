# D2tMode (Database 2 Table Mode)

# Using UDMS

- [Introduction](#introduction)
- [Concept](#concept)
- [Set D2TMode](#set-d2tmode)

## Introduction

this option is a solution for when Addon can not auto create database! (For check Addon can auto create database, App can use `availableDatabaseRule` method) With this mode, app can create auto database. for enable D2TMode first user manual create database then meet that to UDMS and done!

## Concept

*Coming soon*

## Set D2TMode

Receive exists database name az user and set D2TMode:

``` php
// load autoload
require_once '/path/to/vendor/autoload.php';
use Olive\UDMS\Core as udms;

// create a udms
$udms = new udms('/path/to/vendor/', '/path/to/database/dir');
$udms->setD2TMode('ReceivesDatabaseName');
```
