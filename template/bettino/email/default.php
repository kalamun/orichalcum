<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
<title><?= kGetEmailSubject(); ?></title>
<style type="text/css" media="screen,print">
	body {
		color:#000;
		padding:0;
		margin:0;
		font-size:10pt; 
		font-family: Arial,sans-serif;
		background-color:#444;
		}
	img {
		vertical-align:middle;
		}
	#footer {
		font-size:.8em;
		font-style:italic;
		color:#888;
		}
	h1,h2,h3,h4 {
		color:#142D80;
		}
	</style>
</head>

<body>
<table align="center" bgcolor="#ffffff" cellpadding="20" cellspacing="0" width="80%">
<tr><td id="header" bgcolor="#F4F4F4" align="center"><h2><?= kGetSiteName(); ?></h2></td></tr>
<tr><td>
	<?= kGetEmailMessage(); ?>
	<br />
	<br />
	</td></tr>
<tr><td id="footer">
	<a href="<?= kGetSiteURL(); ?>"><?= kGetSiteName(); ?></a>
	</td></tr>
</table>
</body>
</html>