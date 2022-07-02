<?php
class party extends common {
    function __construct() {
        $this->checklogin();
        $this->table_prefix();
        parent:: __construct();
    }
    function insert() {
        $this->saveactivity("New Party Added.");
        $data = $_REQUEST['entry'];
        $data['id_head'] = $_SESSION['id_user'];
        $data['status'] = 0;
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        $data['opening_balance'] = $data['opening_balance'] ? $data['opening_balance'] : 0;
        $data['id_create'] = $_SESSION['id_user'];
        $data['id_modify'] = $_SESSION['id_user'];
        $data['create_date'] = date("Y-m-d h:i:s");
        $sql = $this->create_insert("{$this->prefix}partner_party", $data);
        $this->m->query($sql);
        $_SESSION['msg'] = "Party Added Successfully.";
        $this->redirect("index.php?module=party&func=listing");
    }
    function update() {
        $data = $_REQUEST['entry'];
        $hid = $data['id_head'] = $_SESSION['id_user'];
        $data['opening_balance'] = $data['opening_balance'] ? $data['opening_balance'] : 0;
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "0";
        $this->saveactivity("Party updated for id : $id.");
        $sql = $this->create_update("{$this->prefix}partner_party", $data, "id_party='{$id}' AND id_head='$hid'");
        $this->m->query($sql);
        $_SESSION['msg'] = "Party Updated Successfully.";
        $this->redirect("index.php?module=party&func=listing");
    }
    function edit() {
        $hid = $data['id_head'] = $_SESSION['id_user'];
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "0";
        $res = $this->m->query("SELECT * FROM `{$this->prefix}partner_group` WHERE status=0  ORDER BY name");
        $this->sm->assign("group", $this->m->getall($res, 2, "name", "id_group"));
        $sql = $this->create_select("{$this->prefix}partner_party", "id_party='{$id}' AND id_head='$hid'");
        $data = $this->m->fetch_assoc($sql);
        $this->sm->assign("data", $data);
    }
    function delete() {
        $hid = $data['id_head'] = $_SESSION['id_user'];
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "0";
        $this->saveactivity("Delete Party for id : $id.");
        $data['status'] = 1;
        $sql = $this->create_update("{$this->prefix}partner_party", $data, "id_party='{$id}' AND id_head='$hid'");
        $this->m->query($sql);
        $_SESSION['msg'] = "Party Deleted Successfully.";
        $this->redirect("index.php?module=party&func=listing");
    }
    function listing() {
        $this->saveactivity("Party Listing.");
        $id = $_SESSION['id_user'];
        $sql = "SELECT * FROM {$this->prefix}partner_party WHERE id_head='$id' AND status=0 ORDER BY name";
        $list = $this->m->getall($this->m->query($sql));
        $this->sm->assign("list", $list);
    }
    function profile() {
        $this->saveactivity("Profile.");
        $id = $_SESSION['id_user'];
        $sql = "SELECT * FROM {$this->prefix}head WHERE id_head='$id'";
        $list = $this->m->getall($this->m->query($sql));
        $this->sm->assign("list", $list);
    }
    function activity() {
        $id = $_SESSION['id_user'];
        $sql = "SELECT * FROM {$this->prefix}partner_activity WHERE id_user='$id' ORDER BY date DESC";
        $list = $this->m->getall($this->m->query($sql));
        $this->sm->assign("list", $list);
    }
}
?>