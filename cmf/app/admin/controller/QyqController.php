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
use think\facade\Db;

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
        $allpage = intval(ceil($count / $limits));
        $lists = db('t_group_user','mysql1')->where('groupId',$gid)->page($Nowpage, $limits)->select();
        $qz    = db('t_group_user','mysql1')->where('groupId',$gid)->where('userRole',0)->value('userId');
        if(!is_array($lists)){
            $lists = $lists->toArray();
            foreach ($lists as $keys=>$v) {
                $lists[$keys]['promoterIds'] = get_promoterIds($lists[$keys]['groupId'],$lists[$keys]['userRole'],$lists[$keys]['promoterLevel'],$qz,$lists[$keys]['userGroup'],$lists[$keys]['promoterId1'],$lists[$keys]['promoterId2'],$lists[$keys]['promoterId3'],$lists[$keys]['promoterId4']);
            }
        }
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

    public function ip_config()
    {
        return $this->fetch();
    }

    /**
     * 修改玩家密码
     */
    public function up_bind() {
        return $this->fetch();
    }

    /**
     * 修改玩家密码
     */
    public function up_shares() {
        return $this->fetch();
    }
    /**
     * 成员资料
    */
    public function user_infos () {
        return $this->fetch();
    }
    public function add_user() {
        return $this->fetch();
    }
    /**
     * 小组数据
     */
    public function list_infos () {
        $s_time = date('Ymd',strtotime(date('Y-m-d H:i:s',strtotime('-1 day'))));
        $key = input('key');
        $where = " 1";
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 10;//
        if($key&&$key!=="")
        {
            $gid = $key;
        }else{
            $gid = '';
        }
        $start_time = input('start_time') ?? $s_time;

        $where .=" and lg.dataDate=$start_time ";
        $count = db('log_group_commission','mysql1')->alias('lg')
            ->join('t_group_user tg','tg.groupId = lg.groupId and tg.userId = lg.userID')
            ->where('tg.groupId',$gid)
            ->where("tg.userRole = 10")
            ->where($where)
            ->count();//计算总页面

        $allpage = intval(ceil($count / $limits));
        $lists = db('log_group_commission','mysql1')->alias('lg')
            ->join('t_group_user tg','tg.groupId = lg.groupId and tg.userId = lg.userID')
            ->where('tg.groupId',$gid)
            ->where("tg.userRole = 10")
            ->where($where)
            ->field('tg.userId,tg.userName,lg.zjsCount,lg.totalPay,lg.commissionCount,lg.credit')
            ->order('lg.zjsCount desc')
            ->page($Nowpage, $limits)
            ->select();
        if(input('get.page'))
        {
            return json($lists);
        }
        $this->assign('start_time', $start_time); //当前页
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('val', $key);
        return $this->fetch();
    }

    /**
     * 单组数据
     */
    public function list_one_data() {
        $key = input('key');
        $userId = input('userId');
        $where = " 1";
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = 10;//
        if($key&&$key!=="")
        {
            $gid = $key;
            $where .=" and groupId = $gid";
        }else{
            $gid = '';
        }
        if($userId&&$userId!=="")
        {
            $where .=" and userId = $userId";
        }else{
            $where .=" and userId = 1";
        }
        $count = db('log_group_commission','mysql1')->where($where)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = db('log_group_commission','mysql1')->where($where)
            ->field('dataDate,zjsCount,totalPay,commissionCount,credit')
            ->order('dataDate desc')
            ->page($Nowpage, $limits)
            ->select();
        if(input('get.page'))
        {
            return json($lists);
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('userId', $userId); //总页数
        $this->assign('val', $key);
        return $this->fetch();
    }

    public function get_qyq_cydata() {
        return $this->fetch();
    }

    /**
     * 获取亲友圈成员数据
     */
    public function get_userList($groupId,$dates) {
        $dates = date('Ymd',strtotime($dates));
        if(!empty($groupId)){
            $userList = db('t_group_debug','mysql1')->where('groupId',$groupId)->value("whiteNameList");
            $sql = "SELECT lg.groupId,lg.userId,ui.`name`,lg.selfWinCredit,lg.selfZjsCount,lg.selfDyjCount FROM log_group_commission lg,user_inf ui WHERE lg.groupId='$groupId' AND lg.dataDate='$dates' AND FIND_IN_SET(lg.userId,'$userList') AND lg.userId=ui.userId ORDER BY lg.selfWinCredit DESC";
            if(empty($userList)){
                return json(['code'=>-1,'message'=>"暂无数据"]);
            }
            $list = Db::connect('mysql1')->query($sql);
            return $list;
        }else{
            return  0;
        }

    }

    public function execl_info() {
        $groupId = input('groupId');
        $date    = input('dates');
        $userList = $this->get_userList($groupId,$date);
        $data = [];
        foreach ($userList as $key =>$v) {
            array_push($data,$this->get_guid_by_List($groupId,$v['userId'],$date));
        }
        xtexport($data);
        exit;
//        $data = array(
//            array(
//                'title' => '玩家1',
//                'info' => [
//                    '赢局数：' => 'AAA',
//                    '赢分：' => 'AAA',
//                    '输局数：' => 'AAA',
//                    '输分：' => 'AAA',
//                    '总局数：' => 'AAA',
//                    '总输赢分：' => 'AAA',
//                ],
//                'rows' => [
//                    [
//                        1111,2222,3333,4444,5555,6666
//                    ],
//                    [
//                        111,222,333,444,555,666
//                    ]
//                ],
//                'rows1' => [
//                    [
//                        1111,2222,3333,444,5555,666
//                    ],
//                    [
//                        111,222,333,444,555,6166
//                    ]
//                ]
//            ), array(
//                'title' => '玩家2',
//                'info' => [
//                    '赢局数：' => 'AAA',
//                    '赢分：' => 'AAA2',
//                    '输局数：' => 'AAA',
//                    '输分：' => 'AAA',
//                    '总局数：' => 'AAA',
//                    '总输赢分：' => 'AAA',
//                ],
//                'rows' => [
//                    [
//                        1111,2222,3333,4444,5555,6666,7777
//                    ],
//                    [
//                        111,222,333,444,555,666,777
//                    ]
//                ],
//                'rows1' => [
//                    [
//                        1111,2222,3333,4444,5555,6666,7777
//                    ],
//                    [
//                        111,222,333,444,555,666,777
//                    ]
//                ]
//            )
//        );
    }

    /**
     * 获取亲友圈成员数据
     */
    public function get_guid_by_List($groupId,$userId,$date) {
        $begtime = $date. " 00:00:00";
        $endtime = $date. " 23:59:59";
        $where = " 1 and groupId = $groupId and userId = $userId and createdTime>= '$begtime'  AND createdTime<= '$endtime' and winLoseCredit>=0";
        $where1 = " 1 and groupId = $groupId and userId = $userId and createdTime>= '$begtime'  AND createdTime<= '$endtime' and winLoseCredit<0";
        $cont_info = db('t_table_user','mysql1')->where($where)->field('count(tableNo) as c ,sum(winLoseCredit) as b')->select();
        $cont_info1 = db('t_table_user','mysql1')->where($where1)->field('count(tableNo) as c ,sum(winLoseCredit) as b')->select();

        $sql = "SELECT ss.logId,ss.tableId,tu.userId,gu.`name`,tu2.winLoseCredit FROM t_table_user tu,(
        SELECT tt.tableNo,tt.tableId,tt.logId FROM t_table_record tt,t_group_table gt WHERE tt.tableNo in( SELECT tableNo FROM t_table_user WHERE groupId='$groupId' AND userId=$userId AND createdTime>='$begtime'  AND createdTime<='$endtime' AND winLoseCredit>=0) AND tt.tableNo=gt.keyId
        ) ss, t_table_user tu2,user_inf gu WHERE tu.tableNo=ss.tableNo AND tu2.tableNo=ss.tableNo AND gu.userId=tu.userId  AND tu.userId!='$userId' AND tu2.userId='$userId'";

        $sql1 = "SELECT ss.logId,ss.tableId,tu.userId,gu.`name`,tu2.winLoseCredit FROM t_table_user tu,(
SELECT tt.tableNo,tt.tableId,tt.logId FROM t_table_record tt,t_group_table gt WHERE tt.tableNo in( SELECT tableNo FROM t_table_user WHERE groupId='$groupId' AND userId=$userId AND createdTime>='$begtime'  AND createdTime<='$endtime' AND winLoseCredit<0) AND tt.tableNo=gt.keyId
) ss, t_table_user tu2,user_inf gu WHERE tu.tableNo=ss.tableNo AND tu2.tableNo=ss.tableNo AND gu.userId=tu.userId  AND tu.userId!='$userId' AND tu2.userId='$userId';
";
        $list = Db::connect('mysql1')->query($sql);
        $list1 = Db::connect('mysql1')->query($sql1);
        $username = db('user_inf','mysql1')->where('userId',$userId)->value('name');
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
            "输局数"   =>$cont_losejs,
            "输分数"  => $cont_lose,
            "赢局数"   => $cont_winjs,
            "赢分数"  => $cont_win,
            "总局数"    => $cont_zjs,
            "总输赢分"    => intval($cont_lose) + intval($cont_win)
        ];
        return ['username'=>$username,'userId'=>$userId,'cont_list'=>$cont_list,'list'=>$list,'list1'=>$list1];
    }

    public function get_qyq_pdkdata() {
        return $this->fetch();
    }

    public function execl_info_pdk()
    {
        $groupId = input('get.groupId');
        $start_time = input('get.start_time');
        $end_time = input('get.end_time');
        $start_time = $start_time . " 00:00:00";
        $endtime = $end_time . " 23:59:59";

        $where = " a.groupId = $groupId and a.createdTime >='$start_time' and a.createdTime<= '$endtime'";
        $wheres = " 1 and (t_group_table.playType = 11 or t_group_table.playType = 15 or t_group_table.playType = 16)";
        $res_info = db('t_table_user', 'mysql1')->alias('a')
            ->join('t_group_table', 'a.tableNo = t_group_table.keyId')
            ->join('user_inf', 'a.userId = user_inf.userId')
            ->where($wheres)
            ->where($where)
            ->field('sum(a.winLoseCredit) as syf, count(a.userId) as zjs, count(if(winLoseCredit>=0,a.userId,0)) as wjs,
                  count(if(winLoseCredit<0,a.userId,0)) as sjs,sum(if(winLoseCredit>=0,a.winLoseCredit,0)) as wfs,
                  sum(if(winLoseCredit<0,a.winLoseCredit,0)) as sfs, sum(a.winLoseCredit) as zfs,
                  a.userId , t_group_table.playType,t_group_table.keyId, t_group_table.tableName,user_inf.name')
            ->group('a.userId')
            ->order("syf desc")
            ->limit(30)
            ->select();
        xtexport_pdk($res_info);
        exit;
    }
}
