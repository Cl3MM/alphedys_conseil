<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2011, Think Network GmbH, Munich
 *
 * All rights reserved.  The Joom!Fish project is a set of extentions for
 * the content management system Joomla!. It enables Joomla!
 * to manage multi lingual sites especially in all dynamic information
 * which are stored in the database.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------------
 * $Id: JoomfishManager.class.php 1551 2011-03-24 13:03:07Z akede $
 * @package joomfish
 * @subpackage classes
 *
*/



/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * The JoomFishManager controls all important configuration and information
 * of the content elements. These information might be cached in the session
 * settings if necessary in furture.
 *
 * @package joomfish
 * @subpackage administrator
 * @copyright 2003 - 2011, Think Network GmbH, Munich
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version 2.1, $Revision: 1551 $
 * @author Alex Kempkens <joomfish@thinknetwork.com>
*/
class JoomFishManager {
	/** @var array of all known content elements and the reference to the XML file */
	var $_contentElements;

	/** @var string Content type which can use default values */
	var $DEFAULT_CONTENTTYPE="content";

	/** @var config Configuration of the map */
	var $_config=null;

	/** @var Component config */
	var $componentConfig= null;

	/**	PrimaryKey Data */
	var $_primaryKeys = null;

	/** @var array for all system known languages */
	var $allLanguagesCache=array();

	/** @var array for all languages listed by shortcode */
	var $allLanguagesCacheByShortcode=array();

	/** @var array for all languages listed by ID */
	var $allLanguagesCacheByID=array();

	/** @var array for all active languages */
	var $activeLanguagesCache=array();

	/** @var array for all active languages listed by shortcode */
	var $activeLanguagesCacheByShortcode=array();

	/** @var array for all active languages listed by ID */
	var $activeLanguagesCacheByID=array();

	/** Standard constructor */
	function JoomFishManager() {
		include_once(JOOMFISH_ADMINPATH .DS. "models".DS."ContentElement.php");

		// now redundant
		$this->_loadPrimaryKeyData();

		$this->activeLanguagesCache = array();
		$this->activeLanguagesCacheByShortcode = array();
		$this->activeLanguagesCacheByID = array();
		// get all languages and split out active below
		$langlist = $this->getLanguages(false);
		$this->_cacheLanguages($langlist);

		// Must get the config here since if I do so dynamically it could be within a translation and really mess things up.
		$this->componentConfig = JComponentHelper::getParams( 'com_joomfish' );
	}

	function & getInstance($adminPath=null){
		static $instance;
		if (!isset($instance)){
			$instance = new JoomFishManager($adminPath);
		}
		return $instance;
	}

	/**
	 * Cache languages in instance
	 * This method splits the system relevant languages in various caches for faster access
	 * @param array of languages to be stored
	 */
	function _cacheLanguages($langlist) {
		$this->activeLanguagesCache = array();
		$this->activeLanguagesCacheByShortcode = array();
		$this->activeLanguagesCacheByID = array();

		if (count($langlist)>0){
			foreach ($langlist as $alang){
				if ($alang->active){
					$this->activeLanguagesCache[$alang->code] = $alang;
					$this->activeLanguagesCacheByID[$alang->id] = $alang;
					$this->activeLanguagesCacheByShortcode[$alang->shortcode] = $alang;
				}
				$this->allLanguagesCache[$alang->code] = $alang;
				$this->allLanguagesCacheByID[$alang->id] = $alang;
				$this->allLanguagesCacheByShortcode[$alang->shortcode] = $alang;
			}
		}
	}

	/**
	 * Load Primary key data from database
	 *
	 */
	function _loadPrimaryKeyData() {
		if ($this->_primaryKeys==null){
			$db = JFactory::getDBO();
			$db->setQuery( "SELECT joomlatablename,tablepkID FROM `#__jf_tableinfo`");
			$rows = $db->loadObjectList("",false);
			$this->_primaryKeys = array();
			if( $rows ) {
				foreach ($rows as $row) {
					$this->_primaryKeys[$row->joomlatablename]=$row->tablepkID;
				}
			}

		}
	}

	/**
	 * Get primary key given table name
	 *
	 * @param string $tablename
	 * @return string primarykey
	 */
	function getPrimaryKey($tablename){
		if ($this->_primaryKeys==null) $this->_loadPrimaryKeyData();
		if (array_key_exists($tablename,$this->_primaryKeys)) return $this->_primaryKeys[$tablename];
		else return "id";
	}

