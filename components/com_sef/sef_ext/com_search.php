<?php
/**
 * Search SEF extension for Joomla!
 *
 * @author      $Author: David Jozefov $
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access.');

class SefExt_com_search extends SefExt
{

    function beforeCreate(&$uri)
    {
        $ord = $uri->getVar('ordering', null);
        if ($ord == '') {
            $uri->delVar('ordering');
        }
        
        $ph = $uri->getVar('searchphrase', null);
        if ($ph == 'all') {
            $uri->delVar('searchphrase');
        }
    }
    
    function getNonSefVars(&$uri)
    {
        $this->_createNonSefVars($uri);
        
        return array($this->nonSefVars, $this->ignoreVars);
    }
    
    function _createNonSefVars(&$uri)
    {
        if (isset($this->nonSefVars) && isset($this->ignoreVars))
            return;
            
        $this->params = SEFTools::getExtParams('com_virtuemart');
        
        $this->nonSefVars = array();
        $this->ignoreVars = array();
        if (!is_null($uri->getVar('ordering')))
            $this->nonSefVars['ordering'] = $uri->getVar('ordering');
        if (!is_null($uri->getVar('searchphrase')))
            $this->nonSefVars['searchphrase'] = $uri->getVar('searchphrase');
        if (!is_null($uri->getVar('submit')))
            $this->nonSefVars['submit'] = $uri->getVar('submit');
        if (!is_null($uri->getVar('limit')))
            $this->nonSefVars['limit'] = $uri->getVar('limit');
        if (!is_null($uri->getVar('limitstart')))
            $this->nonSefVars['limitstart'] = $uri->getVar('limitstart');
        if (!is_null($uri->getVar('areas')))
            $this->nonSefVars['areas'] = $uri->getVar('areas');
        
        if (!is_null($uri->getVar('searchword') && ($this->params->get('nonsefphrase', '1') == '1')))
            $this->nonSefVars['searchword'] = $uri->getVar('searchword');
    }
    
    function create(&$uri)
    {
        $vars = $uri->getQuery(true);
        extract($vars);
        
        $this->params =& SEFTools::getExtParams('com_search');
        
        $newUri = $uri;
        if (!(isset($task) ? @$task : null)) {
            $title[] = JoomSEF::_getMenuTitle($option, (isset($task) ? $task : null));
            
            if( isset($searchword) && ($this->params->get('nonsefphrase', '1') != '1') ) {
                $title[] = $searchword;
            }
            
            if (count($title) > 0) {
                $this->_createNonSefVars($uri);
                
                if (!isset($searchword) || ($this->params->get('nonsefphrase', '1') != '1') ) {
                    // Generate meta tags
                    $desc = array();
                    if( isset($searchword) ) {
                        $desc[] = $searchword;
                    }
                    if( isset($searchphrase) ) {
                        $desc[] = $searchphrase;
                    }
                    $this->metadesc = implode(', ',$desc);
                    unset($desc);
                }
                $metatags = $this->getMetaTags();

                $newUri = JoomSEF::_sefGetLocation($uri, $title, null, null, null, @$vars['lang'], $this->nonSefVars, null, $metatags);
            }
        }
        
        return $newUri;
    }
}
?>
