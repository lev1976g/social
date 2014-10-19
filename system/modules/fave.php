<?php
/* 
	Appointment: Закладки
	File: fave.php 
 
*/
if(!defined('MOZG'))
	die('Hacking attempt!');

if($ajax == 'yes')
	NoAjaxQuery();

if($logged){
	$act = $_GET['act'];
	$user_id = $user_info['user_id'];
	
	if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
	$gcount = 70;
	$limit_page = ($page-1)*$gcount;
	
	$metatags['title'] = $lang['fave'];
	
	switch($act){
		
		//################### Добвление юзера в закладки ###################//
		case "add":
			NoAjaxQuery();
			$fave_id = intval($_POST['fave_id']);
			//Проверяем на факт существования юзера которого добавляем в закладки
			$row = $db->super_query("SELECT `user_id` FROM `".PREFIX."_users` WHERE user_id = '{$fave_id}'");
			if($row AND $user_id != $fave_id){
			
				//Проверям на факт существование этого юзера в закладках, если нету то пропускаем
				$db->query("SELECT `user_id` FROM `".PREFIX."_fave` WHERE user_id = '{$user_id}' AND fave_id = '{$fave_id}'");
				if(!$db->num_rows()){
					$db->query("INSERT INTO `".PREFIX."_fave` SET user_id = '{$user_id}', fave_id = '{$fave_id}', date = NOW()");
					$db->query("UPDATE `".PREFIX."_users` SET user_fave_num = user_fave_num+1 WHERE user_id = '{$user_id}'");
				} else
					echo 'yes_user';
			} else
				echo 'no_user';
			
			die();
		break;
		
		//################### Удаление юзера из закладок ###################//
		case "delet":
			NoAjaxQuery();
			$fave_id = intval($_POST['fave_id']);

			//Проверям на факт существование этого юзера в закладках, если есть то пропускаем
			$row = $db->super_query("SELECT `user_id` FROM `".PREFIX."_fave` WHERE user_id = '{$user_id}' AND fave_id = '{$fave_id}'");
			if($row){
				$db->query("DELETE FROM `".PREFIX."_fave` WHERE user_id = '{$user_id}' AND fave_id = '{$fave_id}'");
				$db->query("UPDATE `".PREFIX."_users` SET user_fave_num = user_fave_num-1 WHERE user_id = '{$user_id}'");
			} else
				echo 'yes_user';

			die();
		break;
		
		default:
		
			//################### Вывод людей которые есть в закладках ###################//
			//Выводим кол-во людей в закладках
			$user = $db->super_query("SELECT SQL_CALC_FOUND_ROWS user_fave_num FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");
			
			//Если кто-то есть в заклаках то выводим
			if($user['user_fave_num']){
				
				//Выводи из базы
				$sql_ = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.fave_id, tb2.user_search_pref, user_photo, user_last_visit FROM `".PREFIX."_fave` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = '{$user_id}' AND tb1.fave_id = tb2.user_id ORDER by `date` ASC LIMIT {$limit_page}, {$gcount}", 1);
				$tpl->load_template('fave.tpl');
				$tpl->result['content'] .= '<table class="food_planner" id="fave_users">';
				foreach($sql_ as $row){
					if($row['user_photo'])
						$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['fave_id'].'/100_'.$row['user_photo']);
					else
						$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_100.gif');
					
					$tpl->set('{name}', $row['user_search_pref']);
					$tpl->set('{user-id}', $row['fave_id']);

					OnlineTpl($row['user_last_visit']);
					
					$tpl->compile('content');
				}
				$tpl->result['content'] .= '</table>';
				navigation($gcount, $user['user_fave_num'], $config['home_url'].'fave/page/');
			} else {
				msgbox('', $lang['no_fave'], 'error_gray');
			}
	}
	$tpl->clear();
	$db->free();
} else {
	msgbox('', $lang['not_logged'], 'error_yellow');
}
?>