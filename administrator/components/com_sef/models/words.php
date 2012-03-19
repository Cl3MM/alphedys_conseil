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

class SEFModelWords extends JModel
{
    /**
     * Constructor that retrieves variables from the request
     */
    function __construct()
    {
        parent::__construct();
        $this->_getVars();
    }

    function _getVars()
    {
        global $mainframe;

        $this->filterWords = $mainframe->getUserStateFromRequest("sef.words.filterWords", 'filterWords', '');
        $this->filterOrder = $mainframe->getUserStateFromRequest('sef.words.filter_order', 'filter_order', 'word');
        $this->filterOrderDir = $mainframe->getUserStateFromRequest('sef.words.filter_order_Dir', 'filter_order_Dir', 'asc');

        $this->limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $this->limitstart	= $mainframe->getUserStateFromRequest('sef.words.limitstart', 'limitstart', 0, 'int');

        // in case limit has been changed, adjust limitstart accordingly
        $this->limitstart = ($this->limit != 0 ? (floor($this->limitstart / $this->limit) * $this->limit) : 0);

        $total = $this->getTotal();
    }

    function _getWords()
    {
        $result = array();
        
        $limit = '';
        if (($this->limit != 0) || ($this->limitstart != 0)) {
            $limit = " LIMIT {$this->limitstart},{$this->limit}";
        }
        
        // Get IDs of requested words
        $query = "SELECT `w`.`id` FROM `#__sefwords` AS `w` ".$this->_getWhere()." ORDER BY ".$this->_getSort().$limit;
        $this->_db->setQuery($query);
        $ids = $this->_db->loadResultArray();
        
        // No words
        if (empty($ids)) {
            return $result;
        }
        
        // Get words
        $query = "SELECT `w`.*, `u`.`sefurl` FROM `#__sefwords` AS `w` LEFT JOIN `#__sefurlword_xref` AS `x` ON `w`.`id` = `x`.`word` LEFT JOIN `#__sefurls` AS `u` ON `u`.`id` = `x`.`url` WHERE `w`.`id` IN (".implode(',', $ids).") ORDER BY ".$this->_getSort();
        $this->_db->setQuery($query);
        $words = $this->_db->loadObjectList();
        
        // Build the array
        foreach ($words as $word) {
            if (!isset($result[$word->id])) {
                $result[$word->id] = new stdClass();
                $result[$word->id]->id = $word->id;
                $result[$word->id]->word = $word->word;
                $result[$word->id]->urls = array();
            }
            
            if (is_null($word->sefurl)) {
                continue;
            }
            
            $result[$word->id]->urls[] = $word->sefurl;
        }
        
        return $result;
    }

    function _getSort()
    {
        if( !isset($this->_sort) ) {
            $this->_sort = '`w`.`' . $this->filterOrder . '` ' . $this->filterOrderDir;
        }

        return $this->_sort;
    }

    function _getWhere()
    {
        if( empty($this->_where) ) {
            $db =& JFactory::getDBO();
            $where = '';
            
            // filter words
            if ($this->filterWords != '') {
                $val = $db->Quote('%'.$this->filterWords.'%');
                $where .= "`w`.`word` LIKE $val ";
            }
            
            $this->_where = ($where == '') ? '' : 'WHERE '.$where;
        }

        return $this->_where;
    }
    
    function _getWhereIds()
    {
        $ids = JRequest::getVar('cid', array(), 'post', 'array');

        $where = '';
        if( count($ids) > 0 ) {
            $where = '`id` IN (' . implode(', ', $ids) . ')';
        }

        return $where;
    }

    function getTotal()
    {
        if (!isset($this->_total)) {
            $this->_db->setQuery("SELECT COUNT(*) FROM `#__sefwords` AS `w` ".$this->_getWhere());
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
            $this->_data = $this->_getWords();
        }

        return $this->_data;
    }

    function getLists()
    {
        // make the filter text boxes
        $lists['filterWords']  = "<input class=\"hasTip\" type=\"text\" name=\"filterWords\" value=\"{$this->filterWords}\" size=\"40\" maxlength=\"255\" onchange=\"document.adminForm.submit();\" title=\"".JText::_('TT_FILTER_WORDS')."\" />";
        
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

    function delete()
    {
        $cids = JRequest::getVar('cid', array(0), 'post', 'array');

        if (count($cids)) {
            $ids = implode(',', $cids);
            $query = "DELETE FROM `#__sefwords` WHERE `id` IN ($ids)";
            $this->_db->setQuery($query);
            if (!$this->_db->query()) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
            
            // Remove the references
            $query = "DELETE FROM `#__sefurlword_xref` WHERE `word` IN($ids)";
            $this->_db->setQuery($query);
            if (!$this->_db->query()) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
        }
        return true;
    }

    function store()
    {
        $row =& $this->getTable('Word');

        $data = JRequest::get('post');
        
        // Bind the form fields to the table
        if (!$row->bind($data)) {
            $this->setError($row->_error);
            return false;
        }

        // Make sure the record is valid
        if (!$row->check()) {
            $this->setError($row->_error);
            return false;
        }
        
        // Store the table to the database
        if (!$row->store()) {
            $this->setError( $row->getError() );
            return false;
        }
        
        // Handle the words references
        // remove the current bindings
        $this->_db->setQuery("DELETE FROM `#__sefurlword_xref` WHERE `word` = '{$row->id}'");
        if (!$this->_db->query()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        
        // find all the IDs for URLs
        if (empty($data['urlsArray'])) {
            return true;
        }
        
        $ids = array_map('trim', explode("\n", $data['urlsArray']));
        if (!empty($ids)) {
            // Now we should have all the IDs in array
            $query = "INSERT INTO `#__sefurlword_xref` (`word`, `url`) VALUES ";
            for ($i = 0, $n = count($ids); $i < $n; $i++) {
                if ($i > 0) {
                    $query .= ', ';
                }
                $query .= "('{$row->id}', '{$ids[$i]}')";
            }
            $this->_db->setQuery($query);
            if (!$this->_db->query()) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
        }
        
        return true;
    }
}
?>