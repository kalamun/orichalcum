function processQueue()
{
	var w=400;
	var h=250;
	var l=(screen.width-w)/2;
	var t=(screen.height-h)/2;
	var popup=window.open("","processQueue","status=0,height="+h+",width="+w+",top="+t+",left="+l+",resizable=0");
	if(popup.location.href="about:blank") {
		popup.location.href=ADMINDIR+"newsletter/ajax/processQueue.php";
		}
	popup.focus();
	return popup;
}

function openPreviewPopup()
{
	for(var i=0, c=txts.countAreas(); i<c; i++)
	{
		txts.getArea(i).swapDesignMode();
		txts.getArea(i).swapDesignMode();
	}
	var subject=document.getElementById('subject').value;
	/*var template=document.getElementById('template').value;
	var message=document.getElementById('message').value;*/
	kOpenIPopUp('ajax/previewManager.php','&subject='+encodeURIComponent(subject),'700px','80%');
}


function loadTemplate(e)
{
	var aj=new kAjax();
	aj.onSuccess(processTemplate);
	aj.send("get", "ajax/templateHandler.php", "&template="+encodeURIComponent(this.getAttribute('data-template')));
}

function processTemplate(html)
{
	var blocks = html.split("\n");
	var container = document.getElementById('additionalBlocks');
	
	//clean unsupported blocks
	for(var i=container.childNodes.length-1, c=container.childNodes; c[i]; i--)
	{
		if(c[i].nodeType!=1 && c[i].getAttribute('data-block')==false) continue;
		
		// for now... delete all the textareas
		valid = false;
/*		var id = c[i].getAttribute('data-block');
		var valid = false;
		for(var j=0; blocks[j]; j++)
		{
			if(id == blocks[j]) valid = true;
		} */
		
		if(valid == false) c[i].parentNode.removeChild(c[i],true);
	}
	
	for(var i=0; blocks[i]; i++)
	{
		if(blocks[i]=='-default-') continue;

		var div = document.createElement('DIV');
		div.setAttribute("data-block", blocks[i]);
		
		var br = document.createElement('BR');
		
		var label = document.createElement('LABEL');
		label.appendChild( document.createTextNode(blocks[i]) );
		
		var textarea = document.createElement('TEXTAREA');
		textarea.setAttribute("name", "block-"+blocks[i]);
		textarea.setAttribute("editor", "kzen");
		textarea.setAttribute("id", "block-"+blocks[i]);
		textarea.setAttribute("style", "width:100%;height:200px;");
		
		div.appendChild(br);
		div.appendChild(label);
		div.appendChild(textarea);
		container.appendChild(div);
	}
	
	txts.init(ADMINDIR);
}
