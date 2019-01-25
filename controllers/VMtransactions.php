<?php
require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';
class VMtransactions extends System_Controller{
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
        $pagestart = ($number-1)*20;
        $transactions = $config->select("SELECT * FROM transactions  ORDER  BY id DESC LIMIT $pagestart,100 ");
        $this->view->transactions = $transactions;
        
        /*return all transactions*/
        $arrTransactions = [];
        foreach ($transactions as $res){
            if(!in_array($res['agent_id'],$arrTransactions) and !empty($res['agent_id']) ){
                $arrTransactions[] = $res['agent_id'];
            }
            if(!in_array($res['partner_id'],$arrTransactions) and $res['type'] == 'out' and !empty($res['partner_id']) ){
                $arrTransactions[] = $res['partner_id'];
            }
        }
        $arrids = join(',',$arrTransactions);
        $transactionusers = $config->select("SELECT id,armName,userType FROM  users WHERE id IN ($arrids)");
        $arrayUsers = array();
        if(mysqli_num_rows($transactions) >0){
            foreach ($transactionusers as $transactionuser){
                $arrayUsers[] = [$transactionuser['id']=> $transactionuser['armName']];
            }
        }
        $transactionsPage = $config->select("SELECT id FROM  transactions  ");
        $this->view->transactionsPage = $transactionsPage;
        /*return  transactions pages*/
        $this->view->arrayUsers = $arrayUsers;
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->render('VMtransactions_html');
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
    public function transactions(){
        if(isset($_POST['agentId'])){
            $config = new Config;
            date_default_timezone_set('Asia/Yerevan');
            $date = date("Y-m-d H:i:s");
            $paid = htmlspecialchars($_POST['paid']);
            $date1 = date('Y-m-d H:i:s',strtotime($_POST['date1']));
            $dateitems = explode(' ',$date);
            $nedate = $_POST['date2'].' '.$dateitems[1];
            $date2 = date('Y-m-d H:i:s',strtotime($nedate));
            $type = $_POST['el'];
            if($date2 == '1970-01-01 00:00:00'){
                $date2 = $date;
            }
            $searchData = htmlspecialchars(trim($_POST['searchData']));
            $arrAgents = [];
            if(!empty($searchData)){
                $results = $config->select("SELECT id FROM  users  WHERE  contractNumber = '$searchData' OR armName LIKE '%$searchData%' AND deleted = '0'");
            }else{
                $results = $config->select("SELECT id FROM  users  WHERE  userType = 'agent' AND status =  'confirmed' AND deleted = '0' OR userType = 'partner'  AND status =  'confirmed'  AND deleted = '0'");
            }
            foreach ($results as $result){
                if(!in_array($result['id'],$arrAgents)){
                    $arrAgents[] = $result['id'];
                }
            }
            $arrAgents = join(',',$arrAgents);
             if($paid == 2){
                $type = " type = 'in' ";
            }else if($paid == 3){
                $type = " type = 'out' ";
            }else if(empty($type)){
                $type = "(type = 'in' or type = 'out')";
            }else{
                $type = "type = '$type'";
            }
            if($paid == 0){
                $transactions = $config->select("SELECT * FROM  transactions   WHERE  paid_agent = '1' AND  date BETWEEN '$date1' AND '$date2' AND $type  AND agent_id IN ($arrAgents)   ORDER BY id DESC");
            }else if($paid == 1){
                $transactions = $config->select("SELECT * FROM  transactions  WHERE  paid_partner = '1' AND    date BETWEEN '$date1' AND '$date2'    AND $type  AND partner_id IN ($arrAgents)   ORDER  BY id DESC");
            }else if($paid == 2){
                $transactions = $config->select("SELECT * FROM  transactions  WHERE  paid_agent = '0' AND  date BETWEEN '$date1' AND '$date2' AND $type  AND agent_id IN ($arrAgents)  ORDER  BY id DESC");
            }else if($paid == 3){
                $transactions = $config->select("SELECT * FROM  transactions  WHERE  paid_partner = '0' AND    date BETWEEN '$date1' AND '$date2'  AND $type  AND partner_id IN ($arrAgents)   ORDER  BY id DESC");
            }else if($paid == 4){
                $transactions = $config->select("SELECT * FROM  transactions  WHERE  paid_admin = 0 AND  date BETWEEN '$date1' AND '$date2' AND type = 'in'  AND partner_id IN ($arrAgents) ORDER  BY id DESC");
            }else if($paid == 5){
                $transactions = $config->select("SELECT * FROM  transactions  WHERE   date BETWEEN '$date1' AND '$date2' AND $type  AND (agent_id IN ($arrAgents) or partner_id IN ($arrAgents))  ORDER  BY id DESC");
            }
            $fetch = $transactions->fetch_all(MYSQLI_ASSOC);
            $arrTransactions = [];
            $arrayUsers = [];
            if(mysqli_num_rows($transactions)>0){
                foreach ($transactions as $res){
                    if(!in_array($res['agent_id'],$arrTransactions) and !empty($res['agent_id']) ){
                        $arrTransactions[] = $res['agent_id'];
                    }
                    if(!in_array($res['partner_id'],$arrTransactions)  and !empty($res['partner_id'])){
                        $arrTransactions[] = $res['partner_id'];
                    }
                }
                $arrids = join(',',$arrTransactions);
                $transactionusers = $config->select("SELECT id,armName,userType FROM  users WHERE id IN ($arrids) ");
                $j = 0;
                foreach ($transactionusers as $transactionuser){
                    $arrayUsers[$j]['id'] =  $transactionuser['id'];
                    $arrayUsers[$j]['armName'] =  $transactionuser['armName'];
                    $j++;
                }
            }
            echo  json_encode($fetch).'(****)'.json_encode($arrayUsers);exit;
            /*ajax response selection transactions */
        }
    }
}

