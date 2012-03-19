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

jimport('joomla.application.component.model');

class SEFModelSiteMap extends JModel
{
    function __construct()
    {
        parent::__construct();
        $this->_getVars();
    }

    function _getVars()
    {
        global $mainframe;

        $this->filterComponent = $mainframe->getUserStateFromRequest("sef.sitemap.comFilter", 'comFilter', '');
        $this->filterSEF = $mainframe->getUserStateFromRequest("sef.sitemap.filterSEF", 'filterSEF', '');
        $this->filterReal = $mainframe->getUserStateFromRequest("sef.sitemap.filterReal", 'filterReal', '');
        $this->filterLang = $mainframe->getUserStateFromRequest('sef.sitemap.filterLang', 'filterLang', '');
        $this->filterIndexed = $mainframe->getUserStateFromRequest("sef.sitemap.filterIndexed", 'filterIndexed', '');
        $this->filterFrequency = $mainframe->getUserStateFromRequest("sef.sitemap.filterFrequency", 'filterFrequency', '');
        $this->filterPriority = $mainframe->getUserStateFromRequest("sef.sitemap.filterPriority", 'filterPriority', '');
        $this->filterOrder = $mainframe->getUserStateFromRequest('sef.sitemap.filter_order', 'filter_order', 'sefurl');
        $this->filterOrderDir = $mainframe->getUserStateFromRequest('sef.sitemap.filter_order_Dir', 'filter_order_Dir', 'asc');

        $this->limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $this->limitstart	= $mainframe->getUserStateFromRequest('sef.sitemap.limitstart', 'limitstart', 0, 'int');

        // In case limit has been changed, adjust limitstart accordingly
        $this->limitstart = ( $this->limit != 0 ? (floor($this->limitstart / $this->limit) * $this->limit) : 0 );
    }

    /**
     * Returns the query
     * @return string The query to be used to retrieve the rows from the database
     */
    function _buildQuery()
    {
        $limit = '';
        if( ($this->limit != 0) || ($this->limitstart != 0) ) {
            $limit = " LIMIT {$this->limitstart},{$this->limit}";
        }

        $query = "SELECT * FROM `#__sefurls` ".$this->_getWhere()." ORDER BY ".$this->_getSort().$limit;

        return $query;
    }

    function _getSort()
    {
        if( !isset($this->_sort) ) {
            $this->_sort = '`' . $this->filterOrder . '` ' . $this->filterOrderDir;
        }

        return $this->_sort;
    }

    function _getWhere()
    {
        if( empty($this->_where) ) {
            $where = "`origurl` != '' ";
            $db =& JFactory::getDBO();

            // filter URLs
            if ($this->filterComponent != '') {
                $where .= "AND (`origurl` LIKE '%option={$this->filterComponent}&%' OR `origurl` LIKE '%option={$this->filterComponent}') ";
            }
            if ($this->filterLang != '' ) {
                $where .= "AND (`origurl` LIKE '%lang={$this->filterLang}%') ";
            }
            if ($this->filterSEF != '') {
                if( substr($this->filterSEF, 0, 4) == 'reg:' ) {
                    $val = substr($this->filterSEF, 4);
                    if( $val != '' ) {
                        // Regular expression search
                        $val = $db->Quote($val);
                        $where .= "AND `sefurl` REGEXP $val ";
                    }
                }
                else {
                    $val = $db->Quote('%'.$this->filterSEF.'%');
                    $where .= "AND `sefurl` LIKE $val ";
                }
            }
            if ($this->filterReal != '') {
                if( substr($this->filterReal, 0, 4) == 'reg:' ) {
                    $val = substr($this->filterReal, 4);
                    if( $val != '' ) {
                        // Regular expression search
                        $val = $db->Quote($val);
                        $where .= "AND `origurl` REGEXP $val ";
                    }
                }
                else {
                    $val = $db->Quote('%'.$this->filterReal.'%');
                    $where .= "AND `origurl` LIKE $val ";
                }
            }

            // filter sitemap data
            if ($this->filterIndexed != 0) {
                if ($this->filterIndexed == 1) {
                    $where .= "AND `sm_indexed` = '0' ";
                }
                elseif ($this->filterIndexed == 2) {
                    $where .= "AND `sm_indexed` = '1'";
                }
            }
            if ($this->filterFrequency != '') {
                $where .= "AND `sm_frequency` = '{$this->filterFrequency}' ";
            }
            if ($this->filterPriority != '') {
                $where .= "AND `sm_priority` = '{$this->filterPriority}' ";
            }

            if( !empty($where) ) {
                $where = "WHERE " . $where;
            }

            $this->_where = $where;
        }

        return $this->_where;
    }

