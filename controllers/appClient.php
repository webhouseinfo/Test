<?php
require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';
class AppClient extends System_Controller{
    function __construct() {
        parent::__construct();
        if(empty($_SESSION['admin']['id'])){
            $this->redirect('/vilmar_app/VilMarAdmin/login');
        }
    }
    public function index(){
        $config = new Config;

        if(isset($_GET['page'])){
            $number = $_GET['page'];
        }else{
            $number = 1;
        }
        $pagestart = ($number-1)*10;
        $clients = $config->select("SELECT users.id,users.appConfirm,users.armName,users.armSurname,users.phone,users.email,users.contractNumber,users.percentAgent,users.logo,users.images,users.armCountry,users.hvhh,users.parentId,users.bankName,users.bankNumber,users.area,users.cashIN,users.rating,users.isApp,users.status,users.invitePercent,users.parentPercent,users.clientPercent,users.	armDescription,users.regDate,user_network_data.user_id,user_network_data.address,user_network_data.password 
              FROM  users LEFT JOIN user_network_data ON users.id = user_network_data.user_id   LEFT JOIN cards ON users.id = cards.user_id WHERE  users.status = 'unconfirmed' AND users.isApp = '1' AND users.userType = 'customer' AND cards.user_id   is null ORDER BY users.id DESC limit $pagestart,10");

        $clientspage = $config->select("SELECT users.id FROM  users LEFT JOIN user_network_data ON users.id = user_network_data.user_id WHERE  users.status = 'unconfirmed' AND users.isApp = '1' AND users.userType = 'customer'");
        $this->view->clientspage = $clientspage;/*return selection  customers page*/
        $this->view->clients = $clients;/*return selection customers app*/

        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;

        $this->view->render('appClient_html');
    }
    public function ignore(){
        if(isset($_POST['ignoreapp'])){
            $config = new Config;
            $id = htmlspecialchars((int)$_POST['idclient']);
            if(!empty($id)){
                $config->delete($id, 'users', 'id');
                $config->delete($id, 'user_network_data', 'user_id');
            }
            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/appClient');
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


    public function viewNewUser(){
        if(isset($_POST['newUser'])){
            $config = new Config;
            $id = $_POST['newUser'];
            $upd = $config->update('users',array( 'appConfirm' => 1 ), "id ='$id'");
            if($upd){
                echo 'confirm';
            }
        }
    }

}

