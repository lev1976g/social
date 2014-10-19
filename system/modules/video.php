<?php
/* 
	Appointment: Просмотр видео и комментари к видео
	File: video.php 
 
*/
if(!defined('MOZG'))
	die('Hacking attempt!');

$vid = intval($_POST['vid']);
$close_link = $_POST['close_link'];

//Выводи данные о видео если оно есть
$row = $db->super_query("SELECT tb1.video, title, add_date, descr, owner_user_id, views, privacy, tb2.user_search_pref FROM `".PREFIX."_videos` tb1, `".PREFIX."_users` tb2 WHERE tb1.id = '{$vid}' AND tb1.owner_user_id = tb2.user_id");

if($row){
	//Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
	if($user_id != $get_user_id)
		$check_friend = CheckFriends($row['owner_user_id']);
	
	//Blacklist
	$CheckBlackList = CheckBlackList($row['owner_user_id']);
	
	//Приватность
	if(!$CheckBlackList AND $row['privacy'] == 1 OR $row['privacy'] == 2 AND $check_friend OR $user_info['user_id'] == $row['owner_user_id'])
		$privacy = true;
	else
		$privacy = false;
	
	if($privacy){
		$tpl->load_template('videos/full.tpl');
		$tpl->set('{vid}', $vid);
		$tpl->set('{video}', $row['video']);			
		$tpl->compile('content');
		AjaxTpl();
		exit();	
	} else
		echo 'err_privacy';
} else
	echo 'no_video';
?>