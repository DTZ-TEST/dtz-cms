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
use OpenApi\Tests\Annotations\ValidateRelationsTest;
use think\facade\Db;
/**
 * Class IndexController
 * @package apis\api\controller
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
            $info = curl_post($apiurl,$parm);
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
        $parm = $this->request->param();
        $gid = $parm['groupId'];
        $wheres = " a.parentGroup=0 and a.groupId='$gid' and b.userRole=0";
        if($gid &&$gid>0){
            $userinfo = db('t_group','mysql1')->alias('a')
                ->join('t_group_user b','a.groupId = b.groupId')
                ->join('user_inf c','c.userId = b.userId')
                ->field('a.isCredit, a.extMsg, a.groupId,a.groupState ,a.groupName, a.maxCount,a.currentCount, b.userId ,b.createdTime,b.userName,b.userNickname,c.phoneNum,c.cards,c.freeCards,c.name ,c.os')
                ->where($wheres)
                ->limit(1)
                ->select()->toArray();
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
            $apiurl = $apiurl = get_api_url()."changeGroupMaster.do";
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

    /**
     * 修改绑定
     */
    public function up_user_bind() {
        if(input('post.')){
            $mid = cmf_get_current_admin_id();
            $parm = input('post.');
            $parm['time'] = time();
            $apiurl = $apiurl = get_api_url()."modifyUserBindmapping.do";
            $parm['sign'] = checkSign($parm);
            $info = cmf_api_request($apiurl,$parm);
            $res_info = decrypt_info($info);
            if($res_info['code']===0){
                apilog($mid."更换绑定成功");
                return json($res_info);
            }else{
                return json($res_info);
            }
        }
    }

    /**
     *查看拉手占成
     */
    public function get_creditCommissionRate () {
        $groupId = input('groupId');
        $userId   = input('userId');
        $where = " groupId = $groupId and userId = $userId";
        $creditCommissionRate = db('t_group_user','mysql1')->where($where)->find();
        if(!empty($creditCommissionRate)) {
            return json(['code'=>1,'creditCommissionRate'=>$creditCommissionRate['creditCommissionRate'],'userRole'=>$creditCommissionRate['userRole']]);
        }else{
            return json(['code'=>-1,'message'=>"用户信息不存在"]);
        }
    }

    /**
     *修改拉手占成
     */
    public function up_creditCommissionRate () {
        $groupId = input('groupId');
        $userId   = input('userId');
        $creditCommissionRate = input('creditCommissionRate');
        $where = " groupId = $groupId and userId = $userId and (userRole=10 or userRole=20)";
        $data['creditCommissionRate']  = $creditCommissionRate;
        $creditCommissionRate = db('t_group_user','mysql1')->where($where)->update($data);
        if(!empty($creditCommissionRate)) {
            return json(['code'=>1,'message'=>"修改成功"]);
        }
    }

    /**
     * 查看成员资料
    */
    public function get_user_infos () {
        $userId   = input('userId');
        $lists = db('t_group_user','mysql1')->where('userId',$userId)->select();
        if(empty($lists)) {
            return json(['code'=>-1,'message'=>"用户信息不存在"]);
        }
        if(!is_array($lists)){
            $lists = $lists->toArray();
            if(count($lists)==0){
                return json(['code'=>-1,'message'=>"用户信息不存在"]);
            }
            foreach ($lists as $keys=>$v) {
                $qz = db('t_group_user','mysql1')->where('groupId',$v['groupId'])->where('userRole',0)->value('userId');
                $lists[$keys]['promoterIds'] = get_promoterIds($lists[$keys]['groupId'],$lists[$keys]['userRole'],$lists[$keys]['promoterLevel'],$qz,$lists[$keys]['userGroup'],$lists[$keys]['promoterId1'],$lists[$keys]['promoterId2'],$lists[$keys]['promoterId3'],$lists[$keys]['promoterId4']);
            }
            return json(['code'=>1,'message'=>"获取成功",'data'=>$lists]);
        }
    }


    /**
     * 获取亲友圈成员数据
     */
    public function getgid_by_userList() {
        $groupId = input('post.groupId');
        $dates =  input('post.dates');
        $dates = date('Ymd',strtotime($dates));
        if(!empty($groupId)){
            $userList = db('t_group_debug','mysql1')->where('groupId',$groupId)->value("whiteNameList");
            $sql = "SELECT lg.groupId,lg.userId,ui.`name`,lg.selfWinCredit,lg.selfZjsCount,lg.selfDyjCount FROM log_group_commission lg,user_inf ui WHERE lg.groupId='$groupId' AND lg.dataDate='$dates' AND FIND_IN_SET(lg.userId,'$userList') AND lg.userId=ui.userId ORDER BY lg.selfWinCredit DESC";
            if(empty($userList)){
                return json(['code'=>-1,'message'=>"暂无数据"]);
            }
            $list = Db::connect('mysql1')->query($sql);

            return json(['code'=>1,'userList'=>$list]);
        }

    }

    /**
     * 获取亲友圈成员数据
     */
    public function get_guid_by_List() {
        $groupId = input('post.groupId');
        $userId  = input('post.userId');
        $date    = input('post.dates');
        $begtime = $date. " 00:00:00";
        $endtime = $date. " 23:59:59";
        $where = " 1 and groupId = $groupId and userId = $userId and createdTime>= '$begtime'  AND createdTime<= '$endtime' and winLoseCredit>=0";
        $where1 = " 1 and groupId = $groupId and userId = $userId and createdTime>= '$begtime'  AND createdTime<= '$endtime' and winLoseCredit<0";
        $cont_info = db('t_table_user','mysql1')->where($where)->field('count(tableNo) as c ,sum(winLoseCredit) as b')->select();
        $cont_info1 = db('t_table_user','mysql1')->where($where1)->field('count(tableNo) as c ,sum(winLoseCredit) as b')->select();

        $sql = "SELECT ss.playType,ss.logId,ss.tableId,tu.userId,gu.`name`,tu2.winLoseCredit FROM t_table_user tu,(
        SELECT tt.tableNo,tt.tableId,gt.playType,tt.logId FROM t_table_record tt,t_group_table gt WHERE tt.tableNo in( SELECT tableNo FROM t_table_user WHERE groupId='$groupId' AND userId=$userId AND createdTime>='$begtime'  AND createdTime<='$endtime' AND winLoseCredit>=0) AND tt.tableNo=gt.keyId
        ) ss, t_table_user tu2,user_inf gu WHERE tu.tableNo=ss.tableNo AND tu2.tableNo=ss.tableNo AND gu.userId=tu.userId  AND tu.userId!='$userId' AND tu2.userId='$userId'";

        $sql1 = "SELECT ss.playType,ss.logId,ss.tableId,tu.userId,gu.`name`,tu2.winLoseCredit FROM t_table_user tu,(
