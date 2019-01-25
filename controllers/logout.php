<?php
require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';
class Logout extends System_Controller{
    function __construct() {
        parent::__construct();
        unset($_SESSION['admin']);
        $this->redirect('/vilmar_app/VilMarAdmin/login');
    }

}

