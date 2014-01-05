function kInEvidenzaShow(id) {
	var c=document.getElementById('inEvidenzaTabs');
	for(var i=0;c.childNodes[i];i++) {
		if(c.childNodes[i].nodeType==1) c.childNodes[i].className='';
		}
	if(c.childNodes[id].nodeType==1) c.childNodes[id].className='sel';
	var c=document.getElementById('inEvidenza');
	for(var i=0;c.childNodes[i];i++) {
		if(c.childNodes[i].nodeType==1) c.childNodes[i].className='';
		}
	if(c.childNodes[id].nodeType==1) c.childNodes[id].className='sel';
	}

function removePlaceholder(inp) {
	if(!inp.getAttribute("placeholder")) inp.setAttribute("placeholder",inp.value);
	if(inp.value==inp.getAttribute("placeholder")) {
		inp.value="";
		inp.style.color="#000";
		}
	}
function setPlaceholder(inp) {
	if(inp.value=="") {
		inp.value=inp.getAttribute("placeholder");
		inp.style.color="#aaa";
		}
	}
