<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://rezaid.co.uk
 * @since             1.0.0
 * @package           Credipost
 *
 * @wordpress-plugin
 * Plugin Name:       Credipost
 * Plugin URI:        https://crediblock.com/
 * Description:       https://crediblock.com/
 * Version:           1.1.0
 * Author:            Crediblock
 * Author URI:        https://crediblock.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       credipost
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
define( 'PLUGIN_NAME_VERSION', '1.0.0' );
function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-credipost-activator.php';
	Credipost_Activator::activate();
}
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-credipost-deactivator.php';
	Credipost_Deactivator::deactivate();
}
register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );
require plugin_dir_path( __FILE__ ) . 'includes/class-credipost.php';
function run_plugin_name() {
	$plugin = new Credipost();
	$plugin->run();
}
run_plugin_name();
// Get All Categories with out uncategorized
function custom_list_categories() {
	$categories = get_categories( array(
    'orderby' => 'name',
    'order'   => 'ASC',
		'exclude' => '1'
	) );
	$categories_list = '';
	foreach ( $categories as $category ) {
			$categories_list .=
					'<option value="' . ( $category->cat_ID ) . '">' .
					$category->name .
					'</option>';
	}
	if(empty($categories_list))
	{
		return '';
	}
	else
	{
		return '<select style="width:20%" id="categories_dropdown" class="form-control-lg">' . $categories_list . '</select>';
	}
}

function get_all_sites()
{
	if( is_multisite())
	{
		$sites = get_sites();
		$total_sites = '<option disabled selected>Please select a site to post</option>';
		foreach( $sites as $subsite ) {
			$subsite_id = get_object_vars($subsite)["blog_id"];
			$subsite_name = get_blog_details($subsite_id)->blogname;
			// echo 'Site ID/Name: ' . $subsite_id . ' / ' . $subsite_name . '<br>';
			$total_sites .=
			'<option value="' . ( $subsite_id ) . '">' .
				$subsite_name.
			'</option>';
		}
		return '<select style="width:20%" id="multisite_dropdown" class="form-control-lg">' . $total_sites . '</select>';
	}
	else
	{
		return '';
	}
}

// Ajax Call Function ro render data on screen
add_action( 'wp_ajax_scrape_data_php', 'scrape_data_php' );

function scrape_data_php() {
	include_once plugin_dir_path( __FILE__ ).'ScraperMoreFaster.php';
	
	$url = $_POST['url_1'];
	$smf = new ScraperMoreFaster;
	$smf->setCurrentDirectory($url);
	$smf->file_get_html($url);
	$plain_text = $smf->getPlainText();
	
	if(empty($plain_text)){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);

		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3");

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt ($ch, CURLOPT_HEADER, 0);

		$exec=curl_exec($ch);
		curl_close($ch);

		$plain_text = $smf->getPlainText($exec);
		if(empty($plain_text))
		{
			echo 'error';
		}else
		{
			echo $plain_text;
		}
	}
	else
	{
		echo $plain_text;
	}

	wp_die(); // this is required to terminate immediately and return a proper response
}

// Ajax Call Function ro render data on screen
add_action( 'wp_ajax_embed_code', 'embed_code' );

