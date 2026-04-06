<?php	
//Widget Code
// Creating the widge

class biddingInteractive_class extends WP_widget
{
	public function __construct() 
	{
        // actual widget processes
		
		parent::__construct
		(		  
			// Base ID of your widget
			'biddingInteractive_class', 
			  
			// Widget name will appear in UI
			__('Interactive Bidding Widget', 'ordinance_domain'), 
			  
			// Widget description
			array( 'description' => __( 'Widget Responsible for displaying the currently bidding projects and previously bidded projects', 'ordinance_domain' ), ) 
		);
    }
 
    public function widget( $args, $instance ) 
	{
        // outputs the content of the widget
		//Codes of the Widget
		/*$BAC = new WP_Query(array(
    		'category_name' => 'mpdc-office',
    		'tag' => 'for-bid',
            'posts_per_page' => 1,
    		'showposts' => 1,
	    ));*/
	
	    /*$bac_posts = new WP_Query(array(
	        'post_type' => 'attachment',
	        'posts_per_page' => 1,
	        'post_status' => 'any',
	        'tax_query' => array(
                array(
                    'taxonomy' => 'post_tag',
                    'field' => 'slug',
                    'terms' => 'bac'
                )
            )
	    ));*/
	    $bac_posts = new WP_Query(array(
	        'post_status' => 'published',
    		'tag' => array( 'bac', 'for-bid', 'bidded-projects',
    		                'goods', 'infrastructure', 'awarded-projects',
    		                'on-going-projects', 'completed-projects' ),
            'posts_per_page' => 10,
            'order' => 'DESC',
	    ));
	
	?>
	<style>
			h3.invitationBid_title
			{
				font-family: 'Squada One', Arial;
				color: white;
				text-align: center;
				background-color: #07035e;
				border: solid;
				border-color: #99a1a3;
				text-shadow: 0px 0px 5px #ffb54d;
				letter-spacing: 5px;
			}
	
			div.bid_holder
			{
				height: 200px;
			}
			div.biddingLogo_Holder
			{
				display: grid;
				width: 100%;
				height: 200px;
				float: left;
				transition: 0.3s;
			}
			
			div.biddingSubcategory_Holder
			{
				display: none;
				width: auto;
				height: 100%;
				margin-left: 10%;
				transition: 0.3s;
			}
			
			svg#BidLogo, svg#currBid, svg#prevBid, svg#BidBack
			{
				display: block;
				max-width: 100%;
				max-height: 100%;
				margin: auto;
				padding: auto;
			}
			
			svg#BidBack
			{
				display: none;
			}
		</style>
		<script>
			function Bid_Trigger()
			{
				var BidLogo_Holder = document.getElementById('BidLogo_Holder');
				var BidSubCat_Holder = document.getElementById('BidSubCat_Holder');
				BidLogo_Holder.style.width = "10%";
				BidSubCat_Holder.style.display = 'grid';
				document.getElementById('BidLogo').style.display = 'none';
				document.getElementById('BidBack').style.display = 'grid';
			}
			
			function Back_Trigger()
			{
				var BidLogo_Holder = document.getElementById('BidLogo_Holder');
				var BidSubCat_Holder = document.getElementById('BidSubCat_Holder');
				BidLogo_Holder.style.width = "100%";
				BidSubCat_Holder.style.display = 'none';
				document.getElementById('BidLogo').style.display = 'grid';
				document.getElementById('BidBack').style.display = 'none';
			}
		</script>
		<h3 class="invitationBid_title"><center>BAC CORNER</center></h3>
		<div class="bid_holder" style="height: auto;">
		    <?php 
		       if($bac_posts->have_posts()) {
    	            while($bac_posts->have_posts()) : $bac_posts->the_post();
    	            ?>
    	                <ul><li><a href="<?php the_permalink(); ?>" xlink:title="<?php the_title(); ?>" target="_blank"><?php the_title(); ?></a></li></ul>
    	            <?php
    	            endwhile;
		       } else {
    	            ?><p style="text-align: center;">No posts yet.</p><?php
		       }
    	   ?>
    	          
		</div>
		
		
		<?php
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