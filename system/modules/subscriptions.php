<?php
/* 
	Appointment: Подписки
	File: subscriptions.php 
 
*/
if(!defined('MOZG'))
	die('Hacking attempt!');

NoAjaxQuery();

if($logged){
	$act = $_GET['act'];
	$user_id = $user_info['user_id'];
	
	switch($act){
		
		//################### Добвление юзера в подписки ###################//
		case "add":
			$for_user_id = intval($_POST['for_user_id']);
			
			//Проверка на существование юзера в подписках
			$check = $db->super_query("SELECT user_id FROM `".PREFIX."_friends` WHERE user_id = '{$user_id}' AND friend_id = '{$for_user_id}' AND subscriptions = 1");
			
			//ЧС
			$CheckBlackList = CheckBlackList($check['user_id']);
				
			if(!$CheckBlackList AND !$check){
				$db->query("INSERT INTO `".PREFIX."_friends` SET user_id = '{$user_id}', friend_id = '{$for_user_id}', friends_date = NOW(), subscriptions = 1");
				$db->query("UPDATE `".PREFIX."_users` SET user_subscriptions_num = user_subscriptions_num+1 WHERE user_id = '{$user_id}'");
				$db->query("UPDATE `".PREFIX."_users` SET followers_num = followers_num+1 WHERE user_id = '{$for_user_id}'");
			}
		break;
		
		//################### Удаление юзера из подписок ###################//
		case "del":
			$del_user_id = intval($_POST['del_user_id']);
			
			//Проверка на существование юзера в подписках
			$check = $db->super_query("SELECT user_id FROM `".PREFIX."_friends` WHERE user_id = '{$user_id}' AND friend_id = '{$del_user_id}' AND subscriptions = 1");
			if($check){
				$db->query("DELETE FROM `".PREFIX."_friends` WHERE user_id = '{$user_id}' AND friend_id = '{$del_user_id}' AND subscriptions = 1");
				$db->query("UPDATE `".PREFIX."_users` SET user_subscriptions_num = user_subscriptions_num-1 WHERE user_id = '{$user_id}'");
				$db->query("UPDATE `".PREFIX."_users` SET followers_num = followers_num-1 WHERE user_id = '{$del_user_id}'");
			}
		break;
		
		//################### Считываем подписчиков ###################//
		case "followers":
			if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
			$for_user_id = intval($_POST['uid']);
			$gcount = 6;
			$limit_page = ($page-1)*$gcount;
			$followers_num = intval($_POST['followers_num']);
			
			$sql_ = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.friend_id, tb2.user_id, user_search_pref, user_photo, user_country_city_name FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.friend_id = '{$for_user_id}' AND tb1.user_id = tb2.user_id AND tb1.subscriptions = 1 ORDER by `friends_date` DESC LIMIT {$limit_page}, {$gcount}", 1);
			
			if($sql_){
				$tpl->load_template('profile/all_followers_top.tpl');
				$tpl->set('[top]', '');
				$tpl->set('[/top]', '');
				$tpl->set('{followers-num}', $followers_num.' '.gram_record($followers_num, 'followers'));
				$tpl->compile('content');		
				foreach($sql_ as $row){
					$tpl->load_template('profile/all_followers.tpl');
					if($row['user_photo']){
						$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['user_id'].'/50_'.$row['user_photo']);
					} else {
						$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
					}
					$follow_user_info = explode(' ', $row['user_search_pref']);
					$tpl->set('{user-id}', $row['user_id']);
					$tpl->set('{name}', $follow_user_info[0]);
					$tpl->set('{last-name}', $follow_user_info[1]);
					$country_city = explode('|', $row['user_country_city_name']);
					$tpl->set('{info}', $country_city[1].', '.$country_city[0]);
					$tpl->compile('content');

				}
				box_navigation($gcount, $followers_num, $for_user_id, 'subscriptions.followers', $followers_num);
			}
			AjaxTpl();
			
		break;
		
		default:
		
			//################### Показ всех подпискок юзера ###################//
			if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
			$gcount = 6;
			$limit_page = ($page-1)*$gcount;
			$for_user_id = intval($_POST['for_user_id']);
			$following_num = intval($_POST['following_num']);
			
			$sql_ = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.friend_id, tb2.user_id, user_search_pref, user_photo, user_country_city_name FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = '{$for_user_id}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 1 ORDER by `friends_date` DESC LIMIT {$limit_page}, {$gcount}", 1);
			
			if($sql_){
				$tpl->load_template('profile/all_following_top.tpl');
				$tpl->set('[top]', '');
				$tpl->set('[/top]', '');
				$tpl->set('{following-num}', $following_num.' '.gram_record($following_num, 'following'));
				$tpl->compile('content');

				foreach($sql_ as $row){
					$tpl->load_template('profile/all_following.tpl');
					if($row['user_photo'])
						$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['friend_id'].'/50_'.$row['user_photo']);
					else
						$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
					$follow_user_info = explode(' ', $row['user_search_pref']);
					$tpl->set('{user-id}', $row['friend_id']);
					$tpl->set('{name}', $follow_user_info[0]);
					$tpl->set('{last-name}', $follow_user_info[1]);
					$country_city = explode('|', $row['user_country_city_name']);
					$tpl->set('{info}', $country_city[1].', '.$country_city[0]);
					$tpl->compile('content');
				}
				box_navigation($gcount, $following_num, $for_user_id, 'subscriptions.all', $following_num);
			}
			AjaxTpl();
	}
	$tpl->clear();
	$db->free();
} else 
	echo 'no_log';
die();
?>