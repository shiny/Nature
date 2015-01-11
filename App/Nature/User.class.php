<?php
    namespace Nature;
    class User{
        /**
         * 密码加密
         * @param string $password 密码
         * @param string $salt 加盐
         */
        public static function encrypt($password, $salt){
            return sha1(sha1($password).$salt).':'.$salt;
        }
        public static function getSalt($encrypted_string){
            $arr = explode(':', $encrypted_string);
            if(count($arr) < 2){
                throw new \Exception('Unexpected encrypted strings');
            }
            return $arr[1];
        }
        public static function check($password, $encrypted_string){
            $salt = self::getSalt($encrypted_string);
            $calculated = self::encrypt($password, $salt);
            return $calculated === $encrypted_string;
        }
        /**
         * 用户登录
         * @param string $user 用户名
         * @param string $password 密码
         */
        function login($user, $password){
            if(isset($this->data[$user])){
                if(!self::check($password, $this->data[$user])) {
                    throw new PasswordIsWrongException();
                } else {
                    return true;
                }
            } else {
                throw new UserNotFoundException();
            }
        }
        function set_login(){
            
        }
        function has_login(){
            
        }
    }
    /**
     * 登录异常
     */
    class loginException extends \Exception{ }
    /**
     * 密码错误
     */
    class passwordIsWrongException extends loginException{ 
        protected $message = '密码错误';
    }
    /**
     * 用户不存在
     */
    class userNotFoundException extends loginException{
        protected $message = '用户不存在'; 
    }
    /**
     * 用户被禁用
     */
    class userIsStoppedException extends loginException{
        protected $message = '用户被停用'; 
    }