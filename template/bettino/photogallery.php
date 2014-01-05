<? kPrintHeader(); ?>

	<?
	if(kHavePhotogallery()) {
		kPrintPhotogallery();
		}
	else { ?>
		<div id="titleBox">
			<div class="title"><div class="bottom">
				<h1><?= kGetTitle(); ?></h1>
				</div></div>
			<div class="curve"></div>
			</div>

		<div id="contentsBox">
			<? kPrintPhotogalleryList(); ?>
			</div>

		<? } ?>

<? kPrintFooter(); ?>