SELECT tt.tableNo,tt.tableId,gt.playType,tt.logId FROM t_table_record tt,t_group_table gt WHERE tt.tableNo in( SELECT tableNo FROM t_table_user WHERE groupId='$groupId' AND userId=$userId AND createdTime>='$begtime'  AND createdTime<='$endtime' AND winLoseCredit<0) AND tt.tableNo=gt.keyId
) ss, t_table_user tu2,user_inf gu WHERE tu.tableNo=ss.tableNo AND tu2.tableNo=ss.tableNo AND gu.userId=tu.userId  AND tu.userId!='$userId' AND tu2.userId='$userId';
";
        $list = Db::connect('mysql1')->query($sql);
        $list1 = Db::connect('mysql1')->query($sql1);
        if(empty($cont_info)) {
            $cont_win = 0;
            $cont_winjs = 0;
        }else{
            $cont_win = $cont_info[0]['b'];
            $cont_winjs = $cont_info[0]['c'];
        }
        if(empty($cont_info1)){
            $cont_lose = 0;
            $cont_losejs = 0;
        }else{
            $cont_lose = $cont_info1[0]['b'];
            $cont_losejs = $cont_info1[0]['c'];
        }

        $cont_zjs = intval($cont_winjs) + intval($cont_losejs);
        $cont_list = [
            "lose_credit" => $cont_lose,
            "win_credit"  => $cont_win,
            "cont_zjs"    => $cont_zjs,
            "lose_js"     =>$cont_losejs,
            "win_js"      => $cont_winjs
        ];
        return json(['code'=>1,'cont_list'=>$cont_list,'list'=>$list,'list1'=>$list1]);
    }

    /**
     * 添加账号
     */
    public function add_member() {
        if(input('post.')){
            $mid = cmf_get_current_admin_id();
            $parm = input('post.');
            $userinfo = db('user_inf','mysql1')->where('flatId',$parm['u'])->find();
            if(!empty($userinfo)){
                $r_data['msg'] = "用户名已存在了,请重新输入！";
                $r_data['code'] = -1;
                return json($r_data);
            }
            $parm['t'] = time();
            $parm['p'] = "";
            $apiurl = $apiurl = get_api_url()."user!register.guajilogin";
            $parm['k'] = checkSign02($parm);
            $info = cmf_api_request($apiurl,$parm);
            $res_info = decrypt_info($info);
            if($res_info['code']===0){
                $res = $this->up_userinfo($parm['u']);
                if($res!=0){
                    apilog($mid."后台注册会员成功");
                    return json($res_info);
                }

            }else{
                return json($res_info);
            }
        }
    }

    /**
     * 注册成功以后修改会员信息
    */
    private function up_userinfo($u) {
        $where = " flatId = '$u'";
        $user_info = db('user_inf','mysql1')->where($where)->find();
        if(!empty($user_info)) {
            $upd['phoneNum'] = $u;
            $upd['phonePw'] = $user_info['pw'] ;
            return db('user_inf','mysql1')->where($where)->update($upd);
        }else{
            return 0;
        }
    }

    /**
     * 根据亲友圈id 时间段查询排名前30玩家
     */
    public function getqid_by_pdkdata () {
        $groupId = input('post.groupId');
        $start_time    = input('post.start_time');
        $end_time    = input('post.end_time');
        $start_time = $start_time. " 00:00:00";
        $endtime = $end_time. " 23:59:59";

        $where = " a.groupId = $groupId and a.createdTime >='$start_time' and a.createdTime<= '$endtime'";
        $wheres = " 1 and (t_group_table.playType = 11 or t_group_table.playType = 15 or t_group_table.playType = 16)";
        $res_info = db('t_table_user', 'mysql1')->alias('a')
                  ->join('t_group_table','a.tableNo = t_group_table.keyId')
                  ->join('user_inf','a.userId = user_inf.userId')
                  ->where($wheres)
                  ->where($where)
                  ->field('sum(a.winLoseCredit) as syf, count(a.userId) as zjs, count(if(winLoseCredit>=0,true,null)) as wjs,
                  count(if(winLoseCredit<0,true,null)) as sjs,sum(if(winLoseCredit>=0,a.winLoseCredit,0)) as wfs,
                  sum(if(winLoseCredit<0,a.winLoseCredit,0)) as sfs, sum(a.winLoseCredit) as zfs,
                  a.userId , t_group_table.playType,t_group_table.keyId, t_group_table.tableName,user_inf.name')
                  ->group('a.userId')
                  ->order("syf desc")
                  ->limit(30)
                  ->select();
        return json(['code'=>1,'userList'=>$res_info]);

    }

}
