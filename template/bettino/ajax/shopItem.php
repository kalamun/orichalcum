<?
/* (c)2012 Kalamun GPL v3*/

require_once('../../../inc/tplshortcuts.lib.php');
kInitBettino('../../../');

if(!isset($_GET['dir'])||$_GET['dir']=="") die();
kSetShopItemByDir($_GET['dir']);
?>
<div class="frame">
	<h1><?= kGetShopItemTitle(); ?></h1>
	
	<div class="imgGallery"><?
		foreach(kGetShopItemPhotogallery() as $img) {
			kSetImage($img);
			$w=500;
			$h=round($w/kGetThumbWidth()*kGetThumbHeight());
			if($h>300) {
				$h=300;
				$w=ceil($h/kGetThumbHeight()*kGetThumbWidth());
				}
			?><img src="<?= kGetImageURL(); ?>" width="<?= $w; ?>" height="<?= $h; ?>" alt="" style="padding:<?= ceil((300-$h)/2); ?>px 0" /><?
			}
		?></div>
	
	<div class="description">
		<?= kGetShopItemText(); ?>
		</div>
	<div class="price"><?= kGetShopItemPrice(); ?> <?= kGetShopCurrency("symbol"); ?></div>
	<div class="addToCart"><a href="#" onclick="TShop.addToCart(<?= kGetShopItemId(); ?>,this);return false;"><?= kTranslate('compra'); ?></a></div>

	</div>