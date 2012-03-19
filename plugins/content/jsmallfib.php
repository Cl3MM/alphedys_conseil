<?php
/**
* @version $Id$
* @package Joomla! 1.5.x, jsmallfib plugin
* @copyright (c) 2009-2010 Enrico Sandoli
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

/***************************************************************************
 
     This file is part of jsmallfib
 
     This program is free software: you can redistribute it and/or modify
     it under the terms of the GNU General Public License as published by
     the Free Software Foundation, either version 3 of the License, or
     (at your option) any later version.
 
     This program is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     GNU General Public License for more details.
  
     A copy of the GNU General Public License is on <http://www.gnu.org/licenses/>.
   
 ***************************************************************************

     This plugin has been written by Enrico Sandoli based on the original
     enCode eXplorer v4 by Marek Rei. Because the code works within the Joomla!
     environment, the original password protection has been replaced with a
     new access rights system. The ability to delete files and folders (if empty)
     has also been added to the original code, together with some extra security
     checks to forbid access to areas outside the intended repositories.
  
     For info on usage, please refer to the plugin configuration page within
     the administrator site in Joomla!, or to jsmallfib homepage, currently
     on http://www.jsmallsoftware.com
  
 ***************************************************************************

     Module extended, corrected and modified in several ways by
       Erik Liljencrantz, erik@eldata.se, http://www.eldata.se
     marked below as /ErikLtz

     One special correction: the module used urldecode on $_GET-variables
     which is a no-no. From Google:
       A reminder: if you are considering using urldecode() on a $_GET
       variable, DON'T!
     Though delfile and delfolder is double urlencoded so these still have
     the urldecode there.
 
 ***************************************************************************/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// import the JPlugin class
jimport('joomla.event.plugin');

class plgContentjsmallfib extends JPlugin
{
	var $default_absolute_path;

	var $baselink;
	var $imgdir = "plugins/content/jsmallfib/";
	var $option;
	var $view;
	var $id;
	var $Itemid;

	var $date_format;
	var $display_filechanged;
	var $display_seconds;
	var $filesize_separator;

	var $filter_list_allow;
	var $filter_list_width;

	var $encode_to_utf8;

	var $table_width;
	var $row_height;
	var $highlighted_color;
	var $oddrows_color;
	var $evenrows_color;

	var $framebox_bgcolor;
	var $framebox_border;
	var $framebox_linetype;
	var $framebox_linecolor;

	var $errorbox_bgcolor;
	var $errorbox_border;
	var $errorbox_linetype;
	var $errorbox_linecolor;

	var $successbox_bgcolor;
	var $successbox_border;
	var $successbox_linetype;
	var $successbox_linecolor;

	var $uploadbox_bgcolor;
	var $uploadbox_border;
	var $uploadbox_linetype;
	var $uploadbox_linecolor;

	var $inputbox_bgcolor;
	var $inputbox_border;
	var $inputbox_linetype;
	var $inputbox_linecolor;

	var $header_bgcolor;

	var $line_bgcolor;
	var $line_height;

	var $cur_sort_by;
	var $cur_sort_as;

	var $thumbsize;

	function plgContentjsmallfib( &$subject )
	{
		parent::__construct( $subject );
		
		// load plugin parameters and language file
		$this->_plugin = JPluginHelper::getPlugin( 'content', 'jsmallfib' );
		$this->_params = new JParameter( $this->_plugin->params );
		JPlugin::loadLanguage('plg_content_jsmallfib', JPATH_ADMINISTRATOR);
	}

	function onPrepareContent(&$article, &$params, $limitstart = null) {

		global $mainframe;

		$version_number = "1.1c";

		// return if manually disabled in this article (needed for demo purposes)
		if (strstr($article->text, "jsmallfib_disabled_here")) {
			$article->text = preg_replace("/jsmallfib_disabled_here/", "", $article->text);
			return;
		}

		// check article text; if it is NOT in the form of a jsmallfib command than return
		$regex = '/{(jsmallfib)\s*(.*?)}/i';
		$command_match = array();
		$command_match_found = preg_match($regex, $article->text, $command_match);

		// return if command is not found
		if (!$command_match_found) {
			return;
		}

		// only allow article view (if section or category view, just output a reference to the repository)
		$view = JREQUEST::getVar('view', 0);

		if (strcmp (strtoupper($view), "ARTICLE") && strcmp (strtoupper($view), "DETAILS")  && strcmp (strtoupper($view), "ITEM")) { // compatibility with EventList and K2
			$article->text = preg_replace("/{(jsmallfib)\s*(.*?)}/i", JText::_('only_article_view'), $article->text);
			return;
		}

		// GOT HERE SO GO AHEAD AND PROCESS THE COMMAND

		// this is needed to solve the blank page problem with a long list of files:
		// see solution on http://forum.joomla.org/viewtopic.php?p=1679517
		ini_set('pcre.backtrack_limit', -1);
		ini_set('pcre.recursion_limit', -1);

		// get default parameters
		$this->encode_to_utf8		= $this->_params->def('encode_to_utf8', "1");

		$this->date_format 	 	= $this->_params->def('date_format', 'dd_mm_yyyy_slashsep');
		$this->display_filechanged 	= $this->_params->def('display_filechanged', '1');
		$this->display_seconds 	 	= $this->_params->def('display_seconds', '1');
		$this->filesize_separator 	= $this->_params->def('filesize_separator', '.');

		$this->filter_list_allow 	 	= $this->_params->def('filter_list_allow', '1');
		$this->filter_list_width 	= $this->_params->def('filter_list_width', '150');

		$this->table_width 	 	= $this->_params->def('table_width', 680);
		$this->row_height  	 	= $this->_params->def('row_height', 22);
		$this->highlighted_color 	= $this->_params->def('highlighted_color', "FFD");
		$this->oddrows_color 		= $this->_params->def('oddrows_color', "F9F9F9");
		$this->evenrows_color 		= $this->_params->def('evenrows_color', "FFFFFF");

		$this->framebox_bgcolor		= $this->_params->def('framebox_bgcolor', "FFFFFF");
		$this->framebox_border		= $this->_params->def('framebox_border', "1");
		$this->framebox_linetype	= $this->_params->def('framebox_linetype', "solid");
		$this->framebox_linecolor	= $this->_params->def('framebox_linecolor', "CDD2D6");

		$this->errorbox_bgcolor		= $this->_params->def('errorbox_bgcolor', "FFE4E1");
		$this->errorbox_border		= $this->_params->def('errorbox_border', "1");
		$this->errorbox_linetype	= $this->_params->def('errorbox_linetype', "solid");
		$this->errorbox_linecolor	= $this->_params->def('errorbox_linecolor', "F8A097");

		$this->successbox_bgcolor	= $this->_params->def('successbox_bgcolor', "E7F6DC");
		$this->successbox_border	= $this->_params->def('successbox_border', "1");
		$this->successbox_linetype	= $this->_params->def('successbox_linetype', "solid");
		$this->successbox_linecolor	= $this->_params->def('successbox_linecolor', "66B42D");

		$this->uploadbox_bgcolor	= $this->_params->def('uploadbox_bgcolor', "F8F9FA");
		$this->uploadbox_border		= $this->_params->def('uploadbox_border', "1");
		$this->uploadbox_linetype	= $this->_params->def('uploadbox_linetype', "solid");
		$this->uploadbox_linecolor	= $this->_params->def('uploadbox_linecolor', "CDD2D6");

		$this->header_bgcolor		= $this->_params->def('header_bgcolor', "FFFFFF");

		$this->line_bgcolor		= $this->_params->def('line_bgcolor', "CDD2D6");
		$this->line_height		= $this->_params->def('line_height', "1");

		$this->inputbox_bgcolor		= $this->_params->def('inputbox_bgcolor', "FFFFFF");
		$this->inputbox_border		= $this->_params->def('inputbox_border', "1");
		$this->inputbox_linetype	= $this->_params->def('inputbox_linetype', "solid");
		$this->inputbox_linecolor	= $this->_params->def('inputbox_linecolor', "CDD2D6");

		$is_path_relative 		= $this->_params->def('is_path_relative', 1);

		$is_direct_link_to_files	= $this->_params->def('is_direct_link_to_files', 0);	// 0 for link through download_file; 1 for direct link in same window; 2 for direct link in new window

		$default_file_chmod		= $this->_params->def('default_file_chmod', "0664");
		$default_file_chmod = '0'.ltrim($default_file_chmod, "0");
 		$default_file_chmod = octdec($default_file_chmod);    // convert octal mode to decimal

		$default_dir_chmod		= $this->_params->def('default_dir_chmod',  "0775");
		$default_dir_chmod = '0'.ltrim($default_dir_chmod, "0");
 		$default_dir_chmod = octdec($default_dir_chmod);    // convert octal mode to decimal


		// remove magic quotes if needed
		if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) {

    			function stripslashes_deep($value)
    			{
        			$value = is_array($value) ?  array_map('stripslashes_deep', $value) : stripslashes($value);
        			return $value;
    			}

			$_POST = array_map('stripslashes_deep', $_POST);
			$_GET = array_map('stripslashes_deep', $_GET);
			$_COOKIE = array_map('stripslashes_deep', $_COOKIE);
			$_REQUEST = array_map('stripslashes_deep', $_REQUEST);
		}

		// set JS and CSS //

		$document =& JFactory::getDocument();

		// for jsmallfib
		$document->addScriptDeclaration($this->do_js());
		$document->addStyleDeclaration($this->do_css());

		// split the article text in two parts (before and after the FIRST occurrence of the command)
		$text_array = array();
		$text_array = preg_split($regex, $article->text, 2);

		// CHECK ACCESS RIGHTS
		
		// get access rights (they are in the format [<optional 'g' or 'G'>userid:permission], but would also work
		// without brackets and/or separated by commas or other chars (excluding ':')
		
		$access_rights_args = array();
		$access_rights_args_found = preg_match_all("/(g?\d+|reg|thumbsize|sortby|sortas):\d+n?/i", $command_match[0], $access_rights_args); 	// introduced undocumented feature: 'n' suffix to permission
															// (needed to disable download ability from level 1 when setting '1n')

		// get current userid
		$user	= $mainframe->getUser();	
		$userid = $user->id;
		$username = $user->name;
		$user_username = $user->username; // used for userbound repositories
		if (!$username)
		{
			$username = JText::_('unregistered_visitor');
		}
		$remote_address = $_SERVER['REMOTE_ADDR'];
		if (!$remote_address)
		{
			$remote_address = JText::_('unavailable');
		}

		// set the default access rights, then check if specific ones do apply
		if ($userid) {
			$access_rights = $this->_params->def('default_reguser_access_rights', 0);
		}
		else {
			$access_rights = $this->_params->def('default_visitor_access_rights', 0);
		}

		// this variable accounts for the 'L' undocumented option in permission values
		$disable_level_1_downloads = 0;

		// get the category of the associated joomla! contact (the group), if there is one
		$db =& JFactory::getDBO();
		$query = "SELECT #__categories.id AS catid, #__categories.title AS cattitle "
				."FROM #__contact_details LEFT JOIN #__categories ON #__contact_details.catid=#__categories.id "
				."WHERE #__contact_details.user_id='".$userid."'";
		$db->setQuery($query);
		$row = $db->loadObjectList();

		if ($db->getErrorNum()) {
			echo $db->stderr();
			return false;
		}
		if (count($row)) {
			$user_catid 	= $row[0]->catid;
			$user_cattitle 	= $row[0]->cattitle;
		}
		else {
			$user_catid = 0;
		}

		// CHECK COMMAND OPTIONS FOR ACCESS RIGHTS

		if ($access_rights_args_found)
		{
			// check if any of the specific access rights apply to the current user
			$userid_0_permission = 0;
			foreach ($access_rights_args[0] as $access_rights_pair)
			{
				list ($tmp_userid, $tmp_permission) = explode(":", $access_rights_pair);
				//echo "FOUND PAIR : [$tmp_userid], [$tmp_permission]<br />";

				// if overriding default registered-users permission
				if ($userid && !strcasecmp($tmp_userid, "REG"))
				{
					if (!strcasecmp($tmp_permission, "1n"))
					{
						$access_rights = 1;
						$disable_level_1_downloads = 1;
					}
					else
					{
						$access_rights = $tmp_permission;
						$disable_level_1_downloads = 0;
					}
				}

				// if userid 0 (visitor) is specified, take note of the relevant permission, and assign it immediately out of this loop
				// if access rights have not been defined, or they are lower than user 0's (this prevents a registered user having lower access than a visitor)
				// TODO change this to check if the current userid is 0 (or is guest) and assign immediately access right if tmp_userid value of 0 is also found
				if (!strcmp($tmp_userid, "0"))
				{
					if (!strcasecmp($tmp_permission, "1n"))
					{
						$userid_0_permission = 1;
						$disable_level_1_downloads = 1;
					}
					else
					{
						$userid_0_permission = $tmp_permission;
						$disable_level_1_downloads = 0;
					}
				}
				if ($tmp_userid[0] == 'g' || $tmp_userid[0] == 'G')
				{
					$tmp_userid[0] = ' ';
					$tmp_userid = ltrim($tmp_userid);

					if ($tmp_userid == $user_catid)
					{
						if (!strcasecmp($tmp_permission, "1n"))
						{
							$access_rights = 1;
							$disable_level_1_downloads = 1;
						}
						else
						{
							$access_rights = $tmp_permission;
							$disable_level_1_downloads = 0;
						}
					}
				}
				else if ($tmp_userid == $userid)
				{
					if (!strcasecmp($tmp_permission, "1n"))
					{
						$access_rights = 1;
						$disable_level_1_downloads = 1;
					}
					else
					{
						$access_rights = $tmp_permission;
						$disable_level_1_downloads = 0;
					}
					break;
				}
			}
			if (!$access_rights || $access_rights < $userid_0_permission)
			{
				$access_rights = $userid_0_permission;
			}
		}

		if (!$access_rights)
	        {
			$text  = "<div id='error'>"
				."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
				."<tr height='30' valign='middle'>"
				."<td width='60px'><img src=\"".$this->imgdir."warning.png\"></td><td>".JText::_('no_access_rights')."</td>"
				."</tr>"
				."</table>"
				."</div>";

			$article->text = $article->fulltext = $article->introtext = $text_array[0].$text.$text_array[1];
			return;
		}

		// CHECK COMMAND OPTIONS FOR DEFAULT SORT OVERRIDE

		$default_sort_by = $this->_params->def('default_sort_by', "name");
		$default_sort_as = $this->_params->def('default_sort_as', "desc");

		if ($access_rights_args_found)
		{
			foreach ($access_rights_args[0] as $access_rights_pair)
			{
				list ($tmp_userid, $tmp_permission) = explode(":", $access_rights_pair);
				//echo "FOUND PAIR : [$tmp_userid], [$tmp_permission]<br />";

				// if overriding default sorting backend parameters
				if (!strcasecmp($tmp_userid, "SORTBY"))
				{
					switch ($tmp_permission)
					{
					case 1:		$default_sort_by = "name";
							break;
					case 2:		$default_sort_by = "size";
							break;
					case 3:		$default_sort_by = "changed";
							break;
					default:	$default_sort_by = "name";
					}
				}
				if (!strcasecmp($tmp_userid, "SORTAS"))
				{
					switch ($tmp_permission)
					{
					case 1:		$default_sort_as = "desc";
							break;
					case 2:		$default_sort_as = "asc";
							break;
					default:	$default_sort_as = "desc";
					}
				}
			}
		}

		// CHECK COMMAND OPTIONS FOR THUMBSIZE OVERRIDE

