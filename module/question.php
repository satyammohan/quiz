<?php
class question extends common {
    function __construct() {
        $this->checklogin();
        $this->table_prefix();
        parent:: __construct();
    }
    function listing() {
        $this->get_permission("question", "REPORT");
        if (isset($_REQUEST['flag'])) {
            $wcond = ($_REQUEST['flag'] == 2) ?  "" : " WHERE flag = " . $_REQUEST['flag'];
        } else {
            $_REQUEST['flag'] = 2;
            $wcond = "";
        }
        $sql = "SELECT * FROM {$this->prefix}question $wcond ORDER BY id DESC LIMIT 100";
        $data = $this->m->getall($this->m->query($sql));
        $this->sm->assign("question", $data);
    }
    function edit() {
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "0";
        $this->get_permission("question", ($id ? "INSERT" : "UPDATE"));
        $sql = $this->create_select($this->prefix . "question", "id='{$id}'");
        $data = $this->m->fetch_assoc($sql);
        $this->sm->assign("data", $data);
    }
    function insert() {
        $this->get_permission("question", "INSERT");
        $data = $_REQUEST['comp'];
        $data['flag'] = 0;
        $data['id_user'] = $_SESSION['id_user'];
        $data['create_date'] = date("Y-m-d h:i:s");
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        $sql = $this->create_insert($this->prefix . "question", $data);
        $res = $this->m->query($sql);
        $_SESSION['msg'] = "Record Successfully Inserted";
        $this->redirect("index.php?module=question&func=listing");
    }
    function update() {
        $this->get_permission("question", "UPDATE");
        $data =  $_REQUEST['comp'];
        $sql = $this->create_update($this->prefix . "question",$data, "id='{$_REQUEST['id']}'");
        $res = $this->m->query($sql);
        $_SESSION['msg'] = "Record Successfully Updated";
        $this->redirect("index.php?module=question&func=listing");
    }
    function delete() {
        $this->get_permission("question", "DELETE");
        // $res = $this->m->query($this->create_delete($this->prefix . "question", "id='{$_REQUEST['id']}'"));
        // $_SESSION['msg'] = "Record Successfully Deleted";
        $_SESSION['msg'] = "Delete disabled. Action not Successful";
        $this->redirect("index.php?module=question&func=listing");
    }
}
?>