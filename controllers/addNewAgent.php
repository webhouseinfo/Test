<?php

require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';

class AddNewAgent extends System_Controller
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
        if (isset($_POST['addAgent'])) {
            $firstname = htmlspecialchars($_POST['firstname']);
            $lastname = htmlspecialchars($_POST['lastname']);
            $phone = htmlspecialchars($_POST['phone']);
            $email = htmlspecialchars(strtolower($_POST['email']));
            $gender = htmlspecialchars($_POST['gender']);
            $birtday = htmlspecialchars($_POST['birtday']);
            $adress = htmlspecialchars($_POST['address']);
            $contractNumber = htmlspecialchars($_POST['contractNumber']);
            $prefit_agent= htmlspecialchars($_POST['prefit_agent']);
            $prefit_percent = htmlspecialchars($_POST['prefit_percent']);
            $password = $this->generatePassword(8).'n';
            $found_data = $config->mysqli->query("SELECT id FROM users WHERE email = '$email'  ")->fetch_assoc();

            if (!empty($found_data)) {
                $this->view->erroremail = 'Նման էլ. հասցե արդեն գրանցված է:';
            } elseif (!empty($firstname) && !empty($lastname) && !empty($phone) && !empty($email) && !empty($birtday) && !empty($adress) && !empty($contractNumber)  && !empty($password) && !empty($gender)) {
                $ins = $config->insert('users', array('armName' => $firstname, 'rusName' => $firstname, 'engName' => $firstname,'armSurname' => $lastname, 'rusSurname' => $lastname, 'engSurname' => $lastname, 'userType'=>'agent','phone' => $phone, 'email' => $email, 'birthday' => $birtday,'contractNumber'=>$contractNumber,'gender'=> $gender,'profitAgent'=>$prefit_agent,'percentAgent'=>$prefit_percent,'isApp' => '0','status' => 'confirmed'));


                if ($ins) {
                    $config->insert('user_network_data', array('user_id' => $config->mysqli->insert_id, 'address' => $adress, 'password' => $password));

                    require_once './lib/MailSender.php';
                    if (MailSender::send($email, 'VILMAR administration', "Դուք հաջողությամբ գրանցվել եք մեր հավելվածում: Ձեր գաղտնաբառն է $password")) {
                    }

                }
            }
        }
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $cardfree = $config->select("SELECT id FROM  cards WHERE  giveUserId = '' AND acceptUserId = '0' AND used = '0' ORDER BY id ASC limit 1");
        $this->view->cardfree = $cardfree; /*return selection first card free*/
        $this->view->render('addNewAgent_html');
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
}
