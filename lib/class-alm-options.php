<?php
/**
 * Handles the display, saving, and init/retrieval of options for the plugin
 * Static parameters are initialized after class definition below
 *
 * @since 1.0.0
 */
class Alm_Options{

	/**
	 * The available settings for the plugin. Initialized in this file, below class definition
	 *
	 * See `do_settings_field()` for a description of a typical element of the array
	 * 
	 * @param 	array 	$settings{
	 *
	 *		@type array ...,
	 * 		@type array ...,
	 *			...
	 * }
	 * @since 	1.0.0
 	 */
	static $settings;

	/**
	 * The sections to display on the plugin settings page
	 * Used for WP's `add_settings_section()` and in the corresponding callback function
	 *
	 * @param	array	$sections{
	 *
	 * 		@type 	string	$name 			Optional. The section named used in `add_settings_section()` (Default: self::$default_section )
	 *		@type 	string 	$title 			Optional. The title displayed in the section's header
	 * 		@type 	string 	$description 	Optional. A description for the section
	 * }
	 * @since 	1.0.0
	 */
	static $sections;

	/**
	 * The default section to use for a setting if none is specified
	 *
	 * @param 	string
	 * @since 	1.0.0
	 */
	static $default_section = 'alm_default_alert';
	
	/**
	 * Options saved by the user.
	 *
	 * @param 	array 
	 * @since 	1.0.0
	 */
	static $options = array();


	/**
	 * Class methods 
	 */


	/**
	 * Display a plugin settings form element
	 *
	 * @param 	string|array 	$setting{
	 *
	 *		Use a string for simple fields. Use an array to pass detailed information about the
	 *		setting.  Optional types will be auto-completed via `Alm::get_field_array()`
	 *
	 *		@type 	string 			$label 			Required. The label for the form element	 
	 * 		@type 	string 			$name 			Optional. The HTML name attribute. Will be auto-generated from label if empty
	 * 		@type 	string 			$id 			Optional. The HTML `id` attribute for the form element. Will be auto-generated from label if empty
	 *		@type 	string 			$type 			Optional. The type of form element to display (text|textarea|checkbox|select|single-image|radio) (Default: 'text')
	 *												Use a custom $type and define a method on `self` with the same name to automatically link the field display handler
	 * 		@type 	string 			$value 			Optional. The value of the HTML `value` attribute
	 * 		@type 	array|string 	$choices 		Optional. The choices for the form element (for select, radio, checkbox)
	 * 		@type	string			$class 			Optional. The HTML `class` attribute for the form element
	 * 		@type 	string			$label_class	Optional. For checkboxes and radio buttons, a class can be applied to each choice's label
	 * 		@type 	array 			$data 			Optional. An array of data attributes to add to the form element (see `self::data_atts()`)
	 * }
	 *
	 * @param	string	$option 	Optional (Default: 'alm_options'). By default, an HTML input element whose name is `form_field`
	 *								will actually have a name attribute of `alm_options[form_field]`. Pass in a string to 
	 *								change the default parent field name, or pass an empty string to use a regular input name without a parent
	 * @since 	1.0.0
	 */
	public static function do_settings_field($setting, $option = 'alm_options'){
		# the option `alm_options` can be replaced on the fly and will be passed to handler functions
		$setting['option'] = $option;
		
		# fill out missing attributes for this option and its choices
		$setting = Alm::get_field_array($setting);

		# the arrayed name of this setting, such as `alm_options[my_setting]`
		$setting['option_name'] = (
			$option ? $option.'['.$setting['name'].']' : $setting['name']
		);
				
		# call one of several handler functions based on what type of field we have
		
		## see if a self method is defined having the same name as the setting type
		if( isset($setting['type'] ) && method_exists( get_class(), $setting['type'] ) ) self::$setting['type']($setting);
		else if(!isset($setting['type'])) $setting['type'] = 'text';
		
		## special cases
		switch($setting['type']){
			case "textarea":
				self::textarea_field($setting);
			break;
			case 'checkbox':
				self::checkbox_field($setting);
			break;
			case 'select':
				self::select_field($setting);
			break;
			case 'radio':
				self::radio_field($setting);
			break;			
			case "single-image":
				self::image_field($setting);
			break;
			default: self::text_field($setting);
		} # end switch: setting type
		
		if(array_key_exists('description', $setting)) {
		?>
			<p class='description'><?php echo $setting['description']; ?></p>
		<?php
		}

	} # end: do_settings_field()

