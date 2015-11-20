<?php
/**
 * Includes the dependency classes for the plugin
 * Handles callbacks for basic WP hooks
 * Handles callbacks for shortcodes
 *
 * @since 	1.0.0
 */
class Alm {

	/**
	 * Whether or not we are displaying debug messages
	 *
	 * @param 	bool
	 * @since 	1.0.0
	 */
	static $debug = false;

	/**
	 * The dependency classes to be included
	 *
	 * @param 	array
	 * @since 	1.0.0
	 */
	static $classes = array( "alm-options" );

	/**
	 * Back end
	 * 
	 * - admin_enqueue()
	 */

	/**
	 * Enqueue scripts on the admin side
	 * 
	 * @since 1.0.0
	 */
	static function admin_enqueue() {

		$screen = get_current_screen();
		if( $screen->id != 'settings_page_alm_settings' ) return;
		
		wp_enqueue_style('alm-admin-css', alm_url('/css/alm-admin.css'));
		wp_enqueue_style('alm-iris-css', alm_url('/assets/iris/iris.min.css'));
		
		wp_enqueue_script('alm-options-js', alm_url('/js/admin/plugin-options.js'), array('jquery', 'alm-iris-js'));
		
		wp_enqueue_media();
		
		wp_enqueue_script('alm-jquery-ui-js', alm_url('/assets/iris/jquery-ui.js'), array('jquery', 'media-views'));
		wp_enqueue_script('alm-iris-js', alm_url('/assets/iris/iris.min.js'), array('jquery', 'alm-jquery-ui-js'));	

	} # end: admin_enqueue()

	/**
	 * Front end
	 *
	 * - default_message()
	 * - do_alert()
	 * - do_countdown()
	 */

	/**
	 * Check if we have a message in place, if not display a default message
	 *
	 * @since 1.0.0
	 */
	static function default_message() {

		# Check if we have a message in the system
		if( empty ( $msg = Alm_Options::$options['default_msg'] ) ) return;

		# Check if we need to show the message on this page
		if( ! self::alert_should_show() ) return;

		wp_enqueue_style( 'alm-css', alm_url( '/css/alm.css' ) );
		
		wp_enqueue_script( 'alm-default-msg-js', alm_url( '/js/alm-default-msg.js' ), array( 'jquery' ) );
		
		if( $more_css = Alm_Options::$options['more_css'] ) {
			wp_add_inline_style( 'alm-css', $more_css );
		} # end if

		# pass the necessary settings to the JS file
		$a = array(
			'message' => $msg,
			'bgColor' => ( ! empty( Alm_Options::$options['default_msg_bg_color'] ) ? Alm_Options::$options['default_msg_bg_color'] : '#fff' ),
			'textColor' => ( ! empty( Alm_Options::$options['default_msg_text_color'] ) ? Alm_Options::$options['default_msg_text_color'] : '#000' ),
			'domElement' => Alm_Options::$options['dom_element'],
			'prependAppend' => Alm_Options::$options['prepend_or_append'],
		);

		wp_localize_script('alm-default-msg-js', 'AlmData', $a);

	} # end: default_message()
	
	/**
	* Callback for shortcode [alm_alert]
	*
	* @since 1.0.0
	*/
	static function do_alert(){
		
		extract( Alm_Options::$options );
		if( ! $default_msg ) return;

		# start the output buffer
		ob_start();

	?>
		<link href="<?php echo alm_url('/css/alm.css'); ?>" rel='stylesheet'/>
		<?php if( $more_css ) { ?><style><?php echo $more_css; ?></style><?php } ?>

		<div
			id='alm-default-msg'
			style="<?php 
				if($default_msg_text_color) echo 'color: '.$default_msg_text_color.'; '; 
				if($default_msg_bg_color) echo 'background: '.$default_msg_bg_color.'; ';
			?>"		
		><?php echo $default_msg; ?>
		</div>
		<?php

		# return buffer contents
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	
	} # end: do_alert()

