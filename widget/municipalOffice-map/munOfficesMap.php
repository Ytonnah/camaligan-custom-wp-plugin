<?php	
//Widget Code
// Creating the widget

class munOfficesMap_class extends WP_widget
{
	public function __construct() 
	{
        // actual widget processes
		
		parent::__construct
		(		  
			// Base ID of your widget
			'municipalOfficeMap_id', 
			  
			// Widget name will appear in UI
			__('Camaligan Municipal Offices Map Widget', 'municipalOfficeMap_domain'), 
			  
			// Widget description
			array( 'description' => __( 'Widget Responsible for displaying the Interactive Map of Municipal Offices', 'municipalOfficeMap_domain' ), ) 
		);
    }
 
    public function widget( $args, $instance ) 
	{
        // outputs the content of the widget
		//Codes of the Widget
		include('munOfficesMap_ui.php');
	}
    public function form( $instance ) 
	{
        // outputs the options form in the admin
    }
 
    public function update( $new_instance, $old_instance ) 
	{
        // processes widget options to be saved
    }
}
?>