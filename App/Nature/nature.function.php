<?php
    
    namespace Nature{
        //nature 工具函数
        
        /**
         * 重定向
         * !!会打断执行流程，直接退出页面
         *
         * @param string $url 要跳转的网址
         * @param int $status http 状态码
         */
        function redirect($url, $status=302){
            http_response_code($status);
            header('Location: '.$url);
            exit;
        }
        
        function config($name, $value=null){
            if(strpos($name, '.')===false) {
                $type = $name; 
                $name = false;
            } else {
                list($type, $name) = explode('.', $name);
            }
            if(is_null($value)){
                if($name===false) {
                    if(isset(App::$configure[$type])) {
                        return App::$configure[$type];
                    }
                } else {
                    if(isset(App::$configure[$type][$name])) {
                        return App::$configure[$type][$name];
                    }
                }
            } else {
                if (!isset(App::$configure[$type])) {
                    App::$configure[$type] = [];
                }
                if(($name===false)){
                    App::$configure[$type] = $value;
                } else {
                    App::$configure[$type][$name] = $value;
                }
            }
        }
        
        
        function singleton($className, $renewal=false){
            if(is_array($className)) {
                $objects = [];
                foreach($className as $class){
                    $objects[] = singleton($class);
                }
                return $objects;
            }
            
            static $instances = [];
            static $alias = [
                'tpl'=>'Nature\\Template',
                'db'=>'Nature\\MySQL',
                'mysql'=>'Nature\\MySQL'
            ];
            if(isset($alias[$className])) {
                $className = $alias[$className];
            }
            /**
             * 初始化实例
             */
            $setup = function ($className, $cfg) {
                $object = new $className();
                if(method_exists($object, '__setup')) {
                    $cfg_key = strtolower($className);
                    $cfg = config($cfg_key);
                    if($cfg===false) {
                        throw new \Exception($className.' Need a Configure "'.$cfg_key.'"');
                    } else {
                        call_user_func([$object, '__setup'], $cfg);
                    }
                }
                return $object;
            };
            
            /**
             * 结束实例
             */
            $teardown = function ($instance){
                if(method_exists($instance, '__teardown')) {
                    call_user_func([$instance, '__teardown']);
                }
                unset($instance);
            };
            
            if (!isset($instances[$className]) || $renewal) {
                global $cfg;
                if(isset($instances[$className])) {
                    $teardown($instances[$className]);
                }
                $instances[$className] = $setup($className, $cfg);
            }
            return $instances[$className];
        }
    }
    //register shortcut
    namespace {
        function redirect($url, $status=302){
            return Nature\redirect($url, $status);
        }
        function config($name, $value=null){
            return Nature\config($name, $value);
        }
        function singleton($className, $renewal=false){
            return Nature\singleton($className, $renewal);
        }
    }