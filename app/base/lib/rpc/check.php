<?php
class base_rpc_check{

    function handshake(){
        if(base_kvstore::instance('ecos')->fetch('net.handshake',$value)){
            echo $value;
        }else{
            $code = md5(microtime());
            base_kvstore::instance('ecos')->store('net.handshake',$code);
            echo $code;
        }
    }

    function login_hankshake()
    {
        if(base_kvstore::instance('ecos')->fetch('net.login_handshake',$value)){
            echo $value;
        }else{
            $code = md5(microtime());
            base_kvstore::instance('ecos')->store('net.login_handshake',$code);
            echo $code;
        }
    }

    function check_sys(){
        kernel::single('dev_command_syscheck')->command_allcheck();  
    }
}
