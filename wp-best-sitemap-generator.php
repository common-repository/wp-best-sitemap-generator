<?php
/*
Plugin Name: WP Best Sitemap Generator
Plugin URI: https://www.eastsidecode.com
Description: Best WordPress Sitemap Generator. Geneates a single sitemap.xml file with priority value
Author: Louis Fico
Version: 1.0
Author URI: eastsidecode.com
*/

if ( ! defined( 'ABSPATH' ) ) exit;



// create menu in admin panel
add_action('admin_menu', function() {
	add_submenu_page( 'options-general.php', 'WP Best Sitemap', 'Sitemap Settings', 'manage_options', 'wp_best_sitemap_settings', 'wp_best_sitemap_page' );
});

// get those settings

add_action( 'admin_init', function() {

				$wpbs_postTyPestoCover = array();
				$wpbs_postTyPestoCover = ['post', 'page'];


				// only include public post types
				$publicCPTForSitemapArgs = array(
				   'public'   => true,
				   '_builtin' => false
					);

				$wpbs_output = 'names'; // names or objects, note names is the default
				$wpbs_operator = 'and'; // 'and' or 'or'

				$post_types = get_post_types( $publicCPTForSitemapArgs, $wpbs_output, $wpbs_operator ); 

				foreach ( $post_types  as $post_type ) {

				  $wpbs_postTyPestoCover[] = $post_type;
				}

				

				foreach ($wpbs_postTyPestoCover as $wpbsCoveredPostType) {
					$wpbsSettingName = 'wpbs_include_post_type_' . $wpbsCoveredPostType;
				    register_setting( 'wp-best-sitemap-plugin-settings', $wpbsSettingName );

				}

				// hidden option
				register_setting( 'wp-best-sitemap-plugin-settings', 'wpbs_secret_update_option' );


				// register the setting for all posts
				$wpbs_query_all_posts_args_for_settings = array(
			    	'post_type' => 'any',
				);

				$wpbs_query_all_posts_for_settings = new WP_Query( $wpbs_query_all_posts_args_for_settings );
				if (!$wpbs_query_all_posts_for_settings->have_posts()) {
				} else { 
					while ( $wpbs_query_all_posts_for_settings->have_posts() ) : 
						$wpbs_query_all_posts_for_settings->the_post();
						$wpbsSettingID = get_the_ID();
						$wpbsPostPrioritySetting = "wpbs_priority_$wpbsSettingID";
						register_setting( 'wp-best-sitemap-plugin-settings', $wpbsPostPrioritySetting );
					endwhile;

				} // end else 

});



