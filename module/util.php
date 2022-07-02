<?php

class util extends common {

    function __construct() {
        //$this->checklogin();
        $this->table_prefix();
        parent:: __construct();
    }

    function tcs() {
        $this->addfield('tcsper', $this->prefix . 'sale', 'ADD `tcsper` decimal(9,3)');
        $this->addfield('tcsamt', $this->prefix . 'sale', 'ADD `tcsamt` decimal(15,2)');
        $this->addfield('tcsper', $this->prefix . 'purchase', 'ADD `tcsper` decimal(9,3)');
        $this->addfield('tcsamt', $this->prefix . 'purchase', 'ADD `tcsamt` decimal(15,2)');
        $this->addfield('tanno', $this->prefix . 'head', 'ADD `tanno` varchar(20)');
        $this->addfield('tcsper', $this->prefix . 'head', 'ADD `tcsper` decimal(9,3)');
        $this->addfield('tcs', $this->prefix . 'head', 'ADD `tcs` int(1)');
	$this->changefield('tcsper', $this->prefix . 'sale', 'CHANGE `tcsper` `tcsper` decimal(9,3) ');
	$this->changefield('tcsper', $this->prefix . 'purchase', 'CHANGE `tcsper` `tcsper` decimal(9,3) ');
	$this->changefield('tcsper', $this->prefix . 'head', 'CHANGE `tcsper` `tcsper` decimal(9,3) ');
        $_SESSION['msg'] = "TCS Upgraded Successful.";
        $this->redirect("index.php");
    }

