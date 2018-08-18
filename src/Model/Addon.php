<?php
namespace Olive\UDMS\Model;

interface Addon
{
    // databases method
    public function createDatabase($name, $options);

    public function dropDatabase($name);

    public function existsDatabase($name);

    public function renameDatabase($name, $to);

    public function listDatabases();

    // tables method
    public function createTable($db, $name, $options);

    public function dropTable($db, $name);

    public function existsTable($db, $name);

    public function listTables($db);

    public function renameTable($db, $name, $to);

    // columns method
    public function createColumn($db, $table, $name, $options);

    public function existsColumn($db, $table, $name);

    public function listColumns($db, $table);

    public function dropColumn($db, $table, $name);

    // data methods
    public function insert($db, $table, $data);

    public function update($db, $table, $uid, $data);

    public function delete($db, $table, $uid);

    public function cleanTable($db, $table);

    public function get($db, $table);

    public function __construct($point, $option = []);
}
