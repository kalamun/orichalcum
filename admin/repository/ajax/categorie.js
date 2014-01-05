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
	b3_ajaxSend('post','ajax/categorie.php','idnews='+idnews,k_showCat);
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
	b3_ajaxSend('post','ajax/nuovaCat.php','categoria='+escape(name),k_reloadCat,k_reloadCat);
	}

function k_deleteCat(idcat) {
	var categorieContainer=document.getElementById('categorie');
	var categorieNuovaInput=document.getElementById('nuovaCategoria');
	var chk=categorieContainer.getElementsByTagName('input');
	for(var i=0;chk[i];i++) {
		if(chk[i].type=="checkbox") categorieBkup[i]=chk[i].checked;
		}
	categorieContainer.innerHTML='Loading...';
	b3_ajaxSend('post','ajax/deleteCat.php','idcat='+escape(idcat),k_reloadCat,k_reloadCat);
	}

function k_keypressCat(event) {
	if(event.keyCode==13) {
		k_nuovaCat();
		return false;
		}
	}
