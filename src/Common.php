<?php
namespace Olive\UDMS;

use Olive\Tools;
trait Common
{
    private $path;

    private $udmsCacheDir;

    private $reservedName = [];

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

    public function getDatabaseModel($name)
    {
        if (! isset($GLOBALS['__udms_global']['appModel'][$name])) {
            $GLOBALS['__udms_global']['appModel'][$name] = Tools::getJsonFile($this->getUCPath($name . '/appModel.json'));
        }

        return $GLOBALS['__udms_global']['appModel'][$name];
    }

    public function getDatabaseModelData($name)
    {
        if (! isset($GLOBALS['__udms_global']['appModelData'][$name])) {
            $GLOBALS['__udms_global']['appModelData'][$name] = Tools::getJsonFile($this->getUCPath($name . '/appModelData.json'));
        }

        return $GLOBALS['__udms_global']['appModelData'][$name];
    }

    private function updateDatabaseModel($name, $data = [])
    {
        Tools::file(($this->getUCPath($name . '/appModel.json')), Tools::jsonEncode($data));
        $GLOBALS['__udms_global']['appModel'][$name] = $data;
    }

    private function updateDatabaseModelData($name, $data = [])
    {
        Tools::file(($this->getUCPath($name . '/appModelData.json')), Tools::jsonEncode($data));
        $GLOBALS['__udms_global']['appModelData'][$name] = $data;
    }

    private function addLog($dec = '', $file = '', $line = 0)
    {
        Tools::file($this->getUCPath('error_logs'), '[' . date('c', time()) . "][$file:$line]: $dec\n", 'a+');
        echo $dec . "\n";
    }

    private function inReservedName($name)
    {
        if ($name != 'addLog' and $name != 'inReservedName' and $name != 'getPath' and $name != 'getUCPath' and $name != 'getDatabaseModel' and $name != 'getDatabaseModelData' and in_array($name, $this->reservedName)) {
            return true;
        } else {
            return false;
        }
    }
}
