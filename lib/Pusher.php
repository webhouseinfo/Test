<?php

class Pusher
{
    public static function all($message, $title = 'CFT', $buttons = null)
    {
        $content = array(
            'en' => $message,
        );

        $title = array(
            'en' => $title,
        );

        $hashes_array = array();
        array_push($hashes_array, array(
            'id' => 'like-button',
            'text' => '✓ ',
            'icon' => 'http://i.imgur.com/N8SN8ZS.png',
            'url' => 'https://yoursite.com',
        ));

        $fields = array(
            'app_id' => 'a4a28f40-78e6-4c02-8747-27c54e461d0e',
            'included_segments' => array(
                'All',
            ),
            'data' => array(
                'foo' => 'bar',
            ),
            'contents' => $content,
            'subtitle' => $title,
            'web_buttons' => $hashes_array,
        );

        $fields = json_encode($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic Y2I0ZTc3NGYtZjNmNi00Y2MwLWIwZGEtNTZjYWU5OGNiOGNk',
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public static function normal()
    {
        $content = array(
            'en' => 'English Message',
        );
        $hashes_array = array();
        array_push($hashes_array, array(
            'id' => 'like-button',
            'text' => 'Like',
            'icon' => 'http://i.imgur.com/N8SN8ZS.png',
            'url' => 'https://yoursite.com',
        ));
        array_push($hashes_array, array(
            'id' => 'like-button-2',
            'text' => 'Like2',
            'icon' => 'http://i.imgur.com/N8SN8ZS.png',
            'url' => 'https://yoursite.com',
        ));
        $fields = array(
            'app_id' => 'a4a28f40-78e6-4c02-8747-27c54e461d0e',
            'included_segments' => array(
                'All',
            ),
            'data' => array(
                'foo' => 'bar',
            ),
            'contents' => $content,
            'web_buttons' => $hashes_array,
        );

        $fields = json_encode($fields);
        echo "\nJSON sent:\n";
        echo $fields;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic Y2I0ZTc3NGYtZjNmNi00Y2MwLWIwZGEtNTZjYWU5OGNiOGNk',
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public static function to($id, $message, $insertId, $buttons = null, $armtitle = null)
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
            'en' => $armtitle,
        );
        $hashes_array = array();

        // ✓ ✘
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

        $fields = array(
            'app_id' => 'a4a28f40-78e6-4c02-8747-27c54e461d0e',
            'include_player_ids' => $id,
            'data' => array('notId' => $insertId),
            'contents' => $content,
            'subtitle' => $title,
            'buttons' => $hashes_array,
            'ios_badgeType' => 'Increase',
            'ios_badgeCount' => isset($count['qanak']) && !empty($count['qanak']) ? $count['qanak'] : '0',
            'large_icon' => 'https://vilmar.am/vilmar_app/VilMarAdmin/images/logo.svg',
            // 'image-url' => 'https://i.imgur.com/N8SN8ZS.png',
            // 'big_picture' => 'https://i.imgur.com/N8SN8ZS.png',
        //    'icon'=>"https://vilmar.am/vilmar_app/images/notifications/".$image."?width=192&height=192"
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
