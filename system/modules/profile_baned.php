<?php
/* 
	Appointment: Страница заблокирована
	File: profile_ban.php
	Site: maintalk.ru
 
*/
if(!defined('MOZG'))
	die("Hacking attempt!");
	
	$tpl->set('{head}', '<title>Страница заблокирована</title>
<meta name="generator" content="AhiskalilaR Engine" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<noscript><meta http-equiv="refresh" content="0; URL=/badbrowser.php"></noscript>
<link media="screen" href="{theme}/style/style.css" type="text/css" rel="stylesheet" />  
<script type="text/javascript" src="{theme}/js/'.$checkLang.'/lang.js"></script>
<link rel="shortcut icon" href="{theme}/images/fav.png" />');

	$tpl->load_template('profile/baned_owner.tpl');
	if($user_info['user_ban_date'])
		$tpl->set('{date}', langdate('j F Y в H:i', $user_info['user_ban_date']));
	else
		$tpl->set('{date}', 'Неограниченно');
	
	$user_name = explode(' ', $user_info['user_search_pref']);
	$tpl->set('{name}', $user_name[0]);
	$tpl->set('{lastname}', $user_name[1]);

	if($user_info['user_photo']){
		$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$user_info['user_id'].'/200_'.$user_info['user_photo']);
	} else {
		$tpl->set('{ava}', '/templates/Default/images/no_avatars/no_ava_200.gif');
	}
	$tpl->compile('main');
	echo str_replace('{theme}', '/templates/'.$config['temp'], $tpl->result['main']);
	die();
	
?>