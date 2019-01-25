<?php
require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';
class PartnersDatabase extends System_Controller{
    function __construct() {
        parent::__construct();
        if(empty($_SESSION['admin']['id'])){
            $this->redirect('/vilmar_app/VilMarAdmin/login');
        }
    }
    public function index(){
        $config = new Config;
        $countrys = $config->select("SELECT armCountry,id FROM   countryName  ");
        $this->view->countrys = $countrys;/*return all countrys*/
        if(isset($_POST['filter'])){
            $f = (int)$_POST['f'];
            $partnersfilter = $config->select("SELECT partner_id FROM    transactions WHERE paid_admin = '$f' and type  = 'in'  ");
            $arrFilter = [];
            foreach ($partnersfilter as $res){
                if(!in_array($res['partner_id'],$arrFilter)){
                    $arrFilter[] = $res['partner_id'];
                }
            }
            $filter = '';
            if(!empty($arrFilter)){
                $arrFilter = join(',',$arrFilter);
                $filter .= " AND users.id IN ($arrFilter)";
            }else{
                $filter .= " AND users.id  < 1 ";
            }
            $partners = $config->select("SELECT users.*,user_network_data.user_id,user_network_data.address,user_network_data.password ,user_network_data.lat,user_network_data.lng 
              FROM  users LEFT JOIN user_network_data ON users.id = user_network_data.user_id WHERE  users.status = 'confirmed'  AND users.userType = 'partner' $filter  AND deleted = '0' ORDER BY users.id DESC ");
            $this->view->partners = $partners;
            /*return selection partners*/
            
            $this->view->partnerss = $partners->fetch_all(MYSQLI_ASSOC);
            /*return selection partners*/
            
        }elseif(isset($_POST['search'])) {
            $searchText = trim($_POST['searchText']);
            $search = '';
           // $search1 = '';
            $areas = $_POST['areaSearch'];
            if(!empty($areas) and !empty($searchText)){
                $search.= ' AND area IN ("'.$areas.'") ';
                $search.= " AND  (users.email  LIKE '%$searchText%'  or users.contractNumber  = '".(int)$searchText."' or users.armName LIKE '%$searchText%')";
            }else if(!empty($areas)  and empty($searchText)){
                $search.= ' AND area IN ('.$areas.') ';
            }else if(empty($areas)  and !empty($searchText)){
                $search.= " AND  (users.email  LIKE '%$searchText%'  OR users.contractNumber  = '".(int)$searchText."' OR users.armName LIKE '%$searchText%')";
            }
            $partners = $config->select("SELECT users.*,user_network_data.user_id,user_network_data.address,user_network_data.password ,user_network_data.lat,user_network_data.lng 
                               FROM  users LEFT JOIN user_network_data ON users.id = user_network_data.user_id WHERE  users.status = 'confirmed'  AND users.userType = 'partner' $search  AND deleted = '0'   ORDER BY users.id DESC");
            $this->view->partners = $partners;
            /*return search  agents */
            
            $this->view->searchText = $searchText;
            /*return search  text */
            
            $this->view->searchArea = $areas;
            /*return search  text */
            
        }else{
            if(isset($_GET['page'])){
                $number = $_GET['page'];
            }else{
                $number = 1;
            }
            $pagestart = ($number-1)*10;
            $partners = $config->select("SELECT users.*,user_network_data.user_id,user_network_data.address,user_network_data.password ,user_network_data.lat,user_network_data.lng 
              FROM  users LEFT JOIN user_network_data ON users.id = user_network_data.user_id WHERE  users.status = 'confirmed'  AND users.userType = 'partner'  AND users.deleted = '0' ORDER BY users.id DESC limit $pagestart,10");
            $this->view->partners = $partners;
            /*return selection partners*/
            
            $this->view->partnerss = $partners->fetch_all(MYSQLI_ASSOC);
            /*return selection partners*/
            
            $partnerspage = $config->select("SELECT id FROM   users WHERE status = 'confirmed'  AND deleted = '0'  AND userType = 'partner'");
            $this->view->partnerspage = $partnerspage;
            /*return selection  agents page*/
        }
        $ratings = array();
        $commentsPartner = array();
        $transactionPartner = array();
        foreach ($partners as $partner){
            $partnerSelects = $config->select("SELECT users.armName,comments.id,comments.customer_id,comments.partner_id,comments.stars,comments.comment,comments.transaction_id,comments.date FROM  comments  LEFT JOIN users ON users.id = comments.customer_id  WHERE  comments.partner_id = '".$partner['id']."' ");
            $r  = [];
            $comm = [];
            foreach ($partnerSelects as $partnerSelect){
                $comm[] = $partnerSelect;
                if(!empty($partnerSelect['stars'])){
                    $r[] = $partnerSelect['stars'];
                }
                if(!empty($partnerSelect['transaction_id'])){
                    $trans = $config->select("SELECT users.armName,users.armSurname,transactions.* FROM  transactions LEFT JOIN users ON users.id = transactions.user_id WHERE  transactions.id = '".$partnerSelect['transaction_id']."' ");
                    $transactionPartner[$partner['id']] = json_encode(mysqli_fetch_array($trans),true);
                }else{
                    $trans = null;
                    $transactionPartner[$partner['id']] = json_encode($trans,true);
                }
            }
            if(empty($r)){
                $r[] = 0;
            }
            $ratings[$partner['id']] = json_encode($r,true);
            $commentsPartner[$partner['id']] = json_encode($comm,true);
        }
        $this->view->rating = $ratings;
        $this->view->transComents = $transactionPartner;
        $this->view->comments = $commentsPartner;
        $partnerssearch = $config->select("SELECT id,armName,armSurname FROM   users  WHERE status = 'confirmed'   AND userType = 'partner'  ");
        $this->view->partnerssearch = $partnerssearch;/*return selection  agents page*/

        $areas = $config->select("SELECT id,armName FROM  areas ");
        $this->view->areas = $areas;
        /*return selection areas */
        
        $agents = $config->select("SELECT * FROM  users WHERE 	userType = 'agent' AND  status = 'confirmed'    ");
        $this->view->agents = $agents;
        /*return selection agents*/

        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->render('partnersDatabase_html');
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
        $config = new Config;
        if(isset($_POST['agentId'])){

            $id = (int)htmlspecialchars($_POST['agentId']);

            $paid = (int)htmlspecialchars($_POST['paid']);
            if(!empty($_POST['date1']) and empty($_POST['date2'])){
                $date1 = date('Y-m-d H:i:s',strtotime($_POST['date1'].' 00:00:00'));
                $date = "AND date >= '$date1' ";
            }else if(empty($_POST['date1']) and !empty($_POST['date2'])){
                $date2 = date('Y-m-d H:i:s',strtotime($_POST['date2'].' 23:59:59'));
                $date = " AND date <='$date2' ";
            }else if(!empty($_POST['date1']) and !empty($_POST['date2'])){
                $date1 = date('Y-m-d H:i:s',strtotime($_POST['date1'].' 00:00:00'));
                $date2 = date('Y-m-d H:i:s',strtotime($_POST['date2'].' 23:59:59'));
                $date = " AND date >='$date1' AND date <='$date2' ";
            }else{
                $date = '';
            }
            if($paid == 0){
                $transactions = $config->select("SELECT * FROM  transactions  WHERE partner_id = '$id' AND paid_partner = '0'  AND type = 'out' $date ORDER  BY id DESC");
            }else if($paid == 1){
                $transactions = $config->select("SELECT * FROM  transactions  WHERE partner_id = '$id' AND paid_partner = '1'    AND type = 'out' $date ORDER  BY id DESC");
            }else if($paid == 2 ){
                $transactions = $config->select("SELECT * FROM  transactions  WHERE partner_id = '$id' $date  ORDER  BY id DESC");
            }else if($paid == 3){
                $transactions = $config->select("SELECT * FROM  transactions  WHERE partner_id = '$id' AND paid_admin = '1'   AND type = 'in' $date ORDER  BY id DESC");
            }else if($paid == 4 ){
                $transactions = $config->select("SELECT * FROM  transactions  WHERE partner_id = '$id' AND paid_admin = '0'   AND type = 'in' $date  ORDER  BY id DESC");
            }
            $fetch = $transactions->fetch_all(MYSQLI_ASSOC);
            
            echo  json_encode($fetch);exit;
            /*return selection transactions */
        }
    }

    public function deleteUser(){
        if(isset($_POST['data'])){
            $config = new Config;
            $id = $_POST['data'];
            $config->update('users', array('deleted' => '1'), " id = '$id' ");

            echo 'deleted';
        }
    }
    public function paying()
    {

        if (isset($_POST['In']) or isset($_POST['Out'])) {
            $config = new Config;
            $inIds = explode(',', $_POST['In']);
            for ($i = 0; $i < count($inIds); $i++) {
                $config->update('transactions', array('paid_admin' => 1), " id = '" . $inIds[$i] . "'   ");

            }
            $outIds = explode(',', $_POST['Out']);
            $arrout = [];
            for ($i = 0; $i < count($outIds); $i++) {
                $upd = $config->update('transactions', array('paid_partner' => 1), " id = '" . $outIds[$i] . "' ");
            }

            if ($upd) {
                echo 1111;
            }
        }
    }

        public function confirmUser(){
        if (isset($_POST['updateUppUser'])) {
            $config = new Config;
            $companyName = $_POST['companyName'];
            $logo = '';
            if (!empty($_FILES['logo']['name'])) {
                $temp = explode('.', $_FILES['logo']['name']);
                $extension = end($temp);
                $logo = md5(mt_rand(0, 1000000)).'.'.$extension;
                move_uploaded_file($_FILES['logo']['tmp_name'], '../uploads/'.$logo);
            }
            $images = [];
            for ($i = 0;$i<7;$i++){
                if (!empty($_FILES['image']['name'][$i])) {
                    $temp = explode('.', $_FILES['image']['name'][$i]);
                    $extension = end($temp);
                    $img = md5(mt_rand(0, 1000000)).'.'.$extension;
                    move_uploaded_file($_FILES['image']['tmp_name'][$i], '../uploads/'.$img);
                    $images[] = $img;
                }else{
                    if(!empty($_POST['imageCompany'][$i])){
                        $images[] = $_POST['imageCompany'][$i];
                    }
                }
            }
            $id = (int)$_POST['idpartner'];
            if(empty($logo)){
                $logoData = $config->mysqli->query("SELECT logo FROM users WHERE id = '$id' ");
                $resLogo =  mysqli_fetch_array($logoData);
                $logo = $resLogo['logo'];
            }

            if(!empty($images)){
                $image = json_encode($images,true);
            }
            $cashBack = $_POST['cashBack'];
            $address = [];
            $update_id = [];
            $passwords = [];
            $lat = [];
            $lng = [];

            $addressarr = $config->mysqli->query("SELECT * FROM user_network_data WHERE user_id = '$id' ");
            foreach ($addressarr as $addr){
                $address[] = $addr['address'];
                $update_id[] = $addr['id'];
                $passwords[] =  $addr['password'];
                $lat[] =  $addr['lat'];
                $lng[] =  $addr['lng'];
            }

            $email = htmlspecialchars(strtolower($_POST['email']));
            $phone = htmlspecialchars($_POST['phone']);
            if(count($_POST['address']) >= count($address)){
                for ($i = 0; $i < count($_POST['address']); ++$i) {
                    if(isset($address[$i])){

                        if($_POST['address'][$i] == $address[$i]){
                            continue;
                        }else{
                            $address[$i] = $_POST['address'][$i];
                            $lat[$i] = $_POST['lat'][$i];
                            $lng[$i] = $_POST['lng'][$i];
                        }
                    }else{
                        $address[] = $_POST['address'][$i];
                        $update_id[] = 'ins';
                        $passwords[] = $this->generatePassword(8);
                        $lat[] = $_POST['lat'][$i];
                        $lng[] = $_POST['lng'][$i];
                    }
                }
            }else{
                for ($i = 0; $i < count($address); ++$i) {
                    if(isset($_POST['address'][$i])){
                        if($_POST['address'][$i] == $address[$i]){
                            continue;
                        }else{
                            $address[$i] = $_POST['address'][$i];
                            $lat[] = $_POST['lat'][$i];
                            $lng[] = $_POST['lng'][$i];
                        }
                    }else{
                        $config->delete($update_id[$i], 'user_network_data', 'id');
                        unset($address[$i]);
                        unset($update_id[$i]);
                        unset($passwords[$i]);
                        unset($lat[$i]);
                        unset($lng[$i]);
                    }
                }
            }
            $taxnumber = htmlspecialchars($_POST['taxnumber']);
            $agentName = htmlspecialchars($_POST['agentName']);
            $agentData = $config->mysqli->query("SELECT percentAgent FROM users WHERE id = '$agentName' ");
            $Agent_In_array = mysqli_fetch_array($agentData);
            $Agent_In = $Agent_In_array['percentAgent'];
            $discount = htmlspecialchars($_POST['discount']);
            $contactphonNumber = htmlspecialchars($_POST['contactphonNumber']);
            $bankName = htmlspecialchars($_POST['bankName']);
            $bankNumber = htmlspecialchars($_POST['bankNumber']);
            $contractNumber = htmlspecialchars($_POST['contractNumber']);
            $Client_In = htmlspecialchars($_POST['Client_In']);
            $inviter_In = htmlspecialchars($_POST['inviter_In']);
            $in = htmlspecialchars($_POST['in']);

            if (count($_POST['area']) > 1) {
                $area = htmlspecialchars(join(',', $_POST['area']));
            } else {
                $area = htmlspecialchars($_POST['area'][0]);
            }

            $armdescription = htmlspecialchars_decode($_POST['armdescription']);
            $rusdescription = htmlspecialchars_decode($_POST['rusdescription']);
            $engdescription = htmlspecialchars_decode($_POST['engdescription']);
            if(!empty($contractNumber) and !empty($email) and !empty($phone) and !empty($taxnumber)  and !empty($bankName) and !empty($bankNumber) and !empty($Client_In) and !empty($in) and !empty($area)){

                $upd = $config->update('users',
                    array(
                        'armName' =>  htmlspecialchars_decode($companyName),
                        'rusName' =>  htmlspecialchars_decode($companyName),
                        'engName' =>  htmlspecialchars_decode($companyName),
                        'phone' => $phone,
                        'contactPhonNumber' => $contactphonNumber,
                        'email' => $email,
                        'contractNumber' => $contractNumber,
                        'logo' => $logo,
                        'images' => mysqli_real_escape_string($config->mysqli,$image),
                        'hvhh' => $taxnumber,
                        'parentId'=> (int)$agentName,
                        'bankName' => $bankName,
                        'bankNumber'=>$bankNumber,
                        'area' => $area,
                        'cashIN' => $in,
                        'cashBack' => $cashBack,
                        'discount' => $discount,
                        'status' => 'confirmed',
                        'invitePercent' => $inviter_In,
                        'parentPercent' => $Agent_In,
                        'clientPercent' => $Client_In,
                        'armDescription'=>$armdescription,
                        'rusDescription'=>$rusdescription,
                        'engDescription'=>$engdescription
                    ),
                    "id = $id");
                $trans_pass = mt_rand(10000,99999);
                if ($upd) {
                    $content = '';
                    $content .= '<ul>';
                    for($i = 0;$i<count($address);$i++) {
                        if(is_numeric($update_id[$i])){
                            $config->update('user_network_data', array('address' => $address[$i], 'password' => $passwords[$i], 'lat' => $lat[$i], 'lng' => $lng[$i]),"id = '$update_id[$i]'");
                            $content .= " <li> $address[$i] - <b>".$passwords[$i].'</b> </li>';
                        }else{
                            $config->insert('user_network_data', array('user_id'=> $id,'address' => $address[$i], 'password' => $passwords[$i], 'lat' => $lat[$i], 'lng' => $lng[$i],'trans_password' => $trans_pass));
                            $content .= " <li> $address[$i] - <b>".$passwords[$i].'</b> </li>';
                        }
                    }
                    $content .= '</ul>';
                    require_once './lib/MailSender.php';
                    if (MailSender::send($email, 'VILMAR administration', "Ձեր տվյալների մեջ կա փոփոխություն VILMAR ադմինիստրացիայի կողմից: $content")) {
                    }
                }
            }
            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/partnersDatabase');
        }
    }


     public function generatePassword($length = 8)
     {
         $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
         $numChars = strlen($chars);
         $string = '';
         for ($i = 0; $i < $length; ++$i) {
             $string .= substr($chars, rand(1, $numChars) - 1, 1);
         }

         return $string;
     }

}


