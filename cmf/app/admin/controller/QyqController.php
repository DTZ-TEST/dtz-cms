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

class QyqController extends AdminBaseController
{

    public function add_qyq()
    {
        return $this->fetch();
    }

    public function qyq_detail()
    {
        return $this->fetch();
    }

    public function qyq_list()
    {
        $key = input('key');
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 10;//
        if($key&&$key!=="")
        {
            $gid = $key;
        }else{
            $gid = 666;
        }
        $count = db('t_group_user','mysql1')->where('groupId',$gid)->count();//计算总页面
        $allpage = intval(ceil($count[0]['numbers'] / $limits));
        $lists = db('t_group_user','mysql1')->where('groupId',$gid)->page($Nowpage, $limits)->select();
        if(input('get.page'))
        {
            return json($lists);
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('val', $key);
        return $this->fetch();
    }

    public function get_uid_bygroup()
    {
        $key = input('key');
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 20;//

        if($key&&$key!=="")
        {
            $userId = $key;
        }else{
            $userId = 666;
        }
        $lists = db('t_group_user','mysql1')->where('userId',$userId)->page($Nowpage, $limits)->select();
        if(input('get.page'))
        {
            return json($lists);
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', 1); //总页数
        $this->assign('val', $key);
        return $this->fetch();
    }

    public function get_hhr_data()
    {
        $key = input('key');
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 20;//
        $where = " 1";
        $s_time = date('Y-m-d',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))));
        $start_time = input('start_time') ? input('start_time') : $s_time;
        $end_time   = input('end_time') ? input('end_time') : $s_time;
        $userId     = input('userId') ? input('userId') : 0;
        if($key&&$key!=="")
        {
            $groupId = $key;
        }else{
            $groupId = 666;
        }
        $where .= " and groupId = $groupId and userId = $userId";
        if(!empty($start_time) && !empty($end_time)) {
            $start_date = date('Ymd',strtotime($start_time));
            $end_date = date('Ymd',strtotime($end_time));
            $where .= " and dataDate >= $start_date and dataDate<= $end_date";
            $lists = db('log_group_commission')->where($where)->page($Nowpage, $limits)->order('dataDate desc')->select();
        }else{

            $lists = [];
        }

        if(input('get.page'))
        {
            return json($lists);
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', 1); //总页数
        $this->assign('val', $key);
        $this->assign('userId', $userId);
        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);
        return $this->fetch();
    }
    /**
     * 修改玩家密码
    */
    public function reset_pwd() {
        return $this->fetch();
    }

    public function save_password () {
        $password = input('password');
        $userId   = input('userId');
        $userInfo = db('user_inf','mysql1')->where('userId',$userId)->find();
        if(empty($userInfo)) {
            return json(['code'=>-1,'msg'=>'用户不存在']);
        }
        if(!empty($password)){
            $newpassword = md5($password.'sanguo_shangyou_2013');
            $data['pw'] = $newpassword;
            $res = db('user_inf','mysql1')->where('userId',$userId)->update($data);
            if($res!==false){
                return json(['code'=>1,'msg'=>'修改成功']);
            }
        }
    }

    public function reset_state () {
        $userState = input('userState');
        $userId   = input('userId');
        $userInfo = db('user_inf','mysql1')->where('userId',$userId)->find();
        if(empty($userInfo)) {
            return json(['code'=>-1,'msg'=>'用户不存在']);
        }
        if($userState>=0){
            $data['userState'] = $userState;
            $res = db('user_inf','mysql1')->where('userId',$userId)->update($data);
            if($res!==false){
                return json(['code'=>1,'msg'=>'修改成功']);
            }
        }
    }

}
