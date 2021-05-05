<?php /*a:2:{s:52:"themes/admin_simpleboot3/admin\config\ip_config.html";i:1616395406;s:78:"D:\phpstudy_pro\WWW\dtz\cmf\public/themes/admin_simpleboot3/public\header.html";i:1614844967;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <!-- Set render engine for 360 browser -->
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- HTML5 shim for IE8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <![endif]-->
    <link href="/themes/admin_simpleboot3/public/assets/themes/<?php echo cmf_get_admin_style(); ?>/bootstrap.min.css" rel="stylesheet">
    <link href="/themes/admin_simpleboot3/public/assets/simpleboot3/css/simplebootadmin.css" rel="stylesheet">
    <link href="/static/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <!--[if lt IE 9]>
    <script src="https://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        form .input-order {
            margin-bottom: 0px;
            padding: 0 2px;
            width: 42px;
            font-size: 12px;
        }

        form .input-order:focus {
            outline: none;
        }

        .table-actions {
            margin-top: 5px;
            margin-bottom: 5px;
            padding: 0px;
        }

        .table-list {
            margin-bottom: 0px;
        }

        .form-required {
            color: red;
        }
    </style>
    <?php $_app=app()->http->getName(); ?>
    <script type="text/javascript">
        //全局变量
        var GV = {
            ROOT: "/",
            WEB_ROOT: "/",
            JS_ROOT: "static/js/",
            APP: '<?php echo $_app; ?>'/*当前应用名*/
        };
    </script>
    <script src="/themes/admin_simpleboot3/public/assets/js/jquery-1.10.2.min.js"></script>
    <script src="/static/js/wind.js"></script>
    <script src="/themes/admin_simpleboot3/public/assets/js/bootstrap.min.js"></script>
    <script>
        Wind.css('artDialog');
        Wind.css('layer');
        $(function () {
            $("[data-toggle='tooltip']").tooltip({
                container:'body',
                html:true,
            });
            $("li.dropdown").hover(function () {
                $(this).addClass("open");
            }, function () {
                $(this).removeClass("open");
            });
        });
    </script>
    <?php if(APP_DEBUG): ?>
        <style>
            #think_page_trace_open {
                z-index: 9999;
            }
        </style>
    <?php endif; ?>

</head>
<body>
<div class="wrap js-check-wrap" id="configs">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#A" data-toggle="tab">ip配置</a></li>
        <li><a href="#B" data-toggle="tab">托管状态修改</a></li>
    </ul>
    <form class="form-horizontal js-ajax-form margin-top-20" role="form" action=""
          method="post">
        <fieldset>
            <div class="tabbable">
                <div class="tab-content">
                    <div class="tab-pane active" id="A">
                        <div class="form-group">
                            <label for="input-site-name" class="col-sm-2 control-label"><span
                                    class="form-required">*</span>httpList</label>
                            <div class="col-md-6 col-sm-10">
                                <input type="text" class="form-control" id="input-site-name" name=""
                                       v-model="httpList">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="input-site-name" class="col-sm-2 control-label"><span
                                    class="form-required">*</span>loginList</label>
                            <div class="col-md-6 col-sm-10">
                                <input type="text" class="form-control"  name=""
                                       v-model="loginList">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="input-site-name" class="col-sm-2 control-label"><span
                                    class="form-required">*</span>hotList</label>
                            <div class="col-md-6 col-sm-10">
                                <input type="text" class="form-control"  name=""
                                       v-model="hotList">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button type="button" class="btn btn-primary" @click="saves()" >
                                    <?php echo lang('SAVE'); ?>
                                </button>
                                <button type="button" class="btn btn-info " @click="excels()">
                                    导出json
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="B">
                        <div class="form-group">
                            <button style="margin-left: 10%" type="button" class="btn btn-info" @click="saves_tg(0)" v-if="auto_play_off==-1">
                                取消托管
                            </button>
                            <button style="margin-left: 10%" type="button" class="btn btn-danger" @click="saves_tg(-1)" v-if="auto_play_off==0">
                                开启托管
                            </button>
                        </div>
                        </div>

                </div>
            </div>
        </fieldset>
    </form>
</div>
<script src="/static/js/vue.js"></script>
<script src="/static/js/axios.min.js"></script>
</body>
</html>
<script>
    var app = new Vue({
        el: '#configs',
        data: {
            httpList: "<?php echo $httpList; ?>",
            loginList: "<?php echo $loginList; ?>",
            hotList: "<?php echo $hotList; ?>",
            auto_play_off: 0,
        },
        created () {
            this.get_auto_play_off();
        },
        methods: {
            saves() {
                let vm = this;
                let httpList  = vm.httpList;
                let loginList = vm.loginList;
                let hotList   = vm.hotList;
                axios
                    .post('/admin/config/saves',{httpList: httpList,loginList: loginList,hotList: hotList})
                    .then(function (response) {
                        if(response.data.code==1) {
                            alert(response.data.message);
                        }else{

                        }
                    })
                    .catch(function (error) { // 请求失败处理
                        console.log(error);
                    });
            },
            excels() {
               location.href="/admin/config/excels";
            },
            get_auto_play_off() {
                let vm = this;
                axios
                    .post('/admin/config/auto_play_off',{})
                    .then(function (response) {
                        if(response.data.code==1) {
                            vm.auto_play_off= response.data.auto_play_off;
                        }else{

                        }
                    })
                    .catch(function (error) { // 请求失败处理
                        console.log(error);
                    });
            },
            saves_tg(i) {
                let vm = this;
                let auto_play_off = i;
                axios
                    .post('/admin/config/auto_play_save',{auto_play_off:auto_play_off})
                    .then(function (response) {
                        if(response.data.code==1) {
                            vm.get_auto_play_off();
                        }else{

                        }
                    })
                    .catch(function (error) { // 请求失败处理
                        console.log(error);
                    });
            }
        },
        mounted:function(){
             this.get_auto_play_off();
        },
    })
</script>