	/**
	 * Display a text input element
	 *
	 * @param 	array 	$setting 	See `do_settings_field()`. Has been filtered through `Alm::get_field_array()`
	 * @since 	1.0.0
	 */
	public static function text_field($setting){
		extract($setting);
		$val = self::get_option_value($setting);
		?><input 
			id="<?php echo $name; ?>" name="<?php echo $setting['option_name']; ?>" 
			class="regular-text<?php if(isset($class)) echo ' ' . $class; ?>" type='text' value="<?php echo $val; ?>"
			<?php echo self::data_atts($setting); ?>
		/>

		<?php	
	} # end: text_field()

	/**
	 * Display a textarea element
	 * @param 	array 	$setting 	See `do_settings_field()`. Has been filtered through `Alm::get_field_array()`
	 * @since 	1.0.0
	 */
	public static function textarea_field($setting){
		extract($setting);
		$val = self::get_option_value($setting);	
		?><textarea 
			id="<?php echo $name; ?>" name="<?php echo $setting['option_name']; ?>" 
			class="<?php if(isset($class)) echo $class; ?>"			
			cols='40' rows='7'
			<?php echo self::data_atts($setting); ?>
		><?php echo $val; ?></textarea>
		<?php
	} # end: textarea_field()

	/**
	 * Display one or more checkboxes
	 * @param 	array 	$setting 	See `do_settings_field()`. Has been filtered through `Alm::get_field_array()`
	 * @since 	1.0.0
	 */
	public static function checkbox_field($setting){
		extract($setting);
		foreach($choices as $choice){
		?><label 
			class="checkbox <?php if(isset($label_class)) echo $label_class; ?>"
			for="<?php echo $choice['id']; ?>"
		>
			<input 
				type='checkbox'
				id="<?php echo $choice['id']; ?>"
				name="<?php echo self::get_choice_name($setting, $choice); ?>"
				value="<?php echo $choice['value']; ?>"
				class="<?php if(isset($class)) echo $class; if(array_key_exists('class', $choice)) echo ' ' . $choice['class']; ?>"
				<?php echo self::data_atts($choice); ?>
				<?php checked(true, '' != self::get_option_value($setting, $choice)); ?>						
			/>&nbsp;<?php echo $choice['label']; ?> &nbsp; &nbsp;
		</label>
		<?php
		}
	} # end: checkbox_field()

	/**
	 * Display a group of radio buttons
	 * @param 	array 	$setting 	See `do_settings_field()`. Has been filtered through `Alm::get_field_array()`
	 * @since 	1.0.0
	 */
	public static function radio_field($setting){
		extract($setting);
		$val = self::get_option_value($setting);
		foreach($choices as $choice){
				$label = $choice['label']; 
				$value = $choice['value'];
			?><label 
				class="radio <?php if(isset($label_class)) echo $label_class; ?>"
				for="<?php echo $choice['id']; ?>"
			>
				<input type="radio" id="<?php echo $choice['id']; ?>" 
				name="<?php echo $setting['option_name']; ?>" 
				value="<?php echo $value; ?>"
				class="<?php if(isset($class)) echo $class; if(array_key_exists('class', $choice)) echo ' ' . $choice['class']; ?>"
				<?php echo self::data_atts($choice); ?>				
				<?php checked($value, $val); ?>
			/>&nbsp;<?php echo $label; ?></label>&nbsp;&nbsp;
			<?php
		}
	} # end: radio_field()

	/**
	 * Display a <select> dropdown element
	 * @param 	array 	$setting 	See `do_settings_field()`. Has been filtered through `Alm::get_field_array()`
	 * @since 	1.0.0
	 */
	public static function select_field($setting){		
		extract($setting);
		$val = self::get_option_value($setting);
	?><select 
		id="<?php echo $name; ?>"
		name="<?php echo $setting['option_name']; ?>"
		<?php echo self::data_atts($setting); ?>		
		<?php if(isset($class)) echo "class='".$class."'"; ?>
	>
		<?php 
		foreach($choices as $choice){
			# if $choice is a string
			if(is_string($choice)){
				$label = $choice;
				$value = Alm::clean_str_for_field($choice);
			}
			# if $choice is an array
			elseif(is_array($choice)){
				$label = $choice['label'];
				$value = isset($choice['value']) ? $choice['value'] : Alm::clean_str_for_field($choice['label']);
			}
		?>
			<option 
				value="<?php echo $value; ?>"
				<?php if(array_key_exists('class', $choice)) echo "class='".$choice['class']."' "; ?>
				<?php echo self::data_atts($choice); ?>					
				<?php selected($val, $value ); ?>					
			><?php echo $label; ?></option>
		<?php
		} # end foreach: $choices
		?>
		
	</select><?php
	} # end: select_field()

