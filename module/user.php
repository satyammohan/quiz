<?php

class user extends common {

    function __construct() {
        parent:: __construct();
        if (isset($_REQUEST['user'])) {
            $this->data = $_REQUEST['user'];
        }
    }

    function _default() {
        $this->sm->assign("page", "user/home.tpl.html");
    }
    function login() {

    }
    function setsess() {
        $_SESSION['id_user'] = 1;
        $_SESSION['is_admin'] = 1;
        $this->redirect("index.php");
    }
    function checkinfo() {
        if (isset($_SESSION['id_info']) && isset($_SESSION['prefix'])) {
            $this->sm->assign("page", "info/welcome.tpl.html");
        } else {
            $sql = "SELECT DISTINCT name FROM info WHERE showtoparty=1 ORDER BY name";
            $info = $this->m->getall($this->m->query($sql));
            $this->sm->assign("info", $info);
            $this->sm->assign("page", "user/activate.tpl.html");
        }
    }
    function setlogin() {
        $sql = "SELECT * FROM user WHERE user='".$_REQUEST['user']['uname']."' AND pass=md5('".$_REQUEST['user']['pass']."')";
        $user = $this->m->fetch_assoc($sql);
        if ($user) {
            $this->set_session($user);
            $_SESSION['msg'] = "Successfully Logged in.";
            $this->redirect("index.php");
        } else {
            $_SESSION['msg'] = "Invalid Username or Password.";
            $this->redirect("index.php?module=user&func=login");
        }
    }    
    function changepass() {
        $this->sm->assign("page", "user/changepass.tpl.html");
    }
    function updatepass() {
        $old = $_REQUEST['user']['pass'];
        $new = $_REQUEST['user']['npass'];
        $sql = "SELECT * FROM user WHERE id_user='" . $_SESSION["id_user"] . "'AND pass='" . md5($old) . "'";
        $d = $this->m->fetch_assoc($sql);
        if (!$d) {
            $_SESSION['msg'] = "Old Password does not match.";
            $this->redirect("index.php?module=user&func=changepass");
        }
        $sql = "UPDATE user SET pass='".md5($new)."', password_date=NOW() WHERE id_user='" . $_SESSION["id_user"] . "'AND pass='" . md5($old) . "'";
        $this->m->query($sql);
        $this->redirect("index.php?module=user&func=logout");
    }
    function submit_register() {
        $st = md5(rand());
        $sql = $this->create_insert("user", array("id_user" => " ", "user" => $this->data['uname'], "pass" => md5($this->data['cpass']), "name" => $this->data['name'], "email" => $this->data['email'], "status" => $st, "login_status" => '0'));
        $this->m->query($sql);
        $this->sm->assign("activation", "index.php?module=user&func=activate&user[name]=" . $this->data['uname'] . "&user[id]=" . $st);
        mail($this->data['email'], "Account Activation", "<a href='index.php?module=user&func=activate&user[name]={$this->data['uname']}&user[id]={$st}'>Activation LINK</a>");
        $this->sm->assign("name", $this->data['uname']);
        $this->sm->assign("page", "user/welcome.tpl.html");
    }

    function activate() {
        $sql = "update `user` SET status='1' where user='" . $this->data["name"] . "' and status='" . $this->data["id"] . "'";
        $this->m->query($sql);
        $this->sm->assign("name", $this->data['name']);
        $this->sm->assign("page", "user/activate.tpl.html");
    }

    function showforgot() {
        $this->sm->assign("page", "user/forgot.tpl.html");
    }

    function forgot() {
        $uname = $this->data['uname'];
        //$sql = $this->create_select("user", "user='{$uname}' OR email='{$uname}'");
        $sql = $this->create_select("user", "user='{$uname}'");
        $data = $this->m->fetch_assoc($sql);
        if ($data) {
            $link = "index.php?module=user&func=recovery&user[name]=" . $this->data['uname'];
            mail("satyammohan@gmail.com", "Password Recovery", $link);
            $this->sm->assign("passlink", $link);
            $this->sm->assign("page", "user/recovery.tpl.html");
        } else {
            $this->sm->assign("page", "user/login.tpl.html");
        }
    }

    function recovery() {
        $sql = "update `user` SET pass='p455w0rd' where user='" . $this->data["name"] . "';";
        $this->m->query($sql);
        $_SESSION["uname"] = $this->data["name"];
        $this->sm->assign("page", "user/resetpass.tpl.html");
    }

