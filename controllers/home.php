<?php

require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';


class Home extends System_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (empty($_SESSION['admin']['id'])) {
            $this->redirect('/vilmar_app/VilMarAdmin/login');
        }
    }

    public function index()
    {
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->render('welcome_html');
    }

    public function adminsSettings()
    {
        $config = new Config();
        $id = $_SESSION['admin']['id'];
        $row = $config->select("SELECT 	settings FROM   admins WHERE id = '$id'");
        $res = mysqli_fetch_array($row);
        $admins = explode('|||', $res['settings']);

        return $admins;
    }

    public function notificatyion()
    {
        if (isset($_POST['newUser'])) {
            $config = new Config();
            $notify = $config->select("SELECT * FROM   users WHERE (userType = 'partner' and status = 'unconfirmed') or (userType = 'customer' and appConfirm = '0') ");
            $fetch = $notify->fetch_all(MYSQLI_ASSOC);
            echo json_encode($fetch);
            exit;
        }
    }


    public static function to($id, $message, $insertId, $buttons = null)
    {
        if (!is_array($id)) {
            $id = [$id];
        }
        $content = array(
            'en' => $message,
        );
        $hashes_array = array();
        if (isset($buttons[0])) {
            array_push($hashes_array, array(
                'id' => $buttons[0],
                'text' => $buttons[0],
            ));
        }
        if (isset($buttons[1])) {
            array_push($hashes_array, array(
                'id' => $buttons[1],
                'text' => $buttons[1],
            ));
        }

        $fields = array(
            'app_id' => '9ce78632-4d33-4cdc-8899-3b65854babe5',
            'include_player_ids' => $id,
            'data' => array('notId' => $insertId),
            'contents' => $content,
            'buttons' => $hashes_array,
        );

        $fields = json_encode($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
            'Authorization: Basic Y2I0ZTc3NGYtZjNmNi00Y2MwLWIwZGEtNTZjYWU5OGNiOGNk', ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
