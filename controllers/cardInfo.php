<?php
require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';
class CardInfo extends System_Controller{
    function __construct() {
        parent::__construct();
        if(empty($_SESSION['admin']['id'])){
            $this->redirect('/vilmar_app/VilMarAdmin/login');
        }
    }
    public function index(){
        $config = new Config;
       if(isset($_GET['from'])){
           $number  = $_GET['from'];
           $cardmore = $config->select("SELECT *  FROM  cards  INNER JOIN users ON cards.user_id = users.id  WHERE   cards.cardNumber  = '$number'");
           if(mysqli_num_rows($cardmore) == 0){
               $cardmore = $config->select("SELECT *  FROM  cards  WHERE   cardNumber  = '$number'");
           }
           $this->view->cardmore = $cardmore;/*return selection  more information*/

           $cardmoreuser = $config->select("SELECT *  FROM  cards  INNER JOIN users ON cards.user_id = users.id  WHERE   cards.cardNumber  = '$number'");

           $this->view->cardmoreusere = $cardmoreuser;/*return selection  more information*/


           $cardinfo = $config->select("SELECT * FROM   transactions WHERE  card_number = '$number'  ");
           $this->view->cardinfo = $cardinfo;/*return selection  cards busy*/
       }
        if(isset($_GET['delete'])){
            $info   = json_decode($_GET['delete'],true);//var_dump($info);die;
            $config->delete($info['value'], trim($info['table']), $info['field']);
            header("Location:".$_SERVER['HTTP_REFERER']);
        }
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->render('cardInfo_html');
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
    function addComment(){
        if(isset($_POST['addComment'])){
            $config = new Config;
            $comment = htmlspecialchars($_POST['comment']);
            $number = htmlspecialchars($_POST['cardNumber']);
            $url = htmlspecialchars($_POST['url']);
            if(empty($_POST['allId'])){
                $date1 = date('Y-m-d H:i:s',time());
                $config->insert('calling', array('card_number' => $number, 'comment_1' => $comment,'date_1'=>$date1));

            }else{
                $id = $_POST['allId'];
                $date2 = date('Y-m-d H:i:s',time());
                $config->update('calling', array('card_number' => $number, 'comment_2' => $comment,'date_2'=>$date2), "id >='$id'");
            }
            header("Location:$url");
        }
    }

}

