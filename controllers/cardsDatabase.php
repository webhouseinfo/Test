<?php
require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';
class CardsDatabase extends System_Controller{
    function __construct() {
        parent::__construct();
        if(empty($_SESSION['admin']['id'])){

            $this->redirect('/vilmar_app/VilMarAdmin/login');
        }
    }
    public function index(){
        $config = new Config;
        $cardsfreefirst = $config->select("SELECT * FROM   cards WHERE  used = 0     ORDER BY id ASC limit 1");
        $cardsfreesecond = $config->select("SELECT * FROM  cards WHERE  used = 0    ORDER BY id DESC limit 1");

        $this->view->cardsfreefirst = $cardsfreefirst;
        /*return selection  card first free*/
        
        $this->view->cardsfreesecond = $cardsfreesecond;
        /*return selection   card second free*/

        if(isset($_GET['page'])){
            $number = $_GET['page'];
        }else{
            $number = 1;
        }
        $pagestart = ($number-1)*50;
        /*cards busy*/
        $cardsbusy = $config->select("SELECT * FROM   cards WHERE  used = 1    ORDER BY id ASC limit $pagestart,50");
        $this->view->cardsbusy = $cardsbusy;
        /*return selection  cards busy*/
        
        /*cards busy pages*/
        $cardsbusypage = $config->select("SELECT id FROM   cards WHERE    used = 1  ");
        $this->view->cardsbusypage = $cardsbusypage;
        /*return selection  cards busy*/

        $cardsapp = $config->select("SELECT *  FROM  cards  INNER JOIN users ON cards.user_id = users.id  WHERE   cards.amountDeals  > '0'");
        $this->view->cardsapp = $cardsapp;
        /*return selection  cards from app*/

        $cardfree = $config->select("SELECT id FROM  cards WHERE   used = '0'  ORDER BY id ASC limit 1");
        $this->view->cardfree = $cardfree;
        /*return selection first card free*/

        $agents = $config->select("SELECT * FROM  users WHERE 	userType = 'agent'   and status = 'confirmed'");
        $this->view->agents = $agents;
        /*return selection agents*/

        $partners = $config->select("SELECT * FROM  users WHERE 	userType = 'partner' and status = 'confirmed'");
        $this->view->partners = $partners;
        /*return selection partners*/
        
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->pagefree = 1;
        $this->view->pagebusy = 1;
        $this->view->render('cardsDatabase_html');
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
    public function pagefree($page){
        $config = new Config;
        if(isset($_GET['page'])){
            $number = $_GET['page'];
        }else{
            $number = 1;
        }
        $pagestart = ($number-1)*50;
        $cardsbusy = $config->select("SELECT * FROM   cards WHERE  used = '1' and  amountDeals  > '0'  ORDER BY id ASC limit $pagestart,50");
        $this->view->cardsbusy = $cardsbusy;
        /*return selection  cards busy*/
        
        $cardsbusypage = $config->select("SELECT id FROM   cards WHERE  used = '1' and  amountDeals  > '0'  ");
        $this->view->cardsbusypage = $cardsbusypage;
        /*return selection  cards busy*/
        
        $this->view->pagefree = $page;
        $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/cardsDatabase');
    }
    public function pagebusy($page){
        $config = new Config;
        if(isset($_GET['page'])){
            $number = $_GET['page'];
        }else{
            $number = 1;
        }
        $pagestart = ($number-1)*50;
        $cardsfree = $config->select("SELECT * FROM   cards WHERE used = '1' and  amountDeals  = '0'   ORDER BY id ASC limit $pagestart,50");
        $this->view->cardsfree = $cardsfree;
        /*return selection  cards free*/
        
        $cardsbusy = $config->select("SELECT * FROM   cards WHERE  used = '1' and  amountDeals  > '0'   ORDER BY id ASC limit $pagestart,50");
        $this->view->cardsbusy = $cardsbusy;
        /*return selection  cards busy*/
        
        $cardsfreepage = $config->select("SELECT id FROM   cards WHERE used = '1' and  amountDeals  = '0'  ");
        $this->view->cardsfreepage = $cardsfreepage;
        
        /*return selection  cards free*/
        $cardsbusypage = $config->select("SELECT id FROM   cards WHERE  used = '1' and  amountDeals  > '0'  ");
        
        $this->view->cardsbusypage = $cardsbusypage;
        /*return selection  cards busy*/
        
        $this->view->pagebusy = $page;
        $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/cardsDatabase');

    }
    function addCartsFromAgent(){
        if (isset($_POST['addCartsFromAgent'])) {
            $config = new Config;
            $countCards = htmlspecialchars($_POST['countCards']);
            $type = htmlspecialchars($_POST['type']);
            $money = htmlspecialchars($_POST['money']);
            $startcard = (int)htmlspecialchars($_POST['startcard']);
            $end = $startcard+$countCards-1;
            $config->update('cards',array('used' => 1, 'type' => $type,'unit' => $money), " id >='$startcard' AND id <='$end'");
            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/cardsDatabase');
        }
    }
    public function selectCardsDiapason()
    {
        $config = new Config();
        if (isset($_POST['counts'])) {
            $counts = htmlspecialchars($_POST['counts']);
            $id = htmlspecialchars($_POST['idcart']);
            $appagents = $config->select("SELECT cardNumber FROM  cards WHERE  used = '0' AND id >='$id' ORDER BY id ASC limit $counts");
            $fetch = $appagents->fetch_all(MYSQLI_ASSOC);
            echo json_encode($fetch);
            exit;
        }
    }
    public function activeType(){
        if(isset($_POST['active'])){
            $config = new Config();
            $number  = 1;
            $pagestart = ($number-1)*50;
            if($_POST['active'] == 'passive'){
                $appagents = $config->select("SELECT * FROM  cards WHERE  amountDeals = '0'  AND used = '1'  ORDER BY id ASC limit $pagestart,50");
                $fetch = $appagents->fetch_all(MYSQLI_ASSOC);
                echo json_encode($fetch);
                exit;
            }else if($_POST['active'] == 'active'){
                $appagents = $config->select("SELECT * FROM  cards WHERE  amountDeals > '0'   AND used = 1   ORDER BY id ASC limit $pagestart,50");
                $fetch = $appagents->fetch_all(MYSQLI_ASSOC);
                echo json_encode($fetch);
                exit;
            }
        }
        if ($_POST['page']){
            $config = new Config();
            if($_POST['page'] == 'passive') {
                $pages = $config->select("SELECT id FROM  cards WHERE used = '1' and  amountDeals  = '0'  ");
                echo mysqli_num_rows($pages);
                exit;
            }else if($_POST['page'] == 'active'){
                $pages = $config->select("SELECT id FROM  cards WHERE  used = '1' and  amountDeals  > '0'  ");
                echo mysqli_num_rows($pages);
                exit;
            }
        }

        if(isset($_POST['activebutton'])){
            $config = new Config();
            $number = $_POST['page'];
            $pagestart = ($number-1)*50;
            if($_POST['activebutton'] == 'passive'){
                $appagents = $config->select("SELECT * FROM  cards WHERE  used = '1' and  amountDeals  = '0'  ORDER BY id ASC limit $pagestart,50");
                $fetch = $appagents->fetch_all(MYSQLI_ASSOC);
                echo json_encode($fetch);
                exit;
            }else if($_POST['activebutton'] == 'active'){
                $appagents = $config->select("SELECT * FROM  cards WHERE  used = '1' and  amountDeals  > '0'   ORDER BY id ASC limit $pagestart,50");
                $fetch = $appagents->fetch_all(MYSQLI_ASSOC);
                echo json_encode($fetch);
                exit;
            }else{
                $appagents = $config->select("SELECT * FROM  cards WHERE  used = '1'    ORDER BY id ASC limit $pagestart,50");
                $fetch = $appagents->fetch_all(MYSQLI_ASSOC);
                echo json_encode($fetch);
                exit;
            }
        }
    }
    public function addCartsFromClient(){
        if(isset($_POST['addCartClient'])){
            $config = new Config;
            $type = htmlspecialchars($_POST['type']);
            $money = htmlspecialchars($_POST['money']);
            $startcard = htmlspecialchars($_POST['startcard']);
            $config->update('cards',array('used' => 1, 'type' => $type,'unit' => $money), " id ='$startcard'");
            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/cardsDatabase');
        }
    }
    public function generateCards(){
        if(isset($_POST['generateNewCards'])){
            $config = new Config();
            include "QR/qrlib.php";
            $countsC = $_POST['countCards'];
            $secondCart = $_POST['secondCart'];

            for($i = $secondCart+1;$i<=$secondCart+$countsC;$i++){
                if(strlen($i)<= 4){
                    if(strlen($i)<4){
                        $t = '';
                        for($j =0;$j<4-strlen($i);$j++){
                            $t = $t.'0';

                        }
                        $end = $t.$i;
                    }else if(strlen($i) == 4){
                        $end = $i;
                    }
                    $sub = substr('0000'.'_'.'0000'.'_'.'0000'.'_'.'0000',0,-4);
                    $code = $sub.$end;
                }else {

                    $exp  = array_reverse(str_split($i));

                    $t = '';
                    for($j = count($exp)-1;$j>=0;$j--){

                            if($j == 3 or $j == 7 or $j == 11){
                                $t = $t.'_';
                            }

                            $t = $t.$exp[$j];

                    }

                    $end = $t;
                    $sub = substr('0000'.'_'.'0000'.'_'.'0000'.'_'.'0000',0,-strlen($t));

                    $code = $sub.$end;
                }
                $PNG_TEMP_DIR = '/home/vilmar/public_html/vilmar_app/images/qr/';

                $PNG_WEB_DIR = 'temp/';

                if (!file_exists($PNG_TEMP_DIR))
                    mkdir($PNG_TEMP_DIR);

                $namepng= $code;
                $filename = $PNG_TEMP_DIR.$namepng.'.png';
                $errorCorrectionLevel = 'L';
                ;
                $matrixPointSize = 10;
                $matrixPointSize = min(max((int)4, 1), 10);
                $filename = $PNG_TEMP_DIR.$code.'.png';
                QRcode::png($code, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
                $file = basename($filename);
                $pass = $this->generatePassword(9);
                $select = $config->select("SELECT id FROM   cards WHERE password = '$pass'");
                if(empty($select)){
                    $config->insert('cards', array('user_id' => '0','type'=>'normal','cardNumber' => $code,'amountDeals'=>'0','unit'=>'0','used'=>'0','inviteMoney'=>'0','unconfMoney'=>'0', 'qrImage' => $file,'password'=> $pass,'insertDate'=>''));

                }else{
                    $pass = $this->generatePassword(9);

                    $config->insert('cards', array('user_id' => '0','type'=>'normal','cardNumber' => $code,'amountDeals'=>'0','unit'=>'0','used'=>'0','inviteMoney'=>'0','unconfMoney'=>'0', 'qrImage' => $file,'password'=> $pass,'insertDate'=>''));

                }

            }
        }
    }
    public function generatePassword($length = 8)
    {
        $chars = '0123456789';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; ++$i) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }

        return $string;
    }
}

