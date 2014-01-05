/* menu */
function kMenuInit() {
	var divMenu=document.getElementById('menu');
	var mainUl=divMenu.getElementsByTagName('UL')[0];
	for(i=0;mainUl.getElementsByTagName('UL')[i];i++) {
		mainUl.getElementsByTagName('UL')[i].style.display='none';
		}
	kMenuArrange();
	}
function kMenuArrange() {
	var divMenu=document.getElementById('menu');
	var mainUl=divMenu.getElementsByTagName('UL')[0];
	for(i=0;mainUl.getElementsByTagName('UL')[i];i++) {
		mainUl.getElementsByTagName('UL')[i].style.display='block';
		mainUl.getElementsByTagName('UL')[i].style.minWidth=mainUl.getElementsByTagName('UL')[i].parentNode.offsetWidth-30+'px';
		}
	for(i=0;mainUl.getElementsByTagName('LI')[i];i++) {
		var li=mainUl.getElementsByTagName('LI')[i];
		if(li.tagName=='LI') {
			li.onmouseover=kMenuOnOver;
			li.onmouseout=kMenuOnOut;
			}
		}
	for(i=0;mainUl.getElementsByTagName('UL')[i];i++) {
		var ul=mainUl.getElementsByTagName('UL')[i];
		var ulw=ul.offsetWidth;
		for(j=0;ul.childNodes[j];j++) {
			var li=ul.childNodes[j];
			if(li.tagName=='LI') {
				if(li.getElementsByTagName('UL').length>0) li.childNodes[0].className='haveChilds';
				}
			}
		for(j=0;ul.getElementsByTagName('UL')[j];j++) {
			ulw+=ul.getElementsByTagName('UL')[j].offsetWidth;
			}
		ul.style.overflow="hidden";
		if(ul.offsetHeight>0) ul.setAttribute('openheight',ul.offsetHeight);
		ul.style.height=0;
		}
	}
function kMenuOnOver() {
	var ul=this.getElementsByTagName('UL')[0];
	if(ul) ul.style.height=ul.getAttribute('openheight')+'px';
	}
function kMenuOnOut() {
	var ul=this.getElementsByTagName('UL')[0];
	if(ul) ul.style.height=0;
	}

	
	

/******************************/
/* SHOP                       */
/******************************/