function wp_best_sitemap_page() {
  ?>
    <div class="wrap">
    	<h1>Sitemap Settings</h1>
      <form action="options.php" method="post">
      	  <?php
	          settings_fields( 'wp-best-sitemap-plugin-settings' );
	          do_settings_sections( 'wp-best-sitemap-plugin-settings' );
	        ?>
      	<table>
      		<?php 
      			/*
					Create a hidden field that update to the opposite value every time the page loads, that way we only need to trigger onto one option to trigger te sitemap build
      			*/
  			?>
      		<tr style="display: none;">
      			<td><input name="wpbs_secret_update_option" type="checkbox" value="1" <?php checked( '1', !esc_attr(get_option( 'wpbs_secret_update_option' )) ); ?> /></td>
      		</tr>
      		<!-- spacer -->
			 <tr>
			     <td height="15" style="font-size:15px; line-height:15px;">&nbsp;</td>
			 </tr>

      		<tr>
      			<td style="font-size: 15px; font-weight: 500;">Post Types To Include:</td>
      		</tr>
      		<?php
	      		$wpbs_postTyPestoCover = array();
				$wpbs_postTyPestoCover = ['post', 'page'];


				// only include public post types
				$publicCPTForSitemapArgs = array(
				   'public'   => true,
				   '_builtin' => false,
				   'exclude_from_search' => false
					);

				$wpbs_output = 'names'; // names or objects, note names is the default
				$wpbs_operator = 'and'; // 'and' or 'or'

				$wpbs_custom_post_types = get_post_types( $publicCPTForSitemapArgs, $wpbs_output, $wpbs_operator ); 

				foreach ( $wpbs_custom_post_types  as $post_type ) {

				  $wpbs_postTyPestoCover[] = $post_type;
				}

				// create an array that we'll add to 
				$wpbsIncludedPostTypes = array();
				foreach ($wpbs_postTyPestoCover as $wpbsCoveredPostType) {
					$wpbsOptionName = 'wpbs_include_post_type_' . $wpbsCoveredPostType;
					echo '<tr>';
					echo "<td class='wpbs-post-type-covered'>" . $wpbsCoveredPostType . "</td>";
					echo '<td>'; ?>
						<input name="<?php echo $wpbsOptionName; ?>" type="checkbox" value="1" <?php checked( '1', esc_attr(get_option( $wpbsOptionName )) ); ?> />
					<?php
					echo '</td>';

					echo '</tr>';
					// if its checked, add it to the array to use later
					if (esc_attr(get_option( $wpbsOptionName )) == 1) {
						$wpbsIncludedPostTypes[] = $wpbsCoveredPostType;
					}
				}
			?>
			 <tr>
			     <td height="15" style="font-size:15px; line-height:15px;">&nbsp;</td>
			 </tr>
			<tr>
				<td style="font-size: 15px; font-weight: 500;">Choose Post/Page Priority:</td>
			</tr>
			 <tr>
			     <td height="5" style="font-size:5px; line-height:5px;">&nbsp;</td>
			 </tr>
 			 <tr>
			     <td colspan="2">Note: This value should be one of the following:<br />0.0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1.0 <br /><br />If left blank, this value will default to 1.</td>
			 </tr>
 			 <tr>
			     <td height="5" style="font-size:5px; line-height:5px;">&nbsp;</td>
			 </tr>
			 <tr>
			     <td style="font-size: 15px; font-weight: 500;">Page/Post</td> <td style="font-size: 15px; font-weight: 500;">Priority Value</td>
			 </tr>
			<?php

			$wpbs_query_all_posts_args = array(
			    'post_type' => 'any',
			    'post_status'     => 'publish'
			);
			$wpbs_query_all_posts = new WP_Query( $wpbs_query_all_posts_args );
			if (!$wpbs_query_all_posts->have_posts()) {
			echo "Sorry, your blog pas no posts or pages yet";
			} else { 
				while ( $wpbs_query_all_posts->have_posts() ) : 
					$wpbs_query_all_posts->the_post();
					$wpbsTextValue = "wpbs_priority_" . get_the_id();
					$wpbsPostType = get_post_type(get_the_id());
					if (in_array($wpbsPostType, $wpbsIncludedPostTypes)) {
					echo '<tr>';
				} else { // if its not in the allowd post types, still echo it out so that the value doesnt't go away
					echo '<tr style=\'display: none;\'>';
				}
					echo '<td>' . get_the_title() . '</td>';
					echo '<td><input style=\'width: 40px;\' type=\'text\' name="wpbs_priority_' . get_the_id() . '" value="' . esc_attr(get_option( $wpbsTextValue )) . '"" /></td>';
					echo '</tr>';
				endwhile;
				} // end else 
				?>
		
			  <tr>
                <td><?php submit_button(); ?></td>
            </tr>

            <tr>
            	<td>
            		<?php if ((file_exists( ABSPATH . 'sitemap.xml'))) : ?>
            			<a href="<?php echo get_home_url() . '/sitemap.xml'; ?>" target="_blank">View current sitemap -></a>
        			<?php endif; ?>
            		<?php if ((file_exists( ABSPATH . 'sitemap_index.xml'))) : ?>
            			<a href="<?php echo get_home_url() . '/sitemap_index.xml'; ?>" target="_blank">View current sitemap -></a>
        			<?php endif; ?>
            	</td>
            </tr>
      	</table>

  	    </form>
    </div> <!-- end wrap -->
    <style type="text/css">
    	.wpbs-post-type-covered {
    		text-transform: capitalize;
    	}
	</style>
  <?php
}


