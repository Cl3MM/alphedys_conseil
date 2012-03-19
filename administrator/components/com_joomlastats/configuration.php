<?php
/**
 * @package JoomlaStats
 * @copyright Copyright (C) 2004-2009 JoomlaStats Team. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

 
if( !defined( '_JEXEC' ) ) {
	die( 'JS: No Direct Access' );
}

require_once( dirname(__FILE__) .DS. 'base.classes.php' );
require_once( dirname(__FILE__) .DS. 'util.classes.php' );
include_once( dirname(__FILE__) .DS. 'configuration.html.php' );
require_once( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'database' .DIRECTORY_SEPARATOR. 'select.one.value.php' );

/**
 *  This class contain set of static functions that allow view and save configuration
 *
 *  NOTICE: This class should contain only set of static, argument less functions that are called by task/action
 */
class js_JSConfiguration
{
	/**
	 * This function view configuration page
	 */
	function viewJSConfigurationPage() {

		$JSDbSOV = new js_JSDbSOV();
		$LastSummarizationDate = false;
		$JSDbSOV->getJSLastSummarizationDate($LastSummarizationDate);

		$JSConf = new js_JSConf(); 

		$JSConfigurationTpl = new js_JSConfigurationTpl();
		$JSConfigurationTpl->viewJSConfigurationPageTpl( $JSConf, $LastSummarizationDate );
	}

	/**
	 * Stores the JoomlaStats configuration
	 *
	 * @deprecated language since 2.3.x (mic)
	 * @param unknown_type $redirect_to_task
	 */
	function SetConfiguration( $redirect_to_task ) {
		global $mainframe;

		$JSConfDef 	= new js_JSConfDef();
		$JSConf 	= new js_JSConf();

		$JSConf->onlinetime 		= isset($_POST['onlinetime'])			? $_POST['onlinetime']		: $JSConfDef->onlinetime;
		$JSConf->startoption		= isset($_POST['startoption'])			? $_POST['startoption'] 	: $JSConfDef->startoption;
		$JSConf->startdayormonth	= isset($_POST['startdayormonth'])		? $_POST['startdayormonth'] : $JSConfDef->startdayormonth;
		//$JSConf->language			= isset($_POST['language']) 			? $_POST['language']		: $JSConfDef->language;
		$JSConf->include_summarized	= isset($_POST['include_summarized'] )	? true						: false; //this is checkbox. It have to be serve in different way //$JSConfDef->include_summarized;
		{//temporary solution
			//$JSConf->show_summarized	= isset($_POST['show_summarized'] )	? true						: false; //this is checkbox. It have to be serve in different way //$JSConfDef->show_summarized;
			$JSConf->show_summarized	= isset($_POST['include_summarized'] )	? true					: false; //this is checkbox. It have to be serve in different way //$JSConfDef->show_summarized;
		}
		$JSConf->enable_whois		= isset($_POST['enable_whois']) 		? true						: false; //this is checkbox. It have to be serve in different way //$JSConfDef->enable_whois;
		$JSConf->enable_i18n		= isset($_POST['enable_i18n'])			? true						: false; //this is checkbox. It have to be serve in different way //$JSConfDef->enable_i18n;


		$err_msg	= '';
		$res		= $JSConf->storeConfigurationToDatabase( $err_msg );

		if( $res == false) {
			$msg		= JTEXT::_( '' );//@todo missing message
			$mainframe->redirect( 'index.php?option=com_joomlastats&task='.$redirect_to_task, $msg, 'error' );//third argument: 'message', 'notice', 'error'
			return false;
		}

		$msg		= JTEXT::_( 'Changes sucessfully saved' );
		$mainframe->redirect( 'index.php?option=com_joomlastats&task='.$redirect_to_task, $msg, 'message' );//third argument: 'message', 'notice', 'error'
		return true;
	}

	/**
	 * Restores config to standard/oryginal JS values
	 *
	 */
	function SetDefaultConfiguration() {
		global $mainframe;

		//convienient way to get default configuration within 'current configuration' object
		$JSConf    = new js_JSConf(false);

		$msg		= JTEXT::_( 'Default JoomlaStats configuration has been set' );
		$err_msg	= '';
		$res		= $JSConf->storeConfigurationToDatabase( $err_msg );

		if( $res == false) {
			$msg		= JTEXT::_( '' );//@todo missing message
			$mainframe->redirect( 'index.php?option=com_joomlastats&task=js_view_configuration', $msg, 'error' );//third argument: 'message', 'notice', 'error'
			return false;
		}

		$msg		= JTEXT::_( 'Changes sucessfully saved' );
		$mainframe->redirect( 'index.php?option=com_joomlastats&task=js_view_configuration', $msg, 'message' );//third argument: 'message', 'notice', 'error'
		return true;
	}
}