	/**
	 * Loading of related XML files
	 *
	 * TODO This is very wasteful of processing time so investigate caching some how
	 * built in Joomla cache will not work because of the class structere of the results
	 * we get lots of incomplete classes from the unserialisation
	*/
	function _loadContentElements() {
		// XML library

		// Try to find the XML file
		$filesindir = JFolder::files(JOOMFISH_ADMINPATH ."/contentelements" ,".xml");
		if(count($filesindir) > 0)
		{
			$this->_contentElements = array();
			foreach($filesindir as $file)
			{
				unset($xmlDoc);
				$xmlDoc = new DOMDocument();
				if ($xmlDoc->load(JOOMFISH_ADMINPATH . "/contentelements/" . $file)) {
					$element = $xmlDoc->documentElement;
					if ($element->nodeName == 'joomfish') {
						if ( $element->getAttribute('type')=='contentelement' ) {
							$nameElements = $element->getElementsByTagName('name');
							$nameElement = $nameElements->item(0);
							$name = strtolower( trim($nameElement->textContent) );
							$contentElement = new ContentElement( $xmlDoc );
							$this->_contentElements[$contentElement->getTableName()] = $contentElement;
						}
					}
				}
			}
		}
	}

	/**
	 * Loading of specific XML files
	*/
	function _loadContentElement($tablename) {
		if (!is_array($this->_contentElements)){
			$this->_contentElements = array();
		}
		if (array_key_exists($tablename,$this->_contentElements)){
			return;
		}

		$file = JOOMFISH_ADMINPATH .'/contentelements/'.$tablename.".xml";
		if (file_exists($file)){
			unset($xmlDoc);
			$xmlDoc = new DOMDocument();
			if ($xmlDoc->load( $file)) {
				$element = $xmlDoc->documentElement;
				if ($element->nodeName == 'joomfish') {
					if ( $element->getAttribute('type')=='contentelement' ) {
						$nameElements = $element->getElementsByTagName('name');
						$nameElement = $nameElements->item(0);
						$name = strtolower( trim($nameElement->textContent) );
						$contentElement = new ContentElement( $xmlDoc );
						$this->_contentElements[$contentElement->getTableName()] = $contentElement;
						return $contentElement;
					}
				}
			}
		}
		return null;
	}

	/**
	 * Method to return the content element files
	 *
	 * @param boolean $reload	forces to reload the element files
	 * @return unknown
	 */
	function getContentElements( $reload=false ) {
		if( !isset( $this->_contentElements ) || $reload ) {
			$this->_loadContentElements();
		}
		return $this->_contentElements;
	}

	/** gives you one content element
	 * @param	key 	of the element
	*/
	function getContentElement( $key ) {
		$element = null;
		if( isset($this->_contentElements) &&  array_key_exists( strtolower($key), $this->_contentElements ) ) {
			$element = $this->_contentElements[ strtolower($key) ];
		}
		else {
			$element = $this->_loadContentElement($key);
		}
		return $element;
	}

	/**
	* @param string The name of the variable (from configuration.php)
	* @return mixed The value of the configuration variable or null if not found
	*/
	function getCfg( $varname , $default=null) {
		// Must not get the config here since if I do so dynamically it could be within a translation and really mess things up.
 		return $this->componentConfig->getValue($varname,$default);
	}

	/**
	* @param string The name of the variable (from configuration.php)
	* @param mixed The value of the configuration variable
	*/
	function setCfg( $varname, $newValue) {
		$config = JComponentHelper::getParams( 'com_joomfish' );
		$config->setValue($varname, $newValue);
	}

	/** Creates an array with all the active languages for the JoomFish
	 *
	 * @return	Array of languages
	 */
	function getActiveLanguages($cacheReload=false) {
		if( isset($this) && $cacheReload) {
			$langList = $this->getLanguages();
			$this->_cacheLanguages($langList);
		}
		/* if signed in as Manager or above include inactive languages too */
		$user = JFactory::getUser();
		if ( isset($this) && $this->getCfg("frontEndPreview") && isset($user) && (strtolower($user->usertype)=="manager" || strtolower($user->usertype)=="administrator" || strtolower($user->usertype)=="super administrator")) {
			if (isset($this) && isset($this->allLanguagesCache)) return $this->allLanguagesCache;
		}
		else {
			if (isset($this) && isset($this->activeLanguagesCache)) return $this->activeLanguagesCache;
		}
		return JoomFishManager::getLanguages( true );
	}

	/** Creates an array with all languages for the JoomFish
	 *
	 * @param boolean	indicates if those languages must be active or not
	 * @return	Array of languages
	 */
	function getLanguages( $active=true ) {
		$db = JFactory::getDBO();
		$langActive=null;

		$sql = 'SELECT * FROM #__languages';

		if( $active ) {
			$sql  .= ' WHERE active=1';
		}
		$sql .= ' ORDER BY ordering';

		$db->setQuery(  $sql );
		$rows = $db->loadObjectList('id');
		// We will need this class defined to popuplate the table
		include_once(JOOMFISH_ADMINPATH .DS. 'tables'.DS.'JFLanguage.php');
		if( $rows ) {
			foreach ($rows as $row) {
				$lang = new TableJFLanguage($db);
				$lang->bind($row);

				$langActive[] = $lang;
			}
		}

		return $langActive;
	}

