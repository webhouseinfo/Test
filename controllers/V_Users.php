<?php

require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';

class V_Users extends System_Controller
{
    public function generate_random_string($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
    
    public function update_lang(){
         if ($this->method() == 'POST') {
            $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';
            $user_id = $this->input['id'];
            $this->db->mysqli->query("UPDATE users SET lang = '$current_lang' WHERE id = '$user_id' ");
         }
    }

    public function add_comment()
    {
        if ($this->method() == 'POST') {
            $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';
            $customer = $this->input['user'];
            $comment = $this->input['comment'];
            $partner_id = $this->input['partner_id'];
            $transaction_id = isset($comment['transaction_id']) ? $comment['transaction_id'] : null;
            $data = date('Y-m-d H:i:s');

            $response = [];

            $data = [
                'partner_id' => $partner_id,
                'customer_id' => $customer['id'],
                'stars' => $comment['stars'],
                'comment' => $comment['text'],
                'date' => $data,
            ];

            if ($transaction_id) {
                $data['transaction_id'] = $transaction_id;
            }

            $inserted = $this->db->insert('comments', $data);

            if ($inserted) {
                $response = [
                    'status' => 'ok',
                    'message' => $this->lang[$current_lang]['comment_added'],
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => $this->lang[$current_lang]['error_occured'],
                ];
            }

            $response['rating'] = $this->generate_rating($partner_id);

            return $this->response($response);
        }
    }

    public function generate_rating($partner_id)
    {
        $comments = $this->db->mysqli->query("SELECT stars FROM comments WHERE partner_id = '{$partner_id}' ")->fetch_all(true);
        $sum = 0;

        foreach ($comments as $comment) {
            $sum += (int) $comment['stars'];
        }

        $average = round($sum / count($comments));

        return $this->db->mysqli->query("UPDATE users SET rating = '$average', comments_count = comments_count + 1 WHERE id = $partner_id");
    }

    public function check_forgottend_email()
    {
        if ($this->method() == 'POST') {
            $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';
            $email = $this->input['email'];

            $response = [
                'status' => 'ok',
                'message' => '',
            ];

            $found_user = $this->db->mysqli->query("SELECT * FROM users WHERE email = '$email' ")->fetch_assoc();

            if (empty($found_user)) {
                $response = [
                    'status' => 'not_found_email',
                    'message' => $this->lang[$current_lang]['wrong_data'],
                ];
            } else {
                if ($found_user['userType'] == 'partner') {
                    $result = $this->generate_send_email_to_partner($found_user, $current_lang);

                    switch ($result) {
                        case 'error':
                            $response = [
                                'status' => 'email_not_sent',
                                'message' => $this->lang[$current_lang]['error_occured'],
                            ];
                            break;

                        case 'wrong_data':
                            $response = [
                                'status' => 'ok',
                                'message' => $this->lang[$current_lang]['new_password_was_sent'],
                            ];
                            break;

                        case 'email_sent':
                            $response = [
                                'status' => 'ok',
                                'message' => $this->lang[$current_lang]['new_password_was_sent'],
                            ];
                            break;

                        default:
                            $response = [
                                'status' => 'select_branches',
                                'message' => $this->lang[$current_lang]['select_branches_for_password'],
                                'branches' => $result,
                            ];
                    }
                } else {
                    if ($this->generate_send_email_to_agent_customer($found_user, $current_lang)) {
                        $response = [
                            'status' => 'ok',
                            'message' => $this->lang[$current_lang]['new_password_was_sent'],
                        ];
                    } else {
                        $response = [
                            'status' => 'email_not_sent',
                            'message' => $this->lang[$current_lang]['error_occured'],
                        ];
                    }
                }
            }

            return $this->response($response);
        }
    }

    public function update_selected_branches_passwords()
    {
        if ($this->method() == 'POST') {
            $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';
            $branches_ids = implode(',', $this->input['branches']);

            $branches = $this->db->mysqli->query("SELECT * FROM user_network_data WHERE id in ($branches_ids) ")->fetch_all(true);
            $user = $this->db->mysqli->query("SELECT * FROM users WHERE id = '{$branches[0]['user_id']}' ")->fetch_assoc();

            $response = [
                'status' => 'ok',
                'message' => $this->lang[$current_lang]['selected_branches_has_been_updated'],
            ];

            require_once './lib/MailSender.php';

            $email_message = '<h4> '.$this->lang[$current_lang]['branches_new_passwords'].' </h4><ul>';
            foreach ($branches as $item) {
                $password = $this->generate_random_string();
                $this->db->mysqli->query("UPDATE user_network_data SET `password` = '$password' WHERE `address` = '{$item['address']}' ");

                $email_message .= ' <li> '.$item['address'].' - <b> '.$password.'</b> </li>';
            }
            $email_message .= '</ul>';

            MailSender::send($user['email'], 'Vilmar', $email_message);

            return $this->response($response);
        }
    }

    public function generate_send_email_to_partner($user, $current_lang)
    {
        $addresses = $this->db->mysqli->query("SELECT * FROM user_network_data WHERE user_id = '{$user['id']}' ")->fetch_all(true);

        if (empty($addresses)) {
            return 'wrong_data';
        } elseif (count($addresses) == '1') {
            if ($this->generate_send_email_to_agent_customer($user, $current_lang)) {
                return 'email_sent';
            } else {
                return 'error';
            }
        } elseif (count($addresses) > 1) {
            return $addresses;
        }
    }

    public function generate_send_email_to_agent_customer($user, $current_lang)
    {
        $password = $this->generate_random_string();
        $this->db->mysqli->query("UPDATE user_network_data SET `password` = '$password' WHERE user_id = '{$user['id']}' ");

        $email_message = '<h4> '.$this->lang[$current_lang]['your_new_password_is']." {$password} </h4>";

        require_once './lib/MailSender.php';

        return MailSender::send($user['email'], 'Vilmar', $email_message);
    }

    public function submit_notification_answer()
    {
        if ($this->method() == 'POST') {
            $user = $this->input['user'];
            $button = $this->input['button'];
            $id = $this->input['id'];

            $this->db->mysqli->query("UPDATE Notifications SET answer = '$button', seen = '1' WHERE id = '$id' ");
        }
    }

    public function invite_check_email()
    {
        if ($this->method() == 'POST') {
            $response = [
                'status' => 'ok',
                'message' => '',
            ];

            $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';

            $user = $this->input['user'];
            $email = $this->input['email'];

            $found_user = $this->db->mysqli->query("SELECT * FROM users WHERE email = '$email' limit 1")->fetch_assoc();
            if (empty($found_user)) {
                $response = [
                    'status' => 'ok',
                ];
            } else {
                $response = [
                    'status' => 'user_already_registered',
                    'message' => $this->lang[$current_lang]['user_already_registered'],
                ];
            }

            return $this->response($response);
        }
    }

    public function submit_invitation()
    {
        if ($this->method() == 'POST') {
            $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';
            $data = $this->input['data'];
            $user = $this->input['user'];
            $response = ['status' => 'ok'];
            $duplicated_invitation = $this->db->mysqli->query("SELECT * FROM inviteTable WHERE invitee = '{$data['email']}' AND inviter = '{$user['id']}' ")->fetch_assoc();
            if(!empty($duplicated_invitation)){
                $response = [
                    'status' => 'error',
                    'message' => $this->lang[$current_lang]['user_already_invited'],
                ];
            }
    
            if($response['status'] == 'ok'){
                
                 $percent = null;
                $money = null;
                $date = date('Y-m-d H:i:s');
    
                $card = $this->db->mysqli->query("SELECT * from cards WHERE user_id = '{$user['id']}'")->fetch_assoc();
                $unique_token = $card['password'];
    
                if ($data['type'] == 'once') {
                    $money = $this->db->mysqli->query('SELECT * FROM inviteMoney')->fetch_assoc()['money'];
                } elseif ($data['type'] == 'multiple') {
                    //$percent = $user['invitePercent'];
                    $percent = 1;
                }
    
                $query = "INSERT INTO inviteTable (inviter,invitee,percent,`money`,`status`,`date`, token) VALUES({$user['id']}, '{$data['email']}', '$percent', '$money', 'passive', '$date', '$unique_token')";
                $r = $this->db->mysqli->query($query);
    
                if ($r) {
                    require_once './lib/MailSender.php';
    
                    $message = "
                        <b>{$user['armName']} {$user['armSurname']}</b> ".$this->lang[$current_lang]['user_invited_you'].' <br>
                       '.$this->lang[$current_lang]['use']."  {$unique_token} ".$this->lang[$current_lang]['code_on_register'].'
                    ';
                    $email_was_sent = MailSender::send($data['email'], 'Vilmar', $message);
    
                    if ($email_was_sent) {
                        $response = [
                            'status' => 'ok',
                            'message' => $this->lang[$current_lang]['invitation_submitted'],
                        ];
                    } else {
                        $this->db->mysqli->query('DELETE FROM inviteTable WHERE id = '.$this->db->mysqli->insert_id);
                    }
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => $this->lang[$current_lang]['error_occured'],
                    ];
                }
                
            }
           
            return $this->response($response);
        }
    }

