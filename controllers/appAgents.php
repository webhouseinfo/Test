<?php
require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';
class AppAgents extends System_Controller{
    function __construct() {
        parent::__construct();
        if(empty($_SESSION['admin']['id'])){
            $this->redirect('/cft_app/CFTcard/login');
        }
    }
    public function index(){
        $config = new Config;
             
        $appagents = $config->select("SELECT * FROM  agents WHERE appConfirm ='0' AND isFromApp = '1' AND userType = 'agent' AND deleted = '0'  ORDER BY id DESC");
        $this->view->appagents = $appagents;/*return selection agents app*/
        $cardfree = $config->select("SELECT id FROM  cards WHERE  giveUserId = '' AND acceptUserId = '0' AND used = '0' ORDER BY id ASC limit 1");
        $this->view->cardfree = $cardfree;/*return selection first card free*/
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->render('appAgents_html');
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
            $contractNumber = htmlspecialchars($_POST['contractNumber']);
            $email = htmlspecialchars($_POST['email']);
            $countCards = htmlspecialchars($_POST['countCards']);
            $start = htmlspecialchars($_POST['start']);
            $second = htmlspecialchars($_POST['second']);
            $id = htmlspecialchars((int)$_POST['idagent']);
            $startcard = htmlspecialchars($_POST['startcard']);
            $end = $startcard+$countCards-1;
            if(!empty($contractNumber)  && !empty($start) && !empty($second)){
                $ins =  $config->update('agents',array('contractNumber' => $contractNumber, 'cardCounts' => $countCards, 'cardDiapason' => $start . '*' . $second,'appConfirm'=>'1','status' => 'confirmed'), "id = '$id'");
                
                if ($ins) {
                    require_once './lib/MailSender.php';
                    if (MailSender::send($email, 'CFT administration', "Դուք հաջողությամբ հաստատվեցիք CFT ադմինիստրացիայի կողմից: ")) {
                    }
                }
                $config->update('cards',array( 'giveUserId' => $id,'isApp'=>'1' ), "id >='$startcard' AND id <='$end'");
            }
            $this->redirect('https://cft.am/cft_app/CFTcard/appAgents');
        }
        if(isset($_POST['ignoreapp'])){
            $id = htmlspecialchars((int)$_POST['idagent']);
            if(!empty($id)){
                $config->update('agents',array('deleted'=>'1'), "id = '$id'");

            }
            $this->redirect('https://cft.am/cft_app/CFTcard/appAgents');
        }
    }
    public function selectCardsDiapason(){
        $config = new Config;
        if (isset($_POST['counts'])) {
            $counts = htmlspecialchars($_POST['counts']);
            $id = htmlspecialchars($_POST['idcart']);
            $appagents = $config->select("SELECT cardNumber FROM  cards WHERE  		giveUserId = '' AND acceptUserId = '0'  AND id >='$id' ORDER BY id ASC limit $counts");
            $fetch = $appagents->fetch_all(MYSQLI_ASSOC);
            echo json_encode($fetch);exit;
        }
    }
}
$app = new AppAgents;
$app->selectCardsDiapason();