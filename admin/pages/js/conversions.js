/* (c) 2012 Kalamun GPLv3 */

function showTab(id) {
	hideTabs();
	document.getElementById('tab_'+id).className='sel';
	document.getElementById('panel_'+id).style.display='block';
	document.getElementById('activetab').value=id;
	}
function hideTabs() {
	var links=document.getElementById('tabs').getElementsByTagName('A');
	for(var i in links) {
		links[i].className='';
		}
	var tabs=document.getElementById('imgcontents').getElementsByTagName('DIV');
	for(var i in tabs) {
		if(tabs[i].id&&tabs[i].id.substr(0,6)=='panel_') tabs[i].style.display='none';
		}
	}

function switchCustomSender(select) {
	var options=document.getElementById('notification_custom').parentNode;
	options.style.visibility=(select.value=='custom'?'visible':'hidden');
	}

function duplicateLine(tr) {
	var newtr=tr.cloneNode(true);
	var rand=Math.round(Math.random()*9999);
	tr.parentNode.insertBefore(newtr,tr.nextSibling);
	newtr.getElementsByTagName('INPUT')[0].value="";
	newtr.getElementsByTagName('INPUT')[1].checked=false;
	newtr.getElementsByTagName('INPUT')[1].id+=rand;
	newtr.getElementsByTagName('SELECT')[0].selectedIndex=0;
	newtr.getElementsByTagName('LABEL')[0].setAttribute("for",newtr.getElementsByTagName('LABEL')[0].getAttribute("for")+rand);
	}
function removeLine(tr) {
	tr.parentNode.removeChild(tr);
	}

function kCheckForm(f) {
	var input=document.getElementsByTagName('INPUT');
	for(var i=0;input[i];i++) {
		if(input[i].name=='variable_mandatory[]') {
			input[i].value=(input[i].checked?'y':'n');
			input[i].checked=true;
			}
		}
	return true;
	}