    public function update_settings()
    {
        if ($this->method() == 'POST') {
            $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';

            $fullname = explode(' ', $this->input['fullname']);
            $phone = $this->input['phone'];
            $birthday = $this->input['birthday'];
            $contactNumber = $this->input['contactNumber'];
            $email = $this->input['email'];
            $user_id = $this->input['user_id'];

            $name = isset($fullname[0]) ? $fullname[0] : '';
            $surname = isset($fullname[1]) ? $fullname[1] : '';

            // BUILDING THE QUERY
            
            $query = "UPDATE users SET armName = '$name', rusName = '$name', engName = '$name', armSurname = '$surname', rusSurname = '$surname', engSurname = '$surname', email = '$email', phone = '$phone' ";
            if (!empty($birthday) && $birthday != '0000-00-00') {
                $birthday = date('Y-m-d H:i:s', strtotime($birthday));
                $query .= " ,birthday = '$birthday' ";
            }else{
                $query .= "";
            }

            $query .= " WHERE id = $user_id";
            $r = $this->db->mysqli->query($query);

            if ($r) {
                
                
                $updated_user = $this->db->mysqli->query("SELECT * FROM users WHERE id = '$user_id' ")->fetch_assoc();
                $response = [
                    'status' => 'ok',
                    'message' => $this->lang[$current_lang]['data_saved'],
                    'user' => $updated_user,
                    'query' => $query,
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => $this->lang[$current_lang]['error_occured'],
                ];
            }

            return $this->response($response);
        }
    }

