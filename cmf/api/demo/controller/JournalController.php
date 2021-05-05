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
class JournalController extends RestBaseController
{
    public function add_cards() {
        if(input('post.')){
            $mid = cmf_get_current_admin_id();
            $apiurl = get_api_url()."changeUserCurrency.do";
            $parm = input('post.');
            $inser_parm['type'] = $parm['type'];
            $inser_parm['freeCards'] = $parm['freeCards'];
            $inser_parm['cards']     = $parm['cards'];
            $inser_parm['add_time']  = date('Y-m-d H:i:s');
            $inser_parm['admin_id']  = $mid;
            $inser_parm['userId']    = $parm['userId'];
            $sum_card = intval($parm['freeCards'])+intval($parm['cards']);
            $status =  db('cards_stock')->where('id',1)->value('status');
            if($status!=1){
                return json(['code'=>3,"message"=>"充值已经暂停,请联系管理员"]);
            }


            $t_card   = db('cards_stock')->where('id',1)->value('stock');
            if(intval($t_card)<$sum_card) {
                return json(['code'=>3,"message"=>"平台钻石不足,请联系管理员"]);
            }
            unset($parm['type']);
            $parm['time'] = time();
            $parm['changeType'] = 20001;
            $parm['sign'] = checkSign($parm);
            $info = cmf_api_request($apiurl,$parm);
            $res_info = decrypt_info($info);
            if($res_info['code']===0){
                $inser_parm['status']   = 1;
                Db::startTrans();
                $res2 = db('cards_stock')->where('id',1)->setDec('stock',$sum_card);
                $inser_parm['p_cards'] = $t_card - $sum_card;
                $res = db('cards_info')->insert($inser_parm);
                $res_infos = json_encode($res_info);
                apilog($mid."添加用户钻石成功".$res_infos);
                if(!empty($res) && !empty($res2)){
                    Db::commit();
                    return json($res_info);
                }else{
                    Db::rollback();
                }
            }else{
                return json($res_info);
            }
        }
    }

    public function get_pt_journal() {
        $t_card   = db('cards_stock')->where('id',1)->value('stock');
        return json(['code'=>1,'sums'=>$t_card]);
    }

    /**
    扣除钻石
     */
    public function kc_cards () {
        if(input('post.')){
            $mid = cmf_get_current_admin_id();
            $apiurl = get_api_url()."changeUserCurrency.do";
            $t_card   = db('cards_stock')->where('id',1)->value('stock');
            $parm = input('post.');
            $cardss = intval($parm['cards']);
            $parm['cards'] = 0-$cardss;
            $inser_parm['freeCards'] = $parm['freeCards'];
            $inser_parm['cards']     = $parm['cards'];
            $inser_parm['add_time']  = date('Y-m-d H:i:s');
            $inser_parm['admin_id']  = $mid;
            $inser_parm['userId']    = $parm['userId'];
            $parm['time'] = time();
            $parm['changeType'] = 20002;
            $parm['sign'] = checkSign($parm);
            $info = cmf_api_request($apiurl,$parm);
            $res_info = decrypt_info($info);
            if($res_info['code']===0){
                $inser_parm['status']   = 1;
                Db::startTrans();
                $res2 = db('cards_stock')->where('id',1)->setInc('stock',$cardss);
                $inser_parm['p_cards'] = $t_card + $cardss;
                $res = db('cards_kcinfo')->insert($inser_parm);
                $res_infos = json_encode($res_info);
                apilog($mid."扣除钻石成功".$res_infos);
                if(!empty($res) && !empty($res2)){
                    Db::commit();
                    return json($res_info);
                }else{
                    Db::rollback();
                }
            }else{
                return json($res_info);
            }
        }
    }

    /**
    清除钻石
     */
    public function qc_cards () {
        if(input('post.')){
            $mid = cmf_get_current_admin_id();
            $apiurl = get_api_url()."changeUserCurrency.do";
            $t_card   = db('cards_stock')->where('id',1)->value('stock');
            $parm = input('post.');
            $cardss = intval($parm['freeCards']);
            $parm['freeCards'] = 0 - $cardss;
            $inser_parm['freeCards'] = $parm['freeCards'];
            $inser_parm['cards']     = $parm['cards'];
            $inser_parm['add_time']  = date('Y-m-d H:i:s');
            $inser_parm['admin_id']  = $mid;
            $inser_parm['userId']    = $parm['userId'];
            $parm['time'] = time();
            $parm['changeType'] = 20002;
            $parm['sign'] = checkSign($parm);
            $info = cmf_api_request($apiurl,$parm);
            $res_info = decrypt_info($info);
            if($res_info['code']===0){
                $inser_parm['status']   = 1;
                Db::startTrans();
                $res2 = db('cards_stock')->where('id',1)->setInc('stock',$cardss);
                $inser_parm['p_cards'] = $t_card + $cardss;
                $res = db('cards_kcinfo')->insert($inser_parm);
                $res_infos = json_encode($res_info);
                apilog($mid."清除钻石成功".$res_infos);
                if(!empty($res) && !empty($res2)){
                    Db::commit();
                    return json($res_info);
                }else{
                    Db::rollback();
                }
            }else{
                return json($res_info);
            }
        }
    }
}
