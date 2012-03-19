<?php // no direct access
defined('_JEXEC') or die('Restricted access');

if ( !defined('rightMenuFormat') )
{
	function rightMenuFormat($result, $css) {

		$xml = new SimpleXMLElement($result);
		
		$s = '<ul class="rightmenuitem-'.$css.'">';
		
		foreach($xml->li as $li) {
			$s .= '<li';
			if (isset($li['id'])) {
				$s .= ' class="current"';
			} else { $s .= ' class="rightmenuitem-'.$css.'"'; 
			}
			$s .= '>';
				
			$s .= '<a class="rightmenuitem-'.$css.'" href="'.$li->a['href'].'">'.$li->a->span.'</a>';
			$s .= '</li>';
		}
		$s .= '</ul>';
		$s = str_replace('&', '&amp;', $s);
		$xml = simplexml_load_string($s);

		$result = $xml->asXML();

		//return the code
		return $result; 	

	}
	define('rightMenuFormat', true);
}


/*************************************
************* Main Menu **************
**************************************/
if ( !defined('lyceumHorMenuPatch') )
{
function lyceumHorMenuPatch($result){

/*
	if ( !defined('stro_replace') )
	{
		function stro_replace($search, $replace, $subject)
		{
			return strtr( $subject, array_combine($search, $replace) );
		}
	}
	define('stro_replace', true);
*/
	if ( !defined('in_array2') )
	{
		function in_array2($key, $arr) {
			
			foreach($arr as $el) {
				if (strstr(strtolower($key), strtolower($el))) { return true; }
			}
			return false;
		}
		define('in_array2', true);
	}

	$xml = new SimpleXMLElement($result);
	
	$s = '<ul id="navigation">';
  $arr = array('Ressources', 'Risques', 'Partenaire', 
    'Contact', 'Formations', 'Clients', 'Accueil', 'Durable',
    'Resources', 'Prevention', 'Development', 'sessions', 'customers', 'home',
    '页', '系', '源', '防', '程', '户'
  );
	
	foreach($xml->li as $li) {
		$s .= '<li';
		if (isset($li['id'])) {
			$s .= ' class="current"';
		}
		$s .= '>';
			
		$s .= '<a href="'.$li->a['href'].'">'.$li->a->span.'</a>';
		if ($li->ul) {
			$s .=  '<span class="sub">';
			foreach($li->ul->li as $li2) {
				// if(isset($li2->a->span) and ($li2->a->span == 'Formation et Coaching' )) {
					// $tmp = explode(' et ', $li2->a->span);
					// $li2->a->span = $tmp[0].' et <br/>'.$tmp[1];
				// }
				$s .=  '<a href="'.$li2->a['href'].'">'.$li2->a->span.'</a> ';
				if(isset($li2->a->span) and in_array2($li2->a->span, $arr)) {
					$s .= '<br/>';
				}
			}
			$s .=  '</span>';
		}
		$s .= '</li>';
	}
	$s .= '</ul>';
	$s = str_replace(', </span>', '</span>', $s);
	$s = str_replace('&', '&amp;', $s);
	$search = array(' ', '&');
	$replace = array('&nbsp;', '&amp;');
	
	#stro_replace($search, $replace, $s);

	$xml = simplexml_load_string($s);

	$result = $xml->asXML();

   	//return the code
   	return $result; 
 
}
define('lyceumHorMenuPatch', true);
}

//This is copied from mod_mainmenu and renamed to 'modLyceumMainMenuXMLCallbackDefined'

