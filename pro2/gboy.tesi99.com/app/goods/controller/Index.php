<?php
namespace app\goods\controller;
use app\common\controller\Base;
use app\goods\service\Content;
use app\goods\service\Sku;
use app\goods\service\Spec;
use app\goods\service\Spu;
use app\goods\service\District;
use app\goods\service\Comment;
use app\ads\service\Ads;
class Index extends Base
{
	public function __construct()
    {
        parent::__construct();

	}
	//商城首页
    public function index()
    {
        $contentServer = new Content();
        $adsServer = new Ads();
        $contentList = $contentServer->getList("recommend = 1", 'id, title',1, 5); //轮播推荐动态
        $now = time();
        $bannerList = $adsServer->get_list("status = 1 and {$now} >= starttime (and {$now} <= endtime or endtime = 0)");
        //商品列表
        $spuServer = new Spu();
        $goodList = $spuServer->get_index();
        $this->assign('goodList', $goodList);
        $this->assign('bannerList', $bannerList);
        $this->assign('contentList', $contentList);
        return $this->fetch();
    }
    //商城动态
    public function scdt()
    {
        $contentServer = new Content();
        $content = $contentServer->getList(array('display' => 1), 'title, content, thumb, add_time', 1, 10);
        $this->assign('content', $content);
        return $this->fetch();
    }
    //获取动态列表
    public function scdtlist()
    {
        $page = input('post.page') ? input('post.page') : 1;
        $contentServer = new Content();
        $content = $contentServer->getList(array('display' => 1), 'title, content, thumb, add_time', $page, 10);
        $this->assign('content', $content);
        return $this->fetch();

    }

    // ajaxj获取数据
    public function details_spec(){
        if(is_ajax){
            // 获取数据
            $post = input('post.');
            $sqlmap['spec'] = $post['spec'];
            $sqlmap['spu_id'] = $post['spu_id'];
            $skuServer = model('goods/Sku', 'model');
            $result = $skuServer->getOne($sqlmap);
            showmessage('', '', 1, $result);
        }
    }
    //商品列表
    public function goodlist()
    {
        $status = input('get.type');
        $skuServer = new Sku();
        if(!$status) {
            $where = '';
        } else {
            $where = "type = {$status} and status = 1";
        }
        $goodList = $skuServer->get_list($where, false);
        if($goodList) {
            foreach($goodList as $k => $v) {
                if($sale = json_decode($v['sale'], true)) {
                    $goodList[$k]['all'] = $sale['all'];
                }
            }
        }
        $this->assign('goodList', $goodList);
        return $this->fetch();
    }
    //商品详情
    public function details(){
	    $spu_id = input('get.id');
        $where=array();
        $where['status']=1;
        $where['spu_id']=$spu_id;
        $skuServer = new Sku();
	    $info = $skuServer->getGood($where);
	    if(!$info){
            error_status(404);
        }
        if($info['imgs']) {
            $config = config('aliyun_oss');
            $info['imgs'] = json_decode($info['imgs']);
            foreach($info['imgs'] as $kk => $vv) {
                $info['imgs'][$kk] = $config['Url'].$vv;
            }
        } else {
            $info['imgs'] = [];
        }
        $districeServer = new District();
	    $info['province'] = !empty($info['province']) ? $districeServer->getNameById($info['province']) : '';
	    $info['city'] = !empty($info['city']) ? $districeServer->getNameById($info['city']) : '';
        $info['description'] = html_entity_decode($info['description'], ENT_QUOTES,"UTF-8");
        if($sale = json_decode($info['sale'], true)) {
            $info['month'] = $sale['month'];
        }else{
            $info['month'] = 0;
        }
        //评论列表
        $commentServer = new Comment();
        $comment = $commentServer->getComment($info['sku_id']);

        //规格
        $spec = db('goods_spec')->where(array('spu_id' => $spu_id))->order("id asc")->select();
        foreach ($spec as $k => $v) {
            $vs = explode(',',$v['value']);
            $spec[$k]['value'] = $vs;
            $color = explode(',',$v['color']);
            $spec[$k]['color'] = $color;
            $img = explode(',',$v['img']);
            $spec[$k]['img'] = $img;
        }

        $this->assign('spec', $spec);
        $this->assign('comment', $comment);
        $this->assign('info', $info);
	    return $this->fetch();
    }



    //二维数组去掉重复值
    function unique_arr($array2D,$stkeep=true,$ndformat=true)
    {
        // 判断是否保留一级数组键 (一级数组键可以为非数字)
        if($stkeep) $stArr = array_keys($array2D);
        // 判断是否保留二级数组键 (所有二级数组键必须相同)
        if($ndformat) $ndArr = array_keys(end($array2D));
        //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
        foreach($array2D as $v){
            $v = join(",",$v);
            $temp[] = $v;
        }

        //再将拆开的数组重新组装
        foreach ($temp as $k => $v)
        {
            if($stkeep) $k = $stArr[$k];
            if($ndformat)
            {
                $tempArr = explode(",",$v);
                //去掉重复的字符串,也就是重复的一维数组
                $tempArr = array_unique($tempArr);
                foreach($tempArr as $ndkey => $ndval) $output[$k][] = $ndval;
            }
            else $output[$k] = explode(",",$v);
        }
        return $output;
}


    public function consume()
    {
        $contentServer = new Content();
        $adsServer = new Ads();
        $contentList = $contentServer->getList("recommend = 1", 'id, title',1, 5);
        //商品列表
        $skuServer = new Sku();
        $goodList = $skuServer->get_list("type = 3 and status = 1", false);
        $now = time();
        $bannerList = $adsServer->get_list("status = 1 and {$now} >= starttime (and {$now} <= endtime or endtime = 0)");
        $this->assign('goodList', $goodList);
        $this->assign('bannerList', $bannerList);
        $this->assign('contentList', $contentList);
        return $this->fetch();
    }

    public function consumelist()
    {
        $page = input('post.page') ? input('post.page') : 1;
        $limit = 20;
        $skuServer = new Sku();
        $start = ($page - 1) * $limit;
        $goodList = $skuServer->get_list(array('type' => 3,'status' => 1), false, $start, $limit);
        $this->assign('goodList', $goodList);
        return $this->fetch();
    }
}
