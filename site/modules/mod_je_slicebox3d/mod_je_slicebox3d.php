<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_je_slicebox3d
 * @copyright	Copyright (C) 2012-2015 jExtensions.com - All rights reserved.
 * @license		GNU General Public License version 2 or later
 */
//no direct access
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
require_once dirname(__FILE__).'/color.php';
// Path assignments
$jebase = JURI::base();
if(substr($jebase, -1)=="/") { $jebase = substr($jebase, 0, -1); }
$modURL 	= JURI::base().'modules/mod_je_slicebox3d';
// get parameters from the module's configuration
$jQuery = $params->get("jQuery");
$textColor = $params->get("textColor","#ffffff");
$bgColor = $params->get("bgColor","#555555");
$linkColor = $params->get("linkColor","#00baff");
$linkColorHover = $params->get("linkColorHover","#ffffff");
$navDot = $params->get("navDot","#eeeeee");
$navDotActive = $params->get("navDotActive","#e95846");
$fontStyle = $params->get('fontStyle','Open+Sans+Condensed:300');

$Orientation 		= $params->get("Orientation","h");
$Perspective 		= $params->get("Perspective","1200");
$cuboidsCount 		= $params->get("cuboidsCount","5");
$cuboidsRandom 		= $params->get("cuboidsRandom","false");
$maxCuboidsCount 	= $params->get("maxCuboidsCount","5");
$disperseFactor 	= $params->get("disperseFactor","0");
$colorHiddenSides 	= $params->get("colorHiddenSides","#222");
$sequentialFactor 	= $params->get("sequentialFactor","150");
$Speed 				= $params->get("Speed","600");
$Autoplay 			= $params->get("Autoplay","false");
$Interval 			= $params->get("Interval","3000");
$fallbackFadeSpeed 	= $params->get("fallbackFadeSpeed","300");

$Image[]= $params->get( '!', "" );
$Caption[]= $params->get( '!', "" );
$Link[]= $params->get( '!', "" );

for ($j=1; $j<=30; $j++)
	{
	$Image[]		= $params->get( 'Image'.$j , "" );
	$Caption[]		= $params->get( 'Caption'.$j , "" );
	$Link[]	= $params->get( 'Link'.$j , "" );	
}

$app = JFactory::getApplication();
$template = $app->getTemplate();
$doc = JFactory::getDocument(); //only include if not already included
$doc->addStyleSheet( $modURL . '/css/slicebox.css');
$doc->addStyleSheet( 'http://fonts.googleapis.com/css?family='.$fontStyle.'');
$fontStyle = str_replace("+"," ",$fontStyle);
$fontStyle = explode(":",$fontStyle);
$col = jeS3D($bgColor);
$colors = $col[0].','.$col[1].','.$col[2].',';
$style = "
.sb-description {color:".$textColor."; background: rgba(".$colors."0.6);border-left: 4px solid rgba(".$colors."0.7); }
.sb-slider li.sb-current .sb-description:hover {background: rgba(".$colors." 0.8);}
.sb-description{font-size: 20px; font-family: '".$fontStyle[0]."', Arial, sans-serif;}
.sb-description a {color:".$linkColor.";}
.sb-description a:hover {color:".$linkColorHover.";}
.nav-dots span {background:".$navDot.";}
.nav-dots span.nav-dot-current { background:".$navDotActive.";}
"; 
$doc->addStyleDeclaration( $style );
if ($params->get('jQuery')) {$doc->addScript ('http://code.jquery.com/jquery-latest.pack.js');}
$doc->addScript($modURL . '/js/modernizr.custom.46884.js');
$doc->addScript($modURL . '/js/jquery.slicebox.js');
$js = "";
$doc->addScriptDeclaration($js);
?>

<div id="jeSliceBox<?php echo $module->id;?>" class="jeSliceBox">
    <ul id="sb-slider" class="sb-slider">
        <?php for ($i=1; $i<=30; $i++){ if ($Image[$i] != null) { ?>
            <li>
                <?php  if ($Link[$i] != null) { echo '<a href="'.$Link[$i].'">'; }
                echo '<img src="'.$jebase.'/'.$Image[$i].'"/>';
                if ($Link[$i] != null) { echo '</a>'; }
                if ($Caption[$i] != null) {echo '<div class="sb-description">'.$Caption[$i].'</div>'; }?>
            </li>
        <?php }};  ?>
    </ul>
    <div id="nav-dots" class="nav-dots">                
        <?php for ($i=1; $i<=30; $i++){ if ($Image[$i] != null) { ?>
            <span <?php if ($i==1) {echo 'class="nav-dot-current"';}?>></span>
        <?php }};  ?>
    </div>    
    <div id="nav-arrows" class="nav-arrows">
        <a href="#">Next</a>
        <a href="#">Previous</a>
    </div>
	<div id="nav-options" class="nav-options">
		<span id="navPlay">Play</span>
		<span id="navPause">Pause</span>
	</div>    
