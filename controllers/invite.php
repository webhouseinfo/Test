<?php

require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';

class Invite extends System_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index(){
        $config = new Config;
        $invite = $config->select("SELECT * FROM    inviteMoney WHERE 	id = 1");
        $this->view->invite = $invite;
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->render('invite_html');
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
    public function updateInvite(){
        if (isset($_POST['updateInvite'])) {
            $config = new Config;
            $money = htmlspecialchars($_POST['money']);

            if(!empty($money)){
                $config->update('inviteMoney',array('money' => $money), "id = 1");

            }
            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/invite');
        }
    }

}
