<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
	<head>
		<link rel='stylesheet' id='wp-admin-css'  href='<?php echo get_admin_url() ?>css/wp-admin.css?ver=3.4.1' type='text/css' media='all' />
		<meta http-equiv="REFRESH" content="5;url=<?php echo get_admin_url() ?>">
	</head>
	<body class="login">
		<div id="login">
			<form method="post" style="padding-bottom: 26px">
				<p style="text-align: center">
					<img src="<?php echo get_admin_url() ?>images/wpspin_light.gif" style="vertical-align: top"> Waiting on authorization for device '<?php echo $device_name?>'...
				</p>
			</form>
		</div>
	</body>
</html>