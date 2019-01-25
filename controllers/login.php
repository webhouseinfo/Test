<?php
require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';
class Login extends System_Controller{
    function __construct() {
        parent::__construct();
        if(!empty($_SESSION['admin']['id'])){
            header("Location:https:/vilmar_app/VilMarAdmin/home");
        }
    }
    public function index(){

        if(isset($_POST['loginSubmit'])){
            $config = new Config;

            $user = htmlspecialchars($_POST['adminUserName']);
            $pass = htmlspecialchars($_POST['adminPassword']);

            $admin = $config->select("SELECT *  FROM  admins   WHERE    email = '$user'  AND password = '$pass'");

            if ($admin){
                $data = mysqli_fetch_array($admin);
                $_SESSION['admin']['id'] = $data['id'];
                $this->redirect('/vilmar_app/VilMarAdmin/home');
            }
        }
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->render('login_html');
    }
    public function adminsSettings()
    {
        $config = new Config();
        $id = $_SESSION['admin']['id'];
        $row = $config->select("SELECT 	settings FROM   admins WHERE id = '$id'");
        $res = mysqli_fetch_array($row) ;
        $admins = explode('|||',$res['settings']);
        return $admins;

    }
}

