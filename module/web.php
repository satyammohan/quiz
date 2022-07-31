<?php
class web extends common {
    function __construct() {
        $this->table_prefix();
        parent:: __construct();
        if (!isset($_SESSION['language'])) {
            $sql = "SELECT * FROM {$this->prefix}language WHERE flag=0 ORDER BY id_language LIMIT 1";
            $l = $this->m->sql_getall($sql);
            $_SESSION['language'] = $l[0]['id_language'];
        }
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
        $sql = "SELECT * FROM {$this->prefix}category WHERE id_language='$lang' AND flag=0 ORDER BY name";
        $category = $this->m->getall($this->m->query($sql), 2, "regional_name", "id_category");
        $this->sm->assign("category", $category);
    }
    function subcategory() {
        $cat = $_SESSION['category'];
        $sql = "SELECT * FROM {$this->prefix}subcategory WHERE id_category='$cat' ORDER BY name";
        $scategory = $this->m->getall($this->m->query($sql), 2, "name", "id_subcategory");
        $this->sm->assign("subcategory", $scategory);
    }
    function setcategory() {
        unset($_SESSION['subcategory']);
        if ($_REQUEST['id']==0) {
            unset($_SESSION['category']);
        } else {
            $_SESSION['category'] = $_REQUEST['id'];
        }
        $this->redirect("index.php");
    }
    function setsubcategory() {
        if ($_REQUEST['id']==0) {
            unset($_SESSION['subcategory']);
        } else {
            $_SESSION['subcategory'] = $_REQUEST['id'];
        }
        $this->redirect("index.php");
    }
    function dashboard() {
        $_SESSION['guest'] = @$_SESSION['guest'] ? $_SESSION['guest'] : rand(1000000, 10000000);
        $lang = $_SESSION['language'];
        $wcond = " AND id_language='$lang' ";
        if (isset($_SESSION['category'])) {
            $cat = $_SESSION['category'];
            $wcond = " AND id_category='$cat' ";
        }
        if (isset($_SESSION['subcategory'])) {
            $scat = $_SESSION['subcategory'];
            $wcond = " AND id_subcategory='$scat' ";
        }
        $sql = "SELECT * FROM {$this->prefix}question WHERE flag=0 $wcond ORDER BY RAND()  LIMIT 1 ";
        $q = $this->m->sql_getall($sql);
        if (count($q)!=0) {
            $o = array($q[0]['option_1'],$q[0]['option_2']);
            if (@$q[0]['option_3']) {
                $o[2] = $q[0]['option_3'];
            }
            if (@$q[0]['option_4']) {
                $o[3] = $q[0]['option_4'];
            }
            shuffle($o);
            $q[0]['answer'] = ($q[0]['option_1']==$o[0]) ? 1 : 0;
            $q[0]['answer'] = ($q[0]['option_1']==$o[1]) ? 2 : $q[0]['answer'];
            $q[0]['answer'] = ($q[0]['option_1']==@$o[2]) ? 3 : $q[0]['answer'];
            $q[0]['answer'] = ($q[0]['option_1']==@$o[3]) ? 4 : $q[0]['answer'];

            $q[0]['option_1'] = $o[0];
            $q[0]['option_2'] = $o[1];
            $q[0]['option_3'] = @$o[2];
            $q[0]['option_4'] = @$o[3];
            $this->sm->assign("q", $q);
        }
    }
    function setlanguage() {
        $_SESSION['language'] = $_REQUEST['id'];
        unset($_SESSION['category']);
        unset($_SESSION['subcategory']);
        $this->redirect("index.php");
    }
    function next() {
        //if ($_SESSION['id_user']) {
            $id = $_REQUEST['id'];
            $ans = $_REQUEST['ans'];
            $res = $_REQUEST['result']=="true" ? 1 : 0;
            $ip = $_SERVER['REMOTE_ADDR'];
            $id_user = @$_SESSION['id_user'] ? $_SESSION['id_user'] : $_SESSION['guest'];
            $date = date("Y-m-d H:i:s");
            $sql = "INSERT INTO {$this->prefix}response (id_question, answer, result, ip, id_user, create_date) VALUES ('$id', '$ans', '$res', '$ip', '$id_user', '$date')";
            $this->m->query($sql);
        //}
        exit;
    }
    function result() {
        $id_user = @$_SESSION['id_user'] ? $_SESSION['id_user'] : $_SESSION['guest'];
        $sql = "SELECT r.*, q.question, option_1, option_2, option_3, option_4, q.answer AS correct FROM 
                {$this->prefix}response r, {$this->prefix}question q WHERE q.id=r.id_question AND id_user='$id_user' ORDER BY r.create_date DESC";
        $a = $this->m->sql_getall($sql);
        $this->sm->assign("a", $a);
    }
    function leader() {
        $sql = "SELECT u.name, SUM(r.result) AS total FROM user u, {$this->prefix}response r 
                WHERE u.id_user=r.id_user GROUP BY u.id_user ORDER BY total LIMIT 20";
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
