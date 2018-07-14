<!DOCTYPE html>
<html lang="<?= get_language_code(); ?>">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?= get_site_name(); ?></title>

	<meta name="viewport" content="width=device-width, initial-scale=.8, user-scalable=yes">

	<link rel="shortcut icon" href="<?= get_site_directory_uri(); ?>/inc/template/favicon.png" />
	<link rel="stylesheet" media="screen" href="<?= get_site_directory_uri(); ?>/admin/inc/template/css/style.css">
</head>

<body class="login">

	<div class="login-panel">
		<h1><strong><?= get_site_name(); ?></strong> Admin</h1>
		<form action="" method="post">
			<?= ok_input( [ "type" => "hidden", "name" => "nonce", "value" => ok_create_nonce( "login" ) ] ); ?>

			<?= ok_input( [ "type" => "text", "name" => "username", "id" => "username", "class" => "full-width" ], "Nome utente" ); ?><br>
			<?= ok_input( [ "type" => "password", "name" => "password", "id" => "password", "class" => "full-width" ], "Password" ); ?><br>
			
			<div class="submit margin-top-15">
				<?= ok_input( [ "type" => "submit", "name" => "login", "value" => "Entra" ] ); ?>
			</div>
		</form>
	</div>

</body>
</html>

