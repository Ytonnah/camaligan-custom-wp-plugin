window.onscroll = function() {myFunction()};

var auxiliary = document.getElementById("auxiliary");
auxiliary.style.zIndex = "99999";
var sticky_aux = auxiliary.offsetTop;
var main_nav = document.getElementById("main-nav");
main_nav.style.zIndex = "999991";
var main_navigation = main_nav.offsetHeight;
var main_content = document.getElementById("main-content");
var NavBarLogo = document.getElementById("logoScroll");

function myFunction() {
	var adminBar = document.getElementById("wpadminbar");
	if(typeof(adminBar) != 'undefined' && adminBar != null)
	{
		// The admin bar exists here
		if (window.pageYOffset > sticky_aux-main_navigation + 20)
		{
			auxiliary.classList.add("stickyAux");
			document.getElementById("auxiliary").style.top=(auxiliary.offsetHeight + adminBar.offsetHeight - 20) + 'px';
			document.getElementById("breadcrumbs").style.marginTop=(auxiliary.offsetHeight + 2) + 'px';
			main_nav.classList.add("mainNav");
			main_nav.classList.remove("mainNav-reverse");
			NavBarLogo.style.opacity="1";
			NavBarLogo.style.display = "block";
			NavBarLogo.style.top="38px";
		}
		else if(window.pageYOffset < sticky_aux - main_navigation + 20)
		{
			auxiliary.classList.remove("stickyAux");
			document.getElementById("breadcrumbs").style.marginTop="0px";
			document.getElementById("auxiliary").style.top="0px";
			if(main_nav.offsetHeight != 58)
			{
				main_nav.classList.add("mainNav-reverse");
			}
			main_nav.classList.remove("mainNav");
			NavBarLogo.style.opacity="0";
			NavBarLogo.style.display = "block";
			NavBarLogo.style.top="25px";
		}
	} 
	
	else
	{
		// The admin bar doesn't exists here
		if (window.pageYOffset > sticky_aux-main_navigation + 16) {
			auxiliary.classList.add("stickyAux");
			document.getElementById("auxiliary").style.top=(auxiliary.offsetHeight - 20) + 'px';
			document.getElementById("breadcrumbs").style.marginTop=(auxiliary.offsetHeight + 2) + 'px';
			main_nav.classList.add("mainNav");
			main_nav.classList.remove("mainNav-reverse");
			NavBarLogo.style.opacity="1";
			NavBarLogo.style.top="5px";
		} 
	  
		else if(window.pageYOffset < sticky_aux - main_navigation + 16)
		{
			auxiliary.classList.remove("stickyAux");
			document.getElementById("breadcrumbs").style.marginTop="0px";
			document.getElementById("auxiliary").style.top="0px";
			if(main_nav.offsetHeight != 58)
			{
				main_nav.classList.add("mainNav-reverse");
			}
			main_nav.classList.remove("mainNav");
			NavBarLogo.style.opacity="0";
			NavBarLogo.style.top="0px";
		}
	}
}