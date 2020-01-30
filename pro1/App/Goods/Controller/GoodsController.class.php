<?php

namespace Goods\Controller;

use Common\Controller\AdminController;

class GoodsController extends AdminController {

    public function _initialize() {
        parent::_initialize();

        $this->category_service = D('Goods_category', 'Service');
        $this->brand_service = D('Goods/Brand', 'Service');
        $this->spu_service = D('Goods_spu', 'Service');
    }

    public function index() {

        $sqlmap = array();
	

        if ($_GET['catid']) {
            $catid = (int) $_GET['catid'];
            $sqlmap['path_id'] = array('like', '%,' . $catid . ',%');
            $cate = $this->category_service->find(array('id' => $catid));
        }

        if ($_GET['keyword']) {
            $keyword = trim($_GET['keyword']);
            $sqlmap['name'] = array('like', '%' . $keyword . '%');
        }

        if ($_GET['label']) {
            switch ($_GET['label']) {
                case 2:
                    $sqlmap['status'] = 0;
                    break;
                case 3:
                    $sqlmap['status'] = 1;
                    $sqlmap['sku_total'] = array('exp', '<=warn_number');
                    break;
                case 4:
                    $sqlmap['status'] = -1;
                    break;
                default:
                    $sqlmap['status'] = array('neq', -1);
            }
        } else {
            $sqlmap['status'] = array('neq', -1);
        }


        $result = $this->spu_service->select($sqlmap);

        $category = $this->category_service->get_category_tree();


        $goods_name_length = $_GET['label'] < 3 ? 25 : 30;
        $goods_number_length = $_GET['label'] == 1 || !isset($_GET['label']) ? 5 : 10;

        $lists = array(
            'th' => array(
                'sn' => array('title' => '商品货号', 'length' => 15, 'style' => 'double_click'),
                'name' => array('title' => '商品名称', 'length' => $goods_name_length, 'style' => 'goods'),
                'brand_name' => array('length' => 25, 'title' => '品牌&分类', 'style' => 'cate_brand'),
                'price' => array('title' => '价格', 'length' => 10),
                'number' => array('title' => '库存', 'length' => $goods_number_length),
                'sort' => array('title' => '排序', 'style' => 'double_click', 'length' => 5),
                'status' => array('title' => '上架', 'style' => 'ico_up_rack', 'length' => 5),
            ),
            'lists' => $result['lists'],
        );

        //品牌

        $brands = $this->brand_service->select(null,null);

        if ($_GET['brand_id']) {
            $brand = $this->brand_service->find(array('id' => $_GET['brand_id']), 'id,name');
        }

        if ($_GET['label'] != 1 && isset($_GET['label'])) {
            unset($lists['th']['sort']);
        }
        if ($_GET['label'] > 2) {
            unset($lists['th']['status']);
        }

        $pages = $this->admin_pages($result['count'], $result['limit']);

        $this->assign('lists', $lists)->assign('goods', $goods)->assign('category', $category)->assign('cate', $cate)->assign('brand', $brand)->assign('brands', $brands['lists'])->assign('pages', $pages)->display();
    }

    public function add() {

        $goods = array();

        $brands = $this->brand_service->select(null, null);


        $this->assign('goods', $goods)->assign('brands', $brands['lists'])->display('edit');
    }

    
    
    public function edit() {

        if (!$goods = $this->spu_service->find(array('id' => $_GET['id']))) {

            showmessage($this->spu_service->errors);
        }
        $brands = $this->brand_service->select(null, null);
  
        $this->assign('goods', $goods)->assign('brands', $brands['lists'])->display();
    }

    function save() {

        if (!$this->spu_service->save($_POST)) {
            showmessage($this->spu_service->errors);
        }

        showmessage(L('_os_success_'), U('index'), 1);
    }

    /**
     * [upload 上传商品图片]
     * @return [type] [description]
     */
    public function upload() {
        $result['url'] = D('Attachment/Attachment', 'Service')->upload($_FILES['upfile']);
        $result['img_id'] = $_POST['img_id'];
        showmessage(L('_os_success_'), '', 1, $result, 'json');
    }

    /**
     * [ajax_brand ajax查询品牌]
     * @return [type] [description]
     */
    public function ajax_brand() {
        $result = $this->brand_service->ajax_brand($_REQUEST['brandname']);
        if (!$result) {
            showmessage($this->spu_service->errors, '', 0, '', 'json');
        } else {
            showmessage(L('_os_success_'), '', 1, $result, 'json');
        }
    }

    /**
     * [ajax_sn ajax更改商品货号]
     * @return [type] [description]
     */
    public function ajax_sn() {
        $_POST['sn'] = $_POST['name'];
        unset($_POST['name']);
        $result = $this->spu_service->change_spu_info($_POST);
        if (!$result) {
            showmessage($this->spu_service->errors, '', 0, '', 'json');
        } else {
            showmessage(L('_os_error_'), '', 1, '', 'json');
        }
    }

    /**
     * [ajax_name ajax更改商品名称]
     * @return [type] [description]
     */
    public function ajax_name() {
        $result = $this->spu_service->change_spu_info($_POST);
        if (!$result) {
            showmessage($this->spu_service->errors, '', 0, '', 'json');
        } else {
            showmessage(L('_os_error_'), '', 1, '', 'json');
        }
    }

    /**
     * [ajax_name ajax更改排序]
     * @return [type] [description]
     */
    public function ajax_sort() {
        $result = $this->spu_service->change_spu_info($_POST);
        if (!$result) {
            showmessage($this->spu_service->errors, '', 0, '', 'json');
        } else {
            showmessage(L('_os_error_'), '', 1, '', 'json');
        }
    }

    /**
     * [ajax_name ajax更改商品上下架]
     * @return [type] [description]
     */
    /*
      public function ajax_status(){
      $result = $this->spu_service->change_status($_POST['id'],$_POST['type']);
      if(!$result){
      showmessage($this->spu_service->errors,'',0,'','json');
      }else{
      showmessage(L('_os_error_'),'',1,'','json');
      }
      }
     */

    /**
     * [ajax_del 删除商品，在商品列表里删除只改变状态，在回收站里删除直接删除]
     * @return [type]         [description]
     */
    public function ajax_del() {

        $id = (array) $_REQUEST['id'];

        $result = $this->spu_service->del($id, true);
        if (!$result) {
            showmessage($this->spu_service->errors, U('index'));
        } else {
            showmessage(L('_os_success_'), U('index'), 1);
        }
    }

}
