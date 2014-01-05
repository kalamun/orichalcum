<?
session_start();
if(!isset($_SESSION['iduser'])) die();

include('../../inc/connect.inc.php');
include('../../inc/kalamun.lib.php');
include('../../inc/main.lib.php');
include('../../shop/shop.lib.php');
$kaTranslate=new kaAdminTranslate();
$kaTranslate->import('setup');
$kaShop=new kaShop();
?>

<div id="iPopUpHeader">
	<h1><?= $kaTranslate->translate('Setup:Add a field'); ?></h1>
	<a href="javascript:kCloseIPopUp();" class="closeWindow"><img src="<?= ADMINDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
	</div>

<div style="padding:20px;">
	<form action="#customfields" method="post">
	<table>
		<tr><td><label for="name"><?= $kaTranslate->translate('Setup:Name'); ?></label></td>
			<td><?= b3_create_input("name","text","","","300px",64); ?></td></tr>
		<tr><td><label for="type"><?= $kaTranslate->translate('Setup:Type'); ?></label></td>
			<td><select id="type" name="type">
				<option value="text"><?= $kaTranslate->translate('Setup:Text'); ?></option>
				<option value="checkbox"><?= $kaTranslate->translate('Setup:Checkbox'); ?></option>
				<option value="multichoice"><?= $kaTranslate->translate('Setup:Multiple Choice'); ?></option>
				<option value="select"><?= $kaTranslate->translate('Setup:Select'); ?></option>
				<option value="textarea"><?= $kaTranslate->translate('Setup:Textarea'); ?></option>
				</select>
				</td></tr>
		</table>
	<br />
	<table width="100%"><tr>
		<td width="350">
			<label for="values"><?= $kaTranslate->translate('Setup:Values'); ?> (<?= $kaTranslate->translate('Setup:One value for each line'); ?>)</label><br />
			<textarea name="values" id="values" style="width:300px; height:160px;"></textarea>
			</td>
		<td>
			<label for="values"><?= $kaTranslate->translate('Setup:Use into the following categories'); ?></label><br />
			<?
			foreach($kaShop->getItemCategories() as $cat) { ?>
				<input type="checkbox" value="<?= $cat['idcat']; ?>" name="categories[]" id="category<?= $cat['idcat']; ?>" /> <label for="category<?= $cat['idcat']; ?>"><?= $cat['categoria']; ?></label><br />
				<? } ?>
			</td>
		</tr>
		</table>
	
	<div class="submit" id="submit">
		<input type="submit" name="addCustomField" class="button" value="<?= $kaTranslate->translate('UI:Save'); ?>" />
		</div>
	</form>
	</div>
