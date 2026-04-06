<?php
	/*
	*	This is where all the custom functions are.
	*/
	
	//Tag Category  Redirector and Auto Template Creator
	add_action('template_redirect', 'camaligan_tcratc');
	
	if(!function_exists('camaligan_tcratc'))
	{
		function camaligan_tcratc()
		{	
			//Category Archives				
				//Offices Category
				if(is_category())
				{
					$category = get_queried_object();
					$categorySlug = $category->slug;
					$new_url = site_url('/news-and-updates/' .$categorySlug);
					wp_safe_redirect($new_url, 301);
					exit();
				}
								
			//Tag Archives
				//Ordinance Classification - Administrative
				if(is_tag())
				{
					$tag = get_queried_object();
					$tagSlug = $tag->slug;
					$new_url = site_url('/ordinances/' .$tagSlug);
					wp_safe_redirect($new_url, 301);
					exit();
				}
				
				//BAC Bidded Projects Tag - No assigned page yet
				/*else if(is_tag(31))
				{
					$new_url = site_url('/ordinance/social');
					wp_safe_redirect($new_url, 301);
					exit();
				}*/
		}
	}
	
	// Tag Adder to BAC posts
	add_action( 'publish_post', 'add_tag_to_bid_post' );
    
    if(!function_exists('add_tag_to_bid_post'))
    {
        function add_tag_to_bid_post($post_id)
        {   
            $author = get_post_field( 'post_author', $post_id);
            if($author == 17)
            {
                if($parent = wp_is_post_revision($post_id))
                {
                    $post_id = $parent;
                }
                $post = get_post($post_id);
                if($post -> post_type != 'post')
                {
                    return;
                }
                wp_set_post_terms( $post_id, 'for-bid', 'post_tag', false );
    			if(!wp_next_scheduled('tag_change_trigger'))
    			{
    				wp_schedule_single_event( time() + 604800, 'tag_change_trigger', array($post_id));
    			}
            }    
        }
    }

	// For Bid to Previous Tag Scheduled Changer
	add_action( 'tag_change_trigger', 'tag_changer', 10, 1);
	
	if(!function_exists('tag_changer'))
	{
		function tag_changer($post_id)
		{
			wp_set_post_terms( $post_id, 'bidded-projects', 'post_tag', false );
		}
	}

	// Category Enforcer for every Author
	add_action ('publish_post', 'categoryEnforcer', 10, 1);

	if(!function_exists('categoryEnforcer'))
	{
		function categoryEnforcer($post_id)
		{
			
			
			$author = get_post_field( 'post_author', $post_id );
			// MACCO
			if($author == 7)
			{
				wp_set_post_categories( $post_id, array(5));
			}
			// MAESO
			elseif($author == 8)
			{
				wp_set_post_categories( $post_id, array(6));
			}
			// MASSO
			elseif($author == 21)
			{
				wp_set_post_categories( $post_id, array(7));
			}
			// MBO
			elseif($author == 10)
			{
				wp_set_post_categories( $post_id, array(8));
			}
			// MCRO
			elseif($author == 11)
			{
				wp_set_post_categories( $post_id, array(9));
			}
			// CADRRESMO
			elseif($author == 12)
			{
				wp_set_post_categories( $post_id, array(10));
			}
			// MEO
			elseif($author == 13)
			{
				wp_set_post_categories( $post_id, array(11));
			}
			// MHO
			elseif($author == 14)
			{
				wp_set_post_categories( $post_id, array(12));
			}
			// MHRMO
			elseif($author == 22)
			{
				wp_set_post_categories( $post_id, array(13));
			}
			// MO
			elseif($author == 16)
			{
				wp_set_post_categories( $post_id, array(14));
			}
			// MPDC/BAC
			elseif($author == 17)
			{
				wp_set_post_categories( $post_id, array(15));
			}
			// MSWDO
			elseif($author == 18)
			{
				wp_set_post_categories( $post_id, array(16));
			}
			// MTO
			elseif($author == 20)
			{
				wp_set_post_categories( $post_id, array(18));
			}
			// SB
			elseif($author == 19)
			{
				wp_set_post_categories( $post_id, array(17));
			}
		}
	}

	//Temporary Email Requirement Disabler
	// This will suppress empty email errors when submitting the user form
	add_action('user_profile_update_errors', 'my_user_profile_update_errors', 10, 3 );
	
	if(!function_exists('my_user_profile_update_errors'))
	{
		function my_user_profile_update_errors($errors, $update, $user)
		{
			$errors->remove('empty_email');
		}
	}

	// This will remove javascript required validation for email input
	// It will also remove the '(required)' text in the label
	// Works for new user, user profile and edit user forms
	add_action('user_new_form', 'my_user_new_form', 10, 1);
	add_action('show_user_profile', 'my_user_new_form', 10, 1);
	add_action('edit_user_profile', 'my_user_new_form', 10, 1);
	
	if(!function_exists('my_user_new_form'))
	{
		function my_user_new_form($form_type)
		{ ?>
			<script type="text/javascript">
				jQuery('#email').closest('tr').removeClass('form-required').find('.description').remove();
				// Uncheck send new user email option by default
				<?php if (isset($form_type) && $form_type === 'add-new-user') : ?>
					jQuery('#send_user_notification').removeAttr('checked');
				<?php endif; ?>
			</script>
			<?php
		}
	}
	
	
	// Page Template Filter for the Covid 19 Vaccibe Survey
	add_filter( 'page_template', 'survey_c19vaccine' );

    //
    function survey_c19vaccine( $page_template ) {
      if ( is_page( 'covid-vaccine-registration-form' ) && get_permalink() == home_url() . '/surveys/covid-vaccine-registration-form/') {
         $page_template = dirname( __FILE__ ) . '/custom-template/page-covid19survey.php';
      }
      return $page_template;
    }
    
    // Add the styles.css
    function covid19survey_css(){
      wp_enqueue_style( 'covid-19-survey', plugin_dir_url( __FILE__ ) . 'css/covid19survey-styles.css', array(),time() );
    }
    add_action ('wp_enqueue_scripts', 'covid19survey_css');
    
    // Add Tags to Attachments
    function wptp_add_categories_to_attachments() {
        register_taxonomy_for_object_type( 'post_tag', 'attachment' );
    }
    add_action( 'init' , 'wptp_add_categories_to_attachments' );