    function upgrade() {
	$files = Array("adjustdetail", "creditnotedetail", "preturndetail", "sreturndetail", "purchasedetail", "saledetail", "salesorderdetail");
	foreach ($files as $key => $file) {
		$sql = "ALTER TABLE `{$this->prefix}$file` ADD INDEX ( `batch_no` )";
		$this->m->query($sql);
		$sql = "UPDATE `{$this->prefix}$file` t,`{$this->prefix}batch` m SET t.id_batch=m.id_batch
				WHERE m.batch_no=t.batch_no AND m.id_product=t.id_product AND t.id_batch!=0;";
		//$sql = "UPDATE `{$this->prefix}$file` t,`{$this->prefix}batch` m SET t.id_batch=m.id_batch WHERE m.batch_no=t.batch_no AND m.id_product=t.id_product";
		$this->m->query($sql);
	}
        $sql = "SHOW COLUMNS FROM `user` LIKE 'uid'";
        $uid = $this->m->num_rows($this->m->query($sql)) == 0 ? 0 : 1;
        if ($uid == 1) {
            $sql = "ALTER TABLE `user` CHANGE uid id_user INT (11), ADD `random` VARCHAR (32),
                        ADD `is_admin` TINYINT (1), ADD `id_create` INT (11), ADD `create_date` TIMESTAMP, ADD `id_modify` INT (11),
                        ADD `modify_date` TIMESTAMP, ADD `ip` VARCHAR (30)";
            $this->m->query($sql);
        }
        $data = $this->m->fetch_assoc("SELECT COUNT(*) AS cnt FROM `configuration`");
        if (!$data['cnt']) {
            $sql = "DROP TABLE IF EXISTS `configuration`";
            $this->m->query($sql);
            $sql = "CREATE TABLE `configuration` (`id_config` int(11) NOT NULL AUTO_INCREMENT,`name` varchar(30) NOT NULL,`description` varchar(100) NOT NULL,`value` text NOT NULL,PRIMARY KEY (`id_config`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
            $this->m->query($sql);
            $sql = "INSERT INTO `configuration` (`id_config`, `name`, `description`, `value`) VALUES (1, 'salestock', 'Show only Products having Stock in Sales Billing', '1'),(2, 'purchasereturnstock', 'Show only Products having Stock in Purchase Return', '1'),(3, 'showstockinsale', 'Show Stock available in Sales Billing', '1'), (4, 'salebillfooter', 'Show Footer content in Sales Billing Print', '<b>TERMS & CONDITIONS ::</b> 1. Goods once sold will not be taken back.<br>2. Subject to State Jurisdiction.<br>3. We hereby certify that the good/goods mentioned in this Invoice is/are to be of the nature & quality it purport/purports to be.<br>4. This registration certificate is valid on the date of issue of this Invoice.');";
            $this->m->query($sql);
            $fr = "E. & O. E.<br>For M/s. {$_SESSION['companyname']}<br><br>Authorisied Signatory";
            $sql = "INSERT INTO `configuration` (`id_config`, `name`, `description`, `value`) VALUES 
                        (5, 'salebillfooterright', 'Show Footer content in Rightside of Sales Billing Print',  '{$fr}');";
            $this->m->query($sql);
        }
        $data = $this->m->fetch_assoc("SELECT COUNT(*) AS cnt FROM `permission`");
        if (!$data['cnt']) {
            $sql = "DROP TABLE IF EXISTS `permission`";
            $this->m->query($sql);
            $sql = "CREATE TABLE IF NOT EXISTS `permission` (
                    `id_permission` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(50) NOT NULL,
                    `status` tinyint(1) NOT NULL, `ip` varchar(30) NOT NULL,
                    `id_create` int(11) NOT NULL,`create_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `id_modify` int(11) NOT NULL,`modify_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`id_permission`)) ENGINE=InnoDB ;";
            $this->m->query($sql);
            $sql = " INSERT INTO `permission` (`id_permission`, `name`, `status`, `ip`, `id_create`, `create_date`, `id_modify`, `modify_date`) VALUES
                        (1, 'INSERT', 0, '', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
                        (2, 'UPDATE', 0, '', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
                        (3, 'DELETE', 0, '', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
                        (4, 'REPORT', 0, '', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00');";
            $this->m->query($sql);
        }
        $data = $this->m->fetch_assoc("SELECT COUNT(*) AS cnt FROM `module_map`");
        if (!$data['cnt']) {
            $sql = "DROP TABLE IF EXISTS `module_map`";
            $this->m->query($sql);
            $sql = "CREATE TABLE IF NOT EXISTS `module_map` (
                        `id_module_map` int(11) NOT NULL AUTO_INCREMENT, `id_user` int(11) NOT NULL,
                        `id_module` int(11) NOT NULL, `id_permission` int(11) NOT NULL, `permission` tinyint(1) NOT NULL,
                        `status` tinyint(1) NOT NULL, `ip` varchar(30) NOT NULL,
                        `id_create` int(11) NOT NULL, `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        `id_modify` int(11) NOT NULL, `modify_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                        PRIMARY KEY (`id_module_map`)) ENGINE=MyISAM;";
            $this->m->query($sql);
        }
        $sql = "DROP TABLE IF EXISTS `module`";
        $this->m->query($sql);
        $sql = "CREATE TABLE IF NOT EXISTS `module` (
                        `id_module` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(50) NOT NULL,
                        `status` tinyint(1) NOT NULL, `ip` varchar(30) NOT NULL,
                        `id_create` int(11) NOT NULL, `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        `id_modify` int(11) NOT NULL, `modify_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                        PRIMARY KEY (`id_module`)) ENGINE=MyISAM;";
        $this->m->query($sql);
        $sql = "INSERT INTO `module` (`id_module`, `name`) VALUES
                        (1, 'accounts'), (2, 'adjust'), (3, 'area'), (4, 'batch'), (5, 'company'), (6, 'discount'), (7, 'form'),
                        (8, 'group'), (9, 'head'), (10, 'preturn'), (11, 'product'), (12, 'purchase'), (13, 'represent'), (14, 'sales'),
                        (15, 'series'), (16, 'sreturn'), (17, 'taxmaster'), (18, 'transport'), (19, 'voucher'), (20, 'zone'),
                        (21, 'debitnote'), (22, 'creditnote'), (23, 'production'), (24, 'salesorder'), (25, 'salary');";
        $this->m->query($sql);


	$sql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}debitnote` (`id_debitnote` int(11) NOT NULL)";
	$this->m->query($sql);


        $sql = "SHOW COLUMNS FROM `{$this->prefix}debitnote` LIKE 'date'";
        $uid = $this->m->num_rows($this->m->query($sql)) == 0 ? 0 : 1;
        if ($uid == 0) {
			$sql = "RENAME TABLE {$this->prefix}debitnote TO {$this->prefix}debitnoteold";
			$data = $this->m->query($sql);
			$sql = "CREATE TABLE `{$this->prefix}debitnote` (`id_debitnote` int(11) NOT NULL AUTO_INCREMENT,
				`price` tinyint(4) NOT NULL, `saletype` tinyint(4) NOT NULL, `no` varchar(20) NOT NULL, `date` date NOT NULL,
				`id_head` int(11) NOT NULL, `id_company` int(11) NOT NULL, `id_area` int(11) NOT NULL, `id_represent` int(11) NOT NULL,
				`ref_no` varchar(20) NOT NULL, `ref_date` date NOT NULL, `invno` varchar(20) NOT NULL, `inv_date` date NOT NULL,
				`totalamt` decimal(16,2) NOT NULL, `discount` decimal(16,2) NOT NULL, `vat` decimal(16,2) NOT NULL, `totalcess` decimal(16,2) NOT NULL,
				`adjust` decimal(16,2) NOT NULL, `total` decimal(16,2) NOT NULL, `memo` text NOT NULL, `ip` varchar(30) NOT NULL,`id_create` int(11) NOT NULL,
				`create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`id_modify` int(11) NOT NULL,
				`modify_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',`cd` decimal(5,2) NOT NULL,`cdamt` decimal(16,2) NOT NULL, 
				PRIMARY KEY (`id_debitnote`)) ENGINE=InnoDB;";
			$data = $this->m->query($sql);

			$sql = "CREATE TABLE `{$this->prefix}debitnotedetail` (`id_debitnotedetail` int(11) NOT NULL AUTO_INCREMENT, `saletype` tinyint(4) NOT NULL,
					`no` varchar(20) NOT NULL, `date` date NOT NULL, `id_product` int(11) NOT NULL, `rate` decimal(15,4) NOT NULL, `case` decimal(12,4) NOT NULL,
					`qty` decimal(12,4) NOT NULL, `free` decimal(12,4) NOT NULL, `discount_type1` tinyint(2) NOT NULL, 
					`discount1` decimal(10,4) NOT NULL, `discount_amount1` decimal(10,2) NOT NULL, `discount_type2` tinyint(2) NOT NULL,
					`discount2` decimal(10,4) NOT NULL, `discount_amount2` decimal(10,2) NOT NULL, `discount_type3` tinyint(2) NOT NULL,
					`discount3` decimal(10,4) NOT NULL, `discount_amount3` decimal(10,2) NOT NULL, `discount_type4` tinyint(2) NOT NULL,
					`discount4` decimal(10,4) NOT NULL, `discount_amount4` decimal(10,2) NOT NULL, `amount` decimal(15,2) NOT NULL,
					`goods_amount` decimal(15,2) NOT NULL, `id_taxmaster` int(11) NOT NULL, `tax_per` decimal(8,2) NOT NULL, `tax_amount` decimal(15,2) NOT NULL,
					`net_amount` decimal(15,2) NOT NULL, `cess` float(10,2) NOT NULL, `cessamt` decimal(15,2) NOT NULL, `id_debitnote` int(11) NOT NULL,
					`id_company` int(11) NOT NULL, `id_head` int(11) NOT NULL, `id_represent` int(11) NOT NULL, `id_area` int(11) NOT NULL,
					`id_batch` int(11) NOT NULL, `batch_no` varchar(15) NOT NULL, `code` varchar(5) NOT NULL, `exp_date` varchar(15) NOT NULL,
					`status` tinyint(1) NOT NULL, `ip` varchar(30) NOT NULL, `id_create` int(11) NOT NULL, `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					`id_modify` int(11) NOT NULL, `modify_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', PRIMARY KEY (`id_debitnotedetail`) ) ENGINE=InnoDB;";
			$data = $this->m->query($sql);
		}

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}creditnote` (
                    `id_creditnote` int(11) NOT NULL AUTO_INCREMENT,
                    `no` varchar(20) NOT NULL,
                    `date` date NOT NULL,
                    `reference` varchar(20) NOT NULL,
                    `description` varchar(30) NOT NULL,
                    `id_head` int(11) NOT NULL,
                    `id_company` int(11) NOT NULL,
                    `id_area` int(11) NOT NULL,
                    `id_represent` int(11) NOT NULL,
                    `totalamt` decimal(16,2) NOT NULL,
                    `discount` decimal(16,2) NOT NULL,
                    `vat` decimal(16,2) NOT NULL,
                    `packing` decimal(16,2) NOT NULL,
                    `add` decimal(16,2) NOT NULL,
                    `less` decimal(16,2) NOT NULL,
                    `round` decimal(16,2) NOT NULL,
                    `total` decimal(16,2) NOT NULL,
                    `memo` text NOT NULL,
                    `ip` VARCHAR( 30 ) NOT NULL,
                    `id_create` INT( 11 ) NOT NULL,
                    `create_date` TIMESTAMP NOT NULL,
                    `id_modify` INT( 11 ) NOT NULL,
                    `modify_date` TIMESTAMP NOT NULL,
                    `party_name` varchar (30) NOT NULL,
                    `party_address` varchar (30) NOT NULL,
                    `party_address1` varchar (30) NOT NULL,
                    `party_vattype` varchar (5) NOT NULL,
                    `party_vatno` varchar (20) NOT NULL,            
                    `company` varchar (2) NOT NULL,
                    `cust` varchar (4) NOT NULL,
                    `salesman` varchar (2) NOT NULL,
                    `area` varchar (2) NOT NULL,
                    PRIMARY KEY (`id_creditnote`) ) ENGINE=MyISAM";
        $this->m->query($sql);
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}creditnotedetail` (
                    `id_creditnotedetail` int(11) NOT NULL AUTO_INCREMENT,
                    `no` varchar(20) NOT NULL,
                    `date` date NOT NULL,
                    `id_product` int(11) NOT NULL,
                    `rate` decimal(15,4) NOT NULL,
                    `qty` decimal(12,4) NOT NULL,
                    `free` decimal(12,4) NOT NULL,
                    `amount` decimal(15,2) NOT NULL,
                    `goods_amount` decimal(15,2) NOT NULL,
                    `id_taxmaster` int(11) NOT NULL,
                    `tax_per` decimal(8,2) NOT NULL,
                    `tax_amount` decimal(15,2) NOT NULL,
                    `net_amount` decimal(15,2) NOT NULL,
                    `id_creditnote` int(11) NOT NULL,
                    `id_company` int(11) NOT NULL,
                    `id_head` int(11) NOT NULL,
                    `id_represent` int(11) NOT NULL,
                    `id_area` int(11) NOT NULL,
                    `id_batch` int(11) NOT NULL,
                    `batch_no` varchar(15) NOT NULL,
                    `code` varchar (5) NOT NULL,
                    `exp_date` varchar(15) NOT NULL,
                    `status` TINYINT( 1 ) NOT NULL,
                    `ip` VARCHAR( 30 ) NOT NULL,
                    `id_create` INT( 11 ) NOT NULL,
                    `create_date` TIMESTAMP NOT NULL,
                    `id_modify` INT( 11 ) NOT NULL,
                    `modify_date` TIMESTAMP NOT NULL,
                    PRIMARY KEY (`id_creditnotedetail`) ) ENGINE=MyISAM";
        $this->m->query($sql);
        $_SESSION['msg'] = "Database Upgraded Successful.";
        //$this->redirect("index.php?module=util&func=check");
    }

    function changefield($fld, $tbl, $qstring) {
        $sql = "SHOW COLUMNS FROM {$tbl} LIKE '{$fld}'";
        $uid = $this->m->num_rows($this->m->query($sql)) == 0 ? 0 : 1;
        if ($uid == 1) {
            $sql = "ALTER TABLE `{$tbl}` {$qstring}";
            $this->m->query($sql);
        }
    }

    function updatepending() {
/*        $sql = "UPDATE {$this->prefix}sale SET pending=total";
        $res = $this->m->query($sql);
        $sql = "SELECT id_sale, SUM(amt) AS total FROM {$this->prefix}voucher_details WHERE id_sale AND id_sale!=0 GROUP BY id_sale";
        $res = $this->m->query($sql);
        while ($row = $this->m->fetch_assoc()) {
            $sql1 = "UPDATE {$this->prefix}sale SET pending=pending-" . $row['total'] . " WHERE id_sale=" . $row['id_sale'];
            $this->m->query($sql1);
        }*/
 
        $sql = "UPDATE {$this->prefix}sale SET pending=total";
        $res = $this->m->query($sql);
        $sql = "SELECT id_sale, SUM(amt) AS total FROM {$this->prefix}voucher_details WHERE id_sale AND id_sale!=0 GROUP BY id_sale";
        $res = $this->m->query($sql);
        while ($row = $this->m->mysql_fetch_assoc($res)) {
            $sql1 = "UPDATE {$this->prefix}sale SET pending=pending-" . $row['total'] . " WHERE id_sale=" . $row['id_sale'];
            $this->m->query($sql1);
        }
    }

    function check() {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}voucher_details` (
                    `id_voucher_details` int(11) NOT NULL AUTO_INCREMENT,
                    `date` date NOT NULL,
                    `no` varchar(11) NOT NULL,
                    `total` decimal(16,2) NOT NULL,
                    `id_head_debit` int(11) NOT NULL, `id_head_credit` int(11) NOT NULL,
                    `id_voucher` int(11) NOT NULL,
                    `id_sale` int(11) NOT NULL,
                    `amt` decimal(16,2) NOT NULL,
                    `billno` varchar(20) NOT NULL,
                    `memo` text NOT NULL,
                    `ip` varchar(30) NOT NULL,
                    `id_create` int(11) NOT NULL,
                    `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    `id_modify` int(11) NOT NULL,
                    `modify_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                     PRIMARY KEY (`id_voucher_details`) ) ENGINE=MyISAM;";
        $this->m->query($sql);
        $this->updatepending();
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}preturn` (
              `id` int(11) NOT NULL AUTO_INCREMENT, `slno` int(11) NOT NULL COMMENT 'Serial No', `billdate` date NOT NULL COMMENT 'Bill Date',
              `billno` varchar(35) NOT NULL COMMENT 'Bill No', `date` date NOT NULL COMMENT 'Purchase Date', `id_head` int(10) NOT NULL COMMENT 'Party Id',
              `id_area` int(10) NOT NULL COMMENT 'Area ', `id_represent` int(10) NOT NULL COMMENT 'Salesman', `id_company` int(10) NOT NULL,
              `party_name` varchar(45) NOT NULL COMMENT 'Party Name', `party_address` tinytext NOT NULL COMMENT 'Party Address',
              `party_address1` tinytext NOT NULL COMMENT 'Party Address1', `party_vattype` varchar(5) NOT NULL COMMENT 'Party vattype',
              `party_vatno` varchar(20) NOT NULL COMMENT 'Party vatno', `discount` decimal(10,4) NOT NULL, `vat` decimal(10,4) NOT NULL,
              `add` decimal(10,4) NOT NULL, `less` decimal(10,4) NOT NULL, `packing` decimal(10,4) NOT NULL, `roundoff` decimal(10,2) NOT NULL,
              `total` decimal(10,4) NOT NULL, `cash` tinyint(2) NOT NULL, `memo` tinytext NOT NULL, `lr_no` varchar(35) NOT NULL,
              `lr_date` date NOT NULL, `trmr_date` date NOT NULL, `id_transport` int(10) NOT NULL, `transport_no` varchar(45) NOT NULL COMMENT 'Transport',
              `gate` varchar(35) NOT NULL, `vehicle_no` varchar(35) NOT NULL, `bales` int(10) NOT NULL, `freight` decimal(10,4) NOT NULL,
              `ent_no` varchar(35) NOT NULL,
              `ent_date` date NOT NULL,
              `ent_amount` decimal(10,4) NOT NULL,
              `frm_type` varchar(20) NOT NULL,
              `id_form` int(10) NOT NULL,
              `frm_date` date NOT NULL,
              `frm_no` varchar(20) NOT NULL,
              `frm_amount` decimal(10,4) NOT NULL,
              `waybill_no` varchar(25) NOT NULL,
              `waybill_date` date NOT NULL,
              `waybill_amount` decimal(10,4) NOT NULL,
              `ip` varchar(35) NOT NULL,
              `printed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              `id_create` int(10) NOT NULL,
              `create_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              `id_modify` int(10) NOT NULL,
              `modify_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
              ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $this->m->query($sql);


        $sql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}preturndetail` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `slno` int(11) NOT NULL,
              `date` date NOT NULL,
              `id_product` int(10) NOT NULL,
              `rate` decimal(15,4) NOT NULL,
              `qty` decimal(12,4) NOT NULL,
              `free` decimal(12,4) NOT NULL,
              `amount` decimal(15,2) NOT NULL,
              `distype1` tinyint(2) NOT NULL,
              `distype2` tinyint(2) NOT NULL,
              `distype3` tinyint(2) NOT NULL,
              `dis1` decimal(10,4) NOT NULL,
              `dis2` decimal(10,2) NOT NULL,
              `dis3` decimal(10,2) NOT NULL,
              `disamt1` decimal(10,2) NOT NULL,
              `disamt2` decimal(10,2) NOT NULL,
              `disamt3` decimal(10,2) NOT NULL,
              `tax` int(10) NOT NULL COMMENT 'Tax',
              `taxamt` decimal(10,2) NOT NULL,
              `taxon` decimal(15,4) NOT NULL,
              `id_preturn` int(10) NOT NULL COMMENT 'Purchase Id',
              `id_head` int(10) NOT NULL,
              `id_batch` int(11) NOT NULL,
              `batch_name` varchar(25) NOT NULL COMMENT 'Batch name',
              `exp_date` varchar(55) NOT NULL COMMENT 'Expiry Date',
              `ip` varchar(11) NOT NULL,
              `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $this->m->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}sreturn` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `slno` int(20) NOT NULL,
                `date` date NOT NULL,
                `id_head` int(10) NOT NULL,
                `id_company` int(10) NOT NULL,
                `id_area` int(10) NOT NULL,
                `id_represent` int(10) NOT NULL,
                `challan_no` varchar(35) NOT NULL,
                `challan_date` date NOT NULL,
                `id_transport` int(10) NOT NULL,
                `lr_no` varchar(35) NOT NULL,
                `lr_date` date NOT NULL,
                `station` varchar(55) NOT NULL,
                `cases` varchar(35) NOT NULL,
                `weight` varchar(35) NOT NULL,
                `discount` decimal(10,2) NOT NULL,
                `vat` decimal(10,2) NOT NULL,
                `pack_forward` decimal(10,4) NOT NULL,
                `add` decimal(10,4) NOT NULL,
                `less` decimal(10,4) NOT NULL,
                `round` decimal(2,2) NOT NULL,
                `total` decimal(10,2) NOT NULL,
                `pending` decimal(15,2) NOT NULL,
                `cash` tinyint(2) NOT NULL,
                `memo` tinytext NOT NULL,
                `cheque_no` varchar(25) NOT NULL,
                `cheque_date` date NOT NULL,
                `bank` varchar(35) NOT NULL,
                `chq_amount` decimal(10,2) NOT NULL,
                `id_form` int(10) NOT NULL,
                `frm_type` varchar(25) NOT NULL,
                `frm_no` varchar(25) NOT NULL,
                `frm_date` date NOT NULL,
                `frm_amount` decimal(10,4) NOT NULL,
                `waybill_no` varchar(25) NOT NULL,
                `waybill_date` date NOT NULL,
                `waybill_amount` decimal(10,4) NOT NULL,
                `ip` varchar(35) NOT NULL,
                `transport` varchar(50) NOT NULL,
                `payment` varchar(35) NOT NULL,
                `form` varchar(35) NOT NULL,
                `waybill` varchar(35) NOT NULL,
                `party_name` varchar(45) NOT NULL COMMENT 'Party Name',
                `party_address` tinytext NOT NULL COMMENT 'Party Address1',
                `party_address1` tinytext NOT NULL COMMENT 'Party Address2',
                `party_vattype` varchar(5) NOT NULL,
                `party_vatno` varchar(20) NOT NULL,
                `printed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                `id_create` int(10) NOT NULL,
                `create_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                `id_modify` int(10) NOT NULL,
                `modify_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
              ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $this->m->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}sreturndetail` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `slno` int(15) NOT NULL,
                `date` date NOT NULL,
                `id_product` int(10) NOT NULL,
                `rate` decimal(15,4) NOT NULL,
                `qty` decimal(12,4) NOT NULL,
                `free` decimal(12,4) NOT NULL,
                `amount` decimal(15,2) NOT NULL,
                `distype1` tinyint(2) NOT NULL,
                `distype2` tinyint(2) NOT NULL,
                `distype3` tinyint(2) NOT NULL,
                `dis1` decimal(10,4) NOT NULL,
                `dis2` decimal(10,2) NOT NULL,
                `dis3` decimal(10,2) NOT NULL,
                `disamt1` decimal(10,2) NOT NULL,
                `disamt2` decimal(10,2) NOT NULL,
                `disamt3` decimal(10,2) NOT NULL,
                `tax` int(10) NOT NULL,
                `taxamt` decimal(10,2) NOT NULL,
                `taxon` decimal(15,4) NOT NULL,
                `id_sreturn` int(10) NOT NULL,
                `id_head` int(10) NOT NULL,
                `id_batch` int(11) NOT NULL,
                `batch_name` varchar(25) NOT NULL,
                `exp_date` varchar(55) NOT NULL,
                `ip` varchar(11) NOT NULL,
                `date_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`id`)
              ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $this->m->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}taxmaster` (
                `id_taxmaster` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(30) DEFAULT NULL,
                `tax_per` decimal(6,2) DEFAULT NULL,
                `description` text NOT NULL,
                `status` tinyint(1) NOT NULL,
                `ip` varchar(30) NOT NULL,
                `id_create` int(11) NOT NULL,
                `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `id_modify` int(11) NOT NULL,
                `modify_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`id_taxmaster`)
              ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        $this->m->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}salesorder` (
                `id_salesorder` int(11) NOT NULL AUTO_INCREMENT, `invno` varchar(20) NOT NULL, `date` date NOT NULL,
                `id_head` int(11) NOT NULL, `id_company` int(11) NOT NULL, `id_area` int(11) NOT NULL,
                `id_represent` int(11) NOT NULL, `challan_no` varchar(20) NOT NULL, `challan_date` date NOT NULL,
                `totalamt` decimal(16,2) NOT NULL, `vat` decimal(16,2) NOT NULL, `total` decimal(16,2) NOT NULL,
                `pending` decimal(16,2) NOT NULL, `memo` text NOT NULL, `ip` varchar(30) NOT NULL,
                `id_create` int(11) NOT NULL, `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `id_modify` int(11) NOT NULL, `modify_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`id_salesorder`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
        $this->m->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}salesorderdetail` (
                `id_salesorderdetail` int(11) NOT NULL AUTO_INCREMENT, `invno` varchar(20) NOT NULL, `date` date NOT NULL,
                `id_product` int(11) NOT NULL, `rate` decimal(15,4) NOT NULL, `qty` decimal(12,4) NOT NULL,
                `free` decimal(12,4) NOT NULL, `amount` decimal(15,2) NOT NULL, `goods_amount` decimal(15,2) NOT NULL,
                `id_taxmaster` int(11) NOT NULL, `tax_per` decimal(8,2) NOT NULL, `tax_amount` decimal(15,2) NOT NULL,
                `net_amount` decimal(15,2) NOT NULL, `id_salesorder` int(11) NOT NULL, `id_company` int(11) NOT NULL,
                `id_head` int(11) NOT NULL, `id_represent` int(11) NOT NULL, `id_area` int(11) NOT NULL,
                `id_batch` int(11) NOT NULL, `batch_no` varchar(15) NOT NULL, `exp_date` varchar(15) NOT NULL,
                `ip` varchar(30) NOT NULL, `id_create` int(11) NOT NULL, `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `id_modify` int(11) NOT NULL, `modify_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`id_salesorderdetail`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
        $this->m->query($sql);
        $sql = "SHOW COLUMNS FROM `{$this->prefix}saledetail` LIKE 'cessper'";
        $uid = $this->m->num_rows($this->m->query($sql)) == 0 ? 0 : 1;
        if ($uid == 0) {
            $sql = "SHOW COLUMNS FROM `{$this->prefix}saledetail` LIKE 'cess'";
            $uid = $this->m->num_rows($this->m->query($sql)) == 0 ? 0 : 1;
            if ($uid == 0) {
                $sql = "ALTER TABLE `{$this->prefix}saledetail` ADD `cess` FLOAT(10,2) NOT NULL AFTER `discount_amount4`";
                $this->m->query($sql);
            }
        } else {
            $sql = "ALTER TABLE `{$this->prefix}saledetail` CHANGE `cessper` `cess` FLOAT(10,2) NOT NULL;";
            $this->m->query($sql);
        }
        $this->addfield('case', $this->prefix . 'saledetail', 'ADD `case` decimal(15,2)');
        $this->addfield('totalamt', $this->prefix . 'sreturn', 'ADD `totalamt` decimal(15,2)');
        $this->addfield('totalcess', $this->prefix . 'sreturn', 'ADD `totalcess` decimal(15,2)');
        $this->addfield('id_taxmaster', $this->prefix . 'sreturndetail', 'ADD `id_taxmaster` INT (11)');
        $this->addfield('goods_amount', $this->prefix . 'sreturndetail', 'ADD `goods_amount` FLOAT(14,2) NOT NULL');
        $this->addfield('net_amount', $this->prefix . 'sreturndetail', 'ADD `net_amount` FLOAT(14,2) NOT NULL');
        $this->addfield('batch_no', $this->prefix . 'sreturndetail', 'ADD `batch_no` varchar(15)');
        $this->addfield('cessamt', $this->prefix . 'sreturndetail', 'ADD `cessamt` FLOAT(12,2) NOT NULL');
        $this->addfield('cess', $this->prefix . 'sreturndetail', 'ADD COLUMN `cess` FLOAT(6,2)');

        $salarysql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}salary` (
                        `id_salary` int(11) NOT NULL AUTO_INCREMENT, `id_employee` int(11) NOT NULL DEFAULT '0',
                        `days` int(3) NOT NULL, `month` decimal(2,0) NOT NULL, `year` int(4) NOT NULL, `daysworked` int(3) NOT NULL,
                        `cbasic` decimal(15,2) NOT NULL, `cda` decimal(15,2) NOT NULL, `chra` decimal(15,2) NOT NULL, `cmedical` decimal(15,2) NOT NULL,
                        `cconvency` decimal(15,2) NOT NULL, `ctelephone` decimal(15,2) NOT NULL, `chealth` decimal(15,2) NOT NULL, `basic` decimal(15,2) NOT NULL,
                        `da` decimal(15,2) NOT NULL, `hra` decimal(15,2) NOT NULL, `medical` decimal(15,2) NOT NULL, `convecy` decimal(15,2) NOT NULL,
                        `telephone` decimal(15,2) NOT NULL, `health` decimal(15,2) NOT NULL, `net` decimal(15,2) NOT NULL, `esic` decimal(15,2) NOT NULL, `pf` decimal(15,2) NOT NULL,
                        `eesic` decimal(15,2) NOT NULL, `epf` decimal(15,2) NOT NULL, `insurance` decimal(15,2) NOT NULL,
                        `pt` decimal(15,2) NOT NULL, `tds` decimal(15,2) NOT NULL, `ec` decimal(15,2) NOT NULL, `gross` decimal(15,2) NOT NULL,
                        `padvance` decimal(15,2) NOT NULL, `advance` decimal(15,2) NOT NULL, `totaladvance` decimal(15,2) NOT NULL, `deduct_adv` decimal(15,2) NOT NULL,
                        `deduct_tada` decimal(15,2) NOT NULL, `deduct_allow` decimal(15,2) NOT NULL, `total` decimal(15,2) NOT NULL, `ip` varchar(30) NOT NULL,
                        `id_create` int(11) NOT NULL, `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        `id_modify` int(11) NOT NULL, `modify_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', PRIMARY KEY (`id_salary`) ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $this->m->query($salarysql);
        $sql = "SHOW COLUMNS FROM `{$this->prefix}salary` LIKE 'cconvency'";
        $uid = $this->m->num_rows($this->m->query($sql)) == 0 ? 0 : 1;
        if ($uid == 0) {
            $sql = "DROP TABLE `{$this->prefix}salary`";
            $this->m->query($sql);
            $this->m->query($salarysql);
        }
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}employee` (
                            `id_employee` int(11) NOT NULL, `name` varchar(60) NOT NULL, `function` varchar(60) NOT NULL, `designation` varchar(60) NOT NULL, 
                            `location` varchar(60) NOT NULL, `bank` varchar(60) NOT NULL, `doj` date NOT NULL, `pan` varchar(30) NOT NULL, 
                            `pfdetails` varchar(60) NOT NULL, `esidetails` varchar(60) NOT NULL, `prdetails` varchar(60) NOT NULL, `no` varchar(10) NOT NULL, 
                            `active` tinyint(1) NOT NULL, `basic` decimal(15,2) NOT NULL, `da` decimal(15,2) NOT NULL, `hra` decimal(15,2) NOT NULL, 
                            `medical` decimal(15,2) NOT NULL, `convency` decimal(15,2) NOT NULL, `telephone` decimal(15,2) NOT NULL, 
                            `ta` decimal(15,2) NOT NULL, `other` decimal(15,2) NOT NULL, `esi` decimal(15,2) NOT NULL, `epf` decimal(15,2) NOT NULL, 
                            `lic` decimal(15,2) NOT NULL, `advance` decimal(15,2) NOT NULL, `ip` varchar(30) NOT NULL, `id_create` int(11) NOT NULL, 
                            `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, `id_modify` int(11) NOT NULL, 
                            `modify_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `acno` varchar(30) DEFAULT NULL, `health` decimal(15,2) DEFAULT NULL,
                            PRIMARY KEY (`id_employee`) ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $this->m->query($sql);
        $this->changefield('tax', $this->prefix . 'sreturndetail', 'CHANGE `tax` `tax_per` decimal(8,2)	');
        $this->changefield('taxamt', $this->prefix . 'sreturndetail', 'CHANGE `taxamt` `tax_amount` decimal(15,2)');
        $this->changefield('distype1', $this->prefix . 'sreturndetail', 'CHANGE `distype1` `discount_type1` TINYINT (2)');
        $this->changefield('distype2', $this->prefix . 'sreturndetail', 'CHANGE `distype2` `discount_type2` TINYINT (2)');
        $this->changefield('distype3', $this->prefix . 'sreturndetail', 'CHANGE `distype3` `discount_type3` TINYINT (2)');
        $this->changefield('dis1', $this->prefix . 'sreturndetail', 'CHANGE `dis1` `discount1` decimal(10,2)');
        $this->changefield('dis2', $this->prefix . 'sreturndetail', 'CHANGE `dis2` `discount2` decimal(10,2)');
        $this->changefield('dis3', $this->prefix . 'sreturndetail', 'CHANGE `dis3` `discount3` decimal(10,2)');
        $this->changefield('disamt1', $this->prefix . 'sreturndetail', 'CHANGE `disamt1` `discount_amount1` decimal(10,2)');
        $this->changefield('disamt2', $this->prefix . 'sreturndetail', 'CHANGE `disamt2` `discount_amount2` decimal(10,2)');
        $this->changefield('disamt3', $this->prefix . 'sreturndetail', 'CHANGE `disamt3` `discount_amount3` decimal(10,2)');

        $this->addfield('allow', $this->prefix . 'salary', 'ADD `allow` decimal(15,2)');
        $this->addfield('add_allow', $this->prefix . 'salary', 'ADD `add_allow` decimal(15,2)');
        $this->addfield('id_head', $this->prefix . 'employee', 'ADD `id_head` INT (11)');
        $this->addfield('status', $this->prefix . 'employee', 'ADD `status` TINYINT (1) NOT NULL');
        $this->addfield('id_approve', $this->prefix . 'salesorder', 'ADD `id_approve` INT (11)');
        $this->addfield('is_approve', $this->prefix . 'salesorder', 'ADD `is_approve` TINYINT (1)');
        $this->addfield('is_billed', $this->prefix . 'salesorder', 'ADD `is_billed` TINYINT (1)');
        $this->addfield('approve_date', $this->prefix . 'salesorder', 'ADD `approve_date` timestamp');
        $this->addfield('schemeapprove', $this->prefix . 'salesorder', 'ADD `schemeapprove` TEXT');
        $this->addfield('scheme', $this->prefix . 'salesorder', 'ADD `scheme` TINYINT (1)');
        $this->addfield('id_series', $this->prefix . 'salesorder', 'ADD `id_series` INT (11)');
        $this->addfield('id_sale', $this->prefix . 'salesorder', 'ADD `id_sale` INT (11)');
        $this->addfield('vehicle', $this->prefix . 'sale', 'ADD `vehicle` TINYINT (1)');
        $this->addfield('vehicle_contact', $this->prefix . 'sale', 'ADD `vehicle_contact` VARCHAR(40)');
        $this->addfield('vehicle_number', $this->prefix . 'sale', 'ADD `vehicle_number` VARCHAR(30)');
        $this->addfield('vehicle_amount', $this->prefix . 'sale', 'ADD `vehicle_amount` FLOAT(12,2)');
        $this->addfield('cessamt', $this->prefix . 'saledetail', 'ADD `cessamt` FLOAT(12,2) NOT NULL');
        $this->addfield('totalcess', $this->prefix . 'sale', 'ADD `totalcess` FLOAT(12,2) NOT NULL');
        $this->addfield('totalcess', $this->prefix . 'purchase', 'ADD `totalcess` FLOAT(12,2) NOT NULL');
        $this->addfield('cess', $this->prefix . 'purchasedetail', 'ADD `cess` FLOAT(10,4) NOT NULL;');
        $this->addfield('cessamt', $this->prefix . 'purchasedetail', 'ADD `cessamt` FLOAT(12,2) NOT NULL;');
        $this->addfield('acno', $this->prefix . 'employee', 'ADD COLUMN `acno` VARCHAR(30)');
        $this->addfield('health', $this->prefix . 'employee', 'ADD COLUMN `health` decimal(15,2)');
        $this->addfield('hsncode', $this->prefix . 'product', 'ADD COLUMN `hsncode` VARCHAR(20) NOT NULL AFTER `name`');
        $this->addfield('maximum_stock', $this->prefix . 'product', 'ADD `maximum_stock`  decimal(12,3)');
        $this->addfield('minimum_stock', $this->prefix . 'product', 'ADD `minimum_stock`  decimal(12,3)');
        $this->addfield('cess', $this->prefix . 'product', 'ADD COLUMN `cess` FLOAT(6,2) AFTER `hsncode`');
        $this->addfield('dealer', $this->prefix . 'head', 'ADD COLUMN `dealer` INT(2) COMMENT "0-Distributor/Retailer, 1-Super-Distributor"');
        $this->addfield('pincode', $this->prefix . 'head', 'ADD COLUMN `pincode` VARCHAR(30)');
        $this->addfield('gstin', $this->prefix . 'head', 'ADD COLUMN `gstin` VARCHAR(30)');
        $this->addfield('adhar', $this->prefix . 'head', 'ADD COLUMN adhar VARCHAR(30)');
        $this->addfield('state', $this->prefix . 'head', 'ADD COLUMN `state` VARCHAR(30)');
        $this->addfield('panno', $this->prefix . 'head', 'ADD COLUMN `panno` VARCHAR(30)');
        $this->addfield('id_user', $this->prefix . 'head', 'ADD COLUMN `id_user` INT (11)');
        $this->addfield('ip', $this->prefix . 'head', 'ADD COLUMN `ip` VARCHAR (30)');
        $this->addfield('flicence', $this->prefix . 'head', 'ADD COLUMN `flicence` VARCHAR(40) NOT NULL AFTER dlicence');

        $this->addfield('acno', $this->prefix . 'head', 'ADD COLUMN `acno` VARCHAR(45)');
        $this->addfield('acifsc', $this->prefix . 'head', 'ADD COLUMN `acifsc` VARCHAR(30)');
        $this->addfield('acname', $this->prefix . 'head', 'ADD COLUMN `acname` VARCHAR(45)');
        $this->addfield('actype', $this->prefix . 'head', 'ADD COLUMN `actype` VARCHAR(30)');
        $this->addfield('moderntrade_price', $this->prefix . 'batch', 'ADD COLUMN `moderntrade_price` decimal(15,2)');



        $this->addfield('id_represent', $this->prefix . 'area', 'ADD COLUMN `id_represent` INT(10)');
        $this->changefield('rate', $this->prefix . 'adjustdetail', 'CHANGE `rate` `rate` FLOAT(15,2) NOT NULL');
        $this->changefield('ref1', $this->prefix . 'voucher', 'CHANGE `ref1` `ref1` VARCHAR(40)');
        $this->changefield('ref2', $this->prefix . 'voucher', 'CHANGE `ref2` `ref2` VARCHAR(40)');
        $this->addfield('ref1', $this->prefix . 'voucher_details', 'ADD COLUMN `ref1` VARCHAR (40)');
        $this->addfield('ref2', $this->prefix . 'voucher_details', 'ADD COLUMN `ref2` VARCHAR (40)');
        $this->changefield('ref1', $this->prefix . 'voucher_details', 'CHANGE `ref1` `ref1` VARCHAR(40)');
        $this->changefield('ref2', $this->prefix . 'voucher_details', 'CHANGE `ref2` `ref2` VARCHAR(40)');
        $this->changefield('id', $this->prefix . 'sreturn', 'CHANGE `id` `id_sreturn` INT(11) AUTO_INCREMENT');
        $this->changefield('id', $this->prefix . 'sreturndetail', 'CHANGE `id` `id_sreturndetail` INT(11) AUTO_INCREMENT');
        $this->changefield('id', $this->prefix . 'preturn', 'CHANGE `id` `id_preturn` INT(11) AUTO_INCREMENT');
        $this->changefield('id', $this->prefix . 'preturndetail', 'CHANGE `id` `id_preturndetail` INT(11) AUTO_INCREMENT');
        $this->changefield('add', $this->prefix . 'sreturn', 'CHANGE `add` `add` decimal(10,2) NOT NULL');
        $this->changefield('less', $this->prefix . 'sreturn', 'CHANGE `less` `less` decimal(10,2) NOT NULL');
        $this->addfield('mode', $this->prefix . 'sreturn', 'ADD COLUMN `mode` TINYINT (1)');
        $this->addfield('mode', $this->prefix . 'sreturndetail', 'ADD COLUMN `mode` TINYINT (1)');
        $this->addfield('mode', $this->prefix . 'preturn', 'ADD COLUMN `mode` TINYINT (1)');
        $this->addfield('mode', $this->prefix . 'preturndetail', 'ADD COLUMN `mode` TINYINT (1)');
        $this->addfield('challan_no', $this->prefix . 'preturn', 'ADD COLUMN `challan_no` VARCHAR (20)');
        $this->addfield('challan_date', $this->prefix . 'preturn', 'ADD COLUMN `challan_date` DATE ');

        $this->addfield('totalamt', $this->prefix . 'preturn', 'ADD `totalamt` decimal(15,2)');
        $this->addfield('round', $this->prefix . 'preturn', 'ADD `round` decimal(15,2)');
        $this->addfield('pending', $this->prefix . 'preturn', 'ADD `pending` decimal(15,2)');
        $this->addfield('totalcess', $this->prefix . 'preturn', 'ADD `totalcess` decimal(15,2)');
        $this->addfield('id_taxmaster', $this->prefix . 'preturndetail', 'ADD `id_taxmaster` INT (11)');
        $this->addfield('goods_amount', $this->prefix . 'preturndetail', 'ADD `goods_amount` FLOAT(14,2) NOT NULL');
        $this->addfield('net_amount', $this->prefix . 'preturndetail', 'ADD `net_amount` FLOAT(14,2) NOT NULL');
        $this->addfield('batch_no', $this->prefix . 'preturndetail', 'ADD `batch_no` varchar(15)');
        $this->addfield('cessamt', $this->prefix . 'preturndetail', 'ADD `cessamt` FLOAT(12,2) NOT NULL');
        $this->addfield('cess', $this->prefix . 'preturndetail', 'ADD COLUMN `cess` FLOAT(6,2)');
        $this->changefield('tax', $this->prefix . 'preturndetail', 'CHANGE `tax` `tax_per` decimal(8,2)	');
        $this->changefield('taxamt', $this->prefix . 'preturndetail', 'CHANGE `taxamt` `tax_amount` decimal(15,2)');
        $this->changefield('distype1', $this->prefix . 'preturndetail', 'CHANGE `distype1` `discount_type1` TINYINT (2)');
        $this->changefield('distype2', $this->prefix . 'preturndetail', 'CHANGE `distype2` `discount_type2` TINYINT (2)');
        $this->changefield('distype3', $this->prefix . 'preturndetail', 'CHANGE `distype3` `discount_type3` TINYINT (2)');
        $this->changefield('dis1', $this->prefix . 'preturndetail', 'CHANGE `dis1` `discount1` decimal(10,2)');
        $this->changefield('dis2', $this->prefix . 'preturndetail', 'CHANGE `dis2` `discount2` decimal(10,2)');
        $this->changefield('dis3', $this->prefix . 'preturndetail', 'CHANGE `dis3` `discount3` decimal(10,2)');
        $this->changefield('disamt1', $this->prefix . 'preturndetail', 'CHANGE `disamt1` `discount_amount1` decimal(10,2)');
        $this->changefield('disamt2', $this->prefix . 'preturndetail', 'CHANGE `disamt2` `discount_amount2` decimal(10,2)');
        $this->changefield('disamt3', $this->prefix . 'preturndetail', 'CHANGE `disamt3` `discount_amount3` decimal(10,2)');


        $this->addfield('waybill', $this->prefix . 'sale', 'ADD `waybill` VARCHAR(25)');
        $this->addfield('waybill_date', $this->prefix . 'sale', 'ADD `waybill_date` DATE');
        $this->addfield('waybill_amount', $this->prefix . 'sale', 'ADD `waybill_amount` decimal(15,2)');

        $this->changefield('opening_stock', $this->prefix . 'batch', 'CHANGE `opening_stock` `opening_stock` decimal(16,4) NOT NULL DEFAULT 0');
        $this->changefield('opening_stock_free', $this->prefix . 'batch', 'CHANGE `opening_stock_free` `opening_stock_free` decimal(16,4) NOT NULL DEFAULT 0');

        $this->addfield('mode', $this->prefix . 'debitnotedetail', 'ADD `mode` varchar(1)');
        $this->addfield('mode', $this->prefix . 'creditnotedetail', 'ADD `mode` varchar(1)');
        $sql = "SHOW COLUMNS FROM `info` LIKE 'gstin'";
        $uid = $this->m->num_rows($this->m->query($sql)) == 0 ? 0 : 1;
        if ($uid == 0) {
            $sql = "ALTER TABLE `info` ADD COLUMN adhar VARCHAR(30), ADD COLUMN `gstin` VARCHAR(30) NOT NULL AFTER `membercode`, ADD COLUMN `gstdate` DATE NULL AFTER `gstin`,ADD COLUMN `flicence` VARCHAR(40) NOT NULL AFTER gstin, ADD COLUMN `dlicence` VARCHAR(40) NOT NULL AFTER flicence";
            $this->m->query($sql);
        }
        $sql = "SHOW COLUMNS FROM `{$this->prefix}salesorder` LIKE 'totalcess'";
        $uid = $this->m->num_rows($this->m->query($sql)) == 0 ? 0 : 1;
        if ($uid == 0) {
            $sql = "ALTER TABLE `{$this->prefix}salesorder` ADD `totalcess` FLOAT(16,2) NOT NULL AFTER `vat`;";
            $this->m->query($sql);
            $sql = "ALTER TABLE `{$this->prefix}salesorderdetail` ADD `cess` FLOAT(8,2) NOT NULL AFTER `tax_amount`, ADD `cessamt` FLOAT(15,2) NOT NULL AFTER `cess`;";
            $this->m->query($sql);
        }
        $this->check_index();
        $this->upgrade();
        $_SESSION['msg'] = "Software Upgraded Successful.";
        $this->sm->assign("page", "util/check.tpl.html");
    }

    function check_index() {
        $this->m->query("DROP view IF EXISTS `{$this->prefix}product_ledger`");
        $sql = "SELECT SUM(opening_stock+opening_stock_free) AS stock FROM `{$this->prefix}batch`";
        $rs = $this->m->fetch_assoc($sql);
        if ($rs['stock']>0) {
            $prod = "SELECT 'O' AS type, id_product, '' AS id_head, '' AS ttype, id_batch, '' AS id, '' AS refno, NULL AS date, ROUND(opening_stock,4) AS qty, ROUND(opening_stock_free,4) as free, 0 AS amount FROM `{$this->prefix}batch`";
        } else {
            $prod = "SELECT 'O' AS type, id_product, '' AS id_head, '' AS ttype, 0 AS id_batch, '' AS id, '' AS refno, NULL AS date, ROUND(opening_stock,4) AS qty, 0 as free, 0 AS amount FROM `{$this->prefix}product`";
        }
        $sql = "CREATE view `{$this->prefix}product_ledger` AS $prod
                  UNION ALL 
                  SELECT 'S' AS type, id_product, id_head, 'Sales' AS ttype, id_batch, id_sale AS id, invno AS refno, date, ROUND(-qty,4), ROUND(-free,4), -amount FROM `{$this->prefix}saledetail` 
                  UNION ALL
                  SELECT 'SR' AS type, id_product, id_head, 'Sales Return' AS ttype, id_batch, id_sreturn AS id, slno AS refno, date, ROUND(qty,4), ROUND(free,4), amount  FROM `{$this->prefix}sreturndetail`
                  UNION ALL
		  SELECT 'DN' AS type, id_product, id_head, 'Debit Note' AS ttype, id_batch, id_debitnotedetail AS id, no AS refno, date, ROUND(-qty,4), ROUND(-free,4), amount  FROM `{$this->prefix}debitnotedetail`WHERE mode='S'
		  UNION ALL
                  SELECT 'CN' AS type, id_product, id_head, 'Credit Note' AS ttype, id_batch, id_creditnotedetail AS id, no AS refno, date, ROUND(qty,4), ROUND(free,4), amount  FROM `{$this->prefix}creditnotedetail` WHERE mode='S'
                  UNION ALL
                  SELECT 'PR' AS type, id_product, id_head, 'Purchase Return' AS ttype, id_batch, id_preturn AS id, slno AS refno, date, ROUND(-qty,4), ROUND(-free,4), -amount  FROM `{$this->prefix}preturndetail` 
                  UNION ALL
                  SELECT 'SA' AS type, id_product, '', 'Stock Adjustment' AS ttype, id_batch, id_adjust AS id, no AS refno, date, ROUND(qty,4), ROUND(free,4), amount  FROM `{$this->prefix}adjustdetail` 
                  UNION ALL
                  SELECT 'P' AS type, id_product, id_head, 'Purchase' AS ttype, id_batch, id_purchase AS id, no AS refno, date, ROUND(qty,4), ROUND(free,4), amount  FROM `{$this->prefix}purchasedetail`
                  UNION ALL
                  SELECT 'PD' AS type, id_product, '', type AS ttype, id_batch, id_production AS id, slno AS refno, date, ROUND(qty,4), ROUND(free,4), amount  FROM `{$this->prefix}productiondetail` WHERE `type`='Produce' OR `type`='Add'
                  UNION ALL
                  SELECT 'S' AS type, id_product, '', type AS ttype, id_batch, id_production AS id, slno AS refno, date, ROUND(-qty,4), ROUND(-free,4), -amount  FROM `{$this->prefix}productiondetail` WHERE `type`='Issue' OR `type`='Reduce' OR `type`='Wastage'";
        $this->m->query($sql);

        $this->m->query("DROP view IF EXISTS `{$this->prefix}product_ledger_summary`");
        $sql = "CREATE view `{$this->prefix}product_ledger_summary` AS SELECT id_product, SUM(qty+IF(free IS NULL, 0, free)) AS balance, SUM(amount) AS amount FROM `{$this->prefix}product_ledger` GROUP BY id_product";
        $this->m->query($sql);

	$sql = "SELECT UPPER(p.name) AS name, h.id_head FROM `{$this->prefix}param` p, `{$this->prefix}head` h
                WHERE (p.name='saleac' OR p.name='purcac' OR p.name='cash' OR p.name='SALESRETURN' OR p.name='PURCHASERETURN') AND p.content=h.code";
        $data = $this->m->sql_getall($sql, 2, "id_head", "name");
        $cash = $data['CASH'];
        $sale = $data['SALEAC'];
        $purc = $data['PURCAC'];
        $saler = isset($data['SALESRETURN']) ? $data['SALESRETURN'] : $sale;
        $purcr = isset($data['PURCHASERETURN']) ? $data['PURCHASERETURN'] : $purc;

        $this->addfield('purchase_type', $this->prefix . 'purchase', 'ADD purchase_type VARCHAR(10) NOT NULL DEFAULT "Purchase"');
        $this->addfield('id_account', $this->prefix . 'purchase', 'ADD `id_account` INT(11)');
        $sql = "UPDATE `{$this->prefix}purchase` SET id_account='$purc' WHERE id_account=0 OR purchase_type='Purchase'";
        $this->m->query($sql);

        $sql = "DROP view IF EXISTS `{$this->prefix}ledger`";
        $this->m->query($sql);
        
	    $sql = "SELECT value FROM `configuration` WHERE name='ADJUST SRETURN IN LEDGER'";
        $data = $this->m->sql_getall($sql);
	    $srtnsql = @$data[0]['value']=="NO" ? "" : "SELECT 'S' AS type, id_sreturn AS id, slno AS refno, date, id_head AS chead, {$saler} AS dhead, total, memo 
            FROM `{$this->prefix}sreturn` WHERE date BETWEEN '{$_SESSION['sdate']}' AND '{$_SESSION['edate']}' UNION ALL";

        $sql = "SELECT value FROM `configuration` WHERE name='ADJUST CREDIT NOTE IN LEDGER'";
        $data = $this->m->sql_getall($sql);
        $cnsql = @$data[0]['value']=="YES" ? "SELECT 'CN' AS type, id_creditnote AS id, no AS refno, date, id_head AS chead, {$saler} AS dhead, total, memo 
            FROM `{$this->prefix}creditnote` WHERE date BETWEEN '{$_SESSION['sdate']}' AND '{$_SESSION['edate']}' UNION ALL" : "";

        $sql = "SELECT value FROM `configuration` WHERE name='ADJUST DEBIT NOTE IN LEDGER'";
        $data = $this->m->sql_getall($sql);
        $dnsql = @$data[0]['value']=="YES" ? "SELECT 'CN' AS type, id_debitnote AS id, no AS refno, date, id_head AS chead, {$saler} AS dhead, total, memo 
            FROM `{$this->prefix}debitnote` WHERE date BETWEEN '{$_SESSION['sdate']}' AND '{$_SESSION['edate']}' UNION ALL" : "";

        $sql = "CREATE view `{$this->prefix}ledger` AS 
        		  {$srtnsql} {$cnsql} {$dnsql}
                  SELECT 'S' AS type, id_sale AS id, invno AS refno, date, {$sale} AS chead, IF(cash=1,id_head,{$cash}) AS dhead, total, memo 
                        FROM `{$this->prefix}sale` WHERE date BETWEEN '{$_SESSION['sdate']}' AND '{$_SESSION['edate']}'
                  UNION ALL
                  SELECT 'P' AS type, id_preturn AS id, slno AS refno, date, {$purcr} AS chead, id_head AS dhead, total, memo 
                        FROM `{$this->prefix}preturn` WHERE date BETWEEN '{$_SESSION['sdate']}' AND '{$_SESSION['edate']}'
                  UNION ALL
                  SELECT 'P' AS type, id_purchase AS id, bill_no AS refno, date, IF(cash=1,id_head,{$cash}) AS chead, id_account AS dhead, total, memo
                        FROM `{$this->prefix}purchase` WHERE date BETWEEN '{$_SESSION['sdate']}' AND '{$_SESSION['edate']}'
                  UNION ALL
                  SELECT 'V' AS type, id_voucher AS id, no AS refno, date, id_head_credit AS chead, id_head_debit AS dhead, total, memo
                        FROM `{$this->prefix}voucher` WHERE date BETWEEN '{$_SESSION['sdate']}' AND '{$_SESSION['edate']}'
                  UNION ALL
                  SELECT 'H' AS type, id_head AS id, '' AS refno, '' AS date, IF(otype='D' OR otype='0', 0, id_head) AS chead, IF(otype='D' OR otype='0', id_head, 0) AS dhead, opening_balance AS total, '' AS memo 
                        FROM `{$this->prefix}head`";
        $this->m->query($sql);
        $this->m->query("DROP view IF EXISTS `{$this->prefix}tb`");
        $sql = "CREATE view `{$this->prefix}tb` AS 
                  SELECT dhead AS id_head, ROUND(SUM(total),2) AS debit, 0 AS credit  FROM `{$this->prefix}ledger` GROUP BY 1
                  UNION ALL 
                  SELECT chead AS id_head, 0 AS debit, ROUND(SUM(total),2) AS credit FROM `{$this->prefix}ledger` GROUP BY 1";
        $this->m->query($sql);
        $this->sm->assign("msg", "Ledger View create/replace.<br>TB View create/replace.<br>");
        $this->sm->assign("page", "util/check.tpl.html");
    }
    function dropindex() {
        $this->dropone($this->prefix."sreturndetail");
        $this->dropone($this->prefix."preturndetail");
        //$this->dropone($this->prefix."saledetail");
        //$this->dropone($this->prefix."purchasedetail");
        $this->dropone($this->prefix."salesorderdetail");
        $this->dropone($this->prefix."debitnotedetail");
        $this->dropone($this->prefix."creditnotedetail");
	$_SESSION['msg'] = "Index dropped. Successful.";
	$this->redirect("index.php");
    }

    function dropone($tbl) {
	$sql = "SHOW INDEX FROM $tbl";
	$r = $this->m->sql_getall($sql);
	foreach ($r as $k => $v) {
	    $idx = $v['Key_name'];
	    $sql1 = "ALTER TABLE $tbl DROP INDEX $idx;";
	    echo $sql1."<br>";
	    mysql_query($sql1);
	}
    }

    function check_autoincrement() {
        $msg = "<b>Autoincrement Checking</b><br><br>";
        $prefix = $this->prefix;
        $exclude = array($prefix . "ledger", $prefix . "product_ledger", $prefix . "product_ledger_summary", $prefix . "tb");
        $sql = "SHOW TABLES LIKE '{$prefix}_%'";
        $rs = mysql_query($sql);
        $records = $this->m->num_rows($rs);
        for ($i = 1; $i <= $records; $i++) {
            $data = $this->m->fetch_array($rs);
            $tablename = $data[0];
            if (!in_array($tablename, $exclude)) {
                $sql1 = "describe `{$tablename}`";
                $dt1 = $this->m->fetch_assoc($sql1);
                if ($dt1['Key'] != 'PRI') {
                    $msg .= $tablename . " do not Primary Key<br>";
                    $fld = $dt1['Field'];
                    $sql1 = "ALTER TABLE {$data[0]} CHANGE {$fld} {$fld} INT(11) AUTO_INCREMENT PRIMARY KEY;";
                    mysql_query($sql1);
                }
            } else {
                $msg .= $tablename . " is a view.<br>";
            }
        }
        $sql1 = "ALTER TABLE user CHANGE id_user id_user INT(11) primary key AUTO_INCREMENT  not null;";
        mysql_query($sql1);
        $sql1 = "ALTER TABLE info CHANGE id_info id_info INT(11) primary key AUTO_INCREMENT  not null;";
        mysql_query($sql1);
        $sql1 = "ALTER TABLE template CHANGE id_template id_template INT(11) primary key AUTO_INCREMENT  not null;";
        mysql_query($sql1);
        $this->sm->assign("msg", $msg);
        $this->sm->assign("page", "util/check.tpl.html");
    }

    function detail() {
        $data = $this->m->all_Tables($this->prefix);
        $this->sm->assign("prefix", $this->prefix);
        $this->sm->assign("detail", $data);
    }

    function import() {
        //    $this->sm->assign("page", "util/import.tpl.html");
    }

    function ulocal() {
        $sql = "SELECT id_head AS id,name FROM {$this->prefix}head WHERE debtor ORDER BY name";
        $this->sm->assign("head", json_encode($this->m->sql_getall($sql, 2, "name", "id")));
        $sql = "SELECT id_company AS id,name FROM {$this->prefix}company ORDER BY name";
        $this->sm->assign("company", json_encode($this->m->sql_getall($sql, 2, "name", "id")));
        $sql = "SELECT id_product AS id,name,mrp,seller_price,id_taxmaster_sale,pack,unit,`case`,closing_stock FROM {$this->prefix}product ORDER BY name";
        $data = json_encode($this->m->sql_getall($sql, 1, "name", "id"));
        $this->sm->assign("product", $data);
    }

    function drawform() {
        
    }

    function upload() {
        ini_set('upload_max_filesize', '250M');
        ini_set('post_max_size', '250M');
        ini_set('max_input_time', 300);
        ini_set('max_execution_time', 300);
        $dest = trim($this->prefix, "_");
        move_uploaded_file($_FILES['upload']['tmp_name'], "data/{$dest}.zip");
        $zip = new ZipArchive;
        $res = $zip->open("data/{$dest}.zip");
        if ($res === TRUE) {
            $zip->extractTo("data/{$dest}/");
            $zip->close();
            $msg = 'Zip file Uploaded and extracted Successfully!';
        } else {
            $msg = 'Problem while extractin zip File!';
        }
        unlink("data/{$dest}.zip");
        $type = isset($_REQUEST['type']) ? "&type=" . $_REQUEST['type'] : "";
        $link = "index.php?module=import&func=convert_all" . $type;
        $this->redirect($link);
        //$this->sm->assign("msg", $msg);
        //$this->sm->assign("link", $link);
    }

    function create_backup() {
        set_time_limit(6000);
        ini_set("memory_limit", "256M");
        date_default_timezone_set('Asia/Kolkata');
        $tables = '*';
        if ($tables == '*') {
            $tables = array();
            $result = mysql_query('SHOW FULL TABLES WHERE Table_Type = "BASE TABLE"');
            while ($row = mysql_fetch_row($result)) {
                $tables[] = $row[0];
            }
        } else {
            if (is_array($tables)) {
                $tables = explode(',', $tables);
            }
        }
        $return = "";
        foreach ($tables as $table) {
            $result = mysql_query("SELECT * FROM `$table`");
            $num_fields = mysql_num_fields($result);

            $return .= "DROP TABLE IF EXISTS `$table`;<|||||||>";

            $row2 = mysql_fetch_row(mysql_query("SHOW CREATE TABLE `$table`"));
            $return .= "\n\n" . $row2[1] . ";<|||||||>\n\n";

            for ($i = 0; $i < $num_fields; $i++) {
                while ($row = mysql_fetch_row($result)) {
                    $return.= "INSERT INTO `$table` VALUES(";
                    for ($j = 0; $j < $num_fields; $j++) {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = str_replace("\n", "\\n", $row[$j]);
                        if (isset($row[$j])) {
                            $return .= '"' . $row[$j] . '"';
                        } else {
                            $return .= '""';
                        }
                        if ($j < ($num_fields - 1)) {
                            $return.= ',';
                        }
                    }
                    $return.= ");<|||||||>\n";
                }
            }
            $return.="\n\n\n";
        }
        $fname = str_replace(" ", "_", trim(trim($_SESSION['companyname']), "."));
        $fname = str_replace("&", "_", $fname);
        $sdate = substr($_SESSION['sdate'], 0, 4);
        $edate = substr($_SESSION['edate'], 0, 4);
        $this->file = $file = 'backup/' . $fname . "_" . $sdate . "_" . $edate . "_" . date("d-m-Y_h_i_s") . '.sql';
        $handle = fopen($file, 'w') or die("Can't open file");
        fwrite($handle, $return);
        fclose($handle);
    }

    function backup() {
        $this->create_backup();
        $_SESSION['msg'] = "Backup is Successful. File Created : " . $this->file;
        $this->redirect("index.php?module=util&func=listing");
    }

    function listing() {
        //$this->get_permission("util","UPDATE");
        $files = array();
        if ($dir = opendir("backup")) {
            while (($file = readdir($dir)) !== false) {
                if (strpos($file, '.sql', 1)) {
                    $date = substr($file, strlen($file) - 23, 19);
                    $filename = str_replace('.sql', '', $file);
                    $files[] = array("date" => str_replace("_", ":", $date), "filename" => $filename);
                }
            }
        }
        $this->sm->assign("file", $files);
        $this->sm->assign("page", "util/listing.tpl.html");
    }

    function delete() {
        //          $this->get_permission("util","DELETE");
        $file = $_REQUEST['id'];
        if (!is_dir("backup/" . $file . '.sql')) {
            unlink("backup/" . $file . '.sql');
            $_SESSION['msg'] = "Backup file Successfully Delete.";
        } else {
            $_SESSION['msg'] = "Error occured while Deleting Backup file!!!";
        }
        $this->redirect("index.php?module=util&func=listing");
    }

    function downsql() {
        //         $this->get_permission("util","REPORT");
        $file = "backup/" . $_REQUEST['id'];
        if (!is_dir($file)) {
            header('Content-type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $_REQUEST['id'] . '"');
            readfile($file);
            exit;
        }
    }

    function downzip() {
        //        $this->get_permission("util","REPORT");
        $file = "backup/" . $_REQUEST['id'];
        if (!is_dir($file)) {
            require_once('util/pclzip.lib.php');
            $archive = new PclZip("backup/tmp.zip");
            $v_dir = dirname(getcwd()); // or dirname(__FILE__);
            $v_remove = $v_dir;
            $v_list = $archive->create($file, PCLZIP_OPT_REMOVE_PATH, $v_remove);
            if ($v_list == 0) {
                die("Error : " . $archive->errorInfo(true));
            }
            header('Content-Type: application/zip');
            header('Content-Length: ' . filesize("backup/tmp.zip"));
            header('Content-Disposition: attachment; filename="' . $_REQUEST['id'] . '.zip"');
            readfile("backup/tmp.zip");
            unlink("backup/tmp.zip");
            exit;
        }
    }

    function restore() {
        set_time_limit(6000);
        //        $this->get_permission("util","UPDATE");
        $this->create_backup();
        $file = "backup/" . $_REQUEST['id'] . ".sql";
        if (!is_dir($file)) {
            $f = fopen($file, "r+");
            $sqlFile = fread($f, filesize($file));
            $sqlArray = explode(';<|||||||>', $sqlFile);
            foreach ($sqlArray as $stmt) {
                if (strlen($stmt) > 3) {
                    mysql_query($stmt);
                }
            }
            $_SESSION['msg'] = "Backup file Restored Successfully.";
        }
        $this->redirect("index.php?module=util&func=listing");
    }

    function transentryold() {
        echo "Please create Account Head as followings<br>";

        $sql = "SELECT p.name, h.id_head FROM `{$this->prefix}param` p, `{$this->prefix}head` h
                WHERE (p.name = 'saleac' OR p.name='purcac' OR p.name='cash' OR p.name='SALESRETURN' OR p.name='PURCHASERETURN') AND p.content=h.code";
        $data = $this->m->sql_getall($sql, 2, "id_head", "name");
        $sale = $data['saleac'] ? $data['saleac'] : $data['SALEAC'];
        $purc = $data['purcac'] ? $data['purcac'] : $data['PURCAC'];
        $saler = isset($data['SALESRETURN']) ? $data['SALESRETURN'] : $sale;
        $purcr = isset($data['PURCHASERETURN']) ? $data['PURCHASERETURN'] : $purc;
        $sql = 'DELETE FROM ' . $this->prefix . "voucher WHERE type=10";
        $this->m->query($sql);

        $sql = "SELECT sd.date, h.local, sd.tax_per, SUM(sd.tax_amount) as tax_amount 
                FROM {$this->prefix}saledetail sd LEFT JOIN {$this->prefix}head h ON sd.id_head=h.id_head GROUP BY 1, 2,3";
//echo $sql.'<r>';
        $this->updategstentries($sql, $sale, 1, "SALES GST");

        $sql = "SELECT sd.date, h.local, sd.tax_per, SUM(sd.tax_amount) as tax_amount 
                FROM {$this->prefix}purchasedetail sd LEFT JOIN {$this->prefix}head h ON sd.id_head=h.id_head GROUP BY 1, 2,3";
//echo $sql.'<br>';
        $this->updategstentries($sql, $purc, 2, "PURCHASE GST");

        echo "<h2>Please click <a href='index.php'>here</a> to go to Menu options.</h2>";
        exit;
        //$this->redirect("index.php");
    }
	
    function transentry() {
        echo "Please create Account Head as followings<br>";
        $sql = "SELECT p.name, h.id_head FROM `{$this->prefix}param` p, `{$this->prefix}head` h
                WHERE (p.name = 'saleac' OR p.name='purcac' OR p.name='cash' OR p.name='SALESRETURN' OR p.name='PURCHASERETURN') AND p.content=h.code";
        $data = $this->m->sql_getall($sql, 2, "id_head", "name");
        $sale = $data['saleac'] ? $data['saleac'] : $data['SALEAC'];
        $purc = $data['purcac'] ? $data['purcac'] : $data['PURCAC'];
        $saler = isset($data['SALESRETURN']) ? $data['SALESRETURN'] : $sale;
        $purcr = isset($data['PURCHASERETURN']) ? $data['PURCHASERETURN'] : $purc;
        $sql = 'DELETE FROM ' . $this->prefix . "voucher WHERE type=10";
        $this->m->query($sql);

        $sql = "SELECT sd.date, h.local, sd.tax_per, SUM(sd.tax_amount) as tax_amount 
                FROM {$this->prefix}saledetail sd LEFT JOIN {$this->prefix}head h ON sd.id_head=h.id_head GROUP BY 1, 2,3";
        $this->updategstentries($sql, $sale, 1, "SALES GST");

        $sql = "SELECT sd.date, h.local, sd.tax_per, SUM(sd.tax_amount) as tax_amount 
                FROM {$this->prefix}purchasedetail sd LEFT JOIN {$this->prefix}head h ON sd.id_head=h.id_head GROUP BY 1,2,3";
        $this->updategstentries($sql, $purc, 2, "PURCHASE GST");

        $sql = "SELECT sd.date, h.local, sd.tax_per, SUM(sd.tax_amount) as tax_amount 
                FROM {$this->prefix}sreturndetail sd LEFT JOIN {$this->prefix}head h ON sd.id_head=h.id_head GROUP BY 1,2,3";
        $this->updategstentries($sql, $saler, 2, "SALES RETURN GST");
        // $_SESSION['msg'] = "GST Journal Entry passed from Sales, Purchases and Sales Return.";
        // $this->redirect("index.php");
        echo "<h2>Please click <a href='index.php'>here</a> to go to Menu options.</h2>"; exit;
    }

    function updategstentries($sql, $acno, $type, $memo) {
        $data = $this->m->sql_getall($sql);
        foreach ($data as $k => $v) {
            if ($v['tax_per'] == 0) {
                continue;
            }
            if ($v['local'] == 0) {
                $tp = $v['tax_per'] / 2;
                $tp = (intval($tp) == $tp) ? intval($tp) : $tp;
                $str = "CGST " . $tp . "%";
                if (!isset($main[$str])) {
                    $sql = "SELECT id_head FROM {$this->prefix}head WHERE name='{$str}' LIMIT 1";
                    $c = $this->m->sql_getall($sql);
                    $main[$str] = isset($c[0]['id_head']) ? $c[0]['id_head'] : "";
                }
                if ($main[$str] != '') {
                    $dhead = ($type == 1) ? $acno : $main[$str];
                    $chead = ($type == 1) ? $main[$str] : $acno;
                    $sql = 'INSERT INTO ' . $this->prefix . "voucher (type, date, total, id_head_debit, id_head_credit, memo) VALUES 
                           (10, '$v[date]', $v[tax_amount]/2, $dhead, $chead, '$memo')";
                    $this->m->query($sql);
                } else {
                    echo $str . "- under DUTIES AND TAXES<br>";
                }
                $str = "SGST " . $tp . "%";
                if (!isset($main[$str])) {
                    $sql = "SELECT id_head FROM {$this->prefix}head WHERE name='{$str}' LIMIT 1";
                    $c = $this->m->sql_getall($sql);
                    $main[$str] = isset($c[0]['id_head']) ? $c[0]['id_head'] : "";
                }
                if ($main[$str] != '') {
                    $dhead = ($type == 1) ? $acno : $main[$str];
                    $chead = ($type == 1) ? $main[$str] : $acno;
                    $sql = 'INSERT INTO ' . $this->prefix . "voucher (type, date, total, id_head_debit, id_head_credit, memo) VALUES 
                           (10, '$v[date]', $v[tax_amount]/2, $dhead, $chead, '$memo')";
                    $this->m->query($sql);
                } else {
                    echo $str . "- under DUTIES AND TAXES<br>";
                }
            } else {
                $tp = $v['tax_per'];
                $tp = (intval($tp) == $tp) ? intval($tp) : $tp;
                $str = "IGST " . $tp . "%";
                if (!isset($main[$str])) {
                    $sql = "SELECT id_head FROM {$this->prefix}head WHERE name='{$str}' LIMIT 1";
                    $c = $this->m->sql_getall($sql);
                    $main[$str] = isset($c[0]['id_head']) ? $c[0]['id_head'] : "";
                }
                if ($main[$str] != '') {
                    $dhead = ($type == 1) ? $acno : $main[$str];
                    $chead = ($type == 1) ? $main[$str] : $acno;
                    $sql = 'INSERT INTO ' . $this->prefix . "voucher (type, date, total, id_head_debit, id_head_credit, memo) VALUES 
                           (10, '$v[date]', $v[tax_amount], $dhead, $chead, '$memo')";
                    $this->m->query($sql);
                } else {
                    echo $str . "- under DUTIES AND TAXES<br>";
                }
            }
        }
    }

}
