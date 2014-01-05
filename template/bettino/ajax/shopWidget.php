<?
/* (c)2012 Kalamun GPL v3*/

require_once('../../../inc/tplshortcuts.lib.php');
kInitBettino('../../../');

if(!isset($_POST['iditem'])||$_POST['iditem']=="") die();

foreach(kGetShopCart() as $items) {
	if(is_array($items)) {
		foreach($items as $item) {
			if(isset($item['idsitem'])&&$item['idsitem']==$_POST['iditem']) { ?>
				<?= kTranslate('Hai già %d copie di questo oggetto nel carrello',false,$item['qty']); ?><br />
				<a href="<?= kGetBaseDir().strtolower(kGetLanguage()); ?>/acquista.html"><?= kTranslate('Vai al carrello'); ?></a>
				<? }
			}
		}
	}

?>