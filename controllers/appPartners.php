<?php
require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';
class AppPartners extends System_Controller{
    function __construct() {
        parent::__construct();
        if(empty($_SESSION['admin']['id'])){
            $this->redirect('/vilmar_app/VilMarAdmin/login');
        }
    }
    public function index(){
        $config = new Config;
        $appagents = $config->select("SELECT users.*,user_network_data.user_id,user_network_data.address,user_network_data.password,user_network_data.lat,user_network_data.lng 
           FROM  users LEFT JOIN user_network_data ON users.id = user_network_data.user_id WHERE  users.status = 'unconfirmed' AND users.isApp = '1' AND users.userType = 'partner' AND users.deleted = '0' order by id desc");
        $this->view->apppartners = $appagents;/*return selection agents app*/
        $areas = $config->select("SELECT * FROM  areas ");
        $this->view->areas = $areas;/*return selection areas */
        $agents = $config->select("SELECT * FROM  users WHERE 	userType = 'agent'   AND status = 'confirmed' ORDER BY armName ASC");
        $this->view->agents = $agents;/*return selection agents*/
        $countrys = $config->select("SELECT armCountry,id FROM   countryName  ");
        $this->view->countrys = $countrys;/*return all countrys*/
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->render('appPartners_html');
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
            $companyName = htmlspecialchars($_POST['companyName']);
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
                $image = json_encode($images);
            }

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
            $cashBack = $_POST['cashBack'];
            $discount = htmlspecialchars($_POST['discount']);
            $taxnumber = htmlspecialchars($_POST['taxnumber']);
            $agentName = htmlspecialchars($_POST['agentName']);
            $contactphonNumber = htmlspecialchars($_POST['contactphonNumber']);
            $agentData = $config->mysqli->query("SELECT percentAgent FROM users WHERE id = '$agentName' ");
            $Agent_In_array = mysqli_fetch_array($agentData);
            $Agent_In = $Agent_In_array['percentAgent'];
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
                        'armName' => $companyName,
                        'rusName' => $companyName,
                        'engName' => $companyName,
                        'phone' => $phone,
                        'contactPhonNumber' => $contactphonNumber,
                        'email' => $email,
                        'contractNumber' => $contractNumber,
                        'logo' => $logo,
                        'images' => mysqli_real_escape_string($config->mysqli,$image),
                        'hvhh' => $taxnumber,
                        'parentId'=> $agentName,
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

            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/appPartners');
        }
        if(isset($_POST['ignoreapp'])){
            $config = new Config;
            $id = htmlspecialchars((int)$_POST['idpartner']);
            if(!empty($id)){
                $config->update('users',array('deleted'=>'1'), "id = '$id'");
            }
            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/appPartners');
        }
    }
    /*generait new user ID*/
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