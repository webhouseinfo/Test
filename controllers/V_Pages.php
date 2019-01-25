<?php
require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';

class V_Pages extends System_Controller
{
    public function get_page_content()
    {
        if ($this->method() == 'POST') {
            $page = $this->input['page'];
            $action = 'get_'.$page.'_content';
            $response = call_user_func([$this, $action]);

            return $this->response($response);
        }
    }

    public function get_signup_content()
    {
        if ($this->method() == 'POST') {
            $response = [];

            $countries = $this->db->mysqli->query('SELECT * FROM `countryName` order by armCountry asc')->fetch_all(true);
            $response['areas'] = $this->db->mysqli->query('SELECT * FROM areas ORDER BY priority ASC')->fetch_all(true);
            $response['terms'] = $this->db->mysqli->query("SELECT * FROM texts WHERE page = 'terms' ")->fetch_assoc();

            usort($countries, function ($a, $b) {
                return strcmp($a['armCountry'], $b['armCountry']);
            });
            $response['arm_countries'] = $countries;

            usort($countries, function ($a, $b) {
                return strcmp($a['rusCountry'], $b['rusCountry']);
            });
            $response['rus_countries'] = $countries;

            usort($countries, function ($a, $b) {
                return strcmp($a['engCountry'], $b['engCountry']);
            });
            $response['eng_countries'] = $countries;

            return $this->response($response);
        }
    }


     public function get_comments1()
    {
        if ($this->method() == 'POST') {
            $response = [];

            $id = $this->input['id'];
            $page = $this->input['page'];

            $response['customer_comment'] = $this->get_partner_comments1($id,$page);
            

            return $this->response($response);
        }
    }

    public function get_partner_comments1($id,$page)
    
    {

        $row_count = $page * 5;

        return  $this->db->mysqli->query("SELECT * FROM comments WHERE partner_id = '{$id}' and CHAR_LENGTH(`comment`) >= 1 order by id desc limit 5 offset $row_count")->fetch_all(true);
    }


    public function get_inner_company_content()
    {
        if ($this->method() == 'POST') {
            $id = $this->input['id'];

            $response = ['status' => 'ok'];

            $company = $this->db->mysqli->query("SELECT * FROM users WHERE id = $id ")->fetch_assoc();
            $addresses = $this->db->mysqli->query("SELECT * FROM user_network_data WHERE user_id = $id and address_type = 'normal' ")->fetch_all(true);
            $comments = $this->db->mysqli->query("SELECT * FROM comments WHERE partner_id = $id and CHAR_LENGTH(`comment`) >= 1 order by id DESC limit 5")->fetch_all(true);
            $comments_count = $this->db->mysqli->query("SELECT count(*) as amount FROM comments WHERE partner_id = $id")->fetch_assoc()['amount'];

            if (!empty($company)) {
                $company['addressess'] = $addresses;
                $response['company'] = $company;
                $response['comments'] = $comments;
                $response['comments_count'] = $comments_count;
            }

            return $response;
        }
 
        
    }
    
    

    public function get_users_inactive_money($id)
    {
        $invite_data = $this->db->mysqli->query("SELECT * FROM inviteTable WHERE inviter = $id && `money` != '' and `status` = 'passive' ")->fetch_all(true);

        $client_emails = ["'default'"];
        foreach ($invite_data as $item) {
            $client_emails[] = "'{$item['invitee']}'";
        }
        $client_emails = implode(',', $client_emails);

        $clients = [];
        $clients_list = $this->db->mysqli->query("SELECT id,email,armName,armSurname FROM users WHERE email in ($client_emails) ")->fetch_all(true);
        foreach ($clients_list as $item) {
            $clients[$item['email']] = $item;
        }

        foreach ($invite_data as &$item) {
            $item['client_info'] = $clients[$item['invitee']];
        }

        return $invite_data;
    }

    public function get_transactions()
    {
        if ($this->method() == 'POST') {
            $response = [];
            $page = $this->input['page'];
            $user = $this->input['user'];
            $type = $this->input['type'];

            if ($user['userType'] == 'customer') {
                $response['transactions'] = $this->get_customer_transactions($user['id'], $type, $page);
            }

            if ($user['userType'] == 'partner') {
                $response['transactions'] = $this->get_partner_transactions($user['id'], $page);
            }

            return $this->response($response);
        }
    }

