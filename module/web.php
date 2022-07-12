<?php
class web extends common {

    function __construct() {
        $this->table_prefix();
        parent:: __construct();
        $_SESSION['language'] = isset($_SESSION['language']) ? $_SESSION['language'] : 1;
    }
    function _default() {
    }
    function language() {
        $sql = "SELECT * FROM {$this->prefix}language WHERE flag=0 ORDER BY english_name";
        $res = $this->m->query($sql);
        $l = $this->m->getall($res, 2, "english_name", "id_language");
        $this->sm->assign("language", $l);
    }
    function category() {
        $lang = $_SESSION['language'];
        $sql = "SELECT * FROM {$this->prefix}category WHERE id_language='$lang' ORDER BY name";
        $category = $this->m->getall($this->m->query($sql), 2, "regional_name", "id_category");
        $this->sm->assign("category", $category);
    }
    function setcategory() {
        $_SESSION['category'] = $_REQUEST['id'];
        $this->redirect("index.php");
    }
    function subcategory() {
        $this->get_permission("category", "REPORT");
        $lang = $_SESSION['language'];

        $sql = "SELECT * FROM {$this->prefix}category WHERE id_language='$lang' ORDER BY name";
        $category = $this->m->getall($this->m->query($sql), 2, "regional_name", "id_category");
        $this->sm->assign("category", $category);
    }
    function dashboard() {
        $_SESSION['guest'] = @$_SESSION['guest'] ? $_SESSION['guest'] : rand(1000000, 10000000);
        $lang = $_SESSION['language'];
        $sql = "SELECT * FROM {$this->prefix}question WHERE flag=0 AND id_language='$lang' ORDER BY RAND()  LIMIT 1 ";
        $q = $this->m->sql_getall($sql);
        $o = array($q[0]['option_1'],$q[0]['option_2'],$q[0]['option_3'],$q[0]['option_4']);
        shuffle($o);
        $q[0]['option_1'] = $o[0];
        $q[0]['option_2'] = $o[1];
        $q[0]['option_3'] = $o[2];
        $q[0]['option_4'] = $o[3];
        $this->sm->assign("q", $q);
    }
    function setlanguage() {
        $_SESSION['language'] = $_REQUEST['id'];
        $this->redirect("index.php");
    }
    function next() {
        $id = $_REQUEST['id'];
        $ans = $_REQUEST['ans'];
        $ip = $_SERVER['REMOTE_ADDR'];
        $id_user = @$_SESSION['id_user'] ? $_SESSION['id_user'] : $_SESSION['guest'];
        $date = date("Y-m-d h:i:s");
        $sql = "INSERT INTO {$this->prefix}response (id_question, answer, ip, id_user, create_date) VALUES ('$id', '$ans', '$ip', '$id_user', '$date')";
        $this->m->query($sql);
        $this->redirect("index.php");
    }
    function result() {
        $id_user = @$_SESSION['id_user'] ? $_SESSION['id_user'] : $_SESSION['guest'];
        $sql = "SELECT r.*, q.question, option_1, option_2, option_3, option_4, q.answer AS correct FROM 
                {$this->prefix}response r, {$this->prefix}question q WHERE q.id=r.id_question AND id_user='$id_user' ORDER BY r.create_date DESC";
        $a = $this->m->sql_getall($sql);
        $this->sm->assign("a", $a);
    }
    function social() {
    }
    function marketplace() {
    }
    function leaderboard() {
    }
    function trending() {
    }
    function sports() {
    }
    function gaming() {
    }
    function education() {
    }
    function music() {
    }
}
?>