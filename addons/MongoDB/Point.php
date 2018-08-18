<?php
namespace Olive\UDMS\Addon\MongoDB;

use MongoDB as MDB;
use Olive\Tools;
use Olive\UDMS\Common as Common;
use Olive\UDMS\Exception\Custom as UException;
use Olive\UDMS\Model\Addon as Addon;
class Point implements Addon
{
    use Common;

    public $service;

    public $option;

    public $use_db;

    public $ui;

    private $ds = '__udms_ds';

    private $dse;

    private $cacheDBC;

    public function createDatabase($name, $options)
    {
        $create = $this->service->$name->__udms_table;
        $create->insertOne(
          [
            '__udms_id' => '0'
          ]
        );
        $dir = $this->getCore->getUCPath($name . '/mongodb/');
        if (! is_dir($dir)) {
            mkdir($dir);
        }
        $this->update_dbc($name);
    }

    public function dropDatabase($name)
    {
        $this->service->dropDatabase($name);
    }

    public function existsDatabase($name)
    {
        foreach ($this->service->listDatabases() as $db) {
            if ($db['name'] == $name) {
                return true;
            }
        }

        return false;
    }

    public function listDatabases()
    {
        $return = [];
        foreach ($this->service->listDatabases() as $db) {
            $return[] = $db['name'];
        }

        return $return;
    }

    private function set_database($name, $options = [])
    {
        if ($name != $this->ds) {
            $this->ds = $name;
            $this->dse = $this->service->$name;
            $this->use_db = $name;
        }
    }

    public function renameDatabase($name, $to)
    {
        $db = $this->service->admin;
        $options = $this->option;
        $db->command(
            [
                'copydb' => 1,
                'fromhost' => $options['host'],
                'fromdb' => $name,
                'todb' => $to
            ]
        );
        $this->dropDatabase($name);
    }

    public function createTable($db, $name, $options)
    {
        $this->set_database($db);
        $this->dse->createCollection($name);
        $gdbc = $this->get_dbc($db);
        $gdbc[$name] = [];
        $this->update_dbc($db, $gdbc);
        $this->createColumn($db, $name, '__udms_id', []);
    }

    public function dropTable($db, $name)
    {
        $this->set_database($db);
        $this->dse->dropCollection($name);
        $gdbc = $this->get_dbc($db);
        unset($gdbc[$name]);
        $this->update_dbc($db, $gdbc);
    }

    public function existsTable($db, $name)
    {
        $this->set_database($db);
        foreach ($this->dse->listCollections() as $ti) {
            if ($ti['name'] == $name) {
                return true;
            }
        }

        return false;
    }

    public function listTables($db)
    {
        $this->set_database($db);
        $return = [];
        foreach ($this->dse->listCollections() as $ti) {
            $return[] = $ti['name'];
        }

        return array_diff($return, ['__udms_table']);
    }

    public function renameTable($db, $name, $to)
    {
        $this->set_database($db);
        $this->service->admin->command(
            [
                'renameCollection' => $db . '.' . $name,
                'to' => $db . '.' . $to
            ]
        );
    }

    private function get_dbc($name)
    {
        if (is_null($this->cacheDBC)) {
            $this->cacheDBC = Tools::getJsonFile($this->getCore->getUCPath($name . '/mongodb/config.json'));
        }

        return $this->cacheDBC;
    }

    private function update_dbc($name, $data = [])
    {
        Tools::file($this->getCore->getUCPath($name . '/mongodb/config.json'), Tools::jsonEncode($data));
        $this->cacheDBC = $data;
    }

    public function createColumn($db, $table, $name, $options)
    {
        $gdbc = $this->get_dbc($db);
        $o = [];
        if (isset($options['__udms_config']['mongodb'])) {
            $o = $options['__udms_config']['mongodb'];
        }
        $gdbc[$table][$name] = $o;
        $this->update_dbc($db, $gdbc);
    }

    public function existsColumn($db, $table, $name)
    {
        $gdbc = $this->get_dbc($db);
        if (isset($gdbc[$table][$name])) {
            return true;
        } else {
            return false;
        }
    }

    public function listColumns($db, $table)
    {
        $gdbc = $this->get_dbc($db);

        return array_keys($gdbc[$table]);
    }

    public function dropColumn($db, $table, $name)
    {
        $gdbc = $this->get_dbc($db);
        $this->set_database($db);
        $this->dse->$table->updateMany(
            [
                $name => [
                    '$exists' => true
                ]
            ],
            [
                '$unset' => [
                    $name => '',
                ]
            ]
        );
        unset($gdbc[$table][$name]);
        $this->update_dbc($db, $gdbc);
    }

    public function insert($db, $table, $data)
    {
        $this->set_database($db);
        $this->dse->$table->insertOne($data);
    }

    public function update($db, $table, $uid, $data)
    {
        $this->set_database($db);
        $this->dse->$table->findOneAndUpdate(
            [
                '__udms_id' => $uid
            ],
            [
                '$set' => $data
            ]
        );
    }

    public function delete($db, $table, $uid)
    {
        $this->set_database($db);
        $this->dse->$table->findOneAndDelete(
            [
                '__udms_id' => $uid
            ]
        );
    }

    public function cleanTable($db, $table)
    {
        $this->dropTable($db, $table);
        $this->createTable($db, $table);
    }

    public function get($db, $table)
    {
        $this->set_database($db);
        $data = [];
        $datab = $this->dse->$table->find();
        foreach ($datab as $value) {
            unset($value['_id']);
            $data[] = (array) $value;
        }
        if ($data > 0) {
            return $data;
        } else {
            return [];
        }
    }

    public function __construct($point, $option = [])
    {
        $this->getCore = $point;
        $this->option = $option;
        if (isset($option['login'])) {
            $login = $option['login'];
            unset($option['login']);
        } else {
            $login = [];
        }

        try {
            $db = new MDB\Client($option['type'] . '://' . $option['host'], $login);
        } catch (Excepstion $e) {
            throw new UException($this->getCore->getUCPath(), 'Can not connect to MongoDB.', 200);
        }
        $this->service = $db;
    }
}
