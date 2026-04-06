<?php
/*
** Created By: Patrick James O. De Leon
** Website Developer of LGU Camaligan, Camarines Sur
*/
?>
<style>
	div#ordinance_tab_holder
	{
		background: #0479bd!important;		
		border-top-left-radius: 5px;
		border-top-right-radius: 5px;
		padding-left: 5px;
		margin-right: 20px;
	}
	
	button.ordinance_tab
	{
		width: 150px;
		height: 50px;
		font-family: 'Bahnschrift', Arial, Helvetica;
		font-size: 16px;
		border: none;
		border-radius: 5px;
		outline: none;
	}
	
	.ordinance_buttonActive
	{
		background-color: white;
		font-weight: 900;
		color: black;
	}
	
	.ordinance_buttonInactive
	{
		background-color: #5f6366!important;
		color: white;
		cursor: pointer;
	}
	
	.ordinance_buttonInactive:hover
	{
		background-color: yellow!important;
	}
	
	div#ordinanceUploader_Holder
	{
		display: block;
		padding-left: 20px;
		border: solid 2px #0479bd;
		border-top-color: white;
		border-bottom-left-radius: 5px;
		border-bottom-right-radius: 5px;
		margin-right: 20px;
		background-color: white!important;
		padding-bottom: 25px;
	}
	
	div#ordinanceViewer_Holder
	{
		display: none;
		padding-left: 20px;
		border: solid 2px #0479bd;
		border-top-color: white;
		border-bottom-left-radius: 5px;
		border-bottom-right-radius: 5px;
		margin-right: 20px;
		background-color: white!important;
		padding-bottom: 25px;
	}
</style>

<script>
	function ordinance_upload()
	{
		var ordinanceUploadButton = document.getElementById("upload_tab");
		var ordinanceViewallButton = document.getElementById("viewAll_tab");
		ordinanceUploadButton.classList.add("ordinance_buttonActive");
		ordinanceUploadButton.classList.remove("ordinance_buttonInactive");
		ordinanceViewallButton.classList.add("ordinance_buttonInactive");
		ordinanceViewallButton.classList.remove("ordinance_buttonActive");
		document.getElementById("ordinanceUploader_Holder").style.display = 'block';
		document.getElementById("ordinanceViewer_Holder").style.display = 'none';
	}
	
	function ordinance_view()
	{
		var ordinanceUploadButton = document.getElementById("upload_tab");
		var ordinanceViewallButton = document.getElementById("viewAll_tab");
		ordinanceUploadButton.classList.add("ordinance_buttonInactive");
		ordinanceUploadButton.classList.remove("ordinance_buttonActive");
		ordinanceViewallButton.classList.add("ordinance_buttonActive");
		ordinanceViewallButton.classList.remove("ordinance_buttonInactive");
		document.getElementById("ordinanceUploader_Holder").style.display = 'none';
		document.getElementById("ordinanceViewer_Holder").style.display = 'block';
	}
</script>

<div id="ordinance_tab_holder">
	<button class="ordinance_tab ordinance_buttonActive" id="upload_tab" onclick="ordinance_upload()">Upload Ordinance</button>
	<button class="ordinance_tab ordinance_buttonInactive" id="viewAll_tab" onclick="ordinance_view()">View all Ordinance</button>
</div>

<div id="ordinanceUploader_Holder">
	<?php include("ordinanceuploader.php"); ?>
</div>

<style>
    #ordinanceViewer_Holder
    {
        padding-top: 20px;
    }
    
    form#ord_searchField >tr > td
    {
        vertical-align: middle;
    }
    
    #ord_searchOrb
    {
        transition: 1s;
    }

    #ord_searchInput
    {
        background-color: #b6dbdb;
        width: 200px;
        border: none;
        border-radius: 20px;
        font-family: Bahnschrift, "Times New Roman", "Sans Serif";
        font-size: 15px;
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.5s;
    }
    
    #ord_searchGear
    {
        transition: opacity 0.25s;
        opacity: 0;
    }
    
    .ord_searchAnimate
    {
        transform-origin: 50% 50%;
        animation: ordsearch_Animate 1s forwards;
    }
    
    @keyframes ordsearch_Animate
    {
        from{
            transform: rotate(0deg);
        }
        
        to{
            transform: rotate(320deg);
        }
    }
    
    #ord_searchButton
    {
        height: 60px;
        width: 60px;
        padding: 0;
        margin: 0;
        margin-left: -135px;
        background-color: white;
        border-radius: 50px;
        border: none;
        outline: none;
        transition: margin-left 1s;
    }
    
    #ord_searchButton > svg
    {
        cursor: pointer;
    }
    
    .ord_searchLogo0{fill:#262262; transition: 0.5s;}
	.ord_searchLogo1{fill:#1B75BC; opacity: 0; transition: 0.5s;}
	.ord_searchLogo2{fill:#009444; transition: 0.5s;}
	.ord_searchLogo3{fill:#2BB673; transition: 0.5s;}
	.ord_searchLogo4{fill:#FFF200; opacity: 0; transition: 0.5s;}
</style>

<script>
    function ord_searchAnimation()
    {
        var ord_searchInput = document.getElementById('ord_searchInput');
        var ord_searchGear = document.getElementById('ord_searchGear');
        var ord_searchMagnifier = document.getElementById('ord_searchMagnifier');
        var ord_searchOrb = document.getElementById('ord_searchOrb');
        var ord_searchButton = document.getElementById('ord_searchButton');
        if(ord_searchButton.offsetLeft <= -129)
        {
            ord_searchButton.disabled = true;
        }
        
        ord_searchInput.style.opacity = 1;
        ord_searchButton.style.marginLeft = "-30px";
        ord_searchInput.style.visibility = "visible";
        ord_searchGear.style.opacity = 1;
        ord_searchGear.classList.add("ord_searchAnimate");
        ord_searchMagnifier.classList.add("ord_searchAnimate");
        ord_searchOrb.style.fill = "#18ba7a";
    }
    
    function ord_searchTrigger()
    {
        var ord_searchInput = document.getElementById('ord_searchInput');
        var ord_searchButton = document.getElementById('ord_searchButton');
        console.log(ord_searchButton.offsetLeft);
        if(ord_searchButton.offsetLeft >= -30)
        {
            ord_searchButton.disabled = false;
        }
        console.log(ord_searchInput.value);
    }
    
    function ord_searchNow()
    {
        var ord_searchInput = document.getElementById('ord_searchInput');
        if(ord_searchInput.value != "")
        {
            ord_searchButton.disabled = false;
        }
        else
        {
            ord_searchButton.disabled = true;
        }
    }
</script>

<div id="ordinanceViewer_Holder">
    <?php include("ordinanceviewer.php"); ?>
</div>