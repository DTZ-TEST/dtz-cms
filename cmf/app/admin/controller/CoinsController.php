<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Released under the MIT License.
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use cmf\controller\AdminBaseController;

class CoinsController extends AdminBaseController
{

    public function index()
    {
        return $this->fetch();
    }

    public function lists()
    {
//        $key = input('key');
        $map = " 1";
        $start_time = input('start_time');
        $end_time = input('end_time');

//        if($key&&$key!=="")
//        {
//            $key = intval($key);
//            $map .= " and userId = $key";
//        }
        if(!empty($start_time) && !empty($end_time)){
            $start_time = $start_time;
            $end_time = $end_time;
        }else{
            $start_time = date('Ymd');
            $end_time = date('Ymd');
        }
        $map .= " and dateTime >= '$start_time' and dateTime <= '$end_time'";
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 20;// 获取总条数
        $count =db('gold_commom_statistics')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists =  db('gold_commom_statistics')->where($map)->page($Nowpage, $limits)->order('dateTime desc')->select()->toArray();
        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
//        $this->assign('val',$key );
//        $this->assign('types',$types);
        if(input('get.page'))
        {
            return json($lists);
        }
        return $this->fetch();
    }

    public function fee_money()
    {
//        $key = input('key');
        $map = " 1";
        $start_time = input('start_time');
        $end_time = input('end_time');

//        if($key&&$key!=="")
//        {
//            $key = intval($key);
//            $map .= " and userId = $key";
//        }
        if(!empty($start_time) && !empty($end_time)){
            $start_time = $start_time;
            $end_time = $end_time;
        }else{
            $start_time = date('Ymd');
            $end_time = date('Ymd');
        }
        $map .= " and dateTime >= '$start_time' and dateTime <= '$end_time'";
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 20;// 获取总条数
        $count =db('gold_card_statistics')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists =  db('gold_card_statistics')->where($map)->page($Nowpage, $limits)->order('dateTime desc')->select()->toArray();
        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
//        $this->assign('val',$key );
//        $this->assign('types',$types);
        if(input('get.page'))
        {
            return json($lists);
        }
        return $this->fetch();
    }

}
