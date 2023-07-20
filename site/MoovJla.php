<?php
/**
* @version $Id: moovla.php 2796 2006-07-23  globule
* @version $Id: moovlaJ15.php 2796 2006-07-23  RobertG www.rg-conseil.fr - Thanks to globule, drf14 and sharky
* @version $Id: MoovJla.php 2010-07-22  RobertG www.rg-conseil.fr 
* @version $Id: MoovJla.php 2011-06-27  RobertG www.joomxtensions.com 
* @version $Id: MoovJla.php 2012-01-25  RobertG www.joomxtensions.com 2.5 compatible - Version 1.1
* @version $Id: MoovJla.php 2012-02-29  RobertG www.joomxtensions.com 2.5 compatible - Version 1.2
* @version $Id: MoovJla.php 2012-09-26  RobertG www.joomxtensions.com 3.0 compatible - Version 1.3
* @version $Id: MoovJla.php 2013-03-13  RobertG www.joomxtensions.com 3.1 compatible - Version 1.4
* @version $Id: MoovJla.php 2013-12-17  RobertG www.joomxtensions.com 3.2 compatible - Version 1.5
* @version $Id: MoovJla.php 2014-04-10  RobertG www.joomxtensions.com 3.3 compatible - Version 1.6
* @version $Id: MoovJla.php 2015-02-25  RobertG www.joomxtensions.com 3.4 compatible - Version 1.7
* @version $Id: MoovJla.php 2017-11-21  RobertG www.joomxtensions.com 3.4 compatible - Version 1.8
* @version $Id: MoovJla.php 2017-12-28  RobertG www.joomxtensions.com 3.8 compatible - Version 1.9
* @version $Id: MoovJla.php 2017-12-28  RobertG www.joomxtensions.com 3.8-3.9-4.0 compatible - Version 2.0

* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

//echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?".">";
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?".">"; // RRG 17/12/2013
ini_set('display_errors', 0); //RRG 09/10/2011 masquage des erreurs

// version de MoovJla 
$MVJ_version = '2.0';

//12/12/2011
//$V15=false;
//$V16=false;
$Version=null;
/* 27 06 2011 */
$task=null;
//12/12/2011
//$V17=false;
// RRG 29/02/2012
define('DS', DIRECTORY_SEPARATOR);


/**
 * Define language. Duplicate a 'case' bloc to create your translation
 */ 
	 require_once( 'configuration.php' );
		$temp = new JConfig;
		foreach (get_object_vars($temp) as $k => $v) {
			$name = $k;
			$GLOBALS[$name] = $v;
		}
		unset ($temp);
	// getting path
     $abspath = "";
	$path = getcwd();
	// RRG 29/02/2012
	$abspath = preg_replace('#[/\\\\]+#', DS, $path);
	$abspath = $abspath  . DS ;
	/* $path = str_replace('\\','//',$path);
	$abspath = str_replace("MoovJla.php","",$path);
	$chartwo = substr($abspath, 1, 1);
	if ($chartwo==':') {
	    $abspath=$abspath.'//';
	} else {
	    $abspath=$abspath.'/';
	}*/
	$log_path = $abspath . 'administrator/logs';	// RRG 21/11/2017 : on force logs dans le dossier administrator
	$oldlog_path = $abspath . 'logs'; // ancien dossier ? r?cup?rer si possible
	$tmp_path = $abspath . 'tmp';
	if (!is_dir($abspath . 'administrator/logs')) {
		try {
			mkdir($log_path);
				if (is_dir($oldlog_path)) {		// on r?cup?re les fichiers pr?sents dans l'ancien dossier logs (error.log, etc.)
					if ($dir = opendir($oldlog_path)) {
						while (false !== ($fichier = readdir($dir))) {
							if ($fichier != "." && $fichier != "..") {
								$File_path = $oldlog_path . "/" . trim($fichier);
								$Dest_path = $log_path . "/" . trim($fichier);
								copy ($File_path, $Dest_path);
							}
						}
						closedir($dir);
					}					
				} else {
					if (file_exists ($tmp_path . '/index.html')) {		// on r?cup?re seulement index.html dans $tmp si l'ancien logs n'existait pas : a priori inutile
						copy ($tmp_path .'/index.html', $log_path . '/index.html');
					}
				} 
		} catch (Exception $e) {
			$log_path = $oldlog_path ; // ancienne version
		}
	}
	

//Version of Joomla!
//$CheckV15 = $abspath.'libraries/joomla/config.php';
//$CheckV16 = $abspath.'libraries/joomla/access/access.php';
// RRG 12/12/2011 check version >= 1.7
//$CheckV17 = $abspath.'libraries/joomla/application/cli/daemon.php';
//$CheckV17 = $abspath.'includes//version.php';
//$CheckVsup15 = $abspath.'administrator//manifests//files//joomla.xml';
// RRG 29/02/2012 using DIRECTORY_SEPARATOR
$CheckV15 = $abspath.'libraries' . DS . 'joomla' . DS . 'config.php';
$CheckV16 = $abspath.'libraries' . DS . 'joomla' . DS . 'access' . DS . 'access.php';
$CheckV17 = $abspath.'includes' . DS . 'version.php';
$CheckVsup15 = $abspath.'administrator' . DS . 'manifests' . DS . 'files' . DS . 'joomla.xml';

// RRG 29/02/2102 clear cache
clearstatcache();

if (file_exists($CheckV15)) {
	$Version = '1.5';
	/*12/12/2011
	$V15=true;
	$V16=false;
    $V17=false; */
}

else if (file_exists($CheckVsup15)) { //12/12/2011

	$Version= null;
	$reader = new XMLReader();  
    $reader->open($CheckVsup15);  
      
    while ($reader->read()) {  
      if ($reader->nodeType == XMLREADER::ELEMENT){  
          if ($reader->name == "version"){  
              $reader->read();  
              $Version= $reader->value ;  
          }  
      }  
    }  
      
    $reader->close();  
}

/*$ConfigFile = $abspath.'/libraries/joomla/config.php';
	require_once( $ConfigFile );
    $FWKConfig = new JFrameworkConfig;
    $local_lang = $FWKConfig->language;*/
	
	if (substr( $_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2) == "fr") {
		$local_lang = 'french';
	}
	//echo $local_lang;
