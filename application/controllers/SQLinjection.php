<?php
class sqlinj{
        public function blocksqlinjection($str)
        {
            return str_replay(array("'",'"',"''"), array('&quot;','&quot;'),$str);


        }



}






?>