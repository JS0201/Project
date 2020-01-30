<?php

namespace Common\Controller;
class Admin extends Common {

    public function _initialize() {
        define('IN_ADMIN', TRUE);
        parent::_initialize();

        $this->admin_service = model('Admin/Admin', 'Service');

        $admin_info = $this->admin_service->init();

        if (!$admin_info) {

            $this->admin_service->logout();
			
			if($_GET['go']!='qcgs'){
				
				 _404();
				
			}

            showmessage('请先登录后台管理', url('admin/login/index'));
        }

        define('ADMIN_ID', $admin_info['id']);
        define('ADMIN_USER', $admin_info['username']);
        define('FORMHASH', $admin_info['formhash']);

        if ($_GET['formhash'] !== FORMHASH && $_POST['formhash'] !== FORMHASH) {
            //$this->error('当前请求含有非法字符，已被系统拒绝。',U('login/index'));
             //exit('当前请求含有非法字符，已被系统拒绝。');
        }
		
		
		if($admin_info['group_id'] > 1 && $this->admin_service->auth($admin_info['rules']) === false) {
			showmessage('权限不足，操作失败','null');
			exit();
        }
		
    }

    /**
     * 后台页面调用
     * @param int $totalrow 总记录数
     * @param int $pagesize 每页记录数
     * @param int $pagenum 	页码数量
     */
    final public function admin_pages($totalrow, $pagesize = 10, $pagenum = 5) {
        if (empty($page))
            $page = 1;
        if (empty($_GET['p']))
            $_GET['p'] = 1;

        $totalPage = ceil($totalrow / $pagesize);
        $rollPage = floor($pagenum / 2);

        $StartPage = $_GET['p'] - $rollPage;
        $EndPage = $_GET['p'] + $rollPage;
        if ($StartPage < 1)
            $StartPage = 1;
        if ($EndPage < $pagenum)
            $EndPage = $pagenum;

        if ($EndPage >= $totalPage) {
            $EndPage = $totalPage;
            $StartPage = max(1, $totalPage - $pagenum + 1);
        }
        $string = '<ul class="fr">';
        $string .= '<li>共' . $totalrow . '条数据</li>';
        $string .= '<li class="spacer-gray margin-lr"></li>';
        $string .= '<li>每页显示<input class="input radius-none" style="margin-left: 20px;margin-right: 20px;" type="text" name="limit" value="' . $pagesize . '"/>条</li>';
        $string .= '<li class="spacer-gray margin-left"></li>';

        /* 第一页 */
        if ($_GET['p'] > 1) {
            $string .= '<li class="start"><a href="' . page_url(array('p' => 1)) . '"></a></li>';
            $string .= '<li class="prev"><a href="' . page_url(array('p' => $_GET['p'] - 1)) . '"></a></li>';
        } else {
            $string .= '<li class="default-start"></li>';
            $string .= '<li class="default-prev"></li>';
        }
        for ($page = $StartPage; $page <= $EndPage; $page++) {

            $string .= '<li ' . (($page == $_GET['p']) ? 'class="current"' : '') . '><a href="' . page_url(array('p' => $page)) . '">' . $page . '</a></li>';
        }
        if ($_GET['p'] < $totalPage) {

            $string .= '<li class="next"><a href="' . page_url(array('p' => $_GET['p'] + 1)) . '"></a></li>';
            $string .= '<li class="end"><a href="' . page_url(array('p' => $totalPage)) . '"></a></li>';
        } else {
            $string .= '<li class="default-next"></li>';
            $string .= '<li class="default-end"></li>';
        }
        $string .= '</ul>';
        return $string;
    }

    /*     * 重写display

     * 加载模板和页面输出 可以返回输出内容
     * @access public
     * @param string $templateFile 模板文件名
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     * @param string $content 模板输出内容
     * @param string $prefix 模板缓存前缀
     * @return mixed
     */

    final public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '') {

        $m = MODULE_NAME;
        $c = CONTROLLER_NAME;
        $a = ACTION_NAME;
        $depr = config('TMPL_FILE_DEPR');
        $view = config('DEFAULT_V_LAYER');

        $suffix = config('TMPL_TEMPLATE_SUFFIX');


        if (empty($templateFile)) {

            $templateFile = APP_PATH . $m . '/' . $view . '/' . $c . $depr . $a . $suffix;
        }
		

        parent::display($templateFile, $charset = '', $contentType = '', $content = '', $prefix = '');
    }

}