    public function support_message()
    {
        if ($this->method() == 'POST') {
            $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';

            $response = [
                'status' => 'ok',
                'message' => '',
            ];

            $user = $this->input['user'];
            $text = $this->input['text'];

            require_once './lib/MailSender.php';

            $message = "
                <ul>
                    <li> <b> Name :</b> {$user['armName']} </li>
                    <li> <b> Surname :</b> {$user['armSurname']} </li>
                    <li> <b> Email :</b> {$user['email']} </li>
                </ul>

                <h2> $text </h2>
            ";

            $email_was_sent = MailSender::send('ovsep@mail.ru', 'Vilmar Support', $message);

            if ($email_was_sent) {
                $response['message'] = $this->lang[$current_lang]['message_sent'];
            } else {
                $response['message'] = $this->lang[$current_lang]['error_occured'];
            }

            return $this->response($response);
        }
    }

    public function login_attempt()
    {
        if ($this->method() == 'POST') {
            
            $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';
            $response = [
                'status' => 'ok',
                'message' => '',
            ];

            $login = $this->input['login'];
            $password = $this->input['password'];
            $pusher_id = $this->input['player_id'];

            $found_user = $this->db->mysqli->query("SELECT * FROM users WHERE (email like binary '$login') or phone = '$login'")->fetch_assoc();
            if (empty($found_user)) {
                $response['status'] = 'wrong_data';
                $response['message'] = $this->lang[$current_lang]['wrong_data'];
            } else {
                if ($found_user['userType'] == 'partner' && $found_user['status'] == 'unconfirmed') {
                    $response['status'] = 'no_access';
                    $response['message'] = $this->lang[$current_lang]['account_not_confirmed'];
                } else {
                    $address_data = $this->db->mysqli->query("SELECT * FROM user_network_data WHERE user_id = {$found_user['id']} AND password like Binary '$password' ")->fetch_assoc();
                    if (empty($address_data)) {
                        $response['status'] = 'wrong_data';
                        $response['message'] = $this->lang[$current_lang]['wrong_data'];
                    } else {
                        $this->db->mysqli->query("UPDATE user_network_data SET pusher_id = '' WHERE pusher_id = '$pusher_id' ");
                        $response['player_id_saved'] = $this->db->mysqli->query("UPDATE user_network_data SET pusher_id = '$pusher_id' WHERE id = ".$address_data['id']);
                        $this->db->mysqli->query("UPDATE `cards` SET used = '1' WHERE user_id = '{$found_user['id']}' ");
                        $this->db->mysqli->query("UPDATE `users` SET `status` = 'confirmed', lang = '$current_lang' WHERE id = '{$found_user['id']}' ");
                        $response['user'] = array_merge($address_data, $found_user);
                    }
                }
            }
            

            if ($response['status'] == 'ok') {
                if (!empty($found_user)) {
                    $user_cards = $this->get_users_card($found_user['id']);

                    // Need to give some card
                    
                    if (empty($user_cards)) {
                        $free_card = $this->db->mysqli->query('SELECT * FROM cards WHERE used = 0 and user_id = 0 LIMIT 1')->fetch_assoc();

                        if (empty($free_card)) {
                            $response['status'] = 'no_free_card';
                            $response['message'] = $this->lang[$current_lang]['no_free_card'];
                        } else {
                            $this->db->mysqli->query("UPDATE cards set used = 1, user_id = {$found_user['id']} WHERE id = {$free_card['id']} ");
                        }
                    }
                }
            }

            return $this->response($response);
        }
    }

