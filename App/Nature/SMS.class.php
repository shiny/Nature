<?php
    class yunpian_sms {
        private $gateway = 'http://yunpian.com/v1/sms/tpl_send.json ';
        private $apikey;
        private $var;
        private $tpls;
        function __setup($configure=[]){
            $this->apikey = $configure['apikey'];
            $this->var = isset($configure['default_var']) ? $configure['default_var'] : [];
            $this->tpls = $configure['tpl_types'];
        }
        function send($phone, $type, $var=[]){
            foreach($var as $key=>$value){
                $this->var['#'.$key.'#'] = $value;
            }
            $tpl_id = $this->tpls[$type];
            $tpl_value = http_build_query($this->var);
            $params = [
                'apikey'=>$this->apikey,
                'mobile'=>$phone,
                'tpl_id'=>$tpl_id,
                'tpl_value'=>$tpl_value
            ];
            $ch = singleton('cURL');
            $result = $ch->post($this->gateway, $params, 'json');
            if($result && isset($result['code'])){
                if($result['code']==0) {
                    return true;
                } else {
                    throw new smsSendFailedException($result['msg'], $result['code']);
                }
            } else {
                throw new smsSendFailedException();
            }
            return false;
        }
    }
    class SMS {
        private $gateway;
        private $var=[];
        function __setup($configure=[]){
            $gateway_name = $configure['gateway'];
            $this->gateway = singleton($gateway_name);
            $this->var = isset($configure['default_var']) ? $configure['default_var'] : [] ;
        }
        function session_start() {
            if (!session_id()) {
                session_start();
            }
        }
        function send($phone, $type, $code) {
            $this->saveToSession($code);
            $var = array_merge($this->var, [
                'code'=>$code
            ]);
            return $this->gateway->send($phone, $type, $var);
        }
        function isMatch($code) {
            $this->session_start();
            if(!isset($_SESSION['sms_code'])) {
                return false;
            } else {
                return strtolower($code) === strtolower($_SESSION['sms_code']);
            }
        }
        function saveToSession($code) {
            $this->session_start();
            $_SESSION['sms_code'] = $code;
        }
    }
    class smsSendFailedException extends \Exception {}