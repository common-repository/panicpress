<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
	<head>
		<link rel='stylesheet' id='wp-admin-css'  href='<?php echo get_admin_url() ?>css/wp-admin.css?ver=3.4.1' type='text/css' media='all' />
	</head>
	<body class="login">
		<div id="login">
				<h1><a href="http://wordpress.org/" title="Powered by WordPress"></a></h1>
				<form name="loginform" id="loginform" action="<?php echo get_admin_url() ?>" method="post">
				<p>
					<label for="user_login">
						Identify this device:<br />
						<input type="text" name="<?php echo $cookie_input_name?>" class="input" value="" size="20" tabindex="10" />
					</label>
				</p>
				<p class="submit">
					<input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="Submit" tabindex="100" />
				</p>
			</form>
		</div>
	</body>
</html>