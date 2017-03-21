<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class utils{

    static function match_network ($nets, $ip, $first=false) {
        $return = false;
        if (!is_array ($nets)) $nets = array ($nets);

        foreach ($nets as $net) {
            $rev = (preg_match ("/^\!/", $net)) ? true : false;
            $net = preg_replace ("/^\!/", "", $net);

            $ip_arr  = explode('/', $net);
            $net_long = ip2long($ip_arr[0]);
            $x        = ip2long($ip_arr[1]);
            $mask    = long2ip($x) == $ip_arr[1] ? $x : 0xffffffff << (32 - $ip_arr[1]);
            $ip_long  = ip2long($ip);

            if ($rev) {
                if (($ip_long & $mask) == ($net_long & $mask)) return false;
            } else {
                if (($ip_long & $mask) == ($net_long & $mask)) $return = true;
                if ($first && $return) return true;
            }
        }
        return $return;
    }


    static function microtime(){
        list($usec, $sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }


    static function addslashes_array($value){
        if(empty($value)){
            return $value;
        }else{
            if(is_array($value)){
                foreach($value as $k=>$v){
                    if(is_array($v)){
                        $value[$k] = self::addslashes_array($v);
                    }else{
                        $value[$k] = addslashes($v);
                    }
                }
                return $value;
            }else{
                return addslashes($value);
            }
        }
    }

    static function stripslashes_array($value){
        if(empty($value)){
            return $value;
        }else{
            if(is_array($value)){
                $tmp = $value;
                foreach($tmp as $k=>$v){
                    $k = stripslashes($k);
                    $value[$k] = $v;

                    if(is_array($v)){
                        $value[$k] = self::stripslashes_array($v);
                    }else{
                        $value[$k] = stripslashes($v);
                    }
                }
                return $value;
            }else{
                return stripslashes($value);
            }
        }
    }


    static function _apath(&$array,$path,&$ret){
        $key = array_shift($path);
        if( ($p1 = strpos($key,'[')) && ($p2 = strrpos($key,']'))){
            $predicates = substr($key,$p1+1,$p2-$p1-1);
            $key = substr($key,0,$p1);
        }

        if(is_array($array)&&array_key_exists($key,$array)){
            $next = $array[$key];
            if(isset($predicates) && is_array($next)){
                switch(true){
                case $predicates=='first()':
                    $next = reset($next);
                    break;

                case $predicates=='last()':
                    $next = end($next);
                    break;

                case is_numeric($predicates):
                    $next = $next[$predicates];
                    break;

                default:
                    list($k,$v) = explode('=',$key);
                    if($v){
                        foreach($next as $item){
                            if(isset($item[$k]) && $item[$k]==$v){
                                $nextrst = $item;
                                break;
                            }
                        }
                    }else{
                        foreach($next as $item){
                            if(isset($item[$k])){
                                $nextrst = $item;
                                break;
                            }
                        }
                    }
                    if(isset($nextrst)){
                        $next = $nextrst;
                    }elseif($predicates=='default'){
                        $next = reset($next);
                    }else{
                        return false;
                    }
                    break;
                }
            }
            if(!$path){
                $ret = $next;
                return true;
            }else{
                return self::_apath($next,$path,$ret);
            }
        }else{
            return false;
        }
    }

    static function apath( &$array, $map ){
        if(self::_apath($array,$map,$ret) !== false){
            return $ret;
        }else{
            return false;
        }
    }

    static function unapath(&$array,$col,$path,&$ret){

        if( !array_key_exists($col,$array) )
            return false;
        $ret = '';
        $arrKey = '';
        $tmpArr = null;
        $pathCount = count($path);
        $pathItem = 1;
        foreach( $path as $v ){
            if( ($p1 = strpos($v,'[')) && ($p2 = strrpos($v,']'))){
                $predicates = substr($v,$p1+1,$p2-$p1-1);
                $v = substr($v,0,$p1);
            }
            if( $pathCount == $pathItem++ ){
                eval( '$ret'.$arrKey.'["'.$v.'"] = $array[$col];' );
                unset($array[$col]);
                return true;
            }
            $arrKey .= '["'.$v.'"]';
            if( $predicates ){
                return false;
            }
            $predicates = null;
        }

        return true;

    }

    static function array_path($array, $path){
        $path_array = explode('/', $path);
        $_code = '$return = $array';
        if($path_array){
            foreach($path_array as $s_path){
                $_code .= '[\''.$s_path.'\']';
            }
        }
        $_code = $_code.';';
        eval($_code);
        return $return;
    }

    static function buildTag($params,$tag,$finish=true){
        $ret = array();
        foreach((array)$params as $k=>$v){
            if(!is_null($v) && !is_array($v)){
                if($k=='value'){
                    $v=htmlspecialchars($v);
                }
                $ret[]=$k.'="'.$v.'"';
            }
        }
        return '<'.$tag.' '.implode(' ',$ret).($finish?' /':'').'>';
    }

    static function mkdir_p($dir,$dirmode=0755){
        $path = explode('/',str_replace('\\','/',$dir));
        $depth = count($path);
        for($i=$depth;$i>0;$i--){
            if(file_exists(implode('/',array_slice($path,0,$i)))){
                break;
            }
        }
        for($i;$i<$depth;$i++){
            if($d= implode('/',array_slice($path,0,$i+1))){
                if(!is_dir($d)) mkdir($d,$dirmode);
            }
        }
        return is_dir($dir);
    }

    static function cp($src,$dst){
        if(is_dir($src)){
            $obj = dir($src);
            while(($file = $obj->read()) !== false){
                if($file{0} == '.' ) continue;
                $s_daf = "$src/$file";
                $d_daf = "$dst/$file";
                if(is_dir($s_daf)){
                    if(!file_exists($d_daf)){
                        self::mkdir_p($d_daf);
                    }
                    self::cp($s_daf,$d_daf);
                }else{
                    $d_dir = dirname($d_daf);
                    if(!file_exists($d_dir)){
                        self::mkdir_p($d_dir);
                    }
                    copy($s_daf,$d_daf);
                }
            }
        }else{
            @copy($src,$dst);
        }
    }

    static function remove_p($sDir) 
    {
        if($rHandle=opendir($sDir)){
            while(false!==($sItem=readdir($rHandle))){
                if ($sItem!='.' && $sItem!='..'){
                    if(is_dir($sDir.'/'.$sItem)){
                        self::remove_p($sDir.'/'.$sItem);
                    }else{
                        if(!unlink($sDir.'/'.$sItem)){
                            trigger_error(app::get('base')->_('因权限原因 ').$sDir.'/'.$sItem.app::get('base')->_('无法删除'),E_USER_NOTICE);
                        }
                    }
                }
            }
            closedir($rHandle);
            rmdir($sDir);
            return true;
        }else{
            return false;
        }
    }//End Function
    
    static function replace_p($path,$replace_map){
        if(is_dir($path)){
            $obj = dir($path);
            while(($file = $obj->read()) !== false){
                if($file{0} == '.' ) continue;
                if(is_dir($path.'/'.$file)){
                    self::replace_p($path.'/'.$file,$replace_map);
                }else{
                    self::replace_in_file($path.'/'.$file,$replace_map);
                }
            }
        }else{
            self::replace_in_file($path,$replace_map);
        }
    }

    static function replace_in_file($file,$replace_map){
        file_put_contents($file,str_replace(array_keys($replace_map),array_values($replace_map),file_get_contents($file)));
    }

    static function tree($dir){
        $ret = array();
        if(!is_dir($dir))   return $ret;
        $obj = dir($dir);
        while(($file = $obj->read()) !== false){
             if(substr($file,0,1) == '.' ) continue;
             $daf = "$dir/$file";
             $ret[] = $daf;
             if(is_dir($daf)){
                 $ret = array_merge($ret, self::tree($daf));
             }
        }
        return $ret;
    }

    // 原func_ext.php中的 array_change_key
    static function &array_change_key(&$items, $key, $is_resultset_array=false){
            if (is_array($items)){
                $result = array();
                if (!empty($key) && is_string($key)) {
                    foreach($items as $_k => $_item){
                        if($is_resultset_array){
                            $result[$_item[$key]][] = &$items[$_k];
                        }else{
                            $result[$_item[$key]] = &$items[$_k];
                        }
                    }
                    return $result;
                }
            }
            return false;
    }
    
    
    //配送公式验算function
    static function cal_fee($exp,$weight,$totalmoney,$first_price,$continue_price,$defPrice=0){
        if($str=trim($exp)){
            $dprice = 0;
            $weight = $weight + 0;
            $totalmoney = $totalmoney + 0;
			$first_price = $first_price + 0;
			$continue_price = $continue_price + 0;
            $str = str_replace("[", "self::_getceil(", $str);
            $str = str_replace("]", ")", $str);
            $str = str_replace("{", "self::_getval(", $str);
            $str = str_replace("}", ")", $str);
    
            $str = str_replace("w", $weight, $str);
            $str = str_replace("W", $weight, $str);
            $str = str_replace("fp", $first_price, $str);
            $str = str_replace("cp", $continue_price, $str);
            $str = str_replace("p", $totalmoney, $str);
            $str = str_replace("P", $totalmoney, $str);
            eval("\$dprice = $str;");
            if($dprice === 'failed'){
                return $defPrice;
            }else{
                return $dprice;
            }
        }else{
            return $defPrice;
        }
    }

    static function mydate($f,$d=null){
        global $_dateCache;
        if(!$d)$d=time();
        if(!isset($_dateCache[$d][$f])){
            $_dateCache[$d][$f] = date($f,$d);
        }
        return $_dateCache[$d][$f];
    }

    function _getval($expval){
        $expval = trim($expval);
        if($expval !== ''){
        eval("\$expval = $expval;");
        if ($expval > 0){
            return 1;
        }else if ($expval == 0){
            return 1/2;
        }else{
            return 0;
        }
        }else{
            return 0;
        }
    }
    function _getceil($expval){
        if($expval = trim($expval)){
        eval("\$expval = $expval;");
        if ($expval > 0){
            return ceil($expval);
        }else{
            return 0;
        }
        }else{
            return 0;
        }
    }
    
    static function steprange($start,$end,$step){
        if($end-$start){
            if($step<2)$step=2;
            $s = ($end - $start)/$step;
            $r=array(floor($start)-1);

            for($i=1;$i<$step;$i++){
                $n = $start+$i*$s;
                $f=pow(10,floor(log10($n-$r[$i-1])));
                $r[$i] = round($n/$f)*$f;
                $q[$i] = array($r[$i-1]+1,$r[$i]);
            }
            $q[$i] = array($r[$step-1]+1,ceil($end));
            return $q;
        }else{
            if(!$end)$end = $start;
            return array(array($start,$end));
        }
    }

    static function http_build_query($arr, $prefix='', $arg_separator='&') 
    {
        if(version_compare(phpversion(), '5.1.2', '>=')){
            return http_build_query($arr, $prefix, $arg_separator);
        }else{
            $org = ini_get('arg_separator.output');
            if($org !== $arg_separator){
                ini_set('arg_separator.output', $arg_separator);
                $replace = $org;
            }
            $string = http_build_query($arr, $prefix);
            if(isset($replace)){
                ini_set('arg_separator.output', $replace);
            }
            return $string;
        }
    }//End Function

    static function array_ksort_recursive($data, $sort_flags=SORT_STRING) 
    {
        if(is_array($data)){
            ksort($data, $sort_flags);
            foreach($data AS $k=>$v){
                $data[$k] = self::array_ksort_recursive($v, $sort_flags);
            }
        }
        return $data;
    }//End Function

    static function array_md5($array, $sort_flags=SORT_STRING) 
    {
        return md5(serialize(self::array_ksort_recursive($array, $sort_flags)));
    }//End Function

    static function gzdecode($data) {
        $len = strlen($data);
        if ($len < 18 || strcmp(substr($data,0,2),"\x1f\x8b")) {
           return null; // Not GZIP format (See RFC 1952)
        }
        $method = ord(substr($data,2,1)); // Compression method
        $flags = ord(substr($data,3,1)); // Flags
        if ($flags & 31 != $flags) {
           // Reserved bits are set -- NOT ALLOWED by RFC 1952
           return null;
        }
        // NOTE: $mtime may be negative (PHP integer limitations)
        $mtime = unpack("V", substr($data,4,4));
        $mtime = $mtime[1];
        $xfl = substr($data,8,1);
        $os    = substr($data,8,1);
        $headerlen = 10;
        $extralen = 0;
        $extra    = "";
        if ($flags & 4) {
           // 2-byte length prefixed EXTRA data in header
           if ($len - $headerlen - 2 < 8) {
             return false;    // Invalid format
           }
           $extralen = unpack("v",substr($data,8,2));
           $extralen = $extralen[1];
           if ($len - $headerlen - 2 - $extralen < 8) {
             return false;    // Invalid format
           }
           $extra = substr($data,10,$extralen);
           $headerlen += 2 + $extralen;
        }

        $filenamelen = 0;
        $filename = "";
        if ($flags & 8) {
           // C-style string file NAME data in header
           if ($len - $headerlen - 1 < 8) {
             return false;    // Invalid format
           }
           $filenamelen = strpos(substr($data,8+$extralen),chr(0));
           if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
             return false;    // Invalid format
           }
           $filename = substr($data,$headerlen,$filenamelen);
           $headerlen += $filenamelen + 1;
        }

        $commentlen = 0;
        $comment = "";
        if ($flags & 16) {
           // C-style string COMMENT data in header
           if ($len - $headerlen - 1 < 8) {
             return false;    // Invalid format
           }
           $commentlen = strpos(substr($data,8+$extralen+$filenamelen),chr(0));
           if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
             return false;    // Invalid header format
           }
           $comment = substr($data,$headerlen,$commentlen);
           $headerlen += $commentlen + 1;
        }

        $headercrc = "";
        if ($flags & 1) {
           // 2-bytes (lowest order) of CRC32 on header present
           if ($len - $headerlen - 2 < 8) {
             return false;    // Invalid format
           }
           $calccrc = crc32(substr($data,0,$headerlen)) & 0xffff;
           $headercrc = unpack("v", substr($data,$headerlen,2));
           $headercrc = $headercrc[1];
           if ($headercrc != $calccrc) {
             return false;    // Bad header CRC
           }
           $headerlen += 2;
        }

        // GZIP FOOTER - These be negative due to PHP's limitations
        $datacrc = unpack("V",substr($data,-8,4));
        $datacrc = $datacrc[1];
        $isize = unpack("V",substr($data,-4));
        $isize = $isize[1];

        // Perform the decompression:
        $bodylen = $len-$headerlen-8;
        if ($bodylen < 1) {
           // This should never happen - IMPLEMENTATION BUG!
           return null;
        }
        $body = substr($data,$headerlen,$bodylen);
        $data = "";
        if ($bodylen > 0) {
           switch ($method) {
             case 8:
               // Currently the only supported compression method:
               $data = gzinflate($body);
               break;
             default:
               // Unknown compression method
               return false;
           }
        } else {
           // I'm not sure if zero-byte body content is allowed.
           // Allow it for now... Do nothing...
        }

        // Verifiy decompressed size and CRC32:
        // NOTE: This may fail with large data sizes depending on how
        //      PHP's integer limitations affect strlen() since $isize
        //      may be negative for large sizes.
        if ($isize != strlen($data) || crc32($data) != $datacrc) {
           // Bad format! Length or CRC doesn't match!
           return false;
        }
        return $data;
    }

    /**
     * Escape html entities
     *
     * @param   mixed $data
     * @param   array $allowedTags
     * @return  mixed
     */
    static function escapeHtml($data, $allowedTags = null)
    {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $item) {
                $result[] = self::escapeHtml($item);
            }
        } else {
            // process single item
            if (strlen($data)) {
                if (is_array($allowedTags) and !empty($allowedTags)) {
                    $allowed = implode('|', $allowedTags);
                    $result = preg_replace('/<([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)>/si', '##$1$2$3##', $data);
                    $result = htmlspecialchars($result, ENT_COMPAT, 'UTF-8', false);
                    $result = preg_replace('/##([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)##/si', '<$1$2$3>', $result);
                } else {
                    $result = htmlspecialchars($data, ENT_COMPAT, 'UTF-8', false);
                }
            } else {
                $result = $data;
            }
        }
        return $result;
    }

    //过滤用户输入的数据，防范xss攻击
    static function _RemoveXSS($val) {
        // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
        // this prevents some character re-spacing such as <java\0script>
        // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
        $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

        // straight replacements, the user should never need these since they're normal characters  
        // this prevents like <IMG SRC=@avascript:alert('XSS')>  
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++) {
            // ;? matches the ;, which is optional
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

            // @ @ search for the hex values
            $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
            // @ @ 0{0,7} matches '0' zero to seven times
            $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
        }

        // now the only remaining whitespace attacks are \t, \n, and \r 
        $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base'); 
        $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'); 
        $ra = array_merge($ra1, $ra2);

        $found = true; // keep replacing as long as the previous round replaced something
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
                $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
                if ($val_before == $val) {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }

        return $val;
    }

    static function _filter_input($data){
        if(is_array($data)){
            foreach($data as $key=>$v){
                $data[$key] = self::_filter_input($data[$key]);
            }
        }else{
            if(strlen($data)){
                $data = self::_RemoveXSS($data);
            }else{
                $data = $data;
            }
        }
        return $data;
    }

    //过滤CRLF注入攻击
    static function _filter_crlf($url){
        $url = trim($url);
        $url = strip_tags($url,""); //清除HTML如<br />等代码
        $url = str_replace("\n", "", str_replace(" ", "", $url));//去掉空格和换行
        $url = str_replace("\t","",$url); //去掉制表符号
        $url = str_replace("\r\n","",$url); //去掉回车换行符号
        $url = str_replace("\r","",$url); //去掉回车
        $url = str_replace("\"","",$url); //去掉双引号
        // $url = str_replace("'","",$url); //去掉单引号
        $url = trim($url);
        return $url;
    }

    /**
     * Check url to be used as internal
     *
     * @param   string $url
     * @return  bool
     */
    static function _isInternalUrl($url){
        if(strpos($url, 'http') !== false){
            // Url must start from base url
            if(strpos($url, kernel::base_url(1)) === 0 ){
                return true;
            }
        }
        return false;
    }

}
