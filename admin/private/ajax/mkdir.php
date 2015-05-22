<?php 
session_start();

define("PAGE_ID","private");
require_once('../../inc/connect.inc.php');
require_once('../../inc/kalamun.lib.php');
require_once('../../inc/sessionmanager.inc.php');
require_once('../../inc/main.lib.php');
if(!isset($_SESSION['iduser'])) die('Non hai il permesso di utilizzare questa funzione');

$kaTranslate=new kaAdminTranslate();

require_once('../private.lib.php');
$kaPrivate=new kaPrivate();

$_GET['dir']=trim($_GET['dir']," ./");
$_GET['dir']=str_replace("../","",$_GET['dir']);

/* get current permissions */
/* should be possible to activate only a subset of the parent dir permissions */
$permissions=$kaPrivate->getPermissions($_GET['dir']);
$parentpermissions=$kaPrivate->getPermissions($_GET['dir']);
$allowedpermissions=array();
if($parentpermissions['permissions']=="public") $allowedpermissions['public']=true;
if($parentpermissions['permissions']=="members"||isset($allowedpermissions['public'])) $allowedpermissions['members']=true;
if($parentpermissions['permissions']=="restricted"||isset($allowedpermissions['public'])||isset($allowedpermissions['members'])) $allowedpermissions['restricted']=$parentpermissions['members'];
if($parentpermissions['permissions']=="private"||isset($allowedpermissions['public'])||isset($allowedpermissions['members'])||isset($allowedpermissions['restricted'])) $allowedpermissions['private']=true;


?>
<div id="iPopUpHeader">
	<h1><?= $kaTranslate->translate('Private:New Folder'); ?></h1>
	<a href="javascript:window.parent.kCloseIPopUp();" class="closeWindow"><img src="../img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
</div>

<form action="" method="post">
	<div style="padding:20px;">
		<div class="title">
			<label for="dir"><?= $kaTranslate->translate('Private:Directory Name'); ?></label>
			<input type="text" value="" name="dir" id="dir" style="width:98%;" /><br />
		</div>
		<br />

		<div style="width:48%;float:left;">
			<?= $kaTranslate->translate('Private:Access'); ?>
			<select name="permissions" onchange="swapMembersListVisualization(this,'membersList');">
				<option value=""><?= $kaTranslate->translate('Private:Inherit permissions'); ?></option>
				<?php  if(isset($allowedpermissions['public'])) { ?><option value="public" selected><?= $kaTranslate->translate('Private:Anyone can access'); ?></option><?php  } ?>
				<?php  if(isset($allowedpermissions['members'])) { ?><option value="members"><?= $kaTranslate->translate('Private:All members can access'); ?></option><?php  } ?>
				<?php  if(isset($allowedpermissions['restricted'])) { ?><option value="restricted"><?= $kaTranslate->translate('Private:Only specified members can access'); ?></option><?php  } ?>
				<?php  if(isset($allowedpermissions['coupon'])) { ?><option value="coupon"><?= $kaTranslate->translate('Private:Access with coupon'); ?></option><?php  } ?>
				<?php  if(isset($allowedpermissions['private'])) { ?><option value="private"><?= $kaTranslate->translate('Private:Nobody can access'); ?></option><?php  } ?>
				</select><br />
			<br />

			<div id="membersList" class="box" style="display:none;">
				<div style="float:right;"><a href="javascript:selectAll('membersList');"><?= $kaTranslate->translate('Private:Select all'); ?></a> | <a href="javascript:unselectAll('membersList');"><?= $kaTranslate->translate('Private:Unselect all'); ?></a></div>
				<h3><?= $kaTranslate->translate('Private:Members with reading permissions'); ?></h3>
				<table width="100%"><?php 
				if(count($parentpermissions['members'])>0) {
					$i=0;
					$newColEach=ceil(count($parentpermissions['members'])/2);
					foreach($parentpermissions['members'] as $m) {
						if($i%$newColEach==0) echo '<td>';
						?>
						<input type="checkbox" name="members[<?= $m['idmember']; ?>]" id="member<?= $m['idmember']; ?>" <?= (isset($permissions['members'][$m['idmember']])?'checked':''); ?> onclick="kSyncRWMembers(this);" /> <label for="member<?= $m['idmember']; ?>"><?= $m['name']; ?></label><br />
						<?php 
						if($i+1%$newColEach==0) echo '</td>';
						$i++;
						}
					if($i%$newColEach!=0) echo '</td>';
					}
				else { ?>
					<td><?= $kaTranslate->translate('Private:No members available in this folder'); ?></td>
					<?php  }
				?></table></div>
			</div>

		<div style="width:48%;float:right;">
			<?= $kaTranslate->translate('Private:Writing'); ?>
			<select name="permissionsw" onchange="swapMembersListVisualization(this,'membersListWriting');">
				<option value=""><?= $kaTranslate->translate('Private:Inherit permissions'); ?></option>
				<option value="members" selected><?= $kaTranslate->translate('Private:Anyone with reading permissions'); ?></option>
				<option value="restricted"><?= $kaTranslate->translate('Private:Only specified members can upload and delete files'); ?></option>
				<option value="coupon"><?= $kaTranslate->translate('Private:Upload with coupon'); ?></option>
				<option value="private"><?= $kaTranslate->translate('Private:Nobody can write'); ?></option>
			</select><br />
			<br />

			<div id="membersListWriting" class="box" style="display:none;">
				<div style="float:right;"><a href="javascript:selectAll('membersListWriting');"><?= $kaTranslate->translate('Private:Select all'); ?></a> | <a href="javascript:unselectAll('membersListWriting');"><?= $kaTranslate->translate('Private:Unselect all'); ?></a></div>
				<h3><?= $kaTranslate->translate('Private:Members with writing permissions'); ?></h3>
				<table width="100%"><?php 
				if(count($parentpermissions['members'])>0) {
					$i=0;
					$newColEach=ceil(count($parentpermissions['members'])/2);
					foreach($parentpermissions['members'] as $m) {
						if($i%$newColEach==0) echo '<td>';
						?>
						<input type="checkbox" name="membersw[<?= $m['idmember']; ?>]" id="memberw<?= $m['idmember']; ?>" <?= (isset($permissions['writemembers'][$m['idmember']])?'checked':''); ?> /> <label for="memberw<?= $m['idmember']; ?>"><?= $m['name']; ?></label><br />
						<?php 
						if($i+1%$newColEach==0) echo '</td>';
						$i++;
						}
					if($i%$newColEach!=0) echo '</td>';
					}
				else { ?>
					<td><?= $kaTranslate->translate('Private:No members available in this folder'); ?></td>
					<?php  }
				?></table></div>
			</div>

		<div style="clear:both;"></div>
		<br />
		
	</div>

	<div class="submit">
		<input type="submit" name="mkdir" value="<?= $kaTranslate->translate('Private:Create Folder'); ?>" class="button" />
	</div>
</form>

