<?php

$path = realpath(dirname(__FILE__) . '/../../') . '/';
function xfile($file, $content = '', $p = 'w')
{
    $file = fopen($file, $p);
    fwrite($file, $content);
    fclose($file);
}

function add_log($msg)
{
    global $path;
    xfile($path . 'test/src/lexe.sh', $msg, 'a+');
}

function banner($msg, $color = 'CYAN')
{
    $mt = explode(' ', microtime());
    add_log('echo "${WHITE}[' . date('Y-m-d:H:i:s', $mt[1]) . '.' . str_replace('0.', '', $mt[0]) . '] ${' . $color . '}' . "$msg\"\n");
}

function exidcode($code)
{
    banner('exit code: ' . $code, 'RED');
    add_log("exit $code\n");
    die();
}

add_log("\n");
add_log('RESTORE=$(echo -en \'\033[0m\')' . "\n");
add_log('RED=$(echo -en \'\033[00;31m\')' . "\n");
add_log('GREEN=$(echo -en \'\033[00;32m\')' . "\n");
add_log('YELLOW=$(echo -en \'\033[00;33m\')' . "\n");
add_log('BLUE=$(echo -en \'\033[00;34m\')' . "\n");
add_log('MAGENTA=$(echo -en \'\033[00;35m\')' . "\n");
add_log('PURPLE=$(echo -en \'\033[00;35m\')' . "\n");
add_log('CYAN=$(echo -en \'\033[00;36m\')' . "\n");
add_log('LIGHTGRAY=$(echo -en \'\033[00;37m\')' . "\n");
add_log('LRED=$(echo -en \'\033[01;31m\')' . "\n");
add_log('LGREEN=$(echo -en \'\033[01;32m\')' . "\n");
add_log('LYELLOW=$(echo -en \'\033[01;33m\')' . "\n");
add_log('LBLUE=$(echo -en \'\033[01;34m\')' . "\n");
add_log('LMAGENTA=$(echo -en \'\033[01;35m\')' . "\n");
add_log('LPURPLE=$(echo -en \'\033[01;35m\')' . "\n");
add_log('LCYAN=$(echo -en \'\033[01;36m\')' . "\n");
add_log('WHITE=$(echo -en \'\033[01;37m\')' . "\n");
banner('----------- Start -----------', 'WHITE');

require_once $path . 'vendor/autoload.php';

use Olive\UDMS\Core as udms;
use Olive\Tools;

$udms = new udms($path . 'test/database');

banner('#1 removeing all data...');
$dd = Tools::getDirList($udms->getUCPath());
foreach ($dd as $value) {
    Tools::rmDir($udms->getUCPath($value));
}
foreach ($dd as $value) {
    if (is_dir($udms->getUCPath($value))) {
        banner('failed remove data!!');
        exidcode('1');
    }
}
banner('removed all data');

$dms = $udms->getAddonsList();
banner('found (' . count($dms) . ') data manager');

include $path . 'test/src/simpleAppDatabaseModel.php';
$sdmdbs = [];
foreach ($sdm as $db => $value) {
    $sdmdbs[] = $db;
}
banner('#2 starting udms test...');

