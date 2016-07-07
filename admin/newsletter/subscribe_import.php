<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Newsletter:Import subscribers from file");
require_once("../inc/head.inc.php");
require_once("./newsletter.lib.php");
require_once("../members/members.lib.php");

$kaNewsletter=new kaNewsletter();
$kaMembers=new kaMembers();


/* ACTIONS: save addresses */
if(isset($_POST['insert']) && isset($_FILES['emails'])) {
	$log="";

	$lists=array();
	if(isset($_POST['idlista']))
	{
		foreach($_POST['idlista'] as $idlista=>$true) { $lists[]=$idlista; }
	}
	$stringlists=",".implode(",",$lists).",";

	$emails = file($_FILES['emails']['tmp_name']);
	
	if(count($lists)>0 && count($emails)>0)
	{
		$i=0; // inserted
		$p=0; // processed
		foreach($emails as $line)
		{
			$p++;
			$line = trim($line);
			
			// when line is in "name <email>" format
			if(preg_match("/^[^<]+<[^@]+@[^>]+>$/", $line))
			{
				preg_match("/^([^<]+)<([^@]+@[^>]+)>$/", $line, $match);
				$name=trim($match[1]);
				$email=trim($match[2], "<> ");
			
			// when line is in "name \t email" format
			} elseif(preg_match("/.*\t.*@.*/",$line)) {
				$match = explode("\t", $line);
				$name = trim($match[0]);
				$email = trim($match[1], "<> ");
			
			// when only email is specified
			} elseif(preg_match("/.*@.*/",$line)) {
				$email = trim($line, "<> ");
				$name = substr($email, 0, strpos($email, "@"));
				$name = preg_replace("/[^[:alnum:]]/i", " ", $name);
			
			} else {
				$email="";
				$name="";
			}

			if($email!="" && $name!="")
			{
				//if member not exists, register it
				$members = $kaMembers->getUsersList(array("email"=>$email));
				
				if(count($members)==0)
				{
					$username=strtolower(preg_replace("/[^[:alnum:]]/","",$name));
					if($username!="")
					{
						//if username exists, add a random number at the end
						if($kaMembers->getUserByUsername($username)) $username.=rand(1000,9999);
						$kaMembers->add($name, $email, $username, "");
					}

					$members = $kaMembers->getUsersList(array("email"=>$email));
				}

				if(empty($members)) $log = $kaTranslate->translate("Newsletter:Error while subscribing %s", htmlentities($line)).'<br />';

				foreach($members as $m)
				{
					$userlists = $lists;
					foreach(explode(",",trim($m['newsletter_lists'],",")) as $idlista)
					{
						if(strpos($stringlists,",".$idlista.",")===false) $userslists[]=$idlista;
					}
					if($kaNewsletter->subscribe($m['idmember'], $userlists))
					{
						//welcome message
						if(isset($_POST['welcome'])) $kaNewsletter->sendWelcomeMessage($m['idmember']);
					} else $log = $kaTranslate->translate("Newsletter:Error while subscribing %s", $m['name'].' ('.$m['email'].')').'<br />';

					$i++;
				}
			} else $log = $kaTranslate->translate("Newsletter:Error while subscribing %s", htmlentities($line)).'<br />';

		}
		
		echo $p.' processati<br>';
		echo $i.' iscritti<br>';
	}

	if($log=="")
	{
		echo '<div id="MsgSuccess">'.$kaTranslate->translate("Newsletter:Successfully saved").'</div>';
		$kaLog->add("UPD",'Newsletter: Mass subscription successfully done');
	} else {
		echo '<div id="MsgAlert">'.$kaTranslate->translate($log).'</div>';
		$kaLog->add("ERR",'Newsletter: Error during a mass subscription');
	}

}
/* FINE AZIONI */


?>
	<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	<?php  include('./subscribe_menu.inc.php');	?>

	<form action="?" method="post" enctype="multipart/form-data">
	<label for="emails"><?= $kaTranslate->translate("Newsletter:Import from CSV"); ?></label><br />
	<input type="file" name="emails">

	<br /><br />
	<fieldset class="box" id="lists"><legend><?= $kaTranslate->translate('Newsletter:Lists to subscribe to'); ?></legend>
		<table><tr>
			<?php 
			$languages=$kaAdminMenu->getLanguages();
			foreach($languages as $lang) { ?>
				<td style="padding-right:50px;">
				<h3><?= $lang['lingua']; ?></h3>
				<?php 
				$lists=$kaNewsletter->getNewslettersList(array("ll"=>$lang['ll']));
				foreach($lists as $list) {
					echo b3_create_input("idlista[".$list['idlista']."]","checkbox",$list['lista'],"1").' <small>('.$list['subscribers_number'].')</small><br />';
					}
				?>
				</td>
				<?php  } ?>
			</tr></table>
		</fieldset>

	<br /><br />
	<div class="submit">
		<input type="submit" name="insert" value="<?= $kaTranslate->translate('UI:Import'); ?>" class="button">
		<?= b3_create_input("welcome","checkbox",$kaTranslate->translate('Newsletter:Send the welcome message'),"false","","",""); ?>
		</div>
	</form>

<?php  include_once("../inc/foot.inc.php"); 