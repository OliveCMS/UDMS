<?php
namespace Olive\UDMS;

use Olive\Tools;
use Olive\UDMS\Common as Common;
use Olive\UDMS\Exception\Custom as UException;
use Olive\UDMS\Model\Database as Database;
class Core
{
    use Common;

    private $path;

    private $udmsCacheDir;

    private $selectedAddon;

    public $execute;

    private $d2tMode = false;

    public $prefix = '';

    private $cacheAppModel;

    private $cacheAppModelData;

    private $cacheD2T;

    public $debugc = 0;

    // quickly function

    private function setPath($path = null)
    {
        if ($path != null) {
            $e = str_split($path);
            if ($e[count($e) - 1] != '/') {
                $path = $path . '/';
            }
            $this->path = $path;
        } else {
            $this->path = realpath(dirname(__FILE__) . '/../') . '/';
        }
    }

    public function getPath($value = '')
    {
        return $this->path . $value;
    }

    private function setUCPath($path = null)
    {
        if ($path != null) {
            $e = str_split($path);
            if ($e[count($e) - 1] != '/') {
                $path = $path . '/';
            }
            $this->udmsCacheDir = $path;
        } else {
            $this->udmsCacheDir = $this->getPath();
        }
    }

    public function getUCPath($value = '')
    {
        return $this->udmsCacheDir . $value;
    }

