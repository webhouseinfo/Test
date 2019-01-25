<?php
    class System_routes
    {
        private $parts;

        public function __construct($url)
        {
            $this->parts = explode('/', $url);
            $this->run();
        }

        private function run()
        {
            if (!empty($this->parts[0])) {
                if (strrpos($this->parts[0], '?')) {
                    $item = explode('?', $this->parts[0]);
                    $ctrl = $item[0];
                } else {
                    $ctrl = $this->parts[0];
                }

                if (file_exists('controllers/'.$ctrl.'.php')) {
                    include 'controllers/'.$ctrl.'.php';

                    if (class_exists(ucfirst($ctrl))) {
                        $ctrl_obj = new $ctrl();
                        if (!empty($this->parts[1])) {
                            if (strrpos($this->parts[1], '?')) {
                                $item = explode('?', $this->parts[1]);
                                $method = $item[0];
                            } else {
                                $method = $this->parts[1];
                            }
                            if (method_exists($ctrl_obj, $method)) {
                                if (!empty($this->parts[2])) {
                                    unset($this->parts[0]);
                                    unset($this->parts[1]);
                                    call_user_func_array(array($ctrl_obj, $method), $this->parts);
                                } else {
                                    $ctrl_obj->$method();
                                }
                            } else {
                                echo 'method not found';
                            }
                        } else {
                            $ctrl_obj->index();
                        }
                    } else {
                        echo 'class not exists';
                    }
                } else {
                    header('Location:http://vilmar.am/vilmar_app/VilMarAdmin/home');
                }
            }
        }
    }