		$this->thumbsize = $this->_params->def('thumbsize', 60);

		if ($access_rights_args_found)
		{
			foreach ($access_rights_args[0] as $access_rights_pair)
			{
				list ($tmp_cmd, $tmp_value) = explode(":", $access_rights_pair);
				//echo "FOUND PAIR : [$tmp_userid], [$tmp_permission]<br />";

				// if overriding default sorting backend parameters
				if (!strcasecmp($tmp_cmd, "THUMBSIZE"))
				{
					$this->thumbsize = $tmp_value;
				}
			}
		}

		// DEBUG access rights and other command options
		if (0)
	       	{
			echo "<br />I am user [".$username."] with ID [".$userid."] (cat ID ".$user_catid.", cat TITLE [".$user_cattitle."]) and I have access rights [".$access_rights."]<br />";
			echo "default_sort_by : [".$default_sort_by."]<br />";
			echo "default_sort_as : [".$default_sort_as."]<br /><br />";
		}

		// GOT HERE SO GO AHEAD WITH DISPLAY
		
		$error = NULL;
		$success = NULL;

		// ***********************************************************************************************************************
		// GET REPOSITORY DATA
		// ***********************************************************************************************************************

		// get repository from the command, and follow these rules:
		//
		// if a string is found within brackets xyz(repository) where xyz is either ABS or REL, take it as a repository that overrides
		// the backend default path parameter and relative path indicator (depending on ABS or REL the repository in brackets id an absolute
		// repository or it is relative to the Joomla! installation folder - ONLY AVAILABLE IF DEFAULT PATH OVERRIDE PARAMETER IS ENABLED
		//
		// if a string is found within square brackets [repository], then take it as a repository located within the backend default path parameter;
		// only in this case you may have a USERBOUND or GROUPBOUND repository
		//
		
		// see if overriding default path (if enabled from the backend)
		$default_path_override_enabled = $this->_params->def('default_path_override_enabled', 0);

		$repository_match = array();
		$repository_found = preg_match("/(abspath|relpath)\(.*?\)/i", $command_match[0], $repository_match);
		if ($default_path_override_enabled && $repository_found)
		{
			$repository = $this->chosen_decoding($repository_match[0]);
			$repository_rel_abs_string = substr($repository, 0, 7);
			$repository = trim(substr($repository, 8, strlen($repository) - 7), "()");
			$repository = rtrim($repository, "/\\");

			if (!strcasecmp($repository_rel_abs_string, "ABSPATH"))
			{
				$is_path_relative = 0;
				$this->default_absolute_path = $repository;
			}
			else if (!strcasecmp($repository_rel_abs_string, "RELPATH"))
			{
				$is_path_relative = 1;
				$this->default_absolute_path = JPATH_ROOT.DS.$repository;
			}
			$starting_dir = $this->default_absolute_path;
			$repository = ""; // we need this to make the navigation bar work nicely
		}
		else
		{
			// if no overriding repository is found, proceed normally
			$repository_match = array();
			$repository_found = preg_match("/\[.*?\]/i", $command_match[0], $repository_match);
			if ($repository_found && !strstr($repository_match[0], ":")) // note: avoid mistaking permission pairs for a missing repository (they contain ':')
			{
				$repository = $this->chosen_decoding(trim($repository_match[0], "[]"));
				$repository = rtrim($repository, "/\\");
			}
			else
			{
				$repository = "";
			}

			// check if repository is USERBOUND; in this case set the repository to be the user ID and set the access rights to give maximum access
			if (strtoupper($repository) == "USERBOUND")
	       		{
				if (!$is_path_relative || !$userid)	// TODO specialise cases: maybe allow for guest case...
	        		{
					$text  = "<div id='error'>"
						."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
						."<tr height='30' valign='middle'>"
						."<td width='60px'><img src=\"".$this->imgdir."warning.png\"></td><td>".JText::_('no_access_rights')."</td>" // TODO change this warning text to something more appropriate
						."</tr>"
						."</table>"
						."</div>";
		
					$article->text = $article->fulltext = $article->introtext = $text_array[0].$text.$text_array[1];
					return;
				}

				$userbound_prefix_use = $this->_params->def('userbound_prefix_use', 1);				// needed because setting a default value will not allow prefix to be an empty string
				$userbound_prefix = $userbound_prefix_use ? $this->_params->def('userbound_prefix', "Personal area for user ID") : "";
				$userbound_suffix = $this->_params->def('userbound_suffix', "");
				$userbound_parameter = $this->_params->def('userbound_parameter', 0);	// 0 for ID, 1 for NAME, 2 for USERNAME
				switch($userbound_parameter)
		        	{
					case 0:	$userbound_parameter = $userid;
						break;
					case 1:	$userbound_parameter = $username;
						break;
					case 2:	$userbound_parameter = $user_username;
						break;
				}
				$access_rights = $this->_params->def('default_personal_access_rights', 5);

				$repository = (strlen($userbound_prefix) ? $userbound_prefix." " : "").$userbound_parameter.(strlen($userbound_suffix) ? " ".$userbound_suffix : "");

				$userbound_repository_with_id   = $repository;							// needed in navigation bar
				$userbound_repository_with_name = JText::sprintf('personal_area_for_username', $username);	// needed in navigation bar
			}
			else if (strtoupper($repository) == "GROUPBOUND")
		       	{
				if (!$is_path_relative || !$user_catid)	// TODO specialise cases: maybe allow for guest case...
		        	{
					$text  = "<div id='error'>"
						."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
						."<tr height='30' valign='middle'>"
						."<td width='60px'><img src=\"".$this->imgdir."warning.png\"></td><td>".JText::_('no_access_rights')."</td>" // TODO change this warning text to something more appropriate
						."</tr>"
						."</table>"
						."</div>";
	
					$article->text = $article->fulltext = $article->introtext = $text_array[0].$text.$text_array[1];
					return;
				}

				$groupbound_prefix_use = $this->_params->def('groupbound_prefix_use', 1);			// needed because setting a default value will not allow prefix to be an empty string
				$groupbound_prefix = $groupbound_prefix_use ? $this->_params->def('groupbound_prefix', "Shared area for group ID") : "";
				$groupbound_suffix = $this->_params->def('groupbound_suffix', "");
				$groupbound_parameter = $this->_params->def('groupbound_parameter', 0);	// 0 for ID, 1 for TITLE
				$access_rights = $this->_params->def('default_group_access_rights', 5);

				$repository = (strlen($groupbound_prefix) ? $groupbound_prefix." " : "").($groupbound_parameter ? $user_cattitle : $user_catid).(strlen($groupbound_suffix) ? " ".$groupbound_suffix : "");
				$groupbound_repository_with_id 	 = $repository;								// needed in navigation bar
				$groupbound_repository_with_name = JText::sprintf('shared_area_for_category_name', $user_cattitle);	// needed in navigation bar
			}

			// set the starting directory as an absolute path (if relative add joomla!'s root)
			if ($is_path_relative)
			{
				$this->default_absolute_path = JPATH_ROOT.DS.$this->chosen_decoding(trim($this->_params->def('default_path', 'jsmallfib_top'), "/\\"));

				// create the default path folder (only if 1. does not exist already; 2. using relative path)
				if (!file_exists($this->default_absolute_path))
				{
					if (!($rc = @mkdir ($this->default_absolute_path)))
					{
						$text  = "<div id='error'>"
							."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
							."<tr height='30' valign='middle'>"
							."<td width='60px'><img src=\"".$this->imgdir."warning.png\"></td><td>".JText::sprintf('failed_creating_default_dir', $this->default_absolute_path)."</td>"
							."</tr>"
							."</table>"
							."</div>";
		
						$article->text = $article->fulltext = $article->introtext = $text_array[0].$text.$text_array[1];
						return;
					}
				}
			}
			else
			{
				$this->default_absolute_path = $this->chosen_decoding(rtrim($this->_params->def('default_path', JPATH_ROOT.DS.'jsmallfib_top'), "/\\"));
			}
			if ($repository)
			{
				$starting_dir = $this->default_absolute_path.DS.$repository;
			}
			else
			{
				$starting_dir = $this->default_absolute_path;
			}

		} // end of check for repository within command

		// if using default path and starting dir does not exist, attempt to create it
		if ($is_path_relative && !file_exists($starting_dir))
		{
			if (!($rc = @mkdir ($starting_dir)))
			{
				$text  = "<div id='error'>"
					."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
					."<tr height='30' valign='middle'>"
					."<td width='60px'><img src=\"".$this->imgdir."warning.png\"></td><td>".JText::sprintf('failed_creating_repository', $this->chosen_encoding($repository), $this->default_absolute_path)."</td>"
					."</tr>"
					."</table>"
					."</div>";
		
				$article->text = $article->fulltext = $article->introtext = $text_array[0].$text.$text_array[1];
				return;
			}
		}

		// get optional description from within the command: must be in the form desc(this is a description)
		$description_args = array();
		$description_args_found = preg_match_all("/desc\(.*?\)/i", $command_match[0], $description_args);
		if ($description_args_found)
		{
			$description = substr_replace($description_args[0][0], "", 0, 5);
			$description = substr_replace($description, "", -1, 1);
		}
		else
		{
			$description = "";
		}
			
		// build link base
		$option = JREQUEST::getVar('option', 0);
		$id = JREQUEST::getVar('id', 0);
		$Itemid = JREQUEST::getVar('Itemid', 0);

		if (!strcmp(strtoupper($view), "ITEM"))
		{
			// K2
			$this->baselink = JRoute::_(JURI::base().'index.php?option='.$option.'&view='.$view.'&layout=item&id='.$id.'&Itemid='.$Itemid);
		}
		else
		{
			$this->baselink = JRoute::_(JURI::base().'index.php?option='.$option.'&view='.$view.'&id='.$id.'&Itemid='.$Itemid);
		}

		// The array of folders that will be hidden from the list.
		$hidden_folders_parameter = $this->_params->def('hidden_folders', 0);
		$hidden_folders = array();
		$hidden_folders = preg_split("/\s*,+\s*/", $hidden_folders_parameter.", JS_ARCHIVE, JS_THUMBS");

		// Manage filenames and extensions that will be hidden from the list.
		$hidden_files_parameter = $this->_params->def('hidden_files', 0);
		
		$hidden_extensions = array();
		//$hidden_extensions_found = preg_match_all("/\*{1}\.{1}\w+/", $hidden_files_parameter, $hidden_extensions);	// this matches *.php but not  *.th.jpg
		$hidden_extensions_found = preg_match_all("/\*{1}\.{1}[\w\.]+/", $hidden_files_parameter, $hidden_extensions);	// this matches *.php but also *.th.jpg

		$hidden_prefixes = array();
		$hidden_prefixes_found = preg_match_all("/[^\s]+\*{1}/", $hidden_files_parameter, $hidden_prefixes);

		$hidden_files = array();
		$hidden_files_string = trim(preg_replace("/\*{1}\.{1}\w+/", "", $hidden_files_parameter));
		$hidden_files_string = trim(preg_replace("/[^s]+\*{1}/", "", $hidden_files_string));
		$hidden_files = preg_split("/\s*,+\s*/", $hidden_files_string);

		// ***********************************************************************************************************************
		// Managing input from user actions
		// ***********************************************************************************************************************

		// set variables for logs
		$log_uploads        = $this->_params->def('log_uploads', 0);
		$log_downloads      = $this->_params->def('log_downloads', 0);
		$log_removedfolders = $this->_params->def('log_removedfolders', 0);
		$log_removedfiles   = $this->_params->def('log_removedfiles', 0);
		$log_restoredfiles  = $this->_params->def('log_restoredfiles', 0);
		$log_newfolders     = $this->_params->def('log_newfolders', 0);
		$log_newfoldernames = $this->_params->def('log_newfoldernames', 0);
		$log_newfilenames   = $this->_params->def('log_newfilenames', 0);

		$logfile_prefix    = JPATH_ROOT.DS."logs".DS."jsmallfib_log_".md5($starting_dir)."_";
		$today = date("Y-m-d H:i:s");

		// Let's see what folder is being opened and react accordingly
		$dir = $starting_dir;
		$upper_dir = "";
		
		$a_file_was_removed = 0;

		if(isset($_GET["dir"]) && strlen($_GET["dir"])) 
		{
			// we had to utf-8 encode delfolders and delfiles for Firefox (special chars are not sent to javascript for delete confirmation)
			if ((isset($_GET["delfile"]) && strlen($_GET["delfile"])) || (isset($_GET["delfolder"]) && strlen($_GET["delfolder"])) || (isset($_GET["restorefile"]) && strlen($_GET["restorefile"])))
			{
				$get_dir = html_entity_decode($this->chosen_decoding(urldecode($_GET["dir"])));  // NOTE: Here we need urldecode as delfile is double encoded /ErikLtz
			}
			else
			{
				$get_dir = html_entity_decode($_GET["dir"]);   // Removed urldecode on _GET (not delete) /ErikLtz 
			}

			// unmask get_dir now that's been decoded
			$get_dir = $this->unmaskAbsPath($get_dir);

			// This format is forbidden (also check for trying to access folders outside the repository root)
			if(preg_match("/\.\.(.*)/", $get_dir) || (strlen($get_dir) == 1 && $get_dir[0] == DS) || (!stristr(str_replace("/", "\\", $get_dir), str_replace("/", "\\", $starting_dir)))) 
			{
				$dir = $starting_dir;
				$upper_dir = "";
			}
			else
			{
				// if got here then the user is allowed to view the current folder (remove the upper link if this is the starting_dir)
				$dir = rtrim($get_dir, "/\\");
				if(strcmp(str_replace("/", "\\", $starting_dir), str_replace("/", "\\", $get_dir)))
				{
					$upper_dir = $this->upperDir($this->maskAbsPath($dir));
				}

				// if asking to delete a folder
				if ($access_rights > 3 && isset($_GET["delfolder"]) && strlen($_GET["delfolder"]))
				{
					// only works with empty folders
					$tmpdir=html_entity_decode($dir.DS.$this->chosen_decoding(urldecode($_GET["delfolder"])));  // NOTE: Here we need urldecode as delfolder is double encoded /ErikLtz
					$rc = @rmdir ($tmpdir);
					
					// Check whether directory is gone
					if(file_exists($tmpdir)) {
						
						// Nah, still there, show new error message
						$error .= JText::sprintf('delete_folder_failed', urldecode($_GET["delfolder"]))."<br /><br />";  // NOTE: Here we need urldecode as delfolder is double encoded /ErikLtz
					
					} else {
						
						// for logging purposes
						$removed_folder = "";
						if($log_removedfolders && $rc)
						{
							$removed_folder = $this->chosen_decoding(urldecode($_GET["delfolder"]));  // NOTE: Here we need urldecode as delfolder is double encoded /ErikLtz
						}
					}
				}
				// if asking to delete a file
				else if ($access_rights > 2 && isset($_GET["delfile"]) && strlen($_GET["delfile"]))
				{
					$rc = @unlink (html_entity_decode($dir.DS.$this->chosen_decoding(urldecode($_GET["delfile"]))));  // NOTE: Here we need urldecode as delfile is double encoded /ErikLtz

					// try removing thumbnail and thumbs dir (will only work if a thumbnail for this file exists and if the thumbs dir is empty)
					$rc_thumbs = @unlink (html_entity_decode($dir.DS."JS_THUMBS".DS.$this->chosen_decoding(urldecode($_GET["delfile"]))));
					$rc = @rmdir($dir.DS."JS_THUMBS");

					// for logging purposes
					$removed_file = "";
					if($log_removedfiles && $rc)
					{
						$removed_file = $this->chosen_decoding(urldecode($_GET["delfile"]));  // NOTE: Here we need urldecode as delfile is double encoded /ErikLtz
					}

					// this is used later to check if the file deleted was the last one of an archive
					if ($rc)
					{
						$a_file_was_removed = 1;
					}
				}
				// if asking to restore an archived file
				else if ($access_rights > 2 && isset($_GET["restorefile"]) && strlen($_GET["restorefile"]))
				{
					if(!@copy(html_entity_decode($dir.DS.$this->chosen_decoding(urldecode($_GET['restorefile']))), html_entity_decode($this->upperDir($dir).DS.$this->chosen_decoding(urldecode($this->restoreArchiveFilename($_GET['restorefile']))))))
					{
						$error .= JText::sprintf('restorefile_failed', urldecode($_GET['restorefile']))."<br /><br />";
						$restored_file = "";
					}
					else
					{
						@chmod(html_entity_decode($this->upperDir($dir).DS.$this->chosen_decoding(urldecode($_GET['restorefile']))), $default_file_chmod);

						$success .= JText::sprintf('restorefile_success', urldecode($_GET['restorefile']))."<br /><br />";

						// for logging purposes
						if($log_restoredfiles)
						{
							$restored_file = $this->chosen_decoding(urldecode($_GET["restorefile"]));  // NOTE: Here we need urldecode as restore is double encoded
						}
					}
				}
			}
		}

