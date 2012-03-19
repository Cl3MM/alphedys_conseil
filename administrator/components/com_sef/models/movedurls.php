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

class SEFModelMovedUrls extends JModel
{
    function __construct()
    {
        parent::__construct();
        $this->_getVars();
    }

    function _getVars()
    {
        global $mainframe;

        //$this->sortby = $mainframe->getUserStateFromRequest("sef.movedurls.sortby", 'sortby', 0);
        $this->filterOld = $mainframe->getUserStateFromRequest("sef.movedurls.filterOld", 'filterOld', '');
        $this->filterNew = $mainframe->getUserStateFromRequest("sef.movedurls.filterNew", 'filterNew', '');
        $this->filterDays = $mainframe->getUserStateFromRequest("sef.movedurls.filterDays", 'filterDays', 0);
        $this->filterOrder = $mainframe->getUserStateFromRequest('sef.movedurls.filter_order', 'filter_order', 'old');
        $this->filterOrderDir = $mainframe->getUserStateFromRequest('sef.movedurls.filter_order_Dir', 'filter_order_Dir', 'asc');

        $this->limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $this->limitstart	= $mainframe->getUserStateFromRequest('sef.movedurls.limitstart', 'limitstart', 0, 'int');

        // In case limit has been changed, adjust limitstart accordingly
        $this->limitstart = ( $this->limit != 0 ? (floor($this->limitstart / $this->limit) * $this->limit) : 0 );

        /*
        $total = $this->getTotal();
        if( ($this->limitstart + $this->limit - 1) > $total ) {
            $this->limitstart = max(($total - $this->limit + 1), 0);
        }
        */
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

        $query = "SELECT * FROM `#__sefmoved` ".$this->_getWhere()." ORDER BY ".$this->_getSort().$limit;

        return $query;
    }

    function _getSort()
    {
        if( !isset($this->_sort) ) {
            /*
            switch ($this->sortby) {
                case 1: $this->_sort =  "`old` DESC";    break;
                case 2: $this->_sort =  "`new`";         break;
                case 3: $this->_sort =  "`new` DESC";    break;
                case 4: $this->_sort =  "`lastHit` DESC";            break;
                case 5: $this->_sort =  "`lastHit`";       break;
                default: $this->_sort = "`old`";         break;
            }
            */
            $this->_sort = '`' . $this->filterOrder . '` ' . $this->filterOrderDir;
        }

        return $this->_sort;
    }

    function _getWhere()
    {
        if( empty($this->_where) ) {
            $where = '';

            // Filter URLs
            if( $this->filterOld != '' )  $where .= ($where != '' ? 'AND ' : '') . "`old` LIKE '%{$this->filterOld}%' ";
            if( $this->filterNew != '' )  $where .= ($where != '' ? 'AND ' : '') . "`new` LIKE '%{$this->filterNew}%' ";
            if( $this->filterDays != '' ) $where .= ($where != '' ? 'AND ' : '') . "`lastHit` < DATE_SUB(NOW(), INTERVAL '{$this->filterDays}' DAY) ";

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
            $this->_db->setQuery("SELECT COUNT(*) FROM `#__sefmoved` ".$this->_getWhere());
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
        // Make the input boxes for hits filter
        $lists['filterDays'] = "<input type=\"text\" name=\"filterDays\" value=\"{$this->filterDays}\" size=\"10\" maxlength=\"10\" onkeydown=\"return handleKeyDown(event);\" />";

        // make the filter text boxes
        $lists['filterOld'] = "<input type=\"text\" name=\"filterOld\" value=\"{$this->filterOld}\" size=\"40\" maxlength=\"255\" onkeydown=\"return handleKeyDown(event);\" title=\"".JText::_('TT_FILTER_OLD')."\" />";
        $lists['filterNew'] = "<input type=\"text\" name=\"filterNew\" value=\"{$this->filterNew}\" size=\"40\" maxlength=\"255\" onkeydown=\"return handleKeyDown(event);\" title=\"".JText::_('TT_FILTER_NEW')."\" />";

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

    function deleteFiltered()
    {
        $query = "DELETE FROM `#__sefmoved` ".$this->_getWhere();
        $this->_db->setQuery($query);
        if (!$this->_db->query()) {
            $this->setError( $this->_db->getErrorMsg() );
            return false;
        }

        return true;
    }
}
?>