    function getTotal()
    {
        if( !isset($this->_total) )
        {
            $this->_db->setQuery("SELECT COUNT(*) FROM `#__sefurls` ".$this->_getWhere());
            $this->_total = $this->_db->loadResult();
        }

        return $this->_total;
    }

    /**
     * Retrieves the data
     */
    function getData()
    {
        // Lets load the data if it doesn't already exist
        if (empty( $this->_data ))
        {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList( $query );
        }

        return $this->_data;
    }

    function getLists()
    {
        // make the select list for the component filter
        $comList[] = JHTML::_('select.option', '', JText::_('(All)'));
        //$comList[] = JHTML::_('select.option', 'com_content', 'Content');
        $this->_db->setQuery("SELECT `name`,`option` FROM `#__components` WHERE `parent` = '0' ORDER BY `name`");
        $rows = $this->_db->loadObjectList();
        if ($this->_db->getErrorNum()) {
            echo $this->_db->stderr();
            return false;
        }
        foreach(array_keys($rows) as $i) {
            $row = &$rows[$i];
            $comList[] = JHTML::_('select.option', $row->option, $row->name );
        }
        $lists['comList'] = JHTML::_( 'select.genericlist', $comList, 'comFilter', "class=\"inputbox\" onchange=\"document.adminForm.submit();\" size=\"1\"", 'value', 'text', $this->filterComponent);

        // make the filter text boxes
        $lists['filterSEF']  = "<input class=\"hasTip\" type=\"text\" name=\"filterSEF\" value=\"{$this->filterSEF}\" size=\"40\" maxlength=\"255\" onkeydown=\"return handleKeyDown(event);\" title=\"".JText::_('TT_FILTER_SEF')."\" />";
        $lists['filterReal'] = "<input class=\"hasTip\" type=\"text\" name=\"filterReal\" value=\"{$this->filterReal}\" size=\"40\" maxlength=\"255\" onkeydown=\"return handleKeyDown(event);\" title=\"".JText::_('TT_FILTER_REAL')."\" />";

        // Load the active languages
        if( SEFTools::JoomFishInstalled() ) {
            $db =& JFactory::getDBO();
            $query = "SELECT `name`, `shortcode` FROM `#__languages` WHERE `active` = '1' ORDER BY `name`";
            $db->setQuery($query);
            $langs = $db->loadObjectList();

            $langList = array();
            $langList[] = JHTML::_('select.option', '', JText::_('(All)'));
            foreach($langs as $lang) {
                $langList[] = JHTML::_('select.option', $lang->shortcode, $lang->name);
            }

            // Make the language filter
            $lists['filterLang'] = JHTML::_('select.genericlist', $langList, 'filterLang', 'class="inputbox" onchange="document.adminForm.submit();" size="1"', 'value', 'text', $this->filterLang);
        }

        // Filter Indexed state
        $indexes[] = JHTML::_('select.option', 0, JText::_('(All)'));
        $indexes[] = JHTML::_('select.option', 1, JText::_('Indexed'));
        $indexes[] = JHTML::_('select.option', 2, JText::_('Not Indexed'));
        $lists['filterIndexed'] = JHTML::_('select.genericlist', $indexes, 'filterIndexed', 'class="inputbox" onchange="document.adminForm.submit();" size="1"', 'value', 'text', $this->filterIndexed);

        // Filter Frequency state
        $freqs[] = JHTML::_('select.option', '', JText::_('(All)'));
        $freqs[] = JHTML::_('select.option', 'always', 'always');
        $freqs[] = JHTML::_('select.option', 'hourly', 'hourly');
        $freqs[] = JHTML::_('select.option', 'daily', 'daily');
        $freqs[] = JHTML::_('select.option', 'weekly', 'weekly');
        $freqs[] = JHTML::_('select.option', 'monthly', 'monthly');
        $freqs[] = JHTML::_('select.option', 'yearly', 'yearly');
        $freqs[] = JHTML::_('select.option', 'never', 'never');
        $lists['filterFrequency'] = JHTML::_('select.genericlist', $freqs, 'filterFrequency', 'class="inputbox" onchange="document.adminForm.submit();" size="1"', 'value', 'text', $this->filterFrequency);

        // Filter Priority state
        $priorities[] = JHTML::_('select.option', '', JText::_('(All)'));
        $priorities[] = JHTML::_('select.option', '0.0', '0.0');
        $priorities[] = JHTML::_('select.option', '0.1', '0.1');
        $priorities[] = JHTML::_('select.option', '0.2', '0.2');
        $priorities[] = JHTML::_('select.option', '0.3', '0.3');
        $priorities[] = JHTML::_('select.option', '0.4', '0.4');
        $priorities[] = JHTML::_('select.option', '0.5', '0.5');
        $priorities[] = JHTML::_('select.option', '0.6', '0.6');
        $priorities[] = JHTML::_('select.option', '0.7', '0.7');
        $priorities[] = JHTML::_('select.option', '0.8', '0.8');
        $priorities[] = JHTML::_('select.option', '0.9', '0.9');
        $priorities[] = JHTML::_('select.option', '1.0', '1.0');
        $lists['filterPriority'] = JHTML::_('select.genericlist', $priorities, 'filterPriority', 'class="inputbox" onchange="document.adminForm.submit();" size="1"', 'value', 'text', $this->filterPriority);

        $lists['filterReset'] = '<input type="button" value="'.JText::_('Reset').'" onclick="resetFilters();" />';
        
        // Ordering
        $lists['filter_order'] = $this->filterOrder;
        $lists['filter_order_Dir'] = $this->filterOrderDir;

        return $lists;
    }

