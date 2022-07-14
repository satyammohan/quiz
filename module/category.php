<?php
class category extends common {
    function __construct() {
        $this->checklogin();
        $this->table_prefix();
        parent:: __construct();
    }
    function listing() {
        $this->get_permission("category", "REPORT");
        if (isset($_REQUEST['flag'])) {
            $wcond = ($_REQUEST['flag'] == 2) ?  "" : " AND c.flag = " . $_REQUEST['flag'];
        } else {
            $_REQUEST['flag'] = 2;
            $wcond = "";
        }
        $sql = "SELECT c.*, l.english_name AS language_name, s.cnt 
                FROM {$this->prefix}language l, {$this->prefix}category c LEFT JOIN (SELECT id_category, COUNT(*) AS cnt FROM {$this->prefix}subcategory GROUP BY id_category) s ON c.id_category=s.id_category 
                WHERE c.id_language=l.id_language $wcond ORDER BY c.name";

        //SELECT c.*, l.english_name AS language_name, s.cnt FROM quizstation__language l, quizstation__category c LEFT JOIN (SELECT id_category, COUNT(*) AS cnt FROM quizstation__subcategory GROUP BY id_category) s ON c.id_category=s.id_category WHERE c.id_language=l.id_language ORDER BY c.name;
        $data = $this->m->getall($this->m->query($sql));
        $this->sm->assign("category", $data);
    }
    function search() {
        $filter = $_REQUEST['filter'];
        $sql = "SELECT id_category AS id, concat(name, '(', regional_name, ')') AS name FROM {$this->prefix}category WHERE name LIKE '%$filter%'";
        $data = $this->m->sql_getall($sql);
        echo json_encode($data);
        exit;
    }
    function edit() {
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "0";
        $this->get_permission("category", ($id ? "INSERT" : "UPDATE"));
        $sql = $this->create_select($this->prefix . "category", "id_category='{$id}'");
        $data = $this->m->fetch_assoc($sql);
        $this->sm->assign("data", $data);
        
        $sql = "SELECT * FROM {$this->prefix}language WHERE flag=0 ORDER BY english_name";
        $l = $this->m->getall($this->m->query($sql), 2, "english_name", "id_language");
        $this->sm->assign("language", $l);
    }
    function insert() {
        $this->get_permission("category", "INSERT");
        $data = $_REQUEST['comp'];
        $data['flag'] = 0;
        $data['id_user'] = $_SESSION['id_user'];
        $data['create_date'] = date("Y-m-d h:i:s");
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        $sql = $this->create_insert($this->prefix . "category", $data);
        $res = $this->m->query($sql);
        $_SESSION['msg'] = "Record Successfully Inserted";
        $this->redirect("index.php?module=category&func=listing");
    }
    function update() {
        $this->get_permission("category", "UPDATE");
        $data =  $_REQUEST['comp'];
        $sql = $this->create_update($this->prefix . "category",$data, "id_category='{$_REQUEST['id']}'");
        $res = $this->m->query($sql);
        $_SESSION['msg'] = "Record Successfully Updated";
        $this->redirect("index.php?module=category&func=listing");
    }
    function delete() {
        $this->get_permission("category", "DELETE");
        $id = $_REQUEST['id'];
        $sql = "SELECT count(*) AS cnt FROM {$this->prefix}subcategory WHERE id_category='$id'";
        $data = $this->m->fetch_assoc($sql);
        if ($data['cnt'] == 0) {
            $this->m->query($this->create_delete($this->prefix . "category", "id_category='$id'"));
            $_SESSION['msg'] = "Category Successfully Deleted";
        } else {
            $_SESSION['msg'] = "Sub-Category Exists. Category Delete not possible.";
        }
        $this->redirect("index.php?module=category&func=listing");
    }
}
?>