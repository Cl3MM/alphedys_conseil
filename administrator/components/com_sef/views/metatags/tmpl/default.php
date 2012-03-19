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
    document.adminForm.filterTitle.value = '0';
    document.adminForm.filterDesc.value = '0';
    document.adminForm.filterKeys.value = '0';
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
            echo JText::_('Filter SEF Urls') . ':';
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
            echo JText::_('Meta Title') . ':';
            ?>
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Meta Description') . ':';
            ?>
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Meta Keywords') . ':';
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
            <?php echo $this->lists['filterTitle']; ?>
        </td>
        <td>
            <?php echo $this->lists['filterDesc']; ?>
        </td>
        <td>
            <?php echo $this->lists['filterKeys']; ?>
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
        <th class="title">
            <?php echo JHTML::_('grid.sort', JText::_('SEF Url'), 'sefurl', $this->lists['filter_order'] == 'sefurl' ? $this->lists['filter_order_Dir'] : 'desc', $this->lists['filter_order']); ?>
            /
            <?php echo JHTML::_('grid.sort', JText::_('Real Url'), 'origurl', $this->lists['filter_order'] == 'origurl' ? $this->lists['filter_order_Dir'] : 'desc', $this->lists['filter_order']); ?>
        </th>
		<th class="title" width="232px">
        	<?php echo JHTML::_('grid.sort', JText::_('Meta Title'), 'metatitle', $this->lists['filter_order'] == 'metatitle' ? $this->lists['filter_order_Dir'] : 'desc', $this->lists['filter_order']); ?>
        </th>
		<th class="title" width="232px">
            <?php echo JHTML::_('grid.sort', JText::_('Meta Description'), 'metadesc', $this->lists['filter_order'] == 'metadesc' ? $this->lists['filter_order_Dir'] : 'desc', $this->lists['filter_order']); ?>
        </th>
		<th class="title" width="232px">
        	<?php echo JHTML::_('grid.sort', JText::_('Meta Keywords'), 'metakey', $this->lists['filter_order'] == 'metakey' ? $this->lists['filter_order_Dir'] : 'desc', $this->lists['filter_order']); ?>
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
    foreach (array_keys($this->items) as $i) {
        $row = &$this->items[$i];
        ?>
        <tr class="<?php echo 'row'. $k; ?>">
            <td align="center">
                <?php echo $this->pagination->getRowOffset($i); ?>
                <input type="hidden" name="id[]" value="<?php echo $row->id; ?>" />
            </td>
            <td>
                <?php echo $row->sefurl; ?>
                <br /><br />
                <?php echo $row->origurl; ?>
            </td>
            <td>
                <textarea name="metatitle[<?php echo $row->id; ?>]" cols="30" rows="5"><?php echo $row->metatitle; ?></textarea>
            </td>
            <td>
                <textarea name="metadesc[<?php echo $row->id; ?>]" cols="30" rows="5"><?php echo $row->metadesc; ?></textarea>
            </td>
            <td>
            	<textarea name="metakey[<?php echo $row->id; ?>]" cols="30" rows="5"><?php echo $row->metakey; ?></textarea>
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
<input type="hidden" name="controller" value="metatags" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
</form>
