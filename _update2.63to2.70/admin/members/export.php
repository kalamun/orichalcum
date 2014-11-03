<?php 
/* 2013 (c) Roberto Kalamun Pasini - GPLv3 */

define("PAGE_NAME","Members:Export member list");
require_once("../inc/head.inc.php");
require_once("./members.lib.php");
$kaMembers=new kaMembers();

require_once('../newsletter/newsletter.lib.php');
$kaNewsletter=new kaNewsletter();

require_once('../private/private.lib.php');
$kaPrivate=new kaPrivate();

?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<div class="topset">
	<a href="ajax/export.php?csv" class="button"><?= $kaTranslate->translate('Members:Export as CSV'); ?></a><br />
	<br />
	<a href="?table" class="button"><?= $kaTranslate->translate('Members:Show as HTML Table'); ?></a><br />
	<br />
	</div>

	<?php 
	if(isset($_GET['table'])) { ?>
		<div class="topset">
			<table class="tabella">
			<tr><th>Name</th><th>E-mail</th><th>Username</th><th>Password</th><th>Affiliation</th></tr>
			<?php 
			foreach($kaMembers->getUsersList() as $m) { ?>
				<tr>
				<td><?= $m['name']; ?></td>
				<td><?= $m['email']; ?></td>
				<td><?= $m['username']; ?></td>
				<td><?= $m['password']; ?></td>
				<td><?= $m['affiliation']; ?></td>
				</tr>
				<?php  }
			?>
			</div>
		<?php  }
	?>

<?php 	
include_once("../inc/foot.inc.php");