/* shop */
var kShop=function() {
	var handler=TEMPLATEDIR+"ajax/shopHandler.php";
	var cartDest=null;
	var guide=null, miniwidget=null, clickedlink=null;

	this.printCartList=function(dest,removeButton,buyButton) {
		var ajax=new kAjax();
		if(dest) cartDest=dest;
		if(!cartDest) cartDest='shopCartList';
		if(removeButton==undefined) var removeButton=false;
		if(buyButton==undefined) var buyButton=true;
		ajax.onSuccess(function(html,xml) {
			if(cartDest) document.getElementById(cartDest).innerHTML=html;
			});
		ajax.onFail(function(error) { });
		ajax.send("get",handler,"&printCartList&removeButton="+escape(removeButton)+"&buyButton="+escape(buyButton));
		}
	var printCartList=this.printCartList;

	this.updateWidget=function(dest) {
		var ajax=new kAjax();
		if(dest) cartDest=dest;
		if(!cartDest) cartDest='shopCartList';
		if(removeButton==undefined) var removeButton=false;
		if(buyButton==undefined) var buyButton=true;
		removeGuide();
		ajax.onSuccess(function(html,xml) {
			if(!miniwidget) miniwidget=document.getElementById('cartMiniWidget');
			if(miniwidget) miniwidget.innerHTML=html;
			});
		ajax.onFail(function(error) { });
		ajax.send("get",handler,"&printCompactCart");
		}
	var updateWidget=this.updateWidget;
	
	this.updateMiniWidget=function(iditem) {
		if(!miniwidget) miniwidget=document.getElementById('cartMiniWidget');
		if(miniwidget) {
			var ajax=new kAjax();
			ajax.onSuccess(function(html,xml) {
				miniwidget.innerHTML=html;
				});
			ajax.send("post",TEMPLATEDIR+"ajax/shopWidget.php","&iditem="+escape(iditem));
			}
		}
	var updateMiniWidget=this.updateMiniWidget;
	
	this.addToCart=function(iditem,clicked,removeButton,buyButton) {
		var ajax=new kAjax();
		clickedlink=clicked?clicked:null;
		if(!miniwidget) miniwidget=document.getElementById('cartMiniWidget');
		ajax.onSuccess(function(html,xml) {
			updateMiniWidget(iditem);
			if(cartDest) printCartList(cartDest,removeButton,buyButton);
			});
		ajax.onFail(function(error) { });
		ajax.send("post",handler,"&addToCart="+escape(iditem));
		}
	
	this.removeGuide=function() {
		if(guide) guide.parentNode.removeChild(guide,true);
		guide=null;
		}
	var removeGuide=this.removeGuide;

	this.removeFromCart=function(iditem,removeButton,buyButton) {
		var ajax=new kAjax();
		ajax.onSuccess(function(html,xml) {
			updateWidget();
			if(cartDest) printCartList(cartDest,removeButton,buyButton);
			});
		ajax.onFail(function(error) { });
		ajax.send("post",handler,"&removeFromCart="+escape(iditem)+"&removeButton="+escape(removeButton)+"&buyButton="+escape(buyButton));
		}
	
	this.refreshDeliverer=function(id,ll) {
		var ajax=new kAjax();
		ajax.onSuccess(function(html,xml) {
			document.getElementById(id).innerHTML=html;
			});
		ajax.onFail(function(error) { });
		ajax.send("get",handler,"&getDeliversByCountryCode="+escape(ll));
		}

	this.refreshPayments=function(id,ll) {
		var ajax=new kAjax();
		ajax.onSuccess(function(html,xml) {
			document.getElementById(id).innerHTML=html;
			});
		ajax.onFail(function(error) { });
		ajax.send("get",handler,"&getPaymentsByCountryCode="+escape(ll));
		}
	
	this.saveCartVars=function(id) {
		var form=document.getElementById(id);
		if(!form) return false;
		var ajax=new kAjax();
		var vars='&saveOrderData=true';
		for(var i=0;form.getElementsByTagName('INPUT')[i];i++) {
			var input=form.getElementsByTagName('INPUT')[i];
			if(input.name&&input.type=='text') vars+='&'+input.name+'='+escape(input.value);
			else if(input.name&&(input.type=='checkbox'||input.type=='radio')&&input.checked) vars+='&'+input.name+'='+escape(input.value);
			}
		for(var i=0;form.getElementsByTagName('TEXTAREA')[i];i++) {
			var textarea=form.getElementsByTagName('TEXTAREA')[i];
			vars+='&'+textarea.name+'='+escape(textarea.value);
			}
		for(var i=0;form.getElementsByTagName('SELECT')[i];i++) {
			var select=form.getElementsByTagName('SELECT')[i];
			vars+='&'+select.name+'='+escape(select.value);
			}
		ajax.send("post",handler,vars);
		}

	this.getOrderSummary=function(id) {
		var ajax=new kAjax();
		ajax.onSuccess(function(html,xml) {
			document.getElementById(id).innerHTML=html;
			});
		ajax.onFail(function(error) { });
		ajax.send("get",handler,"&getOrderSummary");
		}

	this.saveOrder=function(id) {
		var ajax=new kAjax();
		ajax.onSuccess(function(html,xml) {
			document.getElementById(id).innerHTML=html;
			if(document.getElementById('PayPalForm')) document.getElementById('PayPalForm').submit();
			});
		ajax.onFail(function(error) { });
		ajax.send("post",handler,"&saveOrder=true");
		}

	this.syncField=function(field) {
		field.setAttribute('edited','true');
		if(field.name) {
			if(document.getElementById('field_del_'+field.name)) {
				var targetfield=document.getElementById('field_del_'+field.name);
				if(!targetfield.getAttribute('edited')) {
					if(field.tagName=='INPUT') targetfield.value=field.value;
					else if(field.tagName=='SELECT') {
						for(var i=0;targetfield.options[i];i++) {
							if(targetfield.options[i].value==field.value) targetfield.selectedIndex=i;
							}
						}
					}
				}
			}
		}
	
	this.openAjaxPopup=function(dir) {
		closeAjaxPopup();
		var bkg=document.createElement('DIV');
		bkg.id="shopIpopupBkg";
		bkg.addEventListener("click",closeAjaxPopup);
		document.body.appendChild(bkg);
		var win=document.createElement('DIV');
		win.id="shopIpopupWindow";
		document.body.appendChild(win);
		win.style.left=(bkg.offsetWidth-win.offsetWidth)/2+'px';
		win.style.top=(bkg.offsetHeight-win.offsetHeight)/2+'px';
		var aj=new kAjax();
		aj.onSuccess(populateAjaxPopup);
		aj.send("get",TEMPLATEDIR+'ajax/shopItem.php','&dir='+escape(dir));
		}
	
	this.populateAjaxPopup=function(html,xml) {
		document.getElementById('shopIpopupWindow').innerHTML=html;
		}
	var populateAjaxPopup=this.populateAjaxPopup;
	
	this.closeAjaxPopup=function() {
		var bkg=document.getElementById('shopIpopupBkg');
		if(bkg) bkg.parentNode.removeChild(bkg,true);
		var win=document.getElementById('shopIpopupWindow');
		if(win) win.parentNode.removeChild(win,true);
		}
	var closeAjaxPopup=this.closeAjaxPopup;

	}