<?php
class common {

    function __construct() {
        global $smarty;
        $this->sm = $smarty;
        $n = func_num_args();
        for ($i = 0; $i < $n; $i += 2) {
            $this->{func_get_arg($i)} = func_get_arg($i + 1);
        }
        $this->m = new database();
        if (file_exists("config/site.ini")) {
            $this->ini = @parse_ini_file("config/site.ini", true);
        } else {
            $this->ini = @parse_ini_file("../config/site.ini", true);
        }
        $this->ini = parse_ini_file("config/site.ini", true);
        $smarty->assign("ini", $this->ini);
        //echo "<b>common default</b></br>";
        $module = isset($_REQUEST['module']) ? $_REQUEST['module'] : "";
        $func = isset($_REQUEST['func']) ? $_REQUEST['func'] : "";
        $tpl = "{$module}/{$func}.tpl.html";
        if (!$smarty->templateExists($tpl)) {
            $tpl = "default.tpl.html";
        }
        $smarty->assign("page", $tpl);
    }

    function _default() {
        
    }

    function table_prefix() {
        if (isset($_SESSION['prefix'])) {
            $this->prefix = $_SESSION['prefix'] . "__";
        }
        $this->prefix = "quizstation__";
        return;
    }

    function create_select($tbl, $cond) {
        $sql = "SELECT * FROM $tbl";
        if ($cond) {
            $sql .= " WHERE $cond ";
        }
        return $sql;
    }

    function create_delete($tbl, $cond) {
        $sql = "DELETE FROM `$tbl`";
        if ($cond) {
            $sql .= " WHERE $cond ";
        }
        return $sql;
    }

    function create_insert($tbl, $arr) {
        $key = array_keys($arr);
        $val = array_values($arr);
        $sql = "INSERT INTO $tbl (`";
        $sql .= implode("`, `", $key);
        $sql .= "`) VALUES ('";
        $sql .= implode("', '", $val);
        $sql .= "')";
        return $sql;
    }

    function create_update($tbl, $arr, $cond) {
        $sql = "UPDATE `$tbl` SET ";
        $fld = array();
        foreach ($arr as $k => $v) {
            $fld[] = "`$k` = '$v'";
        }
        $sql .= implode(", ", $fld);
        $sql .= " WHERE " . $cond;
        return $sql;
    }

    function redirect($url) {
        header("location:$url");
        exit;
    }

    function checklogin() {
        if (isset($_SESSION['id_user'])) {
            return;
        }
        $_SESSION['msg'] = "Please Login to access this Page.";
        $_SESSION['redirect_page'] = $_SERVER[QUERY_STRING];
        $this->redirect("index.php");
    }

    function format_date($date) {
        if (empty($date))
            return "";
        list($d, $m, $y) = preg_split('/[\/.-]/', $date);
        $new_date = "$y-$m-$d";
        return $new_date;
    }

    function format_ymd($date) {
        if (empty($date))
            return "";
        list($y, $m, $d) = preg_split('/[\/.-]/', $date);
        $new_date = "$d-$m-$y";
        return $new_date;
    }

    function pr($data) {
        print "<pre>";
        print_r($data);
        print "</pre>";
    }

    function update_flag() {
        if (($_REQUEST['table'] == 'info') || ($_REQUEST['table'] == 'user') || ($_REQUEST['table'] == 'template')) {
            $tbl = $_REQUEST['table'];
        } else {
            $tbl = $this->prefix . $_REQUEST['table'];
        }
        $sql = "UPDATE `{$tbl}` SET flag='{$_REQUEST['row_status']}' WHERE `id_{$_REQUEST['table']}`='{$_REQUEST['id']}'";
        $this->m->query($sql);
        if ($_REQUEST['list_status'] != 2) {
            $status = $_REQUEST['list_status'];
        } else {
            $status = 2;
        }
        if (isset($_REQUEST['ce'])) {
            $this->redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->redirect("index.php?module={$_REQUEST['table']}&func=listing&flag=$status");
        }
    }

