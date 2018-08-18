# UDMS Handle Errors

UDMS handle errors with Exception method:

``` php
try {
    $udms->createDatabase('school');
} catch (Excepstion $e) {
    $code = $e->getCode();
    $message = $e->getMessage();
}
```

## Core

* Code `100`: Can not access your UMDS Cache directory path!
* Code `101`: Addon class not found.
* Code `102`: your data model is not valid!
* Code `116`: get method: your database name is reserved!
* Code `117`: get method: can not found your database name!
* Render method
 1. Code `103`: render only with set addon. please first set your selection addon and next render!
 2. Code `104`: your data model is not valid!
 3. Code `105`: your database name is not valid!
 4. Code `106`: your table name is not valid!
 5. Code `107`: your column name is not valid!
* Set D2TMode
 1. Code `108`: your database name is not valid!
 2. Code `109`: your database name is reserved!
 3. Code `110`: your database d2tmode name not exist!
* Database methods
 1. Code `111`: in `createDatabase` your database name has exists!
 2. Code `112`: in `dropDatabase` your database name has not exists!
 3. Code `113`: in `existsDatabase` your database name is not valid!
 4. Code `114`: in `renameDatabase` $name: your database name has not exists!
 5. Code `115`: in `renameDatabase` $to: your database name has exists!
* Table methods
 1. Code `118`: in `existsTable` your table name is not valid!
 1. Code `119`: in `createTable` your table name has exists!
 2. Code `120`: in `dropTable` your table name has not exists!
 2. Code `121`: in `cleanTable` your table name has not exists!
 4. Code `122`: in `renameTable` $name: your table name has not exists!
 5. Code `123`: in `renameTable` $to: your table name has exists!
 6. Code `124`: get method: your table name is reserved!
 7. Code `125`: get method: can not found your table name!
* Column methods
 1. Code `126`: in `existsColumn` your column name is not valid!
 2. Code `127`: in `createColumn` your column name has exists
 3. Code `128`: in `createColumn` can not create column without option!
 4. Code `129`: in `dropColumn` your col name has not exists
 5. Code `130`: in `insert` your data can not empty for insert
 6. Code `131`: in `insert` your primary column value exists!
 7. Code `132`: in `insert` your primary column can not empty!
 8. Code `133`: in `update` your data can not empty for insert
 9. Code `134`: in `update` your primary column value exists!
 10. Code `135`: in `delete` your row in table not found
 11. Code `136`: in `isPrimary` your column name not exists
 12. Code `137`: in `isAuto` your column name not exists
 13. Code `138`: in `isRel` your column name not exists
 14. Code `139`: in `existsPrimary` your column name not exists
 15. Code `140`: in `existsPrimary` your column not exists in table

## Addons

### Json

*Not set*

### MongoDB

* Code `200`: Can not connect to MongoDB.

### MySQL

* Code `300`: Can not connect PDO to mysql.
