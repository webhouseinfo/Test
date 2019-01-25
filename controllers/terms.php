<?php

require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';

class Terms extends System_Controller
{
    public function __construct()
    {
        parent::__construct();
        if(empty($_SESSION['admin']['id'])){

            $this->redirect('/vilmar_app/VilMarAdmin/login');
        }
    }

    public function index()
    {
        $config = new Config();
        $terms = $config->select("SELECT * FROM   texts WHERE 	page = 'terms'");
        $this->view->terms = $terms;
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->render('terms_html');
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
    public function updateTerms(){
        if (isset($_POST['updateTerms'])) {
            $config = new Config;
            $armTitle = htmlspecialchars($_POST['armTitle']);
            $rusTitle = htmlspecialchars($_POST['rusTitle']);
            $engTitle = htmlspecialchars($_POST['engTitle']);
            $armText = htmlspecialchars($_POST['armText']);
            $rusText = htmlspecialchars($_POST['rusText']);
            $engText = htmlspecialchars($_POST['engText']);
            if(!empty($armTitle) && !empty($armText)){
                $config->update('texts',array('armTitle' => $armTitle, 'rusTitle' => $rusTitle, 'engTitle' => $engTitle,'armText'=>$armText,'rusText'=>$rusText,'engText'=>$engText), "page = 'terms'");

            }
            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/terms');
        }
    }


}
