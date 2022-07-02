<?php

class database {

    var $hostName;   //stores hostname or the server name
    var $userName;   //username for the database
    var $password;   //password for the database
    var $dbName;   //database name
    var $conn;    //stores connection id
    var $rs;    //stores the resource ID for the sql query
    var $record;   //stores properties of the record corresponding to that column
    var $latestID;   //returns the id of the latest inserted record

    function __construct() {
        if (file_exists("config/db.ini")) {
            $ini = parse_ini_file("config/db.ini");
        } else {
            $dr = $_SERVER['DOCUMENT_ROOT'];
            $ini = parse_ini_file("$dr/source/config/db.ini");
        }
        $this->hostName = $ini['hostName'];
        $this->userName = $ini['userName'];
        $this->password = $ini['password'];
        $this->dbName = $ini['dbName'];
        $this->conn = mysqli_connect($this->hostName, $this->userName, $this->password, $this->dbName) or die("Connection to the server failed");
        $sql = "SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));";
        $this->query($sql);
    }

    function query($sql) {
        $this->rs = $rs = mysqli_query($this->conn, $sql);
        return $rs;
    }
    function num_rows($rs) {
        if (isset($rs)) {
            $this->rs = $rs;
        }
        return mysqli_num_rows($this->rs);
    }
    function fetch_array($rs) {
        if (isset($rs)) {
            $this->rs = $rs;
        }
        return mysqli_fetch_array($this->rs);
    }
    function fetch_assoc($sql) {
        $r = $this->query($sql);
        return mysqli_fetch_assoc($r);
    }
    function movenext($rs) {
        if (isset($rs)) {
            $this->rs = $rs;
        }
        $this->record = mysqli_fetch_object($this->rs);
        return $this->record;
    }
    function movenexta($rs) {
        if (isset($rs)) {
            $this->rs = $rs;
        }
        $this->record = mysqli_fetch_assoc($this->rs);
        return $this->record;
    }    
    function getfield($field) {
        return $this->record->$field;
    }
    function getinsertID() {
        $this->latestID = mysqli_insert_id($this->conn);
        return $this->latestID;
    }

    function getall($rs, $single = 0, $fld = "", $key = "", $second = "") {
        $this->record = array();
        while ($row = mysqli_fetch_assoc($this->rs)) {
            if ($single == 0) {
                $this->record[] = $row;
            } else {
                if ($key == "") {
                    $this->record[] = $row[$fld];
                } else {
                    if ($second == "")
                        $this->record[$row[$key]] = $row;
                    else
                        $this->record[$row[$key]][$row[$second]] = $row;
                }
            }
            if ($single == 2) {
                if ($key == "") {
                    $this->record[] = $row[$fld];
                } else {
                    $this->record[$row[$key]] = $row[$fld];
                }
            }
        }
        return $this->record;
    }
    function sql_getall($sql, $single = 0, $fld = "", $key = "", $second = "") {
        $res = $this->query($sql);
        return $this->getall($res, $single, $fld, $key, $second);
    }
    function all_Tables($prefix) {
        $sql = "SELECT table_name, table_rows FROM INFORMATION_SCHEMA.TABLES WHERE table_type!='VIEW' AND TABLE_SCHEMA = '".$this->dbName."' AND table_name LIKE '{$prefix}%'";
        $res = $this->query($sql);
        return $this->getall($res, 2, "table_rows", "table_name");
    }
    function close() {
        return mysqli_close($this->conn);
    }
    function mysql_pconnect($h, $u, $p) {
        $c = mysqli_connect($h, $u, $p) or die("Connection to the server failed");
        return $c;
    }
    function mysql_select_db($d) {
        return mysqli_select_db($this->conn, $d)  or die("No such database exist");
    }
    function mysql_fetch_assoc($res) {
        return mysqli_fetch_assoc($this->$conn, $res);
    }
}
?>