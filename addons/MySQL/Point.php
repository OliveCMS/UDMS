<?php
namespace Olive\UDMS\Addon\MySQL;

use Olive\UDMS\Common as Common;
use Olive\UDMS\Exception\Custom as UException;
use Olive\UDMS\Model\Addon as Addon;
use PDO;
class Point implements Addon
{
    use Common;

    public $service;

    public $use_db;

    public $type;

    public $ui;

    public $str_type = ['CHAR', 'VARCHAR', 'BINARY', 'VARBINARY', 'TEXT'];

    private $ds = '__udms_ds';

    public function createDatabase($name, $options)
    {
        $c = ';';
        foreach ($options as $key => $value) {
            if ($key == 'mysql_' . $this->type) {
                $e = key($value);
                if ($e != '') {
                    $c = ' CHARACTER SET ' . $e . ' COLLATE ' . $value[$e] . ';';
                }
            }
        }
        $this->service->exec('CREATE DATABASE ' . $name . $c);
    }

    public function dropDatabase($name)
    {
        $this->service->exec('DROP DATABASE ' . $name);
    }

    public function existsDatabase($name)
    {
        $service = $this->service;
        $query = $service->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :name');
        $query->bindValue(':name', $name, PDO::PARAM_STR);
        $query->execute();
        if ($query->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function listDatabases()
    {
        $service = $this->service;
        $query = $service->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA');
        $query->execute();
        $return = [];
        if ($query->rowCount() > 0) {
            $ld = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach ($ld as $db) {
                $return[] = $db['SCHEMA_NAME'];
            }
        }

        return $return;
    }

    private function set_database($name)
    {
        if ($name != $this->ds) {
            $this->ds = $name;
            $this->service->exec('USE ' . $name);
            $this->use_db = $name;
        }
    }

    public function renameDatabase($name, $to)
    {
        $ui = $this->getCore->getDatabaseModel($name);
        $tables = array_keys($ui);
        if (! isset($ui['__udms_config'])) {
            $ui['__udms_config'] = [];
        }
        $this->createDatabase($to, $ui['__udms_config']);
        foreach ($tables as $table) {
            if ($this->existsTable($name, $table)) {
                $this->service->exec('RENAME TABLE ' . $name . '.' . $table . ' TO ' . $to . '.' . $table);
            }
        }
        $this->dropDatabase($name);
    }

    public function createTable($db, $name, $options)
    {
        $this->set_database($db);
        $engine = 'MyISAM';
        $c = ';';
        foreach ($options as $key => $value) {
            if ($key == 'mysql_' . $this->type) {
                if (isset($value['engine'])) {
                    $engine = $value['engine'];
                }
                if (isset($value['charset'])) {
                    $d = $value['charset'];
                    $e = key($d);
                    $c = ' CHARSET=' . $e . ' COLLATE ' . $d[$e] . ';';
                }
            }
        }
        $this->service->exec('CREATE TABLE ' . $name . ' ( __udms_id VARCHAR(32) NOT NULL , PRIMARY KEY (__udms_id)) ENGINE = ' . $engine . $c . ';');
    }

    public function dropTable($db, $name)
    {
        $this->set_database($db);
        $this->service->exec('DROP TABLE ' . $name);
    }

    public function existsTable($db, $name)
    {
        $this->set_database($db);
        $service = $this->service;
        $query = $service->prepare('SELECT table_name FROM information_schema.tables WHERE table_schema = :db AND table_name = :name LIMIT 1');
        $query->bindValue(':name', $name, PDO::PARAM_STR);
        $query->bindValue(':db', $db, PDO::PARAM_STR);
        $query->execute();
        if ($query->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function listTables($db)
    {
        $this->set_database($db);
        $service = $this->service;
        $query = $service->prepare('SELECT table_name FROM information_schema.tables WHERE table_schema = :db');
        $query->bindValue(':db', $db, PDO::PARAM_STR);
        $query->execute();
        $return = [];
        if ($query->rowCount() > 0) {
            $lt = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach ($lt as $table) {
                $return[] = $table['table_name'];
            }
        }

        return $return;
    }

    public function renameTable($db, $name, $to)
    {
        $this->set_database($db);
        $this->service->exec('RENAME TABLE ' . $db . '.' . $name . ' TO ' . $db . '.' . $to);
    }

    public function createColumn($db, $table, $name, $options)
    {
        // check length available
        $this->set_database($db);
        $cm_detect = false;
        $rel_detect = false;
        $ui = $this->getCore->getDatabaseModel($this->getCore->od);
        if (isset($options['__udms_rel'])) {
            $d = $options['__udms_rel'];
            $e = key($d);
            $c = $d[$e];
            $options = $ui[$e][$c];
            $rel_detect = true;
        }
        if (isset($options['__udms_config']['mysql_' . $this->type])) {
            $ci = $options['__udms_config']['mysql_' . $this->type];
            $cm_detect = true;
        }
        if (! isset($options['type'])) {
            $options['type'] = 'text';
        }
        if ((isset($options['length']) and $options['length'] == '') or (! isset($options['length']) and in_array(strtoupper($options['type']), ['VARCHAR', 'CHAR', 'BIT', 'VARBINARY', 'BINARY']))) {
            $options['length'] = 1;
        }
        $query = 'ALTER TABLE `' . $table . '` ADD `' . $name . '` ' . $options['type'];
        if (isset($options['length']) and $options['type'] != 'text') {
            $query = $query . '(' . $options['length'] . ')';
        }
        if ($cm_detect == true) {
            if (isset($ci['charset'])) {
                $d = $ci['charset'];
                $e = key($d);
                $query = $query . ' CHARACTER SET ' . $e . ' COLLATE ' . $d[$e];
            }
            if (isset($ci['null']) and $ci['null']) {
                if ($rel_detect != true and ((isset($ci['primary']) and ! $ci['primary']) or ! isset($ci['primary']))) {
                    $query = $query . ' NULL';
                } else {
                    $this->getCore->addLog('col select primary key, so can not null!', __FILE__, __LINE__);
                }
            }
        }
        $query = $query . ' FIRST';
        if ($cm_detect == true and $rel_detect == false) {
            if (isset($ci['primary']) and $ci['primary']) {
                $query = $query . ', ADD PRIMARY KEY (' . $name . ');';
            }
        }
        $this->service->exec($query);
    }

    public function existsColumn($db, $table, $name)
    {
        $this->set_database($db);
        $service = $this->service;
        $query = $service->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tn AND COLUMN_NAME = :name');
        $query->bindValue(':name', $name, PDO::PARAM_STR);
        $query->bindValue(':tn', $table, PDO::PARAM_STR);
        $query->bindValue(':db', $db, PDO::PARAM_STR);
        $query->execute();
        if ($query->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function listColumns($db, $table)
    {
        $this->set_database($db);
        $service = $this->service;
        $query = $service->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tn');
        $query->bindValue(':tn', $table, PDO::PARAM_STR);
        $query->bindValue(':db', $db, PDO::PARAM_STR);
        $query->execute();
        $return = [];
        if ($query->rowCount() > 0) {
            $lc = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach ($lc as $column) {
                $return[] = $column['COLUMN_NAME'];
            }
        }

        return $return;
    }

    public function dropColumn($db, $table, $name)
    {
        $this->set_database($db);
        $this->service->exec('ALTER TABLE `' . $table . '` DROP `' . $name . '`');
    }

    public function insert($db, $table, $data)
    {
        $this->set_database($db);
        $ui = $this->getCore->getDatabaseModel($db);
        foreach ($data as $col => $value) {
            if ($col == '__udms_id') {
                $data[$col] = '\'' . $value . '\'';
            } else {
                $options = $ui[$table][$col];
                if (isset($options['__udms_rel'])) {
                    $d = $options['__udms_rel'];
                    $e = key($d);
                    $c = $d[$e];
                    $options = $ui[$e][$c];
                }
                if (in_array(strtoupper($options['type']), $this->str_type)) {
                    $data[$col] = '\'' . $value . '\'';
                }
            }
        }
        $this->service->exec('INSERT INTO `' . $table . '` (`' . implode('`,`', array_keys($data)) . '`) VALUES (' . implode(',', array_values($data)) . ');');
    }

    public function update($db, $table, $uid, $data)
    {
        $this->set_database($db);
        $ui = $this->getCore->getDatabaseModel($db);
        $do = [];
        foreach ($data as $col => $value) {
            if ($col == '__udms_id') {
                $data[$col] = '\'' . $value . '\'';
            } else {
                $options = $ui[$table][$col];
                if (isset($options['__udms_rel'])) {
                    $d = $options['__udms_rel'];
                    $e = key($d);
                    $c = $d[$e];
                    $options = $ui[$e][$c];
                }
                if (in_array(strtoupper($options['type']), $this->str_type)) {
                    $data[$col] = '\'' . $value . '\'';
                }
            }
            $do[] = "`$col` = " . $data[$col];
        }
        $this->service->exec("UPDATE `$table` SET " . implode(',', $do) . " WHERE `__udms_id` = '$uid';");
    }

    public function delete($db, $table, $uid)
    {
        $this->set_database($db);
        $this->service->exec('DELETE FROM `' . $table . '` WHERE `__udms_id` = \'' . $uid . '\'');
    }

    public function cleanTable($db, $table)
    {
        $this->set_database($db);
        $this->service->exec('TRUNCATE ' . $table);
    }

    public function get($db, $table)
    {
        $this->set_database($db);
        $service = $this->service;
        $query = $service->prepare('SELECT * FROM ' . $table);
        $query->execute();
        if ($query->rowCount() > 0) {
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [];
        }
    }

    public function __construct($point, $option = [])
    {
        $this->getCore = $point;
        if (! isset($option['charset'])) {
            $option['charset'] = 'utf8mb4';
        }
        $dbstr = $option['type'] . ':';
        if (isset($option['socket'])) {
            $dbstr = $dbstr . 'unix_socket=' . $option['socket'] . ';';
        } else {
            $dbstr = $dbstr . 'host=' . $option['host'] . ';';
        }
        if (isset($option['charset'])) {
            $dbstr = $dbstr . 'charset=' . $option['charset'] . ';';
        }

        try {
            $db = new PDO($dbstr, $option['login']['username'], $option['login']['password']);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (Excepstion $e) {
            throw new UException($this->getCore->getUCPath(), 'Can not connect PDO to mysql.', 300);
        }
        $this->service = $db;
        $this->type = $option['type'];
    }
}
