<?php
namespace Olive\UDMS\Model;

use Olive\UDMS\Exception\Custom as UException;
use Olive\UDMS\Common as Common;
use Olive\UDMS\Model\Table as Table;
class Database
{
    use Common;

    private $dbname;

    private $execute;

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
        $tl = $this->execute->listTables($this->dbname);
        if (is_array($tl)) {
            return $tl;
        } else {
            return [];
        }
    }

    public function existsTable($name)
    {
        if (! \Olive\UDMS\Core::validName($name)) {
            throw new UException($this->getUCPath(), $name . 'name is not valid!');
        }

        return $this->execute->existsTable($this->dbname, $name);
    }

    public function createTable($name, $options = [])
    {
        if (! \Olive\UDMS\Core::validName($name)) {
            throw new UException($this->getUCPath(), $name . 'name is not valid!');
        }
        if ($this->existsTable($name)) {
            throw new UException($this->getUCPath(), 'your table name has exists (' . $name . ')');
        }
        $this->execute->createTable($this->dbname, $name, $options);
        $ud = $this->getDatabaseModelData($this->dbname);
        $ud[$name]['auto']['__udms_id'] = [
          'start' => 1,
          'add' => 1,
          'last' => 0
        ];
        $this->updateDatabaseModelData($this->dbname, $ud);
        $ui = $this->getDatabaseModel($this->dbname);
        if (! isset($ui[$name])) {
            $ui[$name] = $options;
            $this->updateDatabaseModel($this->dbname, $ui);
        }
    }

    public function dropTable($name)
    {
        if (! \Olive\UDMS\Core::validName($name)) {
            throw new UException($this->getUCPath(), $name . 'name is not valid!');
        }
        if (! $this->existsTable($name)) {
            throw new UException($this->getUCPath(), 'your table name has not exists (' . $name . ')');
        }
        $this->execute->dropTable($this->dbname, $name);
        $ui = $this->getDatabaseModel($this->dbname);
        unset($ui[$name]);
        $this->updateDatabaseModel($this->dbname, $ui);

        $ud = $this->getDatabaseModelData($this->dbname);
        unset($ud[$name]);
        $this->updateDatabaseModelData($this->dbname, $ud);
    }

    public function cleanTable($name)
    {
        if (! \Olive\UDMS\Core::validName($name)) {
            throw new UException($this->getUCPath(), $name . 'name is not valid!');
        }
        if (! $this->existsTable($name)) {
            throw new UException($this->getUCPath(), 'your table name has not exists (' . $name . ')');
        }
        $this->execute->cleanTable($this->dbname, $name);
    }

    public function renameTable($name, $to)
    {
        if (! \Olive\UDMS\Core::validName($name)) {
            throw new UException($this->getUCPath(), $name . 'name is not valid!');
        }
        if (! \Olive\UDMS\Core::validName($to)) {
            throw new UException($this->getUCPath(), $to . 'name is not valid!');
        }
        if (! $this->existsTable($name)) {
            throw new UException($this->getUCPath(), 'your table name has not exists (' . $name . ')');
        }
        if ($this->existsTable($to)) {
            throw new UException($this->getUCPath(), 'your table name has exists (' . $name . ')');
        }
        $this->execute->renameTable($this->dbname, $name, $to);
        $ui = $this->getDatabaseModel($this->dbname);
        $ui[$to] = $ui[$name];
        unset($ui[$name]);
        $this->updateDatabaseModel($this->dbname, $ui);

        $ud = $this->getDatabaseModelData($this->dbname);
        $ud[$to] = $ud[$name];
        unset($ud[$name]);
        $this->updateDatabaseModelData($this->dbname, $ud);
    }

    public function __get($name)
    {
        if ($this->inReservedName($name)) {
            throw new UException($this->getUCPath(), 'your table name is reserved! (' . $name . ')');
        }
        if (! $this->existsTable($name)) {
            throw new UException($this->getUCPath(), 'your table name can not found! (' . $name . ')');
        }

        return new Table($this->dbname, $name, $this->path, $this->udmsCacheDir, $this->execute, $this->reservedName);
    }

    public function __toString()
    {
        return $this->dbname;
    }

    public function __construct($dbname, $path, $udmsCacheDir, $execute)
    {
        $this->setPath($path);
        $this->setUCPath($udmsCacheDir);
        $this->dbname = $dbname;
        $this->execute = $execute;
        $this->reservedName = get_class_methods($this);
    }
}
