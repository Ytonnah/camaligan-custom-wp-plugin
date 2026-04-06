<?php	
//Widget Code
// Creating the widge

class camaliganOrdinance_class extends WP_widget
{
	public function __construct() 
	{
        // actual widget processes
		
		parent::__construct
		(		  
			// Base ID of your widget
			'camaliganOrdinance_id', 
			  
			// Widget name will appear in UI
			__('Ordinance Manager', 'ordinance_domain'), 
			  
			// Widget description
			array( 'description' => __( 'Widget Responsible for displaying all the ordinance files uploaded through the Ordinance Uploader Plugin', 'ordinance_domain' ), ) 
		);
    }
 
    public function widget( $args, $instance ) 
	{
        // outputs the content of the widget
		//Codes of the Widget
		$Post_shown = 5; //Number of Ordinance File Link per classification shown in Desktop/ Tablet Mode 
		$Post_shown_m = 2; //Number of Ordinance File Link per classification shown in Mobile Mode

		if(!function_exists('FileCounter'))
		{
			function FileCounter($tag, $PostLimit)
			{
				$fileCount = 0;
				$resultingCount = "";
				$Counter = new WP_QUERY(array(
					'tag' => $tag,
					'post_type' => 'attachment',
					'post_mime_type' => 'application/pdf',
					'post_status' => 'inherit',
					'showposts' => -1,
					'post_per_page' => -1,	
				));
				$pagelink = site_url() .'/ordinances/' .$tag;
				
				if($Counter->have_posts())
				{
					while($Counter->have_posts())
					{
						$Counter->the_post();
						$fileCount++;
					}
				}
				$tagName = ucwords(str_replace('-', ' ', $tag), ' ');
				wp_reset_postdata();
				if($fileCount == 1)
				{
					$resultingCount .= '<span>'
						.'<center>' .'<br>' .'That is the only uploaded ' .$tag .' Ordinance.' .'</center>'
					.'</span>';
				}
				else if($fileCount <= $PostLimit && $fileCount > 1)
				{
					$resultingCount .= '<span>'
						.'<center>' .'<br>' .'These are all of the uploaded ' .$tagName .' Ordinances.' .'</center>'
					.'</span>';
				}
				else if($fileCount >= $PostLimit)
				{
					$unviewedPosts = $fileCount - $PostLimit;
					$resultingCount .= '<span>'
						.'<center>' .'There are ' .$unviewedPosts .' ' .$tagName .' Ordinances not shown here.' .'</center>'
					.'</span>'
					.'<a href="' . $pagelink . '">'
							.'<center><u>' .'Click here to View All' . '</u></center>'
					.'</a>';
				}
				return $resultingCount;
			}
		}

		if(!function_exists('postSearcher'))
		{
			function postSearcher($tag, $postCount)
			{
				$resultingPosts = "";
				$zeroPosts = "Nothing Uploaded in Here Yet";
				$TagPosts = new WP_QUERY(array(
					'tag' => $tag,
					'post_type' => 'attachment',
					'showposts' => $postCount,
					'post_per_page' => $postCount,
					'post_mime_type' => 'application/pdf',
					'orderby' => 'date',
					'order' => 'DESC',
					'suppress_filters' => true,
					'post_status' => 'inherit',
				));
				
				if($TagPosts->have_posts())
				{
					while($TagPosts->have_posts())
					{
						$TagPosts->the_post();
						$post_id = $TagPosts->ID;
						$resultingPosts .= '<ul>'
							.'<li>'
								.'<a href="' . wp_get_attachment_url($post_id) . '"><div>' . get_the_title() . '</div></a>'
							.'</li>'
						.'</ul>';
					}
					wp_reset_postdata();
				}
				else
				{
					$resultingPosts .= '<span>'
					.'<center>' . $zeroPosts . '</center>'
					.'</span>';
				}
				return $resultingPosts;
			}
		}
		
		//Widget Front End?>
		<div id="ordinance_holder">
			<span id="ordinance_title"><center>MUNICIPAL ORDINANCES</center></span>
		</div>
			
		<table id="ordinance_organizer">
			<tr id="classification">
				<td id="administrative"><span>ADMINISTRATIVE</span></td>
				
				<td id="development"><span>DEVELOPMENT</span></td>
				
				<td id="environment"><span>ENVIRONMENT</span></td>
				
				<td id="health_sanitation"><span>HEALTH AND SANITATION</span></td>
				
				<td id="lit"><span>LOCAL TAXATION AND INCENTIVES</span></td>
				
				<td id="public_utilities"><span>PUBLIC UTILITIES</span></td>
				
				<td id="social"><span>SOCIAL</span></td>
			</tr>
			<tr id="class_files">
				<td id="administrative_files">
					<?php 
						echo postSearcher('administrative', $Post_shown);
						echo FileCounter('administrative', $Post_shown);
					?>
				</td>
				
				<td id="development_files">
					<?php
						echo postSearcher('development', $Post_shown);
						echo FileCounter('development', $Post_shown);
					?>
				</td>
				
				<td id="environment_files">
					<?php
						echo postSearcher('environment', $Post_shown);
						echo FileCounter('environment', $Post_shown);
					?>
				</td>
				
				<td id="health_sanitation_files">
					<?php
						echo postSearcher('health-and-sanitation', $Post_shown);
						echo FileCounter('health-and-sanitation', $Post_shown);
					?>
				</td>
				
				<td id="lit_files">
					<?php
						echo postSearcher('local-taxation-and-incentives', $Post_shown);
						echo FileCounter('local-taxation-and-incentives', $Post_shown);
					?>
				</td>
				
				<td id="public_utilities_files">
					<?php
						echo postSearcher('public-utilities', $Post_shown);
						echo FileCounter('public-utilities', $Post_shown);
					?>
				</td>
				
				<td id="social_files">
					<?php
						echo postSearcher('social', $Post_shown);
						echo FileCounter('social', $Post_shown);
					?>
				</td>
			</tr>
		</table>

		<table id="ordinance_organizer_mobile">
			<tr id="administrative_mobile">
				<td id="administrative_title_m"><span>ADMINISTRATIVE</span></td>
				
				<td id="administrative_files_m">
					<?php
						echo postSearcher('administrative', $Post_shown_m);
						echo FileCounter('administrative', $Post_shown_m);
					?>
				</td>
			</tr>

			<tr id="development_mobile">
				<td id="development_title_m"><span>DEVELOPMENT</span></td>
				
				<td id="development_files_m">
					<?php
						echo postSearcher('development', $Post_shown_m);
						echo FileCounter('development', $Post_shown_m);
					?>
				</td>
			</tr>
			
			<tr id="environment_mobile">
				<td id="environment_title_m"><span>ENVIRONMENT</span></td>
				
				<td id="environment_files_m">
					<?php
						echo postSearcher('environment', $Post_shown_m);
						echo FileCounter('environment', $Post_shown_m);
					?>
				</td>
			</tr>
			
			<tr id="health_sanitation_mobile">
				<td id="health_sanitation_title_m"><span>HEALTH AND SANITATION</span></td>
				
				<td id="health_sanitation_files_m">
					<?php
						echo postSearcher('health-and-sanitation', $Post_shown_m);
						echo FileCounter('health-and-sanitation', $Post_shown_m);
					?>
				</td>
			</tr>
			
			<tr id="lit_mobile">
				<td id="lit_title_m"><span>LOCAL TAXATION AND INCENTIVES</span></td>
				<td id="lit_files_m">
					<?php
						echo postSearcher('local-taxation-and-incentives', $Post_shown_m);
						echo FileCounter('local-taxation-and-incentives', $Post_shown_m);
					?>
				</td>
			</tr>
			
			<tr id="public_utilities_mobile">
				<td id="public_utilities_title_m"><span>PUBLIC UTILITIES</span></td>
				
				<td id="public_utilities_files_m">
					<?php
						echo postSearcher('public-utilities', $Post_shown_m);
						echo FileCounter('public-utilities', $Post_shown_m);
					?>
				</td>
			</tr>
			
			<tr id="social_mobile">
				<td id="social_title_m"><span>SOCIAL</span></td>
				
				<td id="social_files_m">
					<?php
						echo postSearcher('social', $Post_shown_m);
						echo FileCounter('social', $Post_shown_m);
					?>
				</td>
			</tr>
		</table>
		<div id="showAll_mainHolder"><center><a id="showAll_OrdinanceLink" href="<?php echo site_url() .'/ordinances'; ?>">CLICK HERE TO SHOW ALL OF THE ORDINANCES</center></a></div>
		</table>
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