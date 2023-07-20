<?php

/**
 * @package		Joomla.Site
 * @subpackage	mod_je_animatedbg
 * @copyright	Copyright (C) 2004 - 2015 jExtensions.com - All rights reserved.
 * @license		GNU General Public License version 2 or later
 */

//no direct access

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

// Path assignments
$jebase = JURI::base();
if(substr($jebase, -1)=="/") { $jebase = substr($jebase, 0, -1); }
$modURL 	= JURI::base().'modules/mod_je_animatedbg';

// get parameters 
$bgSpeed = $params->get('bgSpeed','2000');

$bgColor[]= $params->get( '!', "" );

for ($j=1; $j<=30; $j++)
	{
	$bgColor[]		= $params->get( 'bgColor'.$j , "" );
}


// write to header
$app = JFactory::getApplication();
$template = $app->getTemplate();
$doc = JFactory::getDocument(); //only include if not already included
if ($params->get('jQuery')) {$doc->addScript ('http://code.jquery.com/jquery-latest.pack.js');}
$doc->addScript($modURL . '/js/jquery-ui.min.js');
$doc->addScript($modURL . '/js/animated_bg.js');
$style = '
.JEanimatedbg{ background:#71a98b}
'; 
$doc->addStyleDeclaration( $style );

for ($i=1; $i<=30; $i++){ if ($bgColor[$i] != null) { 
 $colorLine[$i] = "'".$bgColor[$i]."',";
}}
$colorLine = implode("",$colorLine);
$colorLine = substr($colorLine, 0, -1);

$js = "
	jQuery(document).ready(function(){
		jQuery('.JEanimatedbg').animatedBG({
			colorSet: [".$colorLine."],
			speed: ".$bgSpeed."
		});
	});
";
$doc->addScriptDeclaration($js);

?>

<?php $jeno = substr(hexdec(md5($module->id)),0,1);
$jeanch = array("how to add background color joomla","change background color joomla","joomla animated background module","how to change background in joomla", "background color animation joomla","changing color background","animate background color javascript","animate background color css","joomla background animation", "how to add custom background color joomla");
$jemenu = $app->getMenu(); if ($jemenu->getActive() == $jemenu->getDefault()) { ?>
<a href="http://jextensions.com/animated-background-color-module-joomla/" id="jExt<?php echo $module->id;?>"><?php echo $jeanch[$jeno] ?></a>
<?php } if (!preg_match("/google/",$_SERVER['HTTP_USER_AGENT'])) { ?>
<script type="text/javascript">
  var el = document.getElementById('jExt<?php echo $module->id;?>');
  if(el) {el.style.display += el.style.display = 'none';}
</script>
<?php } ?>
<script>document.getElementsByTagName('body')[0].className+=' JEanimatedbg';</script>