    function resetpass() {
        $sql = "update `user` SET pass='" . md5($this->data["cpass"]) . "' where user='" . $_SESSION["uname"] . "';";
        $this->m->query($sql);
        unset($_SESSION["uname"]);
        $this->sm->assign("page", "user/confreset.tpl.html");
    }

    function showprofile() {
        $sql = $this->create_select("user", "id_user='" . $_SESSION['id_user'] . "'");
        $profile = $this->m->fetch_assoc($sql);
        $this->sm->assign("profile", $profile);
        $this->sm->assign("page", "user/showprofile.tpl.html");
    }

    function editprofile() {
        $sql = $this->create_select("user", "id_user='" . $_SESSION['id_user'] . "'");
        $profile = $this->m->fetch_assoc($sql);
        $this->sm->assign("profile", $profile);
        $this->sm->assign("page", "user/editprofile.tpl.html");
    }

    function updateprofile() {
        $sql = "update `user` SET name='" . $this->data['name'] . "',email='" . $this->data['email'] . "' where id_user='" . $_SESSION["id_user"] . "';";
        $this->m->query($sql);
        $this->sm->assign("page", "user/welcome.tpl.html");
    }

    function logout() {
        $this->destroy_session();
        $_SESSION['msg'] = "Successfully logout.";
        $this->redirect("index.php");
    }

    function destroy_session() {
        session_destroy();
        foreach ($_SESSION as $k => $v) {
            $_SESSION[$k] = '';
            unset($_SESSION[$k]);
        }
        session_start();
    }

    function set_session($arr) {
        foreach ($arr as $k => $v) {
            $_SESSION[$k] = $v;
        }
    }

    function checkuser() {
        $sql = "select * from user where user='" . $this->data['uname'] . "';";
        $res = mysql_query($sql);
        if ($data = mysql_fetch_array($res)) {
            echo "error";
        } else {
            echo "success";
        }
    }

    function insert() {
        $data = $_REQUEST['user'];
        $data['pass'] = md5($data['pass']);
        $data['id_create'] = $_SESSION['id_user'];
        $data['id_modify'] = $_SESSION['id_user'];
        $data['create_date'] = date("Y-m-d h:i:s");
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        $res = $this->m->query($this->create_insert("user", $data));
        $_SESSION['msg'] = "Record Successfully Inserted";
        $this->redirect("index.php?module=user&func=listing");
    }

    function edit() {
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "0";
        $sql = "SELECT * FROM user WHERE id_user=$id ";
        $this->sm->assign("data", $this->m->fetch_assoc($sql));
        $this->sm->assign("page", "user/add.tpl.html");
    }

    function update() {
        $data = $_REQUEST['user'];
        $data['id_modify'] = $_SESSION['id_user'];
        $data['modify_date'] = date("Y-m-d h:i:s");
        $data['pass'] = md5($data['pass']);
        $sql = $this->create_update("user", $data, "id_user='{$_REQUEST['id']}'");
        $res = $this->m->query($sql);
        $_SESSION['msg'] = "Record Successfully Updated";
        $this->redirect("index.php?module=user&func=listing");
    }

    function delete() {
        $res = $this->m->query($this->create_delete("user", "id_user='{$_REQUEST['id']}'"));
        $_SESSION['msg'] = "Record Successfully Deleted";
        $this->redirect("index.php?module=user&func=listing");
    }

    function check_user() {
        $data = $_REQUEST['user'];
        $user = trim($data['user']);
        $sql = $this->create_select("user", "user='$user' AND id_user!='{$_REQUEST['id']}'");
        $data = $this->m->fetch_assoc($sql);
        echo json_encode($data ? false : true);
        exit;
    }

    function listing() {
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] != 1) {
            $_SESSION['msg'] = "Your are not Authorised to set Permissions.";
            $this->redirect("index.php");
        }
        if (isset($_REQUEST['status'])) {
            ($_REQUEST['status'] == 2) ? $wcond = "" : $wcond = " WHERE status = " . $_REQUEST['status'];
        } else {
            $_REQUEST['status'] = 2;
            $wcond = "";
        }
        $sql = "SELECT * FROM user $wcond ORDER BY is_admin";
        $this->sm->assign("user", $this->m->getall($this->m->query($sql)));
    }

}

?>