    function convert_number($number) {
        $words = array(
            '0' => '', '1' => 'one', '2' => 'two', '3' => 'three', '4' => 'four', '5' => 'five',
            '6' => 'six', '7' => 'seven', '8' => 'eight', '9' => 'nine', '10' => 'ten',
            '11' => 'eleven', '12' => 'twelve', '13' => 'thirteen', '14' => 'fouteen', '15' => 'fifteen',
            '16' => 'sixteen', '17' => 'seventeen', '18' => 'eighteen', '19' => 'nineteen', '20' => 'twenty',
            '30' => 'thirty', '40' => 'fourty', '50' => 'fifty', '60' => 'sixty', '70' => 'seventy',
            '80' => 'eighty', '90' => 'ninty');

        //First find the length of the number
        //divide number from decimal point
        $originalnum = explode('.', $number);
        $number_length = strlen($originalnum[0]);
        $originalnum[1] = isset($originalnum[1]) ? $originalnum[1] : 0;
        $paisa_length = strlen($originalnum[1]);
        //Initialize an empty array
        $number_array = array(0, 0, 0, 0, 0, 0, 0, 0, 0);
        $paisa_array = array(0, 0);
        $received_number_array = array();
        $paisa_number_array = array();

        //Store all received numbers into an array
        for ($i = 0; $i < $number_length; $i++) {
            $received_number_array[$i] = substr($originalnum[0], $i, 1);
        }

        for ($i = 0; $i < $paisa_length; $i++) {
            $paisa_number_array[$i] = substr($originalnum[1], $i, 1);
        }

        //Populate the empty array with the numbers received - most critical operation
        for ($i = 9 - $number_length, $j = 0; $i < 9; $i++, $j++) {
            $number_array[$i] = $received_number_array[$j];
        }

        for ($i = 2 - $paisa_length, $j = 0; $i < 2; $i++, $j++) {
            $paisa_array[$i] = $paisa_number_array[$j];
        }
        $number_to_words_string = "";
        $paisa_to_string = "";
        //Finding out whether it is teen ? and then multiplying by 10, example 17 is seventeen, so if 1 is preceeded with 7 multiply 1 by 10 and add 7 to it.
        for ($i = 0, $j = 1; $i < 9; $i++, $j++) {
            if ($i == 0 || $i == 2 || $i == 4 || $i == 7) {
                if ($number_array[$i] == "1") {
                    $number_array[$j] = 10 + $number_array[$j];
                    $number_array[$i] = 0;
                }
            }
        }

        for ($i = 0, $j = 1; $i < 2; $i++, $j++) {
            if ($i == 0 || $i == 2) {
                if ($paisa_array[$i] == "1") {
                    $paisa_array[$j] = 10 + $paisa_array[$j];
                    $paisa_array[$i] = 0;
                }
            }
        }

        $value = "";
        for ($i = 0; $i < 9; $i++) {
            if ($i == 0 || $i == 2 || $i == 4 || $i == 7) {
                $value = $number_array[$i] * 10;
            } else {
                $value = $number_array[$i];
            }
            if ($value != 0) {
                $number_to_words_string.= $words["$value"] . " ";
            }
            if ($i == 1 && $value != 0) {
                $number_to_words_string.= "Crores ";
            }
            if ($i == 3 && $value != 0) {
                $number_to_words_string.= "Lakhs ";
            }
            if ($i == 5 && $value != 0) {
                $number_to_words_string.= "Thousand ";
            }
            if ($i == 6 && $value != 0) {
                $number_to_words_string.= "Hundred ";
            }
        }
        if ($number_length > 9) {
            $number_to_words_string = "Sorry This does not support more than 99 Crores";
        }
        $value1 = "";
        for ($i = 0; $i < 2; $i++) {
            if ($i == 0 || $i == 2) {
                $value1 = $paisa_array[$i] * 10;
            } else {
                $value1 = $paisa_array[$i];
            }
            if ($value1 != 0) {
                $paisa_to_string.= $words["$value1"] . " ";
            }
        }
        if ($paisa_length > 2) {
            $paisa_to_string = "Sorry This does not support more than 99 Paisa";
        }
        if ($paisa_to_string == '') {
            $paisa = "Only.";
        } else {
            $paisa = " and " . $paisa_to_string . "paisa  Only.";
        }
        return ucwords(strtolower("" . $number_to_words_string) . $paisa);
    }

