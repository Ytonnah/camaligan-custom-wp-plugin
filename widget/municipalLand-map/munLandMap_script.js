/*////Zoom and Panning for Mouse and Touch\\\\*\
/////////////Created by Patrick James O. De Leon
/////////////for the Office map of LGU Camaligan*/
var MunLandMapStatus = document.getElementById('MunLand_holder');
if(MunLandMapStatus !== null)
{
    //Function Global Variables
    	var MunLandHolder = document.getElementById('MunLand_holder');
    	var MunLandMap = document.getElementById('MunLandMap_svg');
    	
    //Variable for Zoom Feature
    	var MunLandMapzoomFactor = 100; // How many pixels will be added to the Height per button click
    	var MunLandMapzoomMax = 3000; // Maximum Height
    	var MunLandMapzoomMin = MunLandMap.clientHeight; // Minimum Hieght
    	MunLandMap.style.left = 0;
    	MunLandMap.style.top = 0;
    	
    //Variable for Drag Feature
    	var MunLandMap_prevX, MunLandMap_prevY, MunLandMap_initialX, MunLandMap_initialY;
    	var MunLanddragTrigger, panLeftRight, panTopBottom;
    	MunLandMap.addEventListener("mousedown", MunLand_dragStart);
    	MunLandHolder.addEventListener("mousemove", MunLand_dragging);
    	MunLandHolder.addEventListener("mouseup", MunLand_dragEnd);
    	MunLandHolder.addEventListener("touchstart", MunLand_dragStart, true);
    	MunLandHolder.addEventListener("touchmove", MunLand_dragging, true);
    	MunLandHolder.addEventListener("touchend", MunLand_dragEnd, true);
    
    /*Zooming In Function*/
    function MunLand_ZoomIn()
    {
    	var MunLandMapcurrentHeight = MunLandMap.clientHeight;
    	if((MunLandMapcurrentHeight + MunLandMapzoomFactor) <= MunLandMapzoomMax)
    	{
    	    MunLandMap.style.height = MunLandMapcurrentHeight + MunLandMapzoomFactor;
    	}
    }
    
    /*Fit to Frame Function*/
    function MunLand_FitFrame()
    {
    	var MunLandcurrentHeight = MunLandMap.clientHeight;
    	var MunLandholderHeight = MunLandHolder.clientHeight;
    	var MunLandcurrentWidth = MunLandMap.clientWidth;
    	var MunLandholderWidth = MunLandHolder.clientWidth;
    	var MunLandcurrentRatio = MunLandcurrentWidth / MunLandcurrentHeight;
    	if((MunLandholderHeight *  MunLandcurrentRatio) <= MunLandholderWidth)
    	{
    		MunLandMap.style.height = MunLandholderHeight;
    	}
    	else
    	{
    		MunLandMap.style.height = MunLandholderWidth / MunLandcurrentRatio;
    	}
    	MunLandMap.style.left = 0;
    	MunLandMap.style.top = 0;
    }
    /*Zooming Out Function*/
    function MunLand_ZoomOut()
    {
    	var MunLandcurrentHeight = MunLandMap.clientHeight;
    	var MunLandholderHeight = MunLandHolder.clientHeight;
    	var MunLandcurrentWidth = MunLandMap.clientWidth;
    	var MunLandholderWidth = MunLandHolder.clientWidth;
    	var MunLandcurrentRatio = MunLandcurrentWidth / MunLandcurrentHeight;
		if((MunLandholderHeight * MunLandcurrentRatio) <= MunLandholderWidth)
		{
			if((MunLandcurrentHeight - MunLandMapzoomFactor) >= MunLandholderHeight)
			{
				MunLandMap.style.height = (MunLandcurrentHeight - MunLandMapzoomFactor) + "px";
			}
		}
		else
		{
			var temp = (MunLandcurrentHeight - MunLandzoomFactor);
			if((temp  * MunLandcurrentRatio) >= MunLandholderWidth)
			{
				MunLandMap.style.height = temp + "px";
			}
			else
			{
				MunLandMap.style.height = MunLandholderWidth / MunLandcurrentRatio;
			}
		}
		
		var offsetLeftMovement = parseInt(MunLandMap.style.left) + 200;
		if(offsetLeftMovement < 0)
		{
			MunLandMap.style.left =  offsetLeftMovement;
		}
		else
		{
			MunLandMap.style.left =  0;
		}
		
		var offsetTopMovement = parseInt(MunLandMap.style.top) + 100;
		if(offsetTopMovement < 0)
		{
			MunLandMap.style.top =  offsetTopMovement;
		}
		else
		{
			MunLandMap.style.top =  0;
		}
    }
    /*Drag Function  for Mouse and Touch*/
    function MunLand_dragStart()
    {
    	MunLandMap_prevX = parseInt(MunLandMap.style.left);
    	MunLandMap_prevY = parseInt(MunLandMap.style.top);
    	MunLandMap.style.cursor = "grabbing";
    	if(event.clientX != undefined)
    	{	
    		MunLandMap_initialX = event.clientX;
    		MunLandMap_initialY = event.clientY;
    	}
    	else if(event.touches[0].pageX != undefined)
    	{
    		MunLandMap_initialX = event.touches[0].pageX;
    		MunLandMap_initialY = event.touches[0].pageY;
    	}
    	MunLanddragTrigger = true;
    }
    
    function MunLand_dragging()
    {
    	if(MunLanddragTrigger == true)
    	{
    		boundary_toggle("barangay_boundary", "none");
    		
    		if(event.clientX != undefined)
    		{
    			var MunLandMap_finalX = event.clientX;
    			var MunLandMap_finalY = event.clientY;
    		}
    		else if(event.touches[0].pageX != undefined)
    		{	
    			var MunLandMap_finalX = event.touches[0].pageX;
    			var MunLandMap_finalY = event.touches[0].pageY;
    		}
    		//Panning Left and Right Condition
    		panLeftRight = true;
    		if(MunLandMap.clientWidth > MunLandHolder.clientWidth)
    		{
    		    var MunLandMap_movementX = MunLandMap_prevX + (MunLandMap_finalX - MunLandMap_initialX);
    		    var MunLandMapoffsetRight = parseInt(MunLandMap.style.left) + MunLandMap.clientWidth;
    		    if(parseInt(MunLandMap.style.left) >= 0 && MunLandMap_movementX > 0)
    		    {
    		        MunLandMap.style.left = 0;
    		        panLeftRight = false;
    		    }
    		    else if(MunLandMapoffsetRight <= MunLandHolder.clientWidth && MunLandMap_movementX < (MunLandHolder.clientWidth - MunLandMap.clientWidth))
    		    {
    		        MunLandMap.style.left = MunLandHolder.clientWidth - MunLandMap.clientWidth;
    		        panLeftRight = false;
    		    }
    		    else
    		    {
    		        if(panLeftRight == true)
    		        {
    		            MunLandMap.style.left = MunLandMap_movementX;
    		        }
    		    }
    		}
    		//Panning Top and Bottom Condition
    		panTopBottom = true;
    		if(MunLandMap.clientHeight > MunLandHolder.clientHeight)
    		{
    		    var MunLandMap_movementY = MunLandMap_prevY + (MunLandMap_finalY - MunLandMap_initialY);
    		    var MunLandMapoffsetBottom = parseInt(MunLandMap.style.top) + MunLandMap.clientHeight;
    		    if(parseInt(MunLandMap.style.top) >= 0 && MunLandMap_movementY > 0)
    		    {
    		        MunLandMap.style.top = 0;
    		        panTopBottom = false;
    		    }
    		    else if(MunLandMapoffsetBottom <= MunLandHolder.clientHeight && MunLandMap_movementY < (MunLandHolder.clientHeight - MunLandMap.clientHeight))
    		    {
    		        MunLandMap.style.top = MunLandHolder.clientHeight - MunLandMap.clientHeight;
    		        panTopBottom = false;
    		    }
    		    else
    		    {
    		        if(panTopBottom == true)
    		        {
    		            MunLandMap.style.top = MunLandMap_movementY;
    		        }
    		    }
    		}
    	}
    }
    
    function MunLand_dragEnd()
    {
    	MunLandMap.style.cursor = "grab";
    	MunLanddragTrigger = false;
    	boundary_toggle("barangay_boundary", "block");
    }
    
    function boundary_toggle(class_name, class_status)
    {
        var MunLanddisable_boundary = document.getElementsByClassName(class_name);
    		for(var i = 0; i < MunLanddisable_boundary.length; i++)
    		{
    			MunLanddisable_boundary[i].style.display = class_status;
    		}
    }
}