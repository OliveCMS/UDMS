<?php
namespace Olive\UDMS;

use Olive\Tools;
trait Common
{
    private $reservedName = [];

    private function inReservedName($name)
    {
        if ($name=='getCore' and $name != 'addLog' and $name != 'inReservedName' and $name != 'getPath' and $name != 'getUCPath' and $name != 'getDatabaseModel' and $name != 'getDatabaseModelData' and in_array($name, $this->reservedName)) {
            return true;
        } else {
            return false;
        }
    }
}