if ( !defined('modLyceumMainMenuXMLCallbackDefined') )
{
function modLyceumMainMenuXMLCallback(&$node, $args)
{
	$user	= &JFactory::getUser();
	$menu	= &JSite::getMenu();
	$active	= $menu->getActive();
	$path	= isset($active) ? array_reverse($active->tree) : null;

	if (($args['end']) && ($node->attributes('level') >= $args['end']))
	{
		$children = $node->children();
		foreach ($node->children() as $child)
		{
			if ($child->name() == 'ul') {
				$node->removeChild($child);
			}
		}
	}

	if ($node->name() == 'ul') {
		foreach ($node->children() as $child)
		{
			if ($child->attributes('access') > $user->get('aid', 0)) {
				$node->removeChild($child);
			}
		}
	}

	if (($node->name() == 'li') && isset($node->ul)) {
		$node->addAttribute('class', 'parent');
	}

	if (isset($path) && (in_array($node->attributes('id'), $path) || in_array($node->attributes('rel'), $path)))
	{
		if ($node->attributes('class')) {
			$node->addAttribute('class', $node->attributes('class').' active');
		} else {
			$node->addAttribute('class', 'active');
		}
	}
	else
	{
		if (isset($args['children']) && !$args['children'])
		{
			$children = $node->children();
			foreach ($node->children() as $child)
			{
				if ($child->name() == 'ul') {
					$node->removeChild($child);
				}
			}
		}
	}

	if (($node->name() == 'li') && ($id = $node->attributes('id'))) {
		if ($node->attributes('class')) {
			$node->addAttribute('class', $node->attributes('class').' item'.$id);
		} else {
			$node->addAttribute('class', 'item'.$id);
		}
	}

	if (isset($path) && $node->attributes('id') == $path[0]) {
		$node->addAttribute('id', 'current');
	} else {
		$node->removeAttribute('id');
	}
	$node->removeAttribute('rel');
	$node->removeAttribute('level');
	$node->removeAttribute('access');
}
	define('modLyceumMainMenuXMLCallbackDefined', true);
}

ob_start(); //render the menu, and capture the output using output buffering
 modMainMenuHelper::render($params, 'modLyceumMainMenuXMLCallback');
$menu_html = ob_get_contents();
ob_end_clean(); //You can use the "tag" parameter to apply this to only specific menus (not used in this example)
 
if ($params->get('menutype') == "primarynav") {
  $tag = $params->get('tag_id');
}
if (!isset($tag)) {
	$tag = $params->get('tag_id');
}

//output the menu!
if ($params->get('menutype') == "mainmenu") {
	echo lyceumHorMenuPatch($menu_html, $tag);
} else {
	if ($params->get('menutype') == "ressources-humaines") {
		$css = 'rh';
	}
	if ($params->get('menutype') == "prevention-des-risques") {
		$css = 'pr';
	}
	if ($params->get('menutype') == "developpement-durable") {
		$css = 'dd';
	} 
	echo rightMenuFormat($menu_html, $css);
}


/************************************************************************************
if ( !defined('modLyceumMainMenuXMLCallbackDefined') )
{
function modLyceumMainMenuXMLCallback(&$node, $args)
{
	$user	= &JFactory::getUser();
	$menu	= &JSite::getMenu();
	$active	= $menu->getActive();
	$path	= isset($active) ? array_reverse($active->tree) : null;

	if (($args['end']) && ($node->attributes('level') >= $args['end']))
	{
		$children = $node->children();
		foreach ($node->children() as $child)
		{
			if ($child->name() == 'ul') {
				$node->removeChild($child);
			}
		}
	}

	if ($node->name() == 'ul') {
		foreach ($node->children() as $child)
		{
			if ($child->attributes('access') > $user->get('aid', 0)) {
				$node->removeChild($child);
			}
		}
	}

	if (($node->name() == 'li') && isset($node->ul)) {
		$node->addAttribute('class', 'parent');
	}

	if (isset($path) && (in_array($node->attributes('id'), $path) || in_array($node->attributes('rel'), $path)))
	{
		if ($node->attributes('class')) {
			$node->addAttribute('class', $node->attributes('class').' active');
		} else {
			$node->addAttribute('class', 'active');
		}
	}
	else
	{
		if (isset($args['children']) && !$args['children'])
		{
			$children = $node->children();
			foreach ($node->children() as $child)
			{
				if ($child->name() == 'ul') {
					$node->removeChild($child);
				}
			}
		}
	}

	if (($node->name() == 'li') && ($id = $node->attributes('id'))) {
		if ($node->attributes('class')) {
			$node->addAttribute('class', $node->attributes('class').' item'.$id);
		} else {
			$node->addAttribute('class', 'item'.$id);
		}
	}

	if (isset($path) && $node->attributes('id') == $path[0]) {
		$node->addAttribute('id', 'current');
	} else {
		$node->removeAttribute('id');
	}
	$node->removeAttribute('rel');
	$node->removeAttribute('level');
	$node->removeAttribute('access');
}
	define('modLyceumMainMenuXMLCallbackDefined', true);
}*/
?>