    public function get_customer_transactions($id, $type, $page = 0)
    {
        $row_count = $page * 12;

        $transactions_data = $this->db->mysqli->query("SELECT * FROM transactions WHERE (user_id = '{$id}' or invite_id = '{$id}') and deleted = '0' and `type` in ($type) ORDER BY id DESC limit 12 offset $row_count")->fetch_all(true);
        $client_ids = [-1];
        $transaction_ids = [-1];
        $transactions = [];

        

        foreach ($transactions_data as $item) {
            $client_ids[] = $item['user_id'];
            $transaction_ids[] = $item['id'];
            $transactions[$item['id']] = $item;
        }

        $client_ids = implode(',', $client_ids);
        $clients_list = $this->db->mysqli->query("SELECT id,armName, armSurname, email FROM users WHERE id in ($client_ids)")->fetch_all(true);

        $clients = [];

        foreach ($clients_list as $item) {
            $clients[$item['id']] = $item;
        }
        
        $transaction_ids = implode(',', $transaction_ids);
        $comments = $this->db->mysqli->query("SELECT * FROM comments WHERE transaction_id in ($transaction_ids) AND customer_id = '{$id}' ")->fetch_all(true);
        // var_dump($comments);
        $comments_list = [];
        foreach ($comments as $item) {
            $comments_list[$item['transaction_id']] = $item;
        }
        foreach ($transactions_data as &$item) {
            $item['client_info'] = $clients[$item['user_id']]['armName'] . ' ' . $clients[$item['user_id']]['armSurname'];
            $item['client_info_email'] = $clients[$item['user_id']]['email'];
            $item['comment'] = $comments_list[$item['id']];
        }
 
        return $transactions_data;
    }

    public function get_comments()
    {
        if ($this->method() == 'POST') {
            $response = [];

            $user = $this->input['user'];
            $page = $this->input['page'];

            $response['partner_comments'] = $this->get_partner_comments($user['id'], $page);

            return $this->response($response);
        }
    }

    public function get_partner_comments($id, $page)
    {
        $row_count = $page * 5;

        return  $this->db->mysqli->query("SELECT * FROM comments WHERE partner_id = '{$id}' and CHAR_LENGTH(`comment`) >= 1 order by id desc limit 5 offset $row_count")->fetch_all(true);
    }

    public function get_partner_transactions($id, $page)
    {
        $row_count = $page * 12;

        $transactions = $this->db->mysqli->query("SELECT * FROM transactions WHERE ( partner_id = '$id' and deleted = '0' ) limit 12 offset $row_count")->fetch_all(true);

        return $transactions;
    }

    public function get_my_account_content()
    {
        if ($this->method() == 'POST') {
            $user = $this->input['user'];
            $response = [];

            switch ($user['userType']) {
                case 'customer':
                    $response['card'] = $this->db->mysqli->query('SELECT * from cards WHERE user_id = '.$user['id'])->fetch_assoc();
                    $response['inactive_money'] = $this->get_users_inactive_money($user['id']);

                    break;

                case 'partner':
                    $response['user'] = $this->db->mysqli->query("SELECT * FROM users WHERE id = '{$user['id']}'")->fetch_assoc();
                    $response['all_comments_count'] = $this->db->mysqli->query("SELECT count(*) FROM comments as amount WHERE  partner_id = '{$user['id']}'")->fetch_assoc()['amount'];
                    break;
            }

            return $response;
        }
    }

    public function get_map_content()
    {
        if ($this->method() == 'POST') {
            $response = [
                'status' => 'ok',
            ];

            $id = $this->input['id'];

            $data = $this->db->mysqli->query('SELECT * FROM users as U left JOIN user_network_data as N on U.id = N.user_id WHERE U.id = '.$id)->fetch_all(true);

            if (!empty($data)) {
                $response['company'] = $data;
            } else {
                $response['status'] = 'error';
            }

            return $response;
        }
    }

    public function get_notifications_content()
    {
        if ($this->method() == 'POST') {
            $user = $this->input['user'];

            $response = [];
            $response['notifications'] = $this->db->mysqli->query("SELECT * FROM Notifications WHERE (seen = '0' OR ( (answer IS NULL OR answer = '') AND buttons IS NOT NULL) ) AND user_id = '{$user['id']}' order by id desc")->fetch_all(true);

            $this->db->mysqli->query("UPDATE Notifications SET seen = '1' WHERE seen = '0' AND user_id = '{$user['id']}'");

            return $response;
        }
    }

    public function get_invite_content()
    {
        $response = [];
        $response['invite_money'] = $this->db->mysqli->query('SELECT * FROM inviteMoney')->fetch_assoc();

        return $response;
    }

    public function get_terms_content()
    {
        $response = [];
        $response['terms_text'] = $this->db->mysqli->query("SELECT * FROM texts WHERE `page` = 'terms' ")->fetch_assoc();

        return $response;
    }

