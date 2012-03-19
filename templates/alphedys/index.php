<?php
defined('_JEXEC') or die('Restricted access');

//$url = clone(JURI::getInstance());
$showRightColumn = $this->countModules('right');
//$showRightColumn &= JRequest::getCmd('layout') != 'form';
//$showRightColumn &= JRequest::getCmd('task') != 'edit'
$showSearch = (JRequest::getVar('view')=='search')?true:false;
$showClients = (JRequest::getVar('view')=='article' && JRequest::getVar('id')=='5')?true:false;
$catRH = (JRequest::getVar('view')=='article' && JRequest::getVar('id')=='19')?true:false;
$catPR = (JRequest::getVar('view')=='article' && JRequest::getVar('id')=='12')?true:false;
$cat = (JRequest::getVar('view')=='article' && (JRequest::getVar('id')=='12' || JRequest::getVar('id')=='19'|| JRequest::getVar('id')=='9'|| JRequest::getVar('id')=='32'|| JRequest::getVar('id')=='30'))?true:false;
//$cat = true;
//$showRightColumn = false;
//$showSearch = false;
$frontpage = JRequest::getVar('view')=='frontpage';
$sitemap = JRequest::getVar('option')=='com_xmap';

echo '<?xml version="1.0" encoding="utf-8"?'.'>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="google-site-verification" content="-ipI1EM5cV_st0Qlt_DFP_S_dVrhldAHPf46cX3V64o" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<jdoc:include type="head" />
		<!--  CSS  -->
		<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.8.0r4/build/reset-fonts-grids/reset-fonts-grids.css"/>
		<!-- <link rel="stylesheet" type="text/css" href="templates/alphedys/css/reset-fonts-grids.css" /> -->
		<link rel="stylesheet" type="text/css" href="templates/alphedys/css/style.css" media="all"/>
		<!--  IE  -->

		<!--[if IE 8]>
			<link rel="stylesheet" type="text/css" href="templates/alphedys/css/ie8-style.css" media="all"/>
		<![endif]-->
		
		<!--[if lt IE 8]>
			<script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js">IE7_PNG_SUFFIX=".png";</script>
			<script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/ie7-squish.js"></script>
			<link rel="stylesheet" type="text/css" href="templates/alphedys/css/ie6-style.css" media="all"/>
		<![endif]-->
		
		<?php if ($showClients) : ?>
		<script type="text/javascript" src="templates/alphedys/javascripts/jquery.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				$(function() {
					$('h2')
						.css("cursor","pointer")
						.attr("title","Cliquer afin d'afficher la liste des Clients")
						.click(function(){
							//$('.article-content > ul').hide().children('li');
							//$(this).siblings('.'+this.id).toggle();
							$('ul.'+this.id).toggle("slow");
						});
					$('.article-content > ul').hide().children('li');
					$('.art-content > ul').hide().children('li');
				});
			});
		</script>
		<?php endif; ?>
		<?php if (!$showRightColumn) : ?>
		<!--  JS  -->
		<script type="text/javascript" src="templates/alphedys/javascripts/jquery.js"></script>
		<script type="text/javascript" src="templates/alphedys/javascripts/easySlider.js"></script>
		<script type="text/javascript">
			$(document).ready(function(){	
				$("#slider").easySlider({
					auto: true, 
					continuous: true,
					prevText:'',
					pause:5000,
					nextText:''
				});
			});		
			$(function () {
				if ((jQuery.browser.msie) && (jQuery.browser.version < 7)) {
					$('#message').toggle('slow');
						$('#message a.close-notify').click(function () {
							$('#message').toggle('slow');
							return false;
						});
				}
			});
		</script>
		<?php endif; ?>
		<script type="text/javascript">
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-1758468-2']);
		  _gaq.push(['_setAllowAnchor', true]);
		  _gaq.push(['_trackPageview']);

		  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		</script>
	</head>
	<body>
		<?php //echo JRequest::getVar('view'); ?>
		<?php //echo JRequest::getVar('id'); ?>
		<?php //echo JRequest::getVar('itemid'); ?>
		<?php //echo JRequest::getVar('option'); ?>
    <?php
    /*
		if (isset($_SERVER['HTTP_USER_AGENT'])) :
			if (stripos($_SERVER['HTTP_USER_AGENT'], "msie")) :
				if (intval(substr($_SERVER['HTTP_USER_AGENT'], stripos($_SERVER['HTTP_USER_AGENT'], "msie")+5)) > 6) :
		?>
		<div id="message" style="display: none;">
			<span>Vous utilisez une version obsol&egrave;te du navigateur Internet Explorer. Pour un affichage optimal, nous vous conseillons de le mettre &agrave; jour,
			ou d'utiliser <a href="http://www.google.com/chrome" target="_blank">Google Chrome</a> ou 
			<a href="http://www.mozilla-europe.org/fr/firefox/" target="_blank">Mozilla Firefox</a>.<br/>
			<a target="_blank" href="http://www.mon-navigateur.com/">Quel est mon navigateur ?</a> 
			<a href="http://translate.google.fr/translate?hl=fr&sl=en&tl=fr&u=http://www.updatebrowser.net/" target="_blank">Mettre &agrave; jour mon navigateur.</a></span><a href="#" class="close-notify">x</a>
		</div>
		<?php 
				endif; 
			endif; 
		endif; 
    */
    ?>
		<!--<div id="shade"><div id="shade-image"></div></div>-->
		<!--<div id="container">-->
		<div id="doc">
			<div id="hd">
        <div id="searchbar">
          <jdoc:include type="modules" name="banner" style="none" />
          <div class="spacer"></div>
        </div>
				<a href="/"><img style="margin-left: -4px; float:left;" src="templates/alphedys/images/Logo.2010_395x70.png" alt="Alphedys Conseil Logo"/></a>
				<jdoc:include type="modules" name="left" style="none" />
			</div>
			<?php if (!$frontpage && !$sitemap): ?>
			<div id="bd">
				<?php if (!$showSearch and !$cat): ?>
				<div id="menu">
					<jdoc:include type="modules" name="right" style="alphedys" headerLevel="4" />
				</div>
				<div id="rh">
					<jdoc:include type="module" name="breadcrumbs" style="none"/>
					<jdoc:include type="message" />
					<jdoc:include type="component" />
					<jdoc:include type="modules" name="user1" style="xhtml" />
				</div>
				<div class="spacer">&nbsp;</div>
				<?php else : ?>
					<jdoc:include type="module" name="breadcrumbs" style="none"/>
					<jdoc:include type="message" />
					<jdoc:include type="component" />
				<?php endif; ?>
			</div>
			<?php else : ?>
				<jdoc:include type="component" />
			<?php endif; ?>
			<div id="ft">	
				<div id="ftcopy">
					<div id="copy">Alph&eacute;dys Conseil <span style="font-size:123%; font-weight:bold;">&copy;</span> 2010 - <a href="http://www.alphedys.fr/sitemap.html" title="Plan du site">Plan du site</a>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
