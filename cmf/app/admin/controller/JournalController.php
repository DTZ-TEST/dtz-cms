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

class JournalController extends AdminBaseController
{

    /**
     *房钻管理
     */
    public function list_info() {
        return $this->fetch();
    }

    /**
     *赠钻记录
     */
    public function journal_log() {
        $key = input('key');
        $map = " 1";
        $start_time = input('start_time');
        $end_time = input('end_time');
        $types     = input('types');
        if($types&&$types!==0)
        {
            $types = intval($types);
            $map .= " and type = $types";
        }
        if($key&&$key!=="")
        {
            $key = intval($key);
            $map .= " and userId = $key";
        }
        if(!empty($start_time) && !empty($end_time) ){
            $start_time = $start_time;
            $end_time = $end_time;
        }else{
            $start_time = date('Y-m-d')." 00:00:00";
            $end_time = date('Y-m-d')." 23:59:59";
        }
        $map .= " and add_time >= '$start_time' and add_time <= '$end_time'";
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 20;// 获取总条数
        $count =db('cards_info')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists =  db('cards_info')->where($map)->page($Nowpage, $limits)->order('add_time desc')->select()->toArray();
        foreach ($lists as $k=>$v) {
            if($v['type']==1) {
                $lists[$k]['typename'] = "赠送";
            }else if($v['type']==2){
                $lists[$k]['typename'] = "补偿";
            }else if($v['type']==3){
                $lists[$k]['typename'] = "扣除";
            }else if($v['type']==4){
                $lists[$k]['typename'] = "清空";
            }
            $lists[$k]['nickname'] = getuid_byname($v['admin_id']);
        }
        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('val',$key );
        $this->assign('types',$types);
        if(input('get.page'))
        {
            return json($lists);
        }
        return $this->fetch();
    }

    /**
     *扣除记录
     */
    public function kclist_info() {
        $key = input('key');
        $map = " 1";
        $start_time = input('start_time');
        $end_time = input('end_time');
        if($key&&$key!=="")
        {
            $key = intval($key);
            $map .= " and userId = $key";
        }
        if(!empty($start_time) && !empty($end_time) ){
            $start_time = $start_time;
            $end_time = $end_time;
        }else{
            $start_time = date('Y-m-d')." 00:00:00";
            $end_time = date('Y-m-d')." 23:59:59";
        }
        $map .= " and add_time >= '$start_time' and add_time <= '$end_time'";
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 20;// 获取总条数
        $count = db('cards_kcinfo')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));


        $lists = db('cards_kcinfo')->where($map)->page($Nowpage, $limits)->select();
        foreach ($lists as $k=>$v) {
            $lists[$k]['nickname'] = getuid_byname($v['admin_id']);
        }
        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('val',$key );
        if(input('get.page'))
        {
            return json($lists);
        }
        return $this->fetch();
    }
}
