<?php
require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';
class AgentsDatabase extends System_Controller{

    function __construct() {
        parent::__construct();
        if(empty($_SESSION['admin']['id'])){
            $this->redirect('/vilmar_app/VilMarAdmin/login');
        }
    }
    public function index(){
        $config = new Config;

        if(isset($_POST['search'])){
            $searchText = $_POST['searchText'];
            if(strrpos($searchText ,' ')){
                $text = explode(' ',$searchText);
                $search = "or users.armSurname LIKE '%$text[1]%'";
            }else{
                $text = [];
                $text[] = $searchText;
            }
            
            $agents = $config->select("SELECT users.*,user_network_data.address,user_network_data.user_id,user_network_data.password  FROM  users INNER JOIN user_network_data ON users.id = user_network_data.user_id WHERE  users.status = 'confirmed'  AND users.userType = 'agent'  AND deleted = '0' AND  ( users.email  LIKE '%$text[0]%'   OR users.contractNumber  = '".(int)$searchText."'  OR users.armName LIKE '%$text[0]%' OR users.armSurname LIKE '%$text[0]%' $search)  ");
            $this->view->agents = $agents;/*return search  agents */
            $apps = $agents->fetch_all(MYSQLI_ASSOC);
            $this->view->searchText = $searchText;/*return search  text */
        }else{
            if(isset($_GET['page'])){
                $number = $_GET['page'];
            }else{
                $number = 1;
            }
            $pagestart = ($number-1)*10;
            $agents = $config->select("SELECT users.*,user_network_data.address,user_network_data.user_id,user_network_data.password FROM  users INNER JOIN user_network_data ON users.id = user_network_data.user_id WHERE  users.status = 'confirmed'  AND users.userType = 'agent'    AND deleted = '0'    ORDER BY users.id DESC LIMIT $pagestart,10");
            $this->view->agents = $agents;/*return selection All agents */
            $apps = $agents->fetch_all(MYSQLI_ASSOC);

            $agentspage = $config->select("SELECT id FROM   users WHERE status = 'confirmed'  AND userType = 'agent'    ");
            $this->view->agentspage = $agentspage;/*return selection  agents page*/
        }
        
        $adminsSettings = $this->adminsSettings();
        $this->view->adminsSettings = $adminsSettings;
        $this->view->config = new Config;
        // $this->view->cards = $cardarr;
        $this->view->render('agentsDatabase_html');
    }
    public function xlsx_data()
    {

        if (isset($_POST['dataid'])) {
            $data_ids = $_POST['dataid'];
            $dataType = $_POST['datainp'];
            $datatransaction = $_POST['datatransaction'];
            $filneme = $this->generatePassword(8).'n';
            if($dataType == 'transaction'){
                $text = '<?
                session_start();
                require \'../models/config.php\';
                $config = new Config;
                $id  = "'.$data_ids.'";
                $userType  = "'.$dataType.'";
                if(stripos($id,\',\')){
                    $id1 = explode(\',\',$id);
                }else if($id == \'all\'){
                     $rowall = $config->select("SELECT partner_id,user_id,agent_id FROM  transactions ");
                     $id1 = [];
                     foreach($rowall as $rall){
                        if(!in_array($rall[\'partner_id\'],$id1)){
                            $id1[] = $rall[\'partner_id\'];
                        }else if(!in_array($rall[\'user_id\'],$id1)){
                            $id1[] = $rall[\'user_id\'];
                        }else if(!in_array($rall[\'agent_id\'],$id1)){
                            $id1[] = $rall[\'agent_id\'];
                        }
                        
                     }
                     $dataType = \'\';
                }else{
                   $id1 = [];
                   $id1[] = $id;
                }
                $user = "'.$datatransaction.'";
                    for($i = 0;$i< count($id1);$i++){
                        $id =  (int)$id1[$i];
                        $rowUs = $config->select("SELECT armName,armSurname FROM  users  WHERE   id = \'$id\'  ");
                        $resUs = mysqli_fetch_array($rowUs);
                        $search = " '.$datatransaction.'_id = \'$id\' ";
                        if($user == \'all\'){
                             $row = $config->select("SELECT * FROM  transactions   ");
                        }else{
                           $row = $config->select("SELECT * FROM  transactions  WHERE  $search ");
                        }
                         foreach ($row as $res){                      
                            $arr1[] .= $resUs[\'armName\'].\' \'.$resUs[\'armSurname\'];
                            $arr2[] .= $res[\'address\'];   
                            $arr3[] .= $res[\'company_name\'];
                            $arr4[] .= $res[\'card_number\'];
                           
                            $arr5[] .= $res[\'money\'];
                            if($res[\'type\'] == \'in\' ){
                               $arr6[] .= $res[\'discount\'].\'դր\';
                            }else{
                             $arr6[] .= \'___\';
                            }
                            //$arr6[] .= $res[\'admin_points\']+$res[\'client_points\']+$res[\'agent_points\']+$res[\'invite_points\'];
                            if($res[\'type\'] == \'in\' ){
                               $arr7[] .= $res[\'money\'] -  $res[\'discount\'];
                               $arr8[] .= \'___\';
                            }else{
                                $arr7[] .= \'___\';
                                $arr8[] .= $res[\'money\'];
                            }
                            $arr9[] .= $res[\'client_points\'];
                            $arr10[] .= $res[\'agent_points\'];
                            $arr11[] .= $res[\'invite_points\'];
                            $arr12[] .= $res[\'date\'];
                            if($user == "partner"){
                               if($res[\'type\'] == \'in\' ){
                                   $arr13[] .= $res[\'paid_admin\'];
                                }else{
                                     $arr13[] .= $res[\'paid_partner\'];
                                }
                            }else if($user == \'agent\'){
                                $arr13[] .= $res[\'paid_agent\'];
                            }
                            $arr14[] .= $res[\'admin_points\'];
                            
                         }
                    }
              //  $filneme = str_replace(\' \',\'_\',$arr1[0]);
                require_once(\'../PHPExcel-1.8.1/Classes/PHPExcel.php\');
                require_once(\'../PHPExcel-1.8.1/Classes/PHPExcel/Writer/Excel5.php\');
                $xls = new PHPExcel();
                $xls->setActiveSheetIndex(0);
                $sheet = $xls->getActiveSheet();
                $sheet->setTitle(\'Շրջանառություն\');
                $sheet->setCellValue("A1", \'Շրջանառություն\');
                $sheet->getStyle(\'A1\')->getFill()->setFillType( PHPExcel_Style_Fill::FILL_SOLID);
      
              
                $from = "A1";
                $to = "O1";
                $sheet->getColumnDimension(\'A\')->setWidth(10);
                $sheet->getColumnDimension(\'B\')->setWidth(20);
                $sheet->getColumnDimension(\'C\')->setWidth(20);
                $sheet->getColumnDimension(\'D\')->setWidth(20);
                $sheet->getColumnDimension(\'E\')->setWidth(30);
                $sheet->getColumnDimension(\'F\')->setWidth(30);
                $sheet->getColumnDimension(\'G\')->setWidth(30);
                $sheet->getColumnDimension(\'H\')->setWidth(30);
                $sheet->getColumnDimension(\'I\')->setWidth(30);
                $sheet->getColumnDimension(\'J\')->setWidth(30);
                $sheet->getColumnDimension(\'K\')->setWidth(30);
                $sheet->getColumnDimension(\'L\')->setWidth(30);
                $sheet->getColumnDimension(\'M\')->setWidth(30);
                $sheet->getColumnDimension(\'N\')->setWidth(30);
                $sheet->getColumnDimension(\'O\')->setWidth(30);
                
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->mergeCells(\'A1:O1\');
                $sheet->getStyle(\'A1\')->getAlignment()->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("$from:$to")->getFont()->setBold( true );
                $sheet->getStyle(\'A2:O2\')->getFont()->setBold( true );
                $sheet->getStyle("$from:$to")->getFont()->setSize(20);
                $arr = [\'\',\'Անվանում\',\'Հասցե\',\'Կազմակերպության անուն\',\'Քարտի համար\',\'Գին\',\'Զեզչ\',\'Վճարվող գումար\',\'Վճարվող բալ\',\'Հաճախորդի միավոր\',\'Գործակալի միավոր\',\'Հրավիրողի միավոր\',\'Գործարքի ամսաթիվ\',\'Վճարման կարգավիճակ\',\'VM միավոր\'];
                $cols = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O"];
                
                 for($i = 2;$i<=count($arr1)+2;$i++){
                     if( $arr13[$i-3] == \'1\' ){
                         for($k = $i-3;$k<= 14;$k++){
                             $sheet->getStyle($cols[$k].$i)->applyFromArray(
                                 array(
                                     \'fill\' => array(
                                         \'type\' => PHPExcel_Style_Fill::FILL_SOLID,
                                         \'color\' => array(\'rgb\' => \'CCCCCC\')
                                     ),
                                     \'borders\' => array(
                                         \'allborders\' => array(
                                             \'style\' => PHPExcel_Style_Border::BORDER_THIN,
                                             \'color\' => array(\'rgb\' => \'AAAAAA\')
                                         )
                                     )
                                 )
                             );

                         }
                         $paidus = str_replace(1,\'Վճարված\',$arr13[$i-3] );
                     }else{
                         $paidus = str_replace(0,\'Չվճարված\',$arr13[$i-3]);

                     }
                    if($i == 2){
                        $xls->setActiveSheetIndex(0)
                            ->setCellValue($cols[0].$i , $arr[0] )
                            ->setCellValue($cols[1].$i , $arr[1] )
                            ->setCellValue($cols[2].$i , $arr[2] )
                            ->setCellValue($cols[3].$i , $arr[3] )
                            ->setCellValue($cols[4].$i , $arr[4] )
                            ->setCellValue($cols[5].$i , $arr[5] )
                            ->setCellValue($cols[6].$i , $arr[6] )
                            ->setCellValue($cols[7].$i , $arr[7] )
                            ->setCellValue($cols[8].$i , $arr[8] )
                            ->setCellValue($cols[9].$i , $arr[9] )
                            ->setCellValue($cols[10].$i , $arr[10] )
                             ->setCellValue($cols[11].$i , $arr[11] )
                              ->setCellValue($cols[12].$i , $arr[12] )
                              ->setCellValue($cols[13].$i , $arr[13] )
                              ->setCellValue($cols[14].$i , $arr[14] );
                             
                    }else{
                         for($j = 0;$j< 14;$j++){
                            $sheet->getStyle($cols[$j].$i)->getAlignment()->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        }
                        $xls->setActiveSheetIndex(0)
                            ->setCellValue($cols[0].$i , $i-2 )
                            ->setCellValue($cols[1].$i , $arr1[$i-3] )
                            ->setCellValue($cols[2].$i , $arr2[$i-3] )
                            ->setCellValue($cols[3].$i , $arr3[$i-3] )
                            ->setCellValue($cols[4].$i , $arr4[$i-3] )
                            ->setCellValue($cols[5].$i , $arr5[$i-3] )
                            ->setCellValue($cols[6].$i , $arr6[$i-3] )
                            ->setCellValue($cols[7].$i , $arr7[$i-3] )
                            ->setCellValue($cols[8].$i , $arr8[$i-3] )
                            ->setCellValue($cols[9].$i , $arr9[$i-3] )
                            ->setCellValue($cols[10].$i , $arr10[$i-3] )
                             ->setCellValue($cols[11].$i , $arr11[$i-3] )      
                             ->setCellValue($cols[12].$i , $arr12[$i-3] )      
                             ->setCellValue($cols[13].$i , $paidus)    
                             ->setCellValue($cols[14].$i , $arr14[$i-3] )  ;   
                    }
                 }

                 header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
                 header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
                 header ( "Cache-Control: no-cache, must-revalidate" );
                 header ( "Pragma: no-cache" );
                 header ( "Content-type: application/vnd.ms-excel" );
                 header ( "Content-Disposition: attachment; filename='.$filneme.'.xls" );
                 $objWriter = new PHPExcel_Writer_Excel5($xls);
                 $objWriter->save(\'php://output\');
                ?>';
            }else{
                $text = '<?
                session_start();
                require \'../models/config.php\';
                $config = new Config;
                $id  = "'.$data_ids.'";
                $userType  = "'.$dataType.'";
                if($id == \'all\'){
                    $id1 = [];
                     $rowId = $config->select("SELECT id FROM  users  WHERE  status = \'confirmed\'  AND userType = \'$userType\'  ");
                     foreach($rowId as $resid){
                         if(!in_array($resid[\'id\'],$id1)){
                            $id1[] = $resid[\'id\'];
                         }
                     }
                }else if(stripos($id,\',\')){
                    $id1 = explode(\',\',$id);
                }else{
                   $id1 = [];
                   $id1[] = $id;
                }
                $arrIdFor = [];
                for($i = 0;$i< count($id1);$i++){
                    $id =  (int)$id1[$i];
                    $row = $config->select("SELECT users.*,user_network_data.user_id,user_network_data.address FROM  users INNER JOIN user_network_data ON users.id = user_network_data.user_id WHERE  users.status = \'confirmed\'  AND users.userType = \'$userType\' AND 	users.id = \'$id\'   ");
                     foreach ($row as $res){
                         if(!in_array($res[\'id\'],$arrIdFor)){     
                             $arraddr = $config->select("SELECT address FROM  user_network_data  WHERE  user_id = \'".$res[\'id\']."\'  ");  
                             $address = [];
                             foreach($arraddr as $add){
                                   $address[] = $add[\'address\'];
                             }     
                             $arr1[] .= $res[\'armName\'].\' \'.$res[\'armSurname\'];
                             $arr2[] .= $res[\'phone\'];   
                             $arr3[] .= $res[\'birthday\'];
                             $arr4[] .= $res[\'email\'];
                             $arr5[] .= str_replace(\'female\',\'իգական\',str_replace(\'male\',\'արական\',$res[\'gender\']));
                             $arr6[] .= $res[\'contractNumber\'];
                             $arr7[] .= $res[\'armCountry\'];
                             $arr8[] .= join(\',\',$address);
                             $arr9[] = $res[\'hvhh\'];
                             $arr10[] = $res[\'bankName\'];
                             $arr11[] = $res[\'bankNumber\'];
                               if($userType == \'customer\'){
                                    $arrtype = $config->select("SELECT type FROM  cards  WHERE  user_id = \'".$res[\'id\']."\'  ");  
                                    $arrtype = mysqli_fetch_array($arrtype);
                                    $arr12[] = str_replace(\'gift\',\'Նվեր\',str_replace(\'normal\',\'Սովորական\',$arrtype[\'type\']));
                                    
                               }else{
                                    $arr12[] = $res[\'armDescription\'];
                               }
                             
                             $arr13[] = $res[\'regDate\'];
                             $arrIdFor[] = $res[\'id\'];
                         }
                     }
                }
            
                require_once(\'../PHPExcel-1.8.1/Classes/PHPExcel.php\');
                require_once(\'../PHPExcel-1.8.1/Classes/PHPExcel/Writer/Excel5.php\');
                $xls = new PHPExcel();
                $xls->setActiveSheetIndex(0);
                $sheet = $xls->getActiveSheet();
                $sheet->setTitle(\'Անձնական տվյալներ\');
                $sheet->setCellValue("A1", \'Անձնական տվյալներ\');
                $sheet->getStyle(\'A1\')->getFill()->setFillType( PHPExcel_Style_Fill::FILL_SOLID);
              //  $sheet->getStyle(\'A1\')->getFill()->getStartColor()->setRGB(\'3c3b6e\');
                $from = "A1";
                $to = "P1";
                $sheet->getColumnDimension(\'A\')->setWidth(10);
                $sheet->getColumnDimension(\'B\')->setWidth(20);
                $sheet->getColumnDimension(\'C\')->setWidth(20);
                $sheet->getColumnDimension(\'D\')->setWidth(20);
                $sheet->getColumnDimension(\'E\')->setWidth(30);
                $sheet->getColumnDimension(\'F\')->setWidth(30);
                $sheet->getColumnDimension(\'G\')->setWidth(30);
                $sheet->getColumnDimension(\'H\')->setWidth(30);
                $sheet->getColumnDimension(\'I\')->setWidth(30);
                $sheet->getColumnDimension(\'J\')->setWidth(30);
                $sheet->getColumnDimension(\'K\')->setWidth(30);
                $sheet->getColumnDimension(\'L\')->setWidth(30);
                $sheet->getColumnDimension(\'M\')->setWidth(30);
                $sheet->getColumnDimension(\'N\')->setWidth(30);
                
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->mergeCells(\'A1:N1\');
                $sheet->getStyle(\'A1\')->getAlignment()->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("$from:$to")->getFont()->setBold( true );
                $sheet->getStyle(\'A2:N2\')->getFont()->setBold( true );
                $sheet->getStyle("$from:$to")->getFont()->setSize(20);
                if($userType == \'customer\'){
                    $field = \'Քարտի տեսակ\';
                }else{
                    $field = \'Նկարագրություն\';
                }
                $arr = [\'\',\'Անվանում\',\'Հեռախոսահամար\',\'Ծննդյան օր\',\'Էլ հասցե\',\'սեռ\',\'Պայմ. համար\',\'Երկիր\',\'Հասցե\',\'ՀՎՀՀ\',\'Բանկի անվանում\',\'Հաշվի համար\',$field,\'Գրանցման օր\'];
                $cols = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N"];
                
                 for($i = 2;$i<=count($arr1)+2;$i++){
                    if($i == 2){
                        $xls->setActiveSheetIndex(0)
                            ->setCellValue($cols[0].$i , $arr[0] )
                            ->setCellValue($cols[1].$i , $arr[1] )
                            ->setCellValue($cols[2].$i , $arr[2] )
                            ->setCellValue($cols[3].$i , $arr[3] )
                            ->setCellValue($cols[4].$i , $arr[4] )
                            ->setCellValue($cols[5].$i , $arr[5] )
                            ->setCellValue($cols[6].$i , $arr[6] )
                            ->setCellValue($cols[7].$i , $arr[7] )
                            ->setCellValue($cols[8].$i , $arr[8] )
                            ->setCellValue($cols[9].$i , $arr[9] )
                            ->setCellValue($cols[10].$i , $arr[10] )
                            ->setCellValue($cols[11].$i , $arr[11] )
                            ->setCellValue($cols[12].$i , $arr[12] )
                            ->setCellValue($cols[13].$i , $arr[13] );
                    }else{
                         for($j = 0;$j< 14;$j++){
                            $sheet->getStyle($cols[$j].$i)->getAlignment()->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        }
                        $xls->setActiveSheetIndex(0)
                            ->setCellValue($cols[0].$i , $i-2 )
                            ->setCellValue($cols[1].$i , $arr1[$i-3] )
                            ->setCellValue($cols[2].$i , $arr2[$i-3] )
                            ->setCellValue($cols[3].$i , $arr3[$i-3] )
                            ->setCellValue($cols[4].$i , $arr4[$i-3] )
                            ->setCellValue($cols[5].$i , $arr5[$i-3] )
                            ->setCellValue($cols[6].$i , $arr6[$i-3] )
                            ->setCellValue($cols[7].$i , $arr7[$i-3] )
                            ->setCellValue($cols[8].$i , $arr8[$i-3] )
                            ->setCellValue($cols[9].$i , $arr9[$i-3] )
                            ->setCellValue($cols[10].$i , $arr10[$i-3] )
                            ->setCellValue($cols[11].$i , $arr11[$i-3] )
                            ->setCellValue($cols[12].$i , $arr12[$i-3] )
                            ->setCellValue($cols[13].$i , $arr13[$i-3] );
                           
                            
                    }
                 }

                 header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
                 header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
                 header ( "Cache-Control: no-cache, must-revalidate" );
                 header ( "Pragma: no-cache" );
                 header ( "Content-type: application/vnd.ms-excel" );
                 header ( "Content-Disposition: attachment; filename='.$filneme.'.xls" );
                 $objWriter = new PHPExcel_Writer_Excel5($xls);
                 $objWriter->save(\'php://output\');
                ?>';
            }
            $fp = fopen("./exels/$filneme.php", "w");
            fwrite($fp, $text);
            fclose($fp);
            echo $filneme;
            exit();
        }
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
    public function deleteUser(){
        if(isset($_POST['data'])){
            $config = new Config;
            $id = $_POST['data'];
            $config->delete($id, 'users', 'id');
            $config->update('users', array('deleted' => '1'), " id = '$id' ");

            echo 'deleted';
        }
    }
    public function activeType()
    {
        if (isset($_POST['active'])) {
            $config = new Config();
            $id = $_POST['user'];
            if ($_POST['active'] == 'passive') {

                $appagents = $config->select("SELECT * FROM  cards WHERE  amountDeals = '0'  AND used = '0' AND (giveUserId = '$id'  or acceptUserId = '$id') ORDER BY id DESC ");
                $fetch = $appagents->fetch_all(MYSQLI_ASSOC);
                echo json_encode($fetch);
                exit;
            } else if ($_POST['active'] == 'active') {
                $appagents = $config->select("SELECT * FROM  cards WHERE  amountDeals > '0'   AND used <>'0' AND (giveUserId = '$id'  or acceptUserId = '$id')  ORDER BY id DESC ");
                $fetch = $appagents->fetch_all(MYSQLI_ASSOC);
                echo json_encode($fetch);
                exit;
            }
        }
    }

    public function updateUser(){
        if (isset($_POST['updateUppUser'])) {
            $config = new Config;
            $fisrtname = htmlspecialchars($_POST['firstname']);
            $lastname = htmlspecialchars($_POST['lastname']);
            $email = htmlspecialchars(strtolower($_POST['email']));
            $phone = htmlspecialchars($_POST['phone']);
            $birtday = htmlspecialchars($_POST['birtday']);
            $address = htmlspecialchars($_POST['address']);
            $contractNumber = htmlspecialchars($_POST['contractNumber']);
            if($_POST['prefit'] == 1){
                $prefit_agent = htmlspecialchars($_POST['prefit_agent']);
                $prefit_percent = null;;
            }else  if($_POST['prefit'] == 2){
                $prefit_agent= null;
                $prefit_percent = htmlspecialchars($_POST['prefit_agent']);
            }
            $id = htmlspecialchars((int)$_POST['idagent']);

            if(!empty($fisrtname)  && !empty($lastname) && !empty($email) && !empty($phone) && !empty($birtday) && !empty($address) && !empty($contractNumber)){


                $config->update('users',array('armName' => $fisrtname,'rusName' => $fisrtname, 'engName' => $fisrtname, 'armSurname' => $lastname, 'rusSurname' => $lastname, 'engSurname' => $lastname, 'phone' => $phone, 'birthday' => $birtday, 'email' => $email,  'contractNumber' => $contractNumber,'profitAgent' => $prefit_agent,'percentAgent' =>$prefit_percent), "id = '$id'");
                $config->update('user_network_data',array( 'address' => $address ), "user_id ='$id'");
            }
            $this->redirect('https://vilmar.am/vilmar_app/VilMarAdmin/agentsDatabase');
        }
    }
    public function transactions(){
        $config = new Config;
        if(isset($_POST['agentId'])){
            $id = (int)htmlspecialchars($_POST['agentId']);
            $type = $_POST['searchData'];
            if(!empty($_POST['date1']) and empty($_POST['date2'])){
                $date1 = date('Y-m-d H:i:s',strtotime($_POST['date1'].' 00:00:00'));
                //  $date2 = date('Y-m-d',strptime($_POST['date2']));
                $date = "AND date >= '$date1' ";
            }else if(empty($_POST['date1']) and !empty($_POST['date2'])){
                // $date1 = date('Y-m-d',strptime($_POST['date1']));
                $date2 = date('Y-m-d H:i:s',strtotime($_POST['date2'].' 23:59:59'));
                $date = " AND date <='$date2' ";
            }else if(!empty($_POST['date1']) and !empty($_POST['date2'])){

                $date1 = date('Y-m-d H:i:s',strtotime($_POST['date1'].' 00:00:00'));
                $date2 = date('Y-m-d H:i:s',strtotime($_POST['date2'].' 23:59:59'));

                $date = " AND date >='$date1' AND date <='$date2' ";
            }else{
                $date = '';
            }
            if(strlen($_POST['paid']) == 0 or $_POST['paid'] == 2){
                $transactions = $config->select("SELECT * FROM  transactions  WHERE agent_id = '$id'   AND  (type = 'in' OR type = 'out') $date ORDER  BY id DESC");
            }else  if(strlen($_POST['paid']) == 1){
                $paid = (int)htmlspecialchars($_POST['paid']);
                if($type == '') {
                    $transactions = $config->select("SELECT * FROM  transactions  WHERE agent_id = '$id' AND paid_agent = '$paid'   AND  (type = 'in' OR type = 'out') $date ORDER  BY id DESC");
                }else{
                    $transactions = $config->select("SELECT * FROM  transactions  WHERE agent_id = '$id' AND paid_agent = '$paid'   AND  type = '$type' $date ORDER  BY id DESC");
                }

            }
            $fetch = $transactions->fetch_all(MYSQLI_ASSOC);
            echo  json_encode($fetch,true);exit;/*return selection transactions */
        }
    }
    
    public function paying()
    {

        if (isset($_POST['In']) or isset($_POST['Out'])) {
            $config = new Config;
            $inIds = explode(',', $_POST['In']);

           // $arrin = [];
            for ($i = 0; $i < count($inIds); $i++) {
                $upd =  $config->update('transactions', array('paid_agent' => 1), " id = '" . $inIds[$i] . "'   ");
               
            }
            $outIds = explode(',', $_POST['Out']);
            for ($i = 0; $i < count($outIds); $i++) {
                $upd = $config->update('transactions', array('paid_agent' => 1), " id = '" . $outIds[$i] . "' ");

            }
            
            if ($upd) {
                echo 1111;
            }
        }
    }
    public function selectCardsDiapason(){
        $config = new Config;
        if (isset($_POST['counts'])) {
            $counts = htmlspecialchars($_POST['counts']);
            $id = htmlspecialchars($_POST['idcart']);
            $appagents = $config->select("SELECT cardNumber FROM  cards WHERE  	giveUserId = '' AND acceptUserId = '0'  AND id >='$id' ORDER BY id ASC limit $counts");
            $fetch = $appagents->fetch_all(MYSQLI_ASSOC);
            echo json_encode($fetch);exit;
        }
    }
    public function generatePassword($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; ++$i) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }
        return $string;
    }
}