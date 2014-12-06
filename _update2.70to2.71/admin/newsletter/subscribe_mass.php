<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Newsletter:Add a new subscriber");
require_once("../inc/head.inc.php");
require_once("./newsletter.lib.php");
require_once("../members/members.lib.php");

$kaNewsletter=new kaNewsletter();
$kaMembers=new kaMembers();


/* ACTIONS: save addresses */
if(isset($_POST['insert'])) {
	$log="";

	$lists=array();
	if(isset($_POST['idlista'])) {
		foreach($_POST['idlista'] as $idlista=>$true) { $lists[]=$idlista; }
		}
	$stringlists=",".implode(",",$lists).",";

	$emails=explode("\n",trim($_POST['emails']));

	if(count($lists)>0&&count($emails)>0) {

		foreach($emails as $line) {
			$line=trim($line);
			if(preg_match("/.*<.*@.*>/",$line)) {
				preg_match("/(.*)<(.*@.*)>/",$line,$match);
				$name=trim($match[1]);
				$email=trim($match[2]);
				}
			elseif(preg_match("/.*@.*/",$line)) {
				$email=trim($line);
				$name=substr($line,0,strpos($line,"@"));
				}
			else {
				$email="";
				$name="";
				}

			if($email!=""&&$name!="") {
				//if member not exists, register it
				$members=$kaMembers->getUsersList(array("email"=>$email));
				if(count($members)==0) {
					$username=strtolower(preg_replace("/[^[:alnum:]]/","",$name));
					if($username!="") {
						//if username exists, add a random number at the end
						if($kaMembers->getUserByUsername($username)) $username.=rand(1000,9999);
						$kaMembers->add($name,$email,$username,"");
						}
					}
				$members=$kaMembers->getUsersList(array("email"=>$email));

				foreach($members as $m) {
					$userlists=$lists;
					foreach(explode(",",trim($m['newsletter_lists'],",")) as $idlista) {
						if(strpos($stringlists,",".$idlista.",")===false) $userslists[]=$idlista;
						}
					if($kaNewsletter->subscribe($m['idmember'],$userlists)) {
						//welcome message
						if(isset($_POST['welcome'])) $kaNewsletter->sendWelcomeMessage($m['idmember']);
						}
					else $log=$kaTranslate->translate("Newsletter:Error while subscribing").' '.$m['name'].' ('.$m['email'].')<br />';

					}
				}

			}
		}

	if($log=="") {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate("Newsletter:Successfully saved").'</div>';
		$kaLog->add("UPD",'Newsletter: Mass subscription successfully done');
		}
	else {
		echo '<div id="MsgAlert">'.$kaTranslate->translate($log).'</div>';
		$kaLog->add("ERR",'Newsletter: Error during a mass subscription');
		}

	}
/* FINE AZIONI */


?>
	<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	<?php  include('./subscribe_menu.inc.php');	?>

	<form action="?" method="post">
	<label for="emails"><?= $kaTranslate->translate("Newsletter:Write addresses, one for line, in one of the stadard formats Name <email@addres> or only email@addres"); ?></label><br />
	<textarea name="emails" style="width:100%;height:300px;"></textarea>

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
		<input type="submit" name="insert" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button">
		<?= b3_create_input("welcome","checkbox",$kaTranslate->translate('Newsletter:Send the welcome message'),"false","","","checked"); ?>
		</div>
	</form>

<?php  include_once("../inc/foot.inc.php"); 