    public function get_news_content()
    {
        $response = [];
        $response['news'] = $this->db->mysqli->query('SELECT * FROM news ORDER BY id desc LIMIT 50')->fetch_all(true);

        return $response;
    }

    public function get_about_content()
    {
        $response = [];
        $response['about_text'] = $this->db->mysqli->query("SELECT * FROM texts WHERE `page` = 'about_us' ")->fetch_assoc();

        return $response;
    }

    public function get_myqr_content()
    {
        $response = [];
        $user_id = $this->input['user_id'];

        $card = $this->db->mysqli->query("SELECT * FROM cards WHERE user_id = $user_id and deleted != '1' and used = '1' limit 1")->fetch_assoc();

        $card['qr_base_64'] = @base64_encode(file_get_contents('../images/qr/'.$card['qrImage']));

        $response['card'] = $card;

        return $response;
    }

    public function get_category_content()
    {
        if ($this->method() == 'POST') {
            $category_id = $this->input['category_id'];

            $response = [];
            $response['companies'] = $this->Partners_with_addresses("area = '$category_id' or area like '$category_id,%' or area like '%,$category_id,%' or area like '%,$category_id'");
            $response['category'] = $this->db->mysqli->query('SELECT * FROM areas WHERE id = '.$category_id)->fetch_assoc();

            return $response;
        }
    }

    public function get_home_content()
    {
        if ($this->method() == 'POST') {
            $response = [];
            $user = $this->input['user'];

            if ($user['userType'] == 'customer') {
                $response['companies'] = $this->Partners_with_addresses();
                $response['categories'] = $this->db->mysqli->query('SELECT * FROM areas')->fetch_all(true);
                $response['slider'] = $this->db->mysqli->query('SELECT * FROM slider LIMIT 1')->fetch_assoc();
            } elseif ($user['userType'] == 'agent') {
                $income = $this->get_agents_income($user['id']);
                $response['agent_income'] = $income;
            }

            return $response;
        }
    }

    public function get_agents_income($agent_id)
    {
        $partners_by_agent = $this->db->mysqli->query("SELECT id, armName, rusName, engName, parentId FROM users WHERE parentId = '$agent_id' ")->fetch_all(true);
        $transactions = $this->db->mysqli->query("SELECT sum(agent_points) as agent_points, paid_agent,partner_id, agent_id FROM transactions WHERE  agent_id = '$agent_id' group by partner_id having paid_agent = '0' ")->fetch_all(true);

        $users = [];

        foreach ($partners_by_agent as $v) {
            $users[$v['id']] = $v;
            $users[$v['id']]['agent_points'] = 0;
        }

        foreach ($transactions as $item) {
            $users[$item['partner_id']]['agent_points'] = $item['agent_points'];
        }
        
        return $users;
    }

    public function Partners_with_addresses($where_params = null)
    {
        $result = [];
        $stm = '';

        if ($where_params) {
            $stm .= " and ($where_params) ";
        }

        $users = $this->db->mysqli->query("SELECT * FROM users WHERE `userType` = 'partner' and `status` = 'confirmed' and `deleted` = '0' $stm ORDER BY id DESC LIMIT 25")->fetch_all(true);

        $user_ids = [];
        if (!empty($users)) {
            foreach ($users as $user) {
                $user_ids[] = $user['id'];
                $user['address'] = [];
                $user['armName'] = htmlspecialchars_decode($user['armName']);
                $result[$user['id']] = $user;
            }
        }

        if (!empty($user_ids)) {
            $user_ids = join(',', $user_ids);
            $addresses = $this->db->mysqli->query("SELECT * FROM user_network_data WHERE user_id in ($user_ids) AND address_type = 'normal' ");

            foreach ($addresses as $address) {
                $result[$address['user_id']]['address'][] = $address;
            }
        }

        return $result;
    }

