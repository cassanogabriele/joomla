<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function content_sliderYTShortcode($atts,$content = null)
{
	extract(ytshortcode_atts(array(
		'items'            => 3,
		'style'          => 'default',
		'transitionin'   => 'fadeIn',
		'transitionout'  => 'fadeOut',
		'arrows'         => 'yes',
		'arrow_position' => 'arrow-default',
		'pagination'     => 'no',
		'autoplay'       => 'yes',
		'autoheight'     => 'yes',
		'delay'          => 4,
		'speed'          => 0.6,
		'hoverpause'     => 'no',
		'lazyload'       => 'no',
		'loop'           => 'yes',
		'margin'         => 10,
		'class'          => ''

	),$atts));
	 $id = uniqid('ytcs').rand().time();
		JHtml::_('jquery.framework'); 
		JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/assets/css/animate.css",'text/css',"screen");
		JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/assets/css/owl.carousel.css",'text/css',"screen");
		JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/shortcodes/content_slider/css/content_slider.css");
		JHtml::script(JUri::base()."plugins/system/ytshortcodes/assets/js/owl.carousel.min.js");
		JHtml::script(JUri::base()."plugins/system/ytshortcodes/shortcodes/content_slider/js/content_slider.js");
        if ($transitionin === 'slide')
            $transitionin = 'false';
        return '<div id="'. $id .'" class="yt-content-slider owl-theme yt-content-slider-style-' . $style .' '. $arrow_position.'" data-transitionin="' . $transitionin . '" data-transitionout="' . $transitionout . '" data-autoplay="' . $autoplay .'" data-autoheight="' . $autoheight .'" data-delay="' . $delay . '" data-speed="' . $speed . '" data-margin="' . $margin . '" data-items="' . $items . '" data-arrows="' . $arrows .'" data-pagination="' . $pagination . '" data-lazyload="' . $lazyload . '" data-loop="' . $loop . '" data-hoverpause="' . $hoverpause . '">' . parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $content)) . '</div>';
}
?>