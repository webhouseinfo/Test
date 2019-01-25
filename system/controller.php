<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

class System_Controller
{
    protected $view;
    protected $input;
    protected $db;
    protected $lang = [];

    public function __construct()
    {
        include 'lib/helpers.php';
        date_default_timezone_set("Asia/Yerevan");

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $tmp_post = json_decode(file_get_contents('php://input'), true);
            if ($tmp_post) {
                $this->input = $this->clean_vars($tmp_post);
            } else {
                $this->input = $this->clean_vars($_POST);
            }
        }
        $this->db = new Config();
        $this->update_languages();

        $this->view = new System_View();
    }

    protected function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    protected function update_languages()
    {
        $lang_list = $this->db->mysqli->query('SELECT * FROM lang')->fetch_all(true);

        foreach ($lang_list as $item) {
            $this->lang[$item['key']][$item['alias']] = $item['text'];
        }

        return $lang_list;
    }

    protected function load($class_name)
    {
        $obj = new $class_name();

        return $obj;
    }

    public function clean_vars($data)
    {
        foreach ($data as $key => &$val) {
            $data[$key] = $val;
        }

        return $data;
    }

    public function redirect($url)
    {
        header("Location:$url");
    }

    public function response($data)
    {
        echo json_encode($data);exit;
        $prefix = '';
        foreach($data as $row) {
        echo '[';
          echo $prefix, json_encode($row);
          $prefix = ',';
        echo ']';
        }
       
    }
}
