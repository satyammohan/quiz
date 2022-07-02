<?php

class info extends common {

    function __construct() {
        $this->checklogin();
        $this->table_prefix();
        parent:: __construct();
    }

    function date_format($date) {
        list($y, $m, $d) = preg_split('/[\/.-]/', $date);
        $new_date = "$d-$m-$y";
        return $new_date;
    }

    function ddslick() {
        $name = $_REQUEST['name'];
        $sql = "SELECT * FROM info WHERE name='$name' AND showtoparty=1 ORDER BY name, start_date DESC";
        $res = $this->m->sql_getall($sql);
        $ddata = array();
        foreach ($res as $v) {
            $sdt = $this->date_format($v['start_date']);
            $edt = $this->date_format($v['end_date']);
            $ddata[] = array("text" => "{$sdt}---{$edt}", "value" => "{$v['id_info']}");
        }
        echo json_encode($ddata);
        exit;
    }

    function prefix() {
        $_SESSION['id_info'] = $_REQUEST['id'];
        $_SESSION['sid'] = $_REQUEST['index'];
        $_SESSION['yselect'] = $_REQUEST['yindex'];
        $_SESSION['cname'] = "<font size='+1'>" . $_REQUEST['cname'] . "</font>";
        $_SESSION['companyname'] = $_REQUEST['cname'];
        $sql = "SELECT * FROM info WHERE id_info='{$_REQUEST['id']}'";
        $res = $this->m->fetch_assoc($sql);
        foreach ($res as $k => $v) {
            $_SESSION[$k] = $v;
        }
        $_SESSION['sdate'] = $res['start_date'];
        $_SESSION['edate'] = $res['end_date'];
        $_SESSION['fyear'] = $this->date_format($res['start_date']) . "........... " . $this->date_format($res['end_date']);
        $this->redirect("index.php");
    }

    function unset_prefix() {
        unset($_SESSION['id_info']);
        unset($_SESSION['cname']);
        unset($_SESSION['companyname']);
        unset($_SESSION['phone']);
        unset($_SESSION['fyear']);
        unset($_SESSION['sdate']);
        unset($_SESSION['edate']);
        unset($_SESSION['gstdate']);
        unset($_SESSION['address']);
        unset($_SESSION['tin']);
        $this->redirect("index.php?module=user&func=_default");
    }

    function insert() {
        $data = $_REQUEST['info'];
        $data['start_date'] = $this->format_date($data['start_date']);
        $data['end_date'] = $this->format_date($data['end_date']);
        $data['gstdate'] = $this->format_date($data['gstdate']);
        $sql = $this->create_insert("`info`", $data);
        $res = $this->m->query($sql);
        $_SESSION['msg'] = "Record Successfully Inserted";
        $this->redirect("index.php?module=info&func=listing");
    }

    function edit() {
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "0";
        $sql = $this->create_select("`info`", "id_info='{$id}'");
        $data = $this->m->fetch_assoc($sql);
        $this->sm->assign("data", $data);
        $this->sm->assign("page", "info/add.tpl.html");
    }

    function update() {
        $data = $_REQUEST['info'];
        $data['start_date'] = $this->format_date($data['start_date']);
        $data['end_date'] = $this->format_date($data['end_date']);
        $data['gstdate'] = $this->format_date($data['gstdate']);
	$this->addfield('tradename', 'info', 'ADD `tradename` varchar(100)');
        $this->addfield('pincode', 'info', 'ADD `pincode` varchar(10)');
        $this->addfield('statecode', 'info', 'ADD `statecode` varchar(2)');
        $res = $this->m->query($this->create_update("info", $data, "id_info='{$_REQUEST['id']}'"));
        $_SESSION['msg'] = "Record Successfully Updated";
        $this->redirect("index.php?module=info&func=listing");
    }

    function delete() {
        $_SESSION['msg'] = "This feature is disabled.";
        $this->redirect("index.php?module=info&func=listing");
        exit;
        $sql = "SELECT * FROM infO WHERE id_info={$_REQUEST['id']}";
        $data = $this->m->fetch_assoc($sql);
        $tbl = strtolower($data['prefix']) . '__';
        $sql = "SELECT CONCAT( 'DROP TABLE  IF EXISTS ', GROUP_CONCAT( table_name ) , ';' ) AS statement ";
        $sql.=" FROM information_schema.tables WHERE table_name LIKE '$tbl%'";
        $res1 = $this->m->sql_getall($sql);
        $drop = $res1[0]['statement'];
        if ($drop != '') {
            $this->m->query($drop);
            $this->m->query("DROP VIEW {$tbl}ledger;DROP VIEW {$tbl}product_ledger;DROP VIEW {$tbl}product_ledger_summary;DROP VIEW {$tbl}tb;");
        }
        $res = $this->m->query($this->create_delete("info", "id_info='{$_REQUEST['id']}'"));
        $_SESSION['msg'] = "Record Successfully Deleted";
        $this->redirect("index.php?module=info&func=listing");
    }

