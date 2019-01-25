<?php
require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';
class HistoryNotifycation extends System_Controller{
    function __construct() {
        parent::__construct();
        if(empty($_SESSION['admin']['id'])){
            $this->redirect('/cft_app/CFTcard/login');
        }
    }
    public function index(){
        $config = new Config;
        $notsHistory = $config->select("SELECT * FROM  Notifications ORDER BY id DESC");

        $endEl = $config->select("SELECT * FROM  Notifications ORDER BY id DESC LIMIT 1");
        $endElarr = mysqli_fetch_array($endEl);
        $endid = $endElarr['notify_number'];
        $arrhistory = [];
        $seen = [];
        $answer_1 = [];
        $answer_2 = [];
        $arr_users = [];
        $types = array('agent'=>'Գործակալներ','partner'=>'Գործընկերներ','customer'=>'Հաճախորդներ');
        for ( $i = (int)$endid;$i >0;$i--){

            $history = $config->select("SELECT id,buttons FROM Notifications WHERE notify_number = '".$i."' ORDER BY id DESC");
            $historyseen = $config->select("SELECT seen FROM Notifications WHERE seen = '1' AND notify_number = '".$i."' ORDER BY id DESC");
            $historytype = $config->select("SELECT user_type FROM Notifications WHERE notify_number = '".$i."' ORDER BY id DESC");

            $res1 = mysqli_fetch_array($history);
            if($res1['buttons'] !== ''){
                $butt = explode(',',$res1['buttons']);
                $butt1 = trim($butt[0]);
                $butt2 = trim($butt[1]);
                $answer1 = $config->select("SELECT answer FROM Notifications WHERE answer LIKE '%$butt1%'  AND  notify_number = '$i' AND answer <> ''  ORDER BY id DESC");
                $answer2 = $config->select("SELECT answer FROM Notifications WHERE answer LIKE '%$butt2%'  AND  notify_number = '$i'  AND answer <> '' ORDER BY id DESC");
                $answer_1[] = mysqli_num_rows($answer1);
                $answer_2[] = mysqli_num_rows($answer2);
            }else{
                $answer_1[] = 0;
                $answer_2[] = 0;
            }
            $arr = [];
            foreach ($historytype as $row){
                if(count($arr)<3){
                    foreach ($types as $key => $val){
                        if($key == $row['user_type']){
                            if(!in_array($val,$arr)){
                                array_push($arr,$val);
                            }
                        }
                    }
                }

            }
            array_push($arr_users,join(',',$arr));
            array_push($arrhistory,mysqli_num_rows($history));
            array_push($seen,mysqli_num_rows($historyseen));
        }
        $this->view->arr_users = $arr_users;
        $this->view->arrhistory = $arrhistory;
        $this->view->seen = $seen;
        $this->view->answer_1 = $answer_1;
        $this->view->answer_2 = $answer_2;
        $this->view->notsHistory = $notsHistory; 
        /*return selection nots History*/
        
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->render('historyNotifycation_html');
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

}

