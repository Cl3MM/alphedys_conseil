<?php

// no direct access
defined('_JEXEC') or die('Restricted access');


if ( ! defined('customModMainMenuXMLCallback') )
{
function customModMainMenuXMLCallback(&$node, $args)
{
	/* global menu items counter (reserved use) */
	global $item_per_level_counter;
	
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
			/* keep reference to the parent */
			$child->my_parent =& $node;
			
			if ($child->attributes('access') > $user->get('aid', 0)) {
				$node->removeChild($child);
			}
		}
	}

	if (($node->name() == 'li') && isset($node->ul)) {
		$node->addAttribute('class', 'parent');
	}

	if (isset($path) && in_array($node->attributes('id'), $path))
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
	
	/* get current menu level */
	$my_level=$node->level();
	
	/* reset items counter (per level) */
	if ($my_level == 0) $item_per_level_counter=array();
	if ($node->name() == 'ul') $item_per_level_counter[$my_level]=0;
	
	/* our custom applies only to "li" elements */
	if ($node->name() == 'li')
	{
		/* counter == 0, then it is the first item of its sub-menu */
		if ($item_per_level_counter[$my_level] == 0)
			$node->addAttribute('class', $node->attributes('class').' firstItem');
			
		/* counter == max, the it is the last item of its sub-menu */
		if ($item_per_level_counter[$my_level] == sizeof($node->my_parent->children())-1)
			$node->addAttribute('class', $node->attributes('class').' lastItem');
		
		/* increase counter */
		$item_per_level_counter[$my_level]++;
		
	}

	if (isset($path) && $node->attributes('id') == $path[0]) {
		$node->addAttribute('id', 'current');
	} else {
		$node->removeAttribute('id');
	}
	$node->removeAttribute('level');
	$node->removeAttribute('access');
}
	define('customModMainMenuXMLCallback', true);
}

modMainMenuHelper::render($params, 'customModMainMenuXMLCallback');
