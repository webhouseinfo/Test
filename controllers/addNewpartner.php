<?php

require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';

class AddNewpartner extends System_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (empty($_SESSION['admin']['id']) && $_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('/vilmar_app/VilMarAdmin/login');

        }
    }

    public function index()
    {
      
        $config = new Config();
        $areas = $config->select("SELECT id,armName FROM  areas ");
        $this->view->areas = $areas;/*return selection areas */
        $logo = '';
        if (isset($_POST['addPartner'])) {
            $companyName = htmlspecialchars($_POST['companyName']);
            if (!empty($_FILES['logo']['name'])) {
                $temp = explode('.', $_FILES['logo']['name']);
                $extension = end($temp);
                $logo = md5(mt_rand(0, 1000000)).'.'.$extension;
                // $logo = '../uploads/'.$img;
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
                }
            }
          
            $image = json_encode($images);
            $email = htmlspecialchars(strtolower($_POST['email']));
            $phone = htmlspecialchars($_POST['phone']);

            $lat = [];
            $lng = [];

            $address = [];
            $passwords = [];
            for ($i = 0; $i < count($_POST['address']); ++$i) {
               
                $lat[] = $_POST['lat'][$i];
                $lng[] = $_POST['lng'][$i];
                $address[] = $_POST['address'][$i];
                $passwords[] = $this->generatePassword(8);
            }
            $trans_pass = mt_rand(10000,99999);
            $taxnumber = htmlspecialchars($_POST['taxnumber']);
            $agentName = htmlspecialchars($_POST['agentName']);
            $agentData = $config->mysqli->query("SELECT percentAgent FROM users WHERE id = '$agentName' ");
            $Agent_In_array = mysqli_fetch_array($agentData);
            $Agent_In = $Agent_In_array['percentAgent'];
            $contactphonNumber = htmlspecialchars($_POST['contactphonNumber']);
            $bankName = htmlspecialchars($_POST['bankName'], ENT_COMPAT,'ISO-8859-1', true);
            $bankNumber = htmlspecialchars($_POST['bankNumber']);
            $discount = htmlspecialchars($_POST['discount']);
            $contractNumber = htmlspecialchars($_POST['contractNumber']);
            $Client_In = htmlspecialchars($_POST['Client_In']);
            $inviter_In = htmlspecialchars($_POST['inviter_In']);
            $cashBack = $_POST['cashBack'];
            $in = htmlspecialchars($_POST['in']);

            if (count($_POST['area']) > 1) {
                $area = htmlspecialchars(join(',', $_POST['area']));
            } else {
                $area = htmlspecialchars($_POST['area'][0]);

            }
            $armdescription = htmlspecialchars_decode($_POST['armdescription']);
            $rusdescription = htmlspecialchars_decode($_POST['rusdescription']);
            $engdescription = htmlspecialchars_decode($_POST['engdescription']);
            $found_data = $config->mysqli->query("SELECT id FROM users WHERE email = '$email' and deleted = '0' ")->fetch_assoc();
            if (!empty($found_data)) {
                $this->view->erroremail = 'Նման էլ. հասցե արդեն գրանցված է:';
            } elseif (!empty($companyName) && !empty($phone)  && !empty($address) && !empty($taxnumber)  && !empty($contractNumber) && !empty($passwords)  && !empty($logo)) {
                $ins = $config->insert('users', array(
                    'armName' => $companyName,
                    'rusName' => $companyName,
                    'engName' => $companyName,
                    'userType' => 'partner',
                    'phone' => $phone,
                    'contactPhonNumber' => $contactphonNumber,
                    'email' => $email,
                    'contractNumber' => $contractNumber,
                    'logo' => $logo,
                    'images' => mysqli_real_escape_string($config->mysqli,$image),
                    'hvhh' => (int)$taxnumber,
                    'parentId'=>(int)$agentName,
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
                    'engDescription'=>$engdescription));
                    
                if ($ins) {
                    $user_id = $config->mysqli->insert_id;
                    require_once './lib/MailSender.php';
                    $content = '<h4> Դուք հաջողությամբ գրանցվել եք մեր հավելվածում: Ստորեւ բերված է Ձեր գաղտնաբառի(երի) ցանկը </h4>';
                    $content .= '<ul>';
                    $content .= "<li>Շրջանառության գաղտնաբառ - $trans_pass </li>";
                    for($i = 0;$i<count($_POST['address']);$i++) {
                        $config->insert('user_network_data', array('user_id'=>$user_id,'address' => $address[$i], 'password' => $passwords[$i], 'lat' => $lat[$i], 'lng' => $lng[$i],'trans_password' => $trans_pass));
                        $content .= " <li> $address[$i] - <b>".$passwords[$i].'</b> </li>';
                    }
                    $content .= '</ul>';
                    if (MailSender::send($email, 'VILMAR administration', $content)) {
                    }
                }
            }
        }
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $cardfree = $config->select("SELECT id FROM  cards WHERE  giveUserId = '' AND acceptUserId = '0' AND used = '0' ORDER BY id ASC limit 1");
        $this->view->cardfree = $cardfree; /*return selection first card free*/
        $agents = $config->select("SELECT armName,armSurname,id FROM  users WHERE 	userType = 'agent' and status = 'confirmed'    ");

        $this->view->agents = $agents;/*return selection agents*/
        $countrys = $config->select("SELECT armCountry,id FROM   countryName  ");
        $this->view->countrys = $countrys;/*return all countrys*/
        $this->view->render('addNewpartner_html');
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

    public function selectCardsDiapason()
    {
        $config = new Config();
        if (isset($_POST['counts'])) {
            $counts = htmlspecialchars($_POST['counts']);
            $id = htmlspecialchars($_POST['idcart']);
            $appagents = $config->select("SELECT cardNumber FROM  cards WHERE  	giveUserId = '' AND acceptUserId = '0'  AND id >='$id' ORDER BY id ASC limit $counts");
            $fetch = $appagents->fetch_all(MYSQLI_ASSOC);
            echo json_encode($fetch);
            exit;
        }
    }

    public function register_from_app()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $config = new Config();
            $name = htmlspecialchars($_POST['partner_name']);
            $phone = htmlspecialchars($_POST['partner_phone']);
            $email = htmlspecialchars($_POST['partner_email']);
            $date = htmlspecialchars($_POST['partner_date']);
            $car = htmlspecialchars($_POST['partner_car']);
            $is_card_granter = htmlspecialchars($_POST['is_card_granter']);
            $hvhh = htmlspecialchars($_POST['partner_hvhh']);
            $parentID = htmlspecialchars($_POST['parentID']);
            $coordinates = $_POST['coordinates'];
            $from_app = isset($_POST['from_app']) && $_POST['from_app'] == 'true' ? '1' : '0';
            $area = $_POST['partner_area'];
            $address = $_POST['partner_address'];
            $passwords = [];
            $lng = $_POST['lng'];
            $passwords_hashed = [];

            if ($from_app) {
                $cashin = '-1';
                $cashout = '-1';
            } else {
                $cashin = htmlspecialchars($_POST['in']);
                $cashout = htmlspecialchars($_POST['out']);
            }
            $address_with_coordinate = [];
            $coordinates = json_decode($coordinates, true);
            $player_ids = [];

            for ($i = 0; $i < count($address); ++$i) {
                $address_with_coordinate[$i]['name'] = htmlspecialchars($address[$i]);
                $address_with_coordinate[$i]['coordinate'] = $coordinates[$i];
                $passwords[] = $this->generatePassword(8).'n';
                $passwords_hashed[] = password_hash($passwords[$i], PASSWORD_DEFAULT);
                $player_ids[] = "0";
            }

            for ($i = 0; $i < count($area); ++$i) {
                $area[$i] = htmlspecialchars($area[$i]);
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                die('not_correct_email');
            }

            if (isset($_FILES['partner_avatar']) and !empty($_FILES['partner_avatar']) and $_FILES['partner_avatar']['size'] > 0) {
                $file = $_FILES['partner_avatar'];
                $prefix = uniqid();
                $destination = '../uploads/'.$prefix.'___'.$file['name'];
                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    die('file_not_uploaded');
                }
            } else {
                die('file_not_selected');
            }


            require_once './lib/ImageWizard.php';
            $marker_logo = ImageWizard::combine($destination, './images/marker_template.png');

            $found_data = $config->mysqli->query("SELECT id FROM agents WHERE email = '$email' AND deleted = '0'")->fetch_assoc();

            if (!empty($found_data)) {
                die('email_in_use');
            }

            $main_pwd = "";
            for($i = 0; $i < 4; $i++){
                $main_pwd .= rand(0, 9);
            }
            $passwords_hashed[] = $main_pwd;

            $data = [
                'armName' => $name,
                'rusName' => $name,
                'engName' => $name,
                'phone' => $phone,
                'email' => $email,
                'birthday' => $date,
                'address' => json_encode($address_with_coordinate, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'image' => $destination,
                'isFromApp' => $from_app,
                'carNumber' => $car,
                'userType' => 'partner',
                'contractNumber' => '',
                'qashIn' => $cashin,
                'qashOut' => $cashout,
                'cardCounts' => '0',
                'cardDiapason' => '0*0',
                'adminSettings' => '',
                'userName' => '',
                'password' => json_encode($passwords_hashed),
                'hvhh' => $hvhh,
                'lastCall' => '',
                'appConfirm' => '0',
                'status' => 'unconfirmed',
                'area' => implode(',', $area),
                'isFromApp' => "1",
                'canSendCards' => $is_card_granter == 'yes' ? '1' : '0',
                'marker_logo' => $marker_logo,
                'player_id' => json_encode($player_ids)
            ];

            if (!empty($parentID)) {
                $data['parentID'] = $parentID;
                $data['status'] = 'unconfirmed';
            }

            if ($config->insert('agents', $data)) {
                require_once './lib/MailSender.php';

                if($lng == 'arm')
                {
                    $msg = 'Դուք հաջողությամբ գրանցվել եք մեր հավելվածում: Ստորեւ բերված է Ձեր գաղտնաբառերի ցանկը';
                    $shrjanarutyan_gaxtnabar = "Շրջանառության գաղտնաբառ";
                }
                elseif($lng == 'rus')
                {
                    $msg = 'Դուք հաջողությամբ գրանցվել եք մեր հավելվածում: Ստորեւ բերված է Ձեր գաղտնաբառերի ցանկը';
                    $shrjanarutyan_gaxtnabar = "Շրջանառության գաղտնաբառ";

                }

                $content = '<h4> ' . $msg . ' </h4>';
                $content .= '<ul>';
                $content .= "<li>$shrjanarutyan_gaxtnabar - $main_pwd </li>";

                foreach ($address as $key => $value) {
                    $content .= " <li> $value - <b>".$passwords[$key].'</b> </li>';
                }
                $content .= '</ul>';

                if (MailSender::send($email, 'CFT administration', $content)) {
                    echo 'success';
                }
            }else{
                echo "try_again";
            }

            die;
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
