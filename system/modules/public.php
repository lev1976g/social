<?php
/* 
	Appointment: Сообщества -> Публичные страницы
	File: public.php 
 
*/
if(!defined('MOZG'))
	die('Hacking attempt!');

if($ajax == 'yes')
	NoAjaxQuery();

if($logged){
	$user_id = $user_info['user_id'];
	$pid = intval($_GET['pid']);
	
	if(preg_match("/^[a-zA-Z0-9_-]+$/", $_GET['get_adres'])) $get_adres = $db->safesql($_GET['get_adres']);
	
	$sql_where = "id = '".$pid."'";
	
	if($pid){
		$get_adres = '';
		$sql_where = "id = '".$pid."'";
	}
	if($get_adres){
		$pid = '';
		$sql_where = "adres = '".$get_adres."'";
	} else
	
	echo $get_adres;

	//Если страница вывзана через "к предыдущим записям"
	$limit_select = 10;
	if($_POST['page_cnt'] > 0)
		$page_cnt = intval($_POST['page_cnt'])*$limit_select;
	else
		$page_cnt = 0;

	if($page_cnt){
		$row = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$pid}'");
		$row['id'] = $pid;
	} else
		$row = $db->super_query("SELECT id, title, descr, traf, ulist, photo, date, admin, feedback, comments, real_admin, rec_num, del, ban, adres, audio_num FROM `".PREFIX."_communities` WHERE ".$sql_where."");
	
	if($row['del'] == 1){
		msgbox('', '<br /><br />Сообщество удалено администрацией.<br /><br /><br />', 'error_gray');
	} elseif($row['ban'] == 1){
		msgbox('', '<br /><br />Сообщество заблокировано администрацией.<br /><br /><br />', 'error_gray');
	} elseif($row){
		$metatags['title'] = stripslashes($row['title']);
		
		if(stripos($row['admin'], "u{$user_id}|") !== false)
			$public_admin = true;
		else
			$public_admin = false;

		//Стена
		//Если страница вывзана через "к предыдущим записям"
		if($page_cnt)
			NoAjaxQuery();
		
		include ENGINE_DIR.'/classes/wall.public.php';
		$wall = new wall();
		$wall->query("SELECT SQL_CALC_FOUND_ROWS tb1.id, text, public_id, add_date, fasts_num, attach, likes_num, likes_users, tell_uid, public, tell_date, tell_comm, tb2.title, photo, comments, adres FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.public_id = '{$row['id']}' AND tb1.public_id = tb2.id AND fast_comm_id = 0 ORDER by `add_date` DESC LIMIT {$page_cnt}, {$limit_select}");
		$wall->template('groups/record.tpl');
		//Если страница вывзана через "к предыдущим записям"
		if($page_cnt)
			$wall->compile('content');
		else
			$wall->compile('wall');
		$wall->select($public_admin, $server_time);
		
		//Если страница вывзана через "к предыдущим записям"
		if($page_cnt){
			AjaxTpl();
			exit;
		}
				
		//Выводим подписчиков
		if($row['traf']){
			if(!$tpl->result['followers']){
				$sql_users = $db->super_query("SELECT tb1.user_id, tb2.user_name, user_lastname, user_photo, user_country_city_name FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.friend_id = '{$row['id']}' AND tb1.user_id = tb2.user_id AND tb1.subscriptions = 2 ORDER by rand() LIMIT 0, 6", 1);
				$tpl->load_template('public/followers.tpl');
				foreach($sql_users as $row_users){
					$tpl->set('{user-id}', $row_users['friend_id']);
					$tpl->set('{name}', $row_users['user_name']);
					$tpl->set('{last-name}', $row_users['user_lastname']);
					$country_city = explode('|', $row_users['user_country_city_name']);
					$tpl->set('{info}', $country_city[1]);
					if($row_users['user_photo']){
						$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_users['user_id'].'/50_'.$row_users['user_photo']);
					} else {
						$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
					}
					$tpl->compile('followers');
				}
			}
		}
		
		//Выводим контаткы
		if($row['feedback']){
			if(!$tpl->result['feedback']){
				$sql_feedbackusers = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.fuser_id, office, tb2.user_name, user_lastname, user_photo FROM `".PREFIX."_communities_feedback` tb1, `".PREFIX."_users` tb2 WHERE tb1.cid = '{$row['id']}' AND tb1.fuser_id = tb2.user_id ORDER by `fdate` ASC LIMIT 0, 5", 1);
				$tpl->load_template('public/followers.tpl');
				foreach($sql_feedbackusers as $feedback_users){
					$tpl->set('{user-id}', $feedback_users['fuser_id']);
					$tpl->set('{name}', $feedback_users['user_name']);
					$tpl->set('{last-name}', $feedback_users['user_lastname']);
					$feedback_users['office'] = stripslashes($feedback_users['office']);
					$tpl->set('{info}', $feedback_users['office']);
					if($feedback_users['user_photo']){
						$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$feedback_users['fuser_id'].'/50_'.$feedback_users['user_photo']);
					} else {
						$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
					}
					$tpl->compile('feedback');
				}
			}
		}
		
		$tpl->load_template('public/main.tpl');
		
		$tpl->set('{title}', stripslashes($row['title']));
		if($row['photo']){
			$tpl->set('{photo}', "/uploads/groups/{$row['id']}/{$row['photo']}");
			$tpl->set('{display-ava}', '');
		} else {
			$tpl->set('{photo}', "{theme}/images/no_avatars/no_ava_200.gif");
			$tpl->set('{display-ava}', 'no_display');
		}
		
		if($row['descr'])
			$tpl->set('{descr-css}', '');
		else 
			$tpl->set('{descr-css}', 'no_display');
		
		//КНопка Показать полностью..
		$expBR = explode('<br />', $row['descr']);
		$textLength = count($expBR);
		if($textLength > 9)
			$row['descr'] = '<div class="wall_strlen" id="hide_wall_rec'.$row['id'].'">'.$row['descr'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row['id'].', this.id)" id="hide_wall_rec_lnk'.$row['id'].'">Показать полностью..</div>';
				
		$tpl->set('{descr}', stripslashes($row['descr']));
		$tpl->set('{edit-descr}', myBrRn(stripslashes($row['descr'])));
		
		if($row['traf']){
			$tpl->set('{followers-num}', $row['traf']);
			$tpl->set('{no-users}', '');
			$tpl->set('{you_be_one}', 'no_display');
		} else {
			$tpl->set('{followers-num}', '');
			$tpl->set('{no-users}', 'no_display');
			$tpl->set('{you_be_one}', '');
			
		}
		
		//Права админа
		if($public_admin){
			$tpl->set('[admin]', '');
			$tpl->set('[/admin]', '');
		} else
			$tpl->set_block("'\\[admin\\](.*?)\\[/admin\\]'si","");
		
		//Проверка подписан юзер или нет
		if(stripos($row['ulist'], "|{$user_id}|") !== false)
			$tpl->set('{yes}', 'no_display');
		else
			$tpl->set('{no}', 'no_display');
			
		//Контакты
		if($row['feedback']){
			$tpl->set('[yes]', '');
			$tpl->set('[/yes]', '');
			$tpl->set_block("'\\[no\\](.*?)\\[/no\\]'si","");
			$tpl->set('{feedback-users}', $tpl->result['feedback']);
			$tpl->set('[feedback]', '');
			$tpl->set('[/feedback]', '');
		} else {
			$tpl->set('[no]', '');
			$tpl->set('[/no]', '');
			$tpl->set_block("'\\[yes\\](.*?)\\[/yes\\]'si","");
			$tpl->set('{feedback-users}', '');
			if($public_admin){
				$tpl->set('[feedback]', '');
				$tpl->set('[/feedback]', '');
			} else
				$tpl->set_block("'\\[feedback\\](.*?)\\[/feedback\\]'si","");
		}

		$tpl->set('{users}', $tpl->result['followers']);
		
		$tpl->set('{id}', $row['id']);
		megaDate(strtotime($row['date']), 1, 1);
		
		//Комментарии включены
		if($row['comments'])
			$tpl->set('{settings-comments}', 'comments');
		else
			$tpl->set('{settings-comments}', 'none');
			
		//Выводим админов при ред. страницы
		if($public_admin){
			$admins_arr = str_replace('|', '', explode('u', $row['admin']));
			foreach($admins_arr as $admin_id){
				if($admin_id){
					$row_admin = $db->super_query("SELECT user_search_pref, user_photo FROM `".PREFIX."_users` WHERE user_id = '{$admin_id}'");
					if($row_admin['user_photo']) $ava_admin = "/uploads/users/{$admin_id}/50_{$row_admin['user_photo']}";
					else $ava_admin = "{theme}/images/no_avatars/no_ava_50.gif";
					if($admin_id != $row['real_admin']) $admin_del_href = "<a href=\"/\" onClick=\"groups.deladmin('{$row['id']}', '{$admin_id}'); return false\"><small>Удалить</small></a>";
					$adminO .= "<div class=\"public_oneadmin\" id=\"admin{$admin_id}\"><a href=\"/u{$admin_id}\" onClick=\"Page.Go(this.href); return false\"><img src=\"{$ava_admin}\" align=\"left\" width=\"32\" /></a><a href=\"/u{$admin_id}\" onClick=\"Page.Go(this.href); return false\">{$row_admin['user_search_pref']}</a><br />{$admin_del_href}</div>";		
				}
			}
			
			$tpl->set('{admins}', $adminO);
		}

		$tpl->set('{records}', $tpl->result['wall']);
		
		//Стена
		if($row['rec_num'] > 10)
			$tpl->set('{wall-page-display}', '');
		else
			$tpl->set('{wall-page-display}', 'no_display');
			
		if($row['rec_num'])
			$tpl->set('{rec-num}', '<b id="rec_num">'.$row['rec_num'].'</b> '.gram_record($row['rec_num'], 'posts'));
		else {
			$tpl->set('{rec-num}', '<b id="rec_num">Нет записей</b>');
			if($public_admin)
				$tpl->set('{records}', '<div class="wall_none" style="border-top:0px">Новостей пока нет.</div>');
			else
				$tpl->set('{records}', '<div class="wall_none">Новостей пока нет.</div>');
		}
		
		//Выводим информцию о том кто смотрит страницу для себя
		$tpl->set('{viewer-id}', $user_id);
			
		if(!$row['adres']) $row['adres'] = 'public'.$row['id'];
		$tpl->set('{adres}', $row['adres']);
		
		$tpl->compile('content');
	} else {
		msgbox('', $lang['no_upage'], '404');
	}
	
	$tpl->clear();
	$db->free();
} else {
	msgbox('', $lang['not_logged'], 'error_yellow');
}
?>