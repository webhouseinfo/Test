<?
                session_start();
                require '../models/config.php';
                $config = new Config;
                $id  = "11";
                $userType  = "transaction";
                if(stripos($id,',')){
                    $id1 = explode(',',$id);
                }else if($id == 'all'){
                     $rowall = $config->select("SELECT partner_id,user_id,agent_id FROM  transactions ");
                     $id1 = [];
                     foreach($rowall as $rall){
                        if(!in_array($rall['partner_id'],$id1)){
                            $id1[] = $rall['partner_id'];
                        }else if(!in_array($rall['user_id'],$id1)){
                            $id1[] = $rall['user_id'];
                        }else if(!in_array($rall['agent_id'],$id1)){
                            $id1[] = $rall['agent_id'];
                        }
                        
                     }
                     $dataType = '';
                }else{
                   $id1 = [];
                   $id1[] = $id;
                }
                $user = "partner";
                    for($i = 0;$i< count($id1);$i++){
                        $id =  (int)$id1[$i];
                        $rowUs = $config->select("SELECT armName,armSurname FROM  users  WHERE   id = '$id'  ");
                        $resUs = mysqli_fetch_array($rowUs);
                        $search = " partner_id = '$id' ";
                        if($user == 'all'){
                             $row = $config->select("SELECT * FROM  transactions   ");
                        }else{
                           $row = $config->select("SELECT * FROM  transactions  WHERE  $search ");
                        }
                         foreach ($row as $res){                      
                            $arr1[] .= $resUs['armName'].' '.$resUs['armSurname'];
                            $arr2[] .= $res['address'];   
                            $arr3[] .= $res['company_name'];
                            $arr4[] .= $res['card_number'];
                           
                            $arr5[] .= $res['money'];
                            if($res['type'] == 'in' ){
                               $arr6[] .= $res['discount'].'դր';
                            }else{
                             $arr6[] .= '___';
                            }
                            //$arr6[] .= $res['admin_points']+$res['client_points']+$res['agent_points']+$res['invite_points'];
                            if($res['type'] == 'in' ){
                               $arr7[] .= $res['money'] -  $res['discount'];
                               $arr8[] .= '___';
                            }else{
                                $arr7[] .= '___';
                                $arr8[] .= $res['money'];
                            }
                            $arr9[] .= $res['client_points'];
                            $arr10[] .= $res['agent_points'];
                            $arr11[] .= $res['invite_points'];
                            $arr12[] .= $res['date'];
                            if($user == "partner"){
                               if($res['type'] == 'in' ){
                                   $arr13[] .= $res['paid_admin'];
                                }else{
                                     $arr13[] .= $res['paid_partner'];
                                }
                            }else if($user == 'agent'){
                                $arr13[] .= $res['paid_agent'];
                            }
                            $arr14[] .= $res['admin_points'];
                            
                         }
                    }
              //  $filneme = str_replace(' ','_',$arr1[0]);
                require_once('../PHPExcel-1.8.1/Classes/PHPExcel.php');
                require_once('../PHPExcel-1.8.1/Classes/PHPExcel/Writer/Excel5.php');
                $xls = new PHPExcel();
                $xls->setActiveSheetIndex(0);
                $sheet = $xls->getActiveSheet();
                $sheet->setTitle('Շրջանառություն');
                $sheet->setCellValue("A1", 'Շրջանառություն');
                $sheet->getStyle('A1')->getFill()->setFillType( PHPExcel_Style_Fill::FILL_SOLID);
      
              
                $from = "A1";
                $to = "O1";
                $sheet->getColumnDimension('A')->setWidth(10);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(30);
                $sheet->getColumnDimension('F')->setWidth(30);
                $sheet->getColumnDimension('G')->setWidth(30);
                $sheet->getColumnDimension('H')->setWidth(30);
                $sheet->getColumnDimension('I')->setWidth(30);
                $sheet->getColumnDimension('J')->setWidth(30);
                $sheet->getColumnDimension('K')->setWidth(30);
                $sheet->getColumnDimension('L')->setWidth(30);
                $sheet->getColumnDimension('M')->setWidth(30);
                $sheet->getColumnDimension('N')->setWidth(30);
                $sheet->getColumnDimension('O')->setWidth(30);
                
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->mergeCells('A1:O1');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("$from:$to")->getFont()->setBold( true );
                $sheet->getStyle('A2:O2')->getFont()->setBold( true );
                $sheet->getStyle("$from:$to")->getFont()->setSize(20);
                $arr = ['','Անվանում','Հասցե','Կազմակերպության անուն','Քարտի համար','Գին','Զեզչ','Վճարվող գումար','Վճարվող բալ','Հաճախորդի միավոր','Գործակալի միավոր','Հրավիրողի միավոր','Գործարքի ամսաթիվ','Վճարման կարգավիճակ','VM միավոր'];
                $cols = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O"];
                
                 for($i = 2;$i<=count($arr1)+2;$i++){
                     if( $arr13[$i-3] == '1' ){
                         for($k = $i-3;$k<= 14;$k++){
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
                         $paidus = str_replace(1,'Վճարված',$arr13[$i-3] );
                     }else{
                         $paidus = str_replace(0,'Չվճարված',$arr13[$i-3]);

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
                 header ( "Content-Disposition: attachment; filename=uXfrmCASn.xls" );
                 $objWriter = new PHPExcel_Writer_Excel5($xls);
                 $objWriter->save('php://output');
                ?>