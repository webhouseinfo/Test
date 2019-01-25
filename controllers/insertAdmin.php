<?php

require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';

class InsertAdmin extends System_Controller
{
    public function __construct()
    {
        parent::__construct();
        if(empty($_SESSION['admin']['id'])){
            $this->redirect('/vilmar_app/VilMarAdmin/login');

        }

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
    public function index()
    {
        $config = new Config();
        $admins = $config->select("SELECT * FROM   admins ");
        $this->view->admins = $admins;
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->render('insertAdmin_html');
    }

    public function insertAdmin(){

        if (isset($_POST['insertAdmin'])) {
            $config = new Config;
            $firstname = htmlspecialchars($_POST['firstname']);
            $lastname = htmlspecialchars($_POST['lastname']);
            $email = htmlspecialchars($_POST['email']);
            $password = htmlspecialchars($_POST['password']);
            $found_data = $config->select("SELECT email FROM admins WHERE email = '$email'")->fetch_assoc();
            $pages = join('|||', $_POST['pagName']);
            if (!empty($found_data)) {
                $this->view->erroremail = 'Նման էլ. հասցե արդեն գրանցված է:';
            }else if(!empty($firstname) && !empty($lastname) && !empty($email) &&  !empty($password)){
                $config->insert('admins', array('name' => $firstname.' '.$lastname,  'email' => $email, 'adminType' => 'assistant', 'password' => $password,'settings' => $pages));
            }
            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/insertAdmin');
        }
    }
    public function adminAction(){
        if(isset($_POST['actionAdmin'])){
            $config = new Config;
            $action = $_POST['actionAdmin'];
            $row = $_POST['row'];

            if($action == 'delete'){
                $delete = $config->delete($row, 'admins', 'id');
                if($delete){
                    echo 'deleted';
                }
            }else if($action == 'edit'){
                $data = $config->select("SELECT * FROM admins WHERE id = '$row'")->fetch_assoc();
                    echo json_encode($data);
            }

        }
    }
    public function updateAdmin(){
        if(isset($_POST['updateAdmin'])){
            $config = new Config;
            $firstname = htmlspecialchars($_POST['firstname']);
            $lastname = htmlspecialchars($_POST['lastname']);
            $email = htmlspecialchars($_POST['email']);
            $password = htmlspecialchars($_POST['password']);
            $pages = join('|||', $_POST['pagName']);
            $id = $_POST['row'];
            if($id == 1){
                $config->update('admins', array('name' => $firstname.' '.$lastname,  'email' => $email, 'password' => $password), 'id = "'.$id.'"');

            }else{
                $config->update('admins', array('name' => $firstname.' '.$lastname,  'email' => $email, 'password' => $password,'settings' => $pages), 'id = "'.$id.'"');

            }
            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/insertAdmin');
        }
    }
}
