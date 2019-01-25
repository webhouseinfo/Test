<?php

require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';

class About_us extends System_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index(){
        $config = new Config;
        $aboutus = $config->select("SELECT * FROM   texts WHERE 	page = 'about_us'");
        $this->view->aboutus = $aboutus;
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->render('aboutus_html');
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
    public function updateAboutUs(){
        if (isset($_POST['updateAboutUs'])) {
            $config = new Config;
            $armTitle = htmlspecialchars($_POST['armTitle']);
            $rusTitle = htmlspecialchars($_POST['rusTitle']);
            $engTitle = htmlspecialchars($_POST['engTitle']);
            $armText = htmlspecialchars($_POST['armText']);
            $rusText = htmlspecialchars($_POST['rusText']);
            $engText = htmlspecialchars($_POST['engText']);
            if(!empty($armTitle) && !empty($armText)){
                $config->update('texts',array('armTitle' => $armTitle, 'rusTitle' => $rusTitle, 'engTitle' => $engTitle,'armText'=>$armText,'rusText'=>$rusText,'engText'=>$engText), "page = 'about_us'");

            }
            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/about_us');
        }
    }
    public function get_page_content()
    {
        $config = new Config();

        $data = $config->mysqli->query('SELECT * FROM about_us')->fetch_assoc();

        $response = [];

        $response['text'] = $data['text'];

        echo json_encode($response);
        die;
    }
}
