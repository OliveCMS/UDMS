<?php
namespace Olive\UDMS;

use Olive\Tools;
use Olive\UDMS\Common as Common;
use Olive\UDMS\Exception\Custom as UException;
use Olive\UDMS\Model\Database as Database;
class core
{
    use Common;

    private $selectedAddon;

    private $execute;

    // quickly function
    public static function validName($value = '')
    {
        return preg_match('/^([a-zA-Z])+(_)*([0-9a-zA-Z])*$/m', $value);
    }

    private function createDatabaseDir($name)
    {
        $dir = $this->getUCPath($name);
        if (! is_dir($dir)) {
            mkdir($dir);
        }
    }

    private function dropDatabaceDir($name)
    {
        Tools::rmDir($this->getUCPath($name));
    }

    private function renameDatabaceDir($name, $to)
    {
        $dn = $this->getUCPath($name);
        $dt = $this->getUCPath($to);
        if (is_dir($dn) and ! is_dir($dt)) {
            rename($dn, $dt);
        }
    }

    // main functions

    public function getAddonsList()
    {
        return Tools::getDirList($this->getPath('addons'));
    }

    public function setAddon($type, $option = [])
    {
        $type2 = '\\Olive\\UDMS\\Addon\\' . $type . '\\Point';
        if (! class_exists($type2)) {
            throw new UException($this->getUCPath(), $type2 . ' is not found!');
        }
        if (isset($GLOBALS['__udms_global'])) {
            unset($GLOBALS['__udms_global']);
        }
        $this->execute = new $type2($this->path, $this->udmsCacheDir, $option);
        $this->selectedAddon = $type;
    }

    public function getAddon()
    {
        return $this->selectedAddon;
    }

