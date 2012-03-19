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
defined('_JEXEC') or die('Restricted access');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">

<script type="text/javascript">
<!--
function handleKeyDown(e)
{
    var code;
    code = e.keyCode;
    
    if (code == 13) {
        // Enter pressed
        document.adminForm.submit();
        return false;
    }
    
    return true;
}

function resetFilters()
{
    document.adminForm.filterOld.value = '';
    document.adminForm.filterNew.value = '';
    document.adminForm.filterDays.value = '0';
    
    document.adminForm.submit();
}
-->
</script>

<table>
    <tr>
        <td width="100%" valign="bottom">
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Filter Moved from URL') . ':<br />' . $this->lists['filterOld'];
            ?>
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Filter Moved to URL') . ':<br />' . $this->lists['filterNew'];
            ?>
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Not used for (days)') . ':<br />' . $this->lists['filterDays'];
            ?>
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo '<br />' . $this->lists['filterReset'];
            ?>
        </td>
    </tr>
</table>

<table class="adminlist">
<thead>
    <tr>
        <th width="5">
            <?php echo JText::_('Num'); ?>
        </th>
        <th width="20">
            <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
        </th>
        <th class="title">
            <?php echo JHTML::_('grid.sort', JText::_('Moved from URL'), 'old', $this->lists['filter_order'] == 'old' ? $this->lists['filter_order_Dir'] : 'desc', $this->lists['filter_order']); ?>
        </th>
        <th class="title">
            <?php echo JHTML::_('grid.sort', JText::_('Moved to URL'), 'new', $this->lists['filter_order'] == 'new' ? $this->lists['filter_order_Dir'] : 'desc', $this->lists['filter_order']); ?>
        </th>
        <th class="title" width="10%">
            <?php echo JHTML::_('grid.sort', JText::_('Last used'), 'lastHit', $this->lists['filter_order'] == 'lastHit' ? $this->lists['filter_order_Dir'] : 'desc', $this->lists['filter_order']); ?>
        </th>
    </tr>
</thead>
<tfoot>
    <tr>
        <td colspan="5">
            <?php echo $this->pagination->getListFooter(); ?>
        </td>
    </tr>
</tfoot>
<tbody>
    <?php
    $k = 0;
    //for ($i=0, $n=count( $rows ); $i < $n; $i++) {
    foreach (array_keys($this->items) as $i) {
        $row = &$this->items[$i];
        ?>
        <tr class="<?php echo 'row'. $k; ?>">
            <td align="center">
                <?php echo $this->pagination->getRowOffset($i); ?>
            </td>
            <td>
                <?php echo JHTML::_('grid.id', $i, $row->id ); ?>
            </td>
            <td>
                <a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>', 'edit')">
                <?php echo $row->old; ?>
                </a>
            </td>
            <td>
                <?php echo $row->new; ?>
            </td>
			<td>
				<?php echo (substr($row->lastHit, 0, 10) == '0000-00-00' ? JText::_('Never') : $row->lastHit); ?>
			</td>
        </tr>
        <?php
        $k = 1 - $k;
    }
    ?>
</tbody>
</table>

<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="movedurls" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
</form>