		// set masked dirs to mask the absolute path (to be used in get strings)
		$masked_dir = $this->maskAbsPath($dir);
		$masked_starting_dir = $this->maskAbsPath($starting_dir);

		// once dir is defined (with absolute path), define the current position (the sub-path under the main default repository folder),
		// and the relative dir (dir relative to the web root)
		$current_position = substr($dir, strlen($this->default_absolute_path) + 1, strlen($dir) - strlen($this->default_absolute_path));
		$relative_dir = substr($dir, strlen(JPATH_ROOT) + 1, strlen($dir) - strlen(JPATH_ROOT));

		// if the repository is OUTSIDE the web root then use for files the absolute path
		// (you won't be able to display files left-clicking, but you'll be able to download them right-clicking on them)
		// note: user SERVER[DOCUMENT_ROOT] if available, otherwise use JPATH_ROOT (which adds the joomla folder, which might be below the actual webroot)
		$base_web_root = isset($_SERVER['DOCUMENT_ROOT']) && strlen($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : JPATH_ROOT;
//		if(!stristr(str_replace("/", "\\", $dir), str_replace("/", "\\", $_SERVER["DOCUMENT_ROOT"]))) 
		if(!stristr(str_replace("/", "\\", $dir), str_replace("/", "\\", $base_web_root))) 
		{
			$relative_dir = $dir;
		}

		// set the navigation links to put on top of the repository
		$is_current_position_an_archive = !strcmp(substr($current_position, -10), "JS_ARCHIVE");

		if (!$current_position || ($is_current_position_an_archive && !$this->upperDir($current_position)))
		{
			$current_position_links = "<a href='".$this->baselink."&dir=".urlencode($masked_starting_dir)."'>".JText::_('toplevel')."</a>";
		}
		else
		{

			if (!$is_current_position_an_archive)
			{
				// Use current_position to build linked list of directories in $current_position_links [ErikLtz]
				$arr = explode(DS, $current_position);
				$current_position_links = "";
				$tmpdir = $masked_dir;
		  
				for($i = count($arr) - 1; $i >= 0; $i--) {
			
					$current_position_links = "<a href='".$this->baselink."&dir=".urlencode($tmpdir)."'>".$arr[$i]."</a>"
						.($i == count($arr) - 1 ? "" : "&nbsp;<img src=\"".$this->imgdir."arrow_right.png\" />&nbsp;").$current_position_links;

				  	$tmpdir=$this->upperDir($tmpdir);
				}

				// if the repository is not reported in the command (using default top repository) then display link to top level (to default top repository)
				if (!$repository)
				{
					$current_position_links = "<a href='".$this->baselink."&dir=".urlencode($masked_starting_dir)."'>".JText::_('toplevel')."</a>"
						."&nbsp;<img src=\"".$this->imgdir."arrow_right.png\" />&nbsp;".$current_position_links;
				}
			}
			else
			{
				// if inside an archive
				$arr = explode(DS, $current_position);
				$current_position_links = "";
				$tmpdir = $this->upperDir($masked_dir);
		  
				for($i = count($arr) - 2; $i >= 0; $i--) {
			
					$current_position_links = "<a href='".$this->baselink."&dir=".urlencode($tmpdir)."'>".$arr[$i]."</a>"
						.($i == count($arr) - 2 ? "" : "&nbsp;<img src=\"".$this->imgdir."arrow_right.png\" />&nbsp;").$current_position_links;

				  	$tmpdir=$this->upperDir($tmpdir);
				}

				// if the repository is not reported in the command (using default top repository) then display link to top level (to default top repository)
				if (!$repository)
				{
					$current_position_links = "<a href='".$this->baselink."&dir=".urlencode($masked_starting_dir)."'>".JText::_('toplevel')."</a>"
						."&nbsp;<img src=\"".$this->imgdir."arrow_right.png\" />&nbsp;".$current_position_links;
				}
			}
		}

		// once dir is finally completely established, set requested cookies

		// set display filter cookie
		if (isset($_POST['current_filter_list']) && strlen($_POST['current_filter_list']))
		{
			setcookie('current_filter_list', $_POST['current_filter_list']);
			header('Location: '.$this->baselink."&dir=".urlencode($masked_dir));
		}
		else if (isset($_GET['current_filter_list']) && !strlen($_POST['current_filter_list']))
		{
			setcookie('current_filter_list', "");
			header('Location: '.$this->baselink."&dir=".urlencode($masked_dir));
		}

		// set display upload actions cookie (NOTE: ...display_actions... refers to all actions, for example UPLOAD. In case of more action boxes, only one will be open at any one time)
		if (isset($_GET['set_display_actions_cookie']) && !strcmp($_GET['set_display_actions_cookie'], "UPLOAD"))
		{
			setcookie("display_actions", "UPLOAD", time() + 3600 * 24 * 365);
			header('Location: '.$this->baselink."&dir=".urlencode($masked_dir));
		}
		else if (isset($_GET['set_display_actions_cookie']) && !strcmp($_GET['set_display_actions_cookie'], "NO_ACTION"))
		{
			setcookie("display_actions", "", time() - 3600 * 24 * 365);
			header('Location: '.$this->baselink."&dir=".urlencode($masked_dir));
		}

		// now that the current dir is established, log removals/restores registered above
		if($log_removedfolders && $removed_folder)
		{
			$log_file = $logfile_prefix."removedfolders.txt";
			$log_text = JText::sprintf('removedfolder_log_text', $today, $this->chosen_encoding($removed_folder), $this->chosen_encoding($relative_dir), $username, $remote_address);
			if($log_removedfolders > 1 && $this->email_log($log_text))
			{
				file_put_contents($log_file, "MAIL ERROR > ", FILE_APPEND);
			}
			file_put_contents($log_file, $log_text, FILE_APPEND);

			$removed_folder = "";
		}
		if($log_removedfiles && $removed_file)
		{
			$log_file = $logfile_prefix."removedfiles.txt";
			$log_text = JText::sprintf('removedfile_log_text', $today, $this->chosen_encoding($removed_file), $this->chosen_encoding($relative_dir), $username, $remote_address);
			if($log_removedfiles > 1 && $this->email_log($log_text))
			{
				file_put_contents($log_file, "MAIL ERROR > ", FILE_APPEND);
			}
			file_put_contents($log_file, $log_text, FILE_APPEND);

			$removed_file = "";
		}
		if($log_restoredfiles && $restored_file)
		{
			$log_file = $logfile_prefix."restoredfiles.txt";
			$log_text = JText::sprintf('restoredfile_log_text', $today, $this->chosen_encoding($restored_file), $this->chosen_encoding($this->upperDir($relative_dir)), $username, $remote_address);
			if($log_restoredfiles > 1 && $this->email_log($log_text))
			{
				file_put_contents($log_file, "MAIL ERROR > ", FILE_APPEND);
			}
			file_put_contents($log_file, $log_text, FILE_APPEND);

			$restored_file = "";
		}
		
		// creating the new directory
		if($access_rights > 1 && isset($_POST['userdir']) && strlen($_POST['userdir']) > 0)
		{
			$forbidden = array(".", "/", "\\");
			for($i = 0; $i < count($forbidden); $i++)
			{
				$_POST['userdir'] = str_replace($forbidden[$i], "", $_POST['userdir']);
			}
			$tmpdir = html_entity_decode($dir.DS.$this->chosen_decoding($_POST['userdir']));
			if(!@mkdir($tmpdir, 0777))
			{
				// Check for existing file with same name and choose different error message [ErikLtz]
				if(file_exists($tmpdir))
				{
					$error .= JText::_('new_folder_failed_exists')."<br /><br />";
				}
				else
				{
					$error .= JText::_('new_folder_failed')."<br /><br />";
				}
			}
			else if(!@chmod($tmpdir, $default_dir_chmod))
			{
				$error .= JText::_('chmod_dir_failed')."<br /><br />";
			}
			else if($log_newfolders)
			{
				// log
				$log_file = $logfile_prefix."newfolders.txt";
				$log_text = JText::sprintf('newfolder_log_text', $today, $_POST['userdir'], $this->chosen_encoding($relative_dir), $username, $remote_address);
				if($log_newfolders > 1 && $this->email_log($log_text))
				{
					file_put_contents($log_file, "MAIL ERROR > ", FILE_APPEND);
				}
				file_put_contents($log_file, $log_text, FILE_APPEND);
			}
		}

		// changing name to a folder
		if($access_rights > 1 && isset($_POST['old_foldername']) && strlen($_POST['old_foldername']) > 0 &&
		       			 isset($_POST['new_foldername']) && strlen($_POST['new_foldername']) > 0)
		{
			$old_foldername = urldecode($_POST['old_foldername']);
			$new_foldername = $this->chosen_decoding($_POST['new_foldername']); // this is utf-8 encoded because it comes from the visible text field

			$forbidden = array(".", "/", "\\");
			for($i = 0; $i < count($forbidden); $i++)
			{
				$old_foldername = str_replace($forbidden[$i], "", $old_foldername);
			}
			for($i = 0; $i < count($forbidden); $i++)
			{
				$new_foldername = str_replace($forbidden[$i], "", $new_foldername);
			}
			if(!@rename(html_entity_decode($dir."/".$old_foldername), html_entity_decode($dir."/".$new_foldername)))
			{
				$error .= JText::sprintf('folder_rename_failed', $this->chosen_encoding($old_foldername), $this->chosen_encoding($new_foldername))."<br /><br />";
			}
			else if($log_newfoldernames)
			{
				// log
				$log_file = $logfile_prefix."newfoldernames.txt";
				$log_text = JText::sprintf('newfoldername_log_text', $today, $this->chosen_encoding($old_foldername), $this->chosen_encoding($new_foldername), $this->chosen_encoding($relative_dir), $username, $remote_address);
				if($log_newfoldernames > 1 && $this->email_log($log_text))
				{
					file_put_contents($log_file, "MAIL ERROR > ", FILE_APPEND);
				}
				file_put_contents($log_file, $log_text, FILE_APPEND);
			}
		}

		// changing name to a file
		if($access_rights > 1 && isset($_POST['old_filename']) && strlen($_POST['old_filename']) > 0 &&
		       			 isset($_POST['new_filename']) && strlen($_POST['new_filename']) > 0)
		{
			$old_filename = urldecode($_POST['old_filename']);
			$new_filename = $this->chosen_decoding($_POST['new_filename']);

			$forbidden = array("/", "\\");
			for($i = 0; $i < count($forbidden); $i++)
			{
				$old_filename = str_replace($forbidden[$i], "", $old_filename);
			}
			for($i = 0; $i < count($forbidden); $i++)
			{
				$new_filename = str_replace($forbidden[$i], "", $new_filename);
			}
			if(!@rename(html_entity_decode($dir."/".$old_filename), html_entity_decode($dir."/".$new_filename)))
			{
				$error .= JText::sprintf('file_rename_failed', $this->chosen_encoding($old_filename), $this->chosen_encoding($new_filename))."<br /><br />";
			}
			else if($log_newfilenames)
			{
				// try removing thumbnail of oldname file (will only work if a thumbnail for this file exists)
				$rc_thumbs = @unlink (html_entity_decode($dir.DS."JS_THUMBS".DS.$old_filename));

				// log
				$log_file = $logfile_prefix."newfilenames.txt";
				$log_text = JText::sprintf('newfilename_log_text', $today, $this->chosen_encoding($old_filename), $this->chosen_encoding($new_filename), $this->chosen_encoding($relative_dir), $username, $remote_address);
				if($log_newfilenames > 1 && $this->email_log($log_text))
				{
					file_put_contents($log_file, "MAIL ERROR > ", FILE_APPEND);
				}
				file_put_contents($log_file, $log_text, FILE_APPEND);
			}
			else
			{
				// try removing thumbnail of oldname file (will only work if a thumbnail for this file exists)
				$rc_thumbs = @unlink (html_entity_decode($dir.DS."JS_THUMBS".DS.$old_filename));
			}
		}

		// MANAGING UPLOADS **********************************

		$allow_file_archiving = $this->_params->def('allow_file_archiving', 1);

		// moving the uploaded file (HTML upload)

		if($access_rights > 1 && isset($_GET['keep_existing_file']) && isset($_GET['tmpfiletoupload']))
		{
			// unlink WAITING file
			@unlink(html_entity_decode($this->chosen_decoding($_GET['tmpfiletoupload']."_WAITING")));
		}
		else if($access_rights > 1 && (isset($_GET['override_file']) || isset($_GET['archive_file'])))
		{
			$name = $this->baseName($this->chosen_decoding($_GET['filetoupload']));

			$upload_dir = urldecode($_GET['uploaddir']); // we urldecode this get variable as this is originally a post undecoded one (and masked, so we unmask it in the next line)

			$upload_dir = $this->unmaskAbsPath($upload_dir);
			
			// security check on upload_dir (suggestion by Mark Gentry)
			if(preg_match("/\.\.(.*)/", $upload_dir) || (strlen($upload_dir) == 1 && $upload_dir[0] == DS) || (!stristr(str_replace("/", "\\", $upload_dir), str_replace("/", "\\", $starting_dir)))) 
			{
				$text  = "<div id='error'>"
					."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
					."<tr height='30' valign='middle'>"
					."<td width='60px'><img src=\"".$this->imgdir."warning.png\"></td><td>".JText::_('security_file_upload')."</td>"
					."</tr>"
					."</table>"
					."</div>";

				$article->text = $article->fulltext = $article->introtext = $text_array[0].$text.$text_array[1];
				return;
			}

			if ($_GET['override_file'] == 1)
			{
				$upload_file = $upload_dir.DS.$name;

				// copy WAITING file onto existing one (will then unlink WAITING tmp file)
				if(!@copy(html_entity_decode($this->chosen_decoding($_GET['tmpfiletoupload']."_WAITING")), html_entity_decode($upload_file)))
				{
					$error .= JText::_('failed_move')."<br /><br />";
				}
				else
				{
					@chmod(html_entity_decode($upload_file), $default_file_chmod);

					// log
					if($log_uploads)
					{
						$log_file = $logfile_prefix."uploads.txt";
						$log_text = JText::sprintf('upload_log_text', $today, $this->chosen_encoding($this->baseName($upload_file)), $this->chosen_encoding($relative_dir), $username, $remote_address);
						if($log_uploads > 1 && $this->email_log($log_text))
						{
							file_put_contents($log_file, "MAIL ERROR > ", FILE_APPEND);
						}
						file_put_contents($log_file, $log_text, FILE_APPEND);
					}
				}
			}
			else if ($allow_file_archiving && $_GET['archive_file'] == 1)
			{
				if (!is_dir($upload_dir.DS."JS_ARCHIVE") && !($rc = @mkdir ($upload_dir.DS."JS_ARCHIVE")))
				{
					$text  = "<div id='error'>"
						."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
						."<tr height='30' valign='middle'>"
						."<td width='60px'><img src=\"".$this->imgdir."warning.png\"></td><td>".JText::sprintf('failed_creating_archive_dir', $upload_dir.DS."JS_ARCHIVE")."</td>"
						."</tr>"
						."</table>"
						."</div>";
		
					$article->text = $article->fulltext = $article->introtext = $text_array[0].$text.$text_array[1];
					return;
				}

				$upload_file = $upload_dir.DS.$name;

				if (strpos($name, '.') === false)
				{
					$archive_file = $upload_dir.DS."JS_ARCHIVE".DS.$name." (".JText::_('archived')." ".date("Y-m-d H.i.s").")";
				}
				else
				{
					$archive_file = $this->fileWithoutExtension($upload_dir.DS."JS_ARCHIVE".DS.$name)." (".JText::_('archived')." ".date("Y-m-d H.i.s").").".$this->fileExtension($name);
				}

				// copy current file into archive folder
				if(!@copy(html_entity_decode($upload_file), html_entity_decode($archive_file)))
				{
					$error .= JText::_('failed_move')."<br /><br />";
				}
			       	else
				{
					// copy WAITING file onto existing one (will then unlink WAITING tmp file)
					if(!@copy(html_entity_decode($this->chosen_decoding($_GET['tmpfiletoupload']."_WAITING")), html_entity_decode($upload_file)))
					{
						$error .= JText::_('failed_move')."<br /><br />";
					}
					else
					{
						@chmod(html_entity_decode($upload_file), $default_file_chmod);

						// log
						if($log_uploads)
						{
							$log_file = $logfile_prefix."uploads.txt";
							$log_text = JText::sprintf('upload_log_text', $today, $this->chosen_encoding($this->baseName($upload_file)), $this->chosen_encoding($relative_dir), $username, $remote_address);
							if($log_uploads > 1 && $this->email_log($log_text))
							{
								file_put_contents($log_file, "MAIL ERROR > ", FILE_APPEND);
							}
							file_put_contents($log_file, $log_text, FILE_APPEND);
						}
					}
				}
			}
			
			// unlink WAITING file
			@unlink(html_entity_decode($this->chosen_decoding($_GET['tmpfiletoupload']."_WAITING")));
		}
		else if($access_rights > 1 && isset($_FILES['userfile']['name']) && strlen($_FILES['userfile']['name']) > 0)
		{
			$name = $this->baseName($this->chosen_decoding($_FILES['userfile']['name']));

			$upload_dir = urldecode($_POST['upload_dir']);

			$upload_dir = $this->unmaskAbsPath($upload_dir);

			// security check on upload_dir (suggestion by Mark Gentry)
			if(preg_match("/\.\.(.*)/", $upload_dir) || (strlen($upload_dir) == 1 && $upload_dir[0] == DS) || (!stristr(str_replace("/", "\\", $upload_dir), str_replace("/", "\\", $starting_dir)))) 
			{
				$text  = "<div id='error'>"
					."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
					."<tr height='30' valign='middle'>"
					."<td width='60px'><img src=\"".$this->imgdir."warning.png\"></td><td>".JText::_('security_file_upload')."</td>"
					."</tr>"
					."</table>"
					."</div>";

				$article->text = $article->fulltext = $article->introtext = $text_array[0].$text.$text_array[1];
				return;
			}

			$upload_file = $upload_dir.DS.$name;

			// DEBUG
			if (0)
	       		{
				var_dump(is_uploaded_file(html_entity_decode($this->chosen_decoding($_FILES['userfile']['tmp_name']))));
				echo "<br />0. Tried to move file [".html_entity_decode($_FILES['userfile']['tmp_name'])."] ("
						.(file_exists(html_entity_decode($_FILES['userfile']['tmp_name'])) ? "EXISTS" : "DOES NOT EXIST").") to ["
						.$this->chosen_encoding(html_entity_decode($upload_file))."]<br />";
				echo "Permission of temporary file is [".substr(sprintf('%o', @fileperms(html_entity_decode($this->chosen_decoding($_FILES['userfile']['tmp_name'])))), -4)."]<br />";
				echo "Permission of temporary folder [".html_entity_decode($this->upperDir($_FILES['userfile']['tmp_name']))."] is [".substr(sprintf('%o', @fileperms(html_entity_decode($this->chosen_decoding($this->upperDir($_FILES['userfile']['tmp_name']))))), -4)."]<br />";
				echo "Permission of destination file is [".substr(sprintf('%o', @fileperms(html_entity_decode($upload_file))), -4)."]<br />";
				echo "Permission of destination folder [".$this->chosen_encoding($upload_dir)."] is [".substr(sprintf('%o', fileperms(html_entity_decode($upload_dir))), -4)."]<br /><br />";
			}
			if(!is_uploaded_file(html_entity_decode($this->chosen_decoding($_FILES['userfile']['tmp_name']))))
			{
				$error .= JText::_('failed_upload')."<br /><br />";
			}
			else if(file_exists($upload_file))    // Check to avoid overwriting existing file /ErikLtz
			{
				if ($allow_file_archiving)
				{
					$error .= JText::sprintf('failed_upload_exists_archive',
						$this->baselink."&dir=".urlencode($masked_dir)."&keep_existing_file=1&tmpfiletoupload=".$_FILES['userfile']['tmp_name'],
						$this->baselink."&dir=".urlencode($masked_dir)."&override_file=1&uploaddir=".$_POST['upload_dir']."&filetoupload=".$_FILES['userfile']['name']."&tmpfiletoupload=".$_FILES['userfile']['tmp_name'],
						$this->baselink."&dir=".urlencode($masked_dir)."&archive_file=1&uploaddir=".$_POST['upload_dir']."&filetoupload=".$_FILES['userfile']['name']."&tmpfiletoupload=".$_FILES['userfile']['tmp_name'])."<br /><br />";
				}
				else
				{
					$error .= JText::sprintf('failed_upload_exists',
						$this->baselink."&dir=".urlencode($masked_dir)."&keep_existing_file=1&tmpfiletoupload=".$_FILES['userfile']['tmp_name'],
						$this->baselink."&dir=".urlencode($masked_dir)."&override_file=1&uploaddir=".$_POST['upload_dir']."&filetoupload=".$_FILES['userfile']['name']."&tmpfiletoupload=".$_FILES['userfile']['tmp_name'])."<br /><br />";
				}

				// copy tmp uploaded file to WAITING one and delete it
				move_uploaded_file(html_entity_decode($this->chosen_decoding($_FILES['userfile']['tmp_name'])), html_entity_decode($this->chosen_decoding($_FILES['userfile']['tmp_name']."_WAITING")));
				@unlink(html_entity_decode($this->chosen_decoding($_FILES['userfile']['tmp_name'])));
			}
			else if(!move_uploaded_file(html_entity_decode($this->chosen_decoding($_FILES['userfile']['tmp_name'])), html_entity_decode($upload_file)))
			{
				// DEBUG
				if (0)
	       			{
					var_dump(is_uploaded_file(html_entity_decode($this->chosen_decoding($_FILES['userfile']['tmp_name']))));
					echo "<br />1. Tried to move file [".html_entity_decode($_FILES['userfile']['tmp_name'])."] ("
						.(file_exists(html_entity_decode($_FILES['userfile']['tmp_name'])) ? "EXISTS" : "DOES NOT EXIST").") to ["
						.$this->chosen_encoding(html_entity_decode($upload_file))."]<br />";
					echo "Permission of temporary file is [".substr(sprintf('%o', @fileperms(html_entity_decode($this->chosen_decoding($_FILES['userfile']['tmp_name'])))), -4)."]<br />";
					echo "Permission of temporary folder [".html_entity_decode($this->upperDir($_FILES['userfile']['tmp_name']))."] is [".substr(sprintf('%o', @fileperms(html_entity_decode($this->chosen_decoding($this->upperDir($_FILES['userfile']['tmp_name']))))), -4)."]<br />";
					echo "Permission of destination file is [".substr(sprintf('%o', @fileperms(html_entity_decode($upload_file))), -4)."]<br />";
					echo "Permission of destination folder [".$this->chosen_encoding($upload_dir)."] is [".substr(sprintf('%o', fileperms(html_entity_decode($upload_dir))), -4)."]<br /><br />";
				}
				$error .= JText::_('failed_move')."<br /><br />";
			}
			else
			{
				@chmod(html_entity_decode($upload_file), $default_file_chmod);

				// log
				if($log_uploads)
				{
					$log_file = $logfile_prefix."uploads.txt";
					$log_text = JText::sprintf('upload_log_text', $today, $this->chosen_encoding($this->baseName($upload_file)), $this->chosen_encoding($relative_dir), $username, $remote_address);
					if($log_uploads > 1 && $this->email_log($log_text))
					{
						file_put_contents($log_file, "MAIL ERROR > ", FILE_APPEND);
					}
					file_put_contents($log_file, $log_text, FILE_APPEND);
				}
			}
		}

		// managing file download
		if($access_rights && isset($_GET['download_file']) && strlen($_GET['download_file']))
		{
			// send requested file
			$download_file = html_entity_decode($_GET['download_file']);   // Removed urldecode on _GET /ErikLtz
			$download_file = $this->unmaskAbsPath($download_file);

			// security check (problem raised by Ludovic De Luna on 20091013)
			if (strcmp(substr($download_file, 0, strlen($dir)), $dir) || preg_match("/\.\.(.*)/", $download_file)) {
				$text  = "<div id='error'>"
					."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
					."<tr height='30' valign='middle'>"
					."<td width='60px'><img src=\"".$this->imgdir."warning.png\"></td><td>".JText::_('security_file_download')."</td>"
					."</tr>"
					."</table>"
					."</div>";

				$article->text = $article->fulltext = $article->introtext = $text_array[0].$text.$text_array[1];
				return;
			}
			
			if (file_exists($download_file)) {

				ob_end_clean();
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header("Content-Disposition: attachment; filename=\"".$this->baseName($download_file)."\"");
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . filesize($download_file));
				@ob_flush();
				flush();

				// standard PHP function readfile() has documented problems with large files; readfile_chunked() is reported on php.net
				$this->readfile_chunked($download_file);
				//readfile($download_file);

				// log
				if($log_downloads)
				{
					$log_file = $logfile_prefix."downloads.txt";
					$log_text = JText::sprintf('download_log_text', $today, $this->chosen_encoding($this->baseName($download_file)), $this->chosen_encoding($relative_dir), $username, $remote_address);
					if($log_downloads > 1 && $this->email_log($log_text))
					{
						file_put_contents($log_file, "MAIL ERROR > ", FILE_APPEND);
					}
					file_put_contents($log_file, $log_text, FILE_APPEND);
				}
				die(); 	// stop execution of further script because we are only outputting the pdf
					// (see readfile() function comment by mark dated 17-Sep-2008 on php.net)
			}
			else
			{
				$text  = "<div id='error'>"
					."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
					."<tr height='30' valign='middle'>"
					."<td width='60px'><img src=\"".$this->imgdir."warning.png\"></td><td>".JText::sprintf('file_not_found', $this->chosen_encoding($this->baseName($download_file)))."</td>"
					."</tr>"
					."</table>"
					."</div>";

				$article->text = $article->fulltext = $article->introtext = $text_array[0].$text.$text_array[1];
				return;
			}
		}

