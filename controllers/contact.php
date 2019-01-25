<?php

require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';

class Contact extends System_Controller
{
    public function __construct()
    {
        parent::__construct();
        if(empty($_SESSION['admin']['id'])){

            $this->redirect('/vilmar_app/VilMarAdmin/login');
        }
    }

    public function index()
    {
        $config = new Config();
        $apymentterms = $config->select("SELECT * FROM   contacts WHERE id = 1");
        $this->view->apymentterms = $apymentterms;
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->render('contact_html');
    }
    public function updatecontacts(){
        if (isset($_POST['updatecontacts'])) {
            $config = new Config;
            $email = htmlspecialchars($_POST['email']);
            $phone = htmlspecialchars($_POST['phone']);
            $armaddr = htmlspecialchars($_POST['armaddr']);
            $rusaddr = htmlspecialchars($_POST['rusaddr']);
            $engaddr = htmlspecialchars($_POST['engaddr']);
            if(!empty($email) && !empty($phone)){
                $config->update('contacts',array('email' => $email, 'phone' => $phone, 'armaddr' => $armaddr,'rusaddr'=>$rusaddr,'engaddr'=>$engaddr), "id = 1");

            }
            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/contact');
        }
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
