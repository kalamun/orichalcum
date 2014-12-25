</div>

<script type="text/javascript">
	var txts=new kInitZenEditor;
	txts.init('<?= addslashes(ADMINDIR); ?>');
	<?php
	$dir=$_SERVER['DOCUMENT_ROOT'] . BASEDIR . DIR_TEMPLATE . $GLOBALS['kaImpostazioni']->getVar('template_default',1) . '/admin/editor.css';
	if(file_exists($dir)) { ?>txts.addCSS('<?= str_replace("'", "\'", BASEDIR . DIR_TEMPLATE . $GLOBALS['kaImpostazioni']->getVar('template_default',1) . '/admin/editor.css'); ?>')<?php }
	?>
</script>

</body>
</html>

<?php  ksql_close($__db['id']); 