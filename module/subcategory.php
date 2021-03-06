<?php
class subcategory extends common {
    function __construct() {
        $this->checklogin();
        $this->table_prefix();
        parent:: __construct();
    }
    function listing() {
        $this->get_permission("subcategory", "REPORT");
        if (isset($_REQUEST['flag'])) {
            $wcond = ($_REQUEST['flag'] == 2) ?  "" : " WHERE flag = " . $_REQUEST['flag'];
        } else {
            $_REQUEST['flag'] = 2;
            $wcond = " WHERE 1 ";
        }
        $wcond .= " AND id_category={$_REQUEST['id_category']}";
        $sql = "SELECT * FROM {$this->prefix}subcategory $wcond ORDER BY name";
        $data = $this->m->getall($this->m->query($sql));
        $this->sm->assign("subcategory", $data);
    }
    function search() {
        $filter = $_REQUEST['filter'];
        $id = $_REQUEST['id_category'];
        $sql = "SELECT id_subcategory AS id, name FROM {$this->prefix}subcategory WHERE id_category='$id' AND name LIKE '%$filter%'";
        $data = $this->m->sql_getall($sql);
        echo json_encode($data);
        exit;
    }
    function edit() {
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "0";
        $this->get_permission("subcategory", ($id ? "INSERT" : "UPDATE"));
        $sql = $this->create_select($this->prefix . "subcategory", "id_subcategory='{$id}'");
        $data = $this->m->fetch_assoc($sql);
        $this->sm->assign("data", $data);
    }
    function insert() {
        $this->get_permission("subcategory", "INSERT");
        $data = $_REQUEST['comp'];
        $data['flag'] = 0;
        $data['id_user'] = $_SESSION['id_user'];
        $data['create_date'] = date("Y-m-d h:i:s");
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        $data['id_category'] = $_REQUEST['id_category'];
        $sql = $this->create_insert($this->prefix . "subcategory", $data);
        $res = $this->m->query($sql);
        $_SESSION['msg'] = "Record Successfully Inserted";
        $this->redirect("index.php?module=subcategory&func=listing&id_category={$_REQUEST['id_category']}");
    }
    function update() {
        $this->get_permission("subcategory", "UPDATE");
        $data =  $_REQUEST['comp'];
        $sql = $this->create_update($this->prefix . "subcategory",$data, "id_subcategory='{$_REQUEST['id']}'");
        $res = $this->m->query($sql);
        $_SESSION['msg'] = "Record Successfully Updated";
        $this->redirect("index.php?module=subcategory&func=listing&id_category={$_REQUEST['id_category']}");
    }
    function delete() {
        $this->get_permission("subcategory", "DELETE");
        $id = $_REQUEST['id'];
        $sql = "SELECT count(*) AS cnt FROM {$this->prefix}question WHERE id_subcategory='$id'";
        $data = $this->m->fetch_assoc($sql);
        if ($data['cnt'] == 0) {
            $this->m->query($this->create_delete($this->prefix . "subcategory", "id_subcategory='$id'"));
            $_SESSION['msg'] = "Sub-Category Successfully Deleted";
        } else {
            $_SESSION['msg'] = "Question Exists. Sub-Category Delete not possible.";
        }
        $this->redirect("index.php?module=subcategory&func=listing&id_category={$_REQUEST['id_category']}");
    }
}
?>