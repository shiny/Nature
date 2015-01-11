<?php
    if(file_exists(APP_DIR.'/template.function.php')) {
        require_once APP_DIR.'/template.function.php';
    }
    function truncate($str, $len, $dot='……'){
        if(mb_strlen($str, 'utf-8') > $len){
            return mb_substr($str, 0, $len, 'utf-8').$dot;
        } else {
            return $str;
        }
    }
    function defaults($value, $default_value){
        return empty($value) ? $default_value : $value;
    }
    function block($block_name){
        if(function_exists('block_'.$block_name)) {
            $args = func_get_args();
            $func = array_shift($args);
            call_user_func_array('block_'.$func, $args);
        }
    }