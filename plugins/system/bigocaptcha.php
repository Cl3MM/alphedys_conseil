<?php
/**
 * Generic Captcha Plugin for Joomla! 1.5
 * @author bigodines
 * <b>info</b>: http://www.bigodines.com or (in portuguese) http://www.joomla.com.br
 * 
 * 
 * I'm using [almost] the  same plugin structure as 'OSTCaptcha' by CoolAcid so you can easily replace one to the other.
 * If you can't find the features you want here, maybe you should try his: 
 * http://forum.joomla.org/index.php/topic,218637.0.html
 */

// CHANGELOG:
//17-11-2007: added "guessing protection".

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * @package		Joomla
 */
class plgSystemBigocaptcha extends JPlugin
{

	var $max_tries = 6;

	// Function wrappers for TriggerEvent usage
	function onCaptcha_Display() {		
		$session =& JFactory::getSession();
		$plugin =& JPluginHelper::getPlugin('system', 'bigocaptcha');
       	$params = new JParameter( $plugin->params );
       	
		require_once(JPATH_PLUGINS.DS.'system'.DS.'Captcha04'.DS."Functions.php"); // string generator, crypt/decrypt functions
		require_once(JPATH_PLUGINS.DS.'system'.DS.'Captcha04'.DS."GIFEncoder.class.php"); // gif animation
		$tries = $session->get('attempts');
		if ($tries > $this->max_tries) $rnd = 'You are a spambot';
		else $rnd = rnd_string	(intval($params->get('word_len')) );
		$cid = md5_encrypt	( $rnd );
		$uid = "54;".$cid;
		
		$session->set('bigo_uid',$cid); // secret word
		
		
		require_once(JPATH_PLUGINS.DS.'system'.DS.'Captcha04'.DS."CaptchaImage.php"); // creates the magic!
		exit(); 
	}

	function onCaptcha_confirm($word, &$return) {		
		global $mainframe;
		require_once(JPATH_PLUGINS.DS.'system'.DS.'Captcha04'.DS."Functions.php");
		$session =& JFactory::getSession();
		
		// guessing protection
		$tries = 0; 
		$tries = $session->get('attempts');		
		$session->set('attempts', ++$tries);

		if (!$word || $tries > $this->max_tries) {
			return false;
		}
		
  		$correct = md5_decrypt ( $session->get('bigo_uid') );
  		$session->set('bigo_uid', null);
  		if (strtolower($word) == strtolower($correct)) {
  			$session->set('attempts',0); 
  			$return = true;
  		} else $return = false;  		
		
		return $return;
	}

}