	/**
	 * Fetches full langauge data for given shortcode from language cache
	 *
	 * @param array()
	 */
	function getLanguageByShortcode($shortcode, $active=false){
		if ($active){
			if (isset($this) && isset($this->activeLanguagesCacheByShortcode) && array_key_exists($shortcode,$this->activeLanguagesCacheByShortcode))
			return $this->activeLanguagesCacheByShortcode[$shortcode];
		}
		else {
			if (isset($this) && isset($this->allLanguagesCacheByShortcode) && array_key_exists($shortcode,$this->allLanguagesCacheByShortcode))
			return $this->allLanguagesCacheByShortcode[$shortcode];
		}
		return false;
	}

	/**
	 * Fetches full langauge data for given code from language cache
	 *
	 * @param array()
	 */
	function getLanguageByCode($code, $active=false){
		if ($active){
			if (isset($this) && isset($this->activeLanguagesCache) && array_key_exists($code,$this->activeLanguagesCache))
			return $this->activeLanguagesCache[$code];
		}
		else {
			if (isset($this) && isset($this->allLanguagesCache) && array_key_exists($code,$this->allLanguagesCache))
			return $this->allLanguagesCache[$code];
		}
		return false;
	}


	function getLanguagesIndexedByCode($active=false){
		if ($active){
			if (isset($this) && isset($this->activeLanguagesCache))
			return $this->activeLanguagesCache;
		}
		else {
			if (isset($this) && isset($this->allLanguagesCache))
			return $this->allLanguagesCache;
		}
		return false;
	}

	function getLanguagesIndexedById($active=false){
		if ($active){
			if (isset($this) && isset($this->activeLanguagesCacheByID))
			return $this->activeLanguagesCacheByID;
		}
		else {
			if (isset($this) && isset($this->allLanguagesCacheByID))
			return $this->allLanguagesCacheByID;
		}
		return false;
	}

	/** Retrieves the language ID from the given language name
	 *
	 * @param	string	Code language name (normally $mosConfig_lang
	 * @return	int 	Database id of this language
	 */
	function getLanguageID( $codeLangName="" ) {
		$db = JFactory::getDBO();
		$langID = -1;
		if ($codeLangName != "" ) {
			// Should check all languages not just active languages
			if (isset($this) && isset($this->allLanguagesCache) && array_key_exists($codeLangName,$this->allLanguagesCache)){
				return $this->allLanguagesCache[$codeLangName]->id;
			}
			else {
				$db->setQuery( "SELECT id FROM #__languages WHERE active=1 and code = '$codeLangName' order by ordering" );
				$langID = $db->loadResult(false);
			}
		}
		return $langID;
	}

	/** Retrieves the language code (for URL) from the given language name
	 *
	 * @param	string	Code language name (normally $mosConfig_lang
	 * @return	int 	Database id of this language
	 */
	function getLanguageCode( $codeLangName="" ) {
		$db = JFactory::getDBO();
		$langID = -1;
		if ($codeLangName != "" ) {
			if (isset($this) && isset($this->activeLanguagesCache) && array_key_exists($codeLangName,$this->activeLanguagesCache))
			return $this->activeLanguagesCache[$codeLangName]->shortcode;
			else {
				$db->setQuery( "SELECT shortcode FROM #__languages WHERE active=1 and code = '$codeLangName' order by ordering" );
				$langID = $db->loadResult(false);
			}
		}
		return $langID;
	}

	function & getCache($lang=""){
		$conf = JFactory::getConfig();
		if ($lang===""){
			$lang=$conf->getValue('config.language');
		}
		// I need to get language specific cache for language switching module
		if (!isset($this->_cache)) {
			$this->_cache = array();
		}
		if (isset($this->_cache[$lang])){
			return $this->_cache[$lang];
		}

		jimport('joomla.cache.cache');

		if (version_compare(phpversion(),"5.0.0",">=")){
			// Use new Joomfish DB Cache Storage Handler but only for php 5
			$storage = 'jfdb';
			// Make sure we have loaded the cache stroage handler
			JLoader::import('JCacheStorageJFDB', dirname( __FILE__ ));
		}
		else {
			$storage = 'file';
		}

		$options = array(
			'defaultgroup' 	=> "joomfish-".$lang,
			'cachebase' 	=> $conf->getValue('config.cache_path'),
			'lifetime' 		=> $this->getCfg("cachelife",1440) * 60,	// minutes to seconds
			'language' 		=> $conf->getValue('config.language'),
			'storage'		=> $storage
		);

		$this->_cache[$lang] = JCache::getInstance( "callback", $options );
		return $this->_cache[$lang];
	}

}
