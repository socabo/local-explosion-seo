<?php
/*
 Plugin Name: Local Explosion SEO
 Plugin URI: http://ilidiolopes.com/local-explosion-seo-plugin/
 Description: Dominate the SERP for local terms.
 Version: 1.0
 Author: Ilidio Lopes
 Author URI: http://ilidiolopes.com
 Text Domain: local-explosion-seo
 */

if ( !class_exists('LocalExplosionSEO') ) {
	class LocalExplosionSEO {
		
		var $id = 'local-explosion-seo';
		var $version = '1.0';
		var $url;
		
		//Constructor
		public function __construct()
		{
			$this->url = plugins_url('',__FILE__);
				
			add_action('add_meta_boxes', array(&$this,'add_seo'));
			add_action('save_post', array(&$this,'save_seo'));
			add_action( 'admin_menu', array(&$this,'add_les_menu'));
			add_filter('wp_title', array(&$this,'les_wp_title'));
			add_action('admin_print_scripts', array(&$this,'les_admin_js'));
			add_action('admin_print_styles', array(&$this,'les_admin_css'));
		} // end contructor
		
		function add_seo(){
			if ( function_exists('add_meta_box') ) {
				add_meta_box('wpu-les-meta', 'Local Explosion SEO', array(&$this,'meta_les_builder'),'post', 'normal', 'high');
				add_meta_box('wpu-les-meta', 'Local Explosion SEO', array(&$this,'meta_les_builder'),'page', 'normal', 'high');
			}
		}
		function meta_les_builder(){
			global $post;
			$values = get_post_custom( $post->ID );
			$seo = isset( $values['les_seo_meta'] ) ? maybe_unserialize($values['les_seo_meta'][0] ) : array(
					'title' => '', 'desc' => '', 'keywords' => ''
			);
			?>
		            <style type="text/css">
						#dws-meta-wrap { background:#fff; padding:10px; border:#DDD 1px solid; }
						#dws-meta-wrap .title{ font-size:14px; }
						#dws-meta-wrap label { font-weight:bold; }
						#dws-meta-wrap .regular-text { width:100%; }
					</style>
		            <div id="les-meta-wrap">
		            <span class="title">Meta Information</span>
		            <table cellpadding="5" width="100%">
		            <tr>
		            <td align="right" valign="top"><label for="les_meta_title">Title:</label></td>
		            <td align="left" valign="top"><input type="text" name="les_meta_title" id="les_meta_title" class="regular-text" value="<?php echo $seo['title']; ?>" onkeyup="javascript:document.getElementById('les_title_charcount').innerHTML = document.getElementById('les_meta_title').value.length"><br />
		            You've entered <strong id="les_title_charcount"><?php echo strlen($seo['title']); ?></strong> characters. Most search engines use up to 70.
		            </td>
		            </tr>
		            <tr>
		            <td align="right" valign="top"><label for="les_meta_desc">Description:</label></td>
		            <td align="left" valign="top"><textarea name="les_meta_desc" id="les_meta_desc" class="regular-text" cols="60" rows="3" onkeyup="javascript:document.getElementById('les_meta_description_charcount').innerHTML = document.getElementById('les_meta_desc').value.length"><?php echo $seo['desc']; ?></textarea><br />
		            You've entered <strong id="les_meta_description_charcount"><?php echo strlen($seo['desc']); ?></strong> characters. Most search engines use up to 140.
		            </td>
		            </tr>
		            <tr>
		            <td align="right" valign="top"><label for="les_meta_key">Keywords:</label></td>
		            <td align="left" valign="top"><input type="text" name="les_meta_key" id="les_meta_key" value="<?php echo $seo['keywords']; ?>" class="regular-text"  /></td>
		            </tr>
					</table>
		            <?php // We'll use this nonce field later on when saving.  
		    wp_nonce_field( 'les_meta_nonce', 'meta_les_nonce' );  ?>
		           </div>
		            <?php
		}
				
		function save_seo($post_id){
			// Bail if we're doing an auto save
			if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		
			// if our nonce isn't there, or we can't verify it, bail
			if( !isset( $_POST['meta_les_nonce'] ) || !wp_verify_nonce( $_POST['meta_les_nonce'], 'les_meta_nonce' ) ) return;
		
			// if our current user can't edit this post, bail
			if( !current_user_can( 'edit_post' ) ) return;
			// Make sure your data is set before trying to save it
		
			if( isset( $_POST['les_meta_title'] ) )
				$les_seo['title'] = $_POST['les_meta_title'];
			if( isset( $_POST['les_meta_desc'] ) )
				$les_seo['desc'] = $_POST['les_meta_desc'];
			if( isset( $_POST['les_meta_key'] ) )
				$les_seo['keywords'] = $_POST['les_meta_key'];
		
			update_post_meta( $post_id, 'les_seo_meta', maybe_unserialize($les_seo ) );
		
		
		}
				
		function les_wp_title($title){
			global $post, $paged;
			$values = get_post_custom( $post->ID );
		
			$seo = isset( $values['les_seo_meta'] ) ? maybe_unserialize($values['les_seo_meta'][0] ) : '';
			if ( is_feed() || is_home() || is_front_page() )
				return $title;
			if(isset($seo['title']))
				$filtered_title = $seo['title'] . ' | ';
			else
				$filtered_title = $post->post_title. ' | ';
			//$filtered_title .= ( 2 <= $paged || 2 <= $paged ) ? ' | ' . sprintf( __( 'Page %s' ), max( $paged, $page ) ) : '';
		
			return $filtered_title;
		}
		
		// Load the Javascripts
		function les_admin_js(){
			// Check whether the page is the Shortcoder admin page.
			if (isset($_GET['page']) && $_GET['page'] == 'local-explosion-seo'){
				wp_enqueue_script(array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-draggable',
				'jquery-ui-droppable'
						));
			//wp_enqueue_script('local-explosion-seo-magic-js', $this->url . '/js/les-magic-js.js?v=' . $this->version);
			wp_enqueue_script('local-explosion-seo-admin-js', $this->url . '/js/les-admin-js.js?v=' . $this->version);
			}
		}	
		
		// Load the CSS
		function les_admin_css(){
			if (isset($_GET['page']) && $_GET['page'] == 'local-explosion-seo') {
				wp_enqueue_style('local-explosion-seo-admin-css', $this->url . '/css/les-admin-css.css?v=' . $this->version);
				//wp_enqueue_style('local-explosion-seo-bootstrap', '//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css');
				
			}
		}

		
			// Menu
		function add_les_menu() {
			//add an item to the menu
			add_menu_page (
			'Local Explosion SEO',
			'Local Explosion',
			'manage_options',
			'local-explosion-seo',
			array(&$this,'les_admin_page'));
			//plugin_dir_url( __FILE__ ).'icons/my_icon.png','23.56');
		}
		
		function les_admin_page(){
		
			$sc_updated = false;
			//$sc_options = get_option('shortcoder_data');
			$sc_flags = get_option('shortcoder_flags');
		
			$title = __( "Insert Post Template", 'shortcoder' );
			$button = __( "Create Posts", 'shortcoder' );
			$edit = 0;
			$sc_content = '';
			$sc_disable = 0;
			$sc_hide_admin = 0;
		
			// Insert shortcode
			if (isset($_POST["sc_form_main"]) && $_POST["sc_form_main"] == '1' && check_admin_referer('shortcoder_create_form')){
		
				$json_file = plugin_dir_url(__FILE__).'estados-cidades.json';
		
				//Title
				$les_post_title_with_placeholder = $_POST['les_post_title'];
		
				//Caetgoris
				$les_categories = explode(',', $_POST['sc_categories']);
				for($i = 0; $i < count($les_categories); $i++) {
					// Create the categories and get the ids
					$les_categories_ids[] = wp_insert_category(array('cat_name' => $les_categories[$i]));
		
					//$les_categories_ids[] = $les_categories->term_id;
				}
		
				//Tags
				$les_post_tags = explode(',',$_POST['sc_tags']);
		
		
				$state_city_placeholder = array("##state##","##stateabr##","##city##");
				//$state_city = array("Estado","Sigla","Cidade");
		
					
				//$d = new Datetime('2010-02-23 18:57:33');
				//$d = new Datetime('now');
		
		
				$dateMin = new DateTime('now');
				$dateMin->sub(new DateInterval('P4Y'));
				$dateMax = new DateTime('now');
				$dateMax->add(new DateInterval('P1Y'));
		
				$states = json_decode(file_get_contents($json_file), true);
				for($x = 0; $x < count($states['estados']); $x++ ) {
					//echo count($states['estados'][$x]['cidades']).'<br>';
					//echo $states['estados'][$x]['nome'].'<br>';
					$state_city[0] = $states['estados'][$x]['nome'];
					$state_city[1] = $states['estados'][$x]['sigla'];
		
					for($y = 0; $y < count($states['estados'][$x]['cidades']); $y++ ) {
						//echo $states['estados'][$x]['cidades'][$y].'<br>';
						$state_city[2] = $states['estados'][$x]['cidades'][$y];
		
						$les_post_title = str_replace($state_city_placeholder, $state_city, $les_post_title_with_placeholder);
		
						$post_content_with_placeholder = $_POST['sc_content'];
		
						$post_content = str_replace($state_city_placeholder, $state_city, $post_content_with_placeholder);
							
						//Generate random time
						$hour = mt_rand(0,23);
						$minute = mt_rand(0,59);
						$second= mt_rand(0,59);
						//Add the random time to the date
						$dateMin->setTime($hour,$minute,$second);
							
						//Publish one post
		
						$post_data = array(
								'post_title'    => $les_post_title,
								'post_content'  => $post_content,
								'post_status'   => 'publish',
								'post_type'     => 'post',
								'post_author'   => $author_id,
								'post_category' => $les_categories_ids,
								'tags_input'    => $les_post_tags,
								'post_date'     => $postdate = $dateMin->format('Y-m-d H:i:s'),
								'post_date_gmt' => $postdate
						);
		
						$post_id = wp_insert_post($post_data);
		
						if( isset( $_POST['les_meta_title'] ) && !empty($_POST['les_meta_title'])) {
							$les_seo['title'] = str_replace($state_city_placeholder, $state_city, $_POST['les_meta_title']);
						}
						/*		if( isset( $_POST['dws_meta_desc'] ) )
						 $dws_seo['desc'] = $_POST['dws_meta_desc'];
						if( isset( $_POST['dws_meta_key'] ) )
							$dws_seo['keywords'] = $_POST['dws_meta_key'];
						*/
		
						if(!empty($les_seo))
							update_post_meta($post_id, 'les_seo_meta', maybe_unserialize($les_seo));
		
						//Add one day to the current date and repeat the cycle
						$dateMin->add(new DateInterval('P1D'));
							
						if($dateMin == $dateMax) {
							$dateMin->sub(new DateInterval('P5Y'));
						}
		
						if($y == 20) break 2;
					}
				}
		
				unset($states);
		
				$sc_updated = true;
		
				// Insert Message
				if($sc_updated == 'true'){
					echo '<div class="message updated fade"><p>' . __('Posts created successfully !', 'shortcoder') . '</p></div>';
				}else{
					echo '<div class="message error fade"><p>' . __('Unable to create posts !', 'shortcoder') . '</p></div>';
				}
			}
		
		
			?>
				
				<!-- Shortcoder Admin page --> 
				
				<div class="wrap">
				<div class="col-sm-6 col-sm-offset-3">
		  
		
  				<h2>Local Explosion SEO<sup class="smallText"> v<?php echo $this->version; ?></sup></h2>
				
				<div id="content">
					
					<h3><?php echo $title; ?> <?php if($edit == 1) echo '<span class="button sc_back">&lt;&lt; ' . __( "Back", 'shortcoder' ) . '</span>'; ?> </h3>
					
					<form method="post" id="sc_form">
					
						<div class="sc_section">
							<label for="les_post_title" class="sc_fld_title"><?php _e( "Title:", 'shortcoder' ); ?>:</label>
							<span class="sc_name_wrap"><input type="text" name="les_post_title" id="les_post_title" placeholder="Enter the title" class="widefat" required="required"/></span>
						</div>
						
				
						<div class="sc_section">
							<label for="sc_content" class="sc_fld_title"><?php _e( "Content:", 'shortcoder' ); ?>:</label>
							<?php wp_editor( $sc_content, 'sc_content', array( 'wpautop'=> false, 'textarea_rows'=> 18 )); ?>
						</div>
				
				
						<div class="sc_section">
							<label for="sc_categories" class="sc_fld_title"><?php _e( "Categories:", 'shortcoder' ); ?>:</label>
							<span class=""><input type="text" name="sc_categories" id="sc_categories" placeholder="Enter the categories" class="widefat" required="required"/></span>
						</div>
						
						
						<div class="sc_section">
							<label for="sc_tags" class="sc_fld_title"><?php _e( "Tags:", 'shortcoder' ); ?>:</label>
							<span class=""><textarea rows="4" name="sc_tags" id="sc_tags" placeholder="Enter the tags" class="widefat" required="required"></textarea></span>
						</div>
						
						
						<div class="sc_section">
							<label for="les_meta_title" class="sc_fld_title"><?php _e( "SEO Title:", 'shortcoder' ); ?>:</label>
							<span class=""><input type="text" name="les_meta_title" id="les_meta_title" placeholder="Enter a SEO title" class="widefat" /></span>
						</div>
						
						<div class="sc_section"><p></p></div>
						
						<div class="sc_section">
						
							<table width="100%"><tr>
								
								<td width="50%" class="sc_settings"><label></td>
								
								<td><p align="right"><input type="submit" name="sc_submit" id="sc_submit" class="button-primary" value="<?php echo $button; ?>" /></p></td>
					
							</tr></table>
					
						</div>
						
						<?php wp_nonce_field('shortcoder_create_form'); ?>
						<input name="sc_form_main" type="hidden" value="1" />
					</form>
					
					<h3><?php _e( "Created Shortcodes", 'shortcoder' ); ?> <small>(<?php _e( "Click to edit", 'shortcoder' ); ?>)</small></h3>
					<form method="post" id="sc_edit_form">
						<ul id="sc_list" class="clearfix">
						<?php
							$sc_options = get_option('shortcoder_data');
							if(is_array($sc_options)){
								foreach($sc_options as $key=>$value){
									echo '<li>' . $key . '</li>';
								}
							}
						?>
						</ul>
						
						<?php wp_nonce_field('shortcoder_edit_form'); ?>
						<input name="sc_form_edit" type="hidden" value="1" />
						<input name="sc_form_action" id="sc_form_action" type="hidden" value="edit" />
						<input name="sc_name_edit" id="sc_name_edit" type="hidden" />
					</form>
					
				</div><!-- Content -->
				
				</div><!-- Wrap -->
				
				<?php
				}
				
	}
}

