<?php 
	/*
	Plugin Name: Camaligan's Custom Functions
	Plugin URI: camaligan.gov.ph
	Description: A simple plugin that holds all the custom function of the Official Website of Camaligan
	Version: 0.5.1
	Author: Patrick James O. De Leon
	Author URI: patrickjamesdeleon@gmail.com
	*/
	
	//Adding a php file that is composed of custom functions for the Website of Camaligan
	include("custom-functions.php");
	
	
	//Adding Widget php files
	include("widget/ordinanceuploader/ordinance_widget.php");
	include("widget/template_creator/template_creator_widget.php");
	include("widget/template_creator/ordinance_template_creator_widget.php");
	include("widget/bid-Interactive/bid-interactive-widget.php");
	include("widget/municipalOffice-map/munOfficesMap.php");
	include("widget/municipalLand-map/munLandMap.php");
	include("widget/municipal_logo/municipal_logo.php");
	include("widget/office_banner/office_banner.php");
	include("widget/wg_news_manager/init.php");
	include("widget/bac_manager/init.php");
	include("widget/annual_report_manager/init.php");
	
	
	//Registering All Custom Widgets
	/*Registering all the custom Widgets*/
	function camaligan_custom_widget() 
	{
		register_widget( 'camaliganOrdinance_class' );
		register_widget( 'camaliganTemplateCreator_class' );
		register_widget( 'camaliganOrdinanceTemplateCreator_class' );
		register_widget( 'biddingInteractive_class' );
		register_widget( 'munOfficesMap_class' );
		register_widget( 'munLandMap_class' );
		register_widget( 'munlogo_class' );
		register_widget( 'camaliganOfficeBanner_class' );
	}

	add_action( 'widgets_init', 'camaligan_custom_widget' );
	
	
	// Registering custom CSS and JS
	function register_and_enqueue_custom_styles_and_js()
	{
		//Website Custom CSS
		wp_enqueue_style('ResIndependet-styles', plugins_url('/camaligan-customization/css/ResIndependet-styles.css'));
		wp_enqueue_style('custom-styles-DesktopandTablet', plugins_url('/camaligan-customization/css/custom-styles-DesktopandTablet.css'));
		wp_enqueue_style('custom-styles-mobile', plugins_url('/camaligan-customization/css/custom-styles-mobile.css'));
		wp_enqueue_style('camaligan-ordinance-style', plugins_url('camaligan-customization\widget\ordinanceuploader\ordinance_style.css'));
		
	    //Municipal Map Css and Script
	    wp_enqueue_style('interactive-municipalMap', plugins_url('/camaligan-customization/widget/municipalOffice-map/munOfficesMap_style.css'));
		wp_enqueue_script('interactive-municipalMap', plugins_url('/camaligan-customization/widget/municipalOffice-map/munOfficesMap_script.js'), '', false,true);
		
		 //Municipal Land Map Css and Script
	    wp_enqueue_style('interactive-municipalLandMap', plugins_url('/camaligan-customization/widget/municipalLand-map/munLandMap_style.css'));
		wp_enqueue_script('interactive-municipalLandMap', plugins_url('/camaligan-customization/widget/municipalLand-map/munLandMap_script.js'), '', false,true);
		
		//Municipal Logo
	    wp_enqueue_style('mun-logo', plugins_url('/camaligan-customization/widget/municipal_logo/municipal_logo.css'));
		
		//Banner Scroll Effect Invisible Widget CSS and JS
		wp_enqueue_style('banner-scroll-effect', plugins_url('/camaligan-customization/widget/banner-scroll-effect/banner-logo-effect.css'));
		wp_enqueue_script('banner-scroll-effect', plugins_url('/camaligan-customization/widget/banner-scroll-effect/banner-scroll-effect.js'), '', false,true);
		?>
		<img src="<?php echo plugins_url('/camaligan-customization/widget/banner-scroll-effect');?>/logo.png" class="logoScroll" id="logoScroll">
	<?php 
	}

	add_action('wp_enqueue_scripts', 'register_and_enqueue_custom_styles_and_js');

/* Menu in Admin Dashboard */
	//Adding Menu Tab for Ordinance Uploader
	function customFunctions_menu() 
		{
			add_menu_page("Camaligan's Custom Functions", "Camaligan's Custom Functions","manage_options", "camaligan-custom-function", "ccffunction", plugins_url('/camaligan-customization/icon.png'));
			add_submenu_page("camaligan-custom-function", "Ordinance Manager", "Ordinance Manager", "manage_options", "ordinanceuploader", "ordinanceuploader");		  
		
			add_submenu_page("camaligan-custom-function", "News Manager", "News Manager", "manage_options", "newsuploader", "newsuploader");
			add_submenu_page("camaligan-custom-function", "Annual Report Manager", "Annual Report Manager", "manage_options", "annualreportmanager", "annualreportmanager");
		}

		add_action("admin_menu", "customFunctions_menu");

		function ordinanceuploader()
		{
			include("widget/ordinanceuploader/mainmenu.php");
		}

		function newsuploader()
		{
			include("widget/wg_news_manager/init.php");
		}

		function annualreportmanager()
		{
			render_annual_report_manager_page();
		}
		
		function ccffunction()
		{
			?>
				<h1>Camaligan's Custom Functions</h1>
				<h4>Created by Patrick James O. De Leon</h4>
			<?php
		}
?>
