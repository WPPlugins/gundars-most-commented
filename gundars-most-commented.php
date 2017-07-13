<?php
/*
Plugin Name: Gundars Most Commented
Plugin URI: http://gundars.me/wp-plugins/gundars-most-commented-wp-pluginwidget
Description: Displays Most Commented blog posts widget in your Sidebar
Version: 0.6.5
Author: Gundars Meness
Author URI: http://gundars.me
License: GPL2

Copyright 2011  Gundars Meness  (email : me AT  gun d a r s DOT   me)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
register_activation_hook(__FILE__, 'gmg_add_defaults');
register_uninstall_hook(__FILE__, 'gmg_delete_plugin_options');
// ---------------------------------------------------------------------------------


// ***************************************
// *** START - Plugin Core Functions    ***
// ***************************************
// delete options table entries ONLY when plugin deactivated AND deleted
function gmg_delete_plugin_options() {
	delete_option('gm_mc_options');
}



// Define default option settings
function gmg_add_defaults() {
	$tmp = get_option('gm_mc_options');
    if(!is_array($tmp)) {
		$arr = array(
		"gundars_post_limit" 		=> '5',
		"gundars_comments_name"		=> 'Comments',
		"gundars_display_time"		=> 'All times',
		"gundars_show_post_count"	=> '1'
		);
		update_option('gm_mc_options', $arr);
		}
		update_option('gundars_widget_title', 'Most commented posts', '','yes');//DAMNED
}

function gundars_most_commented() {
    global $wpdb;
	global $options;

	$options = get_option('gm_mc_options');
	$gmpostlimit = $options[gundars_post_limit];
	$comment_slogan = $options[gundars_comments_name];
	$chosentimefromadminpanel = $options[gundars_display_time];
	$showchecked = "true";
	$leftshowcomment = ' (';
	$rightshowcomment = ')';
	$space_num_slog = ' ';
	
	//DATES
	
	$today = date("Y-m-d");
	$last_week = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-7, date("Y")));
	$last_2weeks = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-14, date("Y")));
	$last_month = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-30, date("Y")));
	$last_3months = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-91, date("Y")));
	$last_6months = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-182, date("Y")));
	$last_year = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-365, date("Y")));
	$allofthem = "0";

	
	switch ($chosentimefromadminpanel) {
		case "Today":
				$switchedtime = $today;
			break;    
		case "Last week":
				$switchedtime = $last_week;
			break;    
		case "Last 2 weeks":
				$switchedtime = $last_2weeks;
			break;    
		case "Last month":
				$switchedtime = $last_month;
			break;    
		case "Last 3 months":
				$switchedtime = $last_3months;
			break;    
		case "Last 6 months":
				$switchedtime = $last_6months;
			break;    
		case "Last year":
				$switchedtime = $last_year;
			break;    
		case "All times":
				$switchedtime = $allofthem;
			break;
		default:
				$switchedtime = $allofthem;
	}
	
	
    // select top most commented posts that have comment count > 0 and are published
    $sql = "SELECT ID, post_title, comment_count, post_date
	FROM $wpdb->posts
	WHERE post_status = 'publish' and comment_count > 0
	AND post_date
	ORDER BY comment_count DESC
	LIMIT $gmpostlimit";
 
    // execute query
    $posts = $wpdb->get_results($sql);
     $html = '';
     $html .= '<ul>';
  
    foreach ($posts as $post) { //Working with each post
	
			 $post_date_raw = substr($post->post_date, 0,10);
			 $post_date = strtotime($post_date_raw); 
			 $chosen_date_raw = $switchedtime;
			 $chosen_date = strtotime($chosen_date_raw); 
			 if ($post_date >= $chosen_date) { 
				$display_this_post = "yes";
			} else { 
				$display_this_post = "no";
			}
			
			if($display_this_post == "yes") {
			
				 $title = $post->post_title;
				 $permalink = get_permalink($post->ID);
				 $comment_count = $post->comment_count;
				if ( $options[gundars_show_post_count] != 1 ) { //To display only Number and "Comments" if it is checked on admins page
					$comment_count 		= ''; 
					$comment_slogan 	= '';
					$leftshowcomment 	= '';
					$rightshowcomment	= '';
				}
				if ( $options[gundars_comments_name] == '' ) { //To delete space if there is no word after comment count
				$space_num_slog = ''; 
				}
				$html .= '<li>';
				$html .= '<a href="'.$permalink.'" title="'.$title.'">'. $title.''.$leftshowcomment.''.$comment_count.''.$space_num_slog.''.$comment_slogan.''.$rightshowcomment.'</a>';
				$html .= '</li>';
			}
	}
	$html .= '</ul>';
	echo $html;
 
}

?>
<?php
function widget_gundars_most_commented($args) {
  extract($args);
  echo $before_widget;
  echo $before_title;?><?php echo get_option('gundars_widget_title'); ?><?php echo $after_title;
  gundars_most_commented();
  echo $after_widget;
}

wp_register_sidebar_widget(
    'gundars_most_commented_1', // your unique widget id
    'Gundars Most Commented', // widget name
    'widget_gundars_most_commented', // callback function
    array(
        'description' => 'Widget adds most commented blog posts to sidebar',
    )
);
?>
<?php 

// ***************************************
// *** END - Plugin Core Functions    ***
// ***************************************
// ***************************************
// *** START - Create Admin Options   ***
// ***************************************

add_action('admin_menu', 'gm_create_menu');

function gm_create_menu() {

	//create new top-level menu
	add_plugins_page('Most Commented', 'Most Commented', 'administrator', __FILE__, 'gm_settings_page',plugins_url('/images/icon.png', __FILE__));
	//call register settings function
	add_action( 'admin_init', 'register_mysettings' );
}

function register_mysettings() {
	//register our settings
	register_setting( 'gm-settings-group', 'gm_mc_options' );
	register_setting( 'gm-settings-group', 'gundars_widget_title' );//DAMNED
}

function gm_settings_page() {
	global $options;
	global $showchecked;
	$showchecked = '';
?>
<div class="wrap">
<h2>Gundars Most Commented settings</h2>

<div class="postbox-container" style="width:48%; float:right";>

	<div class="metabox-holder">
	<div class="postbox">
		<h3 class="hndle"><span>About</span></h3>
		<div style="margin:20px;">
			
				<strong>Gundars Most Commented</strong><br/> Plugin creates a widget in your sidebar with your most commented posts.<br/><br/>
				<a href="http://gundars.me" style="text-decoration:none" target="_blank">Author</a><br/><br/>
				<a href="http://gundars.me/wp-plugins/gundars-most-commented-wp-pluginwidget/" style="text-decoration:none" target="_blank">Report issues here</a>
			<br/><br/>
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="JR6K6JW7ZPMSA">
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="Donate">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
				</form>
	</div>
	</div>
	</div>
	</div>
	<div class="postbox-container" style="width:49%; float:left;">
	
	
	<div class="metabox-holder">
	<div class="postbox">
		<h3 class="hndle"><span>Display settings</span></h3>
		<div style="margin:20px;">
			
	<form method="post" action="options.php">
    <?php settings_fields( 'gm-settings-group' ); ?>
	<?php $options = get_option('gm_mc_options'); ?>
    <table class="form-table">
		
		
		  <tr valign="top">
        <th scope="row"><strong>Widget title</strong></th>
        <td>
				<input type="text" name="gundars_widget_title" value="<?php echo get_option('gundars_widget_title'); ?>" size="20">
				<p><em>Default is: <strong>Most commented posts</strong></em></p></td>
				</tr>

		
				<tr valign="top">
				<th scope="row"><h4>How many posts?</h4></th>
				<td><SELECT NAME="gm_mc_options[gundars_post_limit]">
				<OPTION VALUE="<?php echo $options[gundars_post_limit]; ?>"><?php echo $options[gundars_post_limit]; ?>
				<OPTION VALUE="1">1
				<OPTION VALUE="2">2
				<OPTION VALUE="3">3
				<OPTION VALUE="4">4
				<OPTION VALUE="5">5
				<OPTION VALUE="6">6
				<OPTION VALUE="7">7
				<OPTION VALUE="8">8
				<OPTION VALUE="9">9
				<OPTION VALUE="10">10
				<OPTION VALUE="11">11
				<OPTION VALUE="12">12
				<OPTION VALUE="13">13
				<OPTION VALUE="14">14
				<OPTION VALUE="15">15
				</SELECT>
				<p><em>Default is: <strong>5</strong></em></p></td>
				</tr>
				
				
							
				<tr valign="top">
				<th scope="row"><h4>Show post count?<h4></th>
				<td>
				<input name="gm_mc_options[gundars_show_post_count]" type="checkbox" value="1" <?php checked( '1', $options[gundars_show_post_count]); ?> />
				<p><em>Default is: <strong>Checked</strong></em></p></td>
				</tr>
				
				<tr valign="top">
				<th scope="row"><h4>Word after number<h4></th>
				<td>
				<input type="text" name="gm_mc_options[gundars_comments_name]" value="<?php echo $options[gundars_comments_name]; ?>" size="20">
				<p><em>Default is: <strong>Comments</strong></em></p>
				<p><em>Leave this empty to display only the number.</em></p></td>
				</tr>
				
				<tr valign="top">
				<th scope="row"><h4>Posts from time interval:</h4></th>
				<td><SELECT NAME="gm_mc_options[gundars_display_time]">
				<OPTION VALUE="<?php echo $options[gundars_display_time]; ?>"><?php echo $options[gundars_display_time]; ?>
				<OPTION VALUE="Today">Today
				<OPTION VALUE="Last week">Last week
				<OPTION VALUE="Last 2 weeks">Last 2 weeks
				<OPTION VALUE="Last month">Last month
				<OPTION VALUE="Last 3 months">Last 3 months
				<OPTION VALUE="Last 6 months">Last 6 months
				<OPTION VALUE="Last year">Last year
				<OPTION VALUE="All times">All times (default)
				</SELECT>
				</tr>
    </table>
 
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
	</form>
		</div>
	</div>
	</div>
	</div>
	
<?php }

// ***************************************
// *** END - Create Admin Options     ***
// ***************************************