	/**
	* Callback for shortcode [alm_countdown]
	*
	* @return 	( string | integer ) 	$days 	Returns day count, or -1 if there was an error
	*
	* @since 1.0.0
	*/
	static function do_countdown() {
		
		# current timestamp
		$now = time();

		# Eastern timezone is UTC -5
		$now = $now - ( 5*60*60 );
		
		# try to get timestamp from date specified in options
		if( ! Alm_Options::$options ) return '-1';

		# start the output buffer
		ob_start();

		extract( Alm_Options::$options );		
		
		$target = strtotime( $countdown_date );		
		
		if( self::$debug ) { 

			echo "You entered: " . $countdown_date."<br />";
			echo "We got: "; var_dump($target); echo "<br />"; 
		
		?>

			<p>Right now: <?php echo date('j M Y h:i:sA', $now); ?></p>		

		<?php

		} # end if
		
		# make sure date is in the future
		$time = $target - $now;

		if( $time <= 0 ) { 
			if( self::$debug ) echo 'Please pick a date in the future';
			echo '-1';
		} # end if

		# get number of days based on number of seconds
		$days = $time / ( 60*60*24 );

		# round up
		$days = ceil( $days );

		# return the day count
		echo '<span id="alm-countdown-timer">' . ceil( $days ) . '</span>';

		# return buffer contents
		$html = ob_get_contents();
		return $html;
	
	} # end: do_countdown()
	
	/**
	 * Helper Functions
	 *
	 * - alert_should_show()
	 * - req_file()
	 * - clean_str_for_url()
	 * - clean_str_for_field()
	 * - get_field_array()
	 * - get_choice_array()
	 */

	/**
	 * Whether or not the alert message should show on the current page
	 *
	 * @since 	1.0.0
	 */
	static function alert_should_show() {

		# if showing on all pages
		if( isset( Alm_Options::$options['show_msg_all_yes'] ) ) return true;

		# if showing on home page
		if( isset( Alm_Options::$options['show_msg_home_yes'] ) ) {

			if( 
				( is_home() && 'posts' == get_option( 'show_on_front' ) )
				|| is_front_page() 
			) {
				return true;
			}
		} # end if: showing on home page

		# if only showing on certain pages
		if( $sIds = Alm_Options::$options['show_msg_page_ids'] ) {

			$aIds = explode( ',', $sIds );

			foreach( $aIds as $id ) {
			
				$id = trim( $id );
			
				if( get_the_id() == $id ) {
					return true;
				} 
			
			} # end foreach : page ID's for message dislay

		} # end if: only showing on certain pages

		return false;

	} # end: alert_should_show()

	/**
	 * Require a file, checking first if it exists
	 *
	 * @param 	string 	$path 	The file path to be required
	 * @since 	1.0.0
	 */
	static function req_file( $path ){ if( file_exists($path) ) require_once $path; }
	
	/**
	 * Return a URL-friendly version of a string ( letters/numbers/hyphens only ), replacing unfriendly chunks with a single dash
	 *
	 * @param 	string 	$input 		The string to clean for URL usage
	 * @return 	string
	 *
	 * @since 	1.0.0
	 */
	static function clean_str_for_url( $input ){

		if( $input == "" ) return "";
		$output = trim( strtolower( $input ) );
		$output = preg_replace( "/\s\s+/" , " " , $output );					
		$output = preg_replace( "/[^a-zA-Z0-9 -]/" , "",$output );	
		$output = preg_replace( "/--+/" , "-",$output );
		$output = preg_replace( "/ +- +/" , "-",$output );
		$output = preg_replace( "/\s\s+/" , " " , $output );	
		$output = preg_replace( "/\s/" , "-" , $output );
		$output = preg_replace( "/--+/" , "-" , $output );
		$word_length = strlen( $output );
		if( $output[ $word_length - 1 ] == "-" ) { $output = substr( $output , 0 , $word_length - 1 ); } 
		return $output;
	
	} # end: clean_str_for_url()

