<?php

namespace Member\Controller;

use Common\Controller\BaseController;

class CheckController extends BaseController
{

    public function _initialize()
    {
        parent::_initialize();
        //   var_dump($this->member['member_auth']);

        if (empty($_COOKIE["member_authadmin"])) {
            if ($this->member['member_auth'] != $_COOKIE["member_auth"]) {

                $url_forward = $_GET['url_forward'] ? $_GET['url_forward'] : urlencode($_SERVER['REQUEST_URI']);

                header('Location:' . U('member/public/login', array('url_forward' => $url_forward)));
            }
        }
        //var_dump($_COOKIE["member_auth"]);
        if ($this->member['id'] < 1) {
            $url_forward = $_GET['url_forward'] ? $_GET['url_forward'] : urlencode($_SERVER['REQUEST_URI']);

            header('Location:' . U('member/public/login', array('url_forward' => $url_forward)));
        }

        hook('check_lock', $this->member);

    }


}
