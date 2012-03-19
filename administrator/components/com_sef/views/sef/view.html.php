<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

//require_once(JPATH_COMPONENT.DS.'classes'.DS.'button.php');

jimport( 'joomla.html.pane' );

class SEFViewSEF extends JView
{
	function display($tpl = null)
	{
		JToolBarHelper::title(JText::_('JoomSEF'), 'artio.png');
		
		// Get number of URLs for purge warning
		$model =& JModel::getInstance('URLs', 'SEFModel');
		$this->assign('purgeCount', $model->getCount(0));
		
		// Get newest version available
		$sefConfig =& SEFConfig::getConfig();
		
		if ($sefConfig->versionChecker) {
    		$model2 =& JModel::getInstance('Upgrade', 'SEFModel');
    		$newVer = $model2->getNewSEFVersion();
    		$sefinfo = SEFTools::getSEFInfo();
    		
    		if( ((strnatcasecmp($newVer, $sefinfo['version']) > 0) ||
            (strnatcasecmp($newVer, substr($sefinfo['version'], 0, strpos($sefinfo['version'], '-'))) == 0)) ) {
                $newVer = '<span style="font-weight: bold; color: red;">'.$newVer.'</span>&nbsp;&nbsp;<input type="button" onclick="showUpgrade();" value="' . JText::_('Go to Upgrade page') . '" />';
            }
            $newVer .= ' <input type="button" onclick="disableStatus(\'versioncheck\');" value="' . JText::_('Disable version checker') . '" />';
            
    		$this->assign('newestVersion', $newVer);
		}
		else {
		    $newestVersion = JText::_('Version checker disabled') . '&nbsp;&nbsp;<input type="button" onclick="enableStatus(\'versioncheck\');" value="' . JText::_('Enable') . '" />';
		    $this->assign('newestVersion', $newestVersion);
		}
		
		// Get statistics
		$stats = $model->getStatistics();
		$this->assignRef('stats', $stats);
		
		// Get feed
		$feed = $this->get('Feed');
		$this->assignRef('feed', $feed);

		parent::display($tpl);
	}
}
