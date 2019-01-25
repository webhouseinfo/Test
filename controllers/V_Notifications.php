<?php

require 'system/controller.php';
require 'system/view.php';
require 'models/config.php';

class V_Notifications extends System_Controller
{
    public function answer()
    {
      
        if ($this->method() == 'POST') {
            $notification_id = $this->input['notification'];
            $answer = $this->input['answer'];
            $result = $this->db->mysqli->query("UPDATE Notifications set answer = '$answer', seen = '1'  WHERE id = '$notification_id' ");

        }
    }
}
