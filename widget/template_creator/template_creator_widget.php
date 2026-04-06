<?php /* */
class camaliganTemplateCreator_class extends WP_widget
{
	public function __construct() 
	{
        // actual widget processes
		
		parent::__construct
		(		  
			// Base ID of your widget
			'camaliganTemplateCreator_id', 
			  
			// Widget name will appear in UI
			__('Camaligan Auto Template Creator', 'TemplateCreator_domain'), 
			  
			// Widget description
			array( 'description' => __( 'Widget Responsible for auto creation of template of Announcement Pages' ), ) 
		);
    }
 
    public function widget( $args, $instance ) 
	{
        // outputs the content of the widget
		//Codes of the Widget
		if(!function_exists('categoryPage_templateCreator'))
		{
			function categoryPage_templateCreator($page, $cat_slug, $cat_name)
			{
				if(is_page(93))
				{
					$currentPage = get_query_var('paged');
					
					$categoryQuery = new WP_Query(array(
						'posts_per_page' => 10,
						'showposts' => 10,
						'paged' => $currentPage,
					));
					
					if($categoryQuery->have_posts()) :
						while($categoryQuery->have_posts()) :
							$categoryQuery->the_post();
							
							camaliganTemplate_structure();
						endwhile;
						wp_pagenavi(array('query' => $categoryQuery));
						wp_reset_query();
					else : ?><p class="office-no-post-text">Sorry. No Office has posted an Announcement Yet.</p><?php 
					endif;
				}
				else
				{
					$currentPage = get_query_var('paged');
					
					$categoryQuery = new WP_Query(array(
						'category_name' =>  $cat_slug,
						'posts_per_page' => 5,
						'showposts' => 5,
						'paged' => $currentPage,
					));
					
					if($categoryQuery->have_posts()) :
						while($categoryQuery->have_posts()) :
							$categoryQuery->the_post();
							camaliganTemplate_structure();
						endwhile;
						wp_pagenavi(array('query' => $categoryQuery));
						wp_reset_query();
					else :
					    error_log($page);
					    ?><p class="office-no-post-text"><?php echo $cat_name;?> does not have any Announcement to post Yet</p><?php 
					endif;
				}
			}
		}
		
		if(!function_exists('camaliganTemplate_structure'))
		{
			function camaliganTemplate_structure()
			{?>
				<div class="post-box">
					<article id="post-<?php the_ID(); ?>" <?php post_class('callout secondary'); ?>>
						
						<?php
							$content_class = 'large-12';
							if(has_post_thumbnail()) : 
								$content_class = 'large-9';
								the_post_thumbnail( 'thumbnail', array( 'class' => 'thumbnail') );
							else : 
								$content_class = 'large-9'; 
								foreach((get_the_category()) as $category) {
								  $catname =$category->slug;
								  $catName =$category->name;
								if(is_page(93)){?>
									<img src="<?php bloginfo('stylesheet_directory'); ?>/template-parts/default-thumbnails/<?php echo $catname; ?>.png" alt="<?php echo $catname; ?>-Thumbnail" class="thumbnail" sizes="(max-width: 150px) 100vw, 150px" width="150px" height="150px" margin="auto"/>
								<?php 
									$content_class = 'large-8';
								}
								else
								{
									$content_class = 'small-12';
								}}
							endif;

							
						?>
						
						<div class="entry-wrapper <?php echo $content_class; ?> medium-12 small-12">
							<!-- entry-header -->
							<header class="entry-header"><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
								<h2 class="entry-title"><a style="color:#000630; font-family:'Bahnschrift Condensed', 'Times New Roman', Arial; font-weight: 900; text-decoration: underline; word-break: break-word;" onMouseOver="this.style.color='#0f77a3'" onMouseOut="this.style.color='#000630'"  href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
								
								<?php if ( 'post' == get_post_type() ) : ?>
									<div class="entry-meta" style="font-weight: 700">
										<?php gwt_wp_posted_on(); ?>
										<span>by </span> <a href="<?php echo $catname; ?>"><?php echo $catName; ?></a>
									</div>
								<?php endif; ?>
							</header>

						
							<!-- entry-summary entry-content -->
							<?php if ( is_search() ) : // Only display Excerpts for Search ?>
								<div class="entry-summary">
									<?php echo get_the_excerpt(); ?>
								</div>
							<?php else : ?>
								<div class="entry-content">
									<?php echo get_the_excerpt(); ?>
								</div>
							<?php endif; ?>
							
							
							<!-- footer entry-meta -->
							<footer class="entry-meta">
								<?php if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search ?>
								<?php endif; ?>
							</footer>
						</div>
					</article>
				</div><?php
			}
		}
		
		//All Office Category
		if(is_page())
		{
			$page = get_queried_object_id();
			switch ($page)
			{
				case 93:
					$cat_slug = 'mainpost-office';
					$cat_name = 'Main Post Office';
					break;
					
				case 2280:
					$cat_slug = 'accounting-office';
					$cat_name = 'Accounting Office';
					break;
					
				case 2467:
					$cat_slug = 'agriculture-office';
					$cat_name = 'Agriculture Office';
					break;
					
				case 2469:
					$cat_slug = 'assessor-office';
					$cat_name = 'Assessor Office';
					break;
					
				case 2471:
					$cat_slug = 'budget-office';
					$cat_name = 'Budget Office';
					break;
					
				case 2473:
					$cat_slug = 'civilregistrar-office';
					$cat_name = 'Civil Registrar Office';
					break;
					
				case 2475:
					$cat_slug = 'drrm-office';
					$cat_name = 'CADRRESM Office';
					break;
					
				case 2477:
					$cat_slug = 'engineer-office';
					$cat_name = 'Engineer Office';
					break;
				
				case 2479:
					$cat_slug = 'health-office';
					$cat_name = 'Health Office';
					break;
				
				case 2481:
					$cat_slug = 'hr-office';
					$cat_name = 'HR Office';
					break;
				
				case 2483:
					$cat_slug = 'mayor-office';
					$cat_name = 'Mayor Office';
					break;
				
				case 2485:
					$cat_slug = 'mpdc-office';
					$cat_name = 'MPDC Office';
					break;
				
				case 2487:
					$cat_slug = 'mswd-office';
					$cat_name = 'MSWD Office';
					break;
				
				case 2489:
					$cat_slug = 'treasurer-office';
					$cat_name = 'Treasurer Office';
					break;
				
				case 2491:
					$cat_slug = 'sb-office';
					$cat_name = 'SB Office';
					break;
			}
			categoryPage_templateCreator($page, $cat_slug, $cat_name);
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