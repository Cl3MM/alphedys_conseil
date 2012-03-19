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

$freqs = array();
$freqs[] = JHTML::_('select.option', 'always', 'always');
$freqs[] = JHTML::_('select.option', 'hourly', 'hourly');
$freqs[] = JHTML::_('select.option', 'daily', 'daily');
$freqs[] = JHTML::_('select.option', 'weekly', 'weekly');
$freqs[] = JHTML::_('select.option', 'monthly', 'monthly');
$freqs[] = JHTML::_('select.option', 'yearly', 'yearly');
$freqs[] = JHTML::_('select.option', 'never', 'never');

$priorities = array();
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
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">

<script type="text/javascript">
<!--
function useRE(el1, el2)
{
    if( !el1 || !el2 ) {
        return;
    }
    
    if( el1.checked && el2.value.substr(0, 4) != 'reg:' ) {
        el2.value = 'reg:' + el2.value;
    }
    else if( !el1.checked && el2.value.substr(0,4) == 'reg:' ) {
        el2.value = el2.value.substr(4);
    }
}

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
    document.adminForm.filterSEF.value = '';
    document.adminForm.filterReal.value = '';
    document.adminForm.filterIndexed.value = '0';
    document.adminForm.filterFrequency.value = '';
    document.adminForm.filterPriority.value = '';
    document.adminForm.comFilter.value = '';
    <?php if( SEFTools::JoomFishInstalled() ) { ?>
    document.adminForm.filterLang.value = '';
    <?php } ?>
    
    document.adminForm.submit();
}
-->
</script>

<table>
    <tr>
        <td width="100%" valign="bottom">
        </td>
        <td nowrap="nowrap">
            Use RE&nbsp;<input type="checkbox" onclick="useRE(this, document.adminForm.filterSEF);" />
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Filter SEF Urls');
            ?>
        </td>
        <td nowrap="nowrap">
            Use RE&nbsp;<input type="checkbox" onclick="useRE(this, document.adminForm.filterReal);" />
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Filter Real Urls') . ':';
            ?>
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Indexed') . ':';
            ?>
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Change Frequency') . ':';
            ?>
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Priority') . ':';
            ?>
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Component') . ':';
            ?>
        </td>
        <?php if( SEFTools::JoomFishInstalled() ) { ?>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Language') . ':';
            ?>
        </td>
        <?php } ?>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td colspan="2">
            <?php echo $this->lists['filterSEF']; ?>
        </td>
        <td colspan="2">
            <?php echo $this->lists['filterReal']; ?>
        </td>
        <td>
            <?php echo $this->lists['filterIndexed']; ?>
        </td>
        <td>
            <?php echo $this->lists['filterFrequency']; ?>
        </td>
        <td>
            <?php echo $this->lists['filterPriority']; ?>
        </td>
        <td>
            <?php echo $this->lists['comList']; ?>
        </td>
        <?php if (SEFTools::JoomFishInstalled()) { ?>
        <td>
            <?php echo $this->lists['filterLang']; ?>
        </td>
        <?php } ?>
        <td>
            <?php echo $this->lists['filterReset']; ?>
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
            <?php echo JHTML::_('grid.sort', JText::_('SEF Url'), 'sefurl', $this->lists['filter_order'] == 'sefurl' ? $this->lists['filter_order_Dir'] : 'desc', $this->lists['filter_order']); ?>
        </th>
        <th class="title">
            <?php echo JHTML::_('grid.sort', JText::_('Real Url'), 'origurl', $this->lists['filter_order'] == 'origurl' ? $this->lists['filter_order_Dir'] : 'desc', $this->lists['filter_order']); ?>
        </th>
		<th class="title" width="60px">
        	<?php echo JHTML::_('grid.sort', JText::_('Indexed'), 'sm_indexed', $this->lists['filter_order'] == 'sm_indexed' ? $this->lists['filter_order_Dir'] : 'desc', $this->lists['filter_order']); ?>
        </th>
		<th class="title" width="100px">
            <?php echo JHTML::_('grid.sort', JText::_('Date'), 'sm_date', $this->lists['filter_order'] == 'sm_date' ? $this->lists['filter_order_Dir'] : 'desc', $this->lists['filter_order']); ?>
        </th>
		<th class="title" width="120px">
        	<?php echo JHTML::_('grid.sort', JText::_('Change Frequency'), 'sm_frequency', $this->lists['filter_order'] == 'sm_frequency' ? $this->lists['filter_order_Dir'] : 'desc', $this->lists['filter_order']); ?>
        </th>
		<th class="title" width="50px">
        	<?php echo JHTML::_('grid.sort', JText::_('Priority'), 'sm_priority', $this->lists['filter_order'] == 'sm_priority' ? $this->lists['filter_order_Dir'] : 'desc', $this->lists['filter_order']); ?>
        </th>
    </tr>
</thead>
<tfoot>
    <tr>
        <td colspan="8">
            <?php echo $this->pagination->getListFooter(); ?>
        </td>
    </tr>
</tfoot>
<tbody>
    <?php
    $k = 0;
    foreach (array_keys($this->items) as $i) {
        $row = &$this->items[$i];
        ?>
        <tr class="<?php echo 'row'. $k; ?>">
            <td align="center">
                <?php echo $this->pagination->getRowOffset($i); ?>
                <input type="hidden" name="id[]" value="<?php echo $row->id; ?>" />
            </td>
            <td>
                <?php echo JHTML::_('grid.id', $i, $row->id ); ?>
            </td>
            <td>
                <?php echo $row->sefurl; ?>
            </td>
            <td>
                <?php echo htmlentities($row->origurl . ($row->Itemid == '' ? '' : (strpos($row->origurl, '?') ? '&' : '?') . 'Itemid='.$row->Itemid ) ); ?>
            </td>
            <td style="text-align: center;">
                <input type="checkbox" name="sm_indexed[<?php echo $row->id; ?>]" value="1" <?php if ($row->sm_indexed) echo 'checked="checked"'; ?> />
            </td>
            <td style="text-align: center;">
                <?php
                if ($row->sm_date == '0000-00-00'){
                    $date = date('Y-m-d');
                } else {
                    $date = $row->sm_date;
                }
                echo JHTML::calendar($date, 'sm_date['.$row->id.']', 'sm_date['.$row->id.']', '%Y-%m-%d', 'style="width: 70px"');
                ?>
            </td>
            <td style="text-align: center;">
            	<?php echo JHTML::_('select.genericlist', $freqs, 'sm_frequency['.$row->id.']', 'class="inputbox" size="1"', 'value', 'text', $row->sm_frequency); ?>
            </td>
            <td style="text-align: center;">
                <?php echo JHTML::_('select.genericlist', $priorities, 'sm_priority['.$row->id.']', 'class="inputbox" size="1"', 'value', 'text', $row->sm_priority); ?>
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
<input type="hidden" name="controller" value="sitemap" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
</form>
