var setFeaturedImage=function(ids)
{
	var inpt=document.getElementById('featuredimage');
	if(inpt)
	{
		inpt.value=ids[0].id;
		var imgcontainer=document.getElementById('featuredImageContainer');
		imgcontainer.innerHTML='';
		var img=document.createElement('IMG');
		img.src=ids[0].dir + ids[0].thumbnail;
		imgcontainer.appendChild(img);
		var imgcontainer=document.getElementById('removeFeaturedImage').style.display='';
	}
	k_closeIframeWindow();
}

var removeFeaturedImage=function(ids)
{
	var inpt=document.getElementById('featuredimage');
	if(inpt)
	{
		inpt.value='';
		var imgcontainer=document.getElementById('featuredImageContainer');
		imgcontainer.innerHTML='';
		var imgcontainer=document.getElementById('removeFeaturedImage').style.display='none';
	}
}
