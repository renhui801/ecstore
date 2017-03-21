<?php
/**
 * cps_theme_inst
 * CPS模板安装功能类
 * 
 * @uses
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_theme_inst Jul 15, 2011  4:27:19 PM ever $
 */
class cps_theme_inst {
    /**
     * @access public
     * @param string $theme 安装的模板名
     * @version 1 Jul 15, 2011
     */
    public function post_theme_install($theme) {
        if(ECAE_MODE==true){
           $sDir=$this->__build_dir(str_replace('\\','/',THEME_DIR.'/'.trim($theme)));
           $this->instEcaeFiles($sDir,$theme);
        }else{
            $this->copyTheme($theme);
            $this->instFiles($theme);
        }
        $libInit = kernel::single('cps_init');
        $libInit->saveTmpl($theme);
        $libInit->saveWidget($theme);
    }

    /**
     * 安装CPS时模板文件安装
     * @access public
     * @version 1 Jul 15, 2011
     */
    public function instTheme() {
        $mdlThemes = app::get('site')->model('themes');
        $themes = $mdlThemes->getList('theme');
        $libInit = kernel::single('cps_init');

        foreach ((array)$themes as $row) {
            if(ECAE_MODE==true){
                $sDir=$this->__build_dir(str_replace('\\','/',THEME_DIR.'/'.trim($row['theme'])));
                $this->instEcaeFiles($sDir,$row['theme']);
            }else{
                $this->copyTheme($row['theme']);
                $this->instFiles($sDir,$row['theme']);
            }
            $libInit->saveTmpl($row['theme']);
            $libInit->saveWidget($row['theme']);
        }
        return true;
    }

    /**
     * 卸载CPS时删除模板文件
     * @access public
     * @version 1 Jul 15, 2011
     */
    public function uninstTheme() {
        $mdlThemes = app::get('site')->model('themes');
        $themes = $mdlThemes->getList('theme');
        $libInit = kernel::single('cps_init');
        
        foreach ((array)$themes as $row) {
            $this->rmTheme($row['theme']);
            $libInit->delTmpl($row['theme']);
            $libInit->delWidget($row['theme']);
        }
        return true;
    }

    /**
     * 复制CPS模板
     * @access private
     * @param string $theme 安装的模板名
     */
    private function copyTheme($theme) {
        $themeDir = app::get('cps')->app_dir . '/init_tmpl';
        $destDir =  ROOT_DIR . '/themes/' . $theme ;
        utils::cp($themeDir,  $destDir);
    }

    //将模板文件放入themes_file表
    private function instFiles($theme){
        $libInit = kernel::single('cps_init');
        $list_new = $libInit->getTmpl($theme);
        foreach ($list_new as $id => $file){
            $themes_file_data = array(
                'filename' => $file['tmpl_path'],
                'filetype' => 'html',
                'fileuri'  => $file['theme'] . ':' . $file['tmpl_path'],
                'version'  => filemtime(THEME_DIR . '/' . $theme . '/' . $file['tmpl_path']),
                'theme'    => $file['theme'],
                'memo'     => '模板文件',
                'content'  => file_get_contents(THEME_DIR . '/' . $theme . '/' . $file['tmpl_path'])
            );
            $file_id = $this->insert_themes_file($themes_file_data);
        }
    }

    public function insert_themes_file($data){
        if($file_id = app::get('site')->model('themes_file')->save($data)){
            return $file_id;
        }else{
            return false;
        }
    }

