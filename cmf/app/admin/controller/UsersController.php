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
use think\Db;

class UsersController extends AdminBaseController
{
      /**
       *
      */
       public function lists () {
           $userId = input('key') ?? '';
           $userInfo = db('user_inf','mysql1')->where('userId',$userId)->select();
           if(input('get.page'))
           {
               return json($userInfo);
           }
           $this->assign('val',$userId);
           return $this->fetch();
       }


    /**
     * [user_state 用户状态]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function user_state()
    {
        $id = input('param.id');
        $userInfo = db('user_inf','mysql1')->where('userId',$id)->find();
        $status = $userInfo['userState'];//判断当前状态情况
        if($status==1)
        {
            $flag = db('user_inf','mysql1')->where('userId',$id)->update(['userState'=>0]);
            return json(['code' => 1, 'data' => $flag, 'msg' => '已禁止']);
        }
        else
        {
            $flag = db('user_inf','mysql1')->where('userId',$id)->update(['userState'=>1]);
            return json(['code' => 0, 'data' => $flag, 'msg' => '已开启']);
        }

    }
}
