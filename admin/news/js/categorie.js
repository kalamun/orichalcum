var soTimer=0;
var scTimer=0;
function showReq(id) {
	var f=document.getElementById(id);
	f.style.display='block';
	f.style.overflow='hidden';
	var hh=parseInt(f.offsetHeight);
	f.style.height='10px';
	soTimer=setInterval(function() { scrollOpening(f,hh); },30);
	}
function scrollOpening(f,hh) {
	f.style.height=(parseInt(f.style.height)+10)+'px';
	if(parseInt(f.style.height)>=hh) clearInterval(soTimer);
	}

var timer=null;
var markURLfield=function(success) {
	if(success=="true") document.getElementById('dirYetExists').style.display="inline";
	else document.getElementById('dirYetExists').style.display="none";
	}
function checkURL(field) {
	var target=document.getElementById('dir')
	//cancello i caratteri non ammessi
	target.value=target.value.replace(/[^\w\/\.\-\u00C0-\uD7FF\u2C00-\uD7FF]+/g,"-");
	if(typeof(ajaxTimer)!=='undefined') clearTimeout(ajaxTimer);
	t=setTimeout(function() {
		var aj=new kAjax();
		aj.onSuccess(markURLfield);
		aj.send('post','ajax/checkCat.php','url='+escape(field.value));
		},500);
	}
function title2url() {
	var titleField=document.getElementById('titolo');
	var urlField=document.getElementById('dir');
	if(!urlField.getAttribute("completed")&&titleField.value!="") urlField.value=titleField.value.replace(/[^\w]+/g,"-").toLowerCase();
	}
function titleBlur() {
	var titleField=document.getElementById('titolo');
	var urlField=document.getElementById('dir');
	if(urlField.value!="") urlField.setAttribute("completed","true");
	checkURL(urlField);
	}

function showActions(td) {
	for(var i=0;td.getElementsByTagName('DIV')[i];i++) {
		td.getElementsByTagName('DIV')[i].style.visibility='visible';
		}
	}
function hideActions(td) {
	for(var i=0;td.getElementsByTagName('DIV')[i];i++) {
		td.getElementsByTagName('DIV')[i].style.visibility='hidden';
		}
	}
