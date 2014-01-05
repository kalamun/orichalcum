<?
kSetMenuSelectedByURL(kGetShopDir());
kPrintHeader();
?>

<script type="text/javascript">var TShop=new kShop();</script>

<?
if(kHaveShopItem()) {
	?><div class="shopSingle"><?
		kSetShopItemByDir();
		$phg=kGetShopItemPhotogallery();
		if(isset($phg[0])) {
			kSetImage($phg[0]);
			$w=800;
			$h=800/kGetImageWidth()*kGetImageHeight();
			?>
			<div class="coverBox"><img src="<?= kGetImageURL(); ?>" width="<?= $w; ?>" height="<?= $h; ?>" alt="" /></div>
			
			<?
			$w=500;
			$h=round(500/kGetImageWidth()*kGetImageHeight());
			if($h>280) {
				$h=280;
				$w=round(280/kGetImageHeight()*kGetImageWidth());
				}
			?>
			<img src="<?= kGetImageUrl(); ?>" width="<?= $w; ?>" height="<?= $h; ?>" alt="<?= kGetImageAlt(); ?>" class="cover" />

			<? }
		?>
		<div id="titleBox">
			<div class="title"><div class="bottom">
			<h1><?= kGetShopItemTitle(); ?></h1>
			<? if(kGetShopItemSubtitle()!="") { ?>
				<br /><h2><?= kGetShopItemSubtitle(); ?></h2>
				<? } ?>
				</div>
				</div>
			</div>

		<div id="contentsBox">
			<?= kGetShopItemPreview(); ?>
			<?= kGetShopItemText(); ?>
			
			<div class="addToCart"><a href="javascript:TShop.addToCart(<?= kGetShopItemId(); ?>);"><?= kTranslate('aggiungi al carrello'); ?></a></div>
			<div id="cartMiniWidget"></div>
			<script type="text/javascript">TShop.updateMiniWidget(<?= kGetShopItemId(); ?>);</script>
			</div>
	
	<div style="clear:both;"></div>
	<?
	}

else {
	?>
	<div id="titleBox">
		<div class="title"><div class="bottom">
			<h1><?
			$crumbs=kGetCrumbs();
			if(isset($crumbs[0]['label'])) echo $crumbs[0]['label'];
			?></h1>
			</div>
			</div>
		</div>

	<div id="contentsBox"><?
	foreach(kGetShopItemQuickList() as $item) {
		?>
		<div class="shopItem">
			<?
			kSetShopItemByDir($item['dir']);
			$phg=kGetShopItemPhotogallery();
			if(isset($phg[0])) {
				kSetImage($phg[0]);
				$h=200;
				$w=round(200/kGetImageHeight()*kGetImageWidth());
				if($w<300) {
					$w=300;
					$h=round(300/kGetImageWidth()*kGetImageHeight());
					}
				?><div class="coverBox"><a href="<?= kGetShopItemPermalink(); ?>"><img src="<?= kGetImageURL(); ?>" width="<?= $w; ?>" height="<?= $h; ?>" alt="" /></a></div><?
				}
			?>
			
			<h2><a href="<?= kGetShopItemPermalink(); ?>"><?= kGetShopItemTitle(); ?></a></h2>
			<?= kGetShopItemSubtitle(); ?>
			<?= kGetShopItemPreview(); ?>
			<? $ecommercelink=kGetShopItemMetadata("ecommerce-link",kGetShopDir().'/libri/'.$item['dir']); ?>
			<a href="<?= kGetShopItemPermalink(); ?>" class="smallbutton"><?= kTranslate('Voglio saperne di più'); ?></a>
			<div class="addToCart"><a href="javascript:TShop.addToCart(<?= kGetShopItemId(); ?>);"><?= kTranslate('aggiungi al carrello'); ?></a></div>
			<br />
			</div>
		<? } ?>
	</div>
	<div style="clear:both;"></div>
	<?
	}
	?>
	</div>

<?
kPrintFooter();
?>