		// asking for the actions log
		if($access_rights > 4 && isset($_GET['view_log']) &&
			($log_uploads || $log_downloads || $log_removedfolders || $log_removedfiles || $log_restoredfiles || $log_newfolders || $log_newfoldernames || $log_newfilenames))
		{
			$this->view_log($logfile_prefix, $article, $params, $description, $masked_dir, $log_uploads, $log_downloads, $log_removedfolders, $log_removedfiles, $log_restoredfiles, $log_newfolders, $log_newfoldernames, $log_newfilenames);
			return;
		}

		// asking for help
		if(isset($_GET['help']))
		{
			$this->do_help($article, $params, $description, $masked_dir);
			return;
		}

		// for file filtering
		$file_filter_pattern_required = 0;
		if (isset($_COOKIE['current_filter_list']) && strlen($_COOKIE['current_filter_list']))
		{
			$file_filter_pattern_required = 1;
		}

		// Reading the data of files and directories
		if($open_dir = @opendir(html_entity_decode(str_replace("\\", "/", $dir."/"))))
		{
			$dirs = array();
			$files = array();
			$i = 0;
			while ($it = @readdir($open_dir)) 
			{
				if($it != "." && $it != "..")
				{
					if(is_dir($dir.DS.$it))
					{
						if(!in_array($it, $hidden_folders))
							$dirs[] = htmlspecialchars($it);
					}
					//else if(!in_array($it, $hidden_files) && !in_array("*.".$this->fileExtension($it), $hidden_extensions[0]))
					else if(!in_array($it, $hidden_files))
					{
						$matched_prefix = 0;
						for ($k = 0; $k < count($hidden_prefixes[0]); $k++)
						{
							if (!strncasecmp($hidden_prefixes[0][$k], $it, strlen($hidden_prefixes[0][$k]) - 1))
								$matched_prefix = 1;
						}

						$matched_extension = 0;
						for ($k = 0; $k < count($hidden_extensions[0]); $k++)
						{
							if (!strncasecmp(strrev($hidden_extensions[0][$k]), strrev($it), strlen($hidden_extensions[0][$k]) - 1))
								$matched_extension = 1;
						}

						// file list filtering
						$file_filter_pattern_matched = 0;
						if ($file_filter_pattern_required)
						{
							$pattern_array = explode(";", $this->chosen_decoding($_COOKIE['current_filter_list']));
							for ($k = 0; $k < count($pattern_array); $k++)
							{
								if (stristr($it, trim($pattern_array[$k])))
								{
									$file_filter_pattern_matched = 1;
								}
							}
						}

						if (!$matched_prefix && !$matched_extension && (!$file_filter_pattern_required || $file_filter_pattern_matched))
						{
							$files[$i]["name"]	= htmlspecialchars($it);
							$it			= $dir."/".$it;
							$files[$i]["extension"]	= $this->fileExtension($it);
							$files[$i]["size"]	= $this->fileRealSize($it);
							$files[$i]["changed"]	= filemtime($it);
							$i++;
						}
					}
				}
			}
			@closedir($open_dir);
		}
		else
		{
			$text  = "<div id='error'>"
				."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
				."<tr height='30' valign='middle'>"
				."<td width='60px'><img src=\"".$this->imgdir."warning.png\"></td><td>".JText::sprintf('dir_not_found', $this->chosen_encoding($current_position), $this->default_absolute_path)."</td>"
				."</tr>"
				."</table>"
				."</div>";

			$article->text = $article->fulltext = $article->introtext = $text_array[0].$text.$text_array[1];
			return;
		}

