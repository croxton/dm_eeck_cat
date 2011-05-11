<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * DM EECK cat v1 for Expression Engine 2
 * Author: Mark Croxton, Hallmark Design (mcroxton@hallmark-design.co.uk)
 */
class Dm_eeck_cat_acc {

	public $name = 'DM EECK Category Description field';
	public $id = 'dm_eeck_cat_description';
	public $version = '1.0';
	public $description = 'Replace the category description field with a CKEditor instance';
	public $sections = array();
	private $eeck_settings;

	// -----------------------------------------------------------------

	/**
	 * Constructor
	 */
	function Dm_eeck_cat_acc() {
		$this->EE =& get_instance();
	}

	// -----------------------------------------------------------------

	/**
	 * Replace the Category Description field with a CKEditor instance
	 */
	function set_sections() {
		
		$this->sections[] = '<script type="text/javascript" charset="utf-8">$("#accessoryTabs a.'.$this->id.'").parent().remove();</script>';

		if($this->EE->input->get('C') == 'admin_content' && $this->EE->input->get('M') == 'category_edit' ) {

			// help!
			require_once(PATH_THIRD.'dm_eeck/includes/eeck_helper.php');
			$this->helper = new eeck_helper();

			// we'll need the settings from the Editor field type
			$this->eeck_settings = $this->helper->load_editor_settings();

			$this->helper->include_editor_js($this->eeck_settings['eeck_ckepath'], $this->eeck_settings['eeck_ckfpath']);
			
			// get our settings for this field
			$myConf = $this->eeck_settings['eeck_config_settings'];
			
			// load out config file
			$conf = $this->load_config_file($myConf);
			if($conf != '') $conf .= ',';

			// add on integration for CK finder
			$conf .= $this->integrate_ckfinder($this->eeck_settings['eeck_ckfpath'],'Images','Files','Flash');

			$str = 'CKEDITOR.replace( "cat_description",{'.$conf.'});';
			$this->sections[] = '<script type="text/javascript" charset="utf-8">'.$str.'</script>';
		}
	}
	
	// -----------------------------------------------------------------

	/**
	 * Load CK Editor config settings
	 *
	 * @param string $file
	 * @return array
	 */
	private function load_config_file($file) {

		$file = PATH_THIRD.'dm_eeck/config/'.$file;

		if(file_exists($file)) {
			require($file);
			if(isset($editor_config)) {
				return $editor_config;
			}
		}

		return '';
	}
	
	// -----------------------------------------------------------------

	/**
	 * Generate code to integrate CK Finder with CK Editor
	 *
	 * @return string
	 */
	private function integrate_ckfinder($path,$images = 'Images', $files = '', $flash = 'Flash') {

		$str = '';
		if($images != false) {
			$i1 = ($images != '') ? '?Type='.$images : '';
			$i2 = ($images != '') ? '&type='.$images : '';

			$str .= 'filebrowserImageBrowseUrl: "'.$path.'ckfinder.html'.$i1.'",
					filebrowserImageUploadUrl: "'.$path.'core/connector/php/connector.php?command=QuickUpload'.$i2.'"';
		}

		if($files != false) {
			$f1 = ($files != '') ? '?Type='.$files : '';
			$f2 = ($files != '') ? '&type='.$files : '';

			if($str != '') $str .= ',';
			$str .= 'filebrowserBrowseUrl: "'.$path.'ckfinder.html'.$f1.'",
					filebrowserUploadUrl: "'.$path.'core/connector/php/connector.php?command=QuickUpload'.$f2.'"';
		}

		if($flash != false) {

			$v1 = ($flash != '') ? '?Type='.$flash : '';
			$v2 = ($flash != '') ? '&type='.$flash : '';

			if($str != '') $str .= ',';
			$str .= 'filebrowserFlashBrowseUrl: "'.$path.'ckfinder.html'.$v1.'",
					filebrowserFlashUploadUrl: "'.$path.'core/connector/php/connector.php?command=QuickUpload'.$v2.'"';
		}

		// make sure the session vars are all set
		$this->helper->save_session_vars($this->eeck_settings);

		return $str;
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Establish the correct resource location string
	 * for CK finder integration
	 *
	 * @param string $field
	 * @param string $default
	 * @return string 
	 */
	private function get_resource_value($field,$default) {

		if(!isset($this->eeck_settings[$field])) {
			return false;
		}

		switch($this->eeck_settings[$field]) {

			case 'EECK_DEFAULT':
				return $default;
				break;

			default:
				return $this->eeck_settings[$field];
				break;
		}

	}
}