<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>

<?php 
	if (count($list) > 1) {
		echo '<ul id="breadcrumbs">';

		for ($i = 0; $i < $count; $i ++) :

		// If not the last item in the breadcrumbs add the separator
		if ($i < $count -1) {
			if(!empty($list[$i]->link)) {
				echo '<li><a href="'.$list[$i]->link.'">'.$list[$i]->name.'</a></li>';
			} else {
				echo $list[$i]->name;
			}
			echo '<li>'.$separator.'</li>';
		}  else if ($params->get('showLast', 1)) { // when $i == $count -1 and 'showLast' is true
			echo '<li>'.$list[$i]->name.'</li>';
		}
		endfor;
		echo '</ul>';
	}
?>
