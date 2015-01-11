<?php 
    namespace Nature;
    class VerifyCode {
        private $words = [];
        private $font;
        private $color;
        function __setup($configure=[]){
            //putenv('GDFONTPATH=' . realpath('.'));
            $txt = file_get_contents($configure['words']);
            $this->words = explode("\n", $txt);
            $this->font = $configure['font'];
        }
        function init(){
            $this->width = 200;
            $this->height = 50;
            $this->font_size = $this->height * 0.75; 
            $randIndex = array_rand($this->words);
            $this->word = ucfirst($this->words[$randIndex]);
            $this->image = imagecreate($this->width, $this->height);
            /* 设置背景、文本和干扰的噪点 */ 
            $background_color = imagecolorallocate($this->image, 228, 242, 234);
            list($red, $green, $blue) = $this->hexrgb("0x47926a");
            $this->color = imagecolorallocate($this->image, $red, $green, $blue);
        }
        function drawLines($count){
            for( $i=0; $i<$count; $i++ ) {
                imageline($this->image, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width), mt_rand(0, $this->height), $this->color);
            }
        }
        function drawDots($count){
            for ( $i=0; $i<$count; $i++) {
                imagefilledellipse($this->image, mt_rand(0, $this->width), mt_rand(0, $this->height), 3, 3, $this->color);
            }
        }
        function drawText(){
            $textbox = imagettfbbox($this->font_size, 0, $this->font, $this->word); 
            $x = ($this->width - $textbox[4])/2;
            $y = ($this->height - $textbox[5])/2;
            imagettftext($this->image, $this->font_size, 0, $x, $y, $this->color, $this->font , $this->word);
        }
        function draw(){
            $this->init();
            $this->drawLines(10);
            $this->drawDots(80);
            $this->drawText();
        }
        function session_start(){
            if (!session_id()) {
                session_start();
            }
        }
        function verifyTimes($times=null){
            $this->session_start();
            if(is_numeric($times)) {
                $_SESSION['verify_code_times'] = $times;
            } else if(isset($_SESSION['verify_code_times'])) {
                $_SESSION['verify_code_times']++;
            }
            return $_SESSION['verify_code_times'];
        }
        function isMatch($verify_code){
            $this->session_start();
            return isset($_SESSION['verify_code']) && strtolower($verify_code) === strtolower($_SESSION['verify_code']);
        }
        function saveToSession(){
            $this->session_start();
            $_SESSION['verify_code'] = $this->word;
        }
        function destroySession(){
            
        }
        function display(){
            header('Content-Type: image/jpeg');// 设定图片输出的类型
            imagejpeg($this->image, null, 95);//显示图片
            imagedestroy($this->image);//销毁图片实例
        }
        function hexrgb ($hexstr) {
            $int = hexdec($hexstr);
            return array( 
                        0xFF & ($int >> 0x10),
                        0xFF & ($int >> 0x8),
                        0xFF & $int
                    );
        }
    }