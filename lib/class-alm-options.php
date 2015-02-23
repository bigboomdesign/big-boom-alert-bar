<?php
class Alm_Options{
	# Static variables are set after class definition
	## available settings
	static $sections;
	static $settings;
	static $default_section = 'alm_alerts';
	
	## saved options
	static $options = array();

	# Display field input
	static function do_settings_field($setting){
		$setting = Alm::get_field_array($setting);	
		# call one of several functions based on what type of field we have
		switch($setting['type']){
			case "textarea":
				self::textarea_field($setting);
			break;
			case "checkbox":
				self::checkbox_field($setting);
			break;
			case "single-image":
				self::image_field($setting);
			break;
			default: self::text_field($setting);
		}
		if(array_key_exists('description', $setting)) {
		?>
			<p class='description'><?php echo $setting['description']; ?></p>
		<?php
		}
	}
	## Text field
	static function text_field($setting){
		extract($setting);
		?><input 
			id="<?php echo $name; ?>" 
			name="alm_options[<?php echo $name; ?>]" 
			class="regular-text <?php if(array_key_exists('class', $setting)) echo $setting['class']; ?>" 
			type='text' value="<?php echo self::$options[$name]; ?>" />
		<?php	
	}
	## Textarea field
	static function textarea_field($setting){
		extract($setting);
		?><textarea id="<?php echo $name; ?>" name="alm_options[<?php echo $name; ?>]" cols='40' rows='7'><?php echo self::$options[$name]; ?></textarea>
		<?php
	}
	## Checkbox field
	static function checkbox_field($setting){
		extract($setting);
		foreach($choices as $choice){
		?><label class='checkbox' for="<?php echo $choice['id']; ?>">
			<input 
				type='checkbox'
				id="<?php echo $choice['id']; ?>"
				name="alm_options[<?php echo $choice['id']; ?>]"
				value="<?php echo $choice['value']; ?>"
				class="<?php if(array_key_exists('class', $setting)) echo $setting['class']; ?>"
				<?php checked(true, array_key_exists($choice['id'], self::$options)); ?>						
			/>&nbsp;<?php echo $choice['label']; ?> &nbsp; &nbsp;
		</label>
		<?php
		}
	}	
	# Image field
	static function image_field($setting){
		# this will set $name for the field
		extract($setting);
		# current value for the field
		$value = self::$options[$name];		
		?><input 
			type='text'
			id="<?php echo $name; ?>" 
			class="regular-text text-upload <?php if(array_key_exists('class', $setting)) echo $setting['class']; ?>"
			name="alm_options[<?php echo $name; ?>]"
			value="<?php if($value) echo esc_url( $value ); ?>"
		/>		
		<input 
			id="media-button-<?php echo $name; ?>" type='button'
			value='Choose/Upload image'
			class=	'button button-primary open-media-button single'
		/>
		<div id="<?php echo $name; ?>-thumb-preview" class="alm-thumb-preview">
			<?php if($value){ ?><img src="<?php echo $value; ?>" /><?php } ?>
		</div>
		<?php
	}
	# Register settings
	static function register_settings(){
		register_setting( 'alm_options', 'alm_options', array('Alm_Options','options_validate'));
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
	}
	# Do settings page
	static function settings_page(){
		?><div>
			<h2>Alerts & Messages</h2>
			<form action="options.php" method="post">
			<?php settings_fields('alm_options'); ?>
			<?php do_settings_sections('alm_settings'); ?>
			<?php submit_button(); ?>
			</form>
		</div><?php
	}
	# Section description
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
	}
	static function options_validate($input){return $input;}
}
# Initialize static variables
## generate all settings for backend
Alm_Options::$sections = array(
	array('name' => 'alm_alerts', 'title' => 'Alerts'),
	array('name' => 'alm_countdown', 'title' => 'Countdown Timer',
		'description' => '<p>Use the shortcode <kbd>[alm_countdown]</kbd> to insert the countdown.</p> 
			<p>If you get the result <kbd>-1</kbd> this means we were unable to successfully produce a timestamp from your input.</p>'
	),
);
Alm_Options::$settings = array(
	# Alerts
	array(
		'name' => 'show_msg_all', 'label' => 'Show at the top of every page', 'type' => 'checkbox',
		'choices' => 'Yes'
	),
	array(
		'name' => 'show_msg_page_ids', 'label' => 'Show only on these pages',
		'description' => 'Enter a comma-separated list of page/post ID\'s'
	),	
	array(
		'name' => 'default_msg', 'label' => 'Default message', 'type' => 'textarea',
		'description' => 'You can use HTML in your message'
	),
	array('name' => 'default_msg_bg_color', 'label' => 'Background Color', 'class' => 'color-picker'),
	array('name' => 'default_msg_text_color', 'label' => 'Text Color', 'class' => 'color-picker'),
	# Countdown
	array('name' => 'countdown_date', 'type' => 'text', 'label' => 'Target date',
		'description' => 'Enter a date in a format like <b>January 1, 1970</b>',
		'section' => 'alm_countdown',
	)
);
## get saved options
Alm_Options::$options = get_option('alm_options');