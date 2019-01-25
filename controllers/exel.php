<?
session_start();
require '../config.php\';
                $config = new Config;
                $id  = "'.$data_ids.'";
                $userType  = "'.$userType.'";
                if(stripos($id,\',\')){
                    $id1 = explode(\',\',$id);
                    for($i = 0;$i< count($id1);$i++){
                        $id =  (int)$id1[$i];
                        $row = $config->select("SELECT * FROM  users INNER JOIN user_network_data ON users.id = user_network_data.user_id WHERE  users.status = \'confirmed\'  AND users.userType = \'$userType\' AND 	id = \'$id\'   ");
                         foreach ($row as $res){                      
                       
                            $id1 = $res[\'userId\'];
                            $arr1[] .= $res[\'armName\'].\' \'.$res[\'armSurname\'];
                            $arr2[] .= $res[\'phone\'];   

                            $arr3[] .= $res[\'birthday\'];
                            $arr4[] .= $res[\'email\'];
                            $arr5[] .= $res[\'gender\'];
                            $arr6[] .= $res[\'contractNumber\'];
                            
                             
                             if(!empty($res[\'profitAgent\'])){
                                $arr7[] = $res[\'profitAgent\']
                             }else{
                                 $arr7[] = $res[\'percentAgent\']
                             }
                             $arr8[] .= $res[\'armCountry\'];
                             $arr9[] .= $res[\'address\'];
                             $arr10[] = $res[\'hvhh\'];
                             $arr11[] = $res[\'bankName\'];
                             $arr12[] = $res[\'bankNumber\'];
                             $arr13[] = $res[\'rating\'];
                             $arr14[] = $res[\'armDescription\'];
                             $arr15[] = $res[\'regDate\'];
                         }
                    }
                }
                require_once(\'PHPExcel-1.8.1/Classes/PHPExcel.php\');
                require_once(\'PHPExcel-1.8.1/Classes/PHPExcel/Writer/Excel5.php\');
                $xls = new PHPExcel();
                $xls->setActiveSheetIndex(0);
                $sheet = $xls->getActiveSheet();
                $sheet->setTitle(\'SOLD PRODUCTS\');
                $sheet->setCellValue("A1", \'SOLD PRODUCTS\');
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
                $sheet->getColumnDimension(\'O\')->setWidth(30);
                $sheet->getColumnDimension(\'P\')->setWidth(30);
                

                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->mergeCells(\'A1:P1\');
                $sheet->getStyle(\'A1\')->getAlignment()->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("$from:$to")->getFont()->setBold( true );
                $sheet->getStyle(\'A2:P2\')->getFont()->setBold( true );
                $sheet->getStyle("$from:$to")->getFont()->setSize(20);
                $arr = [\'\',\'Անվանում\',\'Հեռախոսահամար\',\'Ծննդյան օր\',\'Էլ հասցե\',\'սեռ\',\'Պայմ. համար\',\'Գործակալի աշ. ձև\',\'Երկիր\',\'Հասցե\',\'ՀՎՀՀ\',\'Բանկի անվանում\',\'Հաշվի համար\',\'Վարկանիշ\',\'Նկարագրություն\',\'Գրանցման օր\'];
                $cols = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P"];


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
                            ->setCellValue($cols[13].$i , $arr[13] )
                            ->setCellValue($cols[14].$i , $arr[14] )
                            ->setCellValue($cols[15].$i , $arr[15] )
                            ->setCellValue($cols[16].$i ,$arr[16] );
                    }else{
                         for($j = 0;$j< 17;$j++){
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
                            ->setCellValue($cols[13].$i , $arr13[$i-3] )
                            ->setCellValue($cols[14].$i , $arr14[$i-3] )
                            ->setCellValue($cols[15].$i , $arr15[$i-3] )
                            ->setCellValue($cols[16].$i , $arr16[$i-3] );
                            
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