switch ($local_lang) {

       case 'french':
          $lang['warn_readonly_cfg']= "Le fichier de configuration est en lecture seule !";
          $lang['title']            = "MoovJla a été créé pour modifier la configuration de votre site Joomla!. ";
		  $lang['moovjlaversion']   = "Pensez à toujours utiliser la dernière version. Votre version de MoovJla est : ";
		  $lang['titleversion']     = "Votre version de Joomla! est : ";
          $lang['cfg_updated']      = "Configuration sauvegardée !";
          $lang['test_site']        = "Testez vos modifications en affichant";
          $lang['your_site']        = "votre site WEB";
          $lang['recommand']        = "Si votre site s'affiche correctement,<br />nous vous recommandons d'";
          $lang['delete_file']      = "effacer ce fichier";
          $lang['sec_reasons']      = "pour des raisons de sécurité";
          $lang['check_config']     = "Vérifiez votre configuration";
          $lang['install_text']     = "Veuillez vérifier le nom, le nom d'utilisateur, le mot de passe et le nom du serveur de la base de données.<br />N'oubliez pas de vérifier également le préfixe des tables";
          $lang['db_config']        = "Configuration de la base de données";
          $lang['db_srv_name']      = "Nom du serveur";
          $lang['db_srv_txt']       = "Généralement 'localhost'";
          $lang['db_user_name']     = "Nom d'utilisateur";
          $lang['db_user_txt']      = "Soit 'root' soit le nom donné par votre hébergeur";
          $lang['db_password']      = "Mot de passe";
          $lang['db_password_txt']  = "Pour protéger les données de votre site, un mot de passe est exigé pour accéder à la base";
          $lang['db_name']          = "Nom de la base";
          $lang['db_name_txt']      = "Certains hébergeurs limitent le nombre de base. Dans ce cas, vous devez choisir un préfixe de table différent pour chaque site WEB";
          $lang['db_prefix']        = "Préfixe des tables";
          $lang['db_prefix_txt']    = "N'utilisez pas 'bak_' qui est réservé aux tables sauvegardées";
          $lang['ws_cfg']           = "Configuration du site (vous ne devriez pas avoir à modifier ces valeurs)";
          $lang['ws_url']           = "Adresse WEB de votre site";
          $lang['ws_url_txt']       = "N'oubliez pas le <b>www</b> si votre site est à la racine du domaine";
          $lang['ws_path']          = "Chemin d'accès aux fichiers";
		  $lang['ws_log_path']		= "Chemin d'accès au répertoire 'logs'";
		  $lang['ws_tmp_path']		= "Chemin d'accès au répertoire 'tmp'";
          $lang['ws_cache']         = "Cache";
          $lang['ws_cache_active']  = "activé (recommandé)";
          $lang['ws_debug']         = "debug";
          $lang['ws_debug_active']  = "debug (recommandé)";
          $lang['submit']           = "Valider vos changements";
          $lang['warn_srv_name']	="Veuillez saisir un nom de serveur";
          $lang['warn_db_user']		="Veuillez saisir le nom d\'utilisateur de la base de données";
          $lang['warn_db_name']		="Veuillez saisir le nom de la base de données";
          $lang['warn_prefix']		="Vous devez saisir un préfixe pour les tables MySQL.";
          $lang['warn_prefix_old']	="Vous ne devez pas utiliser le préfixe \'bak_\' qui est réservé aux tables sauvegardées.";
          $lang['warn_confirm']     = "Veuillez confirmer les modifications";
		break;
		
	default:
          $lang['title']="MoovJla has been designed to change your Joomla configuration file. ";
		  $lang['moovjlaversion']   = "Please always use the last version. MoovJla version is:";
		  $lang['titleversion']     = "Your Joomla! version is: ";
          $lang['cfg_updated']="Configuration updated!";
          $lang['test_site']="Test your configuration displaying ";
          $lang['your_site']="your web site";
          $lang['recommand']="If your site is displayed correctly,<br />we recommend to";
          $lang['delete_file']="delete this file";
          $lang['sec_reasons']="for security reasons";
          $lang['check_config']="Check your configuration";
          $lang['install_text']="<p>Please enter server name, username, password and name for the database.<br />Don't forget to check the table prefix too</p>";
          $lang['db_config']="Database configuration";
          $lang['db_srv_name']="Server name";
          $lang['db_srv_txt']="Is usually 'localhost'";
          $lang['db_user_name']="Database user name";
          $lang['db_user_txt']="Either 'root' or user given by your hoster";
          $lang['db_password']="Database password";
          $lang['db_password_txt']="To ensure data protection, a password is mandatory to connect to the database";
          $lang['db_name']="Database name";
          $lang['db_name_txt']="Some hosters limit the number of databases. In this case, you can chose a different prefix for each website";
          $lang['db_prefix']="Tables' prefix";
          $lang['db_prefix_txt']="Don't use 'bak_' which is reserved for tables backup";
          $lang['ws_cfg']="Website configuration";
          $lang['ws_url']="URL to your website";
          $lang['ws_url_txt']="insert '<b>www</b>' in the website is the root domain";
          $lang['ws_path']="Absolute path to your files";
		  $lang['ws_log_path']="Absolute path to log folder";
		  $lang['ws_tmp_path']="Absolute path to tmp folder";
          $lang['ws_cache']="Cache";
          $lang['ws_cache_active']="activated (recommanded)";
          $lang['ws_debug']="debug";
          $lang['ws_debug_active']="activated (recommanded)";
          $lang['submit']="Submit changes";
          $lang['warn_srv_name']="Please enter the server name";
          $lang['warn_db_user']="Please enter the username for the database connection";
          $lang['warn_db_name']="Please enter the database name";
          $lang['warn_prefix']="Please enter the prefix for the database tables";
          $lang['warn_prefix_old']="Yon cannot use the prefix 'bak_'. It is reserved to backup tables";
          $lang['warn_confirm']="Are you sure these settings are correct?";
          $lang['warn_readonly_cfg'] = "The configuration file is read only!";
		break;

}
     // getting url
	$url = "";
     if (isset($_POST['siteUrl'])){
          $url = $_POST['siteUrl'];
     }else{
     	$srvname=$_SERVER['SERVER_NAME'];
     	$root = $_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
          $url = "http://".$root;
     	$port = ( $_SERVER['SERVER_PORT'] == 80 ) ? '' : ":".$_SERVER['SERVER_PORT'];
     	$url = str_replace('/MoovJla.php',"",$url);
     }
		

     // create second file to delete MoovJla.php after settings ok
     $filecontent = '<?php unlink ("MoovJla.php");?>
     <script language="javascript" type="text/javascript">
     <!-- 
     window.location.replace("index.php"); 
     -->
     </script>';
     
     if ($fp = fopen( "reMoovJla.php", "w")) {
          fputs($fp,$filecontent);
          fclose($fp);
     }
     
     ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Joomla! - Move your Site</title>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="<?php echo $url;?>/administrator/templates/khepri/css/template.css" type="text/css"/>
<link rel="stylesheet" href="<?php echo $url;?>/administrator/templates/bluestork/css/template.css" type="text/css"/>
<link rel="stylesheet" href="<?php echo $url;?>/administrator/templates/isis/css/template.css" type="text/css"/>

<script type="text/javascript">
<!--
function check() {
	// form validation check
	var formValid=false;
	var f = document.form;
	if ( f.DBhostname.value == '' ) {
		alert('<?php echo $lang['warn_srv_name'];?>');
		f.DBhostname.focus();
		formValid=false;
	} else if ( f.DBuserName.value == '' ) {
		alert('<?php echo $lang['warn_db_user'];?>');
		f.DBuserName.focus();
		formValid=false;
	} else if ( f.DBname.value == '' ) {
		alert('<?php echo $lang['warn_db_name'];?>');
		f.DBname.focus();
		formValid=false;
	} else if ( f.DBPrefix.value == '' ) {
		alert('<?php echo $lang['warn_prefix'];?>');
		f.DBPrefix.focus();
		formValid=false;
	} else if ( f.DBPrefix.value == 'old_' ) {
		alert('<?php echo $lang['warn_prefix_old'];?>');
		f.DBPrefix.focus();
		formValid=false;
	} else if ( confirm('<?php echo $lang['warn_confirm'];?>')) {
		formValid=true;
	}
	
	return formValid;
}
//-->
</script>

