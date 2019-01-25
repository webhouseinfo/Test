<?php
require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';
class WithoutacardClient extends System_Controller{
    function __construct() {
        parent::__construct();
        if(empty($_SESSION['admin']['id'])){
            $this->redirect('/vilmar_app/VilMarAdmin/login');
        }
    }
    public function index(){
        $config = new Config;
        if(isset($_POST['search'])) {
            $searchText = trim(htmlspecialchars($_POST['searchText']));
            if(strrpos($searchText ,' ')){
                $text = explode(' ',$searchText);
                $search = "or users.armSurname LIKE '%$text[1]%'";
            }else{
                $text = [];
                $text[] = $searchText;
            }
          
            $clients = $config->select("SELECT users.id,users.appConfirm,users.armName,users.armSurname,users.phone,users.email,users.contractNumber,users.percentAgent,users.logo,users.images,users.armCountry,users.hvhh,users.parentId,users.bankName,users.bankNumber,users.area,users.cashIN,users.rating,users.isApp,users.status,users.invitePercent,users.parentPercent,users.clientPercent,users.	armDescription,users.regDate,user_network_data.user_id,user_network_data.address,user_network_data.password 
              FROM  users LEFT JOIN user_network_data ON users.id = user_network_data.user_id CROSS JOIN cards ON users.id = cards.user_id WHERE  users.userType = 'customer' AND (users.email  LIKE '%$text[0]%'  or users.armCountry  LIKE '%$text[0]%' or users.armName LIKE '%$text[0]%' or users.armSurname LIKE '%$text[0]%' $search) ORDER BY users.id DESC ");
            $this->view->clients = $clients;
            /*return selection clients */
            $this->view->searchText = $searchText;
            /*return search  text */
        }else{
            if(isset($_GET['page'])){
                $number = $_GET['page'];
            }else{
                $number = 1;
            }
            $pagestart = ($number-1)*10;
            $clients = $config->select("SELECT users.id,users.appConfirm,users.armName,users.armSurname,users.phone,users.email,users.contractNumber,users.percentAgent,users.logo,users.images,users.armCountry,users.hvhh,users.parentId,users.bankName,users.bankNumber,users.area,users.cashIN,users.rating,users.isApp,users.status,users.invitePercent,users.parentPercent,users.clientPercent,users.	armDescription,users.regDate,user_network_data.user_id,user_network_data.address,user_network_data.password 
              FROM  users LEFT JOIN user_network_data ON users.id = user_network_data.user_id CROSS JOIN cards ON users.id = cards.user_id WHERE   users.isApp = '1' AND users.userType = 'customer' ORDER BY users.id DESC limit $pagestart,10");
            $cardsClients = array();
            $commentsClient = array();
            $transactionClient = array();
            $inviteClient = array();
            $inviteeClient = array();
            foreach ($clients as $client){
                $cardsSelect = $config->select("SELECT * FROM  cards  WHERE  user_id = '".$client['id']."' ");
                $parentSelect = $config->select("SELECT armName,armSurname FROM  users  WHERE  id = '".(int)$client['parentId']."' and userType = 'customer' and status = 'confirmed' ");
                $clientSelects = $config->select("SELECT users.armName,comments.id,comments.customer_id,comments.partner_id,comments.stars,comments.comment,comments.transaction_id,comments.date FROM  comments  LEFT JOIN users ON users.id = comments.partner_id  WHERE  comments.customer_id = '".$client['id']."' ");
                $cardsClients[$client['id']] = json_encode(mysqli_fetch_array($cardsSelect,true));
                $inviteSelects= $config->select("SELECT * FROM  users   LEFT JOIN inviteTable ON users.parentId = inviteTable.inviter WHERE   inviteTable.inviter = '".$client['id']."' ");
                $comm = [];
                $invite = [];
                foreach ($clientSelects as $clientSelect){
                    $comm[] = $clientSelect;
                    if(!empty($clientSelect['transaction_id'])){
                        $trans = $config->select("SELECT * FROM  transactions  WHERE  id = '".$clientSelect['transaction_id']."' ");

                        $transactionClient[$client['id']] = json_encode(mysqli_fetch_array($trans),true);
                    }else{
                        $trans = null;
                        $transactionClient[$client['id']] = json_encode($trans,true);
                    }
                }
                foreach ($inviteSelects as $inviteSelect){
                    $invite[] = $inviteSelect;
                }
                $commentsClient[$client['id']] = json_encode($comm,true);
                $inviteClient[$client['id']] = json_encode($invite,true);
                $inviteeClient[$client['id']] = json_encode(mysqli_fetch_array($parentSelect),true);
            }
            $this->view->cards = $cardsClients;
            $this->view->comments = $commentsClient;
            $this->view->transComents = $transactionClient;
            $this->view->inviteClient = $inviteClient;
            $this->view->inviteeClient = $inviteeClient;

            $clientspage = $config->select("SELECT users.id FROM  users LEFT JOIN user_network_data ON users.id = user_network_data.user_id WHERE  users.status = 'confirmed' AND users.isApp = '1' AND users.userType = 'customer' ");
            $this->view->clientspage = $clientspage;
            /*return selection  agents page*/
            $this->view->clients = $clients;
            /*return selection agents app*/
        }
        $countrys = $config->select("SELECT armCountry,id FROM   countryName  ");
        $this->view->countrys = $countrys;
        /*return all countrys*/
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->render('withoutacardClient_html');
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
    public function confirmUserApp(){
        if (isset($_POST['updateUppUser'])) {
            $config = new Config;
            $name = htmlspecialchars($_POST['firstname']);
            $surname = htmlspecialchars($_POST['lastname']);
            $birthday = htmlspecialchars($_POST['birthday']);
            $address = htmlspecialchars($_POST['address']);
            $id = (int)$_POST['idclient'];
            $email = htmlspecialchars(strtolower($_POST['email']));
            $phone = htmlspecialchars($_POST['phone']);
            $countryId = htmlspecialchars($_POST['country']);
            $country = $config->mysqli->query("SELECT * FROM countryName WHERE id = '$countryId' ");
            $country_array = mysqli_fetch_array($country);
            $armcountry= $country_array['armCountry'];
            $ruscountry= $country_array['rusCountry'];
            $engcountry= $country_array['engCountry'];
            $units =  htmlspecialchars($_POST['old_unit'])+htmlspecialchars($_POST['units']);
            if( !empty($email) and !empty($phone) and !empty($armcountry) and !empty($ruscountry) and !empty($engcountry) and !empty($name) and !empty($surname) ){
                $upd = $config->update('users',
                    array(
                        'armName' => $name,
                        'rusName' => $name,
                        'engName' => $name,
                        'armSurname' => $surname,
                        'rusSurname' => $surname,
                        'engSurname' => $surname,
                        'phone' => $phone,
                        'email' => $email,
                        'armCountry'=>$armcountry,
                        'rusCountry'=>$ruscountry,
                        'engCountry'=>$engcountry,
                        'status' => 'confirmed',
                    ),
                    "id = $id");
                if ($upd) {
                    $config->update('user_network_data', array('address' => $address,  ),"user_id = '$id'");
                    $config->update('cards', array('unit' => $units,  ),"user_id = '$id'");
                }
            }

            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/withoutacardClient');
        }
        if(isset($_POST['ignoreapp'])){
            $config = new Config;
            $id = htmlspecialchars((int)$_POST['idclient']);
            if(!empty($id)){
                $config->delete($id, 'user_network_data', 'user_id');
                $config->delete($id, 'users', 'id');

            }
            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/withoutacardClient');
        }
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
    public function transactions(){
        $config = new Config;
        if(isset($_POST['agentId'])){
            $id = (int)$_POST['agentId'];
            $transactions = $config->select("SELECT * FROM  transactions  WHERE user_id = '$id'  ORDER  BY id DESC");
            $fetch = $transactions->fetch_all(MYSQLI_ASSOC);
            echo  json_encode($fetch);exit;
            /*return selection transactions */
        }
    }
}