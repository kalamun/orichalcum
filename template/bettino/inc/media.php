<?
// embed code (YouTube, Vimeo, etc...)
if(trim(kMediaIsHtmlCode())!=false) {
	echo kGetMediaHtmlCode();
	}

// media gestito dal player del sito
else {
	if(kGetMediaMimeType()=="video/x-flv"||kGetMediaMimeType()=="video/f4v") {
		?>
		<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="<?= kGetMediaWidth(); ?>" height="<?= kGetMediaHeight(); ?>" align="middle">
			<param name="allowScriptAccess" value="sameDomain" />
			<param name="allowFullScreen" value="true" />
			<param name="movie" value="<?= kGetSiteUrl().'/'.kGetTemplateDir(); ?>kflv.swf?filename=<?= kGetMediaUrl(); ?>&autostart=false&repeat=false&scalemode=fit&fullscreen=true" />
			<param name="quality" value="high" />
			<param name="bgcolor" value="#000" />
			<embed src="<?= kGetTemplateDir(); ?>kflv.swf?filename=<?= kGetMediaUrl(); ?>&autostart=false&repeat=false&scalemode=fit&fullscreen=true" quality="high" bgcolor="#000" width="<?= kGetMediaWidth(); ?>" height="<?= kGetMediaHeight(); ?>" name="kflv" align="middle" allowScriptAccess="sameDomain" allowFullScreen="true" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflashplayer" />
			</object>
		<?
		}
	elseif(kGetMediaMimeType()=="audio/mpeg"||kGetMediaMimeType()=="audio/mp3"||kGetMediaMimeType()=="audio/x-mp3"||kGetMediaMimeType()=="audio/mpeg3"||kGetMediaMimeType()=="audio/x-mpeg3") {
		?>
		<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="<?= kGetMediaWidth(); ?>" height="<?= kGetMediaHeight(); ?>" align="middle">
			<param name="allowScriptAccess" value="sameDomain" />
			<param name="allowFullScreen" value="true" />
			<param name="movie" value="<?= kGetSiteUrl().'/'.kGetTemplateDir(); ?>kflv.swf?filename=<?= kGetMediaUrl(); ?>&autostart=false&repeat=false&scalemode=fit&fullscreen=true" />
			<param name="quality" value="high" />
			<param name="bgcolor" value="#fff" />
			<embed src="<?= kGetTemplateDir(); ?>kmp3.swf?filename=<?= kGetMediaUrl(); ?>&autostart=false&repeat=false&scalemode=fit&fullscreen=true" quality="high" bgcolor="#fff" width="<?= kGetMediaWidth(); ?>" height="<?= kGetMediaHeight(); ?>" name="kmp3" align="middle" allowScriptAccess="sameDomain" allowFullScreen="true" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflashplayer" />
			<param name="wmode" value="transparent">
			</object>
		<?
		}
	}
?>