    public function validAppDataModel($model = [])
    {
        if (! is_array($model)) {
            return false;
        }
        foreach ($model as $db => $di) {
            if ($db == '__udms_config') {
                continue;
            }
            if (! $this->validName($db)) {
                $this->addLog($db . ' name is not valid!', __FILE__, __LINE__);

                return false;
            }
            if (! is_array($di)) {
                $this->addLog('Your database app data model not complete (' . $db . ')!', __FILE__, __LINE__);

                return false;
            }
            foreach ($di as $table => $ti) {
                if ($table == '__udms_config') {
                    continue;
                }
                if ($table == '__udms_id' and $table == '__udms_rel') {
                    $this->addLog($table . ' is reserved and you can not use for table name!', __FILE__, __LINE__);

                    return false;
                }
                if (! $this->validName($table)) {
                    $this->addLog($table . ' name is not valid!', __FILE__, __LINE__);

                    return false;
                }
                if (! is_array($ti)) {
                    $this->addLog('Your Table app data model not complete (' . $table . ')!', __FILE__, __LINE__);

                    return false;
                }
                foreach ($ti as $col => $ci) {
                    if ($col == '__udms_config') {
                        continue;
                    }
                    if ($col == '__udms_id' and $col == '__udms_rel') {
                        $this->addLog($col . ' is reserved and you can not use for col name!', __FILE__, __LINE__);

                        return false;
                    }
                    if (! $this->validName($col)) {
                        $this->addLog($col . ' name is not valid!', __FILE__, __LINE__);

                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function setAppDataModel($model = [])
    {
        if (! $this->validAppDataModel($model)) {
            throw new UException($this->getUCPath(), 'your data model is not valid!');
        }
        foreach ($model as $db => $di) {
            $this->createDatabaseDir($db);
            $this->updateDatabaseModel($db, $di);
            $dudf = $this->getUCPath($db . '/appModelData.json');
            if (! file_exists($dudf)) {
                $this->updateDatabaseModelData($db);
            }
        }
    }

    public function render()
    {
        if ($this->getAddon() == '') {
            throw new UException($this->getUCPath(), 'render only with set addon. please first set your selection addon and next render!');
        }
        $dbs = Tools::getDirList($this->getUCPath());
        foreach ($dbs as $db) {
            $ui = $this->getDatabaseModel($db);
            if (! $this->validAppDataModel([$db => $ui])) {
                throw new UException($this->getUCPath(), 'your data model is not valid!');
            }
            if (! isset($ui['__udms_config'])) {
                $ui['__udms_config'] = [];
            }
            if (! $this->validName($db)) {
                throw new UException($this->getUCPath(), $db . 'name is not valid!');
            }
            $this->createDatabase($db, $ui['__udms_config']);
            unset($ui['__udms_config']);
            foreach ($ui as $table => $ti) {
                if (! isset($ti['__udms_config'])) {
                    $ti['__udms_config'] = [];
                }
                if (! $this->validName($table)) {
                    throw new UException($this->getUCPath(), $table . 'name is not valid!');
                }
                $this->$db->createTable($table, $ti['__udms_config']);
                unset($ti['__udms_config']);
                foreach ($ti as $col => $ci) {
                    if (! $this->validName($col)) {
                        throw new UException($this->getUCPath(), $col . 'name is not valid!');
                    }
                    $this->$db->$table->createColumn($col, $ci);
                }
            }
        }
    }

    public function availableDatabaseRule()
    {
        $this->createDatabase('udms_adr');
        if ($this->existsDatabase('udms_adr')) {
            $this->dropDatabase('udms_adr');

            return true;
        } else {
            return false;
        }
    }

    public function listDatabases()
    {
        $dl = $this->execute->listDatabases();
        if (is_array($dl)) {
            return $dl;
        } else {
            return [];
        }
    }

    public function createDatabase($name, $option = [])
    {
        if (! $this->validName($name)) {
            throw new UException($this->getUCPath(), $name . 'name is not valid!');
        }
        if (! $this->existsDatabase($name)) {
            $this->createDatabaseDir($name);
            $this->execute->createDatabase($name, $option);
            $didf = $this->getUCPath($name . '/appModel.json');
            if (! file_exists($didf)) {
                if (count($option) > 0) {
                    $dm = [
                    '__udms_config' => $option
                  ];
                } else {
                    $dm = [];
                }
                $this->updateDatabaseModel($name, $dm);
            }
            $dudf = $this->getUCPath($name . '/appModelData.json');
            if (! file_exists($dudf)) {
                $this->updateDatabaseModelData($name);
            }
        } else {
            throw new UException($this->getUCPath(), 'your database name has exists (' . $name . ')!');
        }
    }

    public function dropDatabase($name, $keep = false)
    {
        if (! $this->validName($name)) {
            throw new UException($this->getUCPath(), $name . 'name is not valid!');
        }
        if ($this->existsDatabase($name)) {
            $this->execute->dropDatabase($name);
            if ($keep == false) {
                $this->dropDatabaceDir($name);
            }
        } else {
            throw new UException($this->getUCPath(), 'your database name has not exists (' . $name . ')');
        }
    }

    public function existsDatabase($name)
    {
        if (! $this->validName($name)) {
            throw new UException($this->getUCPath(), $name . 'name is not valid!');
        }

        return $this->execute->existsDatabase($name);
    }

    public function renameDatabase($name, $to)
    {
        if (! $this->validName($name)) {
            throw new UException($this->getUCPath(), $name . 'name is not valid!');
        }
        if (! $this->validName($to)) {
            throw new UException($this->getUCPath(), $to . 'name is not valid!');
        }
        if ($this->existsDatabase($to)) {
            throw new UException($this->getUCPath(), 'your database name exists (' . $to . ')');
        }
        if (! $this->existsDatabase($name)) {
            throw new UException($this->getUCPath(), 'your database name has not exists (' . $name . ')');
        }
        $this->execute->renameDatabase($name, $to);
        $this->renameDatabaceDir($name, $to);
    }

    public function __get($name)
    {
        if ($this->inReservedName($name)) {
            throw new UException($this->getUCPath(), 'your database name is reserved! (' . $name . ')');
        }
        if (! $this->existsDatabase($name)) {
            throw new UException($this->getUCPath(), 'your database name can not found! (' . $name . ')');
        }

        return new Database($name, $this->path, $this->udmsCacheDir, $this->execute);
    }

    public function __construct($udmsCacheDir = null)
    {
        if ($udmsCacheDir != null) {
            $ec = dirname($udmsCacheDir);
            if (! is_dir($ec)) {
                throw new UException($udmsCacheDir, 'Can not access your UMDS Cache directory path!');
            }
        }
        $this->setPath();
        $this->setUCPath($udmsCacheDir);
        if (! is_dir($this->getUCPath())) {
            mkdir($this->getUCPath());
        }
        $this->reservedName = get_class_methods($this);
    }
}
