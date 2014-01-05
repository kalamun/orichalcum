/* (c) 2012 Kalamun.org GPLv3 */

function showTab(id) {
	var tabs=document.getElementById('tabs');
	var tabsContents=document.getElementById('tabsContents');
	for(var i=0;tabs.getElementsByTagName('A')[i];i++) {
		var tab=tabs.getElementsByTagName('A')[i];
		tab.className=(tab.href.indexOf("'"+id+"'")>=0)?'sel':'';
		}
	for(var i=0;tabsContents.childNodes[i];i++) {
		if(tabsContents.childNodes[i].tagName=='DIV') tabsContents.childNodes[i].style.display='none';
		}
	document.getElementById('tab-'+id).style.display='block';

	}
