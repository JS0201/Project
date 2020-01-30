<?php
/**
 * 特斯云商
 * ============================================================================
 * 版权所有 2014-2020 深圳市特斯在线网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tesi99.com/
 *
 */

namespace app\goods\hook;
use think\Request;

class Hook
{

    public function webSite(){



         //关站
        $site_isclosed=config('cache.site_isclosed');
        $site_closedreason=config('cache.site_closedreason');

       if(empty($site_isclosed)){

            exit($site_closedreason);
       }


       //站点主题
        $site_web_theme=config('cache.site_web_theme');


       if(empty($site_web_theme)){
           $theme='default';
       }elseif($site_web_theme==1){
           $theme='wap';
       }elseif($site_web_theme==2){
            if(Request::instance()->isMobile()){
               $theme='wap';
            }else{
               $theme='default';
            }
       }else{
           $theme='default';
       }


        config('base.default_theme',$theme);


        //模板配置

        $template=config('base.view_path');
        $view_path=config('base.default_theme');
        config('pathinfo_depr','/');

        $tpl_url= $template.'/'.$view_path.'/';

        $dir_arr=explode(DS,$template);

        $tmpl='/'.$dir_arr[count($dir_arr)-1].'/'.$view_path.'/'.'statics';


        config('template.view_base',$tpl_url);
        config('view_replace_str.__TMPL__',$tmpl);
        config('view_replace_str.__TPL__',$tpl_url);





        //CDN配置

        $switch_cdn=config('cache.switch_cdn');
        $cdn_url=config('cache.cdn_url');

        if($switch_cdn && $cdn_url){
            //$cdn_url=$cdn_url.'/';
            config('view_replace_str.__CDN__',$cdn_url);

        }


    }
}