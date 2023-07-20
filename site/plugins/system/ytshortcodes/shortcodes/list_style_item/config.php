<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
	class YT_Shortcode_list_style_item_config {
	static function get_config() {
	        // list style item
	        return array(
	        	'content'  => array(
		        	'type' => 'textarea',
					'default' => 'Add content here',
					'name' => 'Content',
				),
	        );
	    }
	}

?>