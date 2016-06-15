<?php 
/* (c) Kalamun.org - GNU/GPL 3 */
session_start();
if(!isset($_SESSION['iduser'])) die("You don't have permission to access here");


if(isset($_POST['message']))
{
	require_once("../../../inc/tplshortcuts.lib.php");
	kInitBettino("../../../");

	// collect all message parts: if only one block is defined (the default one) save it as string, otherwise serialize an array of blocks
	$message = array();
	$blocks = array();
	foreach($_POST as $k=>$v)
	{
		if(substr($k,0,6)=="block-") $message[substr($k,6)] = b3_htmlize($v, false);
	}
	
	if(count($message)>0) $message['-default-'] = b3_htmlize($_POST['message'],false);
	else $message = b3_htmlize($_POST['message'],false);
	
	// convert local URLs into absolute URLs, adding the site name at the start
	if(is_array($message))
	{
		foreach($message as $k=>$v)
		{
			$message[$k] = str_replace('="/', '="'.SITE_URL.'/', $message[$k]);
			// remove html comments
			$message[$k] = preg_replace("/\<\!--.*?--\>/s", "", $message[$k]);
		}
	} else {
		$message = str_replace('="/','="'.SITE_URL.'/',$message);
		$message = preg_replace("/\<\!--.*?--\>/s", "", $message);
	}

	$preview = $GLOBALS['__emails']->preview("","",$_POST['subject'],$message,$_POST['template']);
	echo $preview['html'];

} else { ?>
	<html>
	<body>
	<div style="text-align:center;">Loading...</div>
	<form id="formpreview" action="" method="post">
		<input type="text" name="template" id="template" value="" style="display:none;" />
		<input type="text" name="subject" id="subject" value="" style="display:none;" />
		<textarea name="message" id="message" style="display:none;"></textarea>
	</form>
	<script type="text/javascript">
		var f = document.getElementById('formpreview');
		for(var i=0; window.parent.document.getElementById('template'+i); i++)
		{
			if(window.parent.document.getElementById('template'+i).checked)
			{
				document.getElementById('template').value = window.parent.document.getElementById('template'+i).value;
				break;
			}
		}
		document.getElementById('subject').value = window.parent.document.getElementById('subject').value;
		document.getElementById('message').value = window.parent.document.getElementById('message').value;
		
		var container = window.parent.document.getElementById('additionalBlocks');
		for(var i=0, c=container.childNodes; c[i]; i++)
		{
			if(c[i].nodeType!=1 && c[i].getAttribute('data-block')==false) continue;
			
			var txtarea = document.createElement('TEXTAREA');
			txtarea.setAttribute('name', 'block-' + c[i].getAttribute('data-block'));
			txtarea.setAttribute('style', 'display:none');
			txtarea.value = c[i].getElementsByTagName('textarea')[0].value;
			f.appendChild(txtarea);
		}

		f.submit();
	</script>
	</body>
	</html>
	<?php  } 