$i = 1;
foreach ($dms as $dm) {
    $start_micro = explode(' ', microtime());
    $start_micro = $start_micro[1] . '.' . str_replace('0.', '', $start_micro[0]);
    banner('#' . ($i + 2) . ' starting ' . $dm . ' addon test...', 'PURPLE');
    $udms->setAddon($dm, Tools::getJsonFile($udms->getPath('addons/' . $dm . '/test/tc.json')));

    banner('starting database service test...');

    banner('create "udms_testdb" database...');
    $udms->createDatabase('udms_testdb');
    if ($udms->existsDatabase('udms_testdb') == true) {
        banner('create "udms_testdb" database seccessfuly.', 'GREEN');
    } else {
        banner('create "udms_testdb" database failed', 'RED');
        exidcode('2');
    }

    banner('getting databases list...');
    $dl = $udms->listDatabases();
    if (in_array('udms_testdb', $dl)) {
        banner('getting databases list seccessfuly.', 'GREEN');
    } else {
        banner('getting databases list failed', 'RED');
        exidcode('11');
    }

    banner('rename "udms_testdb" database to "udms_testdb2"...');
    $udms->renameDatabase('udms_testdb', 'udms_testdb2');
    if ($udms->existsDatabase('udms_testdb2') == true) {
        banner('rename "udms_testdb" database to "udms_testdb2" seccessfuly.', 'GREEN');
    } else {
        banner('rename "udms_testdb" database to "udms_testdb2 failed', 'RED');
        exidcode('3');
    }

    banner('droping "udms_testdb2" database...');
    $udms->dropDatabase('udms_testdb2');
    if ($udms->existsDatabase('udms_testdb2') == false) {
        banner('droping "udms_testdb2" database seccessfuly.', 'GREEN');
    } else {
        banner('droping "udms_testdb2" database failed', 'RED');
        exidcode('4');
    }

    banner('database service testing done.');

    banner('starting table service over "udms_testdb" database test...');
    $udms->createDatabase('udms_testdb');

    banner('create "udms_testtable" table...');
    $udms->udms_testdb->createTable('udms_testtable');
    if ($udms->udms_testdb->existsTable('udms_testtable') == true) {
        banner('create "udms_testtable" table seccessfuly.', 'GREEN');
    } else {
        banner('create "udms_testtable" table failed', 'RED');
        exidcode('5');
    }

    banner('getting tables list...');
    $tl = $udms->udms_testdb->listTables();
    if (in_array('udms_testtable', $tl)) {
        banner('getting tables list seccessfuly.', 'GREEN');
    } else {
        banner('getting tables list failed', 'RED');
        exidcode('12');
    }

    banner('rename "udms_testtable" table to "udms_testtable2"...');
    $udms->udms_testdb->renameTable('udms_testtable', 'udms_testtable2');
    if ($udms->udms_testdb->existsTable('udms_testtable2') == true) {
        banner('rename "udms_testtable" table to "udms_testtable2" seccessfuly.', 'GREEN');
    } else {
        banner('rename "udms_testtable" table to "udms_testtable2 failed', 'RED');
        exidcode('6');
    }

    banner('droping "udms_testtable2" table...');
    $udms->udms_testdb->dropTable('udms_testtable2');
    if ($udms->udms_testdb->existsTable('udms_testtable2') == false) {
        banner('droping "udms_testtable2" table seccessfuly.', 'GREEN');
    } else {
        banner('droping "udms_testtable2" table failed', 'RED');
        exidcode('7');
    }

    banner('table service over "udms_testdb" database testing done.');

    banner('starting column service over "udms_testtable" table on "udms_testdb" database test...');
    $udms->udms_testdb->createTable('udms_testtable');

    banner('create "udms_testcol" column...');
    $udms->udms_testdb->udms_testtable->createColumn('udms_testcol', ['type' => 'int']);
    if ($udms->udms_testdb->udms_testtable->existsColumn('udms_testcol') == true) {
        banner('create "udms_testcol" column seccessfuly.', 'GREEN');
    } else {
        banner('create "udms_testcol" column failed', 'RED');
        exidcode('5');
    }

    banner('getting column list...');
    $cl = $udms->udms_testdb->udms_testtable->listColumns();
    if (in_array('udms_testcol', $cl)) {
        banner('getting column list seccessfuly.', 'GREEN');
    } else {
        banner('getting column list failed', 'RED');
        exidcode('13');
    }

    banner('droping "udms_testcol" column...');
    $udms->udms_testdb->udms_testtable->dropColumn('udms_testcol');
    if ($udms->udms_testdb->udms_testtable->existsColumn('udms_testcol') == false) {
        banner('droping "udms_testcol" column seccessfuly.', 'GREEN');
    } else {
        banner('droping "udms_testcol" column failed', 'RED');
        exidcode('7');
    }

    banner('table column over "udms_testtable" table on "udms_testdb" database testing done.');
    $udms->dropDatabase('udms_testdb');

    banner('done service testing.');

    banner('starting data service testing...');
    $udms->setAppDataModel($sdm);
    $udms->render();
    banner('importing data');
    include $path . 'test/src/import.php';
    banner('importing data done');
    $datatest = $udms->school->class->get(['relation' => true]);
    if ($datatest[1]['t_id']['lname'] == 'karimi' and $datatest[1]['t_id']['id'] == 73500 and $datatest[1]['c_id']['c_id']['id'] == 2 and $datatest[1]['c_id']['sub_id']['id'] == 1) {
        banner('data import test seccessfuly', 'GREEN');
    } else {
        banner('data import test failed', 'RED');
        exidcode('8');
    }

    banner('update data service testing...');
    $udms->school->teacher->update($t2,
        [
            'fname' => 'torabizade'
        ]
    );
    $udst = $udms->school->teacher->getByUid($t2);
    if ($udst['fname'] == 'torabizade') {
        banner('update data service testing seccessfuly.', 'GREEN');
    } else {
        banner('update data service testing is failed.', 'RED');
        exidcode('9');
    }

    banner('delete data service testing...');
    $udms->school->teacher->delete($t2);
    $ddst = $udms->school->teacher->getByUid($t2);
    if (count($ddst) == 0) {
        banner('delete data service testing seccessfuly.', 'GREEN');
    } else {
        banner('delete data service testing is failed.', 'RED');
        exidcode('10');
    }
    foreach ($sdmdbs as $db) {
        $udms->dropDatabase($db);
    }

    banner('data service testing is done.');

    banner('done ' . $dm . ' addon test.');
    $end_micro = explode(' ', microtime());
    $end_micro = $end_micro[1] . '.' . str_replace('0.', '', $end_micro[0]);
    banner($dm . ' addon test in ' . ($end_micro - $start_micro) . ' sec', 'LYELLOW');
    $i++;
}

banner('done udms test.');

banner('----------- End -----------', 'WHITE');
banner('exitcode: 0', 'GREEN');
add_log('exit 0');
