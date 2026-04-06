/*////Zoom and Panning for Mouse and Touch\\\\*\
/////////////Created by Patrick James O. De Leon
/////////////for the Land map of LGU Camaligan*/
var MunMapStatus = document.getElementById('municipalMapSvg_holder');
if(MunMapStatus != null)
{
	//Function Global Variables
		var MunMapHolder = document.getElementById('municipalMapSvg_holder');
		var MunMap = document.getElementById('municipalMap_svg');
		
	//Variable for Zoom Feature
		var MunMapzoomFactor = 100; // How many pixels will be added to the Height per button click
		var MunMapzoomMax = 3000; // Maximum Height
		var MunMapzoomMin = MunMapHolder.clientHeight; // Minimum Height
		MunMap.style.left = 0;
		MunMap.style.top = 0;
		
	//Variable for Drag Feature
		var MunMap_prevX, MunMap_prevY, MunMap_initialX, MunMap_initialY;
		var MunMapdragTrigger, panLeftRight, panTopBottom;
		MunMap.addEventListener("mousedown", MunMap_dragStart);
		MunMapHolder.addEventListener("mousemove", MunMap_dragging);
		MunMapHolder.addEventListener("mouseup", MunMap_dragEnd);
		MunMapHolder.addEventListener("touchstart", MunMap_dragStart, true);
		MunMapHolder.addEventListener("touchmove", MunMap_dragging, true);
		MunMapHolder.addEventListener("touchend", MunMap_dragEnd, true);

	/*Zooming In Function*/
	function MunMap_ZoomIn()
	{
	    var MunMapcurrentHeight = MunMap.clientHeight;
	    if((MunMapcurrentHeight + MunMapzoomFactor) <= MunMapzoomMax)
		{
			MunMap.style.height = MunMapcurrentHeight + MunMapzoomFactor;
		}
	}
	/*Fit to Frame Function*/
	function MunMap_FitFrame()
	{
		var MunMapcurrentHeight = MunMap.clientHeight;
		var MunMapholderHeight = MunMapHolder.clientHeight;
		var MunMapcurrentWidth = MunMap.clientWidth;
		var MunMapholderWidth = MunMapHolder.clientWidth;
		var MunMapcurrentRatio = MunMapcurrentWidth / MunMapcurrentHeight;
		if((MunMapholderHeight *  MunMapcurrentRatio) <= MunMapholderWidth)
		{
			MunMap.style.height = MunMapholderHeight;
		}
		else
		{
			MunMap.style.height = MunMapholderWidth / MunMapcurrentRatio;
		}
		MunMap.style.left = 0;
		MunMap.style.top = 0;
	}
	/*Zooming Out Function*/
	function MunMap_ZoomOut()
	{
		var MunMapcurrentHeight = MunMap.clientHeight;
		var MunMapholderHeight = MunMapHolder.clientHeight;
		var MunMapcurrentWidth = MunMap.clientWidth;
		var MunMapholderWidth = MunMapHolder.clientWidth;
		var MunMapcurrentRatio = MunMapcurrentWidth / MunMapcurrentHeight;
		if((MunMapholderHeight * MunMapcurrentRatio) <= MunMapholderWidth)
		{
			if((MunMapcurrentHeight - MunMapzoomFactor) >= MunMapholderHeight)
			{
				MunMap.style.height = (MunMapcurrentHeight - MunMapzoomFactor) + "px";
			}
		}
		else
		{
			var temp = (MunMapcurrentHeight - MunMapzoomFactor);
			if((temp  * MunMapcurrentRatio) >= MunMapholderWidth)
			{
				MunMap.style.height = temp + "px";
			}
			else
			{
				MunMap.style.height = MunMapholderWidth / MunMapcurrentRatio;
			}
		}
		
		var offsetLeftMovement = parseInt(MunMap.style.left) + 200;
		if(offsetLeftMovement < 0)
		{
			MunMap.style.left =  offsetLeftMovement;
		}
		else
		{
			MunMap.style.left =  0;
		}
		
		var offsetTopMovement = parseInt(MunMap.style.top) + 100;
		if(offsetTopMovement < 0)
		{
			MunMap.style.top =  offsetTopMovement;
		}
		else
		{
			MunMap.style.top =  0;
		}
	}
	
	/*Drag Function for Mouse and Touch*/
	function MunMap_dragStart()
	{
	    MunMap_prevX = parseInt(MunMap.style.left);
	    MunMap_prevY = parseInt(MunMap.style.top)
	    MunMap.style.cursor = "grabbing";
	    if(event.clientX != undefined)
		{	
			MunMap_initialX = event.clientX;
			MunMap_initialY = event.clientY;
		}
		else if(event.touches[0].pageX != undefined)
		{
			MunMap_initialX = event.touches[0].pageX;
			MunMap_initialY = event.touches[0].pageY;
		}
		MunMapdragTrigger = true;
	}
	
	function MunMap_dragging()
    {
        if(MunMapdragTrigger == true)
        {
            // Disables opening of Pagee of Offices when dragging
			boundary_toggle("office_boundary", "none");
			
			//Get the X and Y position while dragging
            if(event.clientX != undefined)
			{
				var MunMap_finalX = event.clientX;
				var MunMap_finalY = event.clientY;
			}
			else if(event.touches[0].pageX != undefined)
			{	
				var MunMap_finalX = event.touches[0].pageX;
				var MunMap_finalY = event.touches[0].pageY;
			}
			
            //Panning Left and Right Condition
            panLeftRight = true;
            if(MunMap.clientWidth > MunMapHolder.clientWidth)
            {
                var MunMap_movementX = MunMap_prevX + (MunMap_finalX - MunMap_initialX);
                var MunMapoffsetRight = parseInt(MunMap.style.left) + MunMap.clientWidth;
                if(parseInt(MunMap.style.left) >= 0 && MunMap_movementX > 0)
                {
                    MunMap.style.left = 0;
                    panLeftRight = false;
                }
                else if(MunMapoffsetRight <= MunMapHolder.clientWidth && MunMap_movementX < (MunMapHolder.clientWidth - MunMap.clientWidth))
                {
                    MunMap.style.left = MunMapHolder.clientWidth - MunMap.clientWidth;
                    panLeftRight = false;
                }
                else
                {
                    if(panLeftRight == true)
                    {
                        MunMap.style.left = MunMap_movementX;
                    }
                }
            }
            
            //Panning Top and Bottom Condition
            panTopBottom = true;
            if(MunMap.clientHeight > MunMapHolder.clientHeight)
            {
                var MunMap_movementY = MunMap_prevY + (MunMap_finalY - MunMap_initialY);
                var MunMapoffsetBottom= parseInt(MunMap.style.top) + MunMap.clientHeight;
                MunMap.style.top = MunMap_movementY;
                if(parseInt(MunMap.style.top) >= 0 && MunMap_movementY > 0)
                {
                    MunMap.style.top = 0;
                    panTopBottom = false;
                }
                else if(MunMapoffsetBottom <= MunMapHolder.clientHeight && MunMap_movementY < (MunMapHolder.clientHeight - MunMap.clientHeight))
                {
                    MunMap.style.top = MunMapHolder.clientHeight - MunMap.clientHeight;
                    panTopBottom = false;
                }
                else
                {
                     if(panTopBottom == true)
                    {
                        MunMap.style.top = parseInt(MunMap.style.top);
                    }
                }
            }
        }
    }
    
    function MunMap_dragEnd()
    {
        MunMap.style.cursor = "grab";
        MunMapdragTrigger = false;
        boundary_toggle("office_boundary", "block");
    }

	/*Staircase and BFI Button Triggers */
	//Sb Hall Staircase
	function sb_to2nd()
	{
		document.getElementById('sb_1st').style.display = "none";
		document.getElementById('sb_2nd').style.display = "block";
		
		document.getElementById('sb2nd_Trigger').style.fill = "#C82227";
		document.getElementById('sb1st_Trigger').style.fill = "#AAEFFF";
		
		document.getElementById('sb_fog').style.opacity = 0.3;
	}

	function sb_to1st()
	{
		document.getElementById('sb_1st').style.display = "block";
		document.getElementById('sb_2nd').style.display = "none";
		
		document.getElementById('sb2nd_Trigger').style.fill = "#AAEFFF";
		document.getElementById('sb1st_Trigger').style.fill = "#C82227";
		
		document.getElementById('sb_fog').style.opacity = 0;
	}

	//Executive Building Staircase
		//Going Up
		function executive_up2nd()
		{
			document.getElementById('executive_1st').style.display = "none";
			document.getElementById('executive_2nd').style.display = "block";
			document.getElementById('executive_3rd').style.display = "none";
			
			document.getElementById('executive1st_trigger').style.fill = "#AAEFFF";
			document.getElementById('executive2nd_trigger').style.fill = "#C82227";
			document.getElementById('executive3rd_trigger').style.fill = "#AAEFFF";
			
			document.getElementById('executive_fog').style.opacity = 0.3;
		}

		function executive_up3rd()
		{
			document.getElementById('executive_1st').style.display = "none";
			document.getElementById('executive_2nd').style.display = "none";
			document.getElementById('executive_3rd').style.display = "block";
			
			document.getElementById('executive1st_trigger').style.fill = "#AAEFFF";
			document.getElementById('executive2nd_trigger').style.fill = "#AAEFFF";
			document.getElementById('executive3rd_trigger').style.fill = "#C82227";
			
			document.getElementById('executive_fog').style.opacity = 0.6;
		}

		//Going Down
		function executive_down2nd()
		{
			document.getElementById('executive_1st').style.display = "none";
			document.getElementById('executive_2nd').style.display = "block";
			document.getElementById('executive_3rd').style.display = "none";
			
			document.getElementById('executive1st_trigger').style.fill = "#AAEFFF";
			document.getElementById('executive2nd_trigger').style.fill = "#C82227";
			document.getElementById('executive3rd_trigger').style.fill = "#AAEFFF";
			
			document.getElementById('executive_fog').style.opacity = 0.3;
		}

		function executive_down1st()
		{
			document.getElementById('executive_1st').style.display = "block";
			document.getElementById('executive_2nd').style.display = "none";
			document.getElementById('executive_3rd').style.display = "none";
			
			document.getElementById('executive1st_trigger').style.fill = "#C82227";
			document.getElementById('executive2nd_trigger').style.fill = "#AAEFFF";
			document.getElementById('executive3rd_trigger').style.fill = "#AAEFFF";
			
			document.getElementById('executive_fog').style.opacity = 0;
		}


	//People's Building Staircase
		//Going Up
		function peopleBldg_up2nd()
		{
			document.getElementById('peopleBldg_1st').style.display = "none";
			document.getElementById('peopleBldg_2nd').style.display = "block";
			document.getElementById('peopleBldg_3rd').style.display = "none";
			
			document.getElementById('peopleBldg1st_trigger').style.fill = "#AAEFFF";
			document.getElementById('peopleBldg2nd_trigger').style.fill = "#C82227";
			document.getElementById('peopleBldg3rd_trigger').style.fill = "#AAEFFF";
			
			document.getElementById('peopleBldg_fog').style.opacity = 0.3;
		}

		function peopleBldg_up3rd()
		{
			document.getElementById('peopleBldg_1st').style.display = "none";
			document.getElementById('peopleBldg_2nd').style.display = "none";
			document.getElementById('peopleBldg_3rd').style.display = "block";
			
			document.getElementById('peopleBldg1st_trigger').style.fill = "#AAEFFF";
			document.getElementById('peopleBldg2nd_trigger').style.fill = "#AAEFFF";
			document.getElementById('peopleBldg3rd_trigger').style.fill = "#C82227";
			
			document.getElementById('peopleBldg_fog').style.opacity = 0.6;
		}

		//Going Down
		function peopleBldg_down2nd()
		{
			document.getElementById('peopleBldg_1st').style.display = "none";
			document.getElementById('peopleBldg_2nd').style.display = "block";
			document.getElementById('peopleBldg_3rd').style.display = "none";
			
			document.getElementById('peopleBldg1st_trigger').style.fill = "#AAEFFF";
			document.getElementById('peopleBldg2nd_trigger').style.fill = "#C82227";
			document.getElementById('peopleBldg3rd_trigger').style.fill = "#AAEFFF";
			
			document.getElementById('peopleBldg_fog').style.opacity = 0.3;
		}

		function peopleBldg_down1st()
		{
			document.getElementById('peopleBldg_1st').style.display = "block";
			document.getElementById('peopleBldg_2nd').style.display = "none";
			document.getElementById('peopleBldg_3rd').style.display = "none";
			
			document.getElementById('peopleBldg1st_trigger').style.fill = "#C82227";
			document.getElementById('peopleBldg2nd_trigger').style.fill = "#AAEFFF";
			document.getElementById('peopleBldg3rd_trigger').style.fill = "#AAEFFF";
			
			document.getElementById('peopleBldg_fog').style.opacity = 0;
		}
		
		function boundary_toggle(class_name, class_status)
        {
            var MunOffice_boundary = document.getElementsByClassName(class_name);
    		for(var i = 0; i < MunOffice_boundary.length; i++)
    		{
    			MunOffice_boundary[i].style.display = class_status;
    		}
        }
}