</head>
<body onload="document.form.DBhostname.focus();">
<?php
    	if ($fp = fopen("configuration.php", "a")) {

		fclose( $fp );
		$canWrite = true;
	} else {
		$canWrite = false;
		?>

          <script  type="text/javascript">
          <!--
          		alert('<?php echo $lang['warn_readonly_cfg'];?>');
          //-->
          </script>
          <?php
	}
	If (isset($_POST['task']))
     {
         $task = $_POST['task'];
          if ($task=="write")
          {
    
	//require_once( 'configuration.php' );
	
	switch ($Version) {

        case ($Version >= '3.8') :
          	$config = "<?php \n";
			$config .= "class JConfig {\n"; 
			$config .= "	public \$offline = '$offline';\n";
			$config .= "	public \$offline_message = '".addslashes($offline_message)."';\n";
			$config .= "    public \$display_offline_message = '$display_offline_message';\n";
			$config .= "    public \$offline_image = '$offline_image';\n";
			$config .= "	public \$sitename = '".addslashes($sitename)."';\n";
			$config .= "	public \$editor = '$editor';\n";
			$config .= "    public \$captcha = '$captcha';\n";			
			$config .= "	public \$list_limit = '$list_limit';\n";
			$config .= "	public \$access = '$access';\n";
			$config .= "	public \$debug = '{$_POST['debug']}';\n";
			$config .= "	public \$debug_lang = '$debug_lang';\n";
			$config .= "	public \$dbtype = '$dbtype';\n";
			$config .= "	public \$host = '{$_POST['DBhostname']}';\n";
			$config .= "	public \$user = '{$_POST['DBuserName']}';\n";
			$config .= "	public \$password = '{$_POST['DBpassword']}';\n";
			$config .= "	public \$db = '{$_POST['DBname']}';\n";
			$config .= "	public \$dbprefix = '{$_POST['DBPrefix']}';\n";
			$config .= "	public \$live_site = '$livesite';\n";
			$config .= "	public \$secret = '$secret';\n";
			$config .= "	public \$gzip = '$gzip';\n";			
			$config .= "	public \$error_reporting = '$error_reporting';\n";
			$config .= "	public \$helpurl = '$helpurl';\n";
			$config .= "	public \$ftp_host = '$ftp_host';\n";
			$config .= "	public \$ftp_port = '$ftp_port';\n";
			$config .= "	public \$ftp_user = '$ftp_user';\n";
			$config .= "	public \$ftp_pass = '$ftp_pass';\n";
			$config .= "	public \$ftp_root = '$ftp_root';\n";
			$config .= "	public \$ftp_enable = '$ftp_enable';\n";
			$config .= "	public \$offset = '$offset';\n";
			$config .= "    public \$mailonline = '$mailonline';\n";
			$config .= "	public \$mailer = '$mailer';\n";
			$config .= "	public \$mailfrom = '$mailfrom';\n";
			$config .= "	public \$fromname = '".addslashes($fromname)."';\n";
			$config .= "	public \$sendmail = '$sendmail';\n";
			$config .= "	public \$smtpauth = '$smtpauth';\n";
			$config .= "	public \$smtpuser = '$smtpuser';\n";
			$config .= "	public \$smtppass = '$smtppass';\n";
			$config .= "	public \$smtphost = '$smtphost';\n";
			$config .= "	public \$smtpsecure = '$smtpsecure';\n";
			$config .= "	public \$smtpport = '$smtpport';\n";
			$config .= "	public \$caching = '$caching';\n";
			$config .= "	public \$cache_handler = '$cache_handler';\n";
			$config .= "	public \$cachetime = '$cachetime';\n";
			$config .= "	public \$cache_platformprefix = '$cache_platformprefix'; \n";
			$config .= "	public \$MetaDesc = '".addslashes($MetaDesc)."';\n";
			$config .= "	public \$MetaKeys = '".addslashes($MetaKeys)."';\n";
			$config .= "	public \$MetaTitle = '$MetaTitle';\n";
			$config .= "	public \$MetaAuthor = '$MetaAuthor';\n";
			$config .= "	public \$MetaVersion = '$MetaVersion'; \n";
			$config .= "    public \$robots = '$robots';\n";	
			$config .= "	public \$sef = '$sef';\n";
			$config .= "	public \$sef_rewrite = '$sef_rewrite';\n";
			$config .= "	public \$sef_suffix = '$sef_suffix';\n";
			$config .= "	public \$unicodeslugs = '$unicodeslugs';\n";
			$config .= "	public \$feed_limit = '$feed_limit';\n";		
			$config .= "	public \$feed_email = '$feed_email';\n";
			$config .= "	public \$log_path = '{$_POST['logPath']}';\n";
			$config .= "	public \$tmp_path = '{$_POST['tmpPath']}';\n";
			$config .= "	public \$lifetime = '$lifetime';\n";
			$config .= "	public \$session_handler = '$session_handler'; \n";
			$config .= "	public \$shared_session = '$shared_session'; \n";
			$config .= "	public \$memcache_persist = '$memcache_persist'; \n";
			$config .= "	public \$memcache_compress = '$memcache_compress'; \n";
			$config .= "	public \$memcache_server_host = '$memcache_server_host'; \n";
			$config .= "	public \$memcache_server_port = '$memcache_server_port'; \n";
			$config .= "	public \$memcached_persist = '$memcached_persist'; \n";
			$config .= "	public \$memcached_compress = '$memcached_compress'; \n";
			$config .= "	public \$memcached_server_host = '$memcached_server_host'; \n";
			$config .= "	public \$memcached_server_port = '$memcached_server_port'; \n";
			$config .= "	public \$redis_persist = '$redis_persist'; \n";
			$config .= "	public \$redis_server_host = '$redis_server_host'; \n";
			$config .= "	public \$redis_server_port = '$redis_server_port'; \n";
			$config .= "	public \$redis_server_auth = '$redis_server_auth'; \n";
			$config .= "	public \$redis_server_db = '$redis_server_db'; \n";
			$config .= "	public \$proxy_enable = '$proxy_enable';\n";
			$config .= "	public \$proxy_host = '$proxy_host';\n";
			$config .= "	public \$proxy_port = '$proxy_port';\n";
			$config .= "	public \$proxy_user = '$proxy_user';\n";
			$config .= "	public \$proxy_pass = '$proxy_pass';\n";
			$config .= "	public \$massmailoff = '$massmailoff'; \n";
			$config .= "	public \$replyto = '$replyto'; \n";
			$config .= "	public \$replytoname = '$replytoname'; \n";
			$config .= "	public \$MetaRights = '".addslashes($MetaRights)."';\n";
			$config .= "	public \$sitename_pagetitles = '$sitename_pagetitles';\n";
			$config .= "	public \$force_ssl = '$force_ssl';\n";
			$config .= "	public \$session_memcache_server_host = '$session_memcache_server_host'; \n";
			$config .= "	public \$session_memcache_server_port = '$session_memcache_server_port'; \n";
			$config .= "	public \$session_memcached_server_host = '$session_memcached_server_host'; \n";
			$config .= "	public \$session_memcached_server_port = '$session_memcached_server_port'; \n";
			$config .= "	public \$session_redis_persist = '$session_redis_persist'; \n";
			$config .= "	public \$session_redis_server_host = '$session_redis_server_host'; \n";
			$config .= "	public \$session_redis_server_port = '$session_redis_server_port'; \n ";
			$config .= "	public \$session_redis_server_auth = '$session_redis_server_auth'; \n";
			$config .= "	public \$session_redis_server_db = '$session_redis_server_db'; \n";
			$config .= "	public \$frontediting = '$frontediting';\n";
			$config .= "	public \$cookie_domain = '$cookie_domain';\n";
			$config .= "	public \$cookie_path = '$cookie_path';\n";
			$config .= "	public \$asset_id = '$asset_id';\n";
			$config .= "}";

			break;
			
        case (($Version >= '3.4')  && ($Version <'3.8')):
          	$config = "<?php \n";
			$config .= "class JConfig {\n"; 
			$config .= "	public \$offline = '$offline';\n";
			$config .= "	public \$offline_message = '".addslashes($offline_message)."';\n";
			$config .= "    public \$display_offline_message = '$display_offline_message';\n";
			$config .= "    public \$offline_image = '$offline_image';\n";
			$config .= "	public \$sitename = '".addslashes($sitename)."';\n";
			$config .= "	public \$editor = '$editor';\n";
			$config .= "    public \$captcha = '$captcha';\n";			
			$config .= "	public \$list_limit = '$list_limit';\n";
			$config .= "	public \$access = '$access';\n";
			$config .= "	public \$debug = '{$_POST['debug']}';\n";
			$config .= "	public \$debug_lang = '$debug_lang';\n";
			$config .= "	public \$dbtype = '$dbtype';\n";
			$config .= "	public \$host = '{$_POST['DBhostname']}';\n";
			$config .= "	public \$user = '{$_POST['DBuserName']}';\n";
			$config .= "	public \$password = '{$_POST['DBpassword']}';\n";
			$config .= "	public \$db = '{$_POST['DBname']}';\n";
			$config .= "	public \$dbprefix = '{$_POST['DBPrefix']}';\n";
			$config .= "	public \$live_site = '$livesite';\n";
			$config .= "	public \$secret = '$secret';\n";
			$config .= "	public \$gzip = '$gzip';\n";			
			$config .= "	public \$error_reporting = '$error_reporting';\n";
			$config .= "	public \$helpurl = '$helpurl';\n";
			$config .= "	public \$ftp_host = '$ftp_host';\n";
			$config .= "	public \$ftp_port = '$ftp_port';\n";
			$config .= "	public \$ftp_user = '$ftp_user';\n";
			$config .= "	public \$ftp_pass = '$ftp_pass';\n";
			$config .= "	public \$ftp_root = '$ftp_root';\n";
			$config .= "	public \$ftp_enable = '$ftp_enable';\n";
			$config .= "	public \$offset = '$offset';\n";
			$config .= "    public \$mailonline = '$mailonline';\n";
			$config .= "	public \$mailer = '$mailer';\n";
			$config .= "	public \$mailfrom = '$mailfrom';\n";
			$config .= "	public \$fromname = '".addslashes($fromname)."';\n";
			$config .= "	public \$sendmail = '$sendmail';\n";
			$config .= "	public \$smtpauth = '$smtpauth';\n";
			$config .= "	public \$smtpuser = '$smtpuser';\n";
			$config .= "	public \$smtppass = '$smtppass';\n";
			$config .= "	public \$smtphost = '$smtphost';\n";
			$config .= "	public \$smtpsecure = '$smtpsecure';\n";
			$config .= "	public \$smtpport = '$smtpport';\n";
			$config .= "	public \$caching = '$caching';\n";
			$config .= "	public \$cache_handler = '$cache_handler';\n";
			$config .= "	public \$cachetime = '$cachetime';\n";
			$config .= "	public \$MetaDesc = '".addslashes($MetaDesc)."';\n";
			$config .= "	public \$MetaKeys = '".addslashes($MetaKeys)."';\n";
			$config .= "	public \$MetaTitle = '$MetaTitle';\n";
			$config .= "	public \$MetaAuthor = '$MetaAuthor';\n";
			$config .= "	public \$MetaVersion = '$MetaVersion'; \n";
			$config .= "    public \$robots = '$robots';\n";	
			$config .= "	public \$sef = '$sef';\n";
			$config .= "	public \$sef_rewrite = '$sef_rewrite';\n";
			$config .= "	public \$sef_suffix = '$sef_suffix';\n";
			$config .= "	public \$unicodeslugs = '$unicodeslugs';\n";
			$config .= "	public \$feed_limit = '$feed_limit';\n";		
			$config .= "	public \$log_path = '{$_POST['logPath']}';\n";
			$config .= "	public \$tmp_path = '{$_POST['tmpPath']}';\n";
			$config .= "	public \$lifetime = '$lifetime';\n";
			$config .= "	public \$session_handler = '$session_handler'; \n";
			$config .= "	public \$memcache_persist = '$memcache_persist'; \n";
			$config .= "	public \$memcache_compress = '$memcache_compress'; \n";
			$config .= "	public \$memcache_server_host = '$memcache_server_host'; \n";
			$config .= "	public \$memcache_server_port = '$memcache_server_port'; \n";
			$config .= "	public \$memcached_persist = '$memcached_persist'; \n";
			$config .= "	public \$memcached_compress = '$memcached_compress'; \n";
			$config .= "	public \$memcached_server_host = '$memcached_server_host'; \n";
			$config .= "	public \$memcached_server_port = '$memcached_server_port'; \n";
			$config .= "	public \$massmailoff = '$massmailoff'; \n";
			$config .= "	public \$MetaRights = '".addslashes($MetaRights)."';\n";
			$config .= "	public \$sitename_pagetitles = '$sitename_pagetitles';\n";
			$config .= "	public \$force_ssl = '$force_ssl';\n";
			$config .= "	public \$frontediting = '$frontediting';\n";
			$config .= "	public \$feed_email = '$feed_email';\n";
			$config .= "	public \$cookie_domain = '$cookie_domain';\n";
			$config .= "	public \$cookie_path = '$cookie_path';\n";
			$config .= "	public \$asset_id = '$asset_id';\n";
			$config .= "	public \$proxy_enable = '$proxy_enable';\n";
			$config .= "	public \$proxy_host = '$proxy_host';\n";
			$config .= "	public \$proxy_port = '$proxy_port';\n";
			$config .= "	public \$proxy_user = '$proxy_user';\n";
			$config .= "	public \$proxy_pass = '$proxy_pass';\n";
			$config .= "	public \$session_memcache_server_host = '$session_memcache_server_host'; \n";
			$config .= "	public \$session_memcache_server_port = '$session_memcache_server_port'; \n";
			$config .= "	public \$session_memcached_server_host = '$session_memcached_server_host'; \n";
			$config .= "	public \$session_memcached_server_port = '$session_memcached_server_port'; \n";
			$config .= "	public \$redis_persist = '$redis_persist'; \n";
			$config .= "	public \$redis_server_host = '$redis_server_host'; \n";
			$config .= "	public \$redis_server_port = '$redis_server_port'; \n";
			$config .= "	public \$redis_server_auth = '$redis_server_auth'; \n";
			$config .= "	public \$redis_server_db = '$redis_server_db'; \n";
			$config .= "}";
			break;
			
        case (($Version >= '3.3') && $Version <'3.4'):
          	$config = "<?php \n";
			$config .= "class JConfig {\n"; 
			$config .= "	public \$offline = '$offline';\n";
			$config .= "	public \$offline_message = '".addslashes($offline_message)."';\n";
			$config .= "    public \$display_offline_message = '$display_offline_message';\n";
			$config .= "    public \$offline_image = '$offline_image';\n";
			$config .= "	public \$sitename = '".addslashes($sitename)."';\n";
			$config .= "	public \$editor = '$editor';\n";
			$config .= "    public \$captcha = '$captcha';\n";			
			$config .= "	public \$list_limit = '$list_limit';\n";
			$config .= "	public \$access = '$access';\n";
			$config .= "	public \$debug = '{$_POST['debug']}';\n";
			$config .= "	public \$debug_lang = '$debug_lang';\n";
			$config .= "	public \$dbtype = '$dbtype';\n";
			$config .= "	public \$host = '{$_POST['DBhostname']}';\n";
			$config .= "	public \$user = '{$_POST['DBuserName']}';\n";
			$config .= "	public \$password = '{$_POST['DBpassword']}';\n";
			$config .= "	public \$db = '{$_POST['DBname']}';\n";
			$config .= "	public \$dbprefix = '{$_POST['DBPrefix']}';\n";
			$config .= "	public \$live_site = '$livesite';\n";
			$config .= "	public \$secret = '$secret';\n";
			$config .= "	public \$gzip = '$gzip';\n";			
			$config .= "	public \$error_reporting = '$error_reporting';\n";
			$config .= "	public \$helpurl = '$helpurl';\n";
			$config .= "	public \$ftp_host = '$ftp_host';\n";
			$config .= "	public \$ftp_port = '$ftp_port';\n";
			$config .= "	public \$ftp_user = '$ftp_user';\n";
			$config .= "	public \$ftp_pass = '$ftp_pass';\n";
			$config .= "	public \$ftp_root = '$ftp_root';\n";
			$config .= "	public \$ftp_enable = '$ftp_enable';\n";
			$config .= "	public \$offset = '$offset';\n";
			$config .= "	public \$offset_user 	 = '$offset_user';\n";
			$config .= "    public \$mailonline = '$mailonline';\n";
			$config .= "	public \$mailer = '$mailer';\n";
			$config .= "	public \$mailfrom = '$mailfrom';\n";
			$config .= "	public \$fromname = '".addslashes($fromname)."';\n";
			$config .= "	public \$sendmail = '$sendmail';\n";
			$config .= "	public \$smtpauth = '$smtpauth';\n";
			$config .= "	public \$smtpuser = '$smtpuser';\n";
			$config .= "	public \$smtppass = '$smtppass';\n";
			$config .= "	public \$smtphost = '$smtphost';\n";
			$config .= "	public \$smtpsecure = '$smtpsecure';\n";
			$config .= "	public \$smtpport = '$smtpport';\n";
			$config .= "	public \$caching = '$caching';\n";
			$config .= "	public \$cache_handler = '$cache_handler';\n";
			$config .= "	public \$cachetime = '$cachetime';\n";
			$config .= "	public \$MetaDesc = '".addslashes($MetaDesc)."';\n";
			$config .= "	public \$MetaKeys = '".addslashes($MetaKeys)."';\n";
			$config .= "	public \$MetaTitle = '$MetaTitle';\n";
			$config .= "	public \$MetaAuthor = '$MetaAuthor';\n";
			$config .= "	public \$MetaVersion = '$MetaVersion'; \n";
			$config .= "    public \$robots = '$robots';\n";	
			$config .= "	public \$sef = '$sef';\n";
			$config .= "	public \$sef_rewrite = '$sef_rewrite';\n";
			$config .= "	public \$sef_suffix = '$sef_suffix';\n";
			$config .= "	public \$unicodeslugs = '$unicodeslugs';\n";
			$config .= "	public \$feed_limit = '$feed_limit';\n";		
			$config .= "	public \$log_path = '{$_POST['logPath']}';\n";
			$config .= "	public \$tmp_path = '{$_POST['tmpPath']}';\n";
			$config .= "	public \$lifetime = '$lifetime';\n";
			$config .= "	public \$session_handler = '$session_handler'; \n";
			$config .= "	public \$memcache_persist = '$memcache_persist'; \n";
			$config .= "	public \$memcache_compress = '$memcache_compress'; \n";
			$config .= "	public \$memcache_server_host = '$memcache_server_host'; \n";
			$config .= "	public \$memcache_server_port = '$memcache_server_port'; \n";
			$config .= "	public \$memcached_persist = '$memcached_persist'; \n";
			$config .= "	public \$memcached_compress = '$memcached_compress'; \n";
			$config .= "	public \$memcached_server_host = '$memcached_server_host'; \n";
			$config .= "	public \$memcached_server_port = '$memcached_server_port'; \n";
			$config .= "	public \$massmailoff = '$massmailoff'; \n";
			$config .= "	public \$MetaRights = '".addslashes($MetaRights)."';\n";
			$config .= "	public \$sitename_pagetitles = '$sitename_pagetitles';\n";
			$config .= "	public \$force_ssl = '$force_ssl';\n";
			$config .= "	public \$frontediting = '$frontediting';\n";
			$config .= "	public \$feed_email = '$feed_email';\n";
			$config .= "	public \$cookie_domain = '$cookie_domain';\n";
			$config .= "	public \$cookie_path = '$cookie_path';\n";
			$config .= "	public \$asset_id = '$asset_id';\n";
			$config .= "	public \$proxy_enable = '$proxy_enable';\n";
			$config .= "	public \$proxy_host = '$proxy_host';\n";
			$config .= "	public \$proxy_port = '$proxy_port';\n";
			$config .= "	public \$proxy_user = '$proxy_user';\n";
			$config .= "	public \$proxy_pass = '$proxy_pass';\n";
			$config .= "	public \$session_memcache_server_host = '$session_memcache_server_host'; \n";
			$config .= "	public \$session_memcache_server_port = '$session_memcache_server_port'; \n";
			$config .= "	public \$session_memcached_server_host = '$session_memcached_server_host'; \n";
			$config .= "	public \$session_memcached_server_port = '$session_memcached_server_port'; \n";
			$config .= "}";
			break;
	 
        case (($Version >= '3.2')  && ($Version < '3.3')) :
          	$config = "<?php \n";
			$config .= "class JConfig {\n"; 
			$config .= "	public \$offline = '$offline';\n";
			$config .= "	public \$offline_message = '".addslashes($offline_message)."';\n";
			$config .= "    public \$display_offline_message = '$display_offline_message';\n";
			$config .= "    public \$offline_image = '$offline_image';\n";
			$config .= "	public \$sitename = '".addslashes($sitename)."';\n";
			$config .= "	public \$editor = '$editor';\n";
			$config .= "	public \$list_limit = '$list_limit';\n";
			$config .= "    public \$captcha = '$captcha';\n";			
			$config .= "	public \$access = '$access';\n";
			$config .= "	public \$debug = '{$_POST['debug']}';\n";
			$config .= "	public \$debug_lang = '$debug_lang';\n";
			$config .= "	public \$dbtype = '$dbtype';\n";
			$config .= "	public \$host = '{$_POST['DBhostname']}';\n";
			$config .= "	public \$user = '{$_POST['DBuserName']}';\n";
			$config .= "	public \$password = '{$_POST['DBpassword']}';\n";
			$config .= "	public \$db = '{$_POST['DBname']}';\n";
			$config .= "	public \$dbprefix = '{$_POST['DBPrefix']}';\n";
			$config .= "	public \$live_site = '$livesite';\n";
			$config .= "	public \$secret = '$secret';\n";
			$config .= "	public \$gzip = '$gzip';\n";			
			$config .= "	public \$error_reporting = '$error_reporting';\n";
			$config .= "	public \$helpurl = '$helpurl';\n";
			$config .= "	public \$ftp_host = '$ftp_host';\n";
			$config .= "	public \$ftp_port = '$ftp_port';\n";
			$config .= "	public \$ftp_user = '$ftp_user';\n";
			$config .= "	public \$ftp_pass = '$ftp_pass';\n";
			$config .= "	public \$ftp_root = '$ftp_root';\n";
			$config .= "	public \$ftp_enable = '$ftp_enable';\n";
			$config .= "	public \$offset = '$offset';\n";
			$config .= "    public \$mailonline = '$mailonline';\n";
			$config .= "	public \$mailer = '$mailer';\n";
			$config .= "	public \$mailfrom = '$mailfrom';\n";
			$config .= "	public \$fromname = '".addslashes($fromname)."';\n";
			$config .= "	public \$sendmail = '$sendmail';\n";
			$config .= "	public \$smtpauth = '$smtpauth';\n";
			$config .= "	public \$smtpuser = '$smtpuser';\n";
			$config .= "	public \$smtppass = '$smtppass';\n";
			$config .= "	public \$smtphost = '$smtphost';\n";
			$config .= "	public \$smtpsecure = '$smtpsecure';\n";
			$config .= "	public \$smtpport = '$smtpport';\n";
			$config .= "	public \$caching = '$caching';\n";
			$config .= "	public \$cache_handler = '$cache_handler';\n";
			$config .= "	public \$cachetime = '$cachetime';\n";
			$config .= "	public \$MetaDesc = '".addslashes($MetaDesc)."';\n";
			$config .= "	public \$MetaKeys = '".addslashes($MetaKeys)."';\n";
			$config .= "	public \$MetaTitle = '$MetaTitle';\n";
			$config .= "	public \$MetaAuthor = '$MetaAuthor';\n";
			$config .= "	public \$MetaVersion = '$MetaVersion'; \n";
			$config .= "    public \$robots = '$robots';\n";	
			$config .= "	public \$sef = '$sef';\n";
			$config .= "	public \$sef_rewrite = '$sef_rewrite';\n";
			$config .= "	public \$sef_suffix = '$sef_suffix';\n";
			$config .= "	public \$unicodeslugs = '$unicodeslugs';\n";
			$config .= "	public \$feed_limit = '$feed_limit';\n";		
			$config .= "	public \$log_path = '{$_POST['logPath']}';\n";
			$config .= "	public \$tmp_path = '{$_POST['tmpPath']}';\n";
			$config .= "	public \$lifetime = '$lifetime';\n";
			$config .= "	public \$session_handler = '$session_handler'; \n";
			$config .= "	public \$MetaRights = '".addslashes($MetaRights)."';\n";
			$config .= "	public \$sitename_pagetitles = '$sitename_pagetitles';\n";
			$config .= "	public \$force_ssl = '$force_ssl';\n";
			$config .= "	public \$frontediting = '$frontediting';\n";
			$config .= "	public \$feed_email = '$feed_email';\n";
			$config .= "	public \$cookie_domain = '$cookie_domain';\n";
			$config .= "	public \$cookie_path = '$cookie_path';\n";
			$config .= "	public \$asset_id = '$asset_id';\n";
			$config .= "}";
			break;
	 
	       case (($Version >= '3.0')  && ($Version < '3.2')) :
          	$config = "<?php \n";
			$config .= "class JConfig {\n"; 
			$config .= "	public \$offline 		 = '$offline';\n";
			$config .= "	public \$offline_message = '".addslashes($offline_message)."';\n";
			$config .= "    public \$display_offline_message = '$display_offline_message';\n";
			$config .= "    public \$offline_image = '$offline_image';\n";
			$config .= "	public \$sitename 		 = '".addslashes($sitename)."';\n";
			$config .= "	public \$editor 		 = '$editor';\n";
			$config .= "	public \$list_limit 	 = '$list_limit';\n";
			$config .= "    public \$captcha 		 = '$captcha';\n";			
			$config .= "	public \$access 		 = '$access';\n";
			$config .= "	public \$debug 		     = '{$_POST['debug']}';\n";
			$config .= "	public \$debug_lang 	 = '$debug_lang';\n";
			$config .= "	public \$dbtype 		 = '$dbtype';\n";
			$config .= "	public \$host 			 = '{$_POST['DBhostname']}';\n";
			$config .= "	public \$user 			 = '{$_POST['DBuserName']}';\n";
			$config .= "	public \$password 		 = '{$_POST['DBpassword']}';\n";
			$config .= "	public \$db 			 = '{$_POST['DBname']}';\n";
			$config .= "	public \$dbprefix 		 = '{$_POST['DBPrefix']}';\n";
			$config .= "	public \$live_site 	     = '$livesite';\n";
			$config .= "	public \$secret 		 = '$secret';\n";
			$config .= "	public \$gzip 			 = '$gzip';\n";			
			$config .= "	public \$error_reporting = '$error_reporting';\n";
			$config .= "	public \$helpurl 		 = '$helpurl';\n";
			$config .= "	public \$ftp_host 		 = '$ftp_host';\n";
			$config .= "	public \$ftp_port 		 = '$ftp_port';\n";
			$config .= "	public \$ftp_user 		 = '$ftp_user';\n";
			$config .= "	public \$ftp_pass 		 = '$ftp_pass';\n";
			$config .= "	public \$ftp_root 		 = '$ftp_root';\n";
			$config .= "	public \$ftp_enable 	 = '$ftp_enable';\n";
			$config .= "	public \$offset 		 = '$offset';\n";
			$config .= "	public \$mailer 		 = '$mailer';\n";
			$config .= "	public \$mailfrom 		 = '$mailfrom';\n";
			$config .= "	public \$fromname 		 = '".addslashes($fromname)."';\n";
			$config .= "	public \$sendmail 		 = '$sendmail';\n";
			$config .= "	public \$smtpauth 		 = '$smtpauth';\n";
			$config .= "	public \$smtpuser 		 = '$smtpuser';\n";
			$config .= "	public \$smtppass 		 = '$smtppass';\n";
			$config .= "	public \$smtphost 		 = '$smtphost';\n";
			$config .= "	public \$smtpsecure = '$smtpsecure';\n";
			$config .= "	public \$smtpport = '$smtpport';\n";
			$config .= "	public \$caching 		 = '$caching';\n";
			$config .= "	public \$cache_handler   = '$cache_handler';\n";
			$config .= "	public \$cachetime 	     = '$cachetime';\n";
			$config .= "	public \$MetaDesc 		 = '".addslashes($MetaDesc)."';\n";
			$config .= "	public \$MetaKeys 		 = '".addslashes($MetaKeys)."';\n";
			$config .= "	public \$MetaTitle 	     = '$MetaTitle';\n";
			$config .= "	public \$MetaAuthor 	 = '$MetaAuthor';\n";
			$config .= "	public \$MetaVersion     = '$MetaVersion'; \n";
			$config .= "    public \$robots = '$robots';\n";	
			$config .= "	public \$sef             = '$sef';\n";
			$config .= "	public \$sef_rewrite     = '$sef_rewrite';\n";
			$config .= "	public \$sef_suffix      = '$sef_suffix';\n";
			$config .= "	public \$unicodeslugs 	 = '$unicodeslugs';\n";
			$config .= "	public \$feed_limit  	 = '$feed_limit';\n";		
			$config .= "	public \$log_path 		 = '{$_POST['logPath']}';\n";
			$config .= "	public \$tmp_path 		 = '{$_POST['tmpPath']}';\n";
			$config .= "	public \$lifetime 		 = '$lifetime';\n";
			$config .= "	public \$session_handler = '$session_handler'; \n";
			$config .= "	public \$MetaRights = '".addslashes($MetaRights)."';\n";
			$config .= "	public \$sitename_pagetitles = '$sitename_pagetitles';\n";
			$config .= "	public \$force_ssl = '$force_ssl';\n";
			$config .= "	public \$feed_email = '$feed_email';\n";
			$config .= "	public \$cookie_domain = '$cookie_domain';\n";
			$config .= "	public \$cookie_path = '$cookie_path';\n";
			$config .= "}";
			break;
	   
       case (($Version >= '2.5') && ($Version < '3')) :
          	$config = "<?php \n";
			$config .= "class JConfig {\n"; 
			$config .= "	public \$offline 		 = '$offline';\n";
			$config .= "	public \$editor 		 = '$editor';\n";
			$config .= "	public \$list_limit 	 = '$list_limit';\n";
			$config .= "	public \$helpurl 		 = '$helpurl';\n";
			$config .= "	public \$debug 		     = '{$_POST['debug']}';\n";
			$config .= "	public \$debug_lang 	 = '$debug_lang';\n";
			$config .= "	public \$sef             = '$sef';\n";
			$config .= "	public \$sef_rewrite     = '$sef_rewrite';\n";
			$config .= "	public \$sef_suffix      = '$sef_suffix';\n";
			$config .= "	public \$feed_limit  	 = '$feed_limit';\n";		
			$config .= "	public \$secret 		 = '$secret';\n";
			$config .= "	public \$gzip 			 = '$gzip';\n";
			$config .= "	public \$error_reporting = '$error_reporting';\n";
			$config .= "	public \$log_path 		 = '{$_POST['logPath']}';\n";
			$config .= "	public \$tmp_path 		 = '{$_POST['tmpPath']}';\n";
			$config .= "	public \$live_site 	     = '$livesite';\n";
			$config .= "	public \$offset 		 = '$offset';\n";
			$config .= "	public \$offset_user 	 = '$offset_user';\n";
			$config .= "	public \$unicodeslugs 	 = '$unicodeslugs';\n";
			$config .= "	public \$caching 		 = '$caching';\n";
			$config .= "	public \$cachetime 	     = '$cachetime';\n";
			$config .= "	public \$cache_handler   = '$cache_handler';\n";
			$config .= "	public \$ftp_host 		 = '$ftp_host';\n";
			$config .= "	public \$ftp_port 		 = '$ftp_port';\n";
			$config .= "	public \$ftp_user 		 = '$ftp_user';\n";
			$config .= "	public \$ftp_pass 		 = '$ftp_pass';\n";
			$config .= "	public \$ftp_root 		 = '$ftp_root';\n";
			$config .= "	public \$ftp_enable 	 = '$ftp_enable';\n";
			$config .= "	public \$dbtype 		 = '$dbtype';\n";
			$config .= "	public \$host 			 = '{$_POST['DBhostname']}';\n";
			$config .= "	public \$user 			 = '{$_POST['DBuserName']}';\n";
			$config .= "	public \$db 			 = '{$_POST['DBname']}';\n";
			$config .= "	public \$dbprefix 		 = '{$_POST['DBPrefix']}';\n";
			$config .= "	public \$mailer 		 = '$mailer';\n";
			$config .= "	public \$mailfrom 		 = '$mailfrom';\n";
			$config .= "	public \$fromname 		 = '".addslashes($fromname)."';\n";
			$config .= "	public \$sendmail 		 = '$sendmail';\n";
			$config .= "	public \$smtpauth 		 = '$smtpauth';\n";
			$config .= "	public \$smtpuser 		 = '$smtpuser';\n";
			$config .= "	public \$smtppass 		 = '$smtppass';\n";
			$config .= "	public \$smtphost 		 = '$smtphost';\n";
			$config .= "	public \$MetaAuthor 	 = '$MetaAuthor';\n";
			$config .= "	public \$MetaTitle 	     = '$MetaTitle';\n";
			$config .= "	public \$lifetime 		 = '$lifetime';\n";
			$config .= "	public \$session_handler = '$session_handler'; \n";
			$config .= "	public \$password 		 = '{$_POST['DBpassword']}';\n";
			$config .= "	public \$sitename 		 = '".addslashes($sitename)."';\n";
			$config .= "	public \$MetaDesc 		 = '".addslashes($MetaDesc)."';\n";
			$config .= "	public \$MetaKeys 		 = '".addslashes($MetaKeys)."';\n";
			$config .= "	public \$offline_message = '".addslashes($offline_message)."';\n";
			$config .= "     public \$display_offline_message = '$display_offline_message';\n";
			$config .= "	public \$access 		 = '$access';\n";
			$config .= "	public \$smtpsecure = '$smtpsecure';\n";
			$config .= "	public \$smtpport = '$smtpport';\n";
			$config .= "    public \$robots = '$robots';\n";
			$config .= "	public \$MetaRights = '".addslashes($MetaRights)."';\n";
			$config .= "	public \$sitename_pagetitles = '$sitename_pagetitles';\n";
			$config .= "	public \$force_ssl = '$force_ssl';\n";
			$config .= "     public \$offline_image = '$offline_image';\n";
			$config .= "    public \$captcha = '$captcha';\n";			
			$config .= "	public \$feed_email = '$feed_email';\n";
			$config .= "	public \$cookie_domain = '$cookie_domain';\n";
			$config .= "	public \$cookie_path = '$cookie_path';\n";

			$config .= "}";
			break;
	   
	   case (($Version >= '1.6') && ($Version < '2.5')) :
          	$config = "<?php \n";
			$config .= "class JConfig {\n"; 
			
			$config .= "	public \$offline 		 = '$offline';\n";
			$config .= "	public \$editor 		 = '$editor';\n";
			$config .= "	public \$list_limit 	 = '$list_limit';\n";
			$config .= "	public \$helpurl 		 = '$helpurl';\n";
			$config .= "	public \$debug 		     = '{$_POST['debug']}';\n";
			$config .= "	public \$debug_lang 	 = '$debug_lang';\n";
			$config .= "	public \$sef             = '$sef';\n";
			$config .= "	public \$sef_rewrite     = '$sef_rewrite';\n";
			$config .= "	public \$sef_suffix      = '$sef_suffix';\n";
			$config .= "	public \$feed_limit  	 = '$feed_limit';\n";		
			$config .= "	public \$secret 		 = '$secret';\n";
			$config .= "	public \$gzip 			 = '$gzip';\n";
			$config .= "	public \$error_reporting = '$error_reporting';\n";
			$config .= "	public \$log_path 		 = '{$_POST['logPath']}';\n";
			$config .= "	public \$tmp_path 		 = '{$_POST['tmpPath']}';\n";
			$config .= "	public \$live_site 	     = '$livesite';\n";
			$config .= "	public \$offset 		 = '$offset';\n";
			$config .= "	public \$offset_user 	 = '$offset_user';\n";
			$config .= "	public \$unicodeslugs 	 = '$unicodeslugs';\n";
			$config .= "	public \$caching 		 = '$caching';\n";
			$config .= "	public \$cachetime 	     = '$cachetime';\n";
			$config .= "	public \$cache_handler   = '$cache_handler';\n";
			$config .= "	public \$ftp_host 		 = '$ftp_host';\n";
			$config .= "	public \$ftp_port 		 = '$ftp_port';\n";
			$config .= "	public \$ftp_user 		 = '$ftp_user';\n";
			$config .= "	public \$ftp_pass 		 = '$ftp_pass';\n";
			$config .= "	public \$ftp_root 		 = '$ftp_root';\n";
			$config .= "	public \$ftp_enable 	 = '$ftp_enable';\n";
			$config .= "	public \$dbtype 		 = '$dbtype';\n";
			$config .= "	public \$host 			 = '{$_POST['DBhostname']}';\n";
			$config .= "	public \$user 			 = '{$_POST['DBuserName']}';\n";
			$config .= "	public \$db 			 = '{$_POST['DBname']}';\n";
			$config .= "	public \$dbprefix 		 = '{$_POST['DBPrefix']}';\n";
			$config .= "	public \$mailer 		 = '$mailer';\n";
			$config .= "	public \$mailfrom 		 = '$mailfrom';\n";
			$config .= "	public \$fromname 		 = '".addslashes($fromname)."';\n";
			$config .= "	public \$sendmail 		 = '$sendmail';\n";
			$config .= "	public \$smtpauth 		 = '$smtpauth';\n";
			$config .= "	public \$smtpsecure		 = '$smtpsecure';\n";
			$config .= "	public \$smtpport 		 = '$smtpport';\n";
			$config .= "	public \$smtpuser 		 = '$smtpuser';\n";
			$config .= "	public \$smtppass 		 = '$smtppass';\n";
			$config .= "	public \$smtphost 		 = '$smtphost';\n";
			$config .= "	public \$MetaAuthor 	 = '$MetaAuthor';\n";
			$config .= "	public \$MetaTitle 	     = '$MetaTitle';\n";
			$config .= "	public \$lifetime 		 = '$lifetime';\n";
			$config .= "	public \$session_handler = '$session_handler'; \n";
			$config .= "	public \$password 		 = '{$_POST['DBpassword']}';\n";
			$config .= "	public \$sitename 		 = '".addslashes($sitename)."';\n";
			$config .= "	public \$MetaDesc 		 = '".addslashes($MetaDesc)."';\n";
			$config .= "	public \$MetaKeys 		 = '".addslashes($MetaKeys)."';\n";
			$config .= "	public \$offline_message = '".addslashes($offline_message)."';\n";
			$config .= "	public \$access 		 = '$access';\n";
			$config .= "	public \$cookie_domain = '$cookie_domain';\n";
			$config .= "	public \$cookie_path = '$cookie_path';\n";
			$config .= "	public \$force_ssl = '$force_ssl';\n";
			$config .= "    public \$offline_image = '$offline_image';\n";	
			$config .= "	public \$feed_email = '$feed_email';\n";			
			$config .= "}; \n";          	
          	$config .= "?>";
			break;
		case ($Version == '1.5') :
			$config = "<?php \n";
			$config .= "class JConfig {\n"; 
			$config .= "	var \$offline 		= '$offline';\n";
			$config .= "	var \$editor 		= '$editor';\n";
			$config .= "	var \$list_limit 	= '$list_limit';\n";
			$config .= "	var \$helpurl 		= '$helpurl';\n";
			$config .= "	var \$debug 		= '{$_POST['debug']}';\n";
			$config .= "	var \$debug_lang 	= '$debug_lang';\n";
			$config .= "	var \$sef           = '$sef';\n";
			$config .= "	var \$sef_rewrite   = '$sef_rewrite';\n";
			$config .= "	var \$sef_suffix    = '$sef_suffix';\n";
			$config .= "	var \$feed_limit  	= '$feed_limit';\n";		
			$config .= "	var \$secret 		= '$secret';\n";
			$config .= "	var \$gzip 			= '$gzip';\n";
			$config .= "	var \$error_reporting = '$error_reporting';\n";
			$config .= "	var \$xmlrpc_server = '$xmlrpc_server';\n";			
			$config .= "	var \$log_path 		= '{$_POST['logPath']}';\n";
			$config .= "	var \$tmp_path 		= '{$_POST['tmpPath']}';\n";
			$config .= "	var \$live_site 	= '$livesite';\n";
			$config .= "	var \$offset 		= '$offset';\n";
			$config .= "	var \$offset_user 	= '$offset_user';\n";
			$config .= "	var \$caching 		= '$caching';\n";
			$config .= "	var \$cachetime 	= '$cachetime';\n";
			$config .= "	var \$cache_handler = '$cache_handler';\n";
			$config .= "	var \$memcache_settings = array(); \n";
			$config .= "	var \$ftp_host 		= '$ftp_host';\n";
			$config .= "	var \$ftp_port 		= '$ftp_port';\n";
			$config .= "	var \$ftp_user 		= '$ftp_user';\n";
			$config .= "	var \$ftp_pass 		= '$ftp_pass';\n";
			$config .= "	var \$ftp_root 		= '$ftp_root';\n";
			$config .= "	var \$ftp_enable 	= '$ftp_enable';\n";
			$config .= "	var \$dbtype 		= '$dbtype';\n";
			$config .= "	var \$host 			= '{$_POST['DBhostname']}';\n";
			$config .= "	var \$user 			= '{$_POST['DBuserName']}';\n";
			$config .= "	var \$db 			= '{$_POST['DBname']}';\n";
			$config .= "	var \$dbprefix 		= '{$_POST['DBPrefix']}';\n";
			$config .= "	var \$mailer 		= '$mailer';\n";
			$config .= "	var \$mailfrom 		= '$mailfrom';\n";
			$config .= "	var \$fromname 		= '".addslashes($fromname)."';\n";
			$config .= "	var \$sendmail 		= '$sendmail';\n";
			$config .= "	var \$smtpauth 		= '$smtpauth';\n";
			$config .= "	var \$smtpuser 		= '$smtpuser';\n";
			$config .= "	var \$smtppass 		= '$smtppass';\n";
			$config .= "	var \$smtphost 		= '$smtphost';\n";
			$config .= "	var \$MetaAuthor 	= '$MetaAuthor';\n";
			$config .= "	var \$MetaTitle 	= '$MetaTitle';\n";
			$config .= "	var \$lifetime 		= '$lifetime';\n";
			$config .= "	var \$session_handler = '$session_handler'; \n";
			$config .= "	var \$password 		= '{$_POST['DBpassword']}';\n";
			$config .= "	var \$sitename 		= '".addslashes($sitename)."';\n";
			$config .= "	var \$MetaDesc 		= '".addslashes($MetaDesc)."';\n";
			$config .= "	var \$MetaKeys 		= '".addslashes($MetaKeys)."';\n";
			$config .= "	var \$offline_message = '".addslashes($offline_message)."';\n";
			$config .= "}; \n";          	
			$config .= "?>";
			break;
		}
			
          	if ($canWrite) {
				copy ('configuration.php', 'configuration_' .date("Y-m-d_H-i-s") . '.php'); // copie de sauvegarde
          		$fp = fopen("configuration.php", "w");
                    fputs( $fp, $config, strlen( $config ));
          		fclose( $fp );
				if (is_dir($log_path)) {		// si validation et dossier administrator/logs, suppression de l'ancien
					if (is_dir($oldlog_path)) { 
						try {
							if ($dir = opendir($oldlog_path)) {
								while (false !== ($fichier = readdir($dir))) {
									if ($fichier != "." && $fichier != "..") {
										unlink ($oldlog_path . "/" . trim($fichier));
									}
								}
								closedir($dir);
							}	
							rmdir ($oldlog_path);
						}catch (Exception $e) {
							
						}
					}
				}
          	}
         	
			$host 			= $_POST['DBhostname'];
			$user 			= $_POST['DBuserName'];
			$db 			= $_POST['DBname'];
			$dbprefix 		= $_POST['DBPrefix'];
			$log_path 		= $_POST['logPath'];
			$tmp_path 		= $_POST['tmpPath'];

          }
     }
     
	 //require_once( 'configuration.php' );

