<?php

require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';

class V_Transactions extends System_Controller
{
    public function check_card()
    {
        if ($this->method() == 'POST') {
            $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';
            $response = [
                'status' => 'ok',
            ];

            $card_number = $this->input['card_number'];

            $card = $this->db->mysqli->query("SELECT * FROM cards WHERE cardNumber = '$card_number' and used = '1' and user_id != '0' and deleted != '1'")->fetch_assoc();

            if (empty($card)) {
                $response = [
                    'status' => 'invalid_card',
                    'message' => $this->lang[$current_lang]['wrong_data'],
                ];
            } else {
                $response['card'] = $card;
            }

            return $this->response($response);
        }
    }

    public function cashin_submit()
    {
        if ($this->method() == 'POST') {
            $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';

            $card = $this->input['card'];
            $user = $this->input['user'];
            $money = $this->input['money'];
            $address = $user['address'];

            $user = $this->db->mysqli->query("SELECT * FROM users WHERE id = '{$user['id']}'")->fetch_assoc();

            $response = [
                'status' => 'ok',
            ];

            $client_points = 0;
            $agent_points = 0;
            $inviter_points = 0;
            $admin_points = 0;

            $card = $this->db->mysqli->query("SELECT * FROM cards WHERE id = '{$card['id']}'")->fetch_assoc();

            // ---------- CLIENT ----------
            
            $client_points = $money * $user['clientPercent'] / 100;
            $this->db->mysqli->query("UPDATE cards SET amountDeals = amountDeals + 1, unit = unit + {$client_points} WHERE id = {$card['id']}");

            $nkarvox = $this->db->mysqli->query("SELECT * FROM users WHERE id = {$card['user_id']}")->fetch_assoc();

            // ---------- INVITER ----------
            if (!empty($nkarvox['parentId'])) {
                $parent = $this->db->mysqli->query("SELECT * FROM users WHERE id = '{$nkarvox['parentId']}' ")->fetch_assoc();
                $invite_data = $this->db->mysqli->query("SELECT * FROM inviteTable WHERE inviter = '{$parent['id']}' and invitee = '{$nkarvox['email']}' LIMIT 1 ")->fetch_assoc();

                if ($card['amountDeals'] == '0') {
                    $this->db->mysqli->query("UPDATE inviteTable SET `status` = 'active' WHERE id = '{$invite_data['id']}' ");
                }

                if (!empty($invite_data['money'])) {
                    $invite_points_type = 'once';
                    if ($card['amountDeals'] == '0') { 
                        $inviter_points = $invite_data['money'];
                        $this->db->mysqli->query("UPDATE cards SET unit = unit + {$invite_data['money']} WHERE user_id = '{$invite_data['inviter']}' ");
                    }
                } else {
                    $invite_points_type = 'several';
                    if (!empty($user['invitePercent'])) {
                        $inviter_points = $money * $user['invitePercent'] / 100;
                        $this->db->mysqli->query("UPDATE cards SET unit = unit + {$inviter_points} WHERE user_id = '{$invite_data['inviter']}' ");
                    }
                }
            }

            // ---------- AGENT ----------
            if (!empty($user['parentId'])) {
                $agent_data = $this->db->mysqli->query("SELECT * FROM users WHERE id = {$user['parentId']} ")->fetch_assoc();

                if (empty($agent_data['profitAgent'])) { 
                    $agent_points = $money * $agent_data['percentAgent'] / 100;
                }
            }

            // ---------- ADMIN ----------
            $admin_points = ($money * $user['cashIN'] / 100) - ($client_points + $agent_points + $inviter_points);
            $this->db->mysqli->query("UPDATE admins SET unit = unit + $admin_points");

            $transaciton = [
                'type' => 'in',
                'paid_agent' => '0',
                'paid_partner' => '0',
                'paid_admin' => '0',
                'partner_id' => $user['id'],
                'user_id' => $card['user_id'],
                'card_id' => $card['id'],
                'agent_id' => $user['parentId'],
                'card_number' => $card['cardNumber'],
                'money' => $money,
                'client_points' => $client_points,
                'admin_points' => $admin_points,
                'agent_points' => $agent_points,
                'invite_points' => $inviter_points,
                'address' => $address,
                'company_name' => $user['armName'],
                'deleted' => '0',
                'discount' => $money * $user['discount'] / 100
            ];

            if ($invite_data) {
                $transaciton['invite_id'] = $invite_data['inviter'];
                $transaciton['invite_points_type'] = $invite_points_type;
            }

            $inserted = $this->db->insert('transactions', $transaciton);

            if ($inserted) {
                $response['status'] = 'ok';
                $response['message'] = $this->lang[$current_lang]['transaction_success'];

                require_once './lib/Pusher.php';

                $pusher = $this->db->mysqli->query("SELECT * FROM user_network_data WHERE user_id = '{$nkarvox['id']}' ")->fetch_assoc();
                $response['sss'] = Pusher::to($pusher['pusher_id'], $this->lang[$current_lang]['transaction_success'], '3', [], $this->lang[$current_lang]['transaction_success']);
            } else {
                $response['status'] = 'error';
                $response['message'] = $this->lang[$current_lang]['error_occured'];
            }

            return $this->response($response);
        }
    }

    public function cashout_submit()
    {
        if ($this->method() == 'POST') {
            $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';

            $card = $this->input['card'];
            $money = $this->input['money'];
            $user = $this->input['user'];

            $response = [
                'status' => 'ok',
            ];

            $card = $this->db->mysqli->query("SELECT * FROM cards WHERE id = '{$card['id']}'")->fetch_assoc();

            if ($card['unit'] < $money) {
                $response['status'] = 'not_enought_money';
                $response['message'] = $this->lang[$current_lang]['not_enough_units'];
            }

            if ($response['status'] == 'ok') {
                $transaciton = [
                    'type' => 'out',
                    'paid_partner' => '0',
                    'partner_id' => $user['id'],
                    'user_id' => $card['user_id'],
                    'card_id' => $card['id'],
                    'card_number' => $card['cardNumber'],
                    'money' => $money,
                    'address' => $user['address'],
                    'company_name' => $user['armName'],
                    'deleted' => '0',
                ];

                $inserted = $this->db->insert('transactions', $transaciton);
                $this->db->mysqli->query("UPDATE cards SET unit = unit - $money WHERE id = '{$card['id']}'");

                if ($inserted) {
                    $response['status'] = 'ok';
                    $response['message'] = $this->lang[$current_lang]['transaction_success'];
                } else {
                    $response['status'] = 'error';
                    $response['message'] = $this->lang[$current_lang]['error_occured'];
                }
            }
            return $this->response($response);
        }
    }

    public function cashin()
    {
        if ($this->method() == 'POST') {
            $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';
            $response = [
                'status' => 'ok',
            ];

            $card = $this->input['card'];
            $amount = $this->input['amount'];

            dd($amount);

            return $this->response($response);
        }
    }
}
