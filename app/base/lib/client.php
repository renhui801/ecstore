<?php
define('HTTP_TIME_OUT', -3);
define('PRISM_SDK_VER', "1.0");

class base_client{

    public $timeout = 10;
    public $defaultChunk = 4096;
    public $http_ver = '1.1';
    public $hostaddr = null;
    public $proxyHost = null;
    public $proxyPort = null;
    public $default_headers = array(
        'Pragma'=>"no-cache",
        'Cache-Control'=>"no-cache",
        );
    public $is_websocket = false;
    public $logfunc = null;
    public $hostport = null;
    public $callback = null;
    public $responseHeader = null;
    public $responseBody = null;
    public $is_keepalive = true;
    private $handles = array();

    function __construct(){
        $this->default_headers["User-Agent"] = "PrismSDK/PHP ".PRISM_SDK_VER;
        $this->register_handler(302, array($this, 'handle_redirect'));
        $this->register_handler(301, array($this, 'handle_redirect'));
    }

    public function register_handler($type, $func){
        $this->handles[$type] = $func;
    }

    public function action($action, $url, $headers=null, $data=null){
        $url_info = parse_url($url);
        $request_query = (isset($url_info['path'])?$url_info['path']:'/').(isset($url_info['query'])?'?'.$url_info['query']:'');
        $request_server = $request_host = $url_info['host'];
        $request_port = (isset($url_info['port'])?$url_info['port']:80);

        $out = strtoupper($action).' '.$request_query." HTTP/{$this->http_ver}\r\n";
        $out .= 'Host: '.$request_host.($request_port!=80?(':'.$request_port):'')."\r\n";

        if($data){
            if(is_array($data)){
                $data = http_build_query($data);
                if(!isset($headers['Content-Type'])){
                    $headers['Content-Type'] = 'application/x-www-form-urlencoded';
                }
            }
            $headers['Content-length'] = strlen($data);
        }


        if(!isset($headers["Connection"])){
            $headers["Connection"] = $this->is_keepalive?"Keep-alive":"close";
        }
        $headers = array_merge($this->default_headers, (array)$headers);

        foreach((array)$headers as $k=>$v){
            $out .= $k.': '.$v."\r\n";
        }
        $out .= "\r\n";
        if($data){
            $out .= $data;
        }
        $data = null;

        $this->responseHeader = array();
        if($this->proxyHost && $this->proxyPort){
            $request_server = $this->proxyHost;
            $request_port = $this->proxyPort;
            $this->log('Using proxy '.$request_server.':'.$request_port.'. ');
        }

        if($this->hostaddr){
            $request_addr = $this->hostaddr;
        }else{
            if(!$this->is_addr($request_server)){
                $this->log('Resolving '.$request_server.'... ',true);
                $request_addr = gethostbyname($request_server);
                $this->log($request_addr);
            }else{
                $request_addr = $request_server;
            }
        }
        if($this->hostport){
            $request_port = $this->hostport;
        }

        $this->log(sprintf('Connecting to %s|%s|:%s... connected.',$request_server,$request_addr,$request_port));
        if($fp = @fsockopen($request_addr,$request_port,$errno, $errstr, $this->timeout)){

            if($this->timeout && function_exists('stream_set_timeout')){
                $this->read_time_left = $this->read_time_total = $this->timeout;
            }else{
                $this->read_time_total = null;
            }

            $sent = fwrite($fp, $out);

            $this->log('HTTP request sent, awaiting response... ',true);
            $this->request_start = $this->microtime();

            $out = null;

            $this->responseBody = '';
            if(HTTP_TIME_OUT === $this->readsocket($fp,512,$status,'fgets')){
                return HTTP_TIME_OUT;
            }

            if(preg_match('/\d{3}/',$status,$match)){
                $this->responseCode = $match[0];
            }

            $this->log($this->responseCode);
            while (!feof($fp)){
                if(HTTP_TIME_OUT === $this->readsocket($fp,512,$raw,'fgets')){
                    return HTTP_TIME_OUT;
                }
                $raw = trim($raw);
                if($raw){
                    if($p = strpos($raw,':')){
                        $this->responseHeader[strtolower(trim(substr($raw,0,$p)))] = trim(substr($raw,$p+1));
                    }
                }else{
                    break;
                }
            }

            if(isset($this->handles[$this->responseCode]) && is_callable($this->handles[$this->responseCode])){
                return call_user_func($this->handles[$this->responseCode], $this, $fp);
            }else{
                return $this->default_handler($fp);
            }
        }else{
            return false;
        }
    }