// handle output of the file when posts are saved or published
add_action( 'publish_post', 'wpbs_create_sitemap' );
add_action( 'publish_page', 'wpbs_create_sitemap' );
add_action( 'save_post', 'wpbs_create_sitemap' );
add_action( 'update_option_wpbs_secret_update_option', 'wpbs_create_sitemap');

function wpbs_create_sitemap() {
	$wpbs_postTyPestoCover = array();

	// check if posts and pages are checked
	if (esc_attr(get_option('wpbs_include_post_type_post')) == 1) {
		$wpbs_postTyPestoCover[] = 'post';
	}
	if (esc_attr(get_option('wpbs_include_post_type_page')) == 1) {
		$wpbs_postTyPestoCover[] = 'page';
	}



	// only include public post types
	$publicCPTForSitemapArgs = array(
	   'public'   => true,
	   '_builtin' => false,
	   'exclude_from_search' => false
		);

	$wpbs_output = 'names'; // names or objects, note names is the default
	$wpbs_operator = 'and'; // 'and' or 'or'

	$wpbs_post_types = get_post_types( $publicCPTForSitemapArgs, $wpbs_output, $wpbs_operator ); 

	// get post types to exclude from options page
	// set up an array to capture the values in a loop
	$wpbs_exclude_types = array();
	$wpbs_exclude_base = 'wpbs_include_post_type_';
	foreach ($wpbs_post_types as $postType) {
		$wpbsOptionToCheck = $wpbs_exclude_base . $postType;
		$wpbsOptionToCheckValue = esc_attr(get_option($wpbsOptionToCheck ));

		// if its not checked in the options page
		if ($wpbsOptionToCheckValue != 1) {
			// add it to the exlude array
			$wpbs_exclude_types[] = str_replace('wpbs_include_post_type_', '', $wpbsOptionToCheck);
		}

	}

	// add the custom post types to the $wpbs_postTyPestoCover array
	foreach ( $wpbs_post_types  as $post_type ) {
		// only add the post type to cover it is not in the 
	  if (!in_array($post_type, $wpbs_exclude_types)) {
	  	$wpbs_postTyPestoCover[] = $post_type;
		
		}
	}



    $postsForSitemap = get_posts(array(
        'numberposts' => -1,
        'orderby' => 'modified',
        'post_type'  => $wpbs_postTyPestoCover,
        'order'    => 'DESC'
    ));

    $wpbs_sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
    $wpbs_sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    foreach( $postsForSitemap as $post ) {
        setup_postdata( $post );
        $wpbsPostID = $post->ID;
        $postdate = explode( " ", $post->post_modified );
        $wpbsPriorityOptionValue = "wpbs_priority_" . $wpbsPostID;
        $wpbsPriority = esc_attr(get_option($wpbsPriorityOptionValue));
        if (empty($wpbsPriority)) {
        	$wpbsPriority = 1;
        }

        $wpbs_sitemap .= '<url>'.
          '<loc>' . get_permalink( $post->ID ) . '</loc>' .
          '<lastmod>' . $postdate[0] . '</lastmod>' .
          // '<changefreq>monthly</changefreq>' .
          '<priority>' . $wpbsPriority . '</priority>' .
         '</url>';
      }

    $wpbs_sitemap .= '</urlset>';

    $wpbs_fp = fopen( ABSPATH . 'sitemap.xml', 'w' );

    fwrite( $wpbs_fp, $wpbs_sitemap );
    fclose( $wpbs_fp );
}

