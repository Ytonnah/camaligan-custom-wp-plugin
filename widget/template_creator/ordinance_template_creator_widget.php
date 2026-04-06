<?php
class camaliganOrdinanceTemplateCreator_class extends WP_widget
{
	public function __construct() 
	{
        // actual widget processes
		
		parent::__construct
		(		  
			// Base ID of your widget
			'camaliganOrdinanceTemplateCreator_id', 
			  
			// Widget name will appear in UI
			__('Camaligan Ordinance Auto Template Creator', 'camaliganOrdinanceTemplateCreator_domain'), 
			  
			// Widget description
			array( 'description' => __( 'Widget Responsible for auto creation of template of ordinances' ), ) 
		);
    }
 
    public function widget( $args, $instance ) 
	{
        // outputs the content of the widget
		//Codes of the Widget
		
		$ordinancePage = get_queried_object_id();
		switch ($ordinancePage)
		{
			//Ordinance Main Page
			case 3776:
				$OrdinanceTag = array('administrative', 'development', 'environment', 'health-and-sanitation', 'local-transaction-and-incentives', 'public-utilities', 'social');
				break;
			
			//Administrative Ordinance
			case 3778:
				$OrdinanceTag = 'administrative';
				break;

			//Development Ordinance
			case 3780:
				$OrdinanceTag = 'development';
				break;
				
			//Environment Ordinance
			case 3782:
				$OrdinanceTag = 'environment';
				break;

			//Health and Sanitation Ordinance
			case 3784:
				$OrdinanceTag = 'health-and-sanitation';
				break;
			
			//Local Taxation and Incentives
			case 3786:
				$OrdinanceTag = 'local-taxation-and-incentives';
				break;
			
			//Public Utilities Ordinance
			case 3788:
				$OrdinanceTag = 'public-utilities';
				break;

			//Social Ordiance
			case 3790:
				$OrdinanceTag = 'social';
				break;
		}
		
		$OrdinanceCurrentPage = get_query_var('paged');
			
        	$Ordinance = new WP_Query(array(
        		'tag' => $OrdinanceTag,
				'post_type' => 'attachment',
				'post_mime_type' => 'application/pdf',
				'post_status' => 'inherit',
				'showposts' => 20,
				'post_per_page' => 20,
        		'paged' => $OrdinanceCurrentPage
        ));
		
		
		?><style>
			div#ordinance_holder
			{
				width: 100%;
				height: auto;
				display: block;
				background-color: #07035e;
			}
			
			span#ordinance_title
			{
				font-family: 'Bahnschrift Condensed', Arial;
				color: white;
				text-align: center;
				text-shadow: 0px 0px 5px #ffb54d;		
				font-weight: 900;
				text-decoration: underline;
				font-size: 20px;
			}
			
			tr#ordinance_columnHead > td
			{
				border: solid 2px #6d71ed;
			}
			
			table#ordinance_holder
			{
				word-break: break-word;
				display: table;
				width: 100%;
			}
			
			td#ordinance_nameHead, td#ordinance_classHead, td#ordinance_dateHead, td#ordinance_downloadLinkHead
			{
				background-color: #8589ff;
			}
			
			td#ordinance_nameHead span, td#ordinance_classHead, td#ordinance_dateHead span, td#ordinance_downloadLinkHead span
			{
				font-size: 14px;
				font-weight: 900;
			}
			
			tr#ordinance_columnFiles  td
			{
				background-color: white;
				vertical-align: middle;
				border: solid 2px #6d71ed;
			}
			
			span#ordinance_name, span#ordinance_class, span#ordinance_date, a#ordinance_link
			{
				font-size: 12px;
				font-family: Helvetica, Arial;
				font-weight: 900;
			}
			
			a#ordinance_link:hover
			{
				color: #ffdd00;
				
			}
			
			.administrative > td
			{
				color: #353d00;
			}
			
			.development > td
			{
				color: #011f42;
			}
			
			.environment > td
			{
				color: #1c4d25;
			}
			
			.health-and-sanitation > td
			{
				color: #e62929;
			}
			
			.local-taxation-and-incentives > td
			{
				color: #3c0e40;
			}
			
			.public-utilities > td
			{
				color: #171854;
			}
			
			.social > td
			{
				color: #9c6e28;
			}
			
			#camaligan_dlButtonHolder
			{
				background-color: #D1D3D4;
				padding: 5px;
				border-radius: 10px;
				transition: 0.5s;
			}
			
			#camaligan_dlButtonHolder *
			{
				transition: 0.2s;
			}
			
    			#camaligan_dlButton
    			{
    				min-width: 50px;
    				max-width: 100px;
    				max-height: 30px;
    				
    			}
			
			div#camaligan_dlButtonHolder:hover
			{
				background-color: #5947C9;	 
			}
			
			#camaligan_dlButtonHolder:hover polygon#camaligan_dlButtonArrow
			{
				fill: #CF3B00;
				opacity: 50%;
			}
			
			#camaligan_dlButtonHolder:hover polygon#camaligan_dlButtonPad
			{
				fill: F3C716;
				opacity: 75%;
			}
		</style><?php
		
		
		if($ordinancePage == 3776)
		{?>
				<div id="ordinance_holder" style="text-transform: uppercase;">
					<span id="ordinance_title"><center>ORDINANCES</center></span>
				</div>
		<?php }
		else
		{
			$OrdinanceTag_name = strtoupper(str_replace('-', ' ', $OrdinanceTag	));
			?>
			<div id="ordinance_holder" style="text-transform: uppercase;">
					<span id="ordinance_title"><center><?php echo "$OrdinanceTag_name ";?>ORDINANCES</center></span>
			</div>
		<?php } ?>
		<table id="ordinance_holder">
			<tr id="ordinance_columnHead">
				<td id="ordinance_nameHead">
					<span><center>Name</center></span>
				</td><?php
				if($ordinancePage == 3776)
				{?>
					<td id="ordinance_classHead">
						<span ><center>Ordinance Classification</center></span>
					</td>
				<?php }?>
				<td id="ordinance_dateHead">
					<span><center>Date Created</center></span>
				</td>
				
				<td id="ordinance_downloadLinkHead">
					<span><center>Download Link</center></span>
				</td>
			</tr><?php
				if($Ordinance->have_posts())
				{
					while($Ordinance->have_posts())
					{
						$Ordinance->the_post();
							foreach(get_the_tags() as $tag)
							{	
								$tagName = $tag->slug;
								$className = strtoupper(str_replace('-', ' ', $tagName));
								$class = $tagName;
							}
							?><tr id="ordinance_columnFiles" class="<?php echo $class;?>">
								<td width="40%"><span id="ordinance_name"><?php echo the_title(); ?></span></td>
						
						<?php if($ordinancePage == 3776)
						{?>
							<td><span id="ordinance_class"><center><?php echo "$className"; ?></center></span></td><?php
						}
						?>
							<td><span id="ordinance_date"><center><?php echo get_the_date('F j, Y')	; ?></center></span></td>
						<td><center>
							<div id="camaligan_dlButtonHolder">
								<a id="ordinance_link" href="<?php echo wp_get_attachment_url(); ?>">
									<svg version="1.1" id="camaligan_dlButton" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
									 viewBox="0 0 256 256" style="enable-background:new 0 0 256 256;" xml:space="preserve">
										<style type="text/css">
											.camaligan_dlButton0{fill:#B5292F;stroke:#450000;stroke-miterlimit:10;}
											.camaligan_dlButton1{fill:#1B144F;stroke:#0E023E;stroke-miterlimit:10;}
											.camaligan_dlButton2{fill:#201B57;}
											.camaligan_dlButton3{fill:none;stroke:#242057;stroke-miterlimit:10;}
										</style>
										<polygon id="camaligan_dlButtonPad" class="camaligan_dlButton0" points="211.64,183.66 211.64,215.06 44.36,215.06 44.36,183.66 5.07,183.66 5.07,215.06 5.07,215.06 5.07,251 
											5.07,251 44.36,251 211.64,251 250.93,251 250.93,251 250.93,183.66 "/>
										<polygon id="camaligan_dlButtonArrow" class="camaligan_dlButton1" points="196.82,107.23 151.73,107.23 151.73,5.74 124.1,5.74 123.76,5.74 96.13,5.74 96.13,107.23 
											51.04,107.23 123.76,197.57 123.93,197.78 124.1,198 "/>
										<polygon class="camaligan_dlButton2" points="109.64,179.21 109.64,6.45 99.43,6.45 99.43,166.64 "/>
										<polygon class="camaligan_dlButton2" points="148.66,166.63 148.66,6.45 138.45,6.45 138.45,179.21 "/>
										<polygon class="camaligan_dlButton2" points="129.38,92.83 118.7,92.83 118.7,190.73 124.04,197.31 129.38,190.73 "/>
										<g>
											<polyline class="camaligan_dlButton3" points="92.25,158.43 92.25,143.47 87.81,138.19 87.81,133.77 93.24,128.33 93.24,118.79 95.64,118.79 	"/>
											<polyline class="camaligan_dlButton3" points="87.81,133.77 84.23,127.3 79.64,127.3 72.23,119.89 72.23,110.79 74.4,108.62 	"/>
											<polyline class="camaligan_dlButton3" points="72.23,119.89 66.7,119.89 63.89,123.19 	"/>
											<polyline class="camaligan_dlButton3" points="82.7,127.3 82.7,119.89 84.74,117.6 84.74,113.17 	"/>
											<polyline class="camaligan_dlButton3" points="82.7,146.56 82.7,138.61 78.79,138.61 73.34,131.55 	"/>
											<polyline class="camaligan_dlButton3" points="81,139.06 81,132.23 83.72,132.23 	"/>
										</g>
										<g>
											<polyline class="camaligan_dlButton3" points="155.66,158.67 155.66,143.71 160.1,138.43 160.1,134.01 154.67,128.57 154.67,119.03 152.27,119.03 	
												"/>
											<polyline class="camaligan_dlButton3" points="160.1,134.01 163.68,127.54 168.27,127.54 175.68,120.14 175.68,111.03 173.51,108.86 	"/>
											<polyline class="camaligan_dlButton3" points="175.68,120.14 181.21,120.14 184.03,123.43 	"/>
											<polyline class="camaligan_dlButton3" points="165.21,127.54 165.21,120.14 163.17,117.84 163.17,113.41 	"/>
											<polyline class="camaligan_dlButton3" points="165.21,146.81 165.21,138.85 169.13,138.85 174.57,131.8 	"/>
											<polyline class="camaligan_dlButton3" points="166.91,139.3 166.91,132.48 164.19,132.48 	"/>
										</g>
									</svg>
								</a>
							</div>
						</center></td></tr><?php
					} ?></table><?php
					
					wp_pagenavi( array( 'query' => $Ordinance ) ); 
					wp_reset_postdata();
				}
     }
    public function form( $instance ) 
	{
        // outputs the options form in the admin
    }
 
    public function update( $new_instance, $old_instance ) 
	{
        // processes widget options to be saved
    }
}?>