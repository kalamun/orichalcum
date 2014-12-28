// imgresize utils

function kRefreshInit()
{
	kAddEvent(document.getElementById('img_mobile1'), "change", kShowMobileRatio);
	kAddEvent(document.getElementById('img_size1'), "keyup", kRefreshPreview);
	kAddEvent(document.getElementById('img_size2'), "keyup", kRefreshPreview);
	kAddEvent(document.getElementById('img_resize2'), "keyup", kRefreshPreview);
	kAddEvent(document.getElementById('img_resize2'), "change", kRefreshPreview);
	kAddEvent(document.getElementById('thumb_size1'), "keyup", kRefreshPreview);
	kAddEvent(document.getElementById('thumb_size2'), "keyup", kRefreshPreview);
	kAddEvent(document.getElementById('thumb_resize2'), "keyup", kRefreshPreview);
	kAddEvent(document.getElementById('thumb_resize2'), "change", kRefreshPreview);
}

function kShowMobileRatio()
{
	cb=document.getElementById('img_mobile1');
	document.getElementById('img_mobile2container').style.display = cb.checked ? 'inline' : 'none';
}
kShowMobileRatio();

function kRefreshPreview()
{
	var prefix = Array( "img", "thumb" );
	
	for(var i=0; prefix[i]; i++)
	{
		var image_size1=parseInt(document.getElementById(prefix[i]+'_size1').value);
		var image_size2=parseInt(document.getElementById(prefix[i]+'_size2').value);
		var image_resize2=document.getElementById(prefix[i]+'_resize2').value;

		var tmpw=100;
		var tmph=60;
		var vtmpw=100;
		var vtmph=80;
		
		var previewcontainer=document.getElementById(prefix[i]+'_container');
		var previewimageh=document.getElementById(prefix[i]+'_imageh');
		var previewimagev=document.getElementById(prefix[i]+'_imagev');
		
		previewcontainer.style.height = parseInt(previewcontainer.offsetWidth/image_size1*image_size2) + 'px';
		previewcontainer.style.top = - parseInt(previewcontainer.offsetHeight/2) + 'px';
		
		previewimageh.style.width = previewcontainer.offsetWidth + 'px';
		previewimageh.style.height = parseInt(previewcontainer.offsetWidth / tmpw * tmph) + 'px';
		
		previewimagev.style.width = previewcontainer.offsetWidth + 'px';
		previewimagev.style.height = parseInt(previewcontainer.offsetWidth / vtmph * vtmpw) + 'px';

		if(image_resize2=="inside")
		{
			if(previewimageh.offsetHeight > previewcontainer.offsetHeight)
			{
				previewimageh.style.height = previewcontainer.style.height;
				previewimageh.style.width = parseInt(previewcontainer.offsetHeight / tmph * tmpw) + 'px';
			}
			
			if(previewimagev.offsetHeight > previewcontainer.offsetHeight)
			{
				previewimagev.style.height = previewcontainer.style.height;
				previewimagev.style.width = parseInt(previewcontainer.offsetHeight / vtmpw * vtmph) + 'px';
			}
		
		} else if(image_resize2=="outside") {
			if(previewimageh.offsetHeight < previewcontainer.offsetHeight)
			{
				previewimageh.style.height = previewcontainer.style.height;
				previewimageh.style.width = parseInt(previewcontainer.offsetHeight / tmph * tmpw) + 'px';
			}

			if(previewimagev.offsetHeight < previewcontainer.offsetHeight)
			{
				previewimagev.style.height = previewcontainer.style.height;
				previewimagev.style.width = parseInt(previewcontainer.offsetHeight / vtmph * vtmpw) + 'px';
			}

		} else {
			previewimageh.style.height = previewcontainer.style.height;
			previewimageh.style.width = previewcontainer.offsetWidth + 'px';
			previewimagev.style.height = previewcontainer.style.height;
			previewimagev.style.width = previewcontainer.offsetWidth + 'px';
		}
		
		previewimageh.style.top = - parseInt(previewimageh.offsetHeight/2) + 'px';
		previewimageh.style.left = - parseInt(previewimageh.offsetWidth/2) + 'px';
		previewimagev.style.top = - parseInt(previewimagev.offsetHeight/2) + 'px';
		previewimagev.style.left = - parseInt(previewimagev.offsetWidth/2) + 'px';
	}
	
}

kRefreshInit();
kRefreshPreview();



var kReprocessing=false;

function kStartReprocess(dbcount)
{
	if(kReprocessing==true) return false;
	
	kReprocessing=true;
	var kR = new kReprocess();
	kR.init(dbcount);
	kR.start();
}

function kReprocess()
{
	var progressBar = document.getElementById('progressBar');
	var completedBar = document.getElementById('completedBar');
	var percent = completedBar.getElementsByTagName('DIV')[0];
	var dbcont=0;
	var processed = 0;
	var aj=null;
	
	var init = function(dbc)
	{
		progressBar.style.display='block';
		completedBar.style.width='0%';
		processed = 0;
		dbcount=parseInt(dbc);
	}
	this.init = init;
	
	var start = function()
	{
		aj = new kAjax();
		aj.onSuccess(onSuccessHandler);
		processNextBlock();
	}
	this.start = start;
	
	var processNextBlock = function()
	{
		aj.send("GET", "ajax/imagesHandler.php", "reprocess="+processed);
	}
	
	var onSuccessHandler = function(html)
	{
		if(html!="true")
		{
			kReprocessing=false;

			if(processed+10 >= dbcount) return false; // end
			alert('Processing interrupted due to a fatal error');
			return false;
		}
		
		processed+=5; // five images at time
		if(processed > dbcount) processed = dbcount;
		completedBar.style.width = Math.round(100/dbcount*processed)+'%';
		percent.innerHTML = Math.round(100/dbcount*processed)+'%';
		processNextBlock();
	}
}
