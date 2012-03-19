<?php
require_once("./configuration.php");

if (isset($_POST["action"])) {
	$action = htmlentities($_POST["action"]);
	if (isset($_POST["mail"])) {
		$mail = htmlentities($_POST["mail"]);
	}
	if ( $action == "del" && isset($mail) ){
		$id = new JConfig;

		$mysql_server = mysql_connect($id->host, $id->user, $id->password); 
		// on sélectionne la base 
		mysql_select_db($id->db,$mysql_server); 

		// on crée la requête SQL 
		$sql = "INSERT INTO `".$id->db."`.`unsuscribe` (`id`, `mail`, `date`) VALUES (NULL, '".$mail."', CURRENT_TIMESTAMP)";

		// on envoie la requête 
		$req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error()); 
	}
} else $action = '';

if (isset($_GET["mail"])) {
	$mail = htmlentities($_GET["mail"]);
} else { if (!isset($mail)) {$mail = '';} }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Alph&eacute;dys Conseil : d&eacute;sinscription newsletter</title>
		<meta name="google-site-verification" content="-ipI1EM5cV_st0Qlt_DFP_S_dVrhldAHPf46cX3V64o" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
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
		
		<!--<script type="text/javascript">
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-1758468-2']);
		  _gaq.push(['_setAllowAnchor', true]);
		  _gaq.push(['_trackPageview']);

		  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		</script>-->
	</head>
	<body>
		<div id="doc">
			<div id="hd">
				<a href="/"><img style="margin-left: -4px; float:left;" src="templates/alphedys/images/Logo.2010_395x70.png" alt="Alphedys Conseil Logo"/></a>
			</div>
			<div class="frontpage">
				<div class="front">
					<div id="bottom" style="text-align:center;">
			
			<?php
			if  ($action == 'del') {
				echo "Votre adresse email (".$mail.") a bien &eacute;t&eacute; supprim&eacute;e de la liste de diffusion d'Alph&eacute;dys Conseil.";
			} else {
			?>
				<form Method="POST" Action="unsuscribe.php">
					<span>
						Confirmez votre adresse email : 
						<input type="text" name="mail" value="<?php echo $mail ?>" />
						<input type="hidden" name="action" value="del" />
						<input type="submit" style="padding-left: 5px;padding-right: 5px;" value="me d&eacute;sinscrire !"/>
					</span>
				</form>
			<?php } ?>
					</div>
				</div>
			</div>
			<div id="ft">	
				<div id="ftcopy">
					<div id="copy">Alph&eacute;dys Conseil <span style="font-size:123%; font-weight:bold;">&copy;</span> 2010 - <a href="http://www.alphedys.fr/sitemap.html" title="Plan du site">Plan du site</a>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>