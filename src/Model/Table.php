<?php
namespace Olive\UDMS\Model;

use Olive\UDMS\Common as Common;
use Olive\UDMS\Exception\Custom as UException;
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
        $cl = $this->getCore->execute->listColumns($this->dbname, $this->table);
        if (is_array($cl)) {
            return $cl;
        } else {
            return [];
        }
    }

    public function existsColumn($name)
    {
        if ($name != '__udms_id' and ! \Olive\UDMS\Core::validName($name)) {
            throw new UException($this->getCore->getUCPath(), $name . ' name is not valid!', 126);
        }

        return $this->getCore->execute->existsColumn($this->dbname, $this->table, $name);
    }

    public function createColumn($name, $options = [])
    {
        if ($this->existsColumn($name)) {
            throw new UException($this->getCore->getUCPath(), 'your column name has exists (' . $name . ')', 127);
        }
        if (count($options) == 0) {
            throw new UException($this->getCore->getUCPath(), 'can not create column without option!', 128);
        }
        if (! isset($options['type']) or (isset($options['type']) and $options['type'] == '')) {
            $options['type'] = 'text';
        }
        if (! isset($options['length'])) {
            $options['length'] = '';
        }
        $this->getCore->execute->createColumn($this->dbname, $this->table, $name, $options);
        $ud = $this->getCore->getDatabaseModelData($this->dbname);
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
            $ud[$this->table]['auto'][$name] = [
              'start' => $start,
              'add' => $add,
              'last' => $start - $add
            ];
        }
        if (isset($options['__udms_rel'])) {
            $tableo = key($options['__udms_rel']);
            $table = $this->getCore->prefix . $tableo;
            $ud[$this->table]['rels'][$name][$table] = $options['__udms_rel'][$tableo];
            if ($this->getCore->prefix != '') {
                $options['__udms_rel'][$table] = $options['__udms_rel'][$tableo];
                unset($options['__udms_rel'][$tableo]);
            }
        }
        if (isset($options['index']) == 'primary') {
            $ud[$this->table]['index']['primary'][] = $name;
        }
        $ud[$this->table]['columns'][$name] = $options;
        $this->getCore->updateDatabaseModelData($this->dbname, $ud);
        $ui = $this->getCore->getDatabaseModel($this->dbname);
        if (! isset($ui[$this->table][$name])) {
            $ui[$this->table][$name] = $options;
            $this->getCore->updateDatabaseModel($this->dbname, $ui);
        }
    }

    public function dropColumn($name)
    {
        if (! $this->existsColumn($name)) {
            throw new UException($this->getCore->getUCPath(), 'your col name has not exists (' . $name . ')', 129);
        }
        $this->getCore->execute->dropColumn($this->dbname, $this->table, $name);
        $ui = $this->getCore->getDatabaseModel($this->dbname);
        unset($ui[$this->table][$name]);
        $this->getCore->updateDatabaseModel($this->dbname, $ui);
        $ud = $this->getCore->getDatabaseModelData($this->dbname);
        unset($ud[$this->table][$name]);
        $this->getCore->updateDatabaseModelData($this->dbname, $ud);
    }

    private function getNextInsert($col, $add = 0, $start = 0)
    {
        $ud = $this->getCore->getDatabaseModelData($this->dbname);
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
        $this->getCore->updateDatabaseModelData($this->dbname, $ud);

        return $last;
    }

    public function get($options = [])
    {
        return $this->find([], $options);
    }

    public function find($filters = [], $options = [])
    {
        $data = $this->getCore->execute->get($this->dbname, $this->table);
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
                $ud = $this->getCore->getDatabaseModelData($this->dbname);
                if (isset($options['relation']) and $options['relation'] == true and isset($ud[$this->table]['rels'])) {
                    foreach ($ud[$this->table]['rels'] as $col => $ci) {
                        $e = key($ci);
                        $c = $ci[$e];
                        $rcc = 0;
                        if ($this->table != $e) {
                            $cie = 0;
                            if ($this->inReservedName($e)) {
                                $this->getCore->addLog('your table name is reserved! (' . $e . ')', __FILE__, __LINE__);
                                $cie = 1;
                            }
                            if (! $this->getCore->execute->existsTable($this->dbname, $e)) {
                                $this->getCore->addLog('your table name can not found! (' . $e . ')', __FILE__, __LINE__);
                                $cie = 1;
                            }
                            if ($cie == 0) {
                                $rc = new self($this->getCore, $this->dbname, $e, $this->reservedName);
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
        if (isset($options['limit']) and is_int($options['limit']) and $options['limit']>0) {
            $return = array_slice($return, 0, $options['limit']);
        }

        return $return;
    }

    public function insert($data = [])
    {
        if (isset($data['__udms_id'])) {
            unset($data['__udms_id']);
        }
        if (count($data) == 0) {
            throw new UException($this->getCore->getUCPath(), 'your data can not empty for insert', 130);
        }
        $ud = $this->getCore->getDatabaseModelData($this->dbname);
        $cols = array_keys($data);
        $uics = array_keys($ud[$this->table]['columns']);
        $uacs = array_keys($ud[$this->table]['auto']);
        $upcs = $ud[$this->table]['index']['primary'];
        foreach ($cols as $col) {
            if ($this->existsColumn($col)) {
                if (in_array($col, $upcs)) {
                    if ($this->existsPrimary($col, $data[$col])) {
                        throw new UException($this->getCore->getUCPath(), 'your primary col (' . $col . ') value (' . $data[$col] . ') exists!', 131);
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
                    throw new UException($this->getCore->getUCPath(), 'your primary col (' . $col . ') can not empty!', 132);
                }
            }
        }
        $data['__udms_id'] = md5($data['__udms_id'] . time());
        //check type and length
        $this->getCore->execute->insert($this->dbname, $this->table, $data);

        return $data['__udms_id'];
    }

    public function update($uid, $data = [])
    {
        $d = $this->find(['__udms_id' => ['==' => $uid]]);
        if (count($d) == 0) {
            throw new UException($this->getCore->getUCPath(), 'your data (' . $uid . ') on (' . $this->table . ') not found!', 133);
        }
        $ud = $this->getCore->getDatabaseModelData($this->dbname);
        $cols = array_keys($data);
        $upcs = $ud[$this->table]['index']['primary'];
        foreach ($cols as $col) {
            if ($this->existsColumn($col)) {
                if (in_array($col, $upcs)) {
                    if ($this->existsPrimary($col, $data[$col])) {
                        throw new UException($this->getCore->getUCPath(), 'your primary col (' . $col . ') value (' . $data[$col] . ') exists!', 134);
                    }
                }
            } else {
                unset($data[$col]);
            }
        }
        $this->getCore->execute->update($this->dbname, $this->table, $uid, $data);
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
            throw new UException($this->getCore->getUCPath(), 'your row (' . $uid . ') in table (' . $this->table . ') not found', 135);
        }
        $this->getCore->execute->delete($this->dbname, $this->table, $uid);
    }

    public function isPrimary($col)
    {
        if (! $this->existsColumn($col)) {
            throw new UException($this->getCore->getUCPath(), 'your column name not exists (' . $col . ')', 136);
        }
        $ud = $this->getCore->getDatabaseModelData($this->dbname);
        if (in_array($col, $ud[$this->table]['index']['primary'])) {
            return true;
        } else {
            return false;
        }
    }

    public function isAuto($col)
    {
        if (! $this->existsColumn($col)) {
            throw new UException($this->getCore->getUCPath(), 'your column name not exists (' . $col . ')', 137);
        }
        $ud = $this->getCore->getDatabaseModelData($this->dbname);
        if (isset($ud[$this->table]['auto'][$col])) {
            return true;
        } else {
            return false;
        }
    }

    public function isRel($col)
    {
        if (! $this->existsColumn($col)) {
            throw new UException($this->getCore->getUCPath(), 'your column name not exists (' . $col . ')', 138);
        }
        $ud = $this->getCore->getDatabaseModelData($this->dbname);
        if (isset($ud[$this->table]['rels'][$col])) {
            return true;
        } else {
            return false;
        }
    }

    public function existsPrimary($col, $value)
    {
        if (! $this->existsColumn($col)) {
            throw new UException($this->getCore->getUCPath(), 'your column name not exists (' . $col . ')', 139);
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
            throw new UException($this->getCore->getUCPath(), 'your column ' . $col . ' not exists in table (' . $this->table . ')', 140);
        }
        $d = $this->find(['__udms_id' => ['==' => $uid]]);

        return $d[0][$col];
    }

    public function __toString()
    {
        return $this->table;
    }

    public function __construct($point, $dbname, $table, $reservedName)
    {
        $this->getCore = $point;
        $this->dbname = $dbname;
        $this->table = $table;
        $this->reservedName = $reservedName;
    }
}
