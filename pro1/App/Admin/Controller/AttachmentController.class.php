<?php

namespace Admin\Controller;

use Common\Controller\AdminController;

class AttachmentController extends AdminController {

    public function _initialize() {
        parent::_initialize();

        $this->model = D('Attachment');
    }

    public function index() {

        $spaces = array('article' => '站点图库', 'ad' => '广告图库', 'member' => '会员图库', 'common' => '其它图库');
        $attachments = array();
        foreach ($spaces as $key => $value) {
            $v = array();
            $v['datetime'] = $this->model->where(array("module" => $key))->order("aid desc")->getField('datetime');
            $v['count'] = $this->model->where(array("module" => $key))->count();
            $filesize = $this->model->where(array("module" => $key))->sum("filesize");

            $v['filesize'] = $filesize;
            list($v['filesize'], $v['fileunit']) = explode(" ", $v['filesize']);
            $attachments[$key] = $v;
        }



        $this->assign('attachments', $attachments);
        $this->assign('spaces', $spaces);
        $this->display();
    }

    public function manage() {
        $sqlmap = array();
        $sqlmap['module'] = $_GET['folder'];
        $sqlmap['isimage'] = 1;
        if (isset($_GET['type']) && $_GET['type'] == 'use') {
            $sqlmap['use_nums'] = 0;
        }
        $limit = (isset($_GET['limit']) && is_numeric($_GET['limit'])) ? $_GET['limit'] : 20;
        $lists = $this->model->where($sqlmap)->limit($limit)->page($_GET['page'])->order('aid DESC')->select();
        $count = $this->model->where($sqlmap)->count();
        $pages = $this->admin_pages($count, $limit);
        $this->assign('lists', $lists);
        $this->assign('pages', $pages);
        $this->display();
    }

    public function replace() {
        if (IS_POST) {

            $old_pic = $this->model->where(array('aid' => $_POST['aid']))->field('filename,url')->find();

            //$savepath=str_replace(array('uploads/',$old_pic['filename']),array('',''),$old_pic['url']);

            $savepath = explode('/', $old_pic['url']);

            $file_info = explode('.', $old_pic['filename']);
            $file = uploads($_FILES['upfile'], '', $savepath[1] . '/', $file_info[0], $file_info[1]);


            $url = $old_pic['url'];


            $image = new \Think\Image();

            $image->open($url);


            $width = $image->width();
            $height = $image->height();
            $filetype = $image->mime();



            $picture_arr = explode('.', $url);
            $picture_arr2 = explode('/', $url);
            $fileext = $picture_arr[1];



            $data = array();
            $data['mid'] = session('gboy_admin_login.id');



            $data['filepath'] = '';
            $data['filesize'] = filesize($url);
            $data['fileext'] = $fileext;
            $data['isimage'] = 1;
            $data['is_mark'] = $is_mark;
            $data['downloads'] = 0;
            $data['datetime'] = time();
            $data['clientip'] = getip();
            $data['use_nums'] = 1;
            $data['filetype'] = $filetype;
            $data['width'] = $width;
            $data['height'] = $height;
            $data['name'] = $_FILES['upfile']['name'];
            $data['issystem'] = 1;


            $result = $this->model->where(array('aid' => $_POST['aid']))->data($data)->save();
            if ($result) {

                showmessage('上传成功', '', 1, array('url' => $url), 'json');
            } else {


                showmessage('上传失败，请重新上传', '', 0, array('url' => $url), 'json');
            }
        }
    }

    public function del() {
        $aids = (array) $_GET['aid'];
        if (empty($aids)) {
            showmessage('数据不存在');
        }
        foreach ($aids as $aid) {
            $r = $this->model->find($aid);
            if (!$r)
                showmessage('数据不存在');
            if ($r['url'] && file_exists($r['url'])) {

                @unlink($r['url']);
            }
            $this->model->delete($aid);
        }
        showmessage('删除成功', U('index'), 1);
    }

}
