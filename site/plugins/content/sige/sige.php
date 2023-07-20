<?php
/**
 * @Copyright
 * @package     SIGE - Simple Image Gallery Extended for Joomla! 3.x
 * @author      Viktor Vogel <admin@kubik-rubik.de>
 * @version     3.2.0 - 2015-08-26
 * @link        https://joomla-extensions.kubik-rubik.de/sige-simple-image-gallery-extended
 *
 * @license     GNU/GPL
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die('Restricted access');

class plgContentSige extends JPlugin
{
	protected $absolute_path;
	protected $live_site;
	protected $root_folder;
	protected $images_dir;
	protected $syntax_parameter;
	protected $plugin_parameter;
	protected $article_title;
	protected $thumbnail_max_height;
	protected $thumbnail_max_width;
	protected $turbo_html_read_in;
	protected $turbo_css_read_in;

	function __construct(&$subject, $config)
	{
		$app = JFactory::getApplication();

		if($app->isAdmin())
		{
			return;
		}

		$version = new JVersion();

		$joomla_main_version = substr($version->RELEASE, 0, strpos($version->RELEASE, '.'));

		if($version->PRODUCT == 'Joomla!' AND $joomla_main_version != '3')
		{
			throw new Exception(JText::_('PLG_CONTENT_SIGE_NEEDJ3'), 404);
		}

		parent::__construct($subject, $config);
		$this->loadLanguage('plg_content_sige', JPATH_ADMINISTRATOR);

		if(isset($_SESSION['sigcount']))
		{
			unset($_SESSION['sigcount']);
		}

		if(isset($_SESSION['sigcountarticles']))
		{
			unset($_SESSION['sigcountarticles']);
		}

		$this->absolute_path = JPATH_SITE;
		$this->live_site = JURI::base();

		if(substr($this->live_site, -1) == '/')
		{
			$this->live_site = substr($this->live_site, 0, -1);
		}

		$this->plugin_parameter = array();
	}

	function onContentPrepare($context, &$article, &$params, $limitstart)
	{
		if(!preg_match('@{gallery}(.*){/gallery}@Us', $article->text))
		{
			return;
		}

		if(function_exists('gd_info'))
		{
			$gdinfo = gd_info();
			$gdsupport = array();
			$version = intval(preg_replace('/[[:alpha:][:space:]()]+/', '', $gdinfo['GD Version']));

			if($version != 2)
			{
				$gdsupport[] = '<div class="message">GD Bibliothek nicht vorhanden</div>';
			}

			if(substr(phpversion(), 0, 3) < 5.3)
			{
				if(!$gdinfo['JPG Support'])
				{
					$gdsupport[] = '<div class="message">GD JPG Bibliothek nicht vorhanden</div>';
				}
			}
			else
			{
				if(!$gdinfo['JPEG Support'])
				{
					$gdsupport[] = '<div class="message">GD JPG Bibliothek nicht vorhanden</div>';
				}
			}

			if(!$gdinfo['GIF Create Support'])
			{
				$gdsupport[] = '<div class="message">GD GIF Bibliothek nicht vorhanden</div>';
			}

			if(!$gdinfo['PNG Support'])
			{
				$gdsupport[] = '<div class="message">GD PNG Bibliothek nicht vorhanden</div>';
			}

			if(count($gdsupport))
			{
				foreach($gdsupport as $k => $v)
				{
					echo $v;
				}
			}
		}

		if(!isset($_SESSION['sigcountarticles']))
		{
			$_SESSION['sigcountarticles'] = -1;
		}

		if(preg_match_all('@{gallery}(.*){/gallery}@Us', $article->text, $matches, PREG_PATTERN_ORDER) > 0)
		{
			$_SESSION['sigcountarticles']++;

			if(!isset($_SESSION['sigcount']))
			{
				$_SESSION['sigcount'] = -1;
			}

			$this->plugin_parameter['lang'] = JFactory::getLanguage()->getTag();

			foreach($matches[0] as $match)
			{
				$_SESSION['sigcount']++;
				$sige_code = preg_replace('@{.+?}@', '', $match);
				$sige_array = explode(',', $sige_code);
				$this->images_dir = $sige_array[0];

				unset($this->syntax_parameter);
				$this->syntax_parameter = array();

				if(count($sige_array) >= 2)
				{
					for($i = 1; $i < count($sige_array); $i++)
					{
						$parameter_temp = explode('=', $sige_array[$i]);
						if(count($parameter_temp) >= 2)
						{
							$this->syntax_parameter[strtolower(trim($parameter_temp[0]))] = trim($parameter_temp[1]);
						}
					}
				}

				unset($sige_array);

				$this->setParams();

				if(!$this->plugin_parameter['root'])
				{
					$this->root_folder = '/images/';
				}
				else
				{
					$this->root_folder = '/';
				}

				$this->turbo_html_read_in = false;
				$this->turbo_css_read_in = false;

				if($this->plugin_parameter['turbo'])
				{
					if($this->plugin_parameter['turbo'] == 'new')
					{
						$this->turbo_html_read_in = true;
						$this->turbo_css_read_in = true;
					}
					else
					{
						if(!file_exists($this->absolute_path.$this->root_folder.$this->images_dir.'/sige_turbo_html-'.$this->plugin_parameter['lang'].'.txt'))
						{
							$this->turbo_html_read_in = true;
						}

						if(!file_exists($this->absolute_path.$this->root_folder.$this->images_dir.'/sige_turbo_css-'.$this->plugin_parameter['lang'].'.txt'))
						{
							$this->turbo_css_read_in = true;
						}
					}
				}

				if(!$this->plugin_parameter['turbo'] OR ($this->plugin_parameter['turbo'] AND $this->turbo_html_read_in))
				{
					unset($images);
					$noimage = 0;

					if($dh = @opendir($this->absolute_path.$this->root_folder.$this->images_dir))
					{
						while(($f = readdir($dh)) !== false)
						{
							if(substr(strtolower($f), -3) == 'jpg' OR substr(strtolower($f), -3) == 'gif' OR substr(strtolower($f), -3) == 'png')
							{
								$images[] = array('filename' => $f);
								$noimage++;
							}
						}

						closedir($dh);
					}

					if($noimage)
					{
						if(!file_exists($this->absolute_path.$this->root_folder.$this->images_dir.'/index.html'))
						{
							file_put_contents($this->absolute_path.$this->root_folder.$this->images_dir.'/index.html', '');
						}

						$jview = JFactory::getApplication()->input->getWord('view');

						if($jview != 'featured' AND isset($article->title))
						{
							$this->article_title = preg_replace("@\"@", "'", $article->title);
						}

						if($this->plugin_parameter['sort'] == 1)
						{
							shuffle($images);
						}
						elseif($this->plugin_parameter['sort'] == 2)
						{
							sort($images);
						}
						elseif($this->plugin_parameter['sort'] == 3)
						{
							rsort($images);
						}
						elseif($this->plugin_parameter['sort'] == 4 OR $this->plugin_parameter['sort'] == 5)
						{
							for($a = 0; $a < count($images); $a++)
							{
								$images[$a]['timestamp'] = filemtime($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$images[$a]['filename']);
							}

							if($this->plugin_parameter['sort'] == 4)
							{
								usort($images, array($this, 'timeasc'));
							}
							elseif($this->plugin_parameter['sort'] == 5)
							{
								usort($images, array($this, 'timedesc'));
							}
						}

						$noimage_rest = 0;
						$single_yes = false;

						if($this->plugin_parameter['single'])
						{
							$count = count($images);

							if($images[0]['filename'] == $this->plugin_parameter['single'])
							{
								if($this->plugin_parameter['single_gallery'])
								{
									$noimage_rest = $noimage;
									$this->plugin_parameter['limit_quantity'] = 1;
								}

								$noimage = 1;
								$single_yes = true;
							}
							else
							{
								for($a = 1; $a < $noimage; $a++)
								{
									if($images[$a]['filename'] == $this->plugin_parameter['single'])
									{
										if($this->plugin_parameter['single_gallery'])
										{
											$noimage_rest = $noimage;
											$this->plugin_parameter['limit_quantity'] = 1;
										}

										$noimage = 1;
										$images[$count] = $images[0];
										$images[0] = array('filename' => $this->plugin_parameter['single']);
										unset($images[$a]);
										$images[$a] = $images[$count];
										unset($images[$count]);
										$single_yes = true;

										break;
									}
								}
							}
						}

						if($this->plugin_parameter['fileinfo'])
						{
							$file_info = $this->getFileInfo();

							// Use the sorting from the captions.text to sort the images
							if(!empty($file_info) AND $this->plugin_parameter['sort'] == 6)
							{
								$images_file_info = array();

								foreach($file_info as $file_info_image)
								{
									foreach($images as $key => $image)
									{
										if($file_info_image[0] == $image['filename'])
										{
											$images_file_info[]['filename'] = $file_info_image[0];
											unset($images[$key]);
											break;
										}
									}
								}

								if(!empty($images_file_info))
								{
									$images = $images_file_info;
									$noimage = count($images);
								}
							}
						}
						else
						{
							$file_info = false;
						}

						if($this->plugin_parameter['calcmaxthumbsize'])
						{
							$this->calculateMaxThumbnailSize($images);
						}
						else
						{
							$this->thumbnail_max_height = $this->plugin_parameter['height'];
							$this->thumbnail_max_width = $this->plugin_parameter['width'];
						}

						$sige_css = '';

						if($this->plugin_parameter['caption'])
						{
							$caption_height = 20;
						}
						else
						{
							$caption_height = 0;
						}

						if($this->plugin_parameter['salign'])
						{
							if($this->plugin_parameter['salign'] == 'left')
							{
								$sige_css .= '.sige_cont_'.$_SESSION["sigcount"].' {width:'.($this->thumbnail_max_width + $this->plugin_parameter['gap_h']).'px;height:'.($this->thumbnail_max_height + $this->plugin_parameter['gap_v'] + $caption_height).'px;float:left;display:inline-block;}'."\n";
							}
							elseif($this->plugin_parameter['salign'] == 'right')
							{
								$sige_css .= '.sige_cont_'.$_SESSION['sigcount'].' {width:'.($this->thumbnail_max_width + $this->plugin_parameter['gap_h']).'px;height:'.($this->thumbnail_max_height + $this->plugin_parameter['gap_v'] + $caption_height).'px;float:right;display:inline-block;}'."\n";
							}
							elseif($this->plugin_parameter['salign'] == 'center')
							{
								$sige_css .= '.sige_cont_'.$_SESSION['sigcount'].' {width:'.($this->thumbnail_max_width + $this->plugin_parameter['gap_h']).'px;height:'.($this->thumbnail_max_height + $this->plugin_parameter['gap_v'] + $caption_height).'px;display:inline-block;}'."\n";
							}
						}
						else
						{
							$sige_css .= '.sige_cont_'.$_SESSION['sigcount'].' {width:'.($this->thumbnail_max_width + $this->plugin_parameter['gap_h']).'px;height:'.($this->thumbnail_max_height + $this->plugin_parameter['gap_v'] + $caption_height).'px;float:left;display:inline-block;}'."\n";
						}

						$this->loadHeadData($sige_css);

						if($this->plugin_parameter['resize_images'])
						{
							$this->resizeImages($images);
						}

						if($this->plugin_parameter['watermark'])
						{
							$this->watermark($images, $single_yes);
						}

						if($this->plugin_parameter['limit'] AND (!$this->plugin_parameter['single'] OR !$this->plugin_parameter['single_gallery']))
						{
							$noimage_rest = $noimage;

							if($noimage > $this->plugin_parameter['limit_quantity'])
							{
								$noimage = $this->plugin_parameter['limit_quantity'];
							}
						}

						if($this->plugin_parameter['thumbs'] AND !$this->plugin_parameter['list'] AND !$this->plugin_parameter['word'])
						{
							$this->thumbnails($images, $noimage);
						}

						if($this->plugin_parameter['word'])
						{
							$noimage_rest = $noimage;
							$this->plugin_parameter['limit_quantity'] = 1;
							$noimage = 1;
						}

						$html = '<!-- Simple Image Gallery Extended - Plugin Joomla! 3.x - Kubik-Rubik Joomla! Extensions -->';

						if($this->plugin_parameter['single'] AND $single_yes AND !$this->plugin_parameter['word'])
						{
							$html .= '<ul class="sige_single">';
						}
						elseif(!$this->plugin_parameter['list'] AND !$this->plugin_parameter['word'])
						{
							$html .= '<ul class="sige">';
						}

						if($this->plugin_parameter['list'] AND !$this->plugin_parameter['word'])
						{
							$html .= '<ul>';
						}

						for($a = 0; $a < $noimage; $a++)
						{
							$this->htmlImage($images[$a]['filename'], $html, 0, $file_info, $a);
						}

						if($this->plugin_parameter['list'] AND !$this->plugin_parameter['word'])
						{
							$html .= '</ul>';
						}

						if(!$this->plugin_parameter['list'] AND !$this->plugin_parameter['word'])
						{
							$html .= '</ul><span class="sige_clr"></span>';
						}

						if(!empty($noimage_rest) AND !$this->plugin_parameter['image_link'])
						{
							for($a = $this->plugin_parameter['limit_quantity']; $a < $noimage_rest; $a++)
							{
								$this->htmlImage($images[$a]['filename'], $html, 1, $file_info, $a);
							}
						}

						if($this->plugin_parameter['copyright'])
						{
							if((!$this->plugin_parameter['single'] OR ($this->plugin_parameter['single'] AND !$single_yes)) AND !$this->plugin_parameter['list'] AND !$this->plugin_parameter['word'])
							{
								$html .= '<p class="sige_small"><a href="http://joomla-extensions.kubik-rubik.de" title="SIGE - Simple Image Gallery Extended - Kubik-Rubik Joomla! Extensions" target="_blank">Simple Image Gallery Extended</a></p>';
							}
						}
					}
					else
					{
						$html = '<strong>'.JText::_('NOIMAGES').'</strong><br /><br />'.JText::_('NOIMAGESDEBUG').' '.$this->live_site.$this->root_folder.$this->images_dir;
					}

					if($this->turbo_html_read_in)
					{
						file_put_contents($this->absolute_path.$this->root_folder.$this->images_dir.'/sige_turbo_html-'.$this->plugin_parameter['lang'].'.txt', $html);
					}
				}
				else
				{
					$this->loadHeadData(1);

					$html = file_get_contents($this->absolute_path.$this->root_folder.$this->images_dir.'/sige_turbo_html-'.$this->plugin_parameter['lang'].'.txt');
				}

				$article->text = preg_replace('@(<p>)?{gallery}'.$sige_code.'{/gallery}(</p>)?@s', $html, $article->text);
			}

			$this->loadHeadData();
		}
	}

	private function setParams()
	{
		$params = array('width', 'height', 'ratio', 'gap_v', 'gap_h', 'quality', 'quality_png', 'displaynavtip', 'navtip', 'limit', 'displaymessage', 'message', 'thumbs', 'thumbs_new', 'view', 'limit_quantity', 'noslim', 'caption', 'iptc', 'iptcutf8', 'print', 'salign', 'connect', 'download', 'list', 'crop', 'crop_factor', 'sort', 'single', 'thumbdetail', 'watermark', 'encrypt', 'image_info', 'image_link', 'image_link_new', 'single_gallery', 'column_quantity', 'css_image', 'css_image_half', 'copyright', 'word', 'watermarkposition', 'watermarkimage', 'watermark_new', 'root', 'js', 'calcmaxthumbsize', 'fileinfo', 'turbo', 'resize_images', 'width_image', 'height_image', 'ratio_image', 'images_new', 'scaption');

		foreach($params as $value)
		{
			$this->plugin_parameter[$value] = $this->getParams($value);
		}

		$count = $this->getParams('count', 1);

		if(!empty($count))
		{
			$_SESSION['sigcount'] = $count;
		}
	}

	private function getParams($param, $syntax_only = 0)
	{
		if($syntax_only == 1)
		{
			if(array_key_exists($param, $this->syntax_parameter) AND $this->syntax_parameter[$param] != '')
			{
				return $this->syntax_parameter[$param];
			}
		}
		else
		{
			if(array_key_exists($param, $this->syntax_parameter) AND $this->syntax_parameter[$param] != '')
			{
				return $this->syntax_parameter[$param];
			}
			else
			{
				return $this->params->get($param);
			}
		}
	}

	private function getFileInfo()
	{
		$file_info = false;

		$captions_lang = $this->absolute_path.$this->root_folder.$this->images_dir.'/captions-'.$this->plugin_parameter['lang'].'.txt';
		$captions_txtfile = $this->absolute_path.$this->root_folder.$this->images_dir.'/captions.txt';

		if(file_exists($captions_lang))
		{
			$captions_file = array_map('trim', file($captions_lang));

			foreach($captions_file as $value)
			{
				if(!empty($value))
				{
					$captions_line = explode('|', $value);
					$file_info[] = $captions_line;
				}
			}
		}
		elseif(file_exists($captions_txtfile) AND !file_exists($captions_lang))
		{
			$captions_file = array_map('trim', file($captions_txtfile));

			foreach($captions_file as $value)
			{
				if(!empty($value))
				{
					$captions_line = explode('|', $value);
					$file_info[] = $captions_line;
				}
			}
		}

		return $file_info;
	}

	private function calculateMaxThumbnailSize($images)
	{
		$max_height = array();
		$max_width = array();

		foreach($images as $image)
		{
			list($max_height[], $max_width[]) = $this->calculateSize($image['filename'], 1);
		}

		rsort($max_height);
		rsort($max_width);

		$this->thumbnail_max_height = $max_height[0];
		$this->thumbnail_max_width = $max_width[0];
	}

	private function calculateSize($image, $thumbnail)
	{
		if($this->plugin_parameter['resize_images'] AND !$thumbnail)
		{
			$new_w = $this->plugin_parameter['width_image'];

			if($this->plugin_parameter['ratio_image'])
			{
				$imagedata = getimagesize($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$image);

				$new_h = (int)($imagedata[1] * ($new_w / $imagedata[0]));
				if($this->plugin_parameter['height_image'] AND ($new_h > $this->plugin_parameter['height_image']))
				{
					$new_h = $this->plugin_parameter['height_image'];
					$new_w = (int)($imagedata[0] * ($new_h / $imagedata[1]));
				}
			}
			else
			{
				$new_h = $this->plugin_parameter['height_image'];
			}
		}
		else
		{
			$new_w = $this->plugin_parameter['width'];

			if($this->plugin_parameter['ratio'])
			{
				$imagedata = getimagesize($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$image);

				$new_h = (int)($imagedata[1] * ($new_w / $imagedata[0]));
				if($this->plugin_parameter['height'] AND ($new_h > $this->plugin_parameter['height']))
				{
					$new_h = $this->plugin_parameter['height'];
					$new_w = (int)($imagedata[0] * ($new_h / $imagedata[1]));
				}
			}
			else
			{
				$new_h = $this->plugin_parameter['height'];
			}
		}

		$ret = array((int)$new_h, (int)$new_w);

		return ($ret);
	}

	private function loadHeadData($sige_css = 0)
	{
		if(!empty($sige_css))
		{
			if(!$this->plugin_parameter['turbo'] OR ($this->plugin_parameter['turbo'] AND $this->turbo_css_read_in))
			{
				$head = '<style type="text/css">'.$sige_css.'</style>';

				if($this->turbo_css_read_in)
				{
					file_put_contents($this->absolute_path.$this->root_folder.$this->images_dir.'/sige_turbo_css-'.$this->plugin_parameter['lang'].'.txt', $head);
				}
			}
			else
			{
				$head = file_get_contents($this->absolute_path.$this->root_folder.$this->images_dir.'/sige_turbo_css-'.$this->plugin_parameter['lang'].'.txt');
			}
		}
		else
		{
			$head = array();

			if($_SESSION['sigcountarticles'] == 0)
			{
				$head[] = '<link rel="stylesheet" href="'.$this->live_site.'/plugins/content/sige/plugin_sige/sige.css" type="text/css" media="screen" />';

				if($this->plugin_parameter['js'] == 1)
				{
					JHtml::_('behavior.framework');

					$head[] = '<script type="text/javascript" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/slimbox.js"></script>';
					$head[] = '<script type="text/javascript">
                                Slimbox.scanPage = function() {
                                    $$("a[rel^=lightbox]").slimbox({counterText: "'.JText::_('PLG_CONTENT_SIGE_SLIMBOX_IMAGES').'"}, null, function(el) {
                                        return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
                                    });
                                };
                                if (!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
                                    window.addEvent("domready", Slimbox.scanPage);
                                }
                                </script>';
					$head[] = '<link rel="stylesheet" href="'.$this->live_site.'/plugins/content/sige/plugin_sige/slimbox.css" type="text/css" media="screen" />';
				}
				elseif($this->plugin_parameter['js'] == 2)
				{
					if($this->plugin_parameter['lang'] == 'de-DE')
					{
						$head[] = '<script type="text/javascript" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/lytebox.js"></script>';
					}
					else
					{
						$head[] = '<script type="text/javascript" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/lytebox_en.js"></script>';
					}
					$head[] = '<link rel="stylesheet" href="'.$this->live_site.'/plugins/content/sige/plugin_sige/lytebox.css" type="text/css" media="screen" />';
				}
				elseif($this->plugin_parameter['js'] == 3)
				{
					if($this->plugin_parameter['lang'] == 'de-DE')
					{
						$head[] = '<script type="text/javascript" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/shadowbox.js"></script>';
					}
					else
					{
						$head[] = '<script type="text/javascript" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/shadowbox_en.js"></script>';
					}

					$head[] = '<link rel="stylesheet" href="'.$this->live_site.'/plugins/content/sige/plugin_sige/shadowbox.css" type="text/css" media="screen" />';
					$head[] = '<script type="text/javascript">Shadowbox.init();</script>';
				}
				elseif($this->plugin_parameter['js'] == 4)
				{
					$head[] = '<script type="text/javascript" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/milkbox.js"></script>';
					$head[] = '<link rel="stylesheet" href="'.$this->live_site.'/plugins/content/sige/plugin_sige/milkbox.css" type="text/css" media="screen" />';
				}
				elseif($this->plugin_parameter['js'] == 5)
				{
					JHtml::_('jquery.framework');

					$head[] = '<script type="text/javascript" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/slimbox2.js"></script>';
					$head[] = '<script type="text/javascript">
                                if (!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
                                    jQuery(function($) {
                                        $("a[rel^=\'lightbox\']").slimbox({counterText: "'.JText::_('PLG_CONTENT_SIGE_SLIMBOX_IMAGES').'"}, null, function(el) {
                                            return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
                                        });
                                    });
                                }
                                </script>';
					$head[] = '<link rel="stylesheet" href="'.$this->live_site.'/plugins/content/sige/plugin_sige/slimbox2.css" type="text/css" media="screen" />';
				}
				elseif($this->plugin_parameter['js'] == 6)
				{
					JHtml::_('jquery.framework');

					$head[] = '<script type="text/javascript" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/venobox/venobox.js"></script>';
					$head[] = '<script type="text/javascript">jQuery(document).ready(function(){jQuery(\'.venobox\').venobox();});</script>';
					$head[] = '<link rel="stylesheet" href="'.$this->live_site.'/plugins/content/sige/plugin_sige/venobox/venobox.css" type="text/css" media="screen" />';
				}
			}

			$head = "\n".implode("\n", $head)."\n";
		}

		$document = JFactory::getDocument();

		if($document instanceof JDocumentHTML)
		{
			// Combine dynamic CSS instructions - Check whether a custom style tag was already set and combine them to
			// avoid problems in some browsers due to too many CSS instructions
			if(!empty($sige_css))
			{
				if(!empty($document->_custom))
				{
					$custom_tags = array();

					foreach($document->_custom as $key => $custom_tag)
					{
						if(preg_match('@<style type="text/css">(.*)</style>@Us', $custom_tag, $match))
						{
							$custom_tags[] = $match[1];
							unset($document->_custom[$key]);
						}
					}

					// If content is loaded from the turbo file, then the CSS instructions need to be prepared for the output
					if($sige_css == 1)
					{
						if(preg_match('@<style type="text/css">(.*)</style>@Us', $head, $match))
						{
							$sige_css = $match[1];
						}
					}

					if(!empty($custom_tags))
					{
						$head = '<style type="text/css">'.implode('', $custom_tags).$sige_css.'</style>';
					}
				}
			}

			$document->addCustomTag($head);
		}
	}

	private function resizeImages($images)
	{
		if(!is_dir($this->absolute_path.$this->root_folder.$this->images_dir.'/resizedimages'))
		{
			mkdir($this->absolute_path.$this->root_folder.$this->images_dir.'/resizedimages', 0755);
			file_put_contents($this->absolute_path.$this->root_folder.$this->images_dir.'/resizedimages/index.html', '');
		}

		$num = count($images);

		for($a = 0; $a < $num; $a++)
		{
			if(!empty($images[$a]['filename']))
			{
				$filenamethumb = $this->absolute_path.$this->root_folder.$this->images_dir.'/resizedimages/'.$images[$a]['filename'];

				if(!file_exists($filenamethumb) OR $this->plugin_parameter['images_new'] != 0)
				{
					list($new_h, $new_w) = $this->calculateSize($images[$a]['filename'], 0);

					if(substr(strtolower($filenamethumb), -3) == 'gif')
					{
						$origimage = imagecreatefromgif($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$images[$a]['filename']);
						$width_ori = imagesx($origimage);
						$height_ori = imagesy($origimage);
						$thumbimage = imagecreatetruecolor($new_w, $new_h);
						imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $new_w, $new_h, $width_ori, $height_ori);
						imagegif($thumbimage, $this->absolute_path.$this->root_folder.$this->images_dir.'/resizedimages/'.$images[$a]['filename']);
					}
					elseif(substr(strtolower($filenamethumb), -3) == 'jpg')
					{
						$origimage = imagecreatefromjpeg($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$images[$a]['filename']);
						$width_ori = imagesx($origimage);
						$height_ori = imagesy($origimage);
						$thumbimage = imagecreatetruecolor($new_w, $new_h);
						imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $new_w, $new_h, $width_ori, $height_ori);
						imagejpeg($thumbimage, $this->absolute_path.$this->root_folder.$this->images_dir.'/resizedimages/'.$images[$a]['filename'], $this->plugin_parameter['quality']);
					}
					elseif(substr(strtolower($filenamethumb), -3) == 'png')
					{
						$origimage = imagecreatefrompng($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$images[$a]['filename']);
						$width_ori = imagesx($origimage);
						$height_ori = imagesy($origimage);
						$thumbimage = imagecreatetruecolor($new_w, $new_h);
						imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $new_w, $new_h, $width_ori, $height_ori);
						imagepng($thumbimage, $this->absolute_path.$this->root_folder.$this->images_dir.'/resizedimages/'.$images[$a]['filename'], $this->plugin_parameter['quality_png']);
					}

					imagedestroy($origimage);
					imagedestroy($thumbimage);
				}
			}
		}
	}

	private function watermark($images, $single_yes)
	{
		if(!is_dir($this->absolute_path.$this->root_folder.$this->images_dir.'/wm'))
		{
			mkdir($this->absolute_path.$this->root_folder.$this->images_dir.'/wm', 0755);
			file_put_contents($this->absolute_path.$this->root_folder.$this->images_dir.'/wm/index.html', '');
		}

		if(empty($this->plugin_parameter['single_gallery']) AND $single_yes)
		{
			$num = 1;
		}
		else
		{
			$num = count($images);
		}

		for($a = 0; $a < $num; $a++)
		{
			if(!empty($images[$a]['filename']))
			{
				$imagename = substr($images[$a]['filename'], 0, -4);
				$type = substr(strtolower($images[$a]['filename']), -3);
				$image_hash = $this->encrypt($imagename).'.'.$type;

				$filenamewm = $this->absolute_path.$this->root_folder.$this->images_dir.'/wm/'.$image_hash;

				if(!file_exists($filenamewm) OR $this->plugin_parameter['watermark_new'] != 0)
				{
					if($this->plugin_parameter['watermarkimage'])
					{
						$watermarkimage = imagecreatefrompng($this->absolute_path.'/plugins/content/sige/plugin_sige/'.$this->plugin_parameter['watermarkimage']);
					}
					else
					{
						$watermarkimage = imagecreatefrompng($this->absolute_path.'/plugins/content/sige/plugin_sige/watermark.png');
					}

					$width_wm = imagesx($watermarkimage);
					$height_wm = imagesy($watermarkimage);

					if(substr(strtolower($images[$a]['filename']), -3) == 'gif')
					{
						if($this->plugin_parameter['resize_images'])
						{
							$origimage = imagecreatefromgif($this->absolute_path.$this->root_folder.$this->images_dir.'/resizedimages/'.$images[$a]['filename']);
						}
						else
						{
							$origimage = imagecreatefromgif($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$images[$a]['filename']);
						}

						$width_ori = imagesx($origimage);
						$height_ori = imagesy($origimage);

						$t_image = imagecreatetruecolor($width_ori, $height_ori);
						imagecopy($t_image, $origimage, 0, 0, 0, 0, $width_ori, $height_ori);
						$origimage = $t_image;

						if($this->plugin_parameter['watermarkposition'] == 1)
						{
							imagecopy($origimage, $watermarkimage, 0, 0, 0, 0, $width_wm, $height_wm);
						}
						elseif($this->plugin_parameter['watermarkposition'] == 2)
						{
							imagecopy($origimage, $watermarkimage, $width_ori - $width_wm, 0, 0, 0, $width_wm, $height_wm);
						}
						elseif($this->plugin_parameter['watermarkposition'] == 3)
						{
							imagecopy($origimage, $watermarkimage, 0, $height_ori - $height_wm, 0, 0, $width_wm, $height_wm);
						}
						elseif($this->plugin_parameter['watermarkposition'] == 4)
						{
							imagecopy($origimage, $watermarkimage, $width_ori - $width_wm, $height_ori - $height_wm, 0, 0, $width_wm, $height_wm);
						}
						else
						{
							imagecopy($origimage, $watermarkimage, ($width_ori - $width_wm) / 2, ($height_ori - $height_wm) / 2, 0, 0, $width_wm, $height_wm);
						}

						imagegif($origimage, $this->absolute_path.$this->root_folder.$this->images_dir.'/wm/'.$image_hash);
					}
					elseif(substr(strtolower($images[$a]['filename']), -3) == 'jpg')
					{
						if($this->plugin_parameter['resize_images'])
						{
							$origimage = imagecreatefromjpeg($this->absolute_path.$this->root_folder.$this->images_dir.'/resizedimages/'.$images[$a]['filename']);
						}
						else
						{
							$origimage = imagecreatefromjpeg($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$images[$a]['filename']);
						}

						$width_ori = imagesx($origimage);
						$height_ori = imagesy($origimage);

						if($this->plugin_parameter['watermarkposition'] == 1)
						{
							imagecopy($origimage, $watermarkimage, 0, 0, 0, 0, $width_wm, $height_wm);
						}
						elseif($this->plugin_parameter['watermarkposition'] == 2)
						{
							imagecopy($origimage, $watermarkimage, $width_ori - $width_wm, 0, 0, 0, $width_wm, $height_wm);
						}
						elseif($this->plugin_parameter['watermarkposition'] == 3)
						{
							imagecopy($origimage, $watermarkimage, 0, $height_ori - $height_wm, 0, 0, $width_wm, $height_wm);
						}
						elseif($this->plugin_parameter['watermarkposition'] == 4)
						{
							imagecopy($origimage, $watermarkimage, $width_ori - $width_wm, $height_ori - $height_wm, 0, 0, $width_wm, $height_wm);
						}
						else
						{
							imagecopy($origimage, $watermarkimage, ($width_ori - $width_wm) / 2, ($height_ori - $height_wm) / 2, 0, 0, $width_wm, $height_wm);
						}

						imagejpeg($origimage, $this->absolute_path.$this->root_folder.$this->images_dir.'/wm/'.$image_hash, $this->plugin_parameter['quality']);
					}
					elseif(substr(strtolower($images[$a]['filename']), -3) == 'png')
					{
						if($this->plugin_parameter['resize_images'])
						{
							$origimage = imagecreatefrompng($this->absolute_path.$this->root_folder.$this->images_dir.'/resizedimages/'.$images[$a]['filename']);
						}
						else
						{
							$origimage = imagecreatefrompng($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$images[$a]['filename']);
						}

						$width_ori = imagesx($origimage);
						$height_ori = imagesy($origimage);

						if($this->plugin_parameter['watermarkposition'] == 1)
						{
							imagecopy($origimage, $watermarkimage, 0, 0, 0, 0, $width_wm, $height_wm);
						}
						elseif($this->plugin_parameter['watermarkposition'] == 2)
						{
							imagecopy($origimage, $watermarkimage, $width_ori - $width_wm, 0, 0, 0, $width_wm, $height_wm);
						}
						elseif($this->plugin_parameter['watermarkposition'] == 3)
						{
							imagecopy($origimage, $watermarkimage, 0, $height_ori - $height_wm, 0, 0, $width_wm, $height_wm);
						}
						elseif($this->plugin_parameter['watermarkposition'] == 4)
						{
							imagecopy($origimage, $watermarkimage, $width_ori - $width_wm, $height_ori - $height_wm, 0, 0, $width_wm, $height_wm);
						}
						else
						{
							imagecopy($origimage, $watermarkimage, ($width_ori - $width_wm) / 2, ($height_ori - $height_wm) / 2, 0, 0, $width_wm, $height_wm);
						}

						imagepng($origimage, $this->absolute_path.$this->root_folder.$this->images_dir.'/wm/'.$image_hash, $this->plugin_parameter['quality_png']);
					}

					imagedestroy($origimage);
					imagedestroy($watermarkimage);
				}
			}
		}
	}

	private function encrypt($imagename)
	{
		$image_hash = md5($imagename);

		if($this->plugin_parameter['encrypt'] == 0)
		{
			$image_hash = str_rot13($imagename);
		}

		if($this->plugin_parameter['encrypt'] == 2)
		{
			$image_hash = sha1($imagename);
		}

		return $image_hash;
	}

	private function thumbnails($images, $noimage)
	{
		if(!is_dir($this->absolute_path.$this->root_folder.$this->images_dir.'/thumbs'))
		{
			mkdir($this->absolute_path.$this->root_folder.$this->images_dir.'/thumbs', 0755);
			file_put_contents($this->absolute_path.$this->root_folder.$this->images_dir.'/thumbs/index.html', '');
		}

		for($a = 0; $a < $noimage; $a++)
		{
			if(!empty($images[$a]['filename']))
			{
				$imagename = substr($images[$a]['filename'], 0, -4);
				$type = substr(strtolower($images[$a]['filename']), -3);
				$image_hash = $this->encrypt($imagename).'.'.$type;

				if($this->plugin_parameter['watermark'])
				{
					$filenamethumb = $this->absolute_path.$this->root_folder.$this->images_dir.'/thumbs/'.$image_hash;
				}
				else
				{
					$filenamethumb = $this->absolute_path.$this->root_folder.$this->images_dir.'/thumbs/'.$images[$a]['filename'];
				}

				if(!file_exists($filenamethumb) OR $this->plugin_parameter['thumbs_new'] != 0)
				{
					list($new_h, $new_w) = $this->calculateSize($images[$a]['filename'], 1);

					if(substr(strtolower($filenamethumb), -3) == 'gif')
					{
						if($this->plugin_parameter['watermark'])
						{
							$origimage = imagecreatefromgif($this->absolute_path.$this->root_folder.$this->images_dir.'/wm/'.$image_hash);
						}
						else
						{
							$origimage = imagecreatefromgif($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$images[$a]['filename']);
						}

						$width_ori = imagesx($origimage);
						$height_ori = imagesy($origimage);
						$thumbimage = imagecreatetruecolor($new_w, $new_h);

						if($this->plugin_parameter['crop'] AND ($this->plugin_parameter['crop_factor'] > 0 AND $this->plugin_parameter['crop_factor'] < 100))
						{
							list($crop_width, $crop_height, $x_coordinate, $y_coordinate) = $this->crop($width_ori, $height_ori);
							imagecopyresampled($thumbimage, $origimage, 0, 0, $x_coordinate, $y_coordinate, $new_w, $new_h, $crop_width, $crop_height);
						}
						else
						{
							if($this->plugin_parameter['thumbdetail'] == 1)
							{
								imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $new_w, $new_h, $new_w, $new_h);
							}
							elseif($this->plugin_parameter['thumbdetail'] == 2)
							{
								imagecopyresampled($thumbimage, $origimage, 0, 0, $width_ori - $new_w, 0, $new_w, $new_h, $new_w, $new_h);
							}
							elseif($this->plugin_parameter['thumbdetail'] == 3)
							{
								imagecopyresampled($thumbimage, $origimage, 0, 0, 0, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);
							}
							elseif($this->plugin_parameter['thumbdetail'] == 4)
							{
								imagecopyresampled($thumbimage, $origimage, 0, 0, $width_ori - $new_w, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);
							}
							else
							{
								imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $new_w, $new_h, $width_ori, $height_ori);
							}
						}

						if($this->plugin_parameter['watermark'])
						{
							imagegif($thumbimage, $this->absolute_path.$this->root_folder.$this->images_dir.'/thumbs/'.$image_hash);
						}
						else
						{
							imagegif($thumbimage, $this->absolute_path.$this->root_folder.$this->images_dir.'/thumbs/'.$images[$a]['filename']);
						}
					}
					elseif(substr(strtolower($filenamethumb), -3) == 'jpg')
					{
						if($this->plugin_parameter['watermark'])
						{
							$origimage = imagecreatefromjpeg($this->absolute_path.$this->root_folder.$this->images_dir.'/wm/'.$image_hash);
						}
						else
						{
							$origimage = imagecreatefromjpeg($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$images[$a]['filename']);
						}

						$width_ori = imagesx($origimage);
						$height_ori = imagesy($origimage);
						$thumbimage = imagecreatetruecolor($new_w, $new_h);

						if($this->plugin_parameter['crop'] AND ($this->plugin_parameter['crop_factor'] > 0 AND $this->plugin_parameter['crop_factor'] < 100))
						{
							list($crop_width, $crop_height, $x_coordinate, $y_coordinate) = $this->crop($width_ori, $height_ori);
							imagecopyresampled($thumbimage, $origimage, 0, 0, $x_coordinate, $y_coordinate, $new_w, $new_h, $crop_width, $crop_height);
						}
						else
						{
							if($this->plugin_parameter['thumbdetail'] == 1)
							{
								imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $new_w, $new_h, $new_w, $new_h);
							}
							elseif($this->plugin_parameter['thumbdetail'] == 2)
							{
								imagecopyresampled($thumbimage, $origimage, 0, 0, $width_ori - $new_w, 0, $new_w, $new_h, $new_w, $new_h);
							}
							elseif($this->plugin_parameter['thumbdetail'] == 3)
							{
								imagecopyresampled($thumbimage, $origimage, 0, 0, 0, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);
							}
							elseif($this->plugin_parameter['thumbdetail'] == 4)
							{
								imagecopyresampled($thumbimage, $origimage, 0, 0, $width_ori - $new_w, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);
							}
							else
							{
								imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $new_w, $new_h, $width_ori, $height_ori);
							}
						}

						if($this->plugin_parameter['watermark'])
						{
							imagejpeg($thumbimage, $this->absolute_path.$this->root_folder.$this->images_dir.'/thumbs/'.$image_hash, $this->plugin_parameter['quality']);
						}
						else
						{
							imagejpeg($thumbimage, $this->absolute_path.$this->root_folder.$this->images_dir.'/thumbs/'.$images[$a]['filename'], $this->plugin_parameter['quality']);
						}
					}
					elseif(substr(strtolower($filenamethumb), -3) == 'png')
					{
						if($this->plugin_parameter['watermark'])
						{
							$origimage = imagecreatefrompng($this->absolute_path.$this->root_folder.$this->images_dir.'/wm/'.$image_hash);
						}
						else
						{
							$origimage = imagecreatefrompng($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$images[$a]['filename']);
						}

						$width_ori = imagesx($origimage);
						$height_ori = imagesy($origimage);
						$thumbimage = imagecreatetruecolor($new_w, $new_h);

						if($this->plugin_parameter['crop'] AND ($this->plugin_parameter['crop_factor'] > 0 AND $this->plugin_parameter['crop_factor'] < 100))
						{
							list($crop_width, $crop_height, $x_coordinate, $y_coordinate) = $this->crop($width_ori, $height_ori);
							imagecopyresampled($thumbimage, $origimage, 0, 0, $x_coordinate, $y_coordinate, $new_w, $new_h, $crop_width, $crop_height);
						}
						else
						{
							if($this->plugin_parameter['thumbdetail'] == 1)
							{
								imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $new_w, $new_h, $new_w, $new_h);
							}
							elseif($this->plugin_parameter['thumbdetail'] == 2)
							{
								imagecopyresampled($thumbimage, $origimage, 0, 0, $width_ori - $new_w, 0, $new_w, $new_h, $new_w, $new_h);
							}
							elseif($this->plugin_parameter['thumbdetail'] == 3)
							{
								imagecopyresampled($thumbimage, $origimage, 0, 0, 0, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);
							}
							elseif($this->plugin_parameter['thumbdetail'] == 4)
							{
								imagecopyresampled($thumbimage, $origimage, 0, 0, $width_ori - $new_w, $height_ori - $new_h, $new_w, $new_h, $new_w, $new_h);
							}
							else
							{
								imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $new_w, $new_h, $width_ori, $height_ori);
							}
						}

						if($this->plugin_parameter['watermark'])
						{
							imagepng($thumbimage, $this->absolute_path.$this->root_folder.$this->images_dir.'/thumbs/'.$image_hash, $this->plugin_parameter['quality_png']);
						}
						else
						{
							imagepng($thumbimage, $this->absolute_path.$this->root_folder.$this->images_dir.'/thumbs/'.$images[$a]['filename'], $this->plugin_parameter['quality_png']);
						}
					}

					imagedestroy($origimage);
					imagedestroy($thumbimage);
				}
			}
		}
	}

	private function crop($width_ori, $height_ori)
	{
		if($width_ori > $height_ori)
		{
			$biggest_side = $width_ori;
		}
		else
		{
			$biggest_side = $height_ori;
		}

		$crop_percent = (1 - ($this->plugin_parameter['crop_factor'] / 100));

		if(!$this->plugin_parameter['ratio'] AND ($this->plugin_parameter['width'] == $this->plugin_parameter['height']))
		{
			$crop_width = $biggest_side * $crop_percent;
			$crop_height = $biggest_side * $crop_percent;
		}
		elseif(!$this->plugin_parameter['ratio'] AND ($this->plugin_parameter['width'] != $this->plugin_parameter['height']))
		{
			if(($width_ori / $this->plugin_parameter['width']) < ($height_ori / $this->plugin_parameter['height']))
			{
				$crop_width = $width_ori * $crop_percent;
				$crop_height = ($this->plugin_parameter['height'] * ($width_ori / $this->plugin_parameter['width'])) * $crop_percent;
			}
			else
			{
				$crop_width = ($this->plugin_parameter['width'] * ($height_ori / $this->plugin_parameter['height'])) * $crop_percent;
				$crop_height = $height_ori * $crop_percent;
			}
		}
		else
		{
			$crop_width = $width_ori * $crop_percent;
			$crop_height = $height_ori * $crop_percent;
		}

		$x_coordinate = ($width_ori - $crop_width) / 2;
		$y_coordinate = ($height_ori - $crop_height) / 2;

		$ret = array($crop_width, $crop_height, $x_coordinate, $y_coordinate);

		return $ret;
	}

	private function htmlImage($image, &$html, $noshow, &$file_info, $a)
	{
		if(!empty($image))
		{
			$imagename = substr($image, 0, -4);
			$type = substr(strtolower($image), -3);
			$image_hash = $this->encrypt($imagename).'.'.$type;

			$file_info_set = false;

			if(!empty($file_info))
			{
				foreach($file_info as $key => $value)
				{
					if($value[0] == $image)
					{
						$image_title = $value[1];

						if(!empty($value[2]))
						{
							$image_description = $value[2];
						}
						else
						{
							$image_description = false;
						}

						// Link for image
						if(!empty($value[3]))
						{
							$image_link_file = $value[3];
						}

						$file_info_set = true;

						// Remove information from file_info array to speed up the process for the following images
						unset($file_info[$key]);
						break;
					}
				}
			}

			if(!$file_info_set)
			{
				$image_title = $imagename;
				$image_description = false;
			}

			if($this->plugin_parameter['iptc'] == 1)
			{
				list($title_iptc, $caption_iptc) = $this->iptcinfo($image);

				if(!empty($title_iptc))
				{
					$image_title = $title_iptc;
				}

				if(!empty($caption_iptc))
				{
					$image_description = $caption_iptc;
				}
			}

			if(empty($noshow))
			{
				if($this->plugin_parameter['list'] AND !$this->plugin_parameter['word'])
				{
					$html .= '<li>';
				}
				elseif($this->plugin_parameter['word'])
				{
					$html .= '<span>';
				}
				else
				{
					$html .= '<li class="sige_cont_'.$_SESSION["sigcount"].'"><span class="sige_thumb">';
				}
			}

			if(($this->plugin_parameter['image_link'] OR !empty($image_link_file)) AND empty($noshow))
			{
				// Use link from captions.txt if provided
				if(!empty($image_link_file))
				{
					// Add http:// if not already set
					if(!preg_match('@http.?://@', $image_link_file))
					{
						$image_link_file = 'http://'.$image_link_file;
					}

					$html .= '<a href="'.$image_link_file.'" title="'.$image_link_file.'" ';
				}
				else
				{
					$html .= '<a href="http://'.$this->plugin_parameter['image_link'].'" title="'.$this->plugin_parameter['image_link'].'" ';
				}

				if($this->plugin_parameter['image_link_new'])
				{
					$html .= 'target="_blank"';
				}

				$html .= '>';
			}
			elseif($this->plugin_parameter['noslim'] AND $this->plugin_parameter['css_image'] AND empty($noshow))
			{
				$html .= '<a class="sige_css_image" href="#sige_thumbnail">';
			}
			elseif(!$this->plugin_parameter['noslim'])
			{
				if($this->plugin_parameter['watermark'])
				{
					if(empty($noshow))
					{
						$html .= '<a href="'.$this->live_site.$this->root_folder.$this->images_dir.'/wm/'.$image_hash.'"';
					}
					else
					{
						$html .= '<span style="display: none"><a href="'.$this->live_site.$this->root_folder.$this->images_dir.'/wm/'.$image_hash.'"';
					}
				}
				else
				{
					if($this->plugin_parameter['resize_images'])
					{
						if(empty($noshow))
						{
							$html .= '<a href="'.$this->live_site.$this->root_folder.$this->images_dir.'/resizedimages/'.$image.'"';
						}
						else
						{
							$html .= '<span style="display: none"><a href="'.$this->live_site.$this->root_folder.$this->images_dir.'/resizedimages/'.$image.'"';
						}
					}
					else
					{
						if(empty($noshow))
						{
							$html .= '<a href="'.$this->live_site.$this->root_folder.$this->images_dir.'/'.$image.'"';
						}
						else
						{
							$html .= '<span style="display: none"><a href="'.$this->live_site.$this->root_folder.$this->images_dir.'/'.$image.'"';
						}
					}
				}

				if(empty($noshow))
				{
					if($this->plugin_parameter['css_image'])
					{
						$html .= ' class="sige_css_image"';
					}
				}

				if($this->plugin_parameter['connect'])
				{
					if($this->plugin_parameter['view'] == 0 OR $this->plugin_parameter['view'] == 5)
					{
						$html .= ' rel="lightbox.sig'.$this->plugin_parameter['connect'].'"';
					}
					elseif($this->plugin_parameter['view'] == 1)
					{
						$html .= ' rel="lytebox.sig'.$this->plugin_parameter['connect'].'"';
					}
					elseif($this->plugin_parameter['view'] == 2)
					{
						$html .= ' rel="lyteshow.sig'.$this->plugin_parameter['connect'].'"';
					}
					elseif($this->plugin_parameter['view'] == 3)
					{
						$html .= ' rel="shadowbox[sig'.$this->plugin_parameter['connect'].']"';
					}
					elseif($this->plugin_parameter['view'] == 4)
					{
						$html .= ' data-milkbox="milkbox-'.$this->plugin_parameter['connect'].'"';
					}
					elseif($this->plugin_parameter['view'] == 6)
					{
						$html .= ' class="venobox" data-gall="venobox-'.$this->plugin_parameter['connect'].'"';
					}
				}
				else
				{
					if($this->plugin_parameter['view'] == 0 OR $this->plugin_parameter['view'] == 5)
					{
						$html .= ' rel="lightbox.sig'.$_SESSION["sigcount"].'"';
					}
					elseif($this->plugin_parameter['view'] == 1)
					{
						$html .= ' rel="lytebox.sig'.$_SESSION["sigcount"].'"';
					}
					elseif($this->plugin_parameter['view'] == 2)
					{
						$html .= ' rel="lyteshow.sig'.$_SESSION["sigcount"].'"';
					}
					elseif($this->plugin_parameter['view'] == 3)
					{
						$html .= ' rel="shadowbox[sig'.$_SESSION["sigcount"].']"';
					}
					elseif($this->plugin_parameter['view'] == 4)
					{
						$html .= ' data-milkbox="milkbox-'.$_SESSION["sigcount"].'"';
					}
					elseif($this->plugin_parameter['view'] == 6)
					{
						$html .= ' class="venobox" data-gall="venobox-'.$_SESSION["sigcount"].'"';
					}
				}

				$html .= ' title="';

				if($this->plugin_parameter['displaynavtip'] AND !empty($this->plugin_parameter['navtip']))
				{
					$html .= $this->plugin_parameter['navtip'].'&lt;br /&gt;';
				}

				if($this->plugin_parameter['displaymessage'] AND isset($this->article_title))
				{
					if(!empty($this->plugin_parameter['message']))
					{
						$html .= $this->plugin_parameter['message'].': ';
					}

					$html .= '&lt;em&gt;'.$this->article_title.'&lt;/em&gt;&lt;br /&gt;';
				}

				if($this->plugin_parameter['image_info'])
				{
					$html .= '&lt;strong&gt;&lt;em&gt;'.$image_title.'&lt;/em&gt;&lt;/strong&gt;';

					if($image_description)
					{
						$html .= ' - '.$image_description;
					}
				}

				if($this->plugin_parameter['print'] == 1)
				{
					if($this->plugin_parameter['watermark'])
					{
						$html .= ' &lt;a href=&quot;'.$this->live_site.'/plugins/content/sige/plugin_sige/print.php?img='.rawurlencode($this->live_site.$this->root_folder.$this->images_dir.'/wm/'.$image_hash).'&amp;name='.rawurlencode($image_title).'&quot; title=&quot;Drucken / Print&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$this->live_site.'/plugins/content/sige/plugin_sige/print.png&quot; /&gt;&lt;/a&gt;';
					}
					else
					{
						if($this->plugin_parameter['resize_images'])
						{
							$html .= ' &lt;a href=&quot;'.$this->live_site.'/plugins/content/sige/plugin_sige/print.php?img='.rawurlencode($this->live_site.$this->root_folder.$this->images_dir.'/resizedimages/'.$image).'&amp;name='.rawurlencode($image_title).'&quot; title=&quot;Drucken / Print&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$this->live_site.'/plugins/content/sige/plugin_sige/print.png&quot; /&gt;&lt;/a&gt;';
						}
						else
						{
							$html .= ' &lt;a href=&quot;'.$this->live_site.'/plugins/content/sige/plugin_sige/print.php?img='.rawurlencode($this->live_site.$this->root_folder.$this->images_dir.'/'.$image).'&amp;name='.rawurlencode($image_title).'&quot; title=&quot;Drucken / Print&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$this->live_site.'/plugins/content/sige/plugin_sige/print.png&quot; /&gt;&lt;/a&gt;';
						}
					}
				}

				if($this->plugin_parameter['download'] == 1)
				{
					if($this->plugin_parameter['watermark'])
					{
						$html .= ' &lt;a href=&quot;'.$this->live_site.'/plugins/content/sige/plugin_sige/download.php?img='.rawurlencode($this->root_folder.$this->images_dir.'/wm/'.$image_hash).'&quot; title=&quot;Download&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$this->live_site.'/plugins/content/sige/plugin_sige/download.png&quot; /&gt;&lt;/a&gt;';
					}
					else
					{
						if($this->plugin_parameter['resize_images'])
						{
							$html .= ' &lt;a href=&quot;'.$this->live_site.'/plugins/content/sige/plugin_sige/download.php?img='.rawurlencode($this->root_folder.$this->images_dir.'/resizedimages/'.$image).'&quot; title=&quot;Download&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$this->live_site.'/plugins/content/sige/plugin_sige/download.png&quot; /&gt;&lt;/a&gt;';
						}
						else
						{
							$html .= ' &lt;a href=&quot;'.$this->live_site.'/plugins/content/sige/plugin_sige/download.php?img='.rawurlencode($this->root_folder.$this->images_dir.'/'.$image).'&quot; title=&quot;Download&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;'.$this->live_site.'/plugins/content/sige/plugin_sige/download.png&quot; /&gt;&lt;/a&gt;';
						}
					}
				}

				if(empty($noshow))
				{
					$html .= '" >';
				}
				else
				{
					$html .= '"></a></span>';
				}
			}

			if(empty($noshow))
			{
				if(!$this->plugin_parameter['list'] AND !$this->plugin_parameter['word'])
				{
					if($this->plugin_parameter['thumbs'])
					{
						$html .= '<img alt="'.$image_title.'" title="'.$image_title;

						if($image_description)
						{
							$html .= ' - '.$image_description;
						}

						if($this->plugin_parameter['watermark'])
						{
							$html .= '" src="'.$this->live_site.$this->root_folder.$this->images_dir.'/thumbs/'.$image_hash.'" />';
						}
						else
						{
							$html .= '" src="'.$this->live_site.$this->root_folder.$this->images_dir.'/thumbs/'.$image.'" />';
						}
					}
					else
					{
						$html .= '<img alt="'.$image_title.'" title="'.$image_title;

						if($image_description)
						{
							$html .= ' - '.$image_description;
						}

						if($this->plugin_parameter['watermark'])
						{
							$html .= '" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/showthumb.php?img='.$this->root_folder.$this->images_dir.'/wm/'.$image_hash.'&amp;width='.$this->plugin_parameter['width'].'&amp;height='.$this->plugin_parameter['height'].'&amp;quality='.$this->plugin_parameter['quality'].'&amp;ratio='.$this->plugin_parameter['ratio'].'&amp;crop='.$this->plugin_parameter['crop'].'&amp;crop_factor='.$this->plugin_parameter['crop_factor'].'&amp;thumbdetail='.$this->plugin_parameter['thumbdetail'].'" />';
						}
						else
						{
							if($this->plugin_parameter['resize_images'])
							{
								$html .= '" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/showthumb.php?img='.$this->root_folder.$this->images_dir.'/resizedimages/'.$image.'&amp;width='.$this->plugin_parameter['width'].'&amp;height='.$this->plugin_parameter['height'].'&amp;quality='.$this->plugin_parameter['quality'].'&amp;ratio='.$this->plugin_parameter['ratio'].'&amp;crop='.$this->plugin_parameter['crop'].'&amp;crop_factor='.$this->plugin_parameter['crop_factor'].'&amp;thumbdetail='.$this->plugin_parameter['thumbdetail'].'" />';
							}
							else
							{
								$html .= '" src="'.$this->live_site.'/plugins/content/sige/plugin_sige/showthumb.php?img='.$this->root_folder.$this->images_dir.'/'.$image.'&amp;width='.$this->plugin_parameter['width'].'&amp;height='.$this->plugin_parameter['height'].'&amp;quality='.$this->plugin_parameter['quality'].'&amp;ratio='.$this->plugin_parameter['ratio'].'&amp;crop='.$this->plugin_parameter['crop'].'&amp;crop_factor='.$this->plugin_parameter['crop_factor'].'&amp;thumbdetail='.$this->plugin_parameter['thumbdetail'].'" />';
							}
						}
					}
				}
				elseif($this->plugin_parameter['list'] AND !$this->plugin_parameter['word'])
				{
					$html .= $image_title;

					if($image_description)
					{
						$html .= ' - '.$image_description;
					}
				}
				elseif($this->plugin_parameter['word'])
				{
					$html .= $this->plugin_parameter['word'];
				}

				if($this->plugin_parameter['css_image'] AND !$this->plugin_parameter['image_link'])
				{
					$html .= '<span>';

					if($this->plugin_parameter['watermark'])
					{
						$html .= '<img src="'.$this->live_site.$this->root_folder.$this->images_dir.'/wm/'.$image_hash.'"';
					}
					else
					{
						if($this->plugin_parameter['resize_images'])
						{
							$html .= '<img src="'.$this->live_site.$this->root_folder.$this->images_dir.'/resizedimages/'.$image.'"';
						}
						else
						{
							$html .= '<img src="'.$this->live_site.$this->root_folder.$this->images_dir.'/'.$image.'"';
						}
					}

					if($this->plugin_parameter['css_image_half'] AND !$this->plugin_parameter['list'])
					{
						$imagedata = getimagesize($this->absolute_path.$this->root_folder.$this->images_dir.'/'.$image);
						$html .= ' width="'.($imagedata[0] / 2).'" height="'.($imagedata[1] / 2).'"';
					}

					$html .= ' alt="'.$image_title.'" title="'.$image_title;

					if($image_description)
					{
						$html .= ' - '.$image_description;
					}

					$html .= '" /></span>';
				}

				if(!$this->plugin_parameter['noslim'] OR $this->plugin_parameter['image_link'] OR $this->plugin_parameter['css_image'] OR !empty($image_link_file))
				{
					$html .= '</a>';
				}

				if($this->plugin_parameter['caption'] AND !$this->plugin_parameter['list'] AND !$this->plugin_parameter['word'])
				{
					if($this->plugin_parameter['single'] AND !empty($this->plugin_parameter['scaption']))
					{
						$html .= '</span><span class="sige_caption">'.$this->plugin_parameter['scaption'].'</span></li>';
					}
					else
					{
						$html .= '</span><span class="sige_caption">'.$image_title.'</span></li>';
					}
				}

				if($this->plugin_parameter['list'] AND !$this->plugin_parameter['word'])
				{
					$html .= '</li>';
				}
				elseif($this->plugin_parameter['word'])
				{
					$html .= '</span>';
				}
				elseif(!$this->plugin_parameter['caption'])
				{
					$html .= '</span></li>';
				}
			}
		}

		if($this->plugin_parameter['column_quantity'] AND empty($noshow))
		{
			if(($a + 1) % $this->plugin_parameter['column_quantity'] == 0)
			{
				$html .= '<br class="sige_clr"/>';
			}
		}
	}

	private function iptcinfo($image)
	{
		$info = array();
		$data = array();

		$size = getimagesize(JPATH_SITE.$this->root_folder.$this->images_dir.'/'.$image, $info);

		if(isset($info['APP13']))
		{
			$iptc_php = iptcparse($info['APP13']);

			if(is_array($iptc_php))
			{
				if(isset($iptc_php["2#120"][0]))
				{
					$data['caption'] = $iptc_php["2#120"][0];
				}
				else
				{
					$data['caption'] = '';
				}

				if(isset($iptc_php["2#005"][0]))
				{
					$data['title'] = $iptc_php["2#005"][0];
				}
				else
				{
					$data['title'] = '';
				}

				if($this->plugin_parameter['iptcutf8'] == 1)
				{
					$iptctitle = html_entity_decode($data['title'], ENT_NOQUOTES);
					$iptccaption = html_entity_decode($data['caption'], ENT_NOQUOTES);
				}
				else
				{
					$iptctitle = utf8_encode(html_entity_decode($data['title'], ENT_NOQUOTES));
					$iptccaption = utf8_encode(html_entity_decode($data['caption'], ENT_NOQUOTES));
				}
			}
			else
			{
				$iptctitle = '';
				$iptccaption = '';
			}
		}
		else
		{
			$iptctitle = '';
			$iptccaption = '';
		}
		$ret = array($iptctitle, $iptccaption);

		return $ret;
	}

	private function timeasc($a, $b)
	{
		return strcmp($a['timestamp'], $b['timestamp']);
	}

	private function timedesc($a, $b)
	{
		return strcmp($b['timestamp'], $a['timestamp']);
	}

}
