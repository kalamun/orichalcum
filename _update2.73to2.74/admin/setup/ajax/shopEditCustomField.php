<?php 
require_once('../../inc/main.lib.php');
$kaOrichalcum=new kaOrichalcum();
$kaOrichalcum->init( array("check-permissions"=>false, "x-frame-options"=>"") );

$kaTranslate->import('setup');

include('../../shop/shop.lib.php');
$kaShop=new kaShop();

$field=$kaShop->getCustomFields(array("id"=>$_POST['idsfield']));
?>

<div id="iPopUpHeader">
	<h1><?= $kaTranslate->translate('Setup:Edit a field'); ?></h1>
	<a href="javascript:kCloseIPopUp();" class="closeWindow"><img src="<?= ADMINDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
	</div>

<div style="padding:20px;">

	<form action="#customfields" method="post">
	<input type="hidden" name="idsfield" value="<?= $field[0]['idsfield']; ?>" />
	<table>
		<tr><td><label for="name"><?= $kaTranslate->translate('Setup:Name'); ?></label></td>
			<td><?= b3_create_input("name","text","",b3_lmthize($field[0]['name'],"input"),"300px",64); ?></td></tr>
		<tr><td><label for="type"><?= $kaTranslate->translate('Setup:Type'); ?></label></td>
			<td><select id="type" name="type">
				<option value="text" <?= $field[0]['type']=="text"?'selected':''; ?>><?= $kaTranslate->translate('Setup:Text'); ?></option>
				<option value="checkbox" <?= $field[0]['type']=="checkbox"?'selected':''; ?>><?= $kaTranslate->translate('Setup:Checkbox'); ?></option>
				<option value="select" <?= $field[0]['type']=="select"?'selected':''; ?>><?= $kaTranslate->translate('Setup:Select'); ?></option>
				<option value="textarea" <?= $field[0]['type']=="textarea"?'selected':''; ?>><?= $kaTranslate->translate('Setup:Textarea'); ?></option>
				</select>
				</td></tr>
		</table>
	<br />
	<table width="100%"><tr>
		<td width="350">
			<label for="values"><?= $kaTranslate->translate('Setup:Values'); ?> (<?= $kaTranslate->translate('Setup:One value for each line'); ?>)</label><br />
			<textarea name="values" id="values" style="width:300px; height:160px;"><?= b3_lmthize($field[0]['values'],"textarea"); ?></textarea>
			</td>
		<td>
			<label for="values"><?= $kaTranslate->translate('Setup:Use into the following categories'); ?></label><br />
			<?php 
			foreach($kaShop->getItemCategories() as $cat) { ?>
				<input type="checkbox" value="<?= $cat['idcat']; ?>" <?= strpos($field[0]['categories'],','.$cat['idcat'].',')!==false?'checked':''; ?> name="categories[]" id="category<?= $cat['idcat']; ?>" /> <label for="category<?= $cat['idcat']; ?>"><?= $cat['categoria']; ?></label><br />
				<?php  } ?>
			</td>
		</tr>
		</table>
	
	<div class="submit" id="submit">
		<input type="submit" name="updateCustomField" class="button" value="<?= $kaTranslate->translate('UI:Save'); ?>" />
		</div>
	</form>
	</div>