    private function handle_redirect($self, $fp){
            $this->log(" Redirect \n\t--> ".$this->responseHeader['location']);
            if(isset($this->responseHeader['location'])){
                return $this->action($action,$this->responseHeader['location'],$headers,$callback);
            }else{
                return false;
            }
    }

    private function default_handler($fp){
        $chunkmode = (isset($this->responseHeader['transfer-encoding']) && $this->responseHeader['transfer-encoding']=='chunked');
        if($chunkmode){
            if(HTTP_TIME_OUT === $this->readsocket($fp,30,$chunklen,'fgets')){
                return HTTP_TIME_OUT;
            }
            $chunklen = hexdec(trim($chunklen));
        }elseif(isset($this->responseHeader['content-length'])){
            $chunklen = min($this->defaultChunk,$this->responseHeader['content-length']);
        }else{
            $chunklen = $this->defaultChunk;
        }

        while (!feof($fp) && $chunklen){
            if(HTTP_TIME_OUT ===$this->readsocket($fp,$chunklen,$content)){
                return HTTP_TIME_OUT;
            }
            $readlen = strlen($content);
            while($chunklen!=$readlen){
                if(HTTP_TIME_OUT === $this->readsocket($fp,$chunklen-$readlen,$buffer)){
                    return HTTP_TIME_OUT;
                }
                if(!strlen($buffer)) break;
                $readlen += strlen($buffer);
                $content.=$buffer;
            }

            if($this->callback){
                if(!call_user_func_array($this->callback,array(&$this,&$content))){
                    break;
                }
            }else{
                $this->responseBody.=$content;
            }

            $readed = 0;
            if($chunkmode){
                fread($fp, 2);
                if(HTTP_TIME_OUT === $this->readsocket($fp,30,$chunklen,'fgets')){
                    return HTTP_TIME_OUT;
                }
                $chunklen = hexdec(trim($chunklen));
            }else{
                $readed += strlen($content);
                if(isset($this->responseHeader['content-length']) && $this->responseHeader['content-length'] <= $readed){
                    break;
                }
            }
        }
        fclose($fp);
        if($this->callback){
            return true;
        }else{
            return $this->responseBody;
        }
    }

    public function set_logger($func){
        $this->logfunc = &$func;
    }

    public function log($str, $nobreak=false){
        if(is_callable($this->logfunc)){
            return call_user_func($this->logfunc, $nobreak?$str:($str."\n"));
        }
    }

    private function is_addr($ip){
        return preg_match('/^[0-9]{1-3}\.[0-9]{1-3}\.[0-9]{1-3}\.[0-9]{1-3}$/',$ip);
    }

    private function microtime(){
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    private function readsocket($fp,$length,&$content,$func='fread'){
        if(!$this->reset_time_out($fp)){
            return HTTP_TIME_OUT;
        }

        $content = $func($fp,$length);

        if($this->check_time_out($fp)){
            return HTTP_TIME_OUT;
        }else{
            return true;
        }
    }

    private function reset_time_out(&$fp){
        if($this->read_time_total===null){
            return true;
        }elseif($this->read_time_left<0){
            return false;
        }else{
            $this->read_time_left = $this->read_time_total - $this->microtime() + $this->request_start;
            $second = floor($this->read_time_left);
            $microsecond = intval(( $this->read_time_left - $second ) * 1000000);
            stream_set_timeout($fp,$second, $microsecond);
            return true;
        }
    }

    private function check_time_out(&$fp){
        if(function_exists('stream_get_meta_data')){
            $info = stream_get_meta_data($fp);
            return $info['timed_out'];
        }else{
            return false;
        }
    }

    protected function build_url($url){
        $ret = $url['scheme'].'://'.$url['host'];
        if(isset($url['port']) && $url['port']!=80){
            $ret.=':'.$url['port'];
        }
        $ret.= $url['path'];
        if(isset($url['query']) && $url['query']){
            $ret.='?'.$url['query'];
        }
        return $ret;
    }

}

