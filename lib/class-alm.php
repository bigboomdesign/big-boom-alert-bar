<?php
class Alm{
	# set to true for debugging
	static $debug = false;
	static $classes = array("alm-options");

	/**
	* Back end
	**/
	static function admin_enqueue(){
		$screen = get_current_screen();
		if($screen->id != 'settings_page_alm_settings') return;
		wp_enqueue_style('alm-admin-css', alm_url('/css/alm-admin.css'));
		wp_enqueue_style('alm-iris-css', alm_url('/assets/iris/iris.min.css'));
		wp_enqueue_script('alm-options-js', alm_url('/js/admin/plugin-options.js'), array('jquery', 'alm-iris-js'));
		wp_enqueue_media();
		wp_enqueue_script('alm-jquery-ui-js', alm_url('/assets/iris/jquery-ui.js'), array('jquery', 'media-views'));
		wp_enqueue_script('alm-iris-js', alm_url('/assets/iris/iris.min.js'), array('jquery', 'alm-jquery-ui-js'));	
	}
	
	/**
	* Front end
	**/
	
	# enqueue scripts
	static function enqueue(){}
	# default message
	static function default_message(){
		# Check if we have a message in the system
		if(!($msg = Alm_Options::$options['default_msg'])) return;
		# Check if we need to show the message on this page
		$bShow = false;
		# if showing on all pages
		if(isset(Alm_Options::$options['show_msg_all_yes'])) $bShow = true;
		# if only showing on certain pages
		elseif($sIds = Alm_Options::$options['show_msg_page_ids']){
			$aIds = explode(',', $sIds);
			foreach($aIds as $id){
				$id = trim($id);
				if(get_the_id() == $id){
					$bShow = true;
					break;
				}
			}
		}
		if(!$bShow) return;
		wp_enqueue_style('alm-css', alm_url('/css/alm.css'));
		wp_enqueue_script('alm-default-msg-js', alm_url('/js/alm-default-msg.js'), array('jquery'));
	
		# pass the message to the JS file
		$a = array(
			'message' => $msg,
			'bgColor' => Alm_Options::$options['default_msg_bg_color'] ? Alm_Options::$options['default_msg_bg_color'] : '#fff',
			'textColor' => Alm_Options::$options['default_msg_text_color'] ? Alm_Options::$options['default_msg_text_color'] : '#000',
			'domElement' => Alm_Options::$options['dom_element'] ? Alm_Options::$options['dom_element'] : 'body',
		);
		wp_localize_script('alm-default-msg-js', 'AlmData', $a);	
	}
	# shortcode [alm_alert]
	function do_alert(){
		extract(Alm_Options::$options);
		if(!$default_msg) return;
	?>
		<link href="<?php echo alm_url('/css/alm.css'); ?>" rel='stylesheet'/>
		<?php if($more_css){ ?><style><?php echo $more_css; ?></style><?php } ?>
		<div 
			id='alm-default-msg'
			style="<?php 
				if($default_msg_text_color) echo 'color: '.$default_msg_text_color.'; '; 
				if($default_msg_bg_color) echo 'background: '.$default_msg_bg_color.'; ';
			?>"
		
		><?php echo $default_msg; ?></div>
	<?php
	}
	# shortcode [alm_countdown]
	static function do_countdown(){
		# current timestamp
		$now = time();
		# Eastern timezone is UTC -5
		$now = $now - (5*60*60);
		
		# try to get timestamp from date specified in options
		if(!Alm_Options::$options) return '-1';
		extract(Alm_Options::$options);		
		$target = strtotime($countdown_date);		
		if(self::$debug){ 
			echo "You entered: " . $countdown_date."<br />";
			echo "We got: "; var_dump($target); echo "<br />"; 
		?>
			<p>Right now: <?php echo date('j M Y h:i:sA', $now); ?></p>		
		<?php
		}
		# make sure date is in the future
		$time = $target - $now;
		if($time <= 0 ){ 
			if(self::$debug) echo 'Please pick a date in the future';
			return "-1";
		}
		# get number of days based on number of seconds
		$days = $time/(60*60*24);
		# round up
		$days = ceil($days);
		# return the day count
		return ceil($days);
	}
	
	/**
	* Helper Functions
	**/

	# require a file, checking first if it exists
	static function req_file($path){ if(file_exists($path)) require_once $path; }
	
	# return a permalink-friendly version of a string
	static function clean_str_for_url( $sIn ){
		if( $sIn == "" ) return "";
		$sOut = trim( strtolower( $sIn ) );
		$sOut = preg_replace( "/\s\s+/" , " " , $sOut );					
		$sOut = preg_replace( "/[^a-zA-Z0-9 -]/" , "",$sOut );	
		$sOut = preg_replace( "/--+/" , "-",$sOut );
		$sOut = preg_replace( "/ +- +/" , "-",$sOut );
		$sOut = preg_replace( "/\s\s+/" , " " , $sOut );	
		$sOut = preg_replace( "/\s/" , "-" , $sOut );
		$sOut = preg_replace( "/--+/" , "-" , $sOut );
		$nWord_length = strlen( $sOut );
		if( $sOut[ $nWord_length - 1 ] == "-" ) { $sOut = substr( $sOut , 0 , $nWord_length - 1 ); } 
		return $sOut;
	}
	# same as above, but allow underscores
	static function clean_str_for_field($sIn){
		if( $sIn == "" ) return "";
		$sOut = trim( strtolower( $sIn ) );
		$sOut = preg_replace( "/\s\s+/" , " " , $sOut );					
		$sOut = preg_replace( "/[^a-zA-Z0-9 -_]/" , "",$sOut );	
		$sOut = preg_replace( "/--+/" , "-",$sOut );
		$sOut = preg_replace( "/__+/" , "_",$sOut );
		$sOut = preg_replace( "/ +- +/" , "-",$sOut );
		$sOut = preg_replace( "/ +_ +/" , "_",$sOut );
		$sOut = preg_replace( "/\s\s+/" , " " , $sOut );	
		$sOut = preg_replace( "/\s/" , "-" , $sOut );
		$sOut = preg_replace( "/--+/" , "-" , $sOut );
		$sOut = preg_replace( "/__+/" , "_" , $sOut );
		$nWord_length = strlen( $sOut );
		if( $sOut[ $nWord_length - 1 ] == "-" || $sOut[ $nWord_length - 1 ] == "_" ) { $sOut = substr( $sOut , 0 , $nWord_length - 1 ); } 
		return $sOut;		
	}	
	# Generate a label, value, etc. for any given setting 
	## input can be a string or array and a full, formatted array will be returned
	## If $field is a string we assume the string is the label
	## if $field is an array we assume that at least a label exists
	## optionally, the parent field's name can be passed for better labelling
	static function get_field_array( $field, $parent_name = ''){
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
	}
	# Get array of choices for a setting field
	## This allows choices to be set as strings or arrays with detailed properties, 
	## so that either way our options display function will have the data it needs
	static function get_choice_array($setting){
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
	}
} # end class Alm

# require files for plugin
foreach(Alm::$classes as $class){ Alm::req_file(alm_dir("lib/class-{$class}.php")); }