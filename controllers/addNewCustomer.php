<?php

require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';

class AddNewCustomer extends System_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function register_from_app()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $config = new Config();
            $name = htmlspecialchars($_POST['name']);
            $surname = htmlspecialchars($_POST['surname']);
            $phone = htmlspecialchars($_POST['phone']);
            $email = htmlspecialchars($_POST['email']);
            $date = htmlspecialchars($_POST['date']);
            $address = htmlspecialchars($_POST['address']);
            $car = htmlspecialchars($_POST['car']);
            $card = htmlspecialchars($_POST['card']);
            $from_app = isset($_POST['from_app']) && $_POST['from_app'] == 'true' ? '1' : '0';
            $password = $this->generatePassword(8).'n';
            $lng = $_POST['lng'];
            $status = '0';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                die('not_correct_email');
            }

            $found_data = $config->mysqli->query("SELECT id FROM agents WHERE email = '$email'")->fetch_assoc();

            if (!empty($found_data)) {
                die('email_in_use');
            }

            $data = [
                'armName' => $name.' '.$surname,
                'rusName' => $name.' '.$surname,
                'engName' => $name.' '.$surname,
                'phone' => $phone,
                'email' => $email,
                'birthday' => $date,
                'address' => json_encode([['name' => $address, 'coordinate' => null]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'image' => '',
                'carNumber' => $car,
                'userType' => 'customer',
                'contractNumber' => '',
                'cardCounts' => '0',
                'cardDiapason' => '0*0',
                'adminSettings' => '',
                'isFromApp' => $from_app,
                'userName' => '',
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'lastCall' => '',
                'appConfirm' => '0',
                'status' => "$status",
            ];

            if ($config->insert('agents', $data)) {
                $userID = $config->mysqli->insert_id;

                if (empty($card)) {
                    $status = 'unconfirmed';
                } else {
                    $card = str_replace(' - ', '_', $card);
                    $found_card = $config->mysqli->query("SELECT * from cards where cardNumber = '$card' and ((giveUserId != '' and acceptUserId = 0) or (giveUserId != '' and acceptUserId != 0 and transferred_to IS NULL))  ")->fetch_assoc();
                    date_default_timezone_set("Asia/Yerevan");
                    $date = date("Y-m-d H:i:s");

                    if (!empty($found_card)) {
                        $partnerID = $found_card['acceptUserId'];

                        if(!empty($partnerID)){
                            $partner = $config->mysqli->query("SELECT * FROM agents where id = $partnerID")->fetch_assoc();
    
                            if($partner['userType'] == "partner"){
                                
                                $config->mysqli->query("UPDATE cards set transferred_to = $userID, insertDate = '$date' WHERE id = ".$found_card['id']);

                            }else if ($partner['userType'] == "customer"){

                                $config->mysqli->query("UPDATE cards set acceptUserId = $userID,insertDate = '$date' WHERE id = ".$found_card['id']);
                            }
                        }else{
                            $config->mysqli->query("UPDATE cards set acceptUserId = $userID,insertDate = '$date' WHERE id = ".$found_card['id']);
                        }

                        $cardDiapason =  $found_card['cardNumber'] . '*' . $found_card['cardNumber']; 
                        $config->mysqli->query("UPDATE agents set cardDiapason = '$cardDiapason' WHERE id = ".$userID);
                    } else {
                        $config->mysqli->query("DELETE FROM agents where id = $userID");
                        die('invalid_card');
                    }
                }
                require_once './lib/MailSender.php';

                if($lng == 'arm')
                    $message = "Դուք հաջողությամբ գրանցվել եք մեր հավելվածում: Ձեր գաղտնաբառն է $password";
                elseif($lng == 'rus')
                    $message = "Դուք հաջողությամբ գրանցվել եք մեր հավելվածում: Ձեր գաղտնաբառն է $password";

                if (MailSender::send($email, 'CFT administration', $message)) {
                    echo 'success';
                }
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