new LocalExplosionSEO();

				
function testDate() {
	
		
	$date = new DateTime('now');
	$dateMin = new DateTime('now');
	$dateMin->sub(new DateInterval('P4Y'));
	$dateMax = new DateTime('now');
	$dateMax->add(new DateInterval('P1Y'));
	
/*	echo $date->format('Y-m-d H:i:s') .'<br>';
	
	echo $dateMax->format('Y-m-d H:i:s') .'<br>';
	
	echo $dateMin->format('Y-m-d H:i:s') .'<br>';
*/	
	$interval = $dateMin->diff($dateMax);
	echo $interval->format('%R%a days');
	echo $interval->format('%a');
	
	/*while(citiesArray is not empty) {
		$dateMin = new DateTime('now');
		$dateMin->sub(new DateInterval('P4Y'));*/
	while($dateMin < $dateMax /*&& citiesArray is not empty*/) {
		//Generate random time
		$hour = mt_rand(0,23);
		$minute = mt_rand(0,59);
		$second= mt_rand(0,59);
		//Add the random time to the date
		$dateMin->setTime($hour,$minute,$second);
		
		//Publish one post
		
		//Add one day to the current date and repeat the cycle
		echo $date->format('Y-m-d H:i:s') .'<br>';
		$dateMin->add(new DateInterval('P1Y'));
		echo $dateMin->format('Y-m-d H:i:s') .'<br>';
	}
	//}
	
/*	for($i=0; $i<10; $i++) {
		$hour = mt_rand(0,23);
		$minute = mt_rand(0,59);
		$second= mt_rand(0,59);
		$date->setTime($hour,$minute,$second);
		echo $date->format('Y-m-d H:i:s') .'<br>';
	}*/

	//echo date('2012-02-28 18:57:33');
	//$date= new DateTime('2012-02-28 18:57:33');
	
}