		// if a file was just successfully removed, we are in an archive folder and there are no more files, then remove the folder and reload to the upper level
		if ($a_file_was_removed && $is_current_position_an_archive && !$files)
		{
			$rc = @rmdir($dir);
					
			// check if the current directory (an archive) is gone
			if(file_exists($dir)) {
					
				$error .= JText::sprintf('delete_folder_failed', $dir)."<br /><br />";
			}
			else
		       	{
				header('Location: '.$this->baselink."&dir=".urlencode($this->upperDir($masked_dir)));
			}
		}

		// Sort files and folders. By default, they are sorted by name
		if($files || $dirs)
		{
			if(isset($_GET["sort_by"]) && isset($_GET["sort_as"]) && $_GET["sort_by"] == "name" && $_GET["sort_as"] != "asc")
			{
				@sort($dirs);
				@usort($files, array($this, "name_cmp_desc"));

				$this->cur_sort_by = "name";
				$this->cur_sort_as = "desc";
			}
			elseif(isset($_GET["sort_by"]) && isset($_GET["sort_as"]) && $_GET["sort_by"] == "name" && $_GET["sort_as"] == "asc")
			{
				@rsort($dirs);
				@usort($files, array($this, "name_cmp_asc"));

				$this->cur_sort_by = "name";
				$this->cur_sort_as = "asc";
			}
			elseif(isset($_GET["sort_by"]) && isset($_GET["sort_as"]) && $_GET["sort_by"] == "size" && $_GET["sort_as"] != "asc" && $files)
			{
				@usort($files, array($this, "size_cmp_desc"));

				$this->cur_sort_by = "size";
				$this->cur_sort_as = "desc";
			}
			elseif(isset($_GET["sort_by"]) && isset($_GET["sort_as"]) && $_GET["sort_by"] == "size" && $_GET["sort_as"] == "asc" && $files)
			{
				@usort($files, array($this, "size_cmp_asc"));

				$this->cur_sort_by = "size";
				$this->cur_sort_as = "asc";
			}
			elseif(isset($_GET["sort_by"]) && isset($_GET["sort_as"]) && $_GET["sort_by"] == "changed" && $_GET["sort_as"] != "asc" && $files)
			{
				@usort($files, array($this, "changed_cmp_desc"));

				$this->cur_sort_by = "changed";
				$this->cur_sort_as = "desc";
			}
			elseif(isset($_GET["sort_by"]) && isset($_GET["sort_as"]) && $_GET["sort_by"] == "changed" && $_GET["sort_as"] == "asc" && $files)
			{
				@usort($files, array($this, "changed_cmp_asc"));

				$this->cur_sort_by = "changed";
				$this->cur_sort_as = "asc";
			}
			else
			{
				// default sort by name
				if (!strcmp($default_sort_by, "name"))
				{
					if (!strcmp($default_sort_as, "desc"))
					{
						@sort($dirs);
						@usort($files, array($this, "name_cmp_desc"));

						$this->cur_sort_by = "name";
						$this->cur_sort_as = "desc";
					}
					else
					{
						@rsort($dirs);
						@usort($files, array($this, "name_cmp_asc"));

						$this->cur_sort_by = "name";
						$this->cur_sort_as = "asc";
					}
				}

				// default sort by size
				if (!strcmp($default_sort_by, "size"))
				{
					if (!strcmp($default_sort_as, "desc"))
					{
						@sort($dirs);
						@usort($files, array($this, "size_cmp_desc"));

						$this->cur_sort_by = "size";
						$this->cur_sort_as = "desc";
					}
					else
					{
						@rsort($dirs);
						@usort($files, array($this, "size_cmp_asc"));

						$this->cur_sort_by = "size";
						$this->cur_sort_as = "asc";
					}
				}

				// default sort by changed
				if (!strcmp($default_sort_by, "changed"))
				{
					if (!strcmp($default_sort_as, "desc"))
					{
						@sort($dirs);
						@usort($files, array($this, "changed_cmp_desc"));

						$this->cur_sort_by = "changed";
						$this->cur_sort_as = "desc";
					}
					else
					{
						@rsort($dirs);
						@usort($files, array($this, "changed_cmp_asc"));

						$this->cur_sort_by = "changed";
						$this->cur_sort_as = "asc";
					}
				}
			}
		}

		// ***********************************************************************************************************************
		// Start of HTML
		// ***********************************************************************************************************************

		$text = "";

		if ($description)
		{
			$text = "<b>$description</b>";
		}

		// this is for file filtering
		if ($this->filter_list_allow)
		{
			$clear_current_filter_list_td = isset($_COOKIE['current_filter_list']) && strlen($_COOKIE['current_filter_list']) ?
			       	"<td class='jsmallfibicon'><a href='".$this->baselink."&dir=".urlencode($masked_dir)."&current_filter_list='><img src='".$this->imgdir."delete.png' title='".JText::_('clear_current_filter_list')."'></a></td>" : "";

			$filter_list_tr = $file_filter_pattern_required ? "<tr class='jsline'><td colspan='6'><img src=\"".$this->imgdir."null.gif\" /></td></tr>" : "";
			$filter_list_tr .= "<tr ".($file_filter_pattern_required ? "class='row highlighted' style='border-style:solid'" : "").">"
				."<form action='".$this->baselink."&dir=".urlencode($masked_dir)."' method='post'>"
				."<td colspan='6'>"
				."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
				."<tr height='30' valign='middle'>"
					."<td align='right' title=\"".JText::_('set_filter_list')."\">".JText::_('set_filter_list_label')."&nbsp;</td>"
					."<td width='".($this->filter_list_width)."' align='right'>"
						."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
						."<tr>"
							."<td>"
							."<input class='filter_text_input' name='current_filter_list' type='text' value=\"".(isset($_COOKIE['current_filter_list']) ? $_COOKIE['current_filter_list'] : "")."\" />"
							."</td>"
							."<td class='jsmallfibicon'>"
							."<input type='image' src=\"".$this->imgdir."tick.png\" title=\"".JText::_('set_filter_list')."\" />"
							."</td>"
							.$clear_current_filter_list_td
						."</tr>"
						."</table>"
					."</td>"
				."</tr>"
				."</table>"
				."</td>"
				."</form>"
				."</tr>";
			$filter_list_tr .= $file_filter_pattern_required ? "<tr class='jsline'><td colspan='6'><img src=\"".$this->imgdir."null.gif\" /></td></tr>"
					."<tr><td colspan='6'><img src=\"".$this->imgdir."null.gif\" height=20 /></td></tr>" : "";
		}
		else
		{
			$filter_list_tr = "";
		}

		// Print the error (if there is something to print)
		if ($error) {

			$text .= "<div id='error'>"
				."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
				."<tr height='30' valign='middle'>"
				."<td width='60px' align='center'><img src=\"".$this->imgdir."warning.png\"></td><td><br />".$error."</td>"
				."</tr>"
				."</table>"
				."</div>";
		}
		if ($success) {

			$text .= "<div id='success'>"
				."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
				."<tr height='30' valign='middle'>"
				."<td width='60px' align='center'><img src=\"".$this->imgdir."success.png\"></td><td><br />".$success."</td>"
				."</tr>"
				."</table>"
				."</div>";
		}

		// logs / help area
		$show_help_link = $this->_params->def('show_help_link', "1");
		$logs_link = $access_rights > 4 && ($log_uploads || $log_downloads) ? "<a href='".$this->baselink."&dir=".urlencode($masked_dir)."&view_log=1'>".JText::_('view_log')."</a>" : "";
		if ($show_help_link) 
		{
			$help_link = ($logs_link ? "&nbsp;|&nbsp;" : "")."<a href='".$this->baselink."&dir=".urlencode($masked_dir)."&help=1'>".JText::_('help')."</a>";
		}
		else
		{
			$help_link = "";
		}

		$links_string = $logs_link.$help_link;

		$browsing_text = ($is_current_position_an_archive ? JText::_('archive_folder_for') : JText::_('browsing'));

		if (isset($userbound_repository_with_id) && isset($userbound_repository_with_name)) 
		{
			$current_position_links = (str_replace($userbound_repository_with_id, $userbound_repository_with_name, $current_position_links));
		}
		else if (isset($groupbound_repository_with_id) && isset($groupbound_repository_with_name)) 
		{
			$current_position_links = (str_replace($groupbound_repository_with_id, $groupbound_repository_with_name, $current_position_links));
		}

		$text .= "<div id='topinfo'>"
			."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
			."<tr valign='bottom'>"
		        // Inserted browsing info here instead of in bottom info /ErikLtz
			//."	<td width='10' valign='middle'><img src=\"".$this->imgdir."arrow_right.png\" /></td>"
			."	<td>".$browsing_text.": ".$this->chosen_encoding($current_position_links)."</td>"
			//."	<td>".$browsing_text.": ".$this->chosen_encoding(str_replace("Personal area for user ID ".$userid, "Personal area for ".$username, $current_position_links))."</td>"
			."	<td class='links'>".$links_string."</td>"
			."</tr>"
			."</table>"
			."</div>";

		// start frame area
		$text .= "<div id='frame'>";
		
		// start files/folders table
		$text .= "<table class='table' border='0' cellpadding='0' cellspacing='0'>"
			.$filter_list_tr
			."<tr class='row header'>"
			."	<td class='jsmallfibicon'>";
		
		if($upper_dir)
		{
			// note: upper_dir has the absolute path masked
			$text .= "<a href='".$this->baselink."&dir=".urlencode($upper_dir)."'><img title=\"".JText::_('go_to_previous_folder')."\" src=\"".$this->imgdir."upperdir.png\" /></a>";
		}
		else
		{
			$text .= "<img src=\"".$this->imgdir."null.gif\" />";
		}
		
		$text .= "	</td>"
			."	<td class='filename'>"
				.$this->makeArrow((isset($_GET["sort_by"]) ? $_GET["sort_by"] : ""), (isset($_GET["sort_as"]) ? $_GET["sort_as"] : ""), "name", $masked_dir, JText::_('file_name'))
			."	</td>"
			."	<td class='size'>"
				.$this->makeArrow((isset($_GET["sort_by"]) ? $_GET["sort_by"] : ""), (isset($_GET["sort_as"]) ? $_GET["sort_as"] : ""), "size", $masked_dir, JText::_('size'))	
			."	</td>"
			."	<td class='changed'>"
				.$this->makeArrow((isset($_GET["sort_by"]) ? $_GET["sort_by"] : ""), (isset($_GET["sort_as"]) ? $_GET["sort_as"] : ""), "changed", $masked_dir, JText::_('last_changed'))
			."	</td>";

		if($access_rights > 2)
		{
			$text .= "<td colspan='2' width='60'>&nbsp;</td>";
		}
		else
		{
			$text .= "<td width='60'>&nbsp;</td>";
		}
		$text .= "</tr>";

		// Ready to display folders and files.
		$row = 1;

		// Folders first
		if ($dirs)
		{
			foreach ($dirs as $a_dir)
			{
				$row_style = ($row ? "one" : "two");
				
				if ($this->line_height)
				{
					$text .= "<tr class='jsline'><td colspan='6'><img src=\"".$this->imgdir."null.gif\" /></td></tr>";
				}

				// different line if editing name or not
				if (isset($_GET['old_foldername']) && strlen($_GET['old_foldername']) && !strcmp($_GET['old_foldername'], $a_dir))   // Removed urldecode on _GET /ErikLtz
				{
					$text .= "<form action='".$this->baselink."&dir=".urlencode($masked_dir)."' method='post'>"
						."<tr class='row $row_style'>"
						."	<td class='jsmallfibicon'>"
						."	<img src=\"".$this->imgdir."folder.png\" />"
						."	</td>"
						."	<td>"
						."	<input class='text_input' name='new_foldername' type='text' value=\"".$this->chosen_encoding($a_dir)."\" />"
						."	</td>"
						."	<td class='size'><img src=\"".$this->imgdir."null.gif\" /></td>"
						."	<td class='changed'><img src=\"".$this->imgdir."null.gif\" /></td>"
						."	<td class='jsmallfibicon'>"
						."	<input type='image' src=\"".$this->imgdir."tick.png\" title=\"".JText::_('rename_folder_title')."\" />"
						."	</td>"
						."	<td class='jsmallfibicon'><a href='".$this->baselink."&dir=".urlencode($masked_dir)."'>".JText::_('rename_folder_cancel')."</a></td>"
						."</tr>"
						."	<input type='hidden' name='old_foldername' value=\"".urlencode($a_dir)."\" />"
						."</form>";
				}
				else
				{
					$text .= "<tr class='row $row_style' onmouseover='this.className=\"highlighted\"' onmouseout='this.className=\"row $row_style\"'>"
						."	<td class='jsmallfibicon'>"
						."	<img src=\"".$this->imgdir."folder.png\" />"
						."	</td>"
						."	<td>"
						."	<a href='".$this->baselink."&dir=".urlencode($masked_dir).DS.urlencode($a_dir)."'>".$this->chosen_encoding($a_dir)."</a>"
						."	</td>"
						."	<td class='size'><img src=\"".$this->imgdir."null.gif\" /></td>"
						."	<td class='changed'><img src=\"".$this->imgdir."null.gif\" /></td>";
					if($access_rights > 1)
					{
						$text .= "<td class='jsmallfibicon'>"
							."<a href='".$this->baselink."&dir=".urlencode($masked_dir)."&old_foldername=".urlencode($a_dir)."'>"
							."<img src=\"".$this->imgdir."rename.png\" border='0' title=\"".JText::sprintf('folder_rename', $this->chosen_encoding($a_dir))."\" /></a>"
							."</td>";
					}
					else
					{
						$text .= "<td>&nbsp;</td>";
					}
					if($access_rights > 3)
					{
						// we need to utf-8 encode potential special characters to be passed to javascript, because Firefox does not handle this (it works in IE)
						$text .= "<td class='jsmallfibicon'>"
							."<a href=\"javascript:confirmDelfolder('".addslashes($this->baselink)."','".urlencode(addslashes($this->chosen_encoding($masked_dir)))."','".urlencode(addslashes($this->chosen_encoding($a_dir)))."','".addslashes(JText::sprintf('about_to_remove_folder', $this->chosen_encoding(addslashes($a_dir))))."')\">"
							."<img src=\"".$this->imgdir."delete.png\" border='0' title=\"".JText::sprintf('remove_folder', $this->chosen_encoding($a_dir))."\" /></a>"
							."</td>";
					}
					else
					{
						$text .= "<td>&nbsp;</td>";
					}
					$text .= "</tr>";
				}
				$row =! $row;
			}
		}

