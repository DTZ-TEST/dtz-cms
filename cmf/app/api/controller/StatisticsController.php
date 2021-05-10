<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------

namespace app\api\controller;

use cmf\controller\RestBaseController;
use app\api\command\Crypt;
/**
 * Class IndexController
 * @package apis\api\controller
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
        $date_c = date('Y-m-d',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))));
        $date = date('Ymd',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))));
        $where1 = " 1 and regTime>= '$date1_time' and regTime < '$date_time' "; //查询新增注册人数
        $where2 = " currentDate = '$date'";                                         //查询活跃人数
        $where3 = " dataDate = '$date' and dataType='jlbDjs'";                     //查询大局数
        $where4 = " dataDate = '$date' and dataType='djsCount'";                   //查询玩牌用户数
        $xjs_where = " dataDate = '$date' and dataType='xjsCount'";                 //查询小局数
        $where5 = "consumeDate = '$date_c'";                                           //查询钻石剩余
        $where6 = " userId>=0";
        $xzdata = db('user_inf','mysql1')->where($where1)->count();
        $hydata = db('t_login_data','mysql1')->where($where2) ->group('userId')->count();
        $djdata = db('t_data_statistics','mysql1')->where($where4)->group('userId')->count();
        $zjs = db('t_data_statistics','mysql1')->where($where3) ->sum('dataValue');
        $xjs = db('t_data_statistics','mysql1')->where($xjs_where) ->sum('dataValue');
        $card_info = db('roomcard_consume_statistics','mysql1')->field('commonCards,freeCards,freeCardSum,commonCardSum')->where($where5)->find();
        $card_xh = abs($card_info['commonCards'])+abs($card_info['freeCards']);
        $card_sy = intval($card_info['freeCardSum'])+intval($card_info['commonCardSum']);
        $parm['xzdata'] = $xzdata;
        $parm['hydata'] = $hydata;
        $parm['djdata'] = $djdata;
        $parm['zjs'] = $zjs;
        $parm['xjs'] = $xjs;
        $parm['card_xh'] = $card_xh;
        $parm['card_sy'] = $card_sy;
        $parm['tdate']   = date('Y-m-d H:i:s',strtotime('-1 day'));
        $d_date = date('Y-m-d',strtotime('-31 day'));;
        $d_where = " tdate <= '$d_date'";
        db('statistics_pt')->where($d_where)->delete();//清除一个月之前的统计数据
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
        $info =db('statistics_qyq')->where('tdate',$datex)->find();
        if(!empty($info)){
            return json(['code'=>-1,'message'=>$datex."日期已存在，无需重复生成"]);
        }
        $group_list = db('t_group_user','mysql1')
            ->where('userRole=0 and (groupId=3120 or groupId = 22200 or groupId = 2880)')
            ->field('groupId,userId')
            ->group('groupId')
            ->select();
        if(empty($group_list)) {
            return json(['code' => -3, 'message' => '没有亲友圈','data'=>$group_list]);
        }
        if(count($group_list)<1 || count($group_list)>1000){
            return json(['code' => -1, 'message' => '异常','data'=>$group_list]);
        }
        foreach ($group_list as $key=>$v) {
            $data[$key]['groupId'] = $v['groupId'];
            $data[$key]['userId']  = $v['userId'];
            $data[$key]['tdate']   = $datex;
            $data[$key]['xzdata']  = $this->get_qyq_xzdatas($v['groupId'],$datex);
            $data[$key]['djdata']  = $this->get_qyq_hydatas($v['groupId'],$datex);
            $data[$key]['zjs']     = $this->get_qyq_zjss($v['groupId'],$datex);
            $data[$key]['xjs']     = $this->get_qyq_xjss($v['groupId'],$datex);
            $data[$key]['card_xh'] = $this->get_qyq_card_xhs($v['groupId'],$datex);
            $data[$key]['card_sy'] = $this->get_qyq_card_sy($v['userId']);
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
        $where2 = " currentDate = '$date1'";
        $where3 = " dataDate = '$date1' and dataType='jlbDjs'";
        $where4 = " dataDate = '$date1' and dataType='djsCount'";
        $xjs_where = " dataDate = '$date1' and dataType='xjsCount'";

        $where5 = "consumeDate = '$datex'";
        $where6 = " userId>=0";
        $xzdata = db('user_inf','mysql1')->where($where1)->count();
        $hydata = db('t_login_data','mysql1')->where($where2) ->group('userId')->count();
        $djdata = db('t_data_statistics','mysql1')->where($where4)->group('userId')->count();
        $zjs = db('t_data_statistics','mysql1')->where($where3) ->sum('dataValue');
        $xjs = db('t_data_statistics','mysql1')->where($xjs_where) ->sum('dataValue');
        $card_info = db('roomcard_consume_statistics','mysql1')->field('commonCards,freeCards,freeCardSum,commonCardSum')->where($where5)->find();
        $card_xh = abs($card_info['commonCards'])+abs($card_info['freeCards']);
        $card_sy = intval($card_info['freeCardSum'])+intval($card_info['commonCardSum']);

        $parm['xzdata'] = $xzdata;
        $parm['hydata'] = $hydata;
        $parm['djdata'] = $djdata;
        $parm['zjs'] = $zjs;
        $parm['xjs'] = $xjs;
        $parm['card_xh'] = $card_xh;
        $parm['card_sy'] = $card_sy;
        $parm['tdate']   = $datex;
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
        $datex = date('Y-m-d',strtotime('-1 day'));
        $info =db('statistics_qyq')->where('tdate',$datex)->find();
        if(!empty($info)){
            return json(['code'=>-1,'message'=>$datex."日期已存在，无需重复生成"]);
        }
        $d_date = date('Y-m-d',strtotime('-31 day'));
        $d_where = " tdate <= '$d_date'";
        db('statistics_qyq')->where($d_where)->delete();//清除一个月之前的统计数据
        $group_list = db('t_group_user','mysql1')
            ->where('userRole=0 and (groupId=3120 or groupId = 22200 or groupId=2880)')
            ->field('groupId,userId')
            ->group('groupId')
            ->select();
        if(count($group_list)<1 || empty($group_list)){
            return ['code' => -1, 'msg' => '异常'];
        }else{
            foreach ($group_list as $key=>$v) {
                $data[$key]['groupId'] = $v['groupId'];
                $data[$key]['userId']  = $v['userId'];
                $data[$key]['tdate']   = $datex;
                $data[$key]['xzdata']  = $this->get_qyq_xzdata($v['groupId']);
                $data[$key]['djdata']  = $this->get_qyq_hydata($v['groupId']);
                $data[$key]['zjs']     = $this->get_qyq_zjs($v['groupId']);
                $data[$key]['xjs']     = $this->get_qyq_xjs($v['groupId']);
                $data[$key]['card_xh'] = $this->get_qyq_card_xh($v['groupId']);
                $data[$key]['card_sy'] = $this->get_qyq_card_sy($v['userId']);
            }
            db('statistics_qyq')->insertAll($data);
            apilog('亲友圈数据添加成功',1);
            $this->success('请求成功!', ['code' => 1, 'data' => '', 'msg' => '添加数据成功']);
        }
    }



    /**
     * 统计亲友圈新增注册人数
    */
    public function get_qyq_xzdata($groupId) {
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
        $groupId = "group".$groupId;
        $date = date('Ymd',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))));
        $where = " `dataCode` = '$groupId' AND `dataDate` = '$date' and dataType='djsCount'";
        $list = db('t_data_statistics','mysql1')->where($where)->count();
        return $list;
    }
    /**
     * 统计亲友圈总局数
     */
    public function get_qyq_zjs($groupId) {
        $date1 = date('Ymd');
        $groupId = "group".$groupId;
        $date = date('Ymd',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))));
        $where = " `dataCode` = '$groupId' AND `dataDate` = '$date' and dataType='jlbDjs'";
        $list = db('t_data_statistics','mysql1')->where($where)->value('dataValue');
        if(empty($list)){
            return 0;
        }
        return $list;
    }
    /**
     * 统计亲友圈小局数
     */
    public function get_qyq_xjs($groupId) {
        $date1 = date('Ymd');
        $groupId = "group".$groupId;
        $date = date('Ymd',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))));
        $where = " `dataCode` = '$groupId' AND `dataDate` = '$date' and dataType='xjsCount'";
        $list = db('t_data_statistics','mysql1')->where($where)->sum('dataValue');
        if(empty($list)){
            return 0;
        }
        return $list;
    }
    /**
     * 统计亲友圈钻石消耗
     */
    public function get_qyq_card_xh($groupId) {
        $groupId = "group".$groupId;
        $date1 = date('Ymd');
        $date = date('Ymd',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))));
        $where = " dataType = 'decDiamond' AND dataDate = '$date' AND `dataCode` = '$groupId'";
        $list = db('t_data_statistics','mysql1')->where($where)->value('dataValue');
        if(empty($list)){
            return 0;
        }
        return $list;
    }
    /**
     * 统计亲友圈钻石剩余
     */
    public function get_qyq_card_sy($userId) {
        $where = " userId = $userId";
        $freeCards = db('user_inf','mysql1')->where($where)->sum('freeCards');
        $cards = db('user_inf','mysql1')->where($where)->sum('Cards');
        return $freeCards + $cards;
    }

    /**
     * 统计亲友圈新增注册人数
     */
    public function get_qyq_xzdatas($groupId ,$date) {
        $date_end = $date." 23:59:59";
        $date_satart = $date." 00:00:00";
        $where = " groupId = $groupId AND `createdTime` >= '$date_satart' AND `createdTime` < '$date_end'";
        $list = db('t_group_user','mysql1')->where($where)->count();
        return $list;
    }
    /**
     * 统计亲友圈活跃人数
     */
    public function get_qyq_hydatas($groupId ,$date) {
        $date_info = date('Ymd',strtotime($date));
        $groupId = "group".$groupId;
        $where = " `dataCode` = '$groupId' AND `dataDate` = '$date_info' and dataType='djsCount'";
        $list = db('t_data_statistics','mysql1')->where($where)->count();
        return $list;
    }
    /**
     * 统计亲友圈总局数
     */
    public function get_qyq_zjss($groupId ,$date) {
        $datax = date('Ymd',strtotime($date));
        $groupId = "group".$groupId;
        $where = " 1 and dataDate = '$datax' AND dataCode = '$groupId' and dataType='jlbDjs'";
        $list = db('t_data_statistics','mysql1')->where($where)->value('dataValue');
        if(empty($list)){
            return 0;
        }
        return $list;
    }
    /**
     * 统计亲友圈小局数
     */
    public function get_qyq_xjss($groupId ,$date) {
        $datax = date('Ymd',strtotime($date));
        $groupId = "group".$groupId;
        $where = " 1 and dataDate = '$datax' AND dataCode = '$groupId' and dataType='xjsCount'";
        $list = db('t_data_statistics','mysql1')->where($where)->sum('dataValue');
        if(empty($list)){
            return 0;
        }
        return $list;
    }
    /**
     * 统计亲友圈钻石消耗
     */

    public function get_qyq_card_xhs($groupId ,$date) {
        $datax = date('Ymd',strtotime($date));
        $groupId = "group".$groupId;
        $where = " dataType = 'decDiamond' AND dataDate = '$datax' AND dataCode = '$groupId'";
        $list = db('t_data_statistics','mysql1')->where($where)->value('dataValue');
        if(empty($list)){
            $list = 0;
        }
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
     *@每日数据
     */
    public function daily_stat() {
        $s_time = date('Y-m-d',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))))." 00:00:00";
        $e_time    = date('Y-m-d',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))))." 23:59:59";
        if(input('post.'))
        {
            $parm = input('post.');
            $start_time = $parm['start_time'];
            $end_time   = $parm['end_time'];
            if(!empty($start_time) && !empty($end_time)){

            }else{
                $start_time = date('Y-m-d',strtotime(date('Y-m-d H:i:s',strtotime('-7 day'))))." 00:00:00";
                $end_time   = date('Y-m-d H:i:s');
            }
            $where = " 1 and tdate >= '$start_time' and tdate<='$end_time'";
            $cont_list = db('statistics_pt')->where($where)->select();
            return json(['data'=>$cont_list,'code'=>1]);
        }
    }

    public function get_tdata() {
        $date = date('Y-m-d');
        $end_date = date('Y-m-d',strtotime('-7 day'));
        $wheres = " 1 and tdate>='$end_date'";
        $tdate = db('statistics_pt')->where($wheres)->column('tdate');
        $zcdata = db('statistics_pt')->where($wheres)->column('xzdata');
        $hydata = db('statistics_pt')->where($wheres)->column('hydata');
        $djdata = db('statistics_pt')->where($wheres)->column('djdata');
        $zjs = db('statistics_pt')->where($wheres)->column('zjs');
        $xjs = db('statistics_pt')->where($wheres)->column('xjs');
        $card_xh = db('statistics_pt')->where($wheres)->column('card_xh');
        $card_sy = db('statistics_pt')->where($wheres)->column('card_sy');
        return json(["code"=>1,"tdate"=>$tdate,"xzdata"=>$zcdata,"hydata"=>$hydata,"djdata"=>$djdata,"zjs"=>$zjs,"xjs"=>$xjs,"card_xh"=>$card_xh,"card_sy"=>$card_sy]);
    }

}
