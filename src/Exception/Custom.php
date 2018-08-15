<?php
namespace Olive\udms\exception;

use Olive\Tools;
use Exception;
class custom extends Exception
{
    public function __construct($udmsCacheDir, $message, $code = 0, Exception $previous = null)
    {
        if (is_dir($udmsCacheDir)) {
            Tools::file($udmsCacheDir . '/error_logs', '[' . date('c', time()) . '][' . $this->getFile() . ':' . $this->getLine() . "]: $message\n", 'a+');
        }
        parent::__construct($message, $code, $previous);
    }
}