		// Now the files
		if($files)
		{
			foreach ($files as $a_file)
			{
				$row_style = ($row ? "one" : "two");

				if ($this->line_height)
				{
					$text .= "<tr class='jsline'><td colspan='6'><img src=\"".$this->imgdir."null.gif\" /></td></tr>";
				}

				// makeThumbnail will only make a new thumbnail if required, and will return one if the right thumbnail is available
				if ($is_path_relative && $this->makeThumbnail($a_file["name"], $a_file["extension"], $dir, $relative_dir, $this->thumbsize, $this->thumbsize))
				{
					$file_icon_td_begin	= "<td class='jsthumb'>";
					$file_icon_image	= "<img src=\"".$this->makeForwardSlashes($this->chosen_encoding($relative_dir."/"."JS_THUMBS"."/".$a_file["name"]))."\" border='0' />";
					$file_icon_td_end	= "</td>";
				}
				else
				{
					$file_icon_td_begin	= "<td class='jsmallfibicon'>";
					$file_icon_image	= "<img src=\"".$this->fileIcon($a_file["extension"])."\" />";
					$file_icon_td_end	= "</td>";
				}

				// different line if editing name or not
				if (isset($_GET['old_filename']) && strlen($_GET['old_filename']) && !strcmp($_GET['old_filename'], $a_file["name"]))   // Removed urldecode on _GET /ErikLtz
				{
					$text .= "<form action='".$this->baselink."&dir=".urlencode($masked_dir)."' method='post'>"
						."<tr class='row $row_style'>"
						.	$file_icon_td_begin.$file_icon_image.$file_icon_td_end
						."	<td>"
						."	<input class='text_input' name='new_filename' type='text' value=\"".$this->chosen_encoding($a_file["name"])."\" />"
						."	</td>"
						."	<td class='size'>"
							.$this->fileSizeF($a_file["size"])
						."	</td>"
						."	<td class='changed'>"
							.$this->fileChanged($a_file["changed"])
						."	</td>"
						."	<td class='jsmallfibicon'>"
						."	<input type='image' src=\"".$this->imgdir."tick.png\" title=\"".JText::_('rename_file_title')."\" />"
						."	</td>"
						."	<td class='jsmallfibicon'><a href='".$this->baselink."&dir=".urlencode($masked_dir)."'>".JText::_('rename_file_cancel')."</a></td>"
						."</tr>"
						."	<input type='hidden' name='old_filename' value=\"".urlencode($a_file["name"])."\" />"
						."</form>";
				}
				else
				{
					if($access_rights == 1 && $disable_level_1_downloads)
					{
						$file_link = $this->chosen_encoding($a_file["name"]);
						$file_link_a_tag_begin = "";
						$file_link_a_tag_end = "";
					}
					else
					{
						// now uses absolute path in download file (relative path returns false to file_exists() on certain unix configurations 

						// set the <a href...> tag for the file link (depends on the linking method, direct or through the open/download box)
						if ($is_path_relative && $is_direct_link_to_files)
						{
							$file_link_a_tag_begin = "<a href=\"".$this->makeForwardSlashes($this->chosen_encoding($relative_dir.DS.$a_file["name"]))."\" ".($is_direct_link_to_files > 1 ? "target='_blank'" : "").">";
						}
						else
						{
							$file_link_a_tag_begin = "<a href='".$this->baselink."&dir=".urlencode($masked_dir)."&download_file=".urlencode($masked_dir.DS.$a_file["name"])."'>";
						}
						$file_link_a_tag_end = "</a>";

						// display normal open/download link if either outside of an archive or inside, but with no right to restore a file
						if (!$is_current_position_an_archive || $access_rights <= 2)
						{
							// normal link
							$file_link = $file_link_a_tag_begin.$this->chosen_encoding($a_file["name"]).$file_link_a_tag_end;
						}
						else
						{
							// link in case of an archived file
							$file_link = "<br />".$this->chosen_encoding($a_file["name"])
								    ."<br />"
								    .$file_link_a_tag_begin.JText::_('download_or_open_file').$file_link_a_tag_end;

							if ($access_rights > 2)
							{
								$file_link .= "&nbsp;|&nbsp;<a href=\"javascript:confirmRestoreFile('".addslashes($this->baselink)."','"
									.urlencode(addslashes($this->chosen_encoding($masked_dir)))."','".urlencode(addslashes($this->chosen_encoding($a_file["name"])))."','"
									.addslashes(JText::sprintf('about_to_restore_archived_file', $this->chosen_encoding(addslashes($a_file["name"]))))."')\">"
									.JText::_('restore_archived_file')."</a>"
									."<br /><br />";
							}
						}
					}

					$text .= "<tr class='row $row_style' onmouseover='this.className=\"highlighted\"' onmouseout='this.className=\"row $row_style\"'>"
						.$file_icon_td_begin.$file_link_a_tag_begin.$file_icon_image.$file_link_a_tag_end.$file_icon_td_end
						."	<td>"
						.$file_link
						."	</td>"
						."	<td class='size'>"
							.$this->fileSizeF($a_file["size"])
						."	</td>"
						."	<td class='changed'>"
							.$this->fileChanged($a_file["changed"])
						."	</td>";
					if($access_rights > 1)
					{
						$text .= "<td class='jsmallfibicon'>"
							."<a href='".$this->baselink."&dir=".urlencode($masked_dir)."&old_filename=".urlencode($a_file["name"])."'>"
							."<img src=\"".$this->imgdir."rename.png\" border='0' title=\"".JText::sprintf('file_rename', $this->chosen_encoding($a_file["name"]))."\" /></a>"
							."</td>";
					}
					else
					{
						$text .= "<td>&nbsp;</td>";
					}
					if($access_rights > 2)
					{
						// we need to utf-8 encode potential special characters to be passed to javascript, because Firefox does not handle this (it works in IE)
						$text .= "<td class='jsmallfibicon'>"
							."<a href=\"javascript:confirmDelfile('".addslashes($this->baselink)."','".urlencode(addslashes($this->chosen_encoding($masked_dir)))."','".urlencode(addslashes($this->chosen_encoding($a_file["name"])))."','".addslashes(JText::sprintf('about_to_remove_file', $this->chosen_encoding(addslashes($a_file["name"]))))."')\">"
							."<img src=\"".$this->imgdir."delete.png\" border='0' title=\"".JText::sprintf('remove_file', $this->chosen_encoding($a_file["name"]))."\" /></a>"
							."</td>";
					}
					else
					{
						$text .= "<td>&nbsp;</td>";
					}
					$text .= "</tr>";
				}
				$row =! $row;
			}
		}

		// Closing files/folders table and frame div
		$text .= "</table>"
			."</div>";

		// archive link section
		if (is_dir($dir.DS."JS_ARCHIVE"))
		{
			$text .= "<img src=\"".$this->imgdir."null.gif\" height=10 />";

			$text .= "<div id='upload'>"
				."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
				."<tr height='25'>"
				//."	<td class='jsmallfibicon'>"
				//."	&nbsp;<img src=\"".$this->imgdir."archive.png\" />"
				//."	</td>"
				."	<td align='left'>"
				."	&nbsp;&nbsp;&nbsp;<a href='".$this->baselink."&dir=".urlencode($masked_dir).DS."JS_ARCHIVE'>".JText::_('view_archive_folder')."</a>"
				."	</td>";

			/* archive folder now deletes itself when deleting last file
			if($access_rights > 3)
			{
				// we need to utf-8 encode potential special characters to be passed to javascript, because Firefox does not handle this (it works in IE)
				$text .= "<td class='jsmallfibicon' align='right'>"
					."<a href=\"javascript:confirmDelfolder('".addslashes($this->baselink)."','".urlencode(addslashes($this->chosen_encoding($masked_dir)))."','JS_ARCHIVE','"
					.addslashes(JText::_('about_to_remove_archive_folder'))."')\"><img src=\"".$this->imgdir."delete.png\" border='0' title=\"".JText::_('remove_archive_folder')."\" /></a>"
					."&nbsp;&nbsp;&nbsp;</td>";
			}
			else
			{
				$text .= "<td>&nbsp;</td>";
			}
			*/

			$text .= "</tr>"
				."</table>"
				."</div>";
		}

		// ***********************************************************************************************************************
		// UPLOAD display_actions
		// ***********************************************************************************************************************

		$allow_upload_box_hiding = $this->_params->def('allow_upload_box_hiding', 1);

		if ($access_rights > 1 && strcmp($this->baseName($dir), "JS_ARCHIVE") && (!$allow_upload_box_hiding || (isset($_COOKIE['display_actions']) && !strcmp($_COOKIE['display_actions'], "UPLOAD"))))
		{
			$split_upload_section = $this->_params->def('split_upload_section', 0);

			$upload_type = "HTMLUPLOAD";

			$text .= "<img src=\"".$this->imgdir."null.gif\" height=10 />";

			$text .= "<div id='upload'>"
				."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
				."<tr height='20' valign='middle'>"
				."	<td>"
				."	<form style='display:inline; margin: 0px; padding: 0px;' enctype='multipart/form-data' action='".$this->baselink."&dir=".urlencode($masked_dir)."' method='post'>"
				."	<table cellspacing='0' cellpadding='0' border='0'>"
				."	<tr>"
				."		<td>"
						.JText::_('create_new_folder').":&nbsp;"
				."		</td>"
				."		<td>"
				."		<input class='text' name='userdir' type='text' />"
				."		</td>"
				."		<td class='jsimage'>"
				."		<input type='image' src=\"".$this->imgdir."addfolder.png\" title=\"".JText::_('add_folder')."\" />"
				."		</td>"
				."	</tr>"
				."	</table>"
				."	</form>"
				."	</td>";

			if ($split_upload_section)
			{
				$text .= "</tr>"
					."</table>"
					."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
					."<tr height='20' valign='middle'>";
			}

			$text .= "	</td>"
				."	<td align='right'>"
				."	<form name='uploadForm' style='display:inline; margin: 0px; padding: 0px;' enctype='multipart/form-data' action='".$this->baselink."&dir=".urlencode($masked_dir)."' method='post'>"
				."	<table cellspacing='0' cellpadding='0' border='0'>"
				."	<tr>"
				."		<td>";

				$text	.= 	JText::_('upload_file').":&nbsp;"
					."	</td>"
					."	<td>"
					."	<input class=\"file\" name=\"userfile\" type=\"file\" />"
					."	</td>"
					."	<td class='jsimage'>"
					."	<input type='image' src=\"".$this->imgdir."addfile.png\" title=\"".JText::_('upload_file')."\" />"
					."	<input type='hidden' name='upload_dir' value=\"".urlencode($masked_dir)."\">";
			$text .= "		</td>"
				."	</tr>"
				."	</table>"
				."	</form>"
				."	</td>"
				."</tr>"
				."</table>"
				."</div>";

		}

		// small icon with link to site and title containing copyright and version number
		
			$credits_icon = "<td width='12' align='right'><a href='http://www.jsmallsoftware.com' target='_blank'>"
					."<img src=\"".$this->imgdir."jsmallsoftware.png\" border='0' title=\"".JText::sprintf('short_credits', $version_number)."\" /></a>"
					."</td>";
		// set display actions link(s): distinguish case of cookie set (the cookie is the same, so only one box is open at any one time) or not set
		if (isset($_COOKIE['display_actions']))
		{
			// for the upload box (not allowed in archive folder)
			if ($allow_upload_box_hiding && strcmp($this->baseName($dir), "JS_ARCHIVE"))
			{
		       		if ($access_rights > 1 && !strcmp($_COOKIE['display_actions'], "UPLOAD"))
				{
					$upload_actions_icon = "<td class='jsmallfibicon' width='20'><a href='".$this->baselink."&dir=".urlencode($masked_dir)."&set_display_actions_cookie=NO_ACTION' "
						."title=\"".JText::_('close_upload_actions_area')."\"><img src=\"".$this->imgdir."minus.png\" border='0'></a></td>";
				}
				else if ($access_rights > 1)
				{
					$upload_actions_icon = "<td class='jsmallfibicon' width='20'><a href='".$this->baselink."&dir=".urlencode($masked_dir)."&set_display_actions_cookie=UPLOAD' "
						."title=\"".JText::_('open_upload_actions_area')."\"><img src=\"".$this->imgdir."plus.png\" border='0'></a></td>";
				}
				else
				{
					$upload_actions_icon = "<td class='jsmallfibicon' width='20'><img src=\"".$this->imgdir."null.gif\"></td>";
				}
			}
			else
			{
				$upload_actions_icon = "<td class='jsmallfibicon' width='20'><img src=\"".$this->imgdir."null.gif\"></td>";
			}
		}
		else
		{
			// for the upload box (not allowed in archive folder)
			if ($allow_upload_box_hiding && strcmp($this->baseName($dir), "JS_ARCHIVE"))
			{
				$upload_actions_icon = "<td class='jsmallfibicon' width='20'><a href='".$this->baselink."&dir=".urlencode($masked_dir)."&set_display_actions_cookie=UPLOAD' "
					."title=\"".JText::_('open_upload_actions_area')."\"><img src=\"".$this->imgdir."plus.png\"></a></td>";
			}
			else
			{
				$upload_actions_icon = "<td class='jsmallfibicon' width='20'><img src=\"".$this->imgdir."null.gif\"></td>";
			}
		}

		// Bottom line

		$text .= "<div id='bottominfo'>"
			."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
			."<tr>"
			.$upload_actions_icon
			."	<td>&nbsp;</td>"
			.$credits_icon
			."</tr>"
			."</table>"
			."</div>";

		// ***********************************************************************************************************************
		// End of HTML - Now set article data
		// ***********************************************************************************************************************

		$article->text = $article->fulltext = $article->introtext = $text_array[0].$text.$text_array[1];

