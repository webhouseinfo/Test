<?php

    class System_View{
        function render($file,$layout=TRUE){
           // var_dump($file);die;
            if($file == 'login_html'){
                include_once 'views/'.$file.'.php';

            }else if(file_exists('views/'.$file.'.php')){
                if($layout){
                    include_once 'views/layout/header.php';
                    include_once 'views/'.$file.'.php';
                    include_once 'views/layout/footer.php';
                }else{
                    include_once 'views/'.$file.'.php';
                }
            }else{
                echo 'error:view not found';
            }
        }
        function __get($name){
            $this->$name=false;
        }
        function __set($name,$value){
            $this->$name=$value;
            
        }
    }