    function getPagination()
    {
        jimport('joomla.html.pagination');
        $pagination = new JPagination($this->getTotal(), $this->limitstart, $this->limit);

        return $pagination;
    }

    function store()
    {
        $ids = JRequest::getVar('id');
        $smindexed = JRequest::getVar('sm_indexed');
        $smdate = JRequest::getVar('sm_date');
        $smfrequency = JRequest::getVar('sm_frequency');
        $smpriority = JRequest::getVar('sm_priority');

        if (is_array($ids)) {
            foreach ($ids as $id) {
                if (!is_numeric($id)) {
                    continue;
                }

                $indexed = isset($smindexed[$id]) ? '1' : '0';
                $date = isset($smdate[$id]) ? $smdate[$id] : '0000-00-00';
                $frequency = isset($smfrequency[$id]) ? $smfrequency[$id] : 'never';
                $priority = isset($smpriority[$id]) ? $smpriority[$id] : '0.0';

                $query = "UPDATE `#__sefurls` SET `sm_indexed` = ".$this->_db->Quote($indexed).", `sm_date` = ".$this->_db->Quote($date).", `sm_frequency` = ".$this->_db->Quote($frequency).", `sm_priority` = ".$this->_db->Quote($priority)." WHERE `id` = '{$id}' LIMIT 1";
                $this->_db->setQuery($query);

                if (!$this->_db->query()) {
                    $this->setError($this->_db->getErrorMsg());
                    return false;
                }
            }
        }

        // Set the sitemap changed flag
        $sefConfig =& SEFConfig::getConfig();
        if (!$sefConfig->sitemap_changed) {
            $sefConfig->sitemap_changed = true;
            $sefConfig->saveConfig();
        }

        return true;
    }
    
    function setIndex($state)
    {
        $cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );

        if( count($cids) )
        {
            $ids = implode(',', $cids);
            $query = "UPDATE `#__sefurls` SET `sm_indexed` = '{$state}' WHERE `id` IN ({$ids})";
            $this->_db->setQuery($query);
            if (!$this->_db->query()) {
                $this->setError( $this->_db->getErrorMsg() );
                return false;
            }
            
            // Set the sitemap changed flag
            $sefConfig =& SEFConfig::getConfig();
            if (!$sefConfig->sitemap_changed) {
                $sefConfig->sitemap_changed = true;
                $sefConfig->saveConfig();
            }
        }

