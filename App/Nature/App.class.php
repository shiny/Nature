<?php
    namespace Nature;
    
    /**
     * nature library 核心类
     */
    require_once __DIR__.'/nature.function.php';
    class App {
        static $configure=[];
        function __construct($app_dir=null) {
            if (is_null($app_dir)) {
                $app_dir = realpath(__DIR__.'/../');
            }
            if(!defined('APP_DIR')) {
                define('APP_DIR', $app_dir);
            }
            set_include_path(get_include_path().':'.__DIR__);
            $this->load_config();
            spl_autoload_register([$this, 'autoloader']);
            set_exception_handler([$this, 'exception_handler']);
        }
        function autoloader($className) {
            $className = trim($className, "\\");
            $file = APP_DIR
                    .DIRECTORY_SEPARATOR
                    .str_replace("\\", DIRECTORY_SEPARATOR, $className)
                    .'.class.php';
            $natureFile = APP_DIR.DIRECTORY_SEPARATOR.
                            __NAMESPACE__.$className.'.class.php';
            $files = [$file, $natureFile];
            foreach($files as $file){
                if(file_exists($file)){
                    require($file);
                    break;
                }
            }
        }
        function run() {
            $this->call_controller();
            $this->call_function();
        }
        function exception_handler($exception){
            if(!is_a($exception, 'Nature\HTTPException')) {
                http_response_code(500);
                $tpl = singleton('tpl');
                $tpl->assign('msg', $exception->getMessage());
                $tpl->display('500.html');
            }
        }
        function load_config(){
            $cfg = require(__DIR__.'/configure.php');
            $user_configure = [];
            if(file_exists(APP_DIR.'/configure.php')) {
                $user_configure = include(APP_DIR.'/configure.php');
            }
            self::$configure = array_replace_recursive($cfg, $user_configure);
            return self::$configure;
        }
        function rest($object=null){
            $method = strtolower($_SERVER['REQUEST_METHOD']);
            $types = array(
                'post'=>$_POST,
                'get'=>$_GET,
                'delete'=>$_REQUEST,
                'put'=>$_REQUEST
            );
            $params = $types[$method];
            if(!is_null($object)) {
                $method = [$object, $method];
            }
            if(is_callable($method)){
                $returnData = call_user_func($method, $params);
                
                switch (gettype($returnData)) {
                    case 'array':
                        echo json_encode($returnData);
                        break;
                    case 'string':
                    case 'integer':
                    case 'float':
                    case 'double':
                        echo $returnData;
                        break;
                }
                
            }
        }
        /**
         * alias of rest
         */
        function call_function(){
            $this->rest();
        }
        function call_controller() {
            foreach (get_declared_classes() as $class) {
                $reflection = new \ReflectionClass($class);
                if($reflection->isSubclassOf('Nature\\Controller') && !$reflection->isAbstract()) {
                    $obj = $reflection->newInstance();
                    $this->rest($obj);
                }
            }
        }
    }