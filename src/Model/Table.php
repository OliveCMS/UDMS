<?php
namespace Olive\UDMS\Model;

use Olive\UDMS\Exception\Custom as UException;
use Olive\UDMS\Common as Common;
class table
{
    use Common;

    private $dbname;

    private $table;

    private $execute;

    private function operator($value1, $operator, $value2)
    {
        switch ($operator) {
            case '<':
                return $value1 < $value2;

                break;
            case '<=':
                return $value1 <= $value2;

                break;
            case '>':
                return $value1 > $value2;

                break;
            case '>=':
                return $value1 >= $value2;

                break;
            case '==':
                return $value1 == $value2;

                break;
            case '!=':
                return $value1 != $value2;

                break;
            default:
                return false;
        }

        return false;
    }

    private function orderBy()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = [];
                foreach ($data as $key => $row) {
                    $tmp[$key] = $row[$field];
                }
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);

        return array_pop($args);
    }

    public function availableColumnRule()
    {
        $this->createColumn('udms_adr',
          [
            'type' => 'int',
            'length' => 7
          ]
        );
        if ($this->existsColumn('udms_adr')) {
            $this->dropColumn('udms_adr');

            return true;
        } else {
            return false;
        }
    }

    public function listColumns()
    {
        $cl = $this->execute->listColumns($this->dbname, $this->table);
        if (is_array($cl)) {
            return $cl;
        } else {
            return [];
        }
    }

    public function existsColumn($name)
    {
        return $this->execute->existsColumn($this->dbname, $this->table, $name);
    }

    public function createColumn($name, $options = [])
    {
        if (! \Olive\UDMS\Core::validName($name)) {
            throw new UException($this->getUCPath(), $name . 'name is not valid!');
        }
        if ($this->existsColumn($name)) {
            throw new UException($this->getUCPath(), 'your col name has exists (' . $name . ')');
        }
        if (count($options) == 0) {
            throw new UException($this->getUCPath(), 'can not create column without option!');
        }
        if (! isset($options['type'])) {
            $options['type'] = 'text';
        }
        $this->execute->createColumn($this->dbname, $this->table, $name, $options);
        if (isset($options['auto'])) {
            if (count($options['auto']) == 0) {
                $start = 1;
                $add = 1;
            } else {
                if (isset($options['auto']['start'])) {
                    $start = $options['auto']['start'];
                }
                if (isset($options['auto']['add'])) {
                    $add = $options['auto']['add'];
                }
            }
            $ud = $this->getDatabaseModelData($this->dbname);
            $ud[$this->table]['auto'][$name] = [
                  'start' => $start,
                  'add' => $add,
                  'last' => $start - $add
                ];
            $this->updateDatabaseModelData($this->dbname, $ud);
        }
        if (isset($options['__udms_rel'])) {
            $ud = $this->getDatabaseModelData($this->dbname);
            $ud[$this->table]['rels'][$name] = $options['__udms_rel'];
            $this->updateDatabaseModelData($this->dbname, $ud);
        }
        if (isset($options['index']) == 'primary') {
            $ud = $this->getDatabaseModelData($this->dbname);
            $ud[$this->table]['index']['primary'][] = $name;
            $this->updateDatabaseModelData($this->dbname, $ud);
        }
        $ui = $this->getDatabaseModel($this->dbname);
        if (! isset($ui[$this->table][$name])) {
            $ui[$this->table][$name] = $options;
            $this->updateDatabaseModel($this->dbname, $ui);
        }
    }

    public function dropColumn($name)
    {
        if (! \Olive\UDMS\Core::validName($name)) {
            throw new UException($this->getUCPath(), $name . 'name is not valid!');
        }
        if (! $this->existsColumn($name)) {
            throw new UException($this->getUCPath(), 'your col name has not exists (' . $name . ')');
        }
        $this->execute->dropColumn($this->dbname, $this->table, $name);
        $ui = $this->getDatabaseModel($this->dbname);
        unset($ui[$this->table][$name]);
        $this->updateDatabaseModel($this->dbname, $ui);
    }

    private function getNextInsert($col, $add = 0, $start = 0)
    {
        $ud = $this->getDatabaseModelData($this->dbname);
        $last = $ud[$this->table]['auto'][$col]['last'];
        $start = $ud[$this->table]['auto'][$col]['start'];
        if ($add == 0) {
            $add = $ud[$this->table]['auto'][$col]['add'];
        }
        do {
            $last = $last + $add;
        } while (count($this->find([$col => ['==' => $last]])) > 0);
        $ud[$this->table]['auto'][$col] = [
          'start' => $start,
          'add' => $add,
          'last' => $last
        ];
        $this->updateDatabaseModelData($this->dbname, $ud);

        return $last;
    }

    public function get($options = [])
    {
        return $this->find([], $options);
    }

    public function find($filters = [], $options = [])
    {
        $data = $this->execute->get($this->dbname, $this->table);
        $return = [];
        foreach ($data as $row) {
            $ok = 0;
            foreach ($filters as $col => $filter) {
                if ($this->existsColumn($col)) {
                    foreach ($filter as $o => $value) {
                        if ($o == 'match') {
                            if (! preg_match($value, $row[$col])) {
                                $ok = 1;

                                break;
                            }
                        } else {
                            if (! $this->operator($row[$col], $o, $value)) {
                                $ok = 1;

                                break;
                            }
                        }
                    }
                } else {
                    break;
                }
                if ($ok == 1) {
                    break;
                }
            }
            if ($ok == 0) {
                $ud = $this->getDatabaseModelData($this->dbname);
                if (isset($options['relation']) and $options['relation'] == true and isset($ud[$this->table]['rels'])) {
                    foreach ($ud[$this->table]['rels'] as $col => $ci) {
                        $e = key($ci);
                        $c = $ci[$e];
                        $rcc = 0;
                        if ($this->table != $e) {
                            $cie = 0;
                            if ($this->inReservedName($e)) {
                                $this->addLog('your table name is reserved! (' . $e . ')', __FILE__, __LINE__);
                                $cie = 1;
                            }
                            if (! $this->execute->existsTable($this->dbname, $e)) {
                                $this->addLog('your table name can not found! (' . $name . ')', __FILE__, __LINE__);
                                $cie = 1;
                            }
                            if ($cie == 0) {
                                $rc = new self($this->dbname, $e, $this->path, $this->udmsCacheDir, $this->execute, $this->reservedName);
                                $rcd = $rc->find([$c => ['==' => $row[$col]]], ['relation' => true]);
                                if ($rc->isPrimary($c)) {
                                    if (count($rcd) == 1) {
                                        $row[$col] = $rcd[0];
                                        $rcc = 1;
                                    }
                                }
                                if ($rcc == 0) {
                                    $row[$col] = $rcd;
                                }
                            }
                        } else {
                            $rcd = $this->find([$c => ['==' => $row[$col]]], ['relation' => true]);
                            if ($this->isPrimary($c)) {
                                if (count($rcd) == 1) {
                                    $row[$col] = $rcd[0];
                                    $rcc = 1;
                                }
                            }
                            if ($rcc == 0) {
                                $row[$col] = $rcd;
                            }
                        }
                    }
                }
                $return[] = $row;
            }
        }

        if (isset($options['sort']) and is_array($options['sort'])) {
            $args = [$return];
            foreach ($options['sort'] as $key => $value) {
                if (isset($return[0][$key])) {
                    $args[] = $key;
                    $args[] = $value;
                }
            }
            $return = call_user_func_array([$this, 'orderBy'], $args);
        }

        return $return;
    }

    public function insert($data = [])
    {
        if (isset($data['__udms_id'])) {
            unset($data['__udms_id']);
        }
        if (count($data) == 0) {
            throw new UException($this->getUCPath(), 'your data can not empty for insert');
        }
        $ui = $this->getDatabaseModel($this->dbname);
        $ud = $this->getDatabaseModelData($this->dbname);
        $cols = array_keys($data);
        $uics = array_keys($ui[$this->table]);
        $uacs = array_keys($ud[$this->table]['auto']);
        $upcs = $ud[$this->table]['index']['primary'];
        foreach ($cols as $col) {
            if ($this->existsColumn($col)) {
                if (in_array($col, $upcs)) {
                    if ($this->existsPrimary($col, $data[$col])) {
                        throw new UException($this->getUCPath(), 'your primary col (' . $col . ') value (' . $data[$col] . ') exists!');
                    }
                }
                $key = array_search($col, $uics);
                unset($uics[$key]);
            } else {
                unset($data[$col]);
            }
        }
        $uics[] = '__udms_id';
        foreach ($uics as $col) {
            if (in_array($col, $uacs)) {
                $data[$col] = $this->getNextInsert($col);
            }
            if (in_array($col, $upcs)) {
                if (! isset($data[$col])) {
                    throw new UException($this->getUCPath(), 'your primary col (' . $col . ') can not empty!');
                }
            }
        }
        $data['__udms_id'] = md5($data['__udms_id'] . time());
        //check type and length
        $this->execute->insert($this->dbname, $this->table, $data);

        return $data['__udms_id'];
    }

    public function update($uid, $data = [])
    {
        $d = $this->find(['__udms_id' => ['==' => $uid]]);
        if (count($d) == 0) {
            throw new UException($this->getUCPath(), 'your data (' . $uid . ') on (' . $this->table . ') not found!');
        }
        $ui = $this->getDatabaseModel($this->dbname);
        $ud = $this->getDatabaseModelData($this->dbname);
        $cols = array_keys($data);
        $upcs = $ud[$this->table]['index']['primary'];
        foreach ($cols as $col) {
            if ($this->existsColumn($col)) {
                if (in_array($col, $upcs)) {
                    if ($this->existsPrimary($col, $data[$col])) {
                        throw new UException($this->getUCPath(), 'your primary col (' . $col . ') value (' . $data[$col] . ') exists!');
                    }
                }
            } else {
                unset($data[$col]);
            }
        }
        $this->execute->update($this->dbname, $this->table, $uid, $data);
    }

    public function getByUid($uid)
    {
        $data = $this->find(['__udms_id' => ['==' => $uid]]);
        if (count($data) > 0) {
            return $data[0];
        } else {
            return [];
        }
    }

    public function delete($uid)
    {
        $d = $this->find(['__udms_id' => ['==' => $uid]]);
        if (count($d) == 0) {
            throw new UException($this->getUCPath(), 'your row (' . $uid . ') in table (' . $this->table . ') not found');
        }
        $this->execute->delete($this->dbname, $this->table, $uid);
    }

    public function isPrimary($col)
    {
        $ud = $this->getDatabaseModelData($this->dbname);
        if (in_array($col, $ud[$this->table]['index']['primary'])) {
            return true;
        } else {
            return false;
        }
    }

    public function isAuto($col)
    {
        $ud = $this->getDatabaseModelData($this->dbname);
        if (isset($ud[$this->table]['auto'][$col])) {
            return true;
        } else {
            return false;
        }
    }

    public function isRel($col)
    {
        $ud = $this->getDatabaseModelData($this->dbname);
        if (isset($ud[$this->table]['rels'][$col])) {
            return true;
        } else {
            return false;
        }
    }

    public function existsPrimary($col, $value)
    {
        if (! $this->existsColumn($col)) {
            throw new UException($this->getUCPath(), 'your col name not exists (' . $col . ')');
        }
        $d = $this->find([$col => ['==' => $value]]);
        if (count($d) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function uidToColumn($uid, $col)
    {
        if (! $this->existsColumn($col)) {
            throw new UException($this->getUCPath(), 'your col ' . $col . ' not exists in table (' . $this->table . ')');
        }
        $d = $this->find(['__udms_id' => ['==' => $uid]]);

        return $d[0][$col];
    }

    public function __toString()
    {
        return $this->table;
    }

    public function __construct($dbname, $table, $path, $udmsCacheDir, $execute, $reservedName)
    {
        $this->setPath($path);
        $this->setUCPath($udmsCacheDir);
        $this->dbname = $dbname;
        $this->table = $table;
        $this->execute = $execute;
        $this->reservedName = $reservedName;
    }
}