		$params->set('show_author', '0');
		$params->set('show_create_date', '0');
		$params->set('show_modify_date', '0');

	} // end of onPrepareContent method

	// ***********************************************************************************************************************
	// Other functions
	// ***********************************************************************************************************************

	function view_log($logfile_prefix, &$article, &$params, $description, $masked_dir, $log_uploads, $log_downloads, $log_removedfolders, $log_removedfiles, $log_restoredfiles, $log_newfolders, $log_newfoldernames, $log_newfilenames)
	{
		$color = $this->_params->def('log_highlighted_color', "FF6600");

		$text = "";
		
		if ($description) {
			$text = "<b>$description</b>";
		}

		// title
		$text .= "<br /><br /><br /><div id='info'>"
			."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
			."<tr height='30' valign='top'>"
			."<td colspan='2'><b>".JText::_('log_title')."</b></td>"
			."</tr>";

		// uploads
		if ($log_uploads) {

			$logfile = $logfile_prefix."uploads.txt";
			$logtext = @file_get_contents($logfile);

			if (!$logtext)
			{
				$logtext = JText::_('no_log_found');
				$icon = $this->imgdir."log_not_found.png";
			}
			else {
				$logtext = preg_replace("/\\n/", "<hr />", $logtext);
				$logtext = preg_replace("/\[/", "<font color='$color'>", $logtext);
				$logtext = preg_replace("/\]/", "</font>", $logtext);
				$icon = $this->imgdir."log_found.png";
			}
		}
		else {
			$logtext = JText::_('not_logging');
			$icon = $this->imgdir."log_disabled.png";
		}
		$text .= "<tr height='30' valign='middle'>"
			."<td colspan='2'><b>".JText::_('log_uploads_title')."</b></td>"
			."</tr>"
			."<tr valign='top'>"
			."<td class='jsmallfibicon'><img src=\"$icon\"></td><td>$logtext</td>"
			."</tr>"
			."<tr height='30' valign='middle'>"
			."<td colspan='2'><img src=\"".$this->imgdir."null.gif\"></td>"
			."</tr>";

		// downloads
		if ($log_downloads) {

			$logfile = $logfile_prefix."downloads.txt";
			$logtext = @file_get_contents($logfile);

			if (!$logtext)
			{
				$logtext = JText::_('no_log_found');
				$icon = $this->imgdir."log_not_found.png";
			}
			else {
				$logtext = preg_replace("/\\n/", "<hr />", $logtext);
				$logtext = preg_replace("/\[/", "<font color='$color'>", $logtext);
				$logtext = preg_replace("/\]/", "</font>", $logtext);
				$icon = $this->imgdir."log_found.png";
			}
		}
		else {
			$logtext = JText::_('not_logging');
			$icon = $this->imgdir."log_disabled.png";
		}
		$text .= "<tr height='30' valign='middle'>"
			."<td colspan='2'><b>".JText::_('log_downloads_title')."</b></td>"
			."</tr>"
			."<tr valign='top'>"
			."<td class='jsmallfibicon'><img src=\"$icon\"></td><td>$logtext</td>"
			."</tr>"
			."<tr height='30' valign='middle'>"
			."<td colspan='2'><img src=\"".$this->imgdir."null.gif\"></td>"
			."</tr>";

		// removed folders
		if ($log_removedfolders) {

			$logfile = $logfile_prefix."removedfolders.txt";
			$logtext = @file_get_contents($logfile);

			if (!$logtext)
			{
				$logtext = JText::_('no_log_found');
				$icon = $this->imgdir."log_not_found.png";
			}
			else {
				$logtext = preg_replace("/\\n/", "<hr />", $logtext);
				$logtext = preg_replace("/\[/", "<font color='$color'>", $logtext);
				$logtext = preg_replace("/\]/", "</font>", $logtext);
				$icon = $this->imgdir."log_found.png";
			}
		}
		else {
			$logtext = JText::_('not_logging');
			$icon = $this->imgdir."log_disabled.png";
		}
		$text .= "<tr height='30' valign='middle'>"
			."<td colspan='2'><b>".JText::_('log_removedfolders_title')."</b></td>"
			."</tr>"
			."<tr valign='top'>"
			."<td class='jsmallfibicon'><img src=\"$icon\"></td><td>$logtext</td>"
			."</tr>"
			."<tr height='30' valign='middle'>"
			."<td colspan='2'><img src=\"".$this->imgdir."null.gif\"></td>"
			."</tr>";

		// removed files
		if ($log_removedfiles) {

			$logfile = $logfile_prefix."removedfiles.txt";
			$logtext = @file_get_contents($logfile);

			if (!$logtext)
			{
				$logtext = JText::_('no_log_found');
				$icon = $this->imgdir."log_not_found.png";
			}
			else {
				$logtext = preg_replace("/\\n/", "<hr />", $logtext);
				$logtext = preg_replace("/\[/", "<font color='$color'>", $logtext);
				$logtext = preg_replace("/\]/", "</font>", $logtext);
				$icon = $this->imgdir."log_found.png";
			}
		}
		else {
			$logtext = JText::_('not_logging');
			$icon = $this->imgdir."log_disabled.png";
		}
		$text .= "<tr height='30' valign='middle'>"
			."<td colspan='2'><b>".JText::_('log_removedfiles_title')."</b></td>"
			."</tr>"
			."<tr valign='top'>"
			."<td class='jsmallfibicon'><img src=\"$icon\"></td><td>$logtext</td>"
			."</tr>"
			."<tr height='30' valign='middle'>"
			."<td colspan='2'><img src=\"".$this->imgdir."null.gif\"></td>"
			."</tr>";

		// restored files
		if ($log_restoredfiles) {

			$logfile = $logfile_prefix."restoredfiles.txt";
			$logtext = @file_get_contents($logfile);

			if (!$logtext)
			{
				$logtext = JText::_('no_log_found');
				$icon = $this->imgdir."log_not_found.png";
			}
			else {
				$logtext = preg_replace("/\\n/", "<hr />", $logtext);
				$logtext = preg_replace("/\[/", "<font color='$color'>", $logtext);
				$logtext = preg_replace("/\]/", "</font>", $logtext);
				$icon = $this->imgdir."log_found.png";
			}
		}
		else {
			$logtext = JText::_('not_logging');
			$icon = $this->imgdir."log_disabled.png";
		}
		$text .= "<tr height='30' valign='middle'>"
			."<td colspan='2'><b>".JText::_('log_restoredfiles_title')."</b></td>"
			."</tr>"
			."<tr valign='top'>"
			."<td class='jsmallfibicon'><img src=\"$icon\"></td><td>$logtext</td>"
			."</tr>"
			."<tr height='30' valign='middle'>"
			."<td colspan='2'><img src=\"".$this->imgdir."null.gif\"></td>"
			."</tr>";

		// new folders
		if ($log_newfolders) {

			$logfile = $logfile_prefix."newfolders.txt";
			$logtext = @file_get_contents($logfile);

			if (!$logtext)
			{
				$logtext = JText::_('no_log_found');
				$icon = $this->imgdir."log_not_found.png";
			}
			else {
				$logtext = preg_replace("/\\n/", "<hr />", $logtext);
				$logtext = preg_replace("/\[/", "<font color='$color'>", $logtext);
				$logtext = preg_replace("/\]/", "</font>", $logtext);
				$icon = $this->imgdir."log_found.png";
			}
		}
		else {
			$logtext = JText::_('not_logging');
			$icon = $this->imgdir."log_disabled.png";
		}
		$text .= "<tr height='30' valign='middle'>"
			."<td colspan='2'><b>".JText::_('log_newfolders_title')."</b></td>"
			."</tr>"
			."<tr valign='top'>"
			."<td class='jsmallfibicon'><img src=\"$icon\"></td><td>$logtext</td>"
			."</tr>"
			."<tr height='30' valign='middle'>"
			."<td colspan='2'><img src=\"".$this->imgdir."null.gif\"></td>"
			."</tr>";

		// renamed folders
		if ($log_newfoldernames) {

			$logfile = $logfile_prefix."newfoldernames.txt";
			$logtext = @file_get_contents($logfile);

			if (!$logtext)
			{
				$logtext = JText::_('no_log_found');
				$icon = $this->imgdir."log_not_found.png";
			}
			else {
				$logtext = preg_replace("/\\n/", "<hr />", $logtext);
				$logtext = preg_replace("/\[/", "<font color='$color'>", $logtext);
				$logtext = preg_replace("/\]/", "</font>", $logtext);
				$icon = $this->imgdir."log_found.png";
			}
		}
		else {
			$logtext = JText::_('not_logging');
			$icon = $this->imgdir."log_disabled.png";
		}
		$text .= "<tr height='30' valign='middle'>"
			."<td colspan='2'><b>".JText::_('log_newfoldernames_title')."</b></td>"
			."</tr>"
			."<tr valign='top'>"
			."<td class='jsmallfibicon'><img src=\"$icon\"></td><td>$logtext</td>"
			."</tr>"
			."<tr height='30' valign='middle'>"
			."<td colspan='2'><img src=\"".$this->imgdir."null.gif\"></td>"
			."</tr>";

		// renamed files
		if ($log_newfilenames) {

			$logfile = $logfile_prefix."newfilenames.txt";
			$logtext = @file_get_contents($logfile);

			if (!$logtext)
			{
				$logtext = JText::_('no_log_found');
				$icon = $this->imgdir."log_not_found.png";
			}
			else {
				$logtext = preg_replace("/\\n/", "<hr />", $logtext);
				$logtext = preg_replace("/\[/", "<font color='$color'>", $logtext);
				$logtext = preg_replace("/\]/", "</font>", $logtext);
				$icon = $this->imgdir."log_found.png";
			}
		}
		else {
			$logtext = JText::_('not_logging');
			$icon = $this->imgdir."log_disabled.png";
		}
		$text .= "<tr height='30' valign='middle'>"
			."<td colspan='2'><b>".JText::_('log_newfilenames_title')."</b></td>"
			."</tr>"
			."<tr valign='top'>"
			."<td class='jsmallfibicon'><img src=\"$icon\"></td><td>$logtext</td>"
			."</tr>";

		// final link
		$text .= "<tr height='60' valign='middle'>"
			."<td class='jsmallfibicon'><img src=\"".$this->imgdir."null.gif\"></td><td><a href='".$this->baselink."&dir=".urlencode($masked_dir)."'>".JText::_('go_back')."</a></td>"
			."</tr>"
			."</table>"
			."</div>";

		$article->text = $article->fulltext = $article->introtext = $text;

		$params->set('show_author', '0');
		$params->set('show_create_date', '0');
		$params->set('show_modify_date', '0');
		return;
	}

	function email_log($log_text)
	{
		$log_email_from_string	= $this->_params->def('log_email_from_string', "Jsmallfib Log Alert");
		$log_email_from		= $this->_params->def('log_email_from', "");
		$log_email_to		= $this->_params->def('log_email_to', "");
		$log_email_subject	= $this->_params->def('log_email_subject', "Jsmallfib Log Alert");

		if ($log_email_from && $log_email_to)
		{
			/*
			$header = "From: $log_email_from_string <$log_email_from>\r\n";
			//ini_set ('sendmail_from', $log_email_from); 
			mail($log_email_to, $log_email_subject, $log_text, $header);
			 */
			$mailer =& JFactory::getMailer();

			$mailer->setSender(array($log_email_from, $log_email_from_string));
			$mailer->addRecipient($log_email_to);
			$mailer->setSubject($log_email_subject);
			$mailer->setBody($log_text);

			if ($mailer->Send() !== true)
			{
				return 1;	// error
			}
			else {
				return 0;	// OK
			}
		}
	}

	function do_help(&$article, &$params, $description, $masked_dir)
	{
		$text = "";

		if ($description) {
			$text = "<b>$description</b>";
		}

		$helptitle = JText::_('help');
		$helptext  = JText::_('jsmallfibplugindesc');
		$helptext  = preg_replace("/\.\.\/plugins/", "plugins", $helptext);

		$text .= "<br /><br /><br /><div id='info'>"
			."<table cellspacing='0' cellpadding='0' border='0' width='100%'>"
			."<tr height='30' valign='top'>"
			."<td colspan='2'><b>$helptitle</b></td>"
			."</tr>"
			."<tr height='30' valign='middle'>"
			."<td width='60px'><img src=\"".$this->imgdir."null.gif\"></td><td>$helptext</td>"
			."</tr>"
			."<tr height='30' valign='middle'>"
			."<td width='60px'><img src=\"".$this->imgdir."null.gif\"></td><td><a href='".$this->baselink."&dir=".urlencode($masked_dir)."'>".JText::_('go_back')."</a></td>"
			."</tr>"
			."</table>"
			."</div>";

		$article->text = $article->fulltext = $article->introtext = $text;

		$params->set('show_author', '0');
		$params->set('show_create_date', '0');
		$params->set('show_modify_date', '0');
		return;
	}

	// ***********************************************************************************************************************
	// Javascript and Cascading Style Sheets used locally, and other functions
	// ***********************************************************************************************************************

	function do_js()
	{
		$js = "function confirmDelfolder(baselink, dir, delfolder, msgString) {"

			."	var browser=navigator.appName;"
			."	var b_version=navigator.appVersion;"
			."	var version=parseFloat(b_version);"

			."	if (confirm(msgString)) {"

				."	if (browser=='Netscape')"
				."	{"
					."	window.location=baselink+'&dir='+escape(encodeURI(dir))+'&delfolder='+escape(encodeURI(delfolder));" // Firefox
				."	}"
				."	else if (browser=='Microsoft Internet Explorer')"
				."	{"
					."	window.location=baselink+'&dir='+escape(dir)+'&delfolder='+escape(delfolder);" // IE
				."	}"
				."	else"
				."	{"
					."	window.location=baselink+'&dir='+escape(dir)+'&delfolder='+escape(delfolder);" // treat others like IE
				."	}"
				."	return;"
			."	}"
		."	}"
		
		."	function confirmDelfile(baselink, dir, delfile, msgString) {"

			."	var browser=navigator.appName;"
			."	var b_version=navigator.appVersion;"
			."	var version=parseFloat(b_version);"

			."	if (confirm(msgString)) {"

				."	if (browser=='Netscape')"
				."	{"
					."	window.location=baselink+'&dir='+escape(encodeURI(dir))+'&delfile='+escape(encodeURI(delfile));" // Firefox
				."	}"
				."	else if (browser=='Microsoft Internet Explorer')"
				."	{"
					."	window.location=baselink+'&dir='+escape(dir)+'&delfile='+escape(delfile);" // IE
				."	}"
				."	else"
				."	{"
					."	window.location=baselink+'&dir='+escape(dir)+'&delfile='+escape(delfile);" // treat others like IE
				."	}"
				."	return;"
			."	}"
		."	}"
		
		."	function confirmRestoreFile(baselink, dir, restorefile, msgString) {"

			."	var browser=navigator.appName;"
			."	var b_version=navigator.appVersion;"
			."	var version=parseFloat(b_version);"

			."	if (confirm(msgString)) {"

				."	if (browser=='Netscape')"
				."	{"
					."	window.location=baselink+'&dir='+escape(encodeURI(dir))+'&restorefile='+escape(encodeURI(restorefile));" // Firefox
				."	}"
				."	else if (browser=='Microsoft Internet Explorer')"
				."	{"
					."	window.location=baselink+'&dir='+escape(dir)+'&restorefile='+escape(restorefile);" // IE
				."	}"
				."	else"
				."	{"
					."	window.location=baselink+'&dir='+escape(dir)+'&restorefile='+escape(restorefile);" // treat others like IE
				."	}"
				."	return;"
			."	}"
		."	}";
		
		return ($js);
	}

	function do_css()
	{
		$css = ""

			."#frame {"
			."	width:".$this->table_width."px;"
			."	background-color:#".$this->framebox_bgcolor.";"
			."	text-align:left;"
			."	position: relative;"
			."	margin: 0 auto;"
			."	padding:5px;"
			."	border: ".$this->framebox_border."px; border-style: ".$this->framebox_linetype."; border-color: #".$this->framebox_linecolor.";"
			."}	"

			."#error {"
			."	width:".$this->table_width."px;"
			."	background-color:#".$this->errorbox_bgcolor.";"
			."	font-family:Verdana;"
			."	font-size:11px;"
			."	padding:5px;"
			."	position: relative;"
			."	margin: 10px auto;"
			."	text-align:left;"
			."	border: ".$this->errorbox_border."px; border-style: ".$this->errorbox_linetype."; border-color: #".$this->errorbox_linecolor.";"
			."}	"

			."#error select.text_input {"
			."	background-color:#".$this->inputbox_bgcolor.";"
			."	font-family:Verdana;"
			."	font-size:11px;"
			."	border: ".$this->inputbox_border."px; border-style: ".$this->inputbox_linetype."; border-color: #".$this->inputbox_linecolor.";"
			."	}"

			."#error tr.jsline {"
			."	background-color:#".$this->line_bgcolor.";"
			."	height:".$this->line_height."px;"
			."}	"

			."#success {"
			."	width:".$this->table_width."px;"
			."	background-color:#".$this->successbox_bgcolor.";"
			."	font-family:Verdana;"
			."	font-size:11px;"
			."	padding:5px;"
			."	position: relative;"
			."	margin: 10px auto;"
			."	text-align:left;"
			."	border: ".$this->successbox_border."px; border-style: ".$this->successbox_linetype."; border-color: #".$this->successbox_linecolor.";"
			."}	"

			."table.table {"
			."	width:".($this->table_width - 6)."px;"
			."	font-family: Verdana;"
			."	font-size: 11px;"
			."	margin:3px;"
			."}	"

			."table.table tr.jsline {"
			."	background-color:#".$this->line_bgcolor.";"
			."	height:".$this->line_height."px;"
			."}	"

			."table.table tr.highlighted {"
			."	background-color:#".($this->highlighted_color).";"
			."	height:".($this->row_height)."px;"
			."}	"

			."table.table tr.row.header {"
			."	background-color:#".($this->header_bgcolor).";"
			."	height:".($this->row_height)."px;"
			."}	"

			."table.table tr.row.one {"
			."	background-color:#".($this->oddrows_color).";"
			."	height:".($this->row_height)."px;"
			."}	"

			."table.table tr.row.two {"
			."	background-color:#".($this->evenrows_color).";"
			."	height:".($this->row_height)."px;"
			."}	"

			."table.table td.jsmallfibicon {"
			."	text-align:center;"
			."	width:30px;"
			."}	"

			."table.table td.jsthumb {"
			."	text-align:left;"
			."	padding:5px;"
			."	width:".($this->thumbsize + 10)."px;"
			."}	"

			."table.table tr.row.header td.size {"
			."	width: 100px;"
			."	text-align:right;"
			."}	"

			."table.table tr.highlighted td.size {"
			."	width: 100px;"
			."	text-align:right;"
			."}	"

			."table.table tr.row.one td.size {"
			."	width: 100px;"
			."	text-align:right;"
			."}	"

			."table.table tr.row.two td.size {"
			."	width: 100px;"
			."	text-align:right;"
			."}	"

			."table.table tr.row.header td.changed {"
			."	width: 130px;"
			."	text-align:center;"
			."}	"

			."table.table tr.highlighted td.changed {"
			."	width: 130px;"
			."	text-align:center;"
			."}	"

			."table.table tr.row.one td.changed {"
			."	width: 130px;"
			."	text-align:center;"
			."}	"

			."table.table tr.row.two td.changed {"
			."	width: 130px;"
			."	text-align:center;"
			."}	"

			."input.filter_text_input {"
			."	width:".$this->filter_list_width."px;"
			."	background-color:#".$this->inputbox_bgcolor.";"
			."	font-family:Verdana;"
			."	font-size:10px;"
			."	border: ".$this->inputbox_border."px; border-style: ".$this->inputbox_linetype."; border-color: #".$this->inputbox_linecolor.";"
			."}	"

			."#upload {"
			."	width:".$this->table_width."px;"
			."	padding:5px;"
			."	background-color:#".$this->uploadbox_bgcolor.";"
			."	font-family:Verdana;"
			."	font-size:11px;"
			."	position: relative;"
			."	margin: 0 auto;"
			."	text-align:left;"
			."	border: ".$this->uploadbox_border."px; border-style: ".$this->uploadbox_linetype."; border-color: #".$this->uploadbox_linecolor.";"
			."}	"

			."#upload input.text {"
			."	background-color:#".$this->inputbox_bgcolor.";"
			."	font-family:Verdana;"
			."	font-size:10px;"
			."	border: ".$this->inputbox_border."px; border-style: ".$this->inputbox_linetype."; border-color: #".$this->inputbox_linecolor.";"
			."}	"

			."#upload input.file {"
			."	background-color:#".$this->inputbox_bgcolor.";"
			."	font-family:Verdana;"
			."	font-size:10px;"
			."	border: ".$this->inputbox_border."px; border-style: ".$this->inputbox_linetype."; border-color: #".$this->inputbox_linecolor.";"
			."}	"

			."#upload td.jsimage {"
			."	width:35px;"
			."	text-align:center;"
			."}	"

			."#upload select.text_input {"
			."	background-color:#".$this->inputbox_bgcolor.";"
			."	font-family:Verdana;"
			."	font-size:11px;"
			."	border: ".$this->inputbox_border."px; border-style: ".$this->inputbox_linetype."; border-color: #".$this->inputbox_linecolor.";"
			."	}"

			."#topinfo {"
			."	width:".$this->table_width."px;"
			."	margin:3px;"
			."	font-family:Verdana;"
			."	font-size:11px;"
			."	color:#000000;"
			."	padding:5px;"
			."	margin: 0px auto;"
			."}	"

			."#topinfo td.links {"
			."	text-align:right;"
			."}	"

			."#frame input.text_input {"
			."	width:".($this->table_width - 380)."px;"
			."	background-color:#".$this->inputbox_bgcolor.";"
			."	font-family:Verdana;"
			."	font-size:11px;"
			."	border: ".$this->inputbox_border."px; border-style: ".$this->inputbox_linetype."; border-color: #".$this->inputbox_linecolor.";"
			."}	"

			."#frame select.text_input {"
			."	background-color:#".$this->inputbox_bgcolor.";"
			."	font-family:Verdana;"
			."	font-size:11px;"
			."	border: ".$this->inputbox_border."px; border-style: ".$this->inputbox_linetype."; border-color: #".$this->inputbox_linecolor.";"
			."	}"

			."#bottominfo {"
			."	width:".$this->table_width."px;"
			."	margin:3px;"
			."	font-family:Verdana;"
			."	font-size:9px;"
			."	color:#888888;"
			."	padding:5px;"
			."	margin: 0px auto;"
			."}	"

			;

		return $css;
	}

	//
	// Format the file size
	//
	function fileSizeF($size) 
	{
		$sizes = Array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
		$y = $sizes[0];
		for ($i = 1; (($i < count($sizes)) && ($size >= 1024)); $i++) 
		{
			$size = $size / 1024;
			$y  = $sizes[$i];
		}

		// Erik: Adjusted number format
		$dec = max(0, (3 - strlen(round($size))));
		return number_format($size, $dec, $this->filesize_separator, " ")." ".$y;
		// Old code:
		//return round($size, 2)." ".$y;
	}

	function fileRealSize($file)
	{
		$sizeInBytes = filesize($file);
		//
		// If filesize() fails (with larger files), try to get the size from unix command line.
		if ($sizeInBytes === false) {
			$sizeInBytes = @exec("ls -l '$file' | awk '{print $5}'");
		}
		else
			return $sizeInBytes;
	}

	//
	// Return file extension (the string after the last dot.
	// NOTE: THIS FUNCTION IS REPLICATED IN UPLOAD.PHP
	//
	function fileExtension($file)
	{
		$a = explode(".", $file);
		$b = count($a);
		return $a[$b-1];
	}

	// Return file without extension (the string before the last dot.
	// NOTE: THIS FUNCTION IS REPLICATED IN UPLOAD.PHP
	//
	function fileWithoutExtension($file)
	{
		$a = explode(".", $file);
		$b = count($a);
		$c = $a[0];
		for ($i = 1; $i < $b - 1; $i++)
		{
			$c .= ".".$a[$i];
		}
		return $c;
	}

	//
	// Formatting the changing time
	//
	function fileChanged($time)
	{
		if (!$this->display_filechanged)
		{
			$timeformat = "";
		}
		else if ($this->display_seconds)
		{
			$timeformat = " H:i:s";
		}
		else {
			$timeformat = " H:i";
		}

		switch ($this->date_format)
		{
		case 'dd_mm_yyyy_dashsep':
			return date("d-m-Y".$timeformat, $time);
		case 'dd_mm_yyyy_pointsep':
			return date("d.m.Y".$timeformat, $time);
		case 'dd_mm_yyyy_slashsep':
			return date("d/m/Y".$timeformat, $time);
		case 'yyyy_mm_dd_dashsep':
			return date("Y-m-d".$timeformat, $time);
		case 'yyyy_mm_dd_pointsep':
			return date("Y.m.d".$timeformat, $time);
		case 'yyyy_mm_dd_slashsep':
			return date("Y/m/d".$timeformat, $time);
		case 'mm_dd_yyyy_dashsep':
			return date("m-d-Y".$timeformat, $time);
		case 'mm_dd_yyyy_pointsep':
			return date("m.d.Y".$timeformat, $time);
		case 'mm_dd_yyyy_slashsep':
			return date("m/d/Y".$timeformat, $time);
		}
	}
	
	//
	// Find the icon for the extension
	//
	function fileIcon($l)
	{
		$l = strtolower($l);
	
		if (file_exists($this->imgdir.$l.".png"))
		{
			return $this->imgdir."$l.png";
		} else {
			return $this->imgdir."unknown.png";
		}
	}

	//
	// Generates the sorting arrows
	//
	function makeArrow($sort_by, $sort_as, $type, $masked_dir, $text)
	{
		// set icons
		$sort_icon    = $this->cur_sort_by == $type ? ($this->cur_sort_as == "desc" ? "arrow_down.png" : "arrow_up.png") : "null.gif"; 

		// set links (with relevant icons)
		if(($sort_by == $type || $this->cur_sort_by == $type) && ($sort_as == "desc" || $this->cur_sort_as == "desc"))
		{
			return "<a href=\"".$this->baselink."&dir=".urlencode($masked_dir)."&amp;sort_by=".$type."&amp;sort_as=asc\" title=\""
				.JText::_('set_ascending_order')."\"> $text <img style=\"border:0;\" src=\"".$this->imgdir.$sort_icon."\" /></a>";
		}
		else
		{
			return "<a href=\"".$this->baselink."&dir=".urlencode($masked_dir)."&amp;sort_by=".$type."&amp;sort_as=desc\" title=\""
				.JText::_('set_descending_order')."\"> $text <img style=\"border:0;\" src=\"".$this->imgdir.$sort_icon."\" /></a>";
		}
	}

	//
	// Functions that help sort the files
	//
	function name_cmp_desc($a, $b)
	{
	   return strcasecmp($a["name"], $b["name"]);
	}

	function size_cmp_desc($a, $b)
	{
		return ($a["size"] - $b["size"]);
	}

	function size_cmp_asc($b, $a)
	{
		return ($a["size"] - $b["size"]);
	}

	function changed_cmp_desc($a, $b)
	{
		return ($a["changed"] - $b["changed"]);
	}

	function changed_cmp_asc($b, $a)
	{
		return ($a["changed"] - $b["changed"]);
	}

	function name_cmp_asc($b, $a)
	{
		return strcasecmp($a["name"], $b["name"]);
	}

	//
	// Find the directory one level up
	//
	function upperDir($dir)
	{
		// Simpler implementation of upperDir method /ErikLtz
		$arr = explode(DS, $dir);
		unset($arr[count($arr) - 1]);
		return implode(DS, $arr);
		
		/*
		$chops = explode(DS, $dir);
		$num = count($chops);
		$chops2 = array();
		for($i = 0; $i < $num - 1; $i++)
		{
			$chops2[$i] = $chops[$i];
		}
		$dir2 = implode(DS, $chops2);
		return $dir2;
		*/
	}

	// Return last part in directory chain (built in basename depends on locale and having an utf8 locale may
        // return wrong characters when they really are iso8859-1)
	// [ErikLtz]
	
	function baseName($dir)
	{
		//$arr = explode(DS, $dir);
		$arr = explode("/", $this->makeForwardSlashes($dir));
		return $arr[count($arr) - 1];
	}

	// this function is reported in readfile() php.net page to bypass readfile() documented problems with large files
	function readfile_chunked($filename,$retbytes=true) { 
	
		$chunksize = 1 * (1024 * 1024); // how many bytes per chunk 
		$buffer = ''; 
		$counter = 0; 
     
		$handle = fopen($filename, 'rb'); 
		if ($handle === FALSE)
		{ 
			return FALSE; 
		} 
	
		while (!feof($handle))
		{ 
			$buffer = fread($handle, $chunksize); 
			echo $buffer; 
			@ob_flush(); 
			flush(); 

			if ($retbytes)
			{ 
				$counter += strlen($buffer); 
			} 
		}

		$status = fclose($handle); 
	
		if ($retbytes && $status)
		{
			return $counter; // return num. bytes delivered like readfile() does. 
		}

		return $status; 
	} 

	// UTF-8 encoding and decoding is set as a backend parameter
	
	function chosen_encoding($in_string)
	{
		if ($this->encode_to_utf8)
		{ 
			return utf8_encode($in_string); 
		} 
		else
		{ 
			return $in_string; 
		} 
	}

	function chosen_decoding($in_string)
	{
		if ($this->encode_to_utf8)
		{ 
			return utf8_decode($in_string); 
		} 
		else
		{ 
			return $in_string; 
		} 
	}

	function restoreArchiveFilename($filename)
	{
		return preg_replace("/\s\(".JText::_('archived')."\s\d{4}\-\d{2}\-\d{2}\s\d{2}\.\d{2}\.\d{2}\)/", "", $filename); 
	}

	function makeForwardSlashes($url)
	{
		return str_replace("\\", "/", $url);
	}

	function maskAbsPath($url)
	{
		return str_replace($this->default_absolute_path, "JSROOT", $url);
	}

	function unmaskAbsPath($url)
	{
		return str_replace("JSROOT", $this->default_absolute_path, $url);
	}

	// function based on CroppedThumbnail() by seifer at loveletslive dot com and olaso on class by satanas147 at gmail dot com (php.net on imagecopyresampled)
	function makeThumbnail($imgfile, $imgfile_ext, $dir, $relative_dir, $thumbnail_width, $thumbnail_height) {

		// if size is 0 return 0 (it means do not make a thumbnail)
		if (!$this->thumbsize || !strcmp($this->baseName($dir), "JS_THUMBS"))
		{
			// remove all existing thumbnails if the current thumbsize is zero
			$this->remove_thumbnail_files($dir);
			return 0;
		}

		// also return 0 if thumbs folder cannot be created
		if (!is_dir($dir.DS."JS_THUMBS") && !($rc = @mkdir ($dir.DS."JS_THUMBS")))
		{
			return 0;
		}

		// and also if extension is not available - update this part when new extensions are introdced
		$available_extensions = array("JPG", "JPEG", "GIF", "PNG");

		if(!in_array(strtoupper($imgfile_ext), $available_extensions))
		{
			return 0;
		}


		// if thumbnail file exists, do some checks before returning 1 (it means use current thumbnail)
		if (file_exists($dir.DS."JS_THUMBS".DS.$imgfile))
		{
			// check if thumbnail is newer than file and the image size is the same as the requested size (in this case return 1 as we don't need to make a new thumb)
			list($curthumbwidth, $curthumbheight) = getimagesize($relative_dir.DS."JS_THUMBS".DS.$imgfile);
			if ((filemtime($dir.DS."JS_THUMBS".DS.$imgfile) >= filemtime($dir.DS.$imgfile)) && $thumbnail_width == $curthumbwidth && $thumbnail_height == $curthumbheight)
			{
				return 1;
			}
		}

		//getting the image dimensions
		list($width_orig, $height_orig) = getimagesize($relative_dir.DS.$imgfile);

		// switch based on image type
		switch(strtoupper($imgfile_ext))
		{
			case "JPEG":
			case "JPG":
				$image_resource = imagecreatefromjpeg($relative_dir.DS.$imgfile);
				break;

			case "GIF":
				$image_resource = imagecreatefromgif($relative_dir.DS.$imgfile);
				break;

			case "PNG":
				$image_resource = imagecreatefrompng($relative_dir.DS.$imgfile);
				break;
		}
		$ratio_orig = $width_orig / $height_orig;
    
		if ($thumbnail_width / $thumbnail_height > $ratio_orig)
		{
			$new_height = $thumbnail_width / $ratio_orig;
			$new_width = $thumbnail_width;
		}
		else
		{
			$new_width = $thumbnail_height * $ratio_orig;
			$new_height = $thumbnail_height;
		}
    
		$x_mid = $new_width / 2;  //horizontal middle
		$y_mid = $new_height / 2; //vertical middle
    
		$process = imagecreatetruecolor(round($new_width), round($new_height)); 

		imagecopyresampled($process, $image_resource, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
		$thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
		imagecopyresampled($thumb, $process, 0, 0, ($x_mid-($thumbnail_width/2)), ($y_mid-($thumbnail_height/2)), $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);

		imagejpeg($thumb, $relative_dir.DS."JS_THUMBS".DS.$imgfile);

		imagedestroy($process);
		imagedestroy($image_resource);

		return 1;
	}

	function remove_thumbnail_files($dir) {

		if (is_dir($dir.DS."JS_THUMBS"))
		{
			if ($dh = opendir($dir.DS."JS_THUMBS"))
			{
				while (($file = readdir($dh)) !== false)
				{
					if ($file == '.' || $file == '..')
					{
						continue;
					}
					@unlink($dir.DS."JS_THUMBS".DS.$file);
				}
				closedir($dh);
			}
			@rmdir($dir.DS."JS_THUMBS");
		}
	}


} // end of plugin class extension

?>
