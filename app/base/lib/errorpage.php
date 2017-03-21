<?php

class base_errorpage 
{

    static private function _var_export($text) {
        ob_start();
        var_dump($text);
        $text = ob_get_contents();
        ob_end_clean();
        return $text;
    }

    static public function exception_handler($exception) {
        if (defined('DEBUG_PHP') && constant('DEBUG_PHP')===true) {
            self::_exception_handler($exception);
            
        }else{
            self::system_crash();
        }
    }
    
    static private function _exception_handler($exception){
        foreach(kernel::serviceList('base_exception_handler') as $service){
            if(method_exists($service, 'pre_display')){
                $service->pre_display($content);
            }
        }
        $message = $exception->getMessage();
        
        $code = $exception->getCode();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTrace();
        $trace_message = $exception->getTraceAsString();

        $trace_message = null;
        
        $root_path = realpath(ROOT_DIR);
        $output = ob_end_clean();
        
        $position = str_replace($root_path,'&gt; &nbsp;',$file).':'.$line;

        $i=0;
        foreach($trace as $t){
            if(!($t['class']=='kernel' && $t['function']=='exception_error_handler')){
                $t['file'] = str_replace($root_path,'ROOT:',$t['file']);
                $basename = basename($t['file']);
                if($i==0){
                    $trace_message .= '<tr class="code" style="color:#000"><td><b>&gt;&nbsp;</b></td>';
                }else{
                    $trace_message .= '<tr class="code" style="color:#999"><td></td>';
                }
                if($t['args']){
                    //                            var_dump(debug_backtrace());
                    $args = array();
                    foreach($t['args'] as $arg_info){
                        if (is_array($arg_info) || (is_string($arg_info) && strlen($arg_info)>20)) {
                            $args[] = "<span class=\"lnk\" onclick=\"alert(this.nextSibling.innerHTML)\">...</span><span style='display:none'>".self::_var_export($arg_info)."</span>";
                        }else if(is_object($arg_info)){
                            $arg_detail = self::_var_export($arg_info);
                            $arg_info = get_class($arg_info);                                
                            $args[
                            ] = "object(<span class=\"lnk\" onclick=\"alert(this.nextSibling.innerHTML)\">$arg_info</span><span style='display:none'>$arg_detail</span>)";
                        }else{
                            $args[] = var_export(htmlspecialchars($arg_info),1);
                        }
                    }
                    $args = implode(',', $args);                    
                }else{
                    $args = '';
                }
                if($t['line']){
                    $trace_message .= "<td>#{$i}</td><td>{$t['class']}{$t['type']}{$t['function']}({$args})</td><td>{$basename}:{$t['line']}</td></tr>";
                }else{
                    $trace_message .= "<td>#{$i}</td><td>{$t['class']}{$t['type']}{$t['function']}({$args})</td><td>{$basename}</td></tr>";
                }
                $i++;
            }
        }
        
        $output=<<<EOF
        <p style="background:#eee;border:1px solid #ccc;padding:10px;margin:10px 0">$message</p>
        <div style="padding:10px 0;font-weight:bold;color:#000">$position</div>
        <table cellspacing="0" cellpadding='0' style="width:100%;">
        $trace_message
        </table>
EOF;

        self::output($output, 'Track');
    }

    static function system_crash() {
        self::output('','服务异常稍后再试');        
    }
    
    
    static function system_offline(){
        self::output('','System is offline');
    }
    
    static protected function output($body,$title='',$status_code=500){
        //header('Connection:close',1,500);
        
        $date = date(DATE_RFC822);
        
        $html =<<<HTML
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
        	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        	<title>Error: $title</title>
        	<style>
                #main{width:500;margin:auto;}
                #header{position: relative;background:#c52f24;margin:20px 0 5px 0;
                padding:5px;color:#fff;height:30px;
                font-family: "Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;}
                .code{font-size:14px;line-height:16px;font-weight:bold;font-family: "Courier New", Courier, mono;}
                .lnk{text-decoration: underline;color:#009;	cursor: pointer;}
        	</style>
        </head>

        <body>
            <div id="main">
                <div id="header">
                    <span style="float:left;">$title</span>
                    <span style="float:right;font-size:10px">$date</span>
                </div>
                <br class="clear" />
                <div>
                $body
                </div>
            </div>
        </body>
        </html>
HTML;

        echo str_pad($html,1024);
        exit;
    }
    
}//End Class