    public function get_users_card($id)
    {
        return $this->db->mysqli->query("SELECT * FROM cards WHERE user_id = '$id' ")->fetch_all();
    }

    public function random_numbers($n = 0)
    {
        $str = '';

        for ($i = 0; $i <= $n; ++$i) {
            $str .= rand(0, 9);
        }

        return $str;
    }

    public function check_social_account()
    {
        if ($this->method() == 'POST') {
             
            $response = [];
            $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';
            $response = [
                'status' => 'ok',
                'message' => '',
            ];
            $email = $this->input['email'];
            $pusher_id = $this->input['player_id'];

            $found_user = $this->db->mysqli->query("SELECT * FROM users WHERE email = '$email' ")->fetch_assoc();

            if (!empty($found_user) && $found_user['userType'] == 'customer') {
                $response['status'] = 'account_found';
                $address_data = $this->db->mysqli->query("SELECT * FROM user_network_data WHERE user_id = {$found_user['id']} ")->fetch_assoc();
                $response['user'] = array_merge($address_data, $found_user);
                
                $this->db->mysqli->query("UPDATE `users` SET `status` = 'confirmed', lang = '$current_lang' WHERE id = '{$found_user['id']}' ");
                $this->db->mysqli->query("UPDATE `cards` SET used = '1' WHERE user_id = '{$found_user['id']}' ");
                $this->db->mysqli->query("UPDATE user_network_data SET pusher_id = '$pusher_id' WHERE id = ".$address_data['id']);
                
                $user_cards = $this->get_users_card($found_user['id']);

                    // Need to give some card
                    if (empty($user_cards)) {
                        $free_card = $this->db->mysqli->query('SELECT * FROM cards WHERE used = 0 and user_id = 0 LIMIT 1')->fetch_assoc();

                        if (empty($free_card)) {
                            $response['status'] = 'no_free_card';
                            $response['message'] = $this->lang[$current_lang]['no_free_card'];
                        } else {
                            $this->db->mysqli->query("UPDATE cards set used = 1, user_id = {$found_user['id']} WHERE id = {$free_card['id']} ");
                        }
                    }
                
                
            } else {
                
                $response['status'] = 'new_user';
            }
            
           

            return $this->response($response);
        }
    }

