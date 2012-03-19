<?php
defined('_JEXEC') or die('Restricted access');
if (($this->error->code) == '404') {
	$referrer = isset($_SERVER["REQUEST_URI"]) ? '#'.$_SERVER["REQUEST_URI"] : '';
	header('Location: http://www.alphedys.fr/404.html'.htmlentities($referrer));
	exit;
}
?>