<?php
class web extends common {

    function __construct() {
        $this->table_prefix();
        parent:: __construct();
        $_SESSION['language'] = isset($_REQUEST['language']) ? $_REQUEST['language'] : 1;
    }
    function _default() {
        echo 1;
    }
    function dashboard() {
        $sql = "SELECT * FROM {$this->prefix}language ORDER BY regional_name";
        $res = $this->m->query($sql);
        $l = $this->m->getall($res, 2, "regional_name", "id");
        $this->sm->assign("language", $l);
        print_r($l);

        $lang =$_SESSION['language'];
        $sql = "SELECT * FROM {$this->prefix}question WHERE status=0 AND id_language='$lang' LIMIT 1 ";
        $quest = $this->m->sql_getall($sql);
        $this->sm->assign("question", $quest);
    }
    function setlanguage() {
        $_SESSION['language'] = $_REQUEST['language'];
    }
    function getnext() {

    }
}
?>