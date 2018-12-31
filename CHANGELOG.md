# Changelog

## v3.0.0

UDMS addons now install with composer!

* Json addon move to olive-cms/udms-json
* MySQL addon move to olive-cms/udms-mysql
* MongoDB addon move to olive-cms/udms-mongodb
* remove mongodb/mongodb require from Core
* update test

## v2.1.1

* fix not set default error

## v2.1

* add limit for find method
* fix mysql rel problem
* fix mysql insert and update string poblem
* update doc
* move colorconsole to dev

## v2.0

UDSM now have D2T (Database 2 Table) mode! this option is a solution for when Addon can not auto create database! with this mode, app can create auto database.

### Core changes:

* Core class
  1. review and rewrite all UDMS namespace classes
  2. rename AppDataModel to AppModel
  3. add `getAppModel`, `getAppModelData`, `setAppModelData`, `resetAppModel`, `setD2TMod`, `desD2TMode`, `getD2T` and `setD2T` methods
  4. rewrite `listDatabases`, `existsDatabase`, `createDatabase`, `renameDatabase` and `dropDatabase` for match with **D2TMode**
  5. rewrite `setAppModel` and `render` method
  6. review and rewrite optimize for `AppModel`, `AppModelData`, `DatabaseModel` and `DatabaseModelData` methods
  7. fix dropDatabase $keep bug
  8. fix type (core class -> Core class)
  9. fix valid name  check bug
  10. add errors code for Exception method
  11. update Documentation and Readme page
  12. add D2TMode Documentation
  13. add Errors Documentation
* Common class
  1. move path, `DatabaseModel`, `DatabaseModelData` and `addLog` methods to Core class
  2. add `getCore` to reserved name
* Database class:
  1. add `getName` method
  2. rewrite `listTable`, `createTable`, `existsTable`, `dropTable`, `renameTable` and `cleanTable` for match with **D2TMode**
* Table class:
  1. rewrite some line for match with **D2TMode**
  2. add columns list to DatabaseModelData
  3. set default value column type and lenght when null

### Addons changes:

* Json
  1. review and rewrite optimize for db and dbc functions
  2. rewrite point core methods
* MongoDB
  1. fix listTable function problem
  2. review and rewrite optimize for db and dbc functions
  3. rewrite point core methods
* MySQL
  1. fix get relation column type and lenght
  2. fix set null column type and lenght
  3. rewrite point core methods

### Test case changes:

1. rewrite with ColorConsole
2. fix rmDir use with new Olive\\Tools
3. add `D2TMode` case
4. add addon cases and full test time

## v1.0

release fast project.

* initial release