	/**
	 * Display an image upload element that uses the WP Media browser
	 * @param 	array 	$setting 	See `do_settings_field()`. Has been filtered through `Alm::get_field_array()`
	 * @since 	1.0.0
	 */
	public static function image_field($setting){
		# this will set $name for the field
		extract($setting);
		$val = self::get_option_value($setting);
		# current value for the field
		?><input 
			type='text'
			id="<?php echo $name; ?>" 
			class="regular-text text-upload <?php if( ! empty( $class ) ) echo $class; ?>"
			name="<?php echo $setting['option_name']; ?>"
			value="<?php if($val) echo esc_url( $val ); ?>"
		/>		
		<input 
			id="media-button-<?php echo $name; ?>" type='button'
			value='Choose/Upload image'
			class=	'button button-primary open-media-button single'
		/>
		<div id="<?php echo $name; ?>-thumb-preview" class="alm-thumb-preview">
			<?php if($val){ ?><img src="<?php echo $val; ?>" /><?php } ?>
		</div>
		<?php
	} # end: image_field()

	/**
	 * Return a string of HTML data attributes for a field or choice input element
	 *
	 * @param	array 	$setting{
	 *		A $setting array (see `do_settings_field()`) or a $choice array, which ostensibly
	 * 		has a `data` key with corresponding hash of data attributes
	 *
	 *		@type array  $data{
	 *			Any key/value pair you like can be added to this array when defining settings
	 *
	 *			@type string $var 	A value to be added for the HTML data attribute `data-var`
	 * 		}
	 * @return 	string
	 * @since 	1.0.0
	 */
	 public static function data_atts($setting){
		if(!array_key_exists('data', $setting)) return;
		$out = '';
		foreach($setting['data'] as $k => $v){
			$out .= "data-{$k}='{$v}' ";
		}
		return $out;
	} # end: data_atts()

	/**
	 * Registers the main option to be stored in the database and adds its sections and fields
	 *
	 * @since 	1.0.0
	 */
	static function register_settings(){

		register_setting( 'alm_options', 'alm_options', array('Alm_Options','validate_options'));
		# add sections
		foreach(Alm_Options::$sections as $section){
			add_settings_section(
				$section['name'], $section['title'], array('Alm_Options', 'section_description'), 'alm_settings'
			);
		}
		# add fields
		foreach(Alm_Options::$settings as $setting){
			add_settings_field($setting['name'], $setting['label'], array('Alm_Options', 'do_settings_field'), 'alm_settings', 
				array_key_exists('section', $setting) ? $setting['section'] : self::$default_section, $setting
			);
		}	
	} # end: register_settings()

	/**
	 * Generate HTML for the plugin settings page
	 */
	static function settings_page(){
		?><div>
			<h2>Alerts & Messages</h2>
			<form action="options.php" method="post">
			<?php settings_fields('alm_options'); ?>
			<?php do_settings_sections('alm_settings'); ?>
			<?php submit_button(); ?>
			</form>
		</div><?php

	} # end: settings_page()

	/**
	 * Display the description for a setting (callback for WP's `add_settings_section`)
	 *
	 * @since 	1.0.0
 	 */
	static function section_description($section){
		# get ID of section being displayed
		$id = $section['id'];
		# loop through sections and display the correct description
		foreach(self::$sections as $section){
			if($section['name'] == $id && array_key_exists('description', $section)){
				echo $section['description'];
				break;
			}
		}

	} # end: section_description()

	/**
	 * Validate fields when saved (callback for WP's `register_setting`)
	 *
	 * @since 	1.0.0
	 */
	static function validate_options( $input ){ return $input; }

	/**
	 * Helper Functions
	 *
	 * - get_option_value()
	 * - get_choice_name()
	 */
	
