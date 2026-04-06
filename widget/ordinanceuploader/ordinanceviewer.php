<?php
    $OrdinanceTag = array('administrative', 'development', 'environment', 'health-and-sanitation', 'local-transaction-and-incentives', 'public-utilities', 'social');
    $post_shown = 20;
    
    if(!function_exists('postSearcher'))
    {
    	function postSearcher()
    	{
    	    $resultingPosts = "";
    	    
    	    $OrdinanceCurrentPage = get_query_var('paged');
        	$Ordinance = new WP_Query(array(
        		'tag' => $OrdinanceTag,
				'post_type' => 'attachment',
				'post_mime_type' => 'application/pdf',
				'post_status' => 'inherit',
				'orderby' => 'date',
				'order' => 'DESC',
				'showposts' => 20,
				'post_per_page' => 20,
        		'paged' => $OrdinanceCurrentPage
        ));
    		$zeroPosts = "Nothing Uploaded in Here Yet";
    		
    		if($Ordinance->have_posts())
    		{
    			while($Ordinance->have_posts())
    			{
    				$Ordinance->the_post();
    				foreach(get_the_tags() as $tag)
						{	
							$tagName = $tag->slug;
							$className = strtoupper(substr($tagName, 0, 1)) . strtolower(str_replace('-', ' ', substr($tagName, 1)));
							$class = $tagName;
						}
    				if($tag->slug != "")
    			    {
        				$post_id = $Ordinance->ID;
        				$resultingPosts .= '<tr><td><input type="checkbox" name="ord_del[]" values="' .wp_get_attachment_url($post_id)  . '"></td>
        				<td><a href = "' .wp_get_attachment_url($post_id) . '"><span class="ord_filename">' . get_the_title() . '</span></a></td>
        				<td><span style="text-align:center;">' . get_the_date('F j, Y') . '</span></td>
        				<td><span>' . $className . '</span></td></tr>';
    			    }
    			}
    			wp_pagenavi( array( 'query' => $Ordinance ) ); 
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
?>

<form id="ord_searchField" action="">
        <table >
            <tr>
                <td style="z-index: 98;">
                    <input type="text" id="ord_searchInput" onMouseDown="ord_searchTrigger();" placeholder="Search for an Ordinance" name="ord_search"/>
                </td>
                <td style="z-index: 99;">
                    <button type="submit" class="ord_searchButton" id="ord_searchButton" onmouseover="ord_searchAnimation();" onmousedown="ord_searchNow();">
                        <svg version="1.1" id="ord_searchlogo" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                    	 viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
                            <circle class="ord_searchLogo0" id="ord_searchOrb" cx="256" cy="256" r="256"/>
                            <path class="ord_searchLogo1" id="ord_searchGear" d="M474.2,284.5v-57H444c-2.1-14.2-5.8-27.9-10.9-40.9l26.1-15.1l-28.5-49.3l-26.1,15.1
                            	c-8.8-11-18.9-21.1-29.9-29.9l15.1-26.1l-49.3-28.5l-15.1,26.1c-13-5.1-26.7-8.8-40.9-10.9V37.8h-57V68
                            	c-14.2,2.1-27.9,5.8-40.9,10.9l-15.1-26.1l-49.3,28.5l15.1,26.1c-11,8.8-21.1,18.9-29.9,29.9l-26.1-15.1l-28.5,49.3l26.1,15.1
                            	c-5.1,13-8.8,26.7-10.9,40.9H37.8v57H68c2.1,14.2,5.8,27.9,10.9,40.9l-26.1,15.1l28.5,49.3l26.1-15.1c8.8,11,18.9,21.1,29.9,29.9
                            	l-15.1,26.1l49.3,28.5l15.1-26.1c13,5.1,26.7,8.8,40.9,10.9v30.2h57V444c14.2-2.1,27.9-5.8,40.9-10.9l15.1,26.1l49.3-28.5
                            	l-15.1-26.1c11-8.8,21.1-18.9,29.9-29.9l26.1,15.1l28.5-49.3l-26.1-15.1c5.1-13,8.8-26.7,10.9-40.9H474.2z M256,422
                            	c-91.7,0-166-74.3-166-166S164.3,90,256,90s166,74.3,166,166S347.7,422,256,422z"/>
                            <g id="ord_searchMagnifier">    
                                <path class="ord_searchLogo2"  d="M347.8,205.7c0-50.7-41.1-91.8-91.8-91.8s-91.8,41.1-91.8,91.8c0,44.3,31.3,81.2,73,89.8v82.9
                                	c0,6.1,4.9,11,11,11h15.5c6.1,0,11-4.9,11-11v-82.9C316.4,286.9,347.8,250,347.8,205.7z M256,275.8c-38.7,0-70-31.3-70-70
                                	c0-38.7,31.3-70,70-70s70,31.3,70,70C326,244.4,294.7,275.8,256,275.8z"/>
                                <path class="ord_searchLogo3" d="M338.2,205.7c0-45.4-36.8-82.2-82.2-82.2s-82.2,36.8-82.2,82.2c0,41.6,31,76,71.2,81.4v90.3c0,6.1,4.9,11,11,11
                                	s11-4.9,11-11v-90.3C307.2,281.8,338.2,247.4,338.2,205.7z M256,268.4c-34.6,0-62.7-28.1-62.7-62.7c0-34.6,28.1-62.7,62.7-62.7
                                	s62.7,28.1,62.7,62.7C318.7,240.4,290.6,268.4,256,268.4z"/>
                            </g>
                            <circle class="ord_searchLogo4" id="ord_searchSunRay" cx="256" cy="205.7" r="18.7"/>
                        </svg>
                    </button>
                </td>
            </tr>
        </table>
    </form>

<div>
    <span>All uploaded Ordinances</span>
    <form method="post" action="">
        <table>
            <?php echo postSearcher(); ?>
        </table>
</div>