function embed_code() {
	$url = $_POST['site_url_text'];
    // function for generating an embed link
    $finalUrl = '';

    if (strpos($url, 'facebook.com/') !== false) {
        // Facebook Video
        $finalUrl.='https://www.facebook.com/plugins/video.php?href='.rawurlencode($url).'&show_text=1&width=200';

    } else if(strpos($url, 'vimeo.com/') !== false) {
        // Vimeo video
        $videoId = isset(explode("vimeo.com/",$url)[1]) ? explode("vimeo.com/",$url)[1] : null;
        if (strpos($videoId, '&') !== false){
            $videoId = explode("&",$videoId)[0];
        }
        $finalUrl.='https://player.vimeo.com/video/'.$videoId;

    } else if (strpos($url, 'youtube.com/') !== false) {
        // Youtube video
        $videoId = isset(explode("v=",$url)[1]) ? explode("v=",$url)[1] : null;
        if (strpos($videoId, '&') !== false){
            $videoId = explode("&",$videoId)[0];
        }
        $finalUrl.='https://www.youtube.com/embed/'.$videoId;

    } else if(strpos($url, 'youtu.be/') !== false) {
        // Youtube  video
        $videoId = isset(explode("youtu.be/",$url)[1]) ? explode("youtu.be/",$url)[1] : null;
        if (strpos($videoId, '&') !== false) {
            $videoId = explode("&",$videoId)[0];
        }
        $finalUrl.='https://www.youtube.com/embed/'.$videoId;

    } else if (strpos($url, 'dailymotion.com/') !== false) {
        // Dailymotion Video
        $videoId = isset(explode("dailymotion.com/",$url)[1]) ? explode("dailymotion.com/",$url)[1] : null;
        if (strpos($videoId, '&') !== false) {
            $videoId = explode("&",$videoId)[0];
        }
        $finalUrl.='https://www.dailymotion.com/embed/'.$videoId;

    } else{
        echo 'error';
    }

    echo $finalUrl;


	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_save_as_post', 'save_as_post' );

function save_as_post() {

	$site_id = $_POST['site'];
	if($site_id != '')
	{
		switch_to_blog($site_id);
		$my_post = array(
			'post_title'    =>  'From - '.$_POST['site_url_text'].' - '.$_POST['title_1'],
			'post_content'  => $_POST['html_1'],
			'post_status'   => 'publish',
			'post_category' => array( $_POST['cats'] ),
			);
	
		// Insert the post into the database
		wp_insert_post( $my_post );
	
		restore_current_blog();
		wp_die(); // this is required to terminate immediately and return a proper response
	}
	else
	{
			$my_post = array(
			'post_title'    => 'From - '.$_POST['site_url_text'].' - '.$_POST['title_1'],
			'post_content'  => $_POST['html_1'],
			'post_status'   => 'publish',
			'post_category' => array( $_POST['cats'] ),
		);
		
		// // Insert the post into the database
		wp_insert_post( $my_post );
		wp_die();
	}


}
// Register Short Code
function credipost_function($atts) {
	// Function to get Categories excluding uncategorized
	$categories1 = custom_list_categories();
	$sites = get_all_sites();
	// Display html of shortcode
	$Content = '<div id="success_msg" style="display:none;color:green;">Post Successfully Saved!</div>';
		$Content 	.= '<div style="display:flex;">';
			$Content 	.= '<input style="width:50%;" type="text" name="url" id="url" class="form-control" placeholder="Enter your URL" required/>&emsp;';
			$Content 	.= '<button class="btn btn-dark" id="scrapperbtn" style="cursor:pointer;">Preview</button>&emsp;';
			$Content 	.= '<button class="btn btn-success" id="embed_code" style="cursor:pointer;">Get Embeded Code</button>';
		$Content 	.= '</div>';
	$Content .= '<div id="error_1" style="display:none;color:red;">Please enter URL!</div>';
	$Content .= '<div id="error_2" style="display:none;color:red;">Please enter a valid URL!</div>';
	$Content .= "<div id='error_3' style='display:none;color:red;'>Couldn't post to external site!</div>";
	$Content .= "<div id='error_4' style='display:none;color:red;'>Please Enter Valid URL to get Embed Code!</div>";
	$Content .= '<div id="ajaxSpinnerImage" style="display:none;"><h3>Loading....</h3></div>';
	$Content .= '<div id="post_create" class="form-row" style="display:none; margin:2% 0 2% 0;">';
		$Content .= '<div style="display:flex;">
										'.$categories1;
		if(!empty($categories1))
		{
			$Content .= '&emsp;';
		}
		$Content .=			$sites;
		if(!empty($sites))
		{
			$Content .= '&emsp;';
		}
		$Content .= '<button class="btn btn-info" id="save_post" style="cursor:pointer;">Save As Post</button>&emsp;
										<button onclick="window.print();return false" class="btn btn-warning" id="print_btn" style="cursor:pointer;">Print this page</button>
									</div>';
		$Content .= '</div>';
	$Content .= '<div id="ajaxSpinnerImage_1" style="display:none;"><h3>Saving....</h3></div>';
	$Content .= '<div id="rendered_data" style="margin-top:2%;"></div>';

  
	return $Content;
}
add_shortcode('CREDIPOST', 'credipost_function');
//Sidebar menu and page
/****************************************************************************************************************/
add_action('admin_menu', 'credipost_page_create');
function credipost_page_create() {
    $page_title   = 'Credipost';
    $menu_title   = 'Credipost';
    $capability   = 'edit_posts';
    $menu_slug    = 'credipost_page';
    $function     = 'credipost_page_display';
    $icon_url     = 'dashicons-media-document';
    $position     = 100;

  add_menu_page( $page_title, $menu_title, $capability , $menu_slug, $function, $icon_url, $position );
}
// Display content on page
function credipost_page_display() { 
	?>
  <div class="wrap">
		<h1><?php echo esc_html( 'Credipost Page' ); ?></h1>
		<div style="margin-top:35px;">
			<?php echo do_shortcode('[CREDIPOST]');?>
		</div>
	</div>
<?php
}