/* (c) Kalamun.org - GNU/GPL 3 */

function kEditor() {
	var textarea=null;
	
	this.init=function(id) {
		textarea=document.getElementById(id);
		if(!textarea) return false;
		textarea.addEventListener("keydown",catchKeys);
		}
	
	var catchKeys=function(e) {
		if(e.keyCode === 9) { // tab was pressed
			// get caret position/selection
			var start=textarea.selectionStart;
			var end=textarea.selectionEnd;
			var value=textarea.value;

			// set textarea value to: text before caret + tab + text after caret
			textarea.value=value.substring(0,start)+"\t"+value.substring(end);

			// put caret at right position again (add one for the tab)
			textarea.selectionStart=textarea.selectionEnd=start+1;

			// prevent the focus lose
			e.preventDefault();
			}
		}

	this.explorerSwapChild=function(li) {
		if(!li) return false;
		var ul=li.getElementsByTagName('UL')[0];
		if(!ul) return false;
		ul.style.display=ul.style.display!='block'?'block':'none';
		}
	
	}