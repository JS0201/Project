<?php

namespace Home\Controller;

use Common\Controller\BaseController;

class SearchController extends BaseController {

    public function _initialize(){
        parent::_initialize();
       
    }

    public function search(){
        $cate = M('category')->field("id,name")->where(['modelid'=>1,'parent_id'=>0])->limit(7)->select();
       // dump($cate);
        $this->assign('cate',$cate);

        $this->display();
    }
    public function index(){
        $cate = M('category')->field("id,name")->where(['modelid'=>1,'parent_id'=>0])->limit(7)->select();
       // dump($cate);
        $this->assign('cate',$cate);

        $this->display();
    }

    //好玩
    public function searchHaowan(){
      // dump(date("Y-m-d",1550888821));
      // return;
        $cate_id = I('cate_id');
        $content = M('content')->field("id,title,thumb,description,add_time")->where(['category_id'=>$cate_id])->select();
        $this->assign('con',$content);
        $this->display();
    }
    //好玩detail
    public function searchHWDetail(){
        $id = I('id');
        $content = M('content')->where(['id'=>$id])->find();
        $this->assign('vo',$content);
        $this->display();
    }
    // 商学院
    public function searchSXY(){
        $cate_id = I('cate_id');
        $content = M('content')->field("id,title,thumb,description,add_time")->where(['category_id'=>$cate_id])->select();
        $this->assign('con',$content);

        $this->display();
    }
    //客服
    public function searchKefu(){
        $this->display();
    }
    // 在线客服
    public function searchOnLineKefu(){
        $this->display();
    }
    //财报
    public function searchCaibao(){
        $cate_id = I('cate_id');
        $content = M('content')->field("id,title,thumb,description,add_time")->where(['category_id'=>$cate_id])->select();
        $this->assign('con',$content);

        $this->display();
    }
    //招贤纳士
    public function searchZhaobiao(){
        $cate_id = I('cate_id');
        $content = M('content')->field("id,title,thumb,description,add_time")->where(['category_id'=>$cate_id])->select();
        $this->assign('con',$content);

        $this->display();
    }
    //接待中心
    public function searchJiedai(){
        $cate_id = I('cate_id');
        $content = M('content')->field("id,title,thumb,description,add_time")->where(['category_id'=>$cate_id])->select();
        $this->assign('con',$content);

        $this->display();
    }
    //活动
    public function searchActive(){
        $cate_id = I('cate_id');
        $content = M('content')->field("id,title,thumb,description,add_time")->where(['category_id'=>$cate_id])->select();
        $this->assign('con',$content);

        $this->display();
    }
    //
    public function question(){
        $authkey = cookie('member_auth');
        $question = I('question');
        if (strlen($question) < 6){
            $this->error('不少于6个字符');
        }
        if ($authkey) {

            list($uid, $rand) = explode("\t", authcode($authkey));

            $data['question'] = $question;
            $data['add_time'] = time();
            $data['member_id'] = $uid;
            $data['update_time'] = time();
            M('question') ->add($data);
            $this->success('问题已提交成功',U('home/search/index'));
        }
       return;
    }
}
