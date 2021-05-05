<?php /*a:2:{s:50:"themes/admin_simpleboot3/admin\user\user_info.html";i:1609556034;s:78:"D:\phpstudy_pro\WWW\dtz\cmf\public/themes/admin_simpleboot3/public\header.html";i:1614844967;}*/ ?>
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
	<div class="wrap">
		<ul class="nav nav-tabs">
			<li class="active"><a href="<?php echo url('user/userInfo'); ?>"><?php echo lang('ADMIN_USER_USERINFO'); ?></a></li>
			<li><a href="<?php echo url('setting/password'); ?>"><?php echo lang('ADMIN_SETTING_PASSWORD'); ?></a></li>
		</ul>
		<form class="form-horizontal js-ajax-form margin-top-20" role="form" method="post" action="<?php echo url('User/userinfoPost'); ?>">
			<div class="form-group">
				<label for="input-user-nickname" class="col-sm-2 control-label"><?php echo lang('NICKNAME'); ?></label>
				<div class="col-md-6 col-sm-10">
					<input type="text" class="form-control" id="input-user-nickname" name="user_nickname" value="<?php echo $user_nickname; ?>">
				</div>
			</div>
			<div class="form-group">
				<label for="input-gender" class="col-sm-2 control-label"><?php echo lang('GENDER'); ?></label>
				<div class="col-md-6 col-sm-10">
					<select class="form-control" name="sex" id="input-gender">
						<?php $sexs=array("0"=>lang('GENDER_SECRECY'),"1"=>lang('MALE'),"2"=>lang('FEMALE')); if(is_array($sexs) || $sexs instanceof \think\Collection || $sexs instanceof \think\Paginator): if( count($sexs)==0 ) : echo "" ;else: foreach($sexs as $key=>$vo): $sexselected=$key==$sex?"selected":""; ?>
							<option value="<?php echo $key; ?>" <?php echo $sexselected; ?>><?php echo $vo; ?></option>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label for="input-birthday" class="col-sm-2 control-label"><?php echo lang('BIRTHDAY'); ?></label>
				<div class="col-md-6 col-sm-10">
					<input type="text" class="form-control js-bootstrap-date" id="input-birthday" name="birthday" value="<?php echo date('Y-m-d',$birthday); ?>" placeholder="2013-01-04">
				</div>
			</div>
			<div class="form-group">
				<label for="input-user_url" class="col-sm-2 control-label"><?php echo lang('WEBSITE'); ?></label>
				<div class="col-md-6 col-sm-10">
					<input type="text" class="form-control" id="input-user_url" name="user_url" value="<?php echo $user_url; ?>" placeholder="http://thinkcmf.com">
				</div>
			</div>
			<div class="form-group">
				<label for="input-signature" class="col-sm-2 control-label"><?php echo lang('SIGNATURE'); ?></label>
				<div class="col-md-6 col-sm-10">
					<textarea class="form-control" id="input-signature" name="signature" placeholder="<?php echo lang('SIGNATURE'); ?>"><?php echo $signature; ?></textarea>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-primary js-ajax-submit"><?php echo lang('SAVE'); ?></button>
				</div>
			</div>
		</form>
	</div>
	<script src="/static/js/admin.js"></script>
</body>
</html>