    public function register_partner()
    {
        if ($this->method() == 'POST') {
            $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';
            $error = false;
            $response = [
                'status' => 'ok',
                'message' => '',
            ];

            $duplicate_user = $this->check_unique_email($this->input['email']);
            if (!empty($duplicate_user)) {
                $error = true;
                $response['message'] = $this->lang[$current_lang]['email_in_use'];
            }

            if (!$error) {
                $user = [
                    'armName' => $this->input['name'],
                    'rusName' => $this->input['name'],
                    'engName' => $this->input['name'],
                    'userType' => 'partner',
                    'hvhh' => $this->input['hvhh'],
                    'area' => $this->input['type'],
                    'armDescription' => $this->input['description'],
                    'rusDescription' => $this->input['description'],
                    'engDescription' => $this->input['description'],
                    'phone' => $this->input['phone_code'] + $this->input['phone_number'],
                    'email' => $this->input['email'],
                    'isApp' => '1',
                    'status' => 'unconfirmed',
                    'regDate' => date('Y-m-d H:i:s'),
                    'token' => $this->generate_random_string(),
                ];

                $images = [];
                foreach ($_FILES as $key => $file) {
                    $temp = explode('.', $file['name']);
                    $extension = end($temp);
                    $filename = hexdec(uniqid()).'.'.$extension;
                    $path = '../uploads/'.$filename;
                    move_uploaded_file($file['tmp_name'], $path);

                    if ($key == 'logo') {
                        $user['logo'] = $filename;
                    } else {
                        $images[] = $filename;
                    }
                }

                $user['images'] = json_encode($images, true);

                $first_half_inserted = $this->db->insert('users', $user);
                $user['id'] = $this->db->mysqli->insert_id;

                if (!empty($_POST['addresses'])) {
                    $addresses = json_decode($_POST['addresses'], true);
                    $transaction_pwd = $this->random_numbers(4);

                    $email_message = '<h4> '.$this->lang[$current_lang]['partner_reg_success'].' </h4>';
                    $email_message .= '<ul>';
                    $email_message .= '<li> '.$this->lang[$current_lang]['transaction_password']." - $transaction_pwd </li>";
                    $email_message .= '</ul>';

                    foreach ($addresses as $item) {
                        $tmp_addr = [
                            'user_id' => $user['id'],
                            'pusher_id' => $user['pusher_id'],
                            'address' => $item['name'],
                            'address_type' => 'normal',
                            'password' => $this->generate_random_string(),
                            'trans_password' => $transaction_pwd,
                            'lat' => $item['lat'],
                            'lng' => $item['lng'],
                        ];
                        $this->db->insert('user_network_data', $tmp_addr);
                        $email_message .= ' <li> '.$item['name'].' - <b> '.$tmp_addr['password'].'</b> </li>';
                    }
                }

                $user_network_data = [
                    'user_id' => $user['id'],
                    'pusher_id' => $user['pusher_id'],
                    'address' => $this->input['address'],
                    'address_type' => 'law',
                    'lat' => 0,
                    'lng' => 0,
                ];

                $second_half_inserted = $this->db->insert('user_network_data', $user_network_data);

                // Check if inserted and send an email !
                
                if ($first_half_inserted and $second_half_inserted) {
                    require_once './lib/MailSender.php';

                    $email_was_sent = MailSender::send($user['email'], 'Vilmar Team', $email_message);

                    if ($email_was_sent) {
                        $response['message'] = $this->lang[$current_lang]['confirm_from_email_partner'];
                    } else {
                        $response['message'] = $this->lang[$current_lang]['couldnt_send_email'];
                    }
                }

                if (!$first_half_inserted) {
                    $response['message'] = 'Main data not inserted';
                }
                if (!$second_half_inserted) {
                    $response['message'] = 'Secondary data not inserted';
                }
            }

            return $this->response($response);
        }
    }

