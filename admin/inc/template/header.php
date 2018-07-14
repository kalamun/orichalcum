<!DOCTYPE html>
<html lang="<?= get_language_code(); ?>">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?= get_site_name(); ?></title>

	<meta name="viewport" content="width=device-width, initial-scale=.8, user-scalable=yes">

	<link rel="shortcut icon" href="<?= get_site_directory_uri(); ?>/inc/template/favicon.png" />
	<link rel="stylesheet" media="screen" href="<?= get_site_directory_uri(); ?>/admin/inc/template/css/style.css">
	
	<script type="text/javascript" src="<?= get_site_directory_uri(); ?>/admin/inc/template/js/functions.js" charset="UTF-8"></script>
	<script type="text/javascript" src="<?= get_site_directory_uri(); ?>/admin/inc/template/js/admin.js" charset="UTF-8"></script>
</head>

<body>

	<header>
		<div class="logo">
			<h1><?= get_site_name(); ?></h1>
		</div>
		
		<div class="nav">
			<?php
			$nav = [
				"label" => "Dashboard",
				"url" => get_site_url() . "/admin",
			];
			add_admin_nav_element( $nav );
			
			$nav = [
				"label" => "Utenti",
				"url" => get_site_url() . "/admin/users",
				"child" => [
					[
						"label" => "Nuovo utente",
						"url" => get_site_url() . "/admin/users/add.php",
					],
					[
						"label" => "Utenti",
						"url" => get_site_url() . "/admin/users",
					],
				],
			];
			add_admin_nav_element( $nav );
			
			$nav = [
				"label" => "Configurazione",
				"url" => get_site_url() . "/admin/config/config.php",
				"child" => [
					[
						"label" => "Amministratori",
						"url" => get_site_url() . "/admin/config/admins.php",
					],
					[
						"label" => "Impostazioni",
						"url" => get_site_url() . "/admin/config/config_site.php",
					],
					[
						"label" => "Lingue",
						"url" => get_site_url() . "/admin/config/languages/index.php",
					],
				],
			];
			add_admin_nav_element( $nav );
			
			print_admin_nav();
			?>
		</div>
		
		<div class="login-info">
			<?php
			$user = ok_get_current_user();
			echo $user->first_name . ' ' . $user->last_name;
			?>
			<a href="?logout">Esci</a>
		</div>
	</header>

	<section class="main_container">