?>
<br />

<div class="centermain" style="margin-left:50px;"><div class="adminheader"><h2 style="color:red"><?php echo $lang['title'].'<br/>'.'<br/>'; echo $lang['moovjlaversion']; echo ($MVJ_version) .'<br/>'.'<br/>'; echo $lang['titleversion']; echo ($Version) ?></h2> 
		<?php 
        /*switch ($Version) {

        case ($Version >= '3.4') : 
			echo (' sup a 3.4'); 
        break; } */
		?>
        </div>
                                   <?php
                                       	if ($task=="write"){
                                                  ?>
                                                  <div class="sectionname"><?php echo $lang['cfg_updated']; ?></div>
                                             	<div class="message">
                                                  <br />
                                                  <?php echo $lang['test_site']; ?> <a href='<?php echo $url ?>' target="_blank" class="button">&nbsp;<?php echo $lang['your_site']; ?>&nbsp;</a>
                                                  <br />&nbsp;
                                                  <br />
                                                  <?php echo $lang['recommand']; ?><a href="reMoovJla.php" target="_parent" class="button"><?php echo $lang['delete_file']; ?></a> <?php echo $lang['sec_reasons']; ?>
                                                  <br />&nbsp;
                                             	</div>
                                                  <?php
                                     	   }else{
                                        	   ?>
                                                <div class="sectionname"><?php echo $lang['check_config']; ?></div>
                                                <?php
                                             }
                                   ?>
                 			
                    	  		<div class="install-text">
                      				<p><?php echo $lang['install_text']; ?></p>
                      			</div>
	<form action="MoovJla.php" method="POST" name="form" id="form" onsubmit="return check();">
      			<table valign="top" class="adminform">
      			       <tr>
						<td colspan="3" class="sectionname">
       					     <?php echo $lang['db_config']; ?>
			  			</td>
                  </tr>
  		  			<tr height="20">
  						<td class="moduleheading">
  							<?php echo $lang['db_srv_name']; ?>
  							<br/>
  						</td>
			  			<td>
  							<input class="inputbox" type="text" name="DBhostname" value="<?php echo "$host"; ?>" size="40" />
  						</td>
		  			<td>
			  				<em><?php echo $lang['db_srv_txt']; ?></em>
			  			</td>
  					</tr>
  		  			<tr height="20">
  						<td class="moduleheading">
			  				<?php echo $lang['db_user_name']; ?>
  						</td>
			  			<td>
			  				<input class="inputbox" type="text" name="DBuserName" value="<?php echo "$user"; ?>" size="40" />
			  			</td>
			  			<td>
			  				<em><?php echo $lang['db_user_txt']; ?></em>
			  			</td>
  					</tr>
  		  			<tr height="20">
  						<td class="moduleheading">
			  				<?php echo $lang['db_password']; ?>
  						</td>
			  			<td>
			  				<input class="inputbox" type="text" name="DBpassword" value="<?php echo "$password"; ?>" size="40" />
			  			</td>
			  			<td>
			  				<em><?php echo $lang['db_password_txt']; ?></em>
			  			</td>
					</tr>
  		  			<tr height="20">
  						<td class="moduleheading">
  							<?php echo $lang['db_name']; ?>
  						</td>
			  			<td>
  							<input class="inputbox" type="text" name="DBname" value="<?php echo "$db"; ?>" size="40" />
  						</td>
			  			<td>
			  				<em><?php echo $lang['db_user_name_txt']; ?></em>
			  			</td>
  					</tr>
  		  			<tr height="20">
  						<td class="moduleheading">
  							<?php echo $lang['db_prefix']; ?>
  						</td>
			  			<td>
  							<input class="inputbox" type="text" name="DBPrefix" value="<?php echo "$dbprefix"; ?>" size="40" />
  						</td>
			  			<td>
			  			<em><?php echo $lang['db_prefix_txt']; ?></em>
			  			</td>
  					</tr>
  		  			<tr>
  		  			    <td colspan="2">&nbsp;
  		  			        
                      </td>
  					</tr>
  		  			 <tr>
						<td colspan="3" class="sectionname">
       					     <?php echo $lang['ws_cfg']; ?>
			  			</td>
  					</tr>
  		  			<!--<tr height="20">
  						<td class="moduleheading">
                                  <?php echo $lang['ws_url']; ?>
                              </td>
          				<td>
                                  <input class="inputbox" type="text" name="siteUrl" value="<?php echo $url; ?>" size="40"/>
                              </td>
			  			<td>
			  				<em><?php echo $lang['ws_url_txt']; ?></em>
			  			</td>
          			</tr> 
  		  			<tr height="20">
  						<td class="moduleheading">
  							<?php echo $lang['ws_path']; ?>
  						</td>
			  			<td>
                                   <input class="inputbox" type="text" name="absPath" value="<?php echo $abspath; ?>" size="40"/>
                              </td>
					</tr>  		  -->		
					<tr height="20">
  						<td class="moduleheading">
  							<?php echo $lang['ws_tmp_path']; ?>
  						</td>
			  			<td>
                                   <input class="inputbox" type="text" name="tmpPath" value="<?php echo $tmp_path; ?>" size="40"/>
                      </td>
					</tr>  		  			
					<tr height="20">
  						<td class="moduleheading">
  							<?php echo $lang['ws_log_path']; ?>
  						</td>
			  			<td>
                                   <input class="inputbox" type="text" name="logPath" value="<?php echo $log_path; ?>" size="40"/>
                      </td>
					</tr>	
  		  			<tr height="20">
  						<td class="moduleheading">
  							<!--<?php echo $lang['ws_cache']; ?>--> &nbsp;
  						</td>
			  			<td>
                                   <!--<input type="checkbox" name="cache" value="1" checked> <?php echo $lang['ws_cache_active']; ?>-->
								   &nbsp;
                      </td>
					</tr>
  		  			<!--<tr height="20">
  						<td class="moduleheading">
  							<?php echo $lang['ws_debug']; ?>
  						</td>
			  			<td>
                                   <input type="checkbox" name="debug" value="0"> <?php echo $lang['ws_debug_active']; ?>
                              </td>
					</tr>-->
  		  			<tr height="20">
  						<td class="moduleheading">
                      </td>
  						<td align="center" colspan="2">
                          		<input type="hidden" name="task" value="write" />
							<input class="button" type="submit" name="next" value=" <?php echo $lang['submit']; ?> "/>
                      </td>
					</tr>

		  		</table>
	</form>
</div>

</body>
</html>
