<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
	class YT_Shortcode_content_slider_config {
	static function get_config() {
	        // content slider
	        return array(
	        	'style' => array(
					'type'    => 'select',
					'default' => 'default',
					'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_STYLE'),
					'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_STYLE_DESC'),
					'values'  => array(
								'default' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_DEFAULT'),
								'dark'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_DARK'),
								'light'   => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_LIGHT')
							),
					'child'   => array(
									'items' => array(
										'type'    => 'slider',
										'min'     => 1,
										'max'     => 10,
										'step'    => 1,
										'default' => 5,
										'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ITEMS'),
										'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ITEMS_DESC')
									),
									'margin' => array(
									'type'    => 'slider',
									'min'     => 0,
									'max'     => 80,
									'step'    => 5,
									'default' => 10,
									'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_MARGIN'),
									'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CAROUSEL_MARGIN_DESC')
								),     
							),
				),
                'transitionin' => array(
                    'type'    => 'select',
                    'values'  => array_combine( YT_Data::animations_in(), YT_Data::animations_in() ),
                    'default' => 'fadeIn',
                    'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_TRANSITION_IN'),
                    'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_TRANSITION_IN_DESC'),
                    'child'   => array(
                        'transitionout' => array(
                            'type'    => 'select',
                            'values'  => array_combine( YT_Data::animations_out(), YT_Data::animations_out() ),
                            'default' => 'fadeOut',
                            'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_TRANSITION_OUT'),
                            'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_TRANSITION_OUT_DESC')
                        )
                    )
                ),       
                'arrows' => array(
                    'type'    => 'bool',
                    'default' => 'yes',
                    'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ARROWS'),
                    'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ARROWS_DESC'),
                    'child'   => array(
                        'arrow_position' => array(
                            'type' => 'select',
                            'values' => array(
                                'arrow-default'     => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_DEFAULT'),
                                'arrow-top-left'     => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_TOP_LEFT'),
                                'arrow-top-right'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_TOP_RIGHT'),
                                'arrow-bottom-left'  => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_BOTTOM_LEFT'),
                                'arrow-bottom-right' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_BOTTOM_RIGHT')
                            ),
                            'default' => 'arrow-default',
                            'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ARROW_POSITION'),
                            'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ARROW_POSITION_DESC')
                        )
                    )
                ),
                
                'pagination' => array(
                    'type'    => 'bool',
                    'default' => 'no',
                    'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_PAGINATION'),
                    'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_PAGINATION_DESC'),
                    'child'   => array(
                        'autoplay' => array(
                            'type'    => 'bool',
                            'default' => 'yes',
                            'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_AUTOPLAY'),
                            'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_AUTOPLAY_DESC')
                        ),
                        'autoheight' => array(
                            'type'    => 'bool',
                            'default' => 'no',
                            'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_AUTOHEIGHT'),
                            'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_AUTOHEIGHT_DESC')
                        )
                    )
                ),
                
                'hoverpause' => array(
                    'type'    => 'bool',
                    'default' => 'yes',
                    'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_HOVERPAUSE'),
                    'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_HOVERPAUSE_DESC'),
                    'child'   => array(
                        'lazyload' => array(
                            'type'    => 'bool',
                            'default' => 'no',
                            'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_LAZYLOAD'),
                            'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_LAZYLOAD_DESC')
                        ),
                        'loop' => array(
                            'type'    => 'bool',
                            'default' => 'yes',
                            'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_LOOP'),
                            'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_LOOP_DESC')
                        ),
                    )
                ),
                
                'speed' => array(
                    'type'    => 'slider',
                    'min'     => 0.1,
                    'max'     => 15,
                    'step'    => 0.2,
                    'default' => 0.6,
                    'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_SPEED'), 
                    'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_SPEED_DESC'),
                    'child'   => array(
                        'delay' => array(
                            'type'    => 'slider',
                            'min'     => 1,
                            'max'     => 10,
                            'step'    => 1,
                            'default' => 4,
                            'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_DELAY'), 
                            'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_DELAY_DESC')
                        )
                    )
                ),
	        );
	    }
	}

?>