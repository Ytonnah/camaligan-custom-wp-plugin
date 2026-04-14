<?php 
	/*
	Plugin Name: Camaligan's Custom Functions
	Plugin URI: camaligan.gov.ph
	Description: A simple plugin that holds all the custom function of the Official Website of Camaligan
	Version: 0.6.0
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
	include("widget/wg_tourism_manager/init.php");
	include("widget/annual_report_manager/init.php");
	include("widget/wg_media_gallery/init.php");
	include("widget/wg_beneficiaries_manager/init.php");
	include("widget/budget_overview_manager/init.php");
	include("widget/breadcrumb_manager/init.php");
	include("widget/wg_barangay_manager/init.php");

	include("widget/project_manager/init.php");
	
	
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
		register_widget( 'Breadcrumb_Widget' );
		// Barangay widgets registered in wg_barangay_manager/init.php
	}

	add_action( 'widgets_init', 'camaligan_custom_widget' );
	
	
	// Registering custom CSS and JS
	function register_and_enqueue_custom_styles_and_js()
	{
		//Website Custom CSS
	wp_enqueue_style('ResIndependet-styles', plugin_dir_url(__FILE__) . 'css/ResIndependet-styles.css');
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
			add_submenu_page("camaligan-custom-function", "Tourism Manager", "Tourism Manager", "manage_options", "tourismuploader", "tourismuploader");
			add_submenu_page("camaligan-custom-function", "BAC Manager", "BAC Manager", "manage_options", "bacuploader", "bacuploader");
			add_submenu_page("camaligan-custom-function", "Media Gallery", "Media Gallery", "manage_options", "mediagallery", "mediagallery");
			add_submenu_page("camaligan-custom-function", "Beneficiaries", "Beneficiaries", "manage_options", "beneficiariesmanager", "beneficiariesmanager");
		add_submenu_page("camaligan-custom-function", "Annual Report Manager", "Annual Report Manager", "manage_options", "annualreportmanager", "annualreportmanager");
			// add_submenu_page("camaligan-custom-function", "Annual Report Manager", "Annual Report Manager", "manage_options", "annualreportmanager", "annualreportmanager");
			add_submenu_page("camaligan-custom-function", "Budget Overview Manager", "Budget Overview Manager", "manage_options", "budgetoverviewmanager", "budgetoverviewmanager");
				add_submenu_page("camaligan-custom-function", "Barangay Manager", "Barangay Manager", "manage_options", "barangaymanager", "barangaymanager");
					add_submenu_page("camaligan-custom-function", "Project Manager", "Project Manager", "manage_options", "projectmanager", "projectmanager");
		}

		add_action("admin_menu", "customFunctions_menu");
		function ordinanceuploader()
		{
			include("widget/ordinanceuploader/mainmenu.php");
		}

		function newsuploader()
		{
			require_once("widget/wg_news_manager/init.php");
			include("widget/wg_news_manager/mainmenu.php");
		}

		function tourismuploader()
		{
			require_once("widget/wg_tourism_manager/init.php");
			include("widget/wg_tourism_manager/mainmenu.php");
		}

		function bacuploader()
		{
			require_once("widget/bac_manager/init.php");
			include("widget/bac_manager/mainmenu.php");
		}

		function mediagallery()
		{
			require_once("widget/wg_media_gallery/init.php");
			include("widget/wg_media_gallery/mainmenu.php");
		}

	
		function barangaymanager()
		{
			require_once("widget/wg_barangay_manager/init.php");
			render_barangay_manager_page();
		}

		function beneficiariesmanager()
		{
			require_once("widget/wg_beneficiaries_manager/init.php");
			include("widget/wg_beneficiaries_manager/mainmenu.php");
		}

		function annualreportmanager()
		{
			render_annual_report_manager_page();
		}

		function budgetoverviewmanager()
		{
			render_budget_overview_manager_page();
		}

		function breadcrumbmanager()
		{
			render_breadcrumb_manager_page();
		}

		function projectmanager()
		{
			render_project_manager_page();
		}
		
		function ccffunction()
		{
			?>
				<h1>Camaligan's Custom Functions</h1>
				<h4>Created by Patrick James O. De Leon</h4>
			<?php
		}

		// Display functions for frontend
		function display_beneficiaries_frontend() {
			wp_enqueue_style('beneficiaries-shortcode-style', plugins_url('/camaligan-customization/widget/wg_beneficiaries_manager/css/beneficiaries-shortcode-style.css'));
			echo do_shortcode('[beneficiaries_list]');
		}

		function display_media_gallery_frontend() {
			wp_enqueue_style('media-gallery-shortcode-style', plugins_url('/camaligan-customization/widget/wg_media_gallery/css/media-gallery-shortcode-style.css'));
			echo do_shortcode('[gallery_list]');
		}

		// Register front-end display shortcodes
		add_shortcode('display_beneficiaries', 'display_beneficiaries_frontend');
		add_shortcode('display_media_galleries', 'display_media_gallery_frontend');
?>

