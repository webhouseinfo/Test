<?php

require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';
class PushNotifycation extends System_Controller
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
        $this->view->render('pushNotifycation_html');
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

    public function sendNotify()
    {
        if (isset($_POST['sendNotify'])) {
            $config = new Config();
            $armtitle = $_POST['armTitle'];
            $rustitle = $_POST['rusTitle'];
            $engtitle = $_POST['engTitle'];
            $armtext = htmlspecialchars($_POST['armtext']);
            $rustext = htmlspecialchars($_POST['rustext']);
            $engtext = htmlspecialchars($_POST['engtext']);
            $armtext = mysqli_real_escape_string($config->mysqli, $armtext);
            $rustext = mysqli_real_escape_string($config->mysqli, $rustext);
            $engtext = mysqli_real_escape_string($config->mysqli, $engtext);
            $buttons = $_POST['button'];
            $usersType = $_POST['usersType'];
            $lastnumber = $config->select('SELECT notify_number FROM  Notifications ORDER BY id DESC limit 1');
            $image = '';
            if (!empty($_FILES['image']['name'])) {
                $temp = explode('.', $_FILES['image']['name']);
                $extension = end($temp);
                $image = md5(mt_rand(0, 1000000)).'.'.$extension;
                move_uploaded_file($_FILES['image']['tmp_name'], '../images/notifications/'.$image);
            }
            if (mysqli_num_rows($lastnumber) > 0) {
                $lastnumberrow = mysqli_fetch_array($lastnumber);
                $notNumber = $lastnumberrow['notify_number'] + 1;
            } else {
                $notNumber = 1;
            }

            for ($i = 0; $i < count($usersType); ++$i) {
                if (!empty($usersType[$i])) {
                    $usersNots = $config->select("SELECT users.lang,users.id,user_network_data.pusher_id FROM  users LEFT JOIN user_network_data ON users.id = user_network_data.user_id WHERE  users.status = 'confirmed' and users.userType = '".$usersType[$i]."'");

                    if (!empty($buttons)) {
                        $buttontexts = join(',', $buttons);
                    } else {
                        $buttontexts = '';
                    }

                    foreach ($usersNots as $usersNot) {
                        $config->insert('Notifications', array('armTitle' => $armtitle, 'rusTitle' => $rustitle, 'engTitle' => $engtitle, 'armText' => $armtext, 'rusText' => $rustext, 'engText' => $engtext, 'user_id' => $usersNot['id'], 'buttons' => $buttontexts, 'answer' => '', 'seen' => 0, 'notify_number' => $notNumber, 'user_type' => $usersType[$i], 'image' => $image));

                        if (!empty($usersNot['pusher_id'])) {
                            $lange = $usersNot['lang'];
                            if($lange == 'arm'){
                                $text = $armtext;
                                $title = $armtitle;
                            }else if($lange == 'rus'){
                                $text = $rustext;
                                $title = $rustitle;
                            }else{
                                $text = $engtext;
                                $title = $engtitle;
                            }
                            self::to($usersNot['pusher_id'],$image, $text, $config->mysqli->insert_id, $buttons, $title);
                            // }
                        }
                    }
                }
            }
            header('Location:https://vilmar.am/vilmar_app/VilMarAdmin/pushNotifycation');
        }
    }

    public static function to($id,$image, $message, $insertId, $buttons = null, $title = null)
    {
        $config = new Config();

        $user = $config->mysqli->query("SELECT users.id,user_network_data.user_id,user_network_data.pusher_id from users LEFT JOIN user_network_data ON users.id = user_network_data.user_id where user_network_data.pusher_id = '$id' ")->fetch_assoc();

        if (!empty($user)) {
            @$count = $config->mysqli->query("SELECT count(*) as qanak from `Notifications` WHERE seen = '0' and user_id = ".$user['id'])->fetch_assoc();
        }
        if (!is_array($id)) {
            $id = [$id];
        }
        $content = array(
            'en' => $message,
        );
        $title = array(
            'en' => $title,
        );
        $hashes_array = array();
        if (isset($buttons[0]) && !empty($buttons[0])) {
            array_push($hashes_array, array(
                'id' => $buttons[0],
                'text' => $buttons[0],
            ));
        }
        if (isset($buttons[1]) && !empty($buttons[1])) {
            array_push($hashes_array, array(
                'id' => $buttons[1],
                'text' => $buttons[1],
            ));
        }
        if(empty($image)){
            $fields = array(
                'app_id' => 'a4a28f40-78e6-4c02-8747-27c54e461d0e',
                'include_player_ids' => $id,
                'data' => array('notId' => $insertId),
                'contents' => $content,
                'subtitle' => $title,
                'buttons' => $hashes_array,
                'ios_badgeType' => 'Increase',
                'ios_badgeCount' => isset($count['qanak']) && !empty($count['qanak']) ? $count['qanak'] : '0',
                'large_icon' => "https://vilmar.am/vilmar_app/images/notification-icon.png",
            );

        }else{
            $fields = array(
                'app_id' => 'a4a28f40-78e6-4c02-8747-27c54e461d0e',
                'include_player_ids' => $id,
                'data' => array('notId' => $insertId),
                'contents' => $content,
                'subtitle' => $title,
                'buttons' => $hashes_array,
                'ios_badgeType' => 'Increase',
                'ios_badgeCount' => isset($count['qanak']) && !empty($count['qanak']) ? $count['qanak'] : '0',
                'large_icon' => "https://vilmar.am/vilmar_app/images/notification-icon.png",
                'big_picture' => "https://vilmar.am/vilmar_app/images/notifications/".$image,
                'ios_attachments' => ['id' . uniqid() => "https://vilmar.am/vilmar_app/images/notifications/".$image]
            );

        }

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
