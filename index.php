<?php
error_reporting(E_ALL);
ini_set('output_buffering', 0);
ini_set('display_errors', 'On');
set_time_limit(3000);
ini_set('max_execution_time', 300);
ini_set("memory_limit", "128M");
ini_set('session.gc_maxlifetime', 18000);
date_default_timezone_set("Asia/Calcutta");
session_set_cookie_params(18000);
session_start();
$smarty = getSmarty();
require 'config/mysql.php';
include "module/common.php";
call_class($_REQUEST);

unset($_SESSION['msg']);
unset($_SESSION['error']);

function getSmarty() {
    require '../Smarty-3/libs/Smarty.class.php';
    $smarty = new Smarty;
    $smarty->template_dir = array("templates");
    $smarty->compile_dir = "templates_c";
    $smarty->error_reporting = true;
    $smarty->force_compile = true;
    //$smarty->force_compile = false;
    $smarty->caching = 1;
    $smarty->registerPlugin("function", "get_page", "get_page");
    $smarty->registerPlugin("function", "get_name", "get_name");
    $smarty->registerPlugin("function", "convert", "convert");
    return $smarty;
}

function call_class($arr) {
    global $smarty;
    if (!isset($arr['module'])) {
        $mod = "web";
        $func = "dashboard";
    } else {
        $mod = $arr['module'];
        $func = $arr['func'];
    }
    include_once ("module/$mod.php");
    $call = new $mod();
    if (method_exists($call, $func)) {
        $call->$func();
    } else {
        $call->_default();
    }
    if (!isset($arr['ce'])) {
        $smarty->display("common/container.tpl.html");
    } else {
        $smarty->display("common/empty.tpl.html");
    }
}

function get_page($params, $smarty) {
    $params['ce'] = 1;
    return call_class($params);
}

function get_name($params, $smarty) {
    $arr = explode(",", $params['key']);
    $res = array();
    foreach ($arr as $k => $v) {
        $res[] = $params['list'][$v];
    }
    return implode(", ", $res);
}

function convert($first, $second) {
    if ($second == 1) {
        $ret = sprintf("%1\$.0f", $first);
        return $ret;
    } else {
        $neg = "";
        if ($first < 0) {
            $neg = "-";
            $first = abs($first);
        }
        $int = floor($first / $second);
        $dec = $first % $second;
        $ret = sprintf("%1\$.4f", $int + ($dec / 1000));
        return ($neg . $ret);
    }
}
?>