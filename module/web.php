<?php
class web extends common {

    function __construct() {
        $this->table_prefix();
        parent:: __construct();
        $_SESSION['language'] = isset($_SESSION['language']) ? $_SESSION['language'] : 1;
    }
    function _default() {
    }
    function dashboard() {
        $sql = "SELECT * FROM {$this->prefix}language WHERE flag=0 ORDER BY regional_name";
        $res = $this->m->query($sql);
        $l = $this->m->getall($res, 2, "regional_name", "id");
        $this->sm->assign("language", $l);

        $lang = $_SESSION['language'];
        $sql = "SELECT * FROM {$this->prefix}question WHERE flag=0 AND id_language='$lang' ORDER BY RAND()  LIMIT 1 ";
        $q = $this->m->sql_getall($sql);
        $this->sm->assign("q", $q);
    }
    function setlanguage() {
        $_SESSION['language'] = $_REQUEST['id'];
        $this->redirect("index.php");
    }
    function getnextquestion() {

    }
}
?>