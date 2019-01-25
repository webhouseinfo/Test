<?
                session_start();
                require '../models/config.php';
                $config = new Config;
                $id  = "all";
                $userType  = "partner";
                if($id == 'all'){
                    $id1 = [];
                     $rowId = $config->select("SELECT id FROM  users  WHERE  status = 'confirmed'  AND userType = '$userType'  ");
                     foreach($rowId as $resid){
                         if(!in_array($resid['id'],$id1)){
                            $id1[] = $resid['id'];
                         }
                     }
                }else if(stripos($id,',')){
                    $id1 = explode(',',$id);
                }else{
                   $id1 = [];
                   $id1[] = $id;
                }
                $arrIdFor = [];
                for($i = 0;$i< count($id1);$i++){
                    $id =  (int)$id1[$i];
                    $row = $config->select("SELECT users.*,user_network_data.user_id,user_network_data.address FROM  users INNER JOIN user_network_data ON users.id = user_network_data.user_id WHERE  users.status = 'confirmed'  AND users.userType = '$userType' AND 	users.id = '$id'   ");
                     foreach ($row as $res){
                         if(!in_array($res['id'],$arrIdFor)){     
                             $arraddr = $config->select("SELECT address FROM  user_network_data  WHERE  user_id = '".$res['id']."'  ");  
                             $address = [];
                             foreach($arraddr as $add){
                                   $address[] = $add['address'];
                             }     
                             $arr1[] .= $res['armName'].' '.$res['armSurname'];
                             $arr2[] .= $res['phone'];   
                             $arr3[] .= $res['birthday'];
                             $arr4[] .= $res['email'];
                             $arr5[] .= str_replace('female','իգական',str_replace('male','արական',$res['gender']));
                             $arr6[] .= $res['contractNumber'];
                             $arr7[] .= $res['armCountry'];
                             $arr8[] .= join(',',$address);
                             $arr9[] = $res['hvhh'];
                             $arr10[] = $res['bankName'];
                             $arr11[] = $res['bankNumber'];
                               if($userType == 'customer'){
                                    $arrtype = $config->select("SELECT type FROM  cards  WHERE  user_id = '".$res['id']."'  ");  
                                    $arrtype = mysqli_fetch_array($arrtype);
                                    $arr12[] = str_replace('gift','Նվեր',str_replace('normal','Սովորական',$arrtype['type']));
                                    
                               }else{
                                    $arr12[] = $res['armDescription'];
                               }
                             
                             $arr13[] = $res['regDate'];
                             $arrIdFor[] = $res['id'];
                         }
                     }
                }
            
                require_once('../PHPExcel-1.8.1/Classes/PHPExcel.php');
                require_once('../PHPExcel-1.8.1/Classes/PHPExcel/Writer/Excel5.php');
                $xls = new PHPExcel();
                $xls->setActiveSheetIndex(0);
                $sheet = $xls->getActiveSheet();
                $sheet->setTitle('Անձնական տվյալներ');
                $sheet->setCellValue("A1", 'Անձնական տվյալներ');
                $sheet->getStyle('A1')->getFill()->setFillType( PHPExcel_Style_Fill::FILL_SOLID);
              //  $sheet->getStyle('A1')->getFill()->getStartColor()->setRGB('3c3b6e');
                $from = "A1";
                $to = "P1";
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
                
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->mergeCells('A1:N1');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("$from:$to")->getFont()->setBold( true );
                $sheet->getStyle('A2:N2')->getFont()->setBold( true );
                $sheet->getStyle("$from:$to")->getFont()->setSize(20);
                if($userType == 'customer'){
                    $field = 'Քարտի տեսակ';
                }else{
                    $field = 'Նկարագրություն';
                }
                $arr = ['','Անվանում','Հեռախոսահամար','Ծննդյան օր','Էլ հասցե','սեռ','Պայմ. համար','Երկիր','Հասցե','ՀՎՀՀ','Բանկի անվանում','Հաշվի համար',$field,'Գրանցման օր'];
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
                 header ( "Content-Disposition: attachment; filename=y6sdqH0Qn.xls" );
                 $objWriter = new PHPExcel_Writer_Excel5($xls);
                 $objWriter->save('php://output');
                ?>