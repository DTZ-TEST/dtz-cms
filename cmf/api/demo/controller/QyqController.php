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
use think\Db;
/**
 * Class IndexController
 * @package api\demo\controller
 */
class QyqController extends RestBaseController
{
    public function index()
    {
        $admin_id = cmf_get_current_admin_id();
        $data = $this->request->param();
        //$list = db('user','mysql1')->where('id',1)->find();
        $this->success('请求成功!', ['test' => 'test', 'data' => $data]);
    }

    /**
     *添加亲友圈
    */
    public function add_qyq() {
            $mid = cmf_get_current_admin_id();
            $parm = $this->request->param();
            $parm['time'] = time();
            unset($parm['gameIds']);
            $apiurl = get_api_url()."createGroup.do";
            $parm['sign'] = checkSign($parm);
            $info = cmf_api_request($apiurl,$parm);
            $res_info = decrypt_info($info);
            if($res_info['code']===0){
                apilog($mid."添加亲友圈成功");
                return json($res_info);
            }else{
                return json($res_info);
            }
    }

    public function get_user() {
        $parm = $this->request->param();
        $uid = $parm['uid'];
        if($uid &&$uid>0){
            $userinfo = db('user_inf','mysql1')->where('userId',$uid)->find();
            if(!empty($userinfo)){
                return json(['code'=>1,'data'=>$userinfo]);
            }else{
                return json(['code'=>-1,'data'=>[],'msg'=>"暂无用户信息"]);
            }
        }else{
            return json([]);
        }
    }

    /**
     *根据亲友圈id获取资料
     */
    public function getid_by_detail() {
        $config = [
            'key' =>"bjdlimsam2019%@)" , //加密key
            'iv' => "bjdlimsam2019%@)" , //保证偏移量为16位
            'method' => 'AES-128-CBC' //加密方式  # AES-256-CBC等
        ];
        $aes = new Crypt($config);
        $gid = input('post.groupId');
        if($gid &&$gid>0){
            $userinfo = db('t_group','mysql1')->alias('a')
                ->join('t_group_user b','a.groupId = b.groupId')
                ->join('user_inf c','c.userId = b.promoterId1')
                ->field('a.isCredit, a.extMsg, a.groupId,a.groupState ,a.groupName, a.maxCount,a.currentCount, b.promoterId1 ,b.createdTime,b.userName,b.userNickname,c.phoneNum,c.cards,c.freeCards,c.name ,c.os')
                ->where('a.parentGroup=0')
                ->limit(1)
                ->select();
            $yesterday_start = date('Y-m-d',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))))." 00:00:00";
            $yesterday_end   = date('Y-m-d',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))))." 23:59:59";
            $month_start     = date('Y-m')."-01 00:00:00";
            $month_end       = date('Y-m-d H:i:s');
            $where = " 1 and tdate >= '$yesterday_start' and tdate<='$yesterday_end' and groupId = $gid";
            $where1 = " 1 and tdate >= '$month_start' and tdate<='$month_end 'and groupId = $gid";
            $yesterday_info =  db('statistics_qyq')->where($where)->sum('zjs');         //昨日局数
            $month_info =   db('statistics_qyq')->where($where1)->sum('zjs');             //本月局数
            if(!empty($userinfo)){
                $userlist = $userinfo[0];
                $userlist['phoneNum'] = $aes->aesDe($userlist['phoneNum']);
                $ext_info = json_decode($userlist['extMsg'],true);
                if(empty($ext_info['forbidden'])){
                    $userlist['kf_start'] = 1;
                }else{
                    $userlist['kf_start'] = 0;
                }
                return json(['code'=>1,'data'=>$userlist,'y_info'=>$yesterday_info,'m_info'=>$month_info]);
            }else{
                return json(['code'=>-1,'msg'=>"没有查到此亲友圈"]);
            }
        }else{
            return json([]);
        }
    }

    /**
     * 暂停亲友圈
     */
    public function stop_qyq() {
        if(input('post.')){
            $mid = cmf_get_current_admin_id();
            $parm = input('post.');
            $parm['time'] = time();
            $apiurl = $apiurl = get_api_url()."forbidGroup.do";
            $parm['sign'] = checkSign($parm);
            $info = cmf_api_request($apiurl,$parm);
            $res_info = decrypt_info($info);
            if($res_info['code']===0){
                apilog($mid."暂停亲友圈成功");
                return json($res_info);
            }else{
                return json($res_info);
            }
        }
    }

    /**
     * 关闭大联盟
     */
    public function stop_dlm() {
        if(input('post.')){
            $mid = cmf_get_current_admin_id();
            $parm = input('post.');
            $parm['time'] = time();
            $parm['optType'] = 2;
            $apiurl = $apiurl = get_api_url()."updateGroup.do";
            $parm['sign'] = checkSign($parm);
            $info = cmf_api_request($apiurl,$parm);
            $res_info = decrypt_info($info);
            if($res_info['code']===0){
                apilog($mid."关闭大联盟成功");
                return json($res_info);
            }else{
                return json($res_info);
            }
        }
    }

    /**
     * 转移亲友圈成员
     */
    public function zy_cy() {
        if(input('post.')){
            $mid = cmf_get_current_admin_id();
            $parm = input('post.');
            $parm['time'] = time();
            $parm['retainCredit'] = 0;
            $apiurl = $apiurl = get_api_url()."moveGroupUser.do";
            $parm['sign'] = checkSign($parm);
            $info = cmf_api_request($apiurl,$parm);
            $res_info = decrypt_info($info);
            if($res_info['code']===0){
                apilog($mid."转移亲友圈成员成功");
                return json($res_info);
            }else{
                return json($res_info);
            }
        }
    }
    /**
     * 踢出亲友圈
     */
    public function fireUser() {
        if(input('post.')){
            $mid = cmf_get_current_admin_id();
            $parm = input('post.');
            $parm['time'] = time();
            $apiurl = $apiurl = get_api_url()."fireUser.do";
            $parm['sign'] = checkSign($parm);
            $info = cmf_api_request($apiurl,$parm);
            $res_info = decrypt_info($info);
            if($res_info['code']===0){
                apilog($mid."踢出群成员成功");
                return json($res_info);
            }else{
                return json($res_info);
            }
        }
    }
    /**
     * 转换群主
     */
    public function move_qz() {
        if(input('post.')){
            $mid = cmf_get_current_admin_id();
            $parm = input('post.');
            $parm['time'] = time();
            $apiurl = $apiurl = get_api_url()."changeMaster.do";
            $parm['sign'] = checkSign($parm);
            $info = cmf_api_request($apiurl,$parm);
            if(empty($info)){
                return json(['code'=>-1,'message'=>"接口返回数据异常",'data'=>$info]);
            }
            $res_info = decrypt_info($info);
            if($res_info['code']===0){
                apilog($mid."转移群主成功");
                return json($res_info);
            }else{
                return json($res_info);
            }
        }
    }

    /**
     * 修改群最大人数
     */
    public function up_max_number() {
        if(input('post.')){
            $mid = cmf_get_current_admin_id();
            $parm = input('post.');
            $parm['time'] = time();
            $parm['optType'] = 1;
            $apiurl = $apiurl = get_api_url()."updateGroup.do";
            $parm['sign'] = checkSign($parm);
            $info = cmf_api_request($apiurl,$parm);
            $res_info = decrypt_info($info);
            if($res_info['code']===0){
                apilog($mid."修改群最大人数成功");
                return json($res_info);
            }else{
                return json($res_info);
            }
        }
    }
}
