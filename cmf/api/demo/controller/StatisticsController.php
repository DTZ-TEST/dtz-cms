<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------

namespace api\demo\controller;

use cmf\controller\RestBaseController;
use api\demo\command\Crypt;
/**
 * Class IndexController
 * @package api\demo\controller
 */
class StatisticsController extends RestBaseController
{
    /**
     * 定时移动当天平台统计数据
     */
    public function get_t_data() {
        $date_time = date('Y-m-d')." 00:00:00";
        $date1_time = date('Y-m-d',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))))." 00:00:00";
        $date1 = date('Ymd');
        $date = date('Ymd',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))));
        $where1 = " 1 and regTime>= '$date1_time' and regTime < '$date_time' ";
        $where2 = " currentDate>='$date1_time' and currentDate < '$date1_time' ";
        $where3 = " dataDate >= '$date1_time' AND dataDate < '$date_time'";
        $where4 = " dataDate >= '$date1' AND dataDate < '$date' ";

        $where5 = " dataType = 'decDiamond' AND dataDate >= '$date1' AND dataDate < '$date'";
        $where6 = " userId>=0";
        $xzdata = db('user_inf','mysql1')->where($where1)->count();
        $hydata = db('t_login_data','mysql1')->where($where2) ->group('userId')->count();
        $djdata = db('log_group_table','mysql1')->where($where4) ->group('userId')->count();
        $zjs = db('log_group_table','mysql1')->where($where4)->field("sum(player2count1 / 2 + player2count2 / 2 + player3Count1 / 3 + player3Count2 / 3 + player4count1 / 4 + player4count2 / 4) AS Dtotal")->select();
        $xjs = db('log_group_table','mysql1')->where($where4)->field('ROUND(sum(player2count3 / 2 + player3Count3 / 3 + player4count3 / 4)) AS Xtotal')->select();
        $card_xh = db('t_data_statistics','mysql1')->where($where5)->sum('dataValue');;

        $card_sy1 = db('user_inf','mysql1')->where($where6)->sum('freeCards');
        $card_sy2 = db('user_inf','mysql1')->where($where6)->sum('cards');
        $parm['xzdata'] = $xzdata;
        $parm['hydata'] = $hydata;
        $parm['djdata'] = $djdata;
        $parm['zjs'] = $zjs[0]['Dtotal'];
        $parm['xjs'] = $xjs[0]['Xtotal'];
        $parm['card_xh'] = $card_xh;
        $parm['card_sy'] = $card_sy1 + $card_sy2;
        $parm['tdate']   = date('Y-m-d H:i;s',strtotime('-1 day'));
        $res = db('statistics_pt')->insert($parm);
        if($res!==false) {
            apilog('平台数据添加成功',1);
            $this->success('请求成功!', ['code' => 1, 'data' => '', 'msg' => '添加数据成功']);
        }else{
            $this->error('请求失败!');
        }
    }

    /**
     * 定时移动当天亲友圈统计数据
     */
    public function get_qyq_datad() {
        $datas = $this->request->param();
        $datex = $datas['date'];
        $group_list = db('t_group','mysql1')->alias('a')->join('t_group_user b','a.groupId = b.groupId')
            ->where('a.parentGroup=0')
            ->field('a.groupId,b.promoterId1')
            ->group('a.groupId')
            ->select();
        if(count($group_list)<1){
            return ['code' => -1, 'msg' => '异常'];
        }

        foreach ($group_list as $key=>$v) {
            $data[$key]['groupId'] = $v['groupId'];
            $data[$key]['userId']  = $v['promoterId1'];
            $data[$key]['tdate']   = $datex;
            $data[$key]['xzdata']  = $this->get_qyq_xzdatas($v['groupId'],$datex);
            $data[$key]['djdata']  = $this->get_qyq_hydatas($v['groupId'],$datex);
            $data[$key]['zjs']     = $this->get_qyq_zjss($v['groupId'],$datex);
            $data[$key]['xjs']     = $this->get_qyq_xjss($v['groupId'],$datex);
            $data[$key]['card_xh'] = $this->get_qyq_card_xhs($v['groupId'],$datex);
            $data[$key]['card_sy'] = $this->get_qyq_card_sy($v['promoterId1']);
        }
        db('statistics_qyq')->insertAll($data);
        apilog('亲友圈数据添加成功',1);
        $this->success('请求成功!', ['code' => 1, 'data' => '', 'msg' => '添加数据成功']);
    }

    /**
     * 按日期移动当天平台统计数据
     */
    public function get_t_datad() {

        $datas = $this->request->param();
        $datex = $datas['date'];
        $date_time = $datex." 23:59:59";
        $date1_time = $datex." 00:00:00";

        $date1 = date('Ymd',strtotime($datex));
        $where1 = " 1 and regTime>= '$date1_time' and regTime < '$date_time' ";
        $where2 = " currentDate>='$date1_time' and currentDate < '$date1_time' ";
        $where3 = " dataDate >= '$date1_time' AND dataDate < '$date_time'";
        $where4 = " dataDate = '$date1'";

        $where5 = " dataType = 'decDiamond' AND dataDate = '$date1'";
        $where6 = " userId>=0";
        $xzdata = db('user_inf','mysql1')->where($where1)->count();
        $hydata = db('t_login_data','mysql1')->where($where2) ->group('userId')->count();
        $djdata = db('log_group_table','mysql1')->where($where4) ->group('userId')->count();
        $zjs = db('log_group_table','mysql1')->where($where4)->field("sum(player2count1 / 2 + player2count2 / 2 + player3Count1 / 3 + player3Count2 / 3 + player4count1 / 4 + player4count2 / 4) AS Dtotal")->select();
        $xjs = db('log_group_table','mysql1')->where($where4)->field('ROUND(sum(player2count3 / 2 + player3Count3 / 3 + player4count3 / 4)) AS Xtotal')->select();
        $card_xh = db('t_data_statistics','mysql1')->where($where5)->sum('dataValue');

        $card_sy1 = db('user_inf','mysql1')->where($where6)->sum('freeCards');
        $card_sy2 = db('user_inf','mysql1')->where($where6)->sum('cards');
        $parm['xzdata'] = $xzdata;

        $parm['hydata'] = $hydata;
        $parm['djdata'] = $djdata;
        $parm['zjs'] = $zjs[0]['Dtotal'];
        $parm['xjs'] = $xjs[0]['Xtotal'];
        $parm['card_xh'] = $card_xh;
        $parm['card_sy'] = $card_sy1 + $card_sy2;

        $parm['tdate']   = date('Y-m-d H:i;s',strtotime('-1 day'));
        $res = db('statistics_pt')->insert($parm);
        if($res!==false) {
            apilog('平台数据添加成功',1);
            $this->success('请求成功!', ['code' => 1, 'data' => '', 'msg' => '添加数据成功']);
        }else{
            $this->error('请求失败!');
        }
    }
    /**
     * 定时移动当天亲友圈统计数据
     */
    public function get_qyq_data() {
        $group_list = db('t_group','mysql1')->alias('a')->join('t_group_user b','a.groupId = b.groupId')
            ->where('a.parentGroup=0')
            ->field('a.groupId,b.promoterId1')
            ->group('a.groupId')
            ->select();
        if(count($group_list)<1){
            return ['code' => -1, 'msg' => '异常'];
        }
        foreach ($group_list as $key=>$v) {
            $data[$key]['groupId'] = $v['groupId'];
            $data[$key]['userId']  = $v['promoterId1'];
            $data[$key]['tdate']   = date('Y-m-d H:i;s',strtotime('-1 day'));
            $data[$key]['xzdata']  = $this->get_qyq_xzdata($v['groupId']);
            $data[$key]['djdata']  = $this->get_qyq_hydata($v['groupId']);
            $data[$key]['zjs']     = $this->get_qyq_zjs($v['groupId']);
            $data[$key]['xjs']     = $this->get_qyq_xjs($v['groupId']);
            $data[$key]['card_xh'] = $this->get_qyq_card_xh($v['groupId']);
            $data[$key]['card_sy'] = $this->get_qyq_card_sy($v['promoterId1']);
        }
        db('statistics_qyq')->insertAll($data);
        apilog('亲友圈数据添加成功',1);
        $this->success('请求成功!', ['code' => 1, 'data' => '', 'msg' => '添加数据成功']);
    }



    /**
     * 统计亲友圈新增注册人数
    */
    public function get_qyq_xzdata($groupId) {
       // $date = date('Y-m-d')." 00:00:00";
      //$date1 = date('Y-m-d',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))))." 00:00:00";
        $date = date('Y-m-d')." 00:00:00";
        $date1 = date('Y-m-d',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))))." 00:00:00";
        $where = " groupId = $groupId AND `createdTime` >= '$date1' AND `createdTime` < '$date'";
        $list = db('t_group_user','mysql1')->where($where)->count();
        return $list;
    }
    /**
     * 统计亲友圈活跃人数
     */
    public function get_qyq_hydata($groupId) {
        $date1 = date('Ymd');
        $date = date('Ymd',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))));
        $where = " groupId = $groupId AND dataDate >= '$date' AND dataDate < '$date1'";
        $list = db('log_group_table','mysql1')->where($where)->count();
        return $list;
    }
    /**
     * 统计亲友圈总局数
     */
    public function get_qyq_zjs($groupId) {
        $date1 = date('Ymd');
        $date = date('Ymd',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))));
        $where = " dataDate >= '$date' AND dataDate < '$date1' AND groupId = $groupId ";
        $list = db('log_group_table','mysql1')->where($where)->field("ROUND(sum(player2count1 / 2 + player2count2 / 2 + player3Count1 / 3 + player3Count2 / 3 + player4count1 / 4 + player4count2 / 4)) AS Dtotal")->select();
        return $list[0]['Dtotal'];
    }
    /**
     * 统计亲友圈小局数
     */
    public function get_qyq_xjs($groupId) {
        $date1 = date('Ymd');
        $date = date('Ymd',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))));
        $where = " dataDate >= '$date' AND dataDate < '$date1' AND groupId = $groupId ";
        $list = db('log_group_table','mysql1')->where($where)->field("ROUND(sum(player2count3 / 2 + player3Count3 / 3 + player4count3 / 4)) AS Xtotal")->select();
        return $list[0]['Xtotal'];
    }
    /**
     * 统计亲友圈钻石消耗
     */
    public function get_qyq_card_xh($groupId) {
        $date1 = date('Ymd');
        $date = date('Ymd',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))));
        $where = " dataType = 'decDiamond' AND dataDate >= '$date' AND dataDate < '$date1' AND userId = $groupId";
        $list = db('t_data_statistics','mysql1')->where($where)->sum('dataValue');
        return $list;
    }
    /**
     * 统计亲友圈钻石剩余
     */
    public function get_qyq_card_sy($groupId) {
        $where = " userId = $groupId";
        $freeCards = db('user_inf','mysql1')->where($where)->sum('freeCards');
        $cards = db('user_inf','mysql1')->where($where)->sum('freeCards');
        return $freeCards + $cards;
    }

    /**
     * 统计亲友圈新增注册人数
     */
    public function get_qyq_xzdatas($groupId ,$date) {
        $date = $date." 23:59:59";
        $date1 = $date." 00:00:00";
        $where = " groupId = $groupId AND `createdTime` >= '$date1' AND `createdTime` < '$date'";
        $list = db('t_group_user','mysql1')->where($where)->count();
        return $list;
    }
    /**
     * 统计亲友圈活跃人数
     */
    public function get_qyq_hydatas($groupId ,$date) {
        $where = " groupId = $groupId AND dataDate = '$date'";
        $list = db('log_group_table','mysql1')->where($where)->count();
        return $list;
    }
    /**
     * 统计亲友圈总局数
     */
    public function get_qyq_zjss($groupId ,$date) {

        $where = " dataDate = '$date' AND groupId = $groupId ";
        $list = db('log_group_table','mysql1')->where($where)->field("ROUND(sum(player2count1 / 2 + player2count2 / 2 + player3Count1 / 3 + player3Count2 / 3 + player4count1 / 4 + player4count2 / 4)) AS Dtotal")->select();
        return $list[0]['Dtotal'];
    }
    /**
     * 统计亲友圈小局数
     */
    public function get_qyq_xjss($groupId ,$date) {
        $where = " dataDate = '$date' AND groupId = $groupId ";
        $list = db('log_group_table','mysql1')->where($where)->field("ROUND(sum(player2count3 / 2 + player3Count3 / 3 + player4count3 / 4)) AS Xtotal")->select();
        return $list[0]['Xtotal'];
    }
    /**
     * 统计亲友圈钻石消耗
     */

    public function get_qyq_card_xhs($groupId ,$date) {
        $where = " dataType = 'decDiamond' AND dataDate = '$date' AND userId = $groupId";
        $list = db('t_data_statistics','mysql1')->where($where)->sum('dataValue');
        return $list;
    }
    /**
     * 统计在线人数
     */
    public function get_nump() {

        $list = db('server_config','mysql1')->where('serverType',1)->select();
        if(count($list)>0){
            $cont = 0;
            foreach ($list as $key=>$v) {
                $data[$key]['type'] = $v['name'];
                $data[$key]['number'] = $v['onlineCount'];
                $data[$key]['addtime'] = date('Y-m-d H:i:s');
                $cont += $v['onlineCount'];
            }
            $data1['type'] = 0;
            $data1['number'] = $cont;
            $data1['addtime'] = date('Y-m-d H:i:s');
            $res = db('onlin')->insertAll($data);
            db('onlin')->insert($data1);
            if(!empty($res)){
                apilog('在线人数数据添加成功',1);
                return json(['code'=>1,'msg'=>'记录成功']);
            }
        }
    }

    /**
     * 转移统计数据
     */
    public function move_qyq_data() {
        $date1 = date('Ymd');
        $date = date('Ymd',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))));
        $where = " dataDate>= '$date' and dataDate< '$date1'";
        $data = db('log_group_commission','mysql1')->where($where)->select();
        $res = db('log_group_commission')->insertAll($data);
        if(!empty($res)){
            apilog('移动亲友圈数据成功',1);
            return json(['code'=>1,'msg'=>'记录成功']);
        }
    }

    /**
     * 获取平台统计数据
     */
    public function getpt_data() {
        $datex = $this->request->param('date');
        if(empty($datex)){
            $day = date('Y-m-d',strtotime(date("Y-m-d H:i:s",strtotime('-1 day'))));
            $date = date('Ymd',strtotime(date("Y-m-d H:i:s",strtotime('-1 day'))));
        }else{
            $day = $datex;
            $date = date('Ymd',strtotime($datex));
        }
        $info = $this->getpt_data_info($date);
        $infos = json_decode($info,true);
        if($infos['code']==0) {
            $data['tdate']  = $day;
            $data['xzdata'] = $infos['DNU'];
            $data['hydata'] = $infos['DAU'];
            $data['zjs']    = $infos['dailyDJ'];
            $data['xjs']    = $infos['dailyXJ'];
            $data['djdata'] = $infos['dailyPlayUserCount'];
            $data['card_xh']= $infos['cardConsume'];
            $data['card_sy']= $infos['cardRemain'];
            $res = db('statistics_pt')->insert($data);
            if($res!==false) {
                apilog('平台数据添加成功',1);
                $this->success('请求成功!', ['code' => 1, 'data' => '', 'msg' => '添加数据成功']);
            }else{
                $this->error('请求失败!');
            }
        }

    }


    /**
     * 获取平台统计数据
     */
    public function getpt_data_info($dates) {
            $sydate = $dates;
            $sytime = time();
            //$key    = get_api_key();
            $key = "7HGO4K61M8N2D9LARSPU";
            $sysign = $this->sysign($sytime,$key);
            $apiurl = "http://47.96.82.40:8081/pdklogin/qipai?actionType=12&funcType=1&date=".$dates."&sytime=".$sytime."&sysign=".$sysign;
            $info = cmf_curl_get($apiurl);
            return $info;
    }

    public function sysign ($sytime,$key) {
        return md5($sytime.$key);
    }
}