    public function export()
    {
        $data_ids = $this->input['dataid'];
        $dataType = $this->input['datainp'];
        $current_lang = !empty($this->input['lang']) ? $this->input['lang'] : 'arm';
        $datatransaction = $this->input['datatransaction'];
        $filneme = uniqid().'n';
        $user = $this->db->mysqli->query("SELECT * FROM users WHERE id = '{$data_ids}'")->fetch_assoc();

        $id = (int) $data_ids;
        $resUs = $this->db->mysqli->query("SELECT armName,armSurname FROM  users  WHERE   id = '$id'  ")->fetch_assoc();
        $search = " 'partner_id' = '$id' ";

        $row = $this->db->mysqli->query("SELECT * FROM  transactions  WHERE  partner_id = '{$data_ids}' ")->fetch_all(true);
        $response['row'] = $row;

        /*nor*/
        foreach ($row as $res){
            $arr1[] .= $res['date'];
            $arr2[] .= $res['card_number'];
            $arr3[] .= $res['money'];
            if($res['type'] == 'in' ){
                $arr4[] .= $user['discount'].'%';
            }else{
                $arr4[] .= '___';
            }
           
            if($res['type'] == 'in' ){
                $arr5[] .= $res['money'] -  ($res['money'] * $user['discount'] / 100);
                $arr6[] .= '___';
            }else{
                $arr5[] .= '___';
                $arr6[] .= $res['money'];
            }

                if($res['type'] == 'in' ){
                    $arr7[] .=  $res['client_points']+$res['agent_points']+$res['invite_points']+$res['admin_points'];
                    $arr8[] .= $res['paid_admin'];
                }else{
                    $arr7[] .= 0;
                    $arr8[] .= $res['paid_partner'];
                }
        }

        require_once './PHPExcel-1.8.1/Classes/PHPExcel.php';
        require_once './PHPExcel-1.8.1/Classes/PHPExcel/Writer/Excel5.php';
        $xls = new PHPExcel();
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle('Շրջանառություն');
        $sheet->setCellValue('A1', 'Շրջանառություն');
        $sheet->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $from = 'A1';
        $to = 'I1';
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(30);
        $sheet->getColumnDimension('G')->setWidth(30);
        $sheet->getColumnDimension('H')->setWidth(30);
        $sheet->getColumnDimension('I')->setWidth(30);

        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("$from:$to")->getFont()->setBold(true);
        $sheet->getStyle('A2:I2')->getFont()->setBold(true);
        $sheet->getStyle("$from:$to")->getFont()->setSize(20);
        $arr = ['', 'Գործարքի ամսաթիվ','Քարտի համար', 'Գին', 'Զեզչ','Վճարվող գումար','Վճարվող բալ','Վճարվելիք ըստ պայմանագրի','Վճարման կարգավիճակ'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H','I'];

        for ($i = 2; $i <= count($arr1) + 2; ++$i) {
            if( $arr8[$i-3] == '1' ){
                for($k = 0;$k<= 8;$k++){
                  //  $g = ($i-3)-1;
                    $sheet->getStyle($cols[$k].$i)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'CCCCCC')
                            ),
                            'borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => array('rgb' => 'AAAAAA')
                                )
                            )
                        )
                    );
                }
                $paidus = str_replace(1,'Վճարված',$arr8[$i-3] );
            }else{
                $paidus = str_replace(0,'Չվճարված',$arr8[$i-3]);

            }
            if ($i == 2) {
                $xls->setActiveSheetIndex(0)
                    ->setCellValue($cols[0].$i, $arr[0])
                    ->setCellValue($cols[1].$i, $arr[1])
                    ->setCellValue($cols[2].$i, $arr[2])
                    ->setCellValue($cols[3].$i, $arr[3])
                    ->setCellValue($cols[4].$i, $arr[4])
                    ->setCellValue($cols[5].$i, $arr[5])
                    ->setCellValue($cols[6].$i, $arr[6])
                    ->setCellValue($cols[7].$i, $arr[7])
                    ->setCellValue($cols[8].$i, $arr[8]);
            } else {
                for ($j = 0; $j < 9; ++$j) {
                    $sheet->getStyle($cols[$j].$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                }
                $xls->setActiveSheetIndex(0)
                    ->setCellValue($cols[0].$i, $i - 2)
                    ->setCellValue($cols[1].$i, $arr1[$i - 3])
                    ->setCellValue($cols[2].$i, $arr2[$i - 3])
                    ->setCellValue($cols[3].$i, $arr3[$i - 3])
                    ->setCellValue($cols[4].$i, $arr4[$i - 3])
                    ->setCellValue($cols[5].$i, $arr5[$i - 3])
                    ->setCellValue($cols[6].$i, $arr6[$i - 3])
                    ->setCellValue($cols[7].$i, $arr7[$i - 3])
                    ->setCellValue($cols[8].$i , $paidus);
            }
        }

        $path = 'exels/Vilmar-payment-list-'.hexdec(uniqid()).'.xls';
        $objWriter = new PHPExcel_Writer_Excel5($xls);
        $objWriter->save($path);
        require_once './lib/MailSender.php';
        $email_was_sent = MailSender::send($user['email'], 'Vilmar Team', '...', ['path' => $path, 'name' => 'Vilmar-payment-list.xls']);

        if ($email_was_sent) {
            $response['message'] = $this->lang[$current_lang]['excel_exported'];
        } else {
            $response['message'] = $this->lang[$current_lang]['error_occured'];
        }

        return $this->response($response);
    }
}
