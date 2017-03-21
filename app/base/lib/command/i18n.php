<?php

class base_command_i18n extends base_shell_prototype 
{
    
    var $command_create_convert = '生成替换文件 app_id (etc: base) | 此命令会更新默认语言po包, 请慎用';
    public function command_create_convert() 
    {
        $options = func_get_args();
        $app_id = $options[0];
        if($app_id){
            $this->parse_app($app_id);
        }else{
            $rows = app::get('base')->model('apps')->getList('app_id');
            foreach($rows AS $row){
                $this->parse_app($row['app_id']);
            }
        }
    }//End Function

    private function parse_app($app_id) 
    {
        $app = app::get($app_id);
        $lang = kernel::get_lang();
        $dir = $app->lang_dir . '/' . $lang.'/LC_MESSAGES';
        if(!is_dir($dir))   mkdir($dir, 0775, true);
        $file = $dir . '/lang.po';
        $array = array();
        
        /* $this->parse_php($app, $app->app_dir . '/controller', $array); */
        /* $this->parse_php($app, $app->app_dir . '/dbschema', $array); */
        /* $this->parse_php($app, $app->app_dir . '/model', $array); */
        /* $this->parse_php($app, $app->app_dir . '/lib', $array); */
        $this->parse_php($app, $app->app_dir . '/', $array);
        
        $this->parse_html($app, $app->app_dir . '/view', $array);
        $this->parse_html($app, $app->app_dir . '/lang/js', $array);
        

        if(!empty($array)){
            $this->create_po_package($app_id, $file, $array);
            logger::info(sprintf('%s 语言包转换文件生成成功，共 %d items', $app->app_id, count($array)));
        }else{
            logger::info(sprintf('%s 没有语言包', $app->app_id));
        }
    }//End Function

    private function create_po_package($app_id, $file_path, $tran_items){
        $output = $this->get_po_package_header();
        foreach( $tran_items as $msgid => $item){
            foreach( $item['file'] as $file){
                $output .= sprintf('#: %s', app::get($app_id)->app_dir.$file)."\n";
            }
            $output .= sprintf('msgid "%s"', $msgid)."\n";
            $output .= sprintf('msgstr ""')."\n\n";
        }
        file_put_contents($file_path, $output);
    }

    private function get_po_package_header(){
        $output = <<<EOF
# SOME DESCRIPTIVE TITLE.
# Copyright (C) YEAR THE PACKAGE'S COPYRIGHT HOLDER
# This file is distributed under the same license as the PACKAGE package.
# FIRST AUTHOR <EMAIL@ADDRESS>, YEAR.
#
#, fuzzy
msgid ""
msgstr ""
"Project-Id-Version: PACKAGE VERSION\\n"
"Report-Msgid-Bugs-To: \\n"
"POT-Creation-Date: 2012-10-30 18:59+0800\\n"
"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n"
"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n"
"Language-Team: LANGUAGE <LL@li.org>\\n"
"Language: \\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
\n
EOF;
        return $output;
    }

    private function parse_php($app, $dir, &$array) 
    {
        if(is_dir($dir)){
            if($handle = opendir($dir)){
                while(($file = readdir($handle)) !== false){
                    if(substr($file, 0, 1) != '.'){
                        if(is_dir($dir . '/' . $file)){
                            $this->parse_php($app, $dir . '/' . $file, $array);
                        }elseif(substr($file, -3, 3) == 'php'){
                            $t = substr($dir . '/' . $file, strlen($app->app_dir));
                            foreach(file($dir . '/' . $file) AS $line=>$subject){
                                if(preg_match_all('/\-\>_\([\'"](.+)[\'"]\)/isU', $subject, $match)){
                                    foreach($match[1] as $word){
                                        $array[stripslashes($word)]['conv'] = '';
                                        $array[stripslashes($word)]['file'][] = $t . ':' . ($line+1);
                                    }
                                }
                            }
                        }
                    }
                }
                closedir($handle);
            }
        }
    }//End Function

    private function parse_html($app, $dir, &$array) 
    {
        if(is_dir($dir)){
            if($handle = opendir($dir)){
                while(($file = readdir($handle)) !== false){
                    if(substr($file, 0, 1) != '.'){
                        if(is_dir($dir . '/' . $file)){
                            $this->parse_html($app, $dir . '/' . $file, $array);
                        }else{
                            $t = substr($dir . '/' . $file, strlen($app->app_dir));
                            foreach(file($dir . '/' . $file) AS $line=>$subject){
                                if(preg_match_all('/\<\{.*[=]{1}[\'"](.+)[\'"]\|t[:]?.*\}\>/is', $subject, $match)){
                                    foreach($match[1] as $word){
                                        $array[stripslashes($word)]['conv'] = '';
                                        $array[stripslashes($word)]['file'][] = $t . ':' . ($line+1);
                                    }
                                }
                                if(preg_match_all('/\<\{t\}\>(.+)<\{\/t\}\>/isU', $subject, $match)){
                                    foreach($match[1] as $word){
                                        $array[stripslashes($word)]['conv'] = '';
                                        $array[stripslashes($word)]['file'][] = $t . ':' . ($line+1);
                                    }
                                }
                            }
                        }
                    }
                }
                closedir($handle);
            }
        }
    }//End Function
    
}//End Class