<?php

class Config
{
    public $mysqli;
    public $host = "localhost";
    public $user = "webhouseinfo18_aaa";
    public $password = "Ab&KGHdA*PGH";
    public $dbname = "webhouseinfo18_cft_db";
    public $charset = 'utf8';
    function __construct()
    {
        $con = $this->mysqli = new Mysqli($this->host, $this->user, $this->password, $this->dbname);
        mysqli_set_charset($con, "$this->charset");
    }
   
    /*function select for ALL*/
    public function select($q)
    {
        $select = $this->mysqli->query($q);
        return $select;
    }
     /*delete function  for ALL */
    public function delete($val,$table,$field){
        return  $this->mysqli->query("DELETE FROM $table  WHERE $field = '$val'");
    }
    public function insert($table,$arr){
        $fields=[];
        $vals=[];
        foreach($arr as $key=>$val){
                $fields[]=$key;
                $vals[]=$val;
        }	
        $fields=join(',',$fields);
        $vals=join("','",$vals);
        return $this->mysqli->query("INSERT IGNORE INTO $table ($fields) VALUES ('$vals')");
    }
    public function update($table,$arr, $where){
        $fields = array();
        foreach($arr as $key=>$val){
              $fields[] = $key.'='.'"'.$val.'"';
        }	
        $fields=join(',',$fields);
        return $this->mysqli->query("UPDATE $table SET $fields WHERE $where");
    }
        /*generait new user ID*/
    public function generatePassword($length = 8)
    {
        $chars = '0123456789';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }
        return $string;
    }

}

/*
$obj = new Config;
//$arrsel = $obj->select("SELECT * FROM   cards ");
//$arr = [];
//$arr1 = [];
//foreach($arrsel as $res){
  //  if(in_array($res['qrImage'],$arr1)){
    //    echo $res['id'].'   '.$res['qrImage'];
    //}
    //$arr[] = $res['id']; $arr1[] = $res['qrImage'];
//}

include "qrlib.php";
//if(isset($_POST['go'])){
for($i = 1;$i<=5000;$i++){
//$i = $_POST['number'];
    $code1 = $obj->generatePassword(4);
    $code2 = $obj->generatePassword(4);
    if(strlen($i)<4){
        $t = '';
        for($j =0;$j<4-strlen($i);$j++){
            $t = $t.'0';
            
        }
        $end = $t.$i;
    }else{
        $end = $i;
    }
    $code = $code1.'_'.$code2.'_'.'0000'.'_'.$end;
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    //html PNG location prefix
    $PNG_WEB_DIR = 'temp/';
    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR))
         mkdir($PNG_TEMP_DIR);
  //  $namepng = md5(mt_rand(0, 100000000));
       $namepng= $code;
     $filename = $PNG_TEMP_DIR.$namepng.'.png';
       $errorCorrectionLevel = 'L';
       // $errorCorrectionLevel = 'H' ;
        $matrixPointSize = 10;
         $matrixPointSize = min(max((int)4, 1), 10);
        //  if (trim($code) == '')
        //     die('data cannot be empty! <a href="?">back</a>');
            
    
        $filename = $PNG_TEMP_DIR.$code.'.png';
        QRcode::png($code, $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        $file = basename($filename);
        $pass = $obj->generatePassword(4);
        $obj->insert('cards', array('giveUserId' => '','acceptUserId'=>'','cardNumber' => $code,'amountDeals'=>'','isApp'=>'','unit'=>'','used'=>'0','qashIn'=>'','qashOut'=>'', 'qrImage' => $file,'password'=> $pass,'insertDate'=>''));
          
       //echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" /><hr/>'; die;
//}
  
}

*/



/*
    

    
    //set it to writable location, a place for temp generated PNG files
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    
    //html PNG location prefix
    $PNG_WEB_DIR = 'temp/';

    include "qrlib.php";    
    
    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);
    
    
    $filename = $PNG_TEMP_DIR.'test.png';
    
    //processing form input
    //remember to sanitize user input in real-life solution !!!
    $errorCorrectionLevel = 'L';
    if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L','M','Q','H')))
        $errorCorrectionLevel = $_REQUEST['level'];    

    $matrixPointSize = 4;
    if (isset($_REQUEST['size']))
        $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 10);


    if (isset($_REQUEST['data'])) { 
    
        //it's very important!
        if (trim($_REQUEST['data']) == '')
            die('data cannot be empty! <a href="?">back</a>');
            
        // user data
        $filename = $PNG_TEMP_DIR.'test'.md5($_REQUEST['data'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        QRcode::png($_REQUEST['data'], $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
    } else {    
    
        //default data
        echo 'You can provide data in GET parameter: <a href="?data=like_that">like that</a><hr/>';    
        QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
    }    
        
    //display generated file
    echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" /><hr/>';  
    
    //config form
    echo '<form action="index.php" method="post">
        Data:&nbsp;<input name="data" value="'.(isset($_REQUEST['data'])?htmlspecialchars($_REQUEST['data']):'PHP QR Code :)').'" />&nbsp;
        ECC:&nbsp;<select name="level">
            <option value="L"'.(($errorCorrectionLevel=='L')?' selected':'').'>L - smallest</option>
            <option value="M"'.(($errorCorrectionLevel=='M')?' selected':'').'>M</option>
            <option value="Q"'.(($errorCorrectionLevel=='Q')?' selected':'').'>Q</option>
            <option value="H"'.(($errorCorrectionLevel=='H')?' selected':'').'>H - best</option>
        </select>&nbsp;
        Size:&nbsp;<select name="size">';
        
    for($i=1;$i<=10;$i++)
        echo '<option value="'.$i.'"'.(($matrixPointSize==$i)?' selected':'').'>'.$i.'</option>';
        
    echo '</select>&nbsp;
        <input type="submit" value="GENERATE"></form><hr/>';
        
    // benchmark
    QRtools::timeBenchmark();    
  
   // echo "<h1>PHP QR Code</h1><hr/>";
    
//     //set it to writable location, a place for temp generated PNG files
//     $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    
//     //html PNG location prefix
//     $PNG_WEB_DIR = 'temp/';

//     include "qrlib.php";    
    
//     //ofcourse we need rights to create temp dir
//     if (!file_exists($PNG_TEMP_DIR))
//         mkdir($PNG_TEMP_DIR);
//     $namepng = md5(mt_rand(0, 100000000));
//     $filename = $PNG_TEMP_DIR.$namepng.'.png';
    
//     //processing form input
//     //remember to sanitize user input in real-life solution !!!
//     $errorCorrectionLevel = 'L';
//   // if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L','M','Q','H')))
//         $errorCorrectionLevel = 'H' ;//'$_REQUEST['level']';    

//     $matrixPointSize = 4;
//   // if (isset($_REQUEST['size']))
//   //     $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 10);
// $matrixPointSize = 4;

//   // if (isset($_REQUEST['data'])) { 
    
//         //it's very important!
//         if (trim('22cf0d95d528b5924f62f62ecc85e7ab') == '')
//             die('data cannot be empty! <a href="?">back</a>');
            
//         // user data
//         $filename = $PNG_TEMP_DIR.'test'.md5($_REQUEST['data'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
//         QRcode::png('22cf0d95d528b5924f62f62ecc85e7ab', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
    // } else {    
    
    //     //default data
    //   // echo 'You can provide data in GET parameter: <a href="?data=like_that">like that</a><hr/>';    
    //     QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
    // }    
       // var_dump(basename($filename));
    //display generated file
   // echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" /><hr/>';  
    
    //config form
      
    // benchmark
   // QRtools::timeBenchmark();    
?>