    function set_permission() {
        return;
        if ($_SESSION['is_admin'])
            return;
        $id = $_SESSION["id_user"];
        $sql = "SELECT a.*, mp.permission FROM (SELECT u.id_user, u.name AS username, m.id_module, m.name AS modulename, 
            p.id_permission, p.name AS permissionname FROM user u, module m, permission p WHERE u.id_user={$id}) AS a 
            LEFT JOIN module_map mp ON a.id_user=mp.id_user AND a.id_module=mp.id_module AND a.id_permission=mp.id_permission
            ORDER BY a.username, a.modulename";
        $data = array();
        $rs = $this->m->query($sql);
        while ($row = $this->m->movenexta($rs)) {
            $module = strtoupper($row['modulename']);
            $permissionname = strtoupper($row['permissionname']);
            $permission = $row['permission'];
            $data[$module][$permissionname] = $permission;
        }
        $_SESSION['permission'] = $data;
    }

    function get_permission($module, $per) {
        return;
        if ($_SESSION['is_admin'])
            return;
        $module = strtoupper($module);
        $per = strtoupper($per);
        $v = $_SESSION['permission'][$module][$per];
        if ($v != 1) {
            $_SESSION['msg'] = "*****Access Denied for Module $module and operation $per.*****";
            $this->redirect('index.php');
        }
    }

    function addfield($fld, $tbl, $qstring) {
        $sql = "SHOW COLUMNS FROM {$tbl} LIKE '{$fld}'";
        $uid = $this->m->num_rows($this->m->query($sql)) == 0 ? 0 : 1;
        if ($uid == 0) {
            $sql = "ALTER TABLE `{$tbl}` {$qstring}";
            $this->m->query($sql);
        }
    }

    function startdate() {
        if (date() >= $_SESSION['sdate'] AND date() <= $_SESSION['edate']) {
            $dt = $this->format_date(isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : date("01/m/Y"));
        } else {
            $dt = $this->format_date(isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : date("01/m/Y"));
        }
        return $dt;
    }

    function getcomp() {
        $filt = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : "";
        $id = isset($_REQUEST['id']) && $_REQUEST['id'] ? "AND p.id_company='{$_REQUEST['id']}'" : "";
        $sql = "SELECT p.name as `value`, p.id_product AS col0, p.seller_price AS col1, t.tax_per AS col2, p.id_taxmaster_sale AS col3, p.closing_stock AS col4 
            FROM {$this->prefix}product p, {$this->prefix}taxmaster t WHERE p.name LIKE '%{$filt}%' AND p.id_taxmaster_sale=t.id_taxmaster {$id}  ORDER BY p.name";
        $data = $this->m->sql_getall($sql);
        echo json_encode($data);
        exit;
    }

    function getparty() {
        $filt = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : "";
        $sql = "SELECT concat(h.name, ',', h.address1, ',', h.address2, ',', IFNULL(address3, '')) as `value`, h.id_head AS col0, h.address1 AS col1, h.address2 AS col2, h.id_area AS col3, h.vattype AS col4, h.vatno AS col5, h.id_transport AS col6, h.dealer AS col7 FROM {$this->prefix}head h WHERE h.name LIKE '{$filt}%' AND h.debtor ORDER BY h.name";
        $data = $this->m->sql_getall($sql);
        echo json_encode($data);
        exit;
    }

    function getbatch() {
        $filt = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : "";
        $id = "AND b.id_product='{$_REQUEST['id']}'";
        $dealer = isset($_REQUEST['dealer']) ? $_REQUEST['dealer'] : '0';
        $price = ($dealer == "1") ? ' b.distributor_price ' : ' b.seller_price ';
        $sql = "SELECT b.batch_no as `value`, b.id_batch AS col0, b.expiry_date AS col1, $price AS col2  FROM {$this->prefix}batch b WHERE b.batch_no LIKE '%{$filt}%' {$id} ORDER BY b.batch_no";
        $data = $this->m->sql_getall($sql);
        echo json_encode($data);
        exit;
    }

    function option_val() {
        $opt = "SELECT id_transport AS id,name FROM {$this->prefix}transport ORDER BY name";
        $transport = $this->m->sql_getall($opt, 2, "name", "id");
        $this->sm->assign("transport", $transport);
        $opt2 = "SELECT id_area AS id,name FROM {$this->prefix}area ORDER BY name";
        $area = $this->m->sql_getall($opt2, 2, "name", "id");
        $this->sm->assign("area", $area);
        $opt4 = "SELECT id_company AS id,name FROM {$this->prefix}company ORDER BY name";
        $company = $this->m->sql_getall($opt4, 2, "name", "id");
        $this->sm->assign("company", $company);
        $opt6 = "SELECT id_represent AS id,name FROM {$this->prefix}represent ORDER BY name";
        $salesman = $this->m->sql_getall($opt6, 2, "name", "id");
        $this->sm->assign("salesman", $salesman);
    }
    
    function getstartdate() {
        $rd = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : date("01/m/Y");
        if (!($rd>=$_SESSION['start_date'] AND $rd>=$_SESSION['end_date']) ) {
  //          $rd = date("Y/m/01", strtotime($_SESSION['end_date']));
        }
//        $_REQUEST['start_date'] = $rd = $this->format_date($rd);
        $rd = $this->format_date($rd);
        return $rd;
    }
    
    function getenddate() {
        $rd = isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : date("d/m/Y");
        if (!($rd>=$_SESSION['start_date'] AND $rd>=$_SESSION['end_date']) ) {
//            $rd = $_SESSION['end_date'];
        }
//        $_REQUEST['end_date'] = $rd = $this->format_date($rd);
        $rd = $this->format_date($rd);
        return $rd;
    }

	function sendsms($numbers, $message) {
		// Account details
		$apiKey = urlencode('2pbVSJ66pQs-ogahSlrFhF4SLOSZJ7ySdVYxulvIFQ');
		
		// Message details
		$sender = urlencode('TXTLCL');
		$message = rawurlencode($message);
	 
		//$numbers = implode(',', $numbers);
	 
		// Prepare data for POST request
		$data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);
	 
		// Send the POST request with cURL
		$ch = curl_init('https://api.textlocal.in/send/');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		
		// Process your response here
		return $response;
	}
    function savelog() {
        if (isset($_SESSION['id_user']) && isset($_SESSION['companyname'])) {
            $acc['id_user'] = $_SESSION['id_user'];
            $acc['company'] = addslashes($_SESSION['companyname']);
            $acc['sdate'] = $_SESSION['sdate'];
            $acc['edate'] = $_SESSION['edate'];
            $acc['module'] = $_REQUEST['module'];
            $acc['func'] = $_REQUEST['func'];
            $acc['cid'] = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
            $acc['ip'] = $_SERVER['REMOTE_ADDR'];
            $acc['date'] = date("Y-m-d H:i");
            $sql = $this->create_insert("accesslog", $acc);
            $this->m->query($sql);
        }
    }
}
function pr($data) {
    print "<pre>";
    print_r($data);
    print "</pre>";
}
?>