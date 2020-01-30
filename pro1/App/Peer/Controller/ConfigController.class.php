<?php

namespace Peer\Controller;

use Common\Controller\AdminController;

class ConfigController extends AdminController {

    public function _initialize() {

        parent::_initialize();

        $this->service = D('Admin/Site', 'Service');
    }

    public function index() {

        if ($_POST) {
            $result = $this->service->update($_POST);
            if (!$result) {

                showmessage('配置文件保存失败');
            } else {


                showmessage('配置文件保存成功', U('index'), 1);
            }
        } else {

           
			$group_list=['8'=>'交易区'];

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



}