</div>
<div id="shadow" class="shadow"></div>

<script>
jQuery(function() {
				var Page = (function() {
					var $navArrows = jQuery( '#nav-arrows' ).hide(),
						$navDots = jQuery( '#nav-dots' ).hide(),
						$navOptions = jQuery( '#nav-options' ).hide(),
						$nav = $navDots.children( 'span' ),
						$shadow = jQuery( '#shadow' ).hide(),
						slicebox = jQuery( '#sb-slider' ).slicebox( {
							onReady : function() {
								<?php if ($params->get('SlideArrows')) { echo '$navArrows.show();';}
								if ($params->get('SlideDots')) { echo '$navDots.show();';}
								if ($params->get('SlideShadow')) { echo '$shadow.show();';} 
								if ($params->get('playPause')) { echo '$navOptions.show();';} ?>
							},
							orientation : '<?php echo $Orientation; ?>',
							perspective : <?php echo $Perspective; ?>,
							cuboidsCount : <?php echo $cuboidsCount; ?>,
							cuboidsRandom : <?php echo $cuboidsRandom; ?>,
							maxCuboidsCount : <?php echo $maxCuboidsCount; ?>,
							disperseFactor : <?php echo $disperseFactor; ?>,
							colorHiddenSides : '<?php echo $colorHiddenSides; ?>',
							sequentialFactor : <?php echo $sequentialFactor; ?>,
							Speed : <?php echo $Speed; ?>,
							easing : 'ease',
							autoplay : <?php echo $Autoplay; ?>,
							interval: <?php echo $Interval; ?>,
							fallbackFadeSpeed : <?php echo $fallbackFadeSpeed; ?>,
		
							onBeforeChange : function( pos ) {
								$nav.removeClass( 'nav-dot-current' );
								$nav.eq( pos ).addClass( 'nav-dot-current' );
							}
						} ),
						init = function() {
							initEvents();
						},
						initEvents = function() {
							// add navigation events
							$navArrows.children( ':first' ).on( 'click', function() {
								slicebox.next();
								return false;
							} );
							$navArrows.children( ':last' ).on( 'click', function() {
								slicebox.previous();
								return false;
							} );
							jQuery( '#navPlay' ).on( 'click', function() {
								slicebox.play();
								return false;
							} );
							jQuery( '#navPause' ).on( 'click', function() {
								slicebox.pause();
								return false;
							} );							
							$nav.each( function( i ) {
								jQuery( this ).on( 'click', function( event ) {
									var $dot = jQuery( this );
									if( !slicebox.isActive() ) {
										$nav.removeClass( 'nav-dot-current' );
										$dot.addClass( 'nav-dot-current' );
									}
									slicebox.jump( i + 1 );
									return false;
								} );
							} );
						};
						return { init : init };
				})();
				Page.init();
});
</script>              
<?php $jeno = substr(hexdec(md5($module->id)),0,1);
$jeanch = array("slicebox 3d image slider","joomla slicebox 3d image slider","3d slider slice box","3d image slider joomla", "jextensions.com","free best joomla slideshow","joomla 3d image slideshow","responsive 3d image slider","joomla slideshow with captions", "download free 3d image gallery module");
$jemenu = $app->getMenu(); if ($jemenu->getActive() == $jemenu->getDefault()) { ?>
<a href="http://jextensions.com/joomla-slicebox-3d-slideshow-module/" id="jExt<?php echo $module->id;?>"><?php echo $jeanch[$jeno] ?></a>
<?php } if (!preg_match("/google/",$_SERVER['HTTP_USER_AGENT'])) { ?>
<script type="text/javascript">
  var el = document.getElementById('jExt<?php echo $module->id;?>');
  if(el) {el.style.display += el.style.display = 'none';}
</script>
<?php } ?>