    public static function validName($value = '')
    {
        return preg_match('/^[a-zA-Z]+[0-9a-zA-Z_]*$/m', $value);
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
        $this->execute = new $type2($this, $option);
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

    public function getAppDataModel()
    {
        return $this->cacheAppModel;
    }

    public function getAppDataModelData()
    {
        return $this->cacheAppModelData;
    }

    public function setAppDataModel($model = [])
    {
        if (! $this->validAppDataModel($model)) {
            throw new UException($this->getUCPath(), 'your data model is not valid!');
        }
        Tools::file($this->getUCPath('appModel.json'), Tools::jsonEncode($model));
        Tools::file($this->getUCPath('appModelData.json'), Tools::jsonEncode([]));
        $this->resetAppModel();
    }

    public function setAppDataModelData($model = [])
    {
        $this->cacheAppModelData = $model;
        Tools::file($this->getUCPath('appModelData.json'), Tools::jsonEncode($model));
    }

    private function resetAppModel()
    {
        $this->cacheAppModel = Tools::getJsonFile($this->getUCPath('appModel.json'));
        $this->cacheAppModelData = Tools::getJsonFile($this->getUCPath('appModelData.json'));
    }

    public function getDatabaseModel($name)
    {
        if (isset($this->cacheAppModel[$name])) {
            return $this->cacheAppModel[$name];
        }

        return [];
    }

    public function getDatabaseModelData($name)
    {
        if (isset($this->cacheAppModelData[$name])) {
            return $this->cacheAppModelData[$name];
        }

        return [];
    }

    public function updateDatabaseModel($name, $data = [])
    {
        $this->cacheAppModel[$name] = $data;
        Tools::file($this->getUCPath('appModel.json'), Tools::jsonEncode($this->cacheAppModel));
    }

    public function updateDatabaseModelData($name, $data = [])
    {
        $this->cacheAppModelData[$name] = $data;
        Tools::file($this->getUCPath('appModelData.json'), Tools::jsonEncode($this->cacheAppModelData));
    }

    public function addLog($dec = '', $file = '', $line = 0)
    {
        Tools::file($this->getUCPath('error_logs'), '[' . date('c', time()) . "][$file:$line]: $dec\n", 'a+');
        echo $dec . "\n";
    }

    public function render()
    {
        if ($this->getAddon() == '') {
            throw new UException($this->getUCPath(), 'render only with set addon. please first set your selection addon and next render!');
        }
        $dbs = $this->getAppDataModel();
        foreach ($dbs as $db => $ui) {
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

    public function setD2TMode($database = '')
    {
        if (! $this->validName($database)) {
            throw new UException($this->getUCPath(), $database . ' name is not valid!');
        }
        if ($this->inReservedName($database)) {
            throw new UException($this->getUCPath(), 'your database name is reserved! (' . $database . ')');
        }
        if (! $this->existsDatabase($database)) {
            throw new UException($this->getUCPath(), 'database name selected (' . $database . ') in d2tMode not exists.');
        }

        $this->createDatabaseDir($database);
        $this->d2tDatabase = $database;
        $this->d2t = new Database($this, $database);
        $this->d2tMode = true;
    }

    public function desD2TMode()
    {
        $this->d2tDatabase = '';
        unset($this->d2t);
        $this->d2tMode = false;
        $this->prefix = '';
        $this->resetAppModel();
        $this->setD2T();
    }

    public function getD2T()
    {
        if (is_null($this->cacheD2T)) {
            $this->cacheD2T = Tools::getJsonFile($this->getUCPath('d2t.json'));
        }

        return $this->cacheD2T;
    }

    private function setD2T($data = [])
    {
        $this->cacheD2T = $data;
        Tools::file(($this->getUCPath('d2t.json')), Tools::jsonEncode($data));
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
        if ($this->d2tMode == false) {
            $dl = $this->execute->listDatabases();
        } else {
            $dl = $this->getD2T();
        }
        if (is_array($dl)) {
            return $dl;
        } else {
            return [];
        }
    }

    public function createDatabase($name, $option = [])
    {
        if ($this->existsDatabase($name)) {
            throw new UException($this->getUCPath(), 'your database name has exists (' . $name . ')!');
        }
        if ($this->d2tMode == false) {
            $this->createDatabaseDir($name);
            $this->execute->createDatabase($name, $option);
        } else {
            $dl = $this->getD2T();
            $dl[] = $name;
            $this->setD2T($dl);
        }
    }

    public function dropDatabase($name)
    {
        if ($this->existsDatabase($name)) {
            if ($this->d2tMode == false) {
                $this->execute->dropDatabase($name);
                $this->dropDatabaceDir($name);
            } else {
                $dtl = $this->d2t->listTables();
                foreach ($dtl as $dt) {
                    $this->d2t->dropTable($dt);
                }
                $this->setD2T(array_diff($this->getD2T(), [$name]));
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
        if ($this->d2tMode == false) {
            return $this->execute->existsDatabase($name);
        } else {
            return in_array($name, $this->listDatabases());
        }
    }

    public function renameDatabase($name, $to)
    {
        if ($this->existsDatabase($to)) {
            throw new UException($this->getUCPath(), 'your database name exists (' . $to . ')');
        }
        if (! $this->existsDatabase($name)) {
            throw new UException($this->getUCPath(), 'your database name has not exists (' . $name . ')');
        }
        if ($this->d2tMode == false) {
            $this->execute->renameDatabase($name, $to);
            $this->renameDatabaceDir($name, $to);
        } else {
            $dtl = $this->d2t->listTables();
            foreach ($dtl as $dt) {
                if ($dt == $name) {
                    $this->d2t->renameTable($name, $to);

                    break;
                }
            }
            $dl = $this->getD2T();
            $key = array_search($name, $dl);
            $dl[$key] = $to;
            $this->setD2T($dl);
        }
    }

    public function __get($name)
    {
        if ($this->inReservedName($name)) {
            throw new UException($this->getUCPath(), 'your database name is reserved! (' . $name . ')');
        }
        if (! $this->existsDatabase($name)) {
            throw new UException($this->getUCPath(), 'your database name can not found! (' . $name . ')');
        }
        $this->od = $name;
        if ($this->d2tMode == false) {
            return new Database($this, $name);
        } else {
            $this->prefix = $name . '_';

            return $this->d2t;
        }
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
        $af = $this->getUCPath('appModel.json');
        $adf = $this->getUCPath('appModelData.json');
        if (! file_exists($af)) {
            Tools::file($af, '[]');
        }
        if (! file_exists($adf)) {
            Tools::file($adf, '[]');
        }
        $this->resetAppModel();
        $this->reservedName = get_class_methods($this);
    }
}
