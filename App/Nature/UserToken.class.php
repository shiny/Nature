<?php
    namespace Nature;
    class UserToken {
        private $timePassed = 1418464671;
    	private $codeMaps = "~!@#$%^&*()_+=-{}[]|\;,.<>?/abcdefghijklmnopqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    	private $key;
    	private $uid;
    	private $expire=604800;
    	function __construct($key=null){
        	if(!is_null($key)) {
            	$this->setKey($key);
        	}
    	}
    	function __toString(){
    		$uid = $this->num2code($this->uid);
    		
    		$expire = $this->num2code($this->expire - $this->timePassed);
    		//更短：过去之时不再有
    		
    		return $uid.':'.$this->encrypt($uid.':'.$expire);
    	}
    	function setExpire($time){
    		$this->expire = $time;
    		return $this;
    	}
    	function setKey($key){
    		$this->key = $key;
    		return $this;
    	}
    	function setUid($uid) {
    		$this->uid = $uid;
    		return $this;
    	}
    	function verify($token){
    		list($rawuid, $token) = explode(":", $token, 2);
    		$uid = $this->code2num($rawuid);
    		$data = $this->decrypt($token);
    		if(!$data || !strpos($data, ':')) {
    			return false;
    		}
    		list($encryptUid, $expire) = explode(":", $data, 2);
    		if($encryptUid !== $rawuid) {
    			return false;
    		}
    		$expire = $this->code2num($expire);
    		if(!is_numeric($expire) || ($expire + $this->timePassed) < time()){
    			return false;
    		}
    		return $uid;
    	}
    	function parseUid($token){
    		list($rawuid, $token) = explode(":", $token, 2);
    		return $this->code2num($rawuid);
    	}
    	function num2code($number) {
    		static $cache = [];
    		if(!isset($cache[$number])) {
    			$out   = "";
    			$len = strlen($this->codeMaps);
    			
    			while ($number >= $len) {
    				$key    = $number % $len;
    				$number = intval(floor($number / $len) - 1);
    				$out    = $this->codeMaps{$key}.$out;
    			}
    			$cache[$number] = $this->codeMaps{$number}.$out;
    		}		
    		return $cache[$number];
    	}
    	function code2num($code) {
    		static $cache = [];
    		if(!isset($cache[$code])) {
    			$len = strlen($this->codeMaps);
    			$codelen = strlen($code);
    			$num = 0;
    			$i = $codelen;
    		  	for($j=0; $j<$codelen; $j++){
    				$i--;
    				$char = $code{$j};
    				$pos = strpos($this->codeMaps, $char);
    				$num += (pow($len, $i) * ($pos + 1));
    			}
    			$num--;
    			$cache[$code] = intval($num);
    		}
    		return $cache[$code];
    	}
    	function encrypt($data) {
    		$encrypt = openssl_encrypt($data, 'bf-ecb', $this->key, OPENSSL_RAW_DATA);
    		return base64_encode($encrypt);
    	}
    	function decrypt($str) {
    		$str = base64_decode($str);
    		$str = openssl_decrypt($str, 'bf-ecb', $this->key, OPENSSL_RAW_DATA);
    		return $str;
    	}
    }