	/**
	 * Get the saved value for a setting, based on the option name we're given
	 * 
	 * @param  	array 	$setting 	The setting to get the value for (see `do_settings_field`)
	 * @param  	array 	$choice 	The particular choice to get the value for if necessary
	 * @return 	string
	 * @since 	1.0.0
	 */	
	public static function get_option_value($setting, $choice = ''){
		# see if an option has been passed in (e.g. `alm_options`)
		if($setting['option']){
			# if we're dealing with the default 
			if('alm_options' == $setting['option']){
				$option = self::$options;
			}
			
			# if we have a custom option name or no option name
			else $option = get_option($setting['option']);
			if(!$option) return '';
			
			# if the option value is an array, get the desired setting
			if(is_array($option)){
				return array_key_exists($setting['name'], $option) 
					? $option[$setting['name']] 
					: (
						'' != $choice 
							?
							(
								array_key_exists($choice['id'], $option)
									? $option[$choice['id']]
									: ''
							)
							: ''
					);
			}
			# if option value is a string
			return $option;
		}
		# if no option is passed in, check post
		if( isset( $_POST[ $setting['name'] ] ) )
			return sanitize_text_field( $_POST[ $setting[ 'name' ] ] );
		return '';

	} # end: get_option_value()

	/**
	 * Get the name attribute for a checkbox choice based on its parent option
	 *
	 * @param 	array 	$setting 	The parent setting (see `do_settings_field()`)
	 * @param 	array 	$choice 	The choice to get the name attribute for
	 * @return 	string
	 * @since 	1.0.0
	 */
	public static function get_choice_name($setting, $choice){
		if( ! $setting['option'] ) return $choice['id'];
		return $setting['option'] . '[' . $choice['id'] . ']';
	}

}

/**
 * Initialize static variables
 */

# Set up the settings sections for the backend
Alm_Options::$sections = array(
	array('name' => 'alm_default_alert', 'title' => 'Default Alert'),
	array('name' => 'alm_default_advanced', 'title' => 'Default Alert: Advanced Options'),	
	array('name' => 'alm_countdown', 'title' => 'Countdown Timer',
		'description' => '<p>Use the shortcode <kbd>[alm_countdown]</kbd> to insert the countdown.</p> 
			<p>If you get the result <kbd>-1</kbd> this means we were unable to successfully produce a timestamp from your input.</p>'
	),
);

# Set up the available settings fields for the backend
Alm_Options::$settings = array(
	# Default Alert
	array(
		'name' => 'show_msg_all', 'label' => 'Insert at the top of every page', 'type' => 'checkbox',
		'choices' => 'Yes',
		'description' => 'Please note that if you have fixed-position elements at the top of the &lt;body&gt;, '
			. 'your alert may not be visible. To bypass our auto-insertion and insert the alert into a location of your choosing '
			. 'on a single page, post, or widget, you can just use the shortcode <code><b>[alm_alert]</b></code>.'
	),
	array(
		'name' => 'show_msg_home', 'label' => 'Show message on home page', 'type' => 'checkbox',
		'choices' => 'Yes',
		'description' => 'If not showing on all pages, check here to show the alert on the home page.'
	),	
	array(
		'name' => 'show_msg_page_ids', 'label' => 'Insert on these pages only',
		'description' => 'Enter a comma-separated list of page/post ID\'s where the alert should be shown.'
	),
	array(
		'name' => 'default_msg', 'label' => 'Default message', 'type' => 'textarea',
		'description' => 'You can use HTML in your message'
	),
	array('name' => 'default_msg_bg_color', 'label' => 'Background Color', 'class' => 'color-picker'),
	array('name' => 'default_msg_text_color', 'label' => 'Text Color', 'class' => 'color-picker'),
	
	# Advanced Options
	array('name' => 'more_css', 'label' => 'Additional CSS', 'type' => 'textarea',
		'description' => 'Type any additional CSS you wish.  Note that the default alert has a container '
			. 'of the form <code><b>div#alm-default-msg</b></code>',
		'section' => 'alm_default_advanced'
	),
	array('name' => 'dom_element', 'label' => 'Insert into this DOM element',
		'description' => 'By default, the alert is prepended to the <code>&lt;body&gt;</code> tag.  If you\'d like the alert to be ' 
			. 'inserted elsewhere, specify a valid DOM element with a unique ID like <code><b>#my_div</b></code>.',
		'section' => 'alm_default_advanced'			
	),
	# Countdown
	array('name' => 'countdown_date', 'type' => 'text', 'label' => 'Target date',
		'description' => 'Enter a date in a format like <b>January 1, 1970</b>',
		'section' => 'alm_countdown',
	)
);

# Get saved options
Alm_Options::$options = get_option('alm_options');
