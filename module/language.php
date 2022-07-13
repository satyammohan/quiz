<?php
class language extends common {
    function __construct() {
        $this->checklogin();
        $this->table_prefix();
        parent:: __construct();
    }
    function listing() {
        $this->get_permission("language", "REPORT");
        if (isset($_REQUEST['flag'])) {
            $wcond = ($_REQUEST['flag'] == 2) ?  "" : " WHERE flag = " . $_REQUEST['flag'];
        } else {
            $_REQUEST['flag'] = 2;
            $wcond = "";
        }
        $sql = "SELECT * FROM {$this->prefix}language $wcond ORDER BY english_name";
        $data = $this->m->getall($this->m->query($sql));
        $this->sm->assign("language", $data);
    }
    function edit() {
        $id = isset($_REQUEST['id_language']) ? $_REQUEST['id_language'] : "0";
        $this->get_permission("language", ($id ? "INSERT" : "UPDATE"));
        $sql = $this->create_select($this->prefix . "language", "id_language='{$id}'");
        $data = $this->m->fetch_assoc($sql);
        $this->sm->assign("data", $data);
    }
    function insert() {
        $this->get_permission("language", "INSERT");
        $data = $_REQUEST['comp'];
        $data['flag'] = 0;
        $data['id_user'] = $_SESSION['id_user'];
        $data['create_date'] = date("Y-m-d h:i:s");
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        $sql = $this->create_insert($this->prefix . "language", $data);
        $res = $this->m->query($sql);
        $_SESSION['msg'] = "Record Successfully Inserted";
        $this->redirect("index.php?module=language&func=listing");
    }
    function update() {
        $this->get_permission("language", "UPDATE");
        $data =  $_REQUEST['comp'];
        $sql = $this->create_update($this->prefix . "language",$data, "id_language='{$_REQUEST['id']}'");
        $res = $this->m->query($sql);
        $_SESSION['msg'] = "Record Successfully Updated";
        $this->redirect("index.php?module=language&func=listing");
    }
    function delete() {
        $this->get_permission("language", "DELETE");
        $id = $_REQUEST['id_language'];
        $sql = "SELECT count(*) AS cnt FROM {$this->prefix}category WHERE id_language='$id'";
        $data = $this->m->fetch_assoc($sql);
        if ($data['cnt'] == 0) {
            $this->m->query($this->create_delete($this->prefix . "language", "id_language='$id'"));
            $_SESSION['msg'] = "Language Successfully Deleted";
        } else {
            $_SESSION['msg'] = "Category Exists. Language Delete not possible.";
        }
        $this->redirect("index.php?module=language&func=listing");
    }
}
?>