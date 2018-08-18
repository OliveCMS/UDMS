<?php
namespace Olive\UDMS\Model;

use Olive\UDMS\Common as Common;
use Olive\UDMS\Exception\Custom as UException;
use Olive\UDMS\Model\Table as Table;
class Database
{
    use Common;

    private $dbname;

    private function getName($name)
    {
        return $this->getCore->prefix . $name;
    }

    public function availableTableRule()
    {
        $this->createTable('udms_adr');
        if ($this->existsTable('udms_adr')) {
            $this->dropTable('udms_adr');

            return true;
        } else {
            return false;
        }
    }

    public function listTables()
    {
        $tl = $this->getCore->execute->listTables($this->dbname);
        if (is_array($tl)) {
            if ($this->getCore->prefix == '') {
                return $tl;
            } else {
                $list = [];
                foreach ($tl as $table) {
                    if (preg_match('/^(' . $this->getCore->prefix . ')/', $table)) {
                        $list[] = preg_replace('/^(' . $this->getCore->prefix . ')/', '', $table);
                    }
                }

                return $list;
            }
        } else {
            return [];
        }
    }

    public function existsTable($name)
    {
        $name = $this->getName($name);
        if (! \Olive\UDMS\Core::validName($name)) {
            throw new UException($this->getCore->getUCPath(), $name . 'name is not valid!', 118);
        }

        return $this->getCore->execute->existsTable($this->dbname, $name);
    }

    public function createTable($name, $options = [])
    {
        if ($this->existsTable($name)) {
            throw new UException($this->getCore->getUCPath(), 'your table name has exists (' . $name . ')', 119);
        }
        $name = $this->getName($name);
        $this->getCore->execute->createTable($this->dbname, $name, $options);
        $ud = $this->getCore->getDatabaseModelData($this->dbname);
        $ud[$name]['auto']['__udms_id'] = [
          'start' => 1,
          'add' => 1,
          'last' => 0
        ];
        $this->getCore->updateDatabaseModelData($this->dbname, $ud);
        $ui = $this->getCore->getDatabaseModel($this->dbname);
        if (! isset($ui[$name])) {
            $ui[$name] = $options;
            $this->getCore->updateDatabaseModel($this->dbname, $ui);
        }
    }

    public function dropTable($name)
    {
        if (! $this->existsTable($name)) {
            throw new UException($this->getCore->getUCPath(), 'your table name has not exists (' . $name . ')', 120);
        }
        $name = $this->getName($name);
        $this->getCore->execute->dropTable($this->dbname, $name);
        $ui = $this->getCore->getDatabaseModel($this->dbname);
        unset($ui[$name]);
        $this->getCore->updateDatabaseModel($this->dbname, $ui);

        $ud = $this->getCore->getDatabaseModelData($this->dbname);
        unset($ud[$name]);
        $this->getCore->updateDatabaseModelData($this->dbname, $ud);
    }

    public function cleanTable($name)
    {
        if (! $this->existsTable($name)) {
            throw new UException($this->getCore->getUCPath(), 'your table name has not exists (' . $name . ')', 121);
        }
        $name = $this->getName($name);
        $this->getCore->execute->cleanTable($this->dbname, $name);
    }

    public function renameTable($name, $to)
    {
        if (! $this->existsTable($name)) {
            throw new UException($this->getCore->getUCPath(), 'your table name has not exists (' . $name . ')', 122);
        }
        $name = $this->getName($name);
        if ($this->existsTable($to)) {
            throw new UException($this->getCore->getUCPath(), 'your table name has exists (' . $to . ')', 123);
        }
        $to = $this->getName($to);
        $this->getCore->execute->renameTable($this->dbname, $name, $to);
        $ui = $this->getCore->getDatabaseModel($this->dbname);
        $ui[$to] = $ui[$name];
        unset($ui[$name]);
        $this->getCore->updateDatabaseModel($this->dbname, $ui);

        $ud = $this->getCore->getDatabaseModelData($this->dbname);
        $ud[$to] = $ud[$name];
        unset($ud[$name]);
        $this->getCore->updateDatabaseModelData($this->dbname, $ud);
    }

    public function __get($name)
    {
        if ($this->inReservedName($this->getName($name))) {
            throw new UException($this->getCore->getUCPath(), 'your table name is reserved! (' . $name . ')', 124);
        }
        if (! $this->existsTable($name)) {
            throw new UException($this->getCore->getUCPath(), 'your table name can not found! (' . $name . ')', 125);
        }
        $this->getCore->ot = $name;

        return new Table($this->getCore, $this->dbname, $this->getName($name), $this->reservedName);
    }

    public function __toString()
    {
        return $this->dbname;
    }

    public function __construct($point, $dbname)
    {
        $this->getCore = $point;
        $this->dbname = $dbname;
        $this->reservedName = get_class_methods($this);
    }
}
