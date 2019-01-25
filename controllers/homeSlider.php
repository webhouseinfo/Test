<?php

require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';

class HomeSlider extends System_Controller
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
        $slider = $config->select("SELECT * FROM   slider WHERE 	id = 1");
        $this->view->slider = $slider;
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->render('homSlider_html');
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
    public function updateSlider(){
        if (isset($_POST['updateSlider'])) {
            $config = new Config;

            $images = [];
            for ($i = 0;$i<3;$i++){
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

            if(!empty($images) ){
                $config->update('slider',array('img1' => $images[0], 'img2' => $images[1], 'img3' => $images[2]), "id = 1");

            }
            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/homeSlider');
        }
    }


}
