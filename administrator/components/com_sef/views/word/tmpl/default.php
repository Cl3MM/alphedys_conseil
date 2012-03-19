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

$sefConfig =& SEFConfig::getConfig();
?>

	<script language="javascript">
	<!--
	function submitbutton(pressbutton)
	{
	    var form = document.adminForm;
	    if (pressbutton == 'cancel') {
	        submitform( pressbutton );
	        return;
	    }
	    // do field validation
	    if (form.word.value == "") {
	        alert( "<?php echo JText::_('ERROR_EMPTY_WORD'); ?>" );
	    } else {
	        // Build the URLs array
	        var list = form.urls;
	        var urlsArray = '';
	        for (var i = 0, n = list.length; i < n; i++) {
	            if (i > 0) {
	                urlsArray += "\n";
	            }
	            urlsArray += list.options[i].value;
	        }
	        form.urlsArray.value = urlsArray;

	        submitform( pressbutton );
	    }
	}

	function addUrl()
	{
	    var url = document.adminForm.txtUrl.value;
	    var id = document.adminForm.urlid.value;
	    var list = document.adminForm.urls;

	    // Check word length
	    if (url.length == 0) {
	        return;
	    }
	    
	    // Check ID presence
	    if (id == '') {
	        return;
	    }

	    // Try to find the URL in list (do not allow duplicities)
	    for (var i = 0, n = list.length; i < n; i++) {
	        if (list.options[i].text == url) {
	            // Found it
	            return;
	        }
	    }

	    // Add the URL
	    var newOpt;
	    newOpt = new Option(url, id);

	    try {
	        list.add(newOpt); // IE, Opera
	    }
	    catch(e) {
	        list.add(newOpt, null); // FF
	    }
	}

	function removeUrls()
	{
	    var list = document.adminForm.urls;

	    for (var i = list.length - 1; i >= 0; i--) {
	        if (list.options[i].selected) {
	            list.remove(i);
	        }
	    }
	}
	//-->
	</script>
	<ul id="autocomplete" style="display: none;"><li>dummy</li></ul>
	
	<form action="index2.php" method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform">
	   <legend><?php echo JText::_('Word'); ?></legend>
	   <table class="admintable">
	       <tr>
	           <td class="key" valign="top"><?php echo JText::_('Word'); ?></td>
	           <td colspan="2"><?php echo $this->lists['word']; ?></td>
	       </tr>
	       <tr>
	           <td class="key" valign="top"><?php echo JText::_('Linked URLs'); ?></td>
	           <td colspan="2">
    	           <?php echo $this->lists['urls']; ?>
    	           <br />
    	           <input type="button" value="<?php echo JText::_('Remove selected'); ?>" onclick="removeUrls();" />
	           </td>
	       </tr>
	       <tr>
	           <td class="key" valign="top"><?php echo JText::_('Add URL'); ?></td>
	           <td><input type="text" autocomplete="off" name="txtUrl" id="txtUrl" size="60" onblur="hideAutoComplete();" onkeydown="handleKey(event, addUrl);" onkeyup="showAutoComplete(this, event, 'urlid', 'ajax', 'findUrls');" /></td>
	           <td><input type="button" value="<?php echo JText::_('Add URL'); ?>" onclick="addUrl();" /></td>
	       </tr>
	   </table>
	   <input type="hidden" name="urlid" id="urlid" value="" />
	</fieldset>
	
<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="words" />
<input type="hidden" name="id" value="<?php echo $this->word->id; ?>" />
<input type="hidden" name="urlsArray" value="" />

</form>
