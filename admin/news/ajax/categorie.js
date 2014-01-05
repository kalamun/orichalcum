/* funzioni per ajax */
var categorieBkup=Array();

var k_showCat=function(success) {
	var categorieContainer=document.getElementById('categorie');
	var categorieNuovaInput=document.getElementById('nuovaCategoria');
	categorieContainer.innerHTML=success;
	var chk=categorieContainer.getElementsByTagName('input');
	for(var i=0;chk[i];i++) {
		if(chk[i].type=="checkbox"&&categorieBkup[i]) chk[i].checked=categorieBkup[i];
		}
	}

function k_reloadCat(idnews) {
	var categorieContainer=document.getElementById('categorie');
	var categorieNuovaInput=document.getElementById('nuovaCategoria');
	var aj=new kAjax();
	aj.onSuccess(k_showCat);
	aj.send('post','ajax/categorie.php','idnews='+idnews);
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
