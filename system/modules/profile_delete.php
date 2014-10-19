<?php
/* 
	Appointment: Страница удалена
	File: profile_delet.php
 
*/
if(!defined('MOZG'))
	die("Hacking attempt!");

if($user_info['user_group'] != '1'){
	$tpl->load_template('profile/deleted_owner.tpl');

	$tpl->set('{head}', '<title>Страница заблокирована</title>
<meta name="generator" content="AhiskalilaR Engine" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<noscript><meta http-equiv="refresh" content="0; URL=/badbrowser.php"></noscript>
<link media="screen" href="{theme}/style/style.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="{theme}/js/jquery.lib.js"></script>  
<script type="text/javascript" src="{theme}/js/'.$checkLang.'/lang.js"></script>
<script type="text/javascript" src="{theme}/js/main.js"></script>
<link rel="shortcut icon" href="{theme}/images/fav.png" />');
	
	$user_name = explode(' ', $user_info['user_search_pref']);
	$tpl->set('{name}', $user_name[0]);
	$tpl->set('{lastname}', $user_name[1]);
	$tpl->set('{ava}', '/templates/Default/images/no_avatars/no_ava_200.gif');
	
	$tpl->compile('main');
	echo str_replace('{theme}', '/templates/'.$config['temp'], $tpl->result['main']);
	die();
}

?>