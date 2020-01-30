<?php

namespace Admin\Controller;

use Common\Controller\AdminController;

class SiteController extends AdminController {

    public function _initialize() {

        parent::_initialize();

        $this->service = D('Site', 'Service');
    }

    public function base() {
        if ($_POST) {


            if (!empty($_FILES['site_logo']['name'])) {
                $file = uploads($_FILES['site_logo'], '', 'logo', 'logo');
                $_POST['site_logo'] = $file['file_url'];
            }

            if (!empty($_FILES['watermark_logo']['name'])) {
                $file = uploads($_FILES['watermark_logo'], '/data/', 'watermark', 'watermark');
                $_POST['watermark_logo'] = $file['file_url'];
            }



            $result = $this->service->update($_POST);
            if (!$result) {

                showmessage('配置文件保存失败');
            } else {


                showmessage('配置文件保存成功', U('base'), 1);
            }
        } else {

            //分组
            $group_list = $this->service->group();



            //分组和配置项

            foreach ($group_list as $k => $v) {

                $list[$k] = $this->service->setting($k);

                foreach ($list[$k] as $kk => $vv) {

                    if ($vv['type'] == 'radio' || $vv['type'] == 'select' || $vv['type'] == 'select' || $vv['type'] == 'checkbox') {
                        $default_value = $vv['default_value'];

                        eval("\$default_valuea = " . $default_value . '; ');
                        $list[$k][$kk]['default_value'] = array('items' => $default_valuea, 'colspan' => 3,);
                    }

                    if ($vv['type'] == 'checkbox') {

                        $list[$k][$kk]['key'] = $vv['key'] . '[]';
                    }
                }
            }


            $this->assign('list', $list);
            $this->assign('group_list', $group_list);
            $this->display();
        }
    }

    public function add() {


        $result = $this->service->add($_POST);

        if (!$result) {

             showmessage($this->service->errors);
        } else {
          
            showmessage('变量添加成功', U('base'), 1);
        }
    }

}
