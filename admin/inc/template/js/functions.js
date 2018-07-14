"strict mode";

var ok_add_event = function( obj, event, func, model )
{
	if( !model ) model = true;
	if( obj.addEventListener ) return obj.addEventListener( event, func,model );
	if( obj.attachEvent ) return obj.attachEvent( 'on' + event, func );
	return false;
}


/*
USAGE:
var aj = new ok_ajax();
aj.onSuccess( onSuccessCallBackFunction );
aj.onFail( onFailCallBackFunction );
aj.send( "post", "ajaxHandler.php", "action=read&id=111", "json");
*/

var ok_ajax = function()
{
	var onSuccess = function(txt) {};
	var onFail = function(txt) {};
	var xhr = null;
	var method = "get";
	var uri = "";
	var vars = "";
	var return_format = "html";

	this.send = function(v_method, v_uri, v_vars, v_return_format)
	{
		method = v_method.toLowerCase();
		uri = v_uri;
		vars = v_vars;
		return_format = v_return_format;
		ajaxSend();
	}
	this.onSuccess = function(func) { onSuccess=func }
	this.onFail = function(func) { onFail=func; }

	function createXMLHttpRequest()
	{
		var XHR=null;
		if(typeof(XMLHttpRequest)==="function" || typeof(XMLHttpRequest)==="object") XHR = new XMLHttpRequest(); //browser standard
		else if(window.ActiveXObject) XHR = new ActiveXObject("Msxml2.XMLHTTP"); //IE
		return XHR;
	}

	function onStateChange()
	{
		if(xhr.readyState === 4)
		{
			if(xhr.status == 200) {
				if(return_format == 'html') onSuccess(xhr.responseText);
				else if(return_format == 'xml') onSuccess(xhr.xhr.responseXML);
				else if(return_format == 'json') onSuccess(JSON.parse(xhr.responseText));
			}
			else onFail(xhr.status);
		}
	}

	function ajaxSend()
	{
		xhr=createXMLHttpRequest();
		if(method=="get")
		{
			uri+="?"+vars;
			xhr.open(method, uri, true);
			xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			xhr.onreadystatechange = onStateChange;
			xhr.send(null);

		} else if(method == "post") {
			xhr.open(method, uri, true);
			xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			xhr.setRequestHeader("content-type","application/x-www-form-urlencoded");
			xhr.onreadystatechange = onStateChange;
			xhr.send(vars);
		}

		delete xhr;
	}	
}


/*
* SERIALIZE FORMS
*/
function ok_serialize_form( form )
{
	if( !form || form.nodeName !== "FORM" ) return;

	var j, q = [];
	for( var i=form.elements.length-1; i>=0; i-=1 )
	{
		var elm=form.elements[i];
		if(elm.name==="") continue;
		if(elm.tagName==="") continue;

		if(elm.tagName=='INPUT')
		{
			switch(elm.type)
			{
				case 'checkbox':
				case 'radio':
					if(elm.checked) q.push(encodeURIComponent(elm.name)+"="+encodeURIComponent(form.elements[i].value));
					break;
				case 'file':
					break; 
				default:
					q.push(encodeURIComponent(elm.name)+"="+encodeURIComponent(elm.value));
			}

		} else if(elm.tagName=='TEXTAREA') {
			q.push(encodeURIComponent(elm.name)+"="+encodeURIComponent(elm.value));

		} else if(elm.tagName=='SELECT') {
			switch(elm.type) {
				case 'select-one':
					q.push(encodeURIComponent(elm.name)+"="+encodeURIComponent(elm.value));
					break;
				case 'select-multiple':
					for(var j=elm.options.length-1; j>=0; j=j-1)
					{
						if(elm.options[j].selected) q.push(encodeURIComponent(elm.name)+"="+encodeURIComponent(elm.options[j].value));
					}
					break;
			}

		} else if(elm.tagName=='BUTTON') {
			q.push(encodeURIComponent(elm.name)+"="+encodeURIComponent(elm.value));
		}
	}
	return q.join("&");
}