function testJson() {
	
/*	echo dirname(plugin_basename(__FILE__))."<br>";
	echo plugin_basename(__FILE__)."<br>";
	echo plugin_dir_url(__FILE__).'<br>';
*/	

	$json_file = plugin_dir_url(__FILE__).'estados-cidades.json';
	//echo $name;
	
/*	$jsonIterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(json_decode(file_get_contents($json_file), TRUE)),
			RecursiveIteratorIterator::SELF_FIRST);

	foreach ($jsonIterator as $key => $val) {
		if(is_array($val)) {
			echo "$key:\n";
		} else {
			echo "$key => $val\n";
		}
	}
/*	
	$jsonIterator = new RecursiveIteratorIterator(
			new RecursiveArrayIterator(json_decode($json, TRUE)),
			RecursiveIteratorIterator::SELF_FIRST);
	
	foreach ($jsonIterator as $key => $val) {
		if(is_array($val)) {
			echo "$key:\n";
		} else {
			echo "$key => $val\n";
			}
	}
*/	
	$date = new DateTime('now');
	$dateMin = new DateTime('now');
	$dateMin->sub(new DateInterval('P4Y'));
	$dateMax = new DateTime('now');
	$dateMax->add(new DateInterval('P1Y'));

	
	$states = json_decode(file_get_contents($json_file), true);
	for($x = 0; $x < count($states['estados']); $x++ ) {
		//echo count($states['estados'][$x]['cidades']).'<br>';
		echo $states['estados'][$x]['nome'].'<br>';
		
		for($y = 0; $y < count($states['estados'][$x]['cidades']); $y++ ) {
			echo $states['estados'][$x]['cidades'][$y].'<br>';
			
			//Generate random time
			$hour = mt_rand(0,23);
			$minute = mt_rand(0,59);
			$second= mt_rand(0,59);
			//Add the random time to the date
			$dateMin->setTime($hour,$minute,$second);
			
			//Publish one post
			
			//Add one day to the current date and repeat the cycle
			$dateMin->add(new DateInterval('P1Y'));
			
			if($dateMin == $dateMax) {
				$dateMin->sub(new DateInterval('P5Y'));
			}
		}
	}
	//echo count($shipments[0]).'<br>';
	//print_r($shipments);
	unset($states);

}

//testDate();
//testJson();

