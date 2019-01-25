<?php
require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';
class AnonimClient extends System_Controller{
    function __construct() {
        parent::__construct();
        if(empty($_SESSION['admin']['id'])){
            $this->redirect('/vilmar_app/VilMarAdmin/login');
        }
    }
    public function index(){
        $config = new Config;
        if(isset($_POST['search'])) {
            $searchText = trim($_POST['searchText']);
            $clients = $config->select("SELECT * FROM  cards WHERE    cardNumber = '$searchText' AND amountDeals > '0' AND  acceptUserId = ''  AND  used <> '0'  ORDER BY id DESC ");
            $this->view->clients = $clients;/*return selection cards search result*/
            $this->view->searchText = $searchText;/*return search  text */
        }else{
            if(isset($_GET['page'])){
                $number = $_GET['page'];
            }else{
                $number = 1;
            }
            $pagestart = ($number-1)*10;
            $clients = $config->select("SELECT * FROM  cards WHERE   amountDeals > 0 AND   used = 1  AND user_id = 0 ORDER BY id DESC LIMIT $pagestart,10");
            $this->view->clients = $clients;/*return selection cards client*/
            $clientspage = $config->select("SELECT * FROM  cards WHERE   amountDeals > 0   AND  used = 1 AND user_id = 0");
            $this->view->clientspage = $clientspage;/*return selection  agents page*/

        }
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->cards = $clients;
        $this->view->render('anonimClient_html');
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
    public function addMoney()
    {
        $config = new Config();
        if(isset($_POST['addMoney'])){
            $id = $_POST['id'];
            $isset = $_POST['issetMoney'];
            $moneyCount = $_POST['moneyCount'];
            $money = (float)$isset + (floor($moneyCount));
            $config->update('cards',array('unit' => $money), " id ='$id'");
            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/anonimClient');

        }


    }
    public function transactions(){
        $config = new Config;
        if(isset($_POST['agentId'])){
            $id = (int)$_POST['agentId'];
            $transactions = $config->select("SELECT * FROM  transactions  WHERE card_id = '$id'   ORDER  BY id DESC");
            $fetch = $transactions->fetch_all(MYSQLI_ASSOC);
            echo  json_encode($fetch);exit;/*return selection transactions */
        }

    }
    public function selectCardsDiapason(){
        $config = new Config;
        if (isset($_POST['counts'])) {
            $counts = htmlspecialchars($_POST['counts']);
            $id = htmlspecialchars($_POST['idcart']);
            $appagents = $config->select("SELECT cardNumber FROM  cards WHERE  	isCardFree = '0' AND id >='$id' ORDER BY id ASC limit $counts");
            $fetch = $appagents->fetch_all(MYSQLI_ASSOC);
            echo  json_encode($fetch);exit;
        }
    }
}

