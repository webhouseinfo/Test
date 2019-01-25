<?php

require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';


class News extends System_Controller{
    public $imgerroremail;
    function __construct() {
        parent::__construct();
        if(empty($_SESSION['admin']['id'])){
            $this->redirect('/vilmar_app/VilMarAdmin/login');
        }
    }
    public function index()
    {
        $config = new Config();
        if (isset($_POST['addPartner'])) {
            $armTitle = htmlspecialchars($_POST['armTitle']);
            $rusTitle = htmlspecialchars($_POST['rusTitle']);
            $engTitle = htmlspecialchars($_POST['engTitle']);
            $armText = htmlspecialchars($_POST['armText']);
            $rusText = htmlspecialchars($_POST['rusText']);
            $engText = htmlspecialchars($_POST['engText']);
            if (!empty($_FILES['image']['name'])) {
                $temp = explode('.', $_FILES['image']['name']);
                $extension = end($temp);
                $image = md5(mt_rand(0, 1000000)).'.'.$extension;
               // $image = $img;
                move_uploaded_file($_FILES['image']['tmp_name'], '../images/news/'.$image);
            }
            if (!empty($armTitle) and !empty($rusTitle) and !empty($engTitle) and !empty($armText) and !empty($rusText) and !empty($engText) and !empty($image)) {
                if( strlen($armText)<=180 and strlen($rusText)<=180 and strlen($engText)<=180){
                    $config->insert('news', array('armTitle' => $armTitle,  'rusTitle' => $rusTitle, 'engTitle' => $engTitle, 'armText' => $armText, 'rusText' => $rusText, 'engText' => $engText, 'image' => $image));
                }else{
                    $this->imgerroremail = 'Տեքստի սիմվոլները գերազանցում են 180 - ը';
                }
            }
        }
        $partnerspage = $config->select("SELECT id FROM   news  ");
        $this->view->partnerspage = $partnerspage;
        /*return selection  partners page*/
        
        if(isset($_GET['page'])){
            $number = $_GET['page'];
        }else{
            $number = 1;
        }
        $pagestart = ($number-1)*10;

        $news = $config->select("SELECT * FROM news ORDER BY id DESC LIMIT $pagestart,10 ");
        $this->view->news = $news;

        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->render('news_html');
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
    public function adminNews(){
        if(isset($_POST['actionNews'])){
            $config = new Config;
            $action = $_POST['actionNews'];
            $row = $_POST['row'];

            if($action == 'delete'){
                $delete = $config->delete($row, 'news', 'id');
                if($delete){
                    echo 'deleted';
                }
            }else if($action == 'edit'){
                $data = $config->select("SELECT * FROM news WHERE id = '$row'")->fetch_assoc();
                echo json_encode($data);
            }

        }
    }

    public function updatePartner(){
        if(isset($_POST['updatePartner'])){
            $config = new Config;
            $armTitle = htmlspecialchars($_POST['armTitle']);
            $rusTitle = htmlspecialchars($_POST['rusTitle']);
            $engTitle = htmlspecialchars($_POST['engTitle']);
            $armText = htmlspecialchars($_POST['armText']);
            $rusText = htmlspecialchars($_POST['rusText']);
            $engText = htmlspecialchars($_POST['engText']);
           // $country = htmlspecialchars($_POST['country']);
            $id = $_POST['row'];
            if (!empty($_FILES['image']['name'])) {
                $temp = explode('.', $_FILES['image']['name']);
                $extension = end($temp);
                $image = md5(mt_rand(0, 1000000)).'.'.$extension;
               // $image = '../uploads/'.$img;
                move_uploaded_file($_FILES['image']['tmp_name'], '../images/news/'.$image);
            }else{
                $data = $config->select("SELECT image FROM news WHERE id = '$id'");
                $imagearr = mysqli_fetch_array($data);
                $image = $imagearr['image'];
            }
            if(!empty($armTitle) and !empty($rusTitle) and !empty($engTitle) and !empty($armText)  and !empty($rusText)  and !empty($engText)  and !empty($id) and !empty($image)){
                if( strlen($armText)<=180 and strlen($rusText)<=180 and strlen($engText)<=180){
                    $config->update('news', array('armTitle' => $armTitle,  'rusTitle' => $rusTitle, 'engTitle' => $engTitle, 'armText' => $armText, 'rusText' => $rusText, 'engText' => $engText, 'image' => $image), 'id = "'.$id.'"');

                }else{
                    $this->imgerroremail = 'Տեքստի սիմվոլները գերազանցում են 180 - ը';
                }
            }
            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/news');
        }
    }
}