        return true;
    }

    function generateXml()
    {
        $sefConfig =& SEFConfig::getConfig();
        $file = JPATH_ROOT.DS.$sefConfig->sitemap_filename.'.xml';

        // Check that the file is writable
        if (!file_exists($file)) {
            // Try to create the file
            $f = @fopen($file, 'w');
            if ($f === false) {
                $this->setError(JText::_('Cannot create the specified XML file.'));
                return false;
            }
            fclose($f);

            // Chmod the file, so it is writable
            JPath::setPermissions($file, '0666');
        }
        if (!is_writable($file)) {
            $this->setError(JText::_('Specified XML file is not writable.'));
            return false;
        }

        // Get domain
        $domain = JURI::root();
        
        // Adjust domain according to www handling
        if ($sefConfig->wwwHandling != _COM_SEF_WWW_NONE) {
            if ($sefConfig->wwwHandling == _COM_SEF_WWW_USE_WWW) {
                if (strpos('://www.', $domain) === false) {
                    $domain = str_replace('://', '://www.', $domain);
                }
            }
            else if ($sefConfig->wwwHandling == _COM_SEF_WWW_USE_NONWWW) {
                $domain = str_replace('://www.', '://', $domain);
            }
        }

        // Add slash after domain
        if(substr($domain, -1) != '/') {
            $domain .= '/';
        }

        // Put header
        $text =
        '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';

        // Get URLs
        $this->_db->setQuery("SELECT `sefurl`, `sm_date`, `sm_frequency`, `sm_priority` FROM `#__sefurls` WHERE `sm_indexed` = '1' AND `origurl` != '' ORDER BY `sefurl`");
        $urls = $this->_db->loadObjectList();

        if (!is_null($urls)) {
            foreach ($urls as $url) {
                if ($url->sm_date == '0000-00-00' || $url->sm_date == ''){
                    $url->sm_date = date('Y-m-d');
                }
                if ($url->sm_frequency == '') {
                    $url->sm_frequency = $sefConfig->sitemap_frequency;
                }
                if ($url->sm_priority == '') {
                    $url->sm_priority = $sefConfig->sitemap_priority;
                }

                $url->sefurl = str_replace('&', '&amp;', $url->sefurl);

                $text .=
                '	<url>
		<loc>'.$domain.$url->sefurl.'</loc>
		<lastmod>'.$url->sm_date.'</lastmod>
		<changefreq>'.$url->sm_frequency.'</changefreq>
		<priority>'.$url->sm_priority.'</priority>
	</url>
';
            }
            $text .= '</urlset>';

            // Write the file
            if (!JFile::write($file, $text)) {
                $this->setError(JText::_('Could not write data to the XML file.'));
                return false;
            }
        }

        // Unset the sitemap changed flag
        if ($sefConfig->sitemap_changed) {
            $sefConfig->sitemap_changed = false;
            $sefConfig->saveConfig();
        }

        // Ping search engines if set to
        if ($sefConfig->sitemap_pingauto) {
            $this->pingGoogle();
            $this->pingYahoo();
            $this->pingBing();
        }

        return true;
    }

    function pingGoogle()
    {
        $sefConfig =& SEFConfig::getConfig();

        // Get domain
        $domain = JURI::root();

        // Add slash after domain
        if (substr($domain, -1) != '/') {
            $domain .= '/';
        }

        $file = $domain.$sefConfig->sitemap_filename.'.xml';
        $response = SEFTools::PostRequest('http://www.google.com/webmasters/sitemaps/ping?sitemap='.urlencode($file), null, null, 'get');

        if ($response->code == 200) {
            JError::raiseNotice(100, JText::_('Google').' '.JText::_('pinged'));
            return true;
        }

        JError::raiseWarning(100, JText::_('Could not ping').' '.JText::_('Google'));

        return false;
    }

    function pingYahoo()
    {
        $sefConfig =& SEFConfig::getConfig();

        $appid = trim($sefConfig->sitemap_yahooId);
        if ($appid == '') {
            JError::raiseWarning(100, JText::_('Yahoo Application ID not set.'));
            return false;
        }

        // Get domain
        $domain = JURI::root();

        // Add slash after domain
        if (substr($domain, -1) != '/') {
            $domain .= '/';
        }

        $file = $domain.$sefConfig->sitemap_filename.'.xml';
        $response = SEFTools::PostRequest('http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid='.$appid.'&url='.urlencode($file), null, null, 'get');

        if ($response->code == 200) {
            JError::raiseNotice(100, JText::_('Yahoo').' '.JText::_('pinged'));
            return true;
        }

        JError::raiseWarning(100, JText::_('Could not ping').' '.JText::_('Yahoo'));

        return false;
    }

    function pingBing()
    {
        $sefConfig =& SEFConfig::getConfig();

        // Get domain
        $domain = JURI::root();

        // Add slash after domain
        if (substr($domain, -1) != '/') {
            $domain .= '/';
        }

        $file = $domain.$sefConfig->sitemap_filename.'.xml';

        // Ping Bing
        $response = SEFTools::PostRequest('http://www.bing.com/webmaster/ping.aspx?siteMap='.urlencode($file), null, null, 'get');

        if ($response->code == 200) {
            JError::raiseNotice(100, JText::_('Bing').' '.JText::_('pinged'));
            return true;
        }

        JError::raiseWarning(100, JText::_('Could not ping').' '.JText::_('Bing'));

        return false;
    }

    function pingServices()
    {
        $sefConfig =& SEFConfig::getConfig();

        if (!is_array($sefConfig->sitemap_services) || count($sefConfig->sitemap_services) == 0) {
            return;
        }
        
        // Get domain
        $domain = JURI::root();

        // Add slash after domain
        if (substr($domain, -1) != '/') {
            $domain .= '/';
        }

        $file = $domain.$sefConfig->sitemap_filename.'.xml';

        // Site name
        $config = &JFactory::getConfig();
        $sitename = $config->getValue("sitename");

		$data = "<?xml version=\"1.0\"?>\r\n".
				"  <methodCall>\r\n".
				"    <methodName>weblogUpdates.ping</methodName>\r\n".
				"    <params>\r\n".
				"      <param>\r\n".
				"        <value>$sitename</value>\r\n".
				"      </param>\r\n".
				"      <param>\r\n".
				"        <value>$file</value>\r\n".
				"      </param>\r\n".
				"    </params>\r\n".
				"  </methodCall>";
				
		// loop through services and try to ping them
		foreach ($sefConfig->sitemap_services as $service) {
		    $response = SEFTools::PostRequest($service, null, $data, 'post', 'Joomla! Ping/1.0');
		    
		    if ($response->code != 200) {
		        JError::raiseWarning(100, JText::_('Could not ping').' '.$service);
		        continue;
		    }
		    
		    // Parse the response
		    $xml = @simplexml_load_string($response->content);
		    
		    if ($xml === false) {
		        JError::raiseWarning(100, $service.' | '.JText::_('Could not parse response'));
		        continue;
		    }
		    
		    $m1 = $xml->params->param->value->struct->member[0];
		    $m2 = $xml->params->param->value->struct->member[1];
		    if (empty($m1) || empty($m2)) {
		        JError::raiseWarning(100, $service.' | '.JText::_('Could not parse response'));
		        continue;
		    }
		    
		    if (empty($m1->value) || empty($m2->value)) {
		        JError::raiseWarning(100, $service.' | '.JText::_('Could not parse response'));
		        continue;
		    }
		    
		    if (((string)($m1->name)) == 'flerror') {
		        $err = (int)($m1->value->boolean);
		        if (!empty($m2->value->string)) {
		            $msg = (string)($m2->value->string);
		        } else {
		          $msg = (string)($m2->value);
		        }
		    }
		    else {
		        $err = (int)($m2->value->boolean);
		        if (!empty($m2->value->string)) {
		            $msg = (string)($m1->value->string);
		        } else {
		          $msg = (string)($m1->value);
		        }
		    }
		    
		    JError::raiseNotice(100, $service.' | '.$err.' - '.$msg);
		}
    }
    
}
?>
