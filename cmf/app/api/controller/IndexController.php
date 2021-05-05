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

/**
 * Class IndexController
 * @package apis\api\controller
 */
class IndexController extends RestBaseController
{
    public function index()
    {
        $data = $this->request->param();
        $this->success('请求成功!', ['test' => 'test', 'data' => $data]);
    }
}
