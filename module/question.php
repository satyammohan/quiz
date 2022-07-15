<?php
require 'xls/reader.php';
class question extends common {
    function __construct() {
        $this->checklogin();
        $this->table_prefix();
        parent:: __construct();
    }
    function category() {
        $lang = $_REQUEST['id_language'];
        $sql = "SELECT * FROM {$this->prefix}category WHERE id_language='$lang' ORDER BY name";
        $category = $this->m->getall($this->m->query($sql), 2, "regional_name", "id_category");
        ob_clean();
        echo json_encode($category);
        exit;
    }
    function subcategory() {
        $cat = $_REQUEST['id_category'];
        $sql = "SELECT * FROM {$this->prefix}subcategory WHERE id_category='$cat' ORDER BY name";
        $scategory = $this->m->getall($this->m->query($sql), 2, "name", "id_subcategory");
        ob_clean();
        echo json_encode($scategory);
        exit;
    }
    function listing() {
        $this->get_permission("question", "REPORT");
        $wcond = " WHERE 1 ";
        if (isset($_REQUEST['id_category'])) {
            $id = $_REQUEST['id_category'];
            $wcond .= " AND id_category='{$id}'";
        }
        if (isset($_REQUEST['id_language'])) {
            $id = $_REQUEST['id_language'];
            $wcond .= " AND id_language='{$id}'";
        }
        if (isset($_REQUEST['id_subcategory'])) {
            $id = $_REQUEST['id_subcategory'];
            $wcond .= " AND id_subcategory='{$id}'";
        }
        if (isset($_REQUEST['question'])) {
            $id = $_REQUEST['question'];
            $wcond .= " AND question LIKE '%{$id}%'";
        }
        $sql = "SELECT * FROM {$this->prefix}question $wcond ORDER BY id_question DESC LIMIT 500";
        $data = $this->m->getall($this->m->query($sql));
        $this->sm->assign("question", $data);
        $sql = "SELECT * FROM {$this->prefix}language WHERE flag=0 ORDER BY english_name";
        $l = $this->m->getall($this->m->query($sql), 2, "english_name", "id_language");
        $this->sm->assign("language", $l);
    }
    function edit() {
        $id = isset($_REQUEST['id_question']) ? $_REQUEST['id_question'] : "0";
        $this->get_permission("question", ($id ? "INSERT" : "UPDATE"));
        $sql = $this->create_select($this->prefix . "question", "id_question='{$id}'");
        $data = $this->m->fetch_assoc($sql);
        $this->sm->assign("data", $data);

        $sql = "SELECT * FROM {$this->prefix}language WHERE flag=0 ORDER BY english_name";
        $l = $this->m->getall($this->m->query($sql), 2, "english_name", "id_language");
        $this->sm->assign("language", $l);
    }
    function insert() {
        $this->get_permission("question", "INSERT");
        $data = $_REQUEST['comp'];
        // $data['flag'] = 0;
        // $data['id_user'] = $_SESSION['id_user'];
        // $data['create_date'] = date("Y-m-d h:i:s");
        // $data['ip'] = $_SERVER['REMOTE_ADDR'];
        $sql = $this->create_insert($this->prefix . "question", $data);
        $res = $this->m->query($sql);
        $_SESSION['msg'] = "Record Successfully Inserted";
        $this->redirect("index.php?module=question&func=listing");
    }
    function update() {
        $this->get_permission("question", "UPDATE");
        $data =  $_REQUEST['comp'];
        $sql = $this->create_update($this->prefix . "question",$data, "id_question='{$_REQUEST['id_question']}'");
        $res = $this->m->query($sql);
        $_SESSION['msg'] = "Record Successfully Updated";
        $this->redirect("index.php?module=question&func=listing");
    }
    function delete() {
        $this->get_permission("question", "DELETE");
        $id = $_REQUEST['id_question'];
        $res = $this->m->query($this->create_delete($this->prefix . "question", "id_question='$id'"));
        $_SESSION['msg'] = "Question Successfully Deleted";
        $this->redirect("index.php?module=question&func=listing");
    }
    function upload() {
        $this->get_permission("question", "REPORT");
        $sql = "SELECT * FROM {$this->prefix}language WHERE flag=0 ORDER BY english_name";
        $l = $this->m->getall($this->m->query($sql), 2, "english_name", "id_language");
        $this->sm->assign("language", $l);
    }
    function uploadsave() {
        $data = $_REQUEST['comp'];
        if ($_FILES['filename']) {
            $rawfile = getcwd() . "/upload/".date("Y-m-d h:i:s").".xls";
            //$rawfile = getcwd() . "/upload/sample.xls";
            if (move_uploaded_file($_FILES['filename']['tmp_name'], $rawfile)) {
            //if (1) {
                $cols="id_language, id_category, id_subcategory, question, option_1, option_2, option_3, option_4, answer";
                $date = date("Y-m-d H:i:s");
                $vals = $data['id_language'].", ".$data['id_category'].", ".$data['id_subcategory'];

                $excel = new PhpExcelReader;
                $excel->read($rawfile);
                $header = $excel->sheets[0]['cells'][1];
                $cnt = count($excel->sheets[0]['cells']);
                for($i=2; $i<=$cnt; ++$i) {
                    $row = $excel->sheets[0]['cells'][$i];
                    $q = $row[1]; $o1 = $row[2]; $o2 = $row[3]; $o3 = $row[4]; $o4 = $row[5]; $a = $row[6];
                    $sql = "INSERT INTO {$this->prefix}question ($cols) VALUES ($vals, '$q', '$o1', '$o2', '$o3', '$o4', '$a')";
                    $this->m->query($sql);
                }
                $res = "Report : Total records : $cnt, total updated is : ".($cnt-1);
            } else {
                $res = "File not uploaded.";
            }
        }
        $_SESSION['msg'] = $res;
        $this->redirect("index.php?module=question&func=listing");
    }
}
?>