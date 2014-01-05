/* (c) 2012 Kalamun GPLv3 */

var kAutocomplete=function() {
	var timer=null,input=null,inputID=null,inputClear=null,Ul=null,lang=null;

	var init=function(ll) {
		lang=ll;
		if(ll.substring(0,1)=="-") ll="";
		input=document.getElementById('translation'+ll);
		inputID=document.getElementById('translation_id'+ll);
		inputClear=document.getElementById('translation_clear'+ll);
		input.addEventListener('keypress',onKeyPress,true);
		input.addEventListener('blur',off,true);
		if(inputClear) inputClear.addEventListener('click',clear,true);
		input.setAttribute("autocomplete","off");
		if(timer) clearTimeout(timer);
		}
	this.init=init;

	var onKeyPress=function(e) {
		var k=e.keyCode;
		var sel=ulGetSelectedId();
        if(!e) var e = window.event;

		//arrow down = 40 or 98
		if(k==40||k==98) {
			if(Ul) {
				if(sel<0) {
					if(Ul.childNodes[0]) Ul.childNodes[0].className='sel';
					}
				else if(Ul.childNodes[sel+1]) {
					Ul.childNodes[sel].className='';
					Ul.childNodes[sel+1].className='sel';
					}
				}
	        if(e.stopPropagation) {
                e.stopPropagation();
                e.preventDefault();
		        }
			return false;
			}
		//arrow up = 38 or 104
		if(k==38||k==104) {
			if(Ul) {
				if(sel<0) {
					if(Ul.childNodes.length>0) Ul.childNodes[Ul.childNodes.length-1].className='sel';
					}
				else if(Ul.childNodes[sel-1]) {
					Ul.childNodes[sel].className='';
					Ul.childNodes[sel-1].className='sel';
					}
				}
	        if(e.stopPropagation) {
                e.stopPropagation();
                e.preventDefault();
		        }
			return false;
			}
		//enter = 13
		if(k==13) {
	        e.cancelBubble=true;
	        e.returnValue=false;
	        if(e.stopPropagation) {
                e.stopPropagation();
                e.preventDefault();
		        }
		    setTranslationByRef();
		    hideSuggestions();
			return false;
			}
		//tab = 9
		if(k==9) {
			return false;
			}
		//show suggestions
		if(input.value.length>3) {
			timer=setTimeout(loadSuggestions,500);
			}
		}
	var loadSuggestions=function() {
		if(input) {
			//create container
			if(Ul&&Ul.parentNode) Ul.parentNode.removeChild(Ul);
			Ul=document.createElement('UL');
			input.parentNode.appendChild(Ul);
			Ul.className='suggestions';
			Ul.top=input.offsetHeight+'px';
			//load contents
			var aj=new kAjax();
			aj.onSuccess(populateSuggestions);
			aj.onFail(hideSuggestions);
			aj.send("post","ajax/translationsHandler.php","&getSuggestions="+escape(input.value)+"&ll="+escape(lang));
			}
		}
	var populateSuggestions=function(html,xml) {
		var lines=html.split("\n");
		for(var i=0;lines[i];i++) {
			if(lines[i]!="") {
				var line=lines[i].split("\t");
				var li=document.createElement('LI');
				var small=document.createElement('SMALL');
				var div=document.createElement('DIV');
				div.appendChild(document.createTextNode(line[3]));
				var dir="";
				if(lang.substring(0,1)=="-") dir+="["+line[0]+"] ";
				dir+=line[2];
				small.appendChild(document.createTextNode(dir));
				li.appendChild(div);
				li.appendChild(small);
				li.setAttribute('refid',line[1]);
				li.onclick=setTranslationByRef;
				Ul.appendChild(li);
				}
			}
		}
	var ulGetSelectedId=function(){
		if(!Ul) return -1;
		for(var i=0;Ul.childNodes[i];i++) {
			if(Ul.childNodes[i].className=='sel') return i;
			}
		return -1;
		}
	var clear=function() {
		input.value="";
		inputID.value="";
		}
	var off=function() {
		setTimeout(hideSuggestions,100);
		}
	var hideSuggestions=function() {
		if(Ul&&Ul.parentNode) Ul.parentNode.removeChild(Ul);
		}
	function setTranslationByRef() {
		if(this&&this!=window&&this.getAttribute('refid')) var sel=this;
		else var sel=Ul.childNodes[ulGetSelectedId()];
		var id=sel.getAttribute('refid');
		var title=sel.getElementsByTagName('DIV')[0].innerHTML;
		input.value=title;
		inputID.value=id;
		}
	}