    function instEcaeFiles($sDir,$filename){
        $list = array();
        $workdir = getcwd();
        if(chdir(app::get('cps')->app_dir . '/init_tmpl')){
            $this->__get_all_files('.', $list);
            $list_new = array();
            foreach ($list as $key => $value) {
                $list_new[$key]['name']=ltrim($value,'./');
            }
            $files = app::get('base')->model('files');
            $obj = app::get('site')->model('themes_file');
            $storager = kernel::single('base_storager');
            foreach ($list_new as $id => $file) {
                $fpath = $sDir.$file['name'];
                if(!is_dir(dirname($fpath))){
            	    if(mkdir(dirname($fpath), 0755, true)){
                        file_put_contents($fpath,file_get_contents(app::get('cps')->app_dir . '/init_tmpl/'.$file['name']));
                    }else{
                        $msg = app::get('site')->_('权限不允许');
                        return false;
                    }
                }else{
                    file_put_contents($fpath,file_get_contents(app::get('cps')->app_dir . '/init_tmpl/'.$file['name']));
                }

                $arr_fext = $this->get_file_ext($file['name']);
                if(!$arr_fext) continue;
                $fext = $arr_fext['ext'];
                $fmemo = $arr_fext['memo'];

                if($fext=='html' || $fext=='xml' || preg_match('/\.php/',$file['name'])){
                    $file_content = file_get_contents(app::get('cps')->app_dir . '/init_tmpl/'.$file['name']);
                }elseif($fext=='js' || $fext=='css'){
                    $index = $file['name'];
                    $fpath = $sDir.$file['name'];
                    $file_content = file_get_contents(app::get('cps')->app_dir . '/init_tmpl/'.$file['name']);
                    file_put_contents($fpath,$file_content);
                    $addons = array('name'=>basename($index),'path'=>dirname('/theme/'.$filename.'/'.$index).'/');
                    $file_content = $storager->save($fpath,'file',$addons);
                    $save_file = array('file_path'=>$file_content,'file_type'=>'public');
                    $b=$files->save($save_file);
                }else{//image
                    $index = $file['name'];
                    $fpath = $sDir.$file['name'];
                    $addons = array('name'=>basename($index),'path'=>dirname('/theme/'.$filename.'/'.$index).'/');
                    $file_content = $storager->save($fpath,'file',$addons);
                    $save_file = array('file_path'=>$file_content,'file_type'=>'public');
                    $files->save($save_file);
                }

                $save_data = array(
                    'fileuri'=>$filename.':'.$file['name'],
                    'filename'=>$file['name'],
                    'theme'=>$filename,
                    'content'=>$file_content,
                    'filetype'=>$fext,
                    'memo'=>$fmemo,
                    );
                $obj->save($save_data);
            }
            chdir($workdir);
        }else{
            chdir($workdir);
            return false;
        }
    }

    private function __get_all_files($sDir, &$aFile, $loop=true){
        if($rHandle=opendir($sDir)){
            while(false!==($sItem=readdir($rHandle))){
                if ($sItem!='.' && $sItem!='..' && $sItem!='' && $sItem!='.svn' && $sItem!='_svn'){
                    if(is_dir($sDir.'/'.$sItem)){
                        if($loop){
                            $this->__get_all_files($sDir.'/'.$sItem,$aFile);
                        }
                    }else{
                        $aFile[]=$sDir.'/'.$sItem;
                    }
                }
            }
            closedir($rHandle);
        }
    }

    public function get_file_ext($file_name){
        $ftype = array(
            'html' => app::get('site')->_('模板文件'),
            'gif'  => app::get('site')->_('图片文件'),
            'jpg'  => app::get('site')->_('图片文件'),
            'jpeg' => app::get('site')->_('图片文件'),
            'png'  => app::get('site')->_('图片文件'),
            'bmp'  => app::get('site')->_('图片文件'),
            'css'  => app::get('site')->_('样式表文件'),
            'js'   => app::get('site')->_('脚本文件'),
            'xml'  => app::get('site')->_('theme.xml'),
            'php'  => app::get('site')->_('模板挂件'),
        );
        if(strrpos($file_name,'.')===false) return false;
        $fext = strtolower(substr($file_name,strrpos($file_name,'.')+1));
        if(!$ftype[$fext])  return false;
        return array('ext'=>$fext,'memo'=>$ftype[$fext]);
    }

    private function __build_dir($sDir){
        if(file_exists($sDir)){
            $aTmp=explode('/',$sDir);
            $sTmp=end($aTmp);
            if(strpos($sTmp,'(')){
                $i=substr($sTmp,strpos($sTmp,'(')+1,-1);
                $i++;
                $sDir=str_replace('('.($i-1).')','('.$i.')',$sDir);
            }else{
                $sDir.='(1)';
            }
            return $this->__build_dir($sDir);
        }else{
            if(!is_dir($sDir)){
                mkdir($sDir,0755,true);
            }
            return $sDir.'/';
        }
    }

    /**
     * 删除CPS模板文件
     * @access private
     * @param string $theme 模板名
     */
    private function rmTheme($theme) {
        $arrThemeDir = $this->getThemeDir($theme);
        foreach ($arrThemeDir as $td) {
            if (is_dir($td)) {
                utils::remove_p($td);
            } elseif (is_file($td)) {
                unlink($td);
            }
        }
    }

    /**
     * 获取CPS模板文件路径
     * @access private
     * @param string $theme 安装的模板名
     * @version 1 Jul 15, 2011
     */
    private function getThemeDir($theme) {
        $tmplDir = ROOT_DIR . '/themes/' . $theme ;
        $arrThemeDir = array(
            $tmplDir . '/cps',
            $tmplDir . '/images/cps',
            $tmplDir . '/cps_common.html',
            $tmplDir . '/cps_index.html',
            $tmplDir . '/cps_notice.html',
        );
        return $arrThemeDir;
    }
}