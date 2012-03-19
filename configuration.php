<?php
class JConfig {
	var $offline = '0';
	var $editor = 'jce';
	var $list_limit = '20';
	var $helpurl = 'http://help.joomla.org';
	var $debug = '0';
	var $debug_lang = '0';
	var $sef = '1';
	var $sef_rewrite = '0';
	var $sef_suffix = '1';
	var $feed_limit = '10';
	var $feed_email = 'author';
	var $secret = 'WRYVm7cBRe2KAOWe';
	var $gzip = '1';
	var $error_reporting = '30719';
	var $xmlrpc_server = '0';
	var $log_path = '/var/www/alphedys/logs';
	var $tmp_path = '/var/www/alphedys/tmp';
	var $live_site = '';
	var $force_ssl = '0';
	var $offset = '0';
	var $caching = '0';
	var $cachetime = '15';
	var $cache_handler = 'file';
	var $memcache_settings = array();
	var $ftp_enable = '0';
	var $ftp_host = '127.0.0.1';
	var $ftp_port = '21';
	var $ftp_user = '';
	var $ftp_pass = '';
	var $ftp_root = '';
	var $dbtype = 'mysql';
	var $host = '127.0.0.1';
	var $user = 'admin';
	var $db = 'web89db1';
	var $dbprefix = 'jos_';
	var $mailer = 'mail';
	var $mailfrom = 'admin@alphedys.com';
	var $fromname = 'Alphedys';
	var $sendmail = '/usr/sbin/sendmail';
	var $smtpauth = '0';
	var $smtpsecure = 'none';
	var $smtpport = '25';
	var $smtpuser = '';
	var $smtppass = '';
	var $smtphost = 'localhost';
	var $MetaAuthor = '1';
	var $MetaTitle = '1';
	var $lifetime = '15';
	var $session_handler = 'database';
	var $password = 'meuh';
	var $sitename = 'Alphedys Conseil';
	var $MetaDesc = 'Alphédys Conseil, spécialiste du Conseil en Organisation, Prévention des Risques, Bilan Carbone et Conduite du Changement, dans les collectivités locales et les entreprises.';
	var $MetaKeys = 'alphedys, conseil, ressources, humaines, organisation, bilan, carbone, collectivité locale, prévention, risques';
	var $offline_message = 'This site is down for maintenance. Please check back again soon.';
}
?>