    public function register_customer()
    {
        if ($this->method() == 'POST') {
            $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';

            $error = false;
            $response = [
                'status' => 'ok',
                'message' => '',
            ];

            $duplicate_user = $this->check_unique_email($this->input['email']);

            if (!empty($duplicate_user)) {
                $error = true;
                $response['message'] = $this->lang[$current_lang]['email_in_use'];
            }

            if ($this->input['has_card']) {
                $card_number = $this->input['card_number'];
                $found_card = $this->db->mysqli->query("SELECT * FROM cards WHERE cardNumber = '$card_number' ")->fetch_assoc();

                if (!empty($found_card)) {
                    if (!empty($found_card['user_id'])) { // If the cards doesnt belong to anyone
                    
                        $error = true;
                        $response['message'] = $this->lang[$current_lang]['card_busy'];
                    }
                } else {
                    $error = true;
                    $response['message'] = $this->lang[$current_lang]['invalid_card'];
                }
            }

            if ($this->input['is_invited']) {
                $invite_code = $this->input['invite_code'];

                $card = @$this->db->mysqli->query("SELECT user_id,password FROM cards WHERE password = '$invite_code' ")->fetch_assoc();
                $found_token = @$this->db->mysqli->query("SELECT * FROM users WHERE id = '{$card['user_id']}' ")->fetch_assoc();

                if (empty($found_token)) {
                    $error = true;
                    $response['message'] = $this->lang[$current_lang]['invalid_invite_token'];
                }
            }

            if (!$error) {
                $user = [
                    'armName' => $this->input['name'],
                    'rusName' => $this->input['name'],
                    'engName' => $this->input['name'],
                    'armSurname' => $this->input['surname'],
                    'engSurname' => $this->input['surname'],
                    'rusSurname' => $this->input['surname'],
                    'userType' => 'customer',
                    'phone' => $this->input['phone'],
                    'birthday' => $this->input['date'],
                    'email' => strtolower($this->input['email']),
                    'gender' => $this->input['gender'],
                    'armCountry' => $this->input['country'],
                    'rusCountry' => $this->input['country'],
                    'engCountry' => $this->input['country'],
                    'parentId' => $this->input['is_invited'] ? $found_token['id'] : null,
                    'isApp' => '1',
                    'status' => 'unconfirmed',
                    'regDate' => date('Y-m-d H:i:s'),
                    'token' => hexdec(uniqid()),
                ];
                if ($this->input['is_invited']) {
                    $date = date('Y-m-d H:i:s');
                    $money = $this->db->mysqli->query('SELECT * FROM inviteMoney')->fetch_assoc()['money'];

                $this->db->mysqli->query("INSERT INTO inviteTable (inviter,invitee,percent,`money`,`status`,`date`, token) VALUES({$found_token['id']}, '{$this->input['email']}', '', '$money', 'passive', '$date', '{$card['password']}')");

                }


                $first_half_inserted = $this->db->insert('users', $user);
                $user['id'] = $this->db->mysqli->insert_id;

                if (!empty($found_card)) {
                    $this->db->mysqli->query("UPDATE cards SET user_id = '{$user['id']}', used = '0' WHERE id = {$found_card['id']}");
                }

                $user_network_data = [
                    'user_id' => $user['id'],
                    'pusher_id' => $user['pusher_id'],
                    'address' => $this->input['address'],
                    'password' => $this->generate_random_string(),
                    'lat' => $this->input['lat'],
                    'lng' => $this->input['lng'],
                ];

                $second_half_inserted = $this->db->insert('user_network_data', $user_network_data);

                // Check if inserted and send an email !
                
                if ($first_half_inserted and $second_half_inserted) {
                    require_once './lib/MailSender.php';

                    $message = $this->lang[$current_lang]['register_success'].' '.$user_network_data['password'];
                    $email_was_sent = MailSender::send($user['email'], 'Vilmar Team', $message);

                    if ($email_was_sent) {
                        $response['message'] = $this->lang[$current_lang]['confirm_from_email'];
                    } else {
                        $response['message'] = $this->lang[$current_lang]['couldnt_send_email'];
                    }
                }
            }

            return $this->response($response);
        }
    }

    public function check_unique_email($email)
    {
        return $this->db->mysqli->query("SELECT id FROM users WHERE email = '$email'")->fetch_assoc();
    }
}
