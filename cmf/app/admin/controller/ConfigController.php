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

class ConfigController extends AdminBaseController
{

    public function ip_config()
    {
        $url = db('config')->where('name','up_url')->value('value');
        $file =  file_get_contents($url);
        $data_info = decrypt_infos($file);
        if(is_array($data_info)){
            $httpList = $data_info['httpList']['ips'];
            $loginList = $data_info['loginList']['ips'];
            $hotList =   $data_info['hotList']['ips'];
            $this->assign('httpList',$httpList);
            $this->assign('loginList',$loginList);
            $this->assign('hotList',$hotList);
        }
        return $this->fetch();
    }
    /**
     * 修改IP
     */
    public function saves()
    {
        $url = db('config')->where('name','up_url')->value('value');
        $file =  file_get_contents($url);
        $data_info = decrypt_infos($file);
        if(input('post.')) {
            $parm = input('post.');
            if(is_array($data_info)){
                $data_info['httpList']['ips'] = $parm['httpList'];
                $data_info['loginList']['ips'] = $parm['loginList'];
                $data_info['hotList']['ips'] = $parm['hotList'];
            }
            $datas = encrypt_info(json_encode($data_info));
            $res = file_put_contents('configList.json',$datas);
            if($res!==false) {
                return json(["code"=>1,"message"=>"保存成功"]);
            }
        }
    }
    /**
     * 导出json
     */
    public function excels()
    {
        $filepath =  'configList.json';
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($filepath));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
    }

    /**
     * 托管状态
     */
    public function auto_play_off(){
        $info = db('t_resources_configs','mysql1')->where('msgKey','auto_play_off_servers')->value('msgValue');
        if($info!==false) {
            return json(['code'=>1,'message'=>'获取成功','auto_play_off'=>$info]);
        }
    }
    /**
     * 修改托管状态
     */
    public function auto_play_save(){
        if(input('post.')) {
            $auto_play_off = input('post.auto_play_off');
            $data['msgValue'] = $auto_play_off;
            $info = db('t_resources_configs', 'mysql1')->where('msgKey', 'auto_play_off_servers')->update($data);
            if ($info !== false) {
                return json(['code' => 1, 'message' => '修改成功']);
            }
        }
    }
}
