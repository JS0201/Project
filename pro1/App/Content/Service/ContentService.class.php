<?php

namespace Content\Service;

use Think\Model;

class ContentService extends Model {

    public function __construct() {


        $this->model = D('Content');
        $this->category_model = D('Category');
        $this->category_service = D('Category', 'Service');
    }

    /**
     * [add 保存]
     * @param [array] $params [信息]
     * @return [boolean]         [返回ture or false]
     */
    public function save($params) {

        if (empty($params['category_id'])) {

            $this->errors = '请选择分类';
            return false;
        }



        $data = array();
        $data = $this->model->create($params);

        if (!$data) {
            $this->errors = $this->model->geterror();
            return false;
        }


        $data['path_id'] = $this->category_model->where(array('id' => $params['category_id']))->getfield('path_id');



        //下载远程图片和资源
        if ($params['remote'] == 'true') {
            $data['content'] = get_remote_file($data['content']);
        }




        //编辑器图片添加水印

        $picarr = content_picture($data['content']);

        if (is_array($picarr[1])) {

            foreach ($picarr[1] as $k => $v) {

                att_mark('article', $v, $data['category_id']);
            }
        }

        //提取第一个图片为缩略图 
        if ($params['autothumb'] == 'true') {

            if (is_array($picarr[1])) {
                $data['thumb'] = $picarr[1][0];
            } else {

                $data['thumb'] = '';
            }
        } else {

            if ($data['thumb']) {

                $conf = array(
                    'module' => 'article',
                    'url' => $data['thumb'],
                    'catid' => $data['category_id'],
                );
                attachment($conf);
            }
        }

        if ($data['thumb']) {
            //缩略图打水印
            att_mark('article', $data['thumb'], $data['category_id']);
        }



        //自动提取内容到摘要
        if ($params['autodesc'] == 'true') {
            if (empty($params['autodescsize']) || !$params['autodescsize']) {
                $autodescsize = 200;
            } else {

                $autodescsize = $params['autodescsize'];
            }

            $descstr = clearhtml($data['content']);


            $data['description'] = restrlen($descstr, $autodescsize);
        }




        if ($data['id']) {


            //标签Tag
            if ($data['tag']) {
                $tag_arr = explode(',', $data['tag']);
                foreach ($tag_arr as $v) {
                    $trow = M('tag')->where(array('name' => $v))->find();
                    if (!$trow) {
                        $tdata = array();
                        $tdata['name'] = $v;
                        $tdata['num'] = 1;
                        M('tag')->add($tdata);
                    }
                }
            }

            $data['update_time'] = time();
            $result = $this->model->save($data);
        } else {

            //标签Tag
            if ($data['tag']) {
                $tag_arr = explode(',', $data['tag']);
                foreach ($tag_arr as $v) {
                    $trow = M('tag')->where(array('name' => $v))->find();
                    if ($trow) {
                        M('tag')->where(array('name' => $v))->setinc('num', 1);
                    } else {

                        $tdata = array();
                        $tdata['name'] = $v;
                        $tdata['num'] = 1;
                        M('tag')->add($tdata);
                    }
                }
            }
            $data['add_time'] = time();
            $result = $this->model->add($data);
        }




        if ($result === false) {
            $this->errors = '发生知未错误，操作失败';
            return false;
        } else {
            return true;
        }
    }

    /**
     * [get_article_by_id 根据id获取内容信息]
     * @param  [type] $id [查询单条id]
     * @return [type]     [description]
     */
    public function get_content_by_id($id) {
        if ((int) $id < 1) {
            $this->errors = '数据不存在';
            return FALSE;
        }
        $result = $this->model->find($id);
        $result['category'] = $this->category_service->get_parents_name($result['category_id']);
        if (!$result) {
            $this->errors = '数据不存在';
        }
        return $result;
    }

    /**
     * [del 删除]
     * @param [type] $id [id]
     * @return [boolean]         [返回ture or false]
     */
    public function del($params) {

        $data = array();
        if (is_array($params)) {

            foreach ($params as $k => $v) {

                $data['id'] = $v;
                $this->model->where($data)->delete();
            }
        } else {
            $data['id'] = $params;
            $this->model->where($data)->delete();
        }

        return TRUE;
    }

    /**
     * [ajax_edit 编辑分类信息]
     */
    public function ajax_edit($params) {

        $result = $this->model->save($params);
        if ($result == false) {
            $this->errors = '修改失败';
            return false;
        }
        return true;
    }

}

?>