	/**
	 * Return a field-key-friendly version of a string ( letters/numbers/hyphens/underscores only ), replacing unfriendly chunks with a single underscore
	 *
	 * @param 	string 	$input 		The string to clean for field key usage
	 * @return 	string
	 *
	 * @since 	1.0.0
	 */
	static function clean_str_for_field($input){

		if( $input == "" ) return "";
		$output = trim( strtolower( $input ) );
		$output = preg_replace( "/\s\s+/" , " " , $output );					
		$output = preg_replace( "/[^a-zA-Z0-9 -_]/" , "",$output );	
		$output = preg_replace( "/--+/" , "-",$output );
		$output = preg_replace( "/__+/" , "_",$output );
		$output = preg_replace( "/ +- +/" , "-",$output );
		$output = preg_replace( "/ +_ +/" , "_",$output );
		$output = preg_replace( "/\s\s+/" , " " , $output );	
		$output = preg_replace( "/\s/" , "-" , $output );
		$output = preg_replace( "/--+/" , "-" , $output );
		$output = preg_replace( "/__+/" , "_" , $output );
		$word_length = strlen( $output );
		if( $output[ $word_length - 1 ] == "-" || $output[ $word_length - 1 ] == "_" ) { $output = substr( $output , 0 , $word_length - 1 ); } 
		return $output;		

	} # end: clean_str_for_field()

	/**
	 * Generate a label, value, etc. for any given setting 
	 * input can be a string or array and a full, formatted array will be returned
	 * If $field is a string we assume the string is the label
	 * if $field is an array we assume that at least a label exists
	 * optionally, the parent field's name can be passed for better labelling
	 *
	 * @param	(array|string)		$field {
	 *		The key string or field array that we are completing
	 *
	 * 		@type 	string 		$type 		The field type (default: text)
	 * 		@type 	string 		$id			The ID attribute 
	 * 		@type	mixed 		$value		The field value
	 * 		@type 	string 		$label		The label for the field
	 * 		@type 	string 		$name		The input name (default: $id)
	 * 		@type 	array 		$choices	Choices for the field value
	 *
	 * }
	 * @param 	string 	$parent_name 	Added for child fields to identify their parent
	 * @since 	1.0.0
	 */
	static function get_field_array( $field, $parent_name = '' ) {

		$id = $parent_name ? $parent_name.'_' : '';
		if(!is_array($field)){
			$id .= self::clean_str_for_field($field);
			$out = array();
			$out['type'] = 'text';
			$out['label'] = $field;
			$out['value'] = $id;
			$out['id'] .= $id;
			$out['name'] = $id;
		}
		elseif(is_array($field)){
			# do nothing if we don't have a label
			if(!array_key_exists('label', $field)) return $field;
			
			$id .= array_key_exists('name', $field) ? $field['name'] : self::clean_str_for_field($field['label']);
			$out = $field;
			if(!array_key_exists('id', $out)) $out['id'] = $id;
			if(!array_key_exists('name', $out)) $out['name'] = $id;
			# make sure all choices are arrays
			if(array_key_exists('choices', $field)){
				$out['choices'] = self::get_choice_array($field);
			}
		}
		return $out;
	
	} # end: get_field_array()

	/**
	 * Get array of choices for a setting field
	 * This allows choices to be set as strings or arrays with detailed properties, 
	 * so that either way our options display function will have the data it needs
	 *
	 * @param 	array 	$setting 	The field array to get choices for (see get_field_array)
	 * @since 	1.0.0
	 */
	static function get_choice_array( $setting ) {
		
		extract($setting);
		if(!isset($choices)) return;
		$out = array();
		if(!is_array($choices)){
			$out[] = array(
				'id' => $name.'_'.self::clean_str_for_field($choices),
				'label' => $choices, 
				'value' => self::clean_str_for_field($choices)
			);
		}
		else{
			foreach($choices as $choice){
				if(!is_array($choice)){
					$out[] = array(
						'label' => $choice,
						'id' => $name . '_' . self::clean_str_for_field($choice),
						'value' => self::clean_str_for_field($choice)
					);
				}
				else{
					# if choice is already an array, we need to check for missing data
					if(!array_key_exists('id', $choice)) $choice['id'] = $name.'_'.self::clean_str_for_field($choice['label']);
					if(!array_key_exists('value', $choice)) $choice['value'] = $name.'_'.self::clean_str_for_field($choice['label']);
					$out[] = $choice;
				}
			}
		}
		return $out;

	} # end: get_choice_array()

} # end class Alm

# require files for plugin
foreach(Alm::$classes as $class){ Alm::req_file(alm_dir("lib/class-{$class}.php")); }