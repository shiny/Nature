<?php
    namespace Nature;
    abstract class Controller {
        protected $tpl;
        function __construct() {
            $properties = ['db', 'tpl'];
            foreach($properties as $property){
                if(property_exists($this, $property)) {
                    $this->$property = singleton($property);
                }
            }
        }
        function get() {
            if($this->tpl->exists()){
                $this->display();
            } else {
                throw new HTTPException("Page Not Found", 404);
            }
        }
        function assign() {
            call_user_func_array([$this->tpl, 'assign'], func_get_args());
        }
        function display($var=null){
            $this->tpl->display($var);
        }
    }