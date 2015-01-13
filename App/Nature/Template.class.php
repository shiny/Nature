<?php
    namespace Nature;
    class Template {
        private $root;
        private $values=[];
        function __setup($configure){
            $this->root = $configure['root'];
            set_include_path(get_include_path().':'.$this->root);
        }
        function assign($key, $value=null){
            if(is_array($key)) {
                $this->values = array_merge($this->values, $key);
            } else {
                $this->values[$key] = $value;
            }
        }
        function get_template_path($file=null){
            $dir = dirname($_SERVER['SCRIPT_NAME']);
            if(is_null($file)){
                $file = basename($_SERVER['SCRIPT_NAME'], '.php').'.html';
            }
            $file = $this->root.$dir.'/'.$file;

            if (realpath($file)) {
                return realpath($file);
            } else {
                return realpath($file.'.html');
            }
        }
        function exists($file=null){
            $file = $this->get_template_path($file);
            return file_exists($file);
        }
        function display($file=null){
            $file = $this->get_template_path($file);
            if($file===false) {
                throw new \Exception('Template Not Found');
            }
            extract($this->values);
            include_once(__DIR__.'/template.function.php');
            require($file);
        }
    }