    function listing() {
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] != 1) {
            $_SESSION['msg'] = "Your are not Authorised to set Permissions.";
            $this->redirect("index.php");
        }
        if (isset($_REQUEST['status'])) {
            ($_REQUEST['status'] == 2) ? $wcond = "" : $wcond = " WHERE status = " . $_REQUEST['status'];
        } else {
            $_REQUEST['status'] = 0;
            $wcond = "WHERE status ={$_REQUEST['status']}";
        }
        $info = $this->m->getall($this->m->query("SELECT * FROM `info` $wcond ORDER BY name, `start_date`"));
        $this->sm->assign("info", $info);
        $this->sm->assign("page", "info/listing.tpl.html");
    }

    function newyear() {
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "0";
        $sql = $this->create_select("`info`", "id_info='{$id}'");
        $data = $this->m->fetch_assoc($sql);
        $this->sm->assign("data", $data);
        $this->sm->assign("page", "info/newyear.tpl.html");
    }

    function insertyr() {
        $data = $_REQUEST['info'];
        $sql = $this->m->query($this->create_select("`info`", "prefix='" . $data['newprefix']) . "'");
        $res = $this->m->getall($sql);
        if (count($res)) {
            $_SESSION['msg'] = "<b>{$data['prefix']}</b>. already created.";
            $this->redirect("index.php");
        }
        $newpf = $data['prefix'];
        $tables = array();
        $result = mysql_query('SHOW FULL TABLES WHERE Table_Type = "BASE TABLE"');
        while ($row = mysql_fetch_row($result)) {
            if (strpos($row[0], $newpf) === 0)
                $tables[] = $row[0];
        }
        $master = array("area", "batch", "bonus", "book", "category", "company", "discount", "employee", "form", "group", "head", "param", "product", "represent", "salary", "staff", "series", "taxmaster", "transport", "zone");
        foreach ($tables as $v) {
            $m = explode("__", $v);
            $nv = $data['newprefix'] . "__" . $m[1];
            if (in_array($m[1], $master)) {
                $sql = "CREATE TABLE $nv (SELECT * FROM $v)";
            } else {
                $sql = "CREATE TABLE $nv (SELECT * FROM $v WHERE id_{$m[1]}=0)";
            }
            mysql_query($sql);
        }
        $data['id_info'] = 0;
        $data['start_date'] = $this->format_date($data['newstart_date']);
        $data['end_date'] = $this->format_date($data['newend_date']);
        $data['prefix'] = $data['newprefix'];
        unset($data['newprefix']);
        unset($data['newstart_date']);
        unset($data['newend_date']);
        unset($data['opt']);
        $this->m->query($this->create_insert("`info`", $data));
        $_SESSION['msg'] = "Record Successfully Inserted";
        $this->redirect("index.php?module=info&func=listing");
    }

    function stockop() {
        $thisprefix = $_SESSION['prefix'];
        $thisname = trim($_SESSION['name']);
        $lastdate = date('Y-m-d', strtotime($_SESSION['start_date']) - 1);
        $sql = 'SELECT prefix FROM info WHERE name LIKE "' . $thisname . '%" AND end_date="' . $lastdate . '"';
        $data = $this->m->fetch_assoc($sql);
        $lastprefix = $data['prefix'];
        if (!$lastprefix) {
            $_SESSION['msg'] = "Last year data not available to import Stock Opening Balance.";
            $this->redirect("index.php");
        } else {
            $this->addfield('prev_opening_stock', $thisprefix . '__product', 'ADD prev_opening_stock DECIMAL(16,4) ');
            $sql = "SELECT COUNT(*) AS cnt FROM {$thisprefix}__product WHERE prev_opening_stock!=0";
            $data = $this->m->fetch_assoc($sql);
            if ($data['cnt'] == 0) {
                $sql = "UPDATE {$thisprefix}__product SET prev_opening_stock=opening_stock";
                $this->m->query($sql);
            }
            $sql = "UPDATE {$thisprefix}__product SET opening_stock=0";
            $this->m->query($sql);


            $sql = "SELECT count(*)  AS cnt FROM  {$thisprefix}__batch";
            $data = $this->m->fetch_assoc($sql);
            if ($data['cnt'] == 0) {
                $sql = "UPDATE {$thisprefix}__product p, {$lastprefix}__product_ledger_summary s SET p.opening_stock=s.balance WHERE p.id_product=s.id_product";
                $this->m->query($sql);
                $_SESSION['msg'] = "Import Stock Opening Balance of Products from last year Successful.";
            } else {
                $this->addfield('prev_opening_stock', $thisprefix . '__batch', 'ADD prev_opening_stock DECIMAL(16,4) ');
                $sql = "UPDATE {$thisprefix}__batch SET prev_opening_stock=opening_stock";
                $this->m->query($sql);
                $sql = "UPDATE {$thisprefix}__batch SET opening_stock=0, opening_stock_free=0";
                $this->m->query($sql);
                $sql = "UPDATE {$thisprefix}__batch a, (SELECT id_product, id_batch, SUM(IF(qty IS NULL, 0, qty)+IF(free IS NULL, 0, free)) AS myob 
                        FROM {$lastprefix}__product_ledger GROUP BY 1,2) b SET a.opening_stock_free=b.myob WHERE a.id_product=b.id_product AND a.id_batch=b.id_batch;";
                $this->m->query($sql);
                $_SESSION['msg'] = "Import Stock Opening Balance of Productswise Batches from last year Successful.";
            }
            $this->redirect("index.php");
        }
        exit;
    }

    function ledgerop() {
        $thisprefix = $_SESSION['prefix'];
        $thisname = trim($_SESSION['name']);
        $lastdate = date('Y-m-d', strtotime($_SESSION['start_date']) - 1);
        $sql = 'SELECT prefix FROM info WHERE name LIKE "' . $thisname . '%" AND end_date="' . $lastdate . '"';
        $data = $this->m->fetch_assoc($sql);
        $lastprefix = $data['prefix'];
        if (!$lastprefix) {
            $_SESSION['msg'] = "Last year data not available to import Ledger Opening Balance.";
            $this->redirect("index.php");
        } else {
            $this->addfield('prev_opening_balance', $thisprefix . '__head', 'ADD prev_opening_balance DECIMAL(16,2) ');
            $sql = "SELECT COUNT(*) AS cnt FROM {$thisprefix}__head WHERE prev_opening_balance!=0";
            $data = $this->m->fetch_assoc($sql);
            if ($data['cnt'] == 0) {
                $sql = "UPDATE {$thisprefix}__head SET prev_opening_balance=opening_balance";
                $this->m->query($sql);
            }
            $sql = "UPDATE {$thisprefix}__head SET opening_balance=0, otype=0";
            $this->m->query($sql);

            $sql = "UPDATE {$thisprefix}__head h, (SELECT id_head, SUM(debit-credit) as total FROM {$lastprefix}__tb GROUP BY id_head) t SET h.opening_balance=t.total WHERE h.id_head=t.id_head";
            $this->m->query($sql);
            $sql = "UPDATE {$thisprefix}__head h SET otype=1 WHERE h.opening_balance<0";
            $this->m->query($sql);
            $sql = "UPDATE {$thisprefix}__head h SET h.opening_balance=-1*h.opening_balance WHERE h.opening_balance<0";
            $this->m->query($sql);

            $_SESSION['msg'] = "Import Ledger Opening Balance from last year Successful.";
            $this->redirect("index.php");
        }
        exit;
    }

    function billop() {
        $thisprefix = $_SESSION['prefix'];
        $thisname = trim($_SESSION['name']);
        $lastdate = date('Y-m-d', strtotime($_SESSION['start_date']) - 1);
        $sql = 'SELECT prefix FROM info WHERE name LIKE "' . $thisname . '%" AND end_date="' . $lastdate . '"';
        $data = $this->m->fetch_assoc($sql);
        $lastprefix = $data['prefix'];
        if (!$lastprefix or $lastprefix==$thisprefix) {
            $_SESSION['msg'] = "Last year data not available to import Ledger Opening Balance.";
            $this->redirect("index.php");
        } else {
            $sql = "DELETE FROM {$this->prefix}sale WHERE date <= '$lastdate'";
            $this->m->query($sql);

            $sql = "SELECT h.id_head, p.total FROM `{$lastprefix}__head` h, (SELECT id_head, SUM(t.total1) AS total FROM (
              SELECT dhead AS id_head, SUM(-total) AS total1 FROM `{$lastprefix}__ledger` WHERE !(type='S' OR type='H') GROUP BY 1
              UNION ALL 
              SELECT chead AS id_head, SUM(total) AS total1 FROM `{$lastprefix}__ledger` WHERE !(type='S' OR type='H') GROUP BY 1) t
              GROUP BY 1) p WHERE h.id_head=p.id_head AND h.debtor AND p.total<>0
              GROUP BY h.id_head  ORDER BY h.id_head ";
            $res = $this->m->sql_getall($sql, 2, "total", "id_head");
            $sql = "SELECT s.id_company, s.id_represent, s.id_area, s.invno, s.date, s.id_head, s.total, s.total AS balance FROM `{$lastprefix}__sale` s WHERE s.cash=1";
            $rs = $this->m->query($sql);
            while ($row = mysql_fetch_assoc($rs)) {
                $hid = $row['id_head'];
                $res[$hid] = isset($res[$hid]) ? $res[$hid] : 0;
                $row['balance'] = max(0, $row['balance'] - $res[$hid]);
                $res[$hid] = max(0, $res[$hid] - $row['total']);
                if ($row['balance'] != 0)
                    $result[] = $row;
            }
            foreach ($result as $v) {
                $sql = "INSERT INTO {$this->prefix}sale (id_company, id_represent, id_area, id_head, invno, date, total, cash) VALUES 
                    ('" . $v['id_company'] . "', '" . $v['id_represent'] . "', '" . $v['id_area'] . "', '" . $v['id_head'] . "', '" . $v['invno'] . "', '" . $v['date'] . "', '" . $v['balance'] . "', 1)";
                $this->m->query($sql);
            }
            $_SESSION['msg'] = "Import of Billwise Opening Balance of Sundry Debtors from last year Successful.";
            $this->redirect("index.php");
        }
    }

}

?>
