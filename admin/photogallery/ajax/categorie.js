/* funzioni per ajax */
var categorieBkup=Array();

var k_showCat=function(success) {
	var categorieContainer=document.getElementById('categorie');
	var categorieNuovaInput=document.getElementById('nuovaCategoria');
	categorieContainer.innerHTML=success;
	var chk=categorieContainer.getElementsByTagName('input');
	for(var i=0;chk[i];i++) {
		if(chk[i].type=="checkbox") {
			if(categorieBkup[i]) chk[i].checked=categorieBkup[i];
			chk[i].addEventListener('change',k_refreshCatSelection);
			}
		}
	}

function k_refreshCatSelection(e,elm) {
	if(!elm) elm=this;

	//(un)select childs
	var selected=0;
	for(var i=0;elm.parentNode.getElementsByTagName('INPUT')[i];i++) {
		if(elm.parentNode.getElementsByTagName('INPUT')[i].checked) selected++;
		}
	for(var i=0;elm.parentNode.getElementsByTagName('INPUT')[i];i++) {
		if(elm.checked&&selected==1) elm.parentNode.getElementsByTagName('INPUT')[i].checked=true;
		else if(!elm.checked&&selected>0) elm.parentNode.getElementsByTagName('INPUT')[i].checked=false;
		}

	//(un)select parent
	var parent=elm.parentNode;
	while(parent&&parent.tagName!='UL') { parent=parent.parentNode; }
	var parentCheck=false;
	var firstParentCheck=parent.parentNode.getElementsByTagName('INPUT')[0];
	if(firstParentCheck!=parent.getElementsByTagName('INPUT')[0]) parentCheck=firstParentCheck;
	var selected=0;
	for(var i=0;parent.getElementsByTagName('INPUT')[i];i++) {
		if(parent.getElementsByTagName('INPUT')[i].checked) selected++;
		}
	if(parentCheck) {
		parentCheck.checked=(selected==0?false:true);
		k_refreshCatSelection(false,parentCheck);
		}
	}

function k_reloadCat(idphg) {
	var categorieContainer=document.getElementById('categorie');
	var categorieNuovaInput=document.getElementById('nuovaCategoria');
	var aj=new kAjax();
	aj.onSuccess(k_showCat);
	aj.onFail(k_showCat);
	aj.send('post','ajax/categorie.php','idphg='+idphg);
	}

function k_nuovaCat() {
	var categorieContainer=document.getElementById('categorie');
	var categorieNuovaInput=document.getElementById('nuovaCategoria');
	var chk=categorieContainer.getElementsByTagName('input');
	for(var i=0;chk[i];i++) {
		if(chk[i].type=="checkbox") categorieBkup[i]=chk[i].checked;
		}
	var name=categorieNuovaInput.value;
	categorieContainer.innerHTML='Loading...';
	var aj=new kAjax();
	aj.onSuccess(k_reloadCat);
	aj.onFail(k_reloadCat);
	aj.send('post','ajax/nuovaCat.php','categoria='+escape(name));
	}

function k_deleteCat(idcat) {
	var categorieContainer=document.getElementById('categorie');
	var categorieNuovaInput=document.getElementById('nuovaCategoria');
	var chk=categorieContainer.getElementsByTagName('input');
	for(var i=0;chk[i];i++) {
		if(chk[i].type=="checkbox") categorieBkup[i]=chk[i].checked;
		}
	categorieContainer.innerHTML='Loading...';
	var aj=new kAjax();
	aj.onSuccess(k_reloadCat);
	aj.onFail(k_reloadCat);
	aj.send('post','ajax/deleteCat.php','idcat='+escape(idcat));
	}

function k_keypressCat(event) {
	if(event.keyCode==13) {
		k_nuovaCat();
		return false;
		}
	}
