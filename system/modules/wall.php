<?php
/* 
	Appointment: Стена
	File: wall.php 
 
*/
if(!defined('MOZG'))
	die('Hacking attempt!');
	
if($logged){
	$act = $_GET['act'];
	$user_id = $user_info['user_id'];
	$limit_select = 10;
	$limit_page = 0;
	
	switch($act){
	
		//################### Добвление новой записи на стену ###################//
		case "send":
			NoAjaxQuery();
			$wall_text = ajax_utf8(textFilter($_POST['wall_text']));	
			$attach_files = ajax_utf8(textFilter($_POST['attach_files'], false, true));
			$for_user_id = intval($_POST['for_user_id']);
			$fast_comm_id = intval($_POST['rid']);
			$str_date = time();
			
			//Проверка на наличии юзера которум отправляется запись
			$check = $db->super_query("SELECT user_privacy, user_last_visit FROM `".PREFIX."_users` WHERE user_id = '{$for_user_id}'");
			
			if($check){

				if(isset($wall_text) AND !empty($wall_text) OR isset($attach_files) AND !empty($attach_files)){
					
					//Приватность
					$user_privacy = xfieldsdataload($check['user_privacy']);
					
					//Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
					if($user_privacy['val_wall2'] == 2 OR $user_privacy['val_wall1'] == 2 OR $user_privacy['val_wall3'] == 2 AND $user_id != $for_user_id)
						$check_friend = CheckFriends($for_user_id);

					if(!$fast_comm_id){
						if($user_privacy['val_wall2'] == 1 OR $user_privacy['val_wall2'] == 2 AND $check_friend OR $user_id == $for_user_id)
							$xPrivasy = 1;
						else
							$xPrivasy = 0;
					} else {
						if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $check_friend OR $user_id == $for_user_id)
							$xPrivasy = 1;
						else
							$xPrivasy = 0;
					}
					
					if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $check_friend OR $user_id == $for_user_id)
						$xPrivasyX = 1;
					else
						$xPrivasyX = 0;

					//ЧС
					$CheckBlackList = CheckBlackList($for_user_id);
					if(!$CheckBlackList){
						if($xPrivasy){
							
							$attach_files = str_replace('vote|', 'hack|', $attach_files);
							$attach_files = str_replace(array('&amp;#124;', '&amp;raquo;', '&amp;quot;'), array('&#124;', '&raquo;', '&quot;'), $attach_files);

							//Вставляем саму запись в БД
							$db->query("INSERT INTO `".PREFIX."_wall` SET author_user_id = '{$user_id}', for_user_id = '{$for_user_id}', text = '{$wall_text}', add_date = '{$str_date}', fast_comm_id = '{$fast_comm_id}', attach = '".$attach_files."'");
							$dbid = $db->insert_id();
							
							//Если пользователь пишет сам у себя на стене, то вносим это в "Мои Новости"
							if($user_id == $for_user_id AND !$fast_comm_id){
								$db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 1, action_text = '{$wall_text}', obj_id = '{$dbid}', action_time = '{$str_date}'");
							}
							
							//Если добавляется комментарий к записи то вносим в ленту новостей "ответы"
							if($fast_comm_id){
								//Выводим ид владельца записи
								$row_owner = $db->super_query("SELECT author_user_id FROM `".PREFIX."_wall` WHERE id = '{$fast_comm_id}'");
								
								if($user_id != $row_owner['author_user_id'] AND $row_owner){
									$db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 6, action_text = '{$wall_text}', obj_id = '{$fast_comm_id}', for_user_id = '{$row_owner['author_user_id']}', action_time = '{$str_date}'");

									$cntCacheNews = mozg_cache('user_'.$row_owner['author_user_id'].'/new_news');
									mozg_create_cache('user_'.$row_owner['author_user_id'].'/new_news', ($cntCacheNews+1));
									
									//Отправка уведомления на E-mail
									if($config['news_mail_2'] == 'yes'){
										$rowUserEmail = $db->super_query("SELECT user_name, user_email FROM `".PREFIX."_users` WHERE user_id = '".$row_owner['author_user_id']."'");
										if($rowUserEmail['user_email']){
											include_once ENGINE_DIR.'/classes/mail.php';
											$mail = new dle_mail($config);
											$rowMyInfo = $db->super_query("SELECT user_search_pref FROM `".PREFIX."_users` WHERE user_id = '".$user_id."'");
											$rowEmailTpl = $db->super_query("SELECT text FROM `".PREFIX."_mail_tpl` WHERE id = '2'");
											$rowEmailTpl['text'] = str_replace('{%user%}', $rowUserEmail['user_name'], $rowEmailTpl['text']);
											$rowEmailTpl['text'] = str_replace('{%user-friend%}', $rowMyInfo['user_search_pref'], $rowEmailTpl['text']);
											$rowEmailTpl['text'] = str_replace('{%rec-link%}', $config['home_url'].'wall'.$row_owner['author_user_id'].'_'.$fast_comm_id, $rowEmailTpl['text']);
											$mail->send($rowUserEmail['user_email'], 'Ответ на запись', $rowEmailTpl['text']);
										}
									}
								}
							}

							if($fast_comm_id)
								$db->query("UPDATE `".PREFIX."_wall` SET fasts_num = fasts_num+1 WHERE id = '{$fast_comm_id}'");
							else
								$db->query("UPDATE `".PREFIX."_users` SET user_wall_num = user_wall_num+1 WHERE user_id = '{$for_user_id}'");

							//Подгружаем и объявляем класс для стены
							include ENGINE_DIR.'/classes/wall.php';
							$wall = new wall();
				
							//Если добавлена просто запись, то сразу обновляем все записи на стене
							if(!$fast_comm_id){
									
								if($xPrivasyX){
									$wall->query("SELECT SQL_CALC_FOUND_ROWS tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, type, tell_uid, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE for_user_id = '{$for_user_id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '0' ORDER by `add_date` DESC LIMIT 0, {$limit_select}");
									$wall->template('wall/record.tpl');
									$wall->compile('content');
									$wall->select();
								}
									
								mozg_clear_cache_file('user_'.$for_user_id.'/profile_'.$for_user_id);
								
								//Отправка уведомления на E-mail
								if($config['news_mail_7'] == 'yes' AND $user_id != $for_user_id){
									$rowUserEmail = $db->super_query("SELECT user_name, user_email FROM `".PREFIX."_users` WHERE user_id = '".$for_user_id."'");
									if($rowUserEmail['user_email']){
										include_once ENGINE_DIR.'/classes/mail.php';
										$mail = new dle_mail($config);
										$rowMyInfo = $db->super_query("SELECT user_search_pref FROM `".PREFIX."_users` WHERE user_id = '".$user_id."'");
										$rowEmailTpl = $db->super_query("SELECT text FROM `".PREFIX."_mail_tpl` WHERE id = '7'");
										$rowEmailTpl['text'] = str_replace('{%user%}', $rowUserEmail['user_name'], $rowEmailTpl['text']);
										$rowEmailTpl['text'] = str_replace('{%user-friend%}', $rowMyInfo['user_search_pref'], $rowEmailTpl['text']);
										$rowEmailTpl['text'] = str_replace('{%rec-link%}', $config['home_url'].'wall'.$for_user_id.'_'.$dbid, $rowEmailTpl['text']);
										$mail->send($rowUserEmail['user_email'], 'Новая запись на стене', $rowEmailTpl['text']);
									}
								}
									
							//Если добавлен комментарий к записи то просто обновляем нужную часть, тоесть только часть комментариев, но не всю стену
							} else {
								//Выводим кол-во комментов к записи
								$row = $db->super_query("SELECT fasts_num FROM `".PREFIX."_wall` WHERE id = '{$fast_comm_id}'");
								$record_fasts_num = $row['fasts_num'];
								if($record_fasts_num > 3)
									$limit_comm_num = $row['fasts_num']-3;
								else
									$limit_comm_num = 0;
									
								$wall->comm_query("SELECT SQL_CALC_FOUND_ROWS tb1.id, author_user_id, text, add_date, fasts_num, tb2.user_photo, user_search_pref, user_last_visit FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '{$fast_comm_id}' ORDER by `add_date` ASC LIMIT {$limit_comm_num}, 3");
									
								if($_POST['type'] == 1)
									$wall->comm_template('news/news.tpl');
								else if($_POST['type'] == 2)
									$wall->comm_template('wall/one_record.tpl');
								else
									$wall->comm_template('wall/record.tpl');
									
								$wall->comm_compile('content');
								$wall->comm_select();
							}
							
							AjaxTpl();
							
						} else
							echo 'err_privacy';
					} else
						echo 'err_privacy';
				}
			}

			die();
		break;
		
		//################### Удаление записи со стены ###################//
		case "delet":
			NoAjaxQuery();
			$rid = intval($_POST['rid']);
			//Проверка на существование записи и выводим ID владельца записи и кому предназначена запись
			$row = $db->super_query("SELECT author_user_id, for_user_id, fast_comm_id, add_date, attach FROM `".PREFIX."_wall` WHERE id = '{$rid}'");
			if($row['author_user_id'] == $user_id OR $row['for_user_id'] == $user_id){
				
				//удаляем саму запись
				$db->query("DELETE FROM `".PREFIX."_wall` WHERE id = '{$rid}'");

				//Если удаляется НЕ комментарий к записи
				if(!$row['fast_comm_id']){
					//удаляем комменты к записиы
					$db->query("DELETE FROM `".PREFIX."_wall` WHERE fast_comm_id = '{$rid}'");
					
					//удаляем "мне нравится"
					$db->query("DELETE FROM `".PREFIX."_wall_like` WHERE rec_id = '{$rid}'");
					
					//обновляем кол-во записей
					$db->query("UPDATE `".PREFIX."_users` SET user_wall_num = user_wall_num-1 WHERE user_id = '{$row['for_user_id']}'");
					
					//Чистим кеш
					mozg_clear_cache_file('user_'.$row['for_user_id'].'/profile_'.$row['for_user_id']);
					
					//удаляем из ленты новостей
					$db->query("DELETE FROM `".PREFIX."_news` WHERE obj_id = '{$rid}' AND action_type = 6");
					
					//Удаляем фотку из прикрипленой ссылке, если она есть
					if(stripos($row['attach'], 'link|') !== false){
						$attach_arr = explode('link|', $row['attach']);
						$attach_arr2 = explode('|/uploads/attach/'.$user_id.'/', $attach_arr[1]);
						$attach_arr3 = explode('||', $attach_arr2[1]);
						if($attach_arr3[0])
							@unlink(ROOT_DIR.'/uploads/attach/'.$user_id.'/'.$attach_arr3[0]);	
					}
				
					$action_type = 1;
				}

				//Если удаляется комментарий к записи
				if($row['fast_comm_id']){
					$db->query("UPDATE `".PREFIX."_wall` SET fasts_num = fasts_num-1 WHERE id = '{$row['fast_comm_id']}'");
					$rid = $row['fast_comm_id'];
					$action_type = 6;
				}
				
				//удаляем из ленты новостей
				$db->query("DELETE FROM `".PREFIX."_news` WHERE obj_id = '{$rid}' AND action_time = '{$row['add_date']}' AND action_type = {$action_type}");
			}
			
			die();
		break;
		
		//################### Ставим "Мне нравится" ###################//
		case "like_yes":
			NoAjaxQuery();
			$rid = intval($_POST['rid']);
			//Проверка на существование записи
			$row = $db->super_query("SELECT likes_users, author_user_id FROM `".PREFIX."_wall` WHERE id = '{$rid}'");
			if($row){
				//Проверка на то что этот юзер ставил уже мне нрав или нет
				$likes_users = explode('|', str_replace('u', '', $row['likes_users']));
				if(!in_array($user_id, $likes_users)){
					$db->query("INSERT INTO `".PREFIX."_wall_like` SET rec_id = '{$rid}', user_id = '{$user_id}', date = '{$server_time}'");

					$db->query("UPDATE `".PREFIX."_wall` SET likes_num = likes_num+1, likes_users = '|u{$user_id}|{$row['likes_users']}' WHERE id = '{$rid}'");
					
					//Добавляем в ленту новостей "ответы"
					if($user_id != $row['author_user_id']){
						$generateLastTime = $server_time-10800;
						$row_news = $db->super_query("SELECT ac_id, action_text, action_time FROM `".PREFIX."_news` WHERE action_time > '{$generateLastTime}' AND action_type = 7 AND obj_id = '{$rid}'");
						if($row_news)
							$db->query("UPDATE `".PREFIX."_news` SET action_text = '|u{$user_id}|{$row_news['action_text']}', action_time = '{$server_time}' WHERE obj_id = '{$rid}' AND action_type = 7 AND action_time = '{$row_news['action_time']}'");
						else {
							$db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 7, action_text = '|u{$user_id}|', obj_id = '{$rid}', for_user_id = '{$row['author_user_id']}', action_time = '{$server_time}'");
						}
						
						mozg_create_cache("user_{$row['author_user_id']}/new_news", 1);
					}
				}
			}

			die();
		break;
		
		//################### Удаляем "Мне нравится" ###################//
		case "like_no":
			NoAjaxQuery();
			$rid = intval($_POST['rid']);
			//Проверка на существование записи
			$row = $db->super_query("SELECT likes_users FROM `".PREFIX."_wall` WHERE id = '{$rid}'");
			if($row){
				//Проверка на то что этот юзер ставил уже мне нрав или нет
				$likes_users = explode('|', str_replace('u', '', $row['likes_users']));
				if(in_array($user_id, $likes_users)){
					$db->query("DELETE FROM `".PREFIX."_wall_like` WHERE rec_id = '{$rid}' AND user_id = '{$user_id}'");
					$newListLikesUsers = strtr($row['likes_users'], array('|u'.$user_id.'|' => ''));
					$db->query("UPDATE `".PREFIX."_wall` SET likes_num = likes_num-1, likes_users = '{$newListLikesUsers}' WHERE id = '{$rid}'");
					
					//удаляем из ленты новостей
					$row_news = $db->super_query("SELECT ac_id, action_text FROM `".PREFIX."_news` WHERE action_type = 7 AND obj_id = '{$rid}'");
					$row_news['action_text'] = strtr($row_news['action_text'], array('|u'.$user_id.'|' => ''));
					if($row_news['action_text'])
						$db->query("UPDATE `".PREFIX."_news` SET action_text = '{$row_news['action_text']}' WHERE obj_id = '{$rid}' AND action_type = 7");
					else
						$db->query("DELETE FROM `".PREFIX."_news` WHERE obj_id = '{$rid}' AND action_type = 7");
				}
			}

			die();
		break;
		
		//################### Выводим всех юзеров которые поставили "мне нравится" ###################//
		case "all_liked_users":
			NoAjaxQuery();
			$rid = intval($_POST['rid']);
			$liked_num = intval($_POST['liked_num']);
			
			if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
			$gcount = 24;
			$limit_page = ($page-1)*$gcount;
			
			if(!$liked_num)
				$liked_num = 24;
			
			if($rid AND $liked_num){
				$sql_ = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.user_id, tb2.user_photo, user_search_pref, user_country_city_name FROM `".PREFIX."_wall_like` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.rec_id = '{$rid}' ORDER by `date` DESC LIMIT {$limit_page}, {$gcount}", 1);
				if($sql_){
					$tpl->load_template('wall/liked_users_top.tpl');
					$tpl->set('[top]', '');
					$tpl->set('[/top]', '');
					$tpl->set('{likes-num}', $liked_num.' '.gram_record($liked_num, 'peoples'));
					$tpl->compile('content');
					foreach($sql_ as $row){
						$tpl->load_template('wall/liked_users.tpl');
						if($row['user_photo']){
							$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['user_id'].'/50_'.$row['user_photo']);
						} else {
							$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
						}
						$liked_user_info = explode(' ', $row['user_search_pref']);
						$tpl->set('{user-id}', $row['user_id']);
						$tpl->set('{name}', $liked_user_info[0]);
						$tpl->set('{last-name}', $liked_user_info[1]);
						$country_city = explode('|', $row['user_country_city_name']);
						$tpl->set('{info}', $country_city[1].', '.$country_city[0]);
						$tpl->compile('content');
					}
					box_navigation($gcount, $liked_num, $rid, 'wall.all_liked_users', $liked_num);
				} else {
					$tpl->load_template('wall/liked_users_no.tpl');
					$tpl->compile('content');
				}
				AjaxTpl();
			}
			die();
		break;
		
		//################### Показ всех комментариев к записи ###################//
		case "all_comm":
			NoAjaxQuery();
			$fast_comm_id = intval($_POST['fast_comm_id']);
			$for_user_id = intval($_POST['for_user_id']);
			if($fast_comm_id AND $for_user_id){
				//Подгружаем и объявляем класс для стены
				include ENGINE_DIR.'/classes/wall.php';
				$wall = new wall();
				
				//Проверка на существование получателя
				$row = $db->super_query("SELECT user_privacy FROM `".PREFIX."_users` WHERE user_id = '{$for_user_id}'");
				if($row){
					//Приватность
					$user_privacy = xfieldsdataload($row['user_privacy']);
					
					//Если приватность "Только друщья" то Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
					if($user_privacy['val_wall3'] == 2 AND $user_id != $for_user_id)
						$check_friend = $db->super_query("SELECT user_id FROM `".PREFIX."_friends` WHERE user_id = '{$user_id}' AND friend_id = '{$for_user_id}' AND subscriptions = 0");
						
					if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $check_friend OR $user_id == $for_user_id){
						$wall->comm_query("SELECT SQL_CALC_FOUND_ROWS tb1.id, author_user_id, text, add_date, fasts_num, tb2.user_photo, user_search_pref, user_last_visit FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '{$fast_comm_id}' ORDER by `add_date` ASC LIMIT 0, 200", '');

						if($_POST['type'] == 1)
							$wall->comm_template('news/news.tpl');
						else if($_POST['type'] == 2)
							$wall->comm_template('wall/one_record.tpl');
						else
							$wall->comm_template('wall/record.tpl');
						$wall->comm_compile('content');
						$wall->comm_select();
					
						AjaxTpl();
					} else
						echo 'err_privacy';
				}
			}
			die();
		break;
		
		//################### Показ предыдущих записей ###################//
		case "page":
			NoAjaxQuery();
			$last_id = intval($_POST['last_id']);
			$for_user_id = intval($_POST['for_user_id']);
			
			//ЧС
			$CheckBlackList = CheckBlackList($for_user_id);
				
			if(!$CheckBlackList AND $for_user_id AND $last_id){
				include ENGINE_DIR.'/classes/wall.php';
				$wall = new wall();
				
				//Проверка на существование получателя
				$row = $db->super_query("SELECT user_privacy FROM `".PREFIX."_users` WHERE user_id = '{$for_user_id}'");
				
				if($row){
					//Приватность
					$user_privacy = xfieldsdataload($row['user_privacy']);

					//Если приватность "Только друщья" то Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
					if($user_privacy['val_wall1'] == 2 AND $user_id != $for_user_id)
						$check_friend = $db->super_query("SELECT user_id FROM `".PREFIX."_friends` WHERE user_id = '{$user_id}' AND friend_id = '{$for_user_id}' AND subscriptions = 0");
							
					if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $check_friend OR $user_id == $for_user_id)
						$wall->query("SELECT SQL_CALC_FOUND_ROWS tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, type, tell_uid, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.id < '{$last_id}' AND for_user_id = '{$for_user_id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '0' ORDER by `add_date` DESC LIMIT 0, {$limit_select}");
					else
						$wall->query("SELECT SQL_CALC_FOUND_ROWS tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, type, tell_uid, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.id < '{$last_id}' AND for_user_id = '{$for_user_id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '0' AND tb1.author_user_id = '{$for_user_id}' ORDER by `add_date` DESC LIMIT 0, {$limit_select}");
					
					$wall->template('wall/record.tpl');
					$wall->compile('content');
					$wall->select();
					AjaxTpl();
				}
			}
			die();
		break;
		
			default:

				//################### Показ последних 10 записей ###################//

				//Если вызвана страница стены, не со страницы юзера
				if(!$id){
					$rid = intval($_GET['rid']);
					
					$id = intval($_GET['uid']);
					if(!$id)
						$id = $user_id;
						
					$walluid = $id;
					$metatags['title'] = $lang['wall_title'];

					if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
					$gcount = 10;
					$limit_page = ($page-1)*$gcount;
	
					//Выводим имя юзера и настройки приватности
					$row_user = $db->super_query("SELECT user_name, user_wall_num, user_privacy FROM `".PREFIX."_users` WHERE user_id = '{$id}'");
					$user_privacy = xfieldsdataload($row_user['user_privacy']);

					if($row_user){
						//ЧС
						$CheckBlackList = CheckBlackList($id);
						if(!$CheckBlackList){
							//Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
							if($user_privacy['val_wall1'] == 2 AND $user_id != $id)
								$check_friend = CheckFriends($id);

							if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $check_friend OR $user_id == $id)
								$cnt_rec['cnt'] = $row_user['user_wall_num'];
							else
								$cnt_rec = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_wall` WHERE for_user_id = '{$id}' AND author_user_id = '{$id}' AND fast_comm_id = 0");
								
							if($_GET['type'] == 'own'){
								$cnt_rec = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_wall` WHERE for_user_id = '{$id}' AND author_user_id = '{$id}' AND fast_comm_id = 0");
								$where_sql = "AND tb1.author_user_id = '{$id}'";
								$tpl->set_block("'\\[record-tab\\](.*?)\\[/record-tab\\]'si","");
								$page_type = '/wall'.$id.'_sec=own&page=';
							} else if($_GET['type'] == 'record'){
								$where_sql = "AND tb1.id = '{$rid}'";
								$tpl->set('[record-tab]', '');
								$tpl->set('[/record-tab]', '');
								$wallAuthorId = $db->super_query("SELECT author_user_id FROM `".PREFIX."_wall` WHERE id = '{$rid}'");
							} else {
								$_GET['type'] = '';
								$where_sql = '';
								$tpl->set_block("'\\[record-tab\\](.*?)\\[/record-tab\\]'si","");
								$page_type = '/wall'.$id.'/page/';
							}

							if($cnt_rec['cnt'] > 0)
								$user_speedbar = 'На стене '.$cnt_rec['cnt'].' '.gram_record($cnt_rec['cnt'], 'rec');

							$tpl->load_template('wall/head.tpl');
							$tpl->set('{name}', gramatikName($row_user['user_name']));
							$tpl->set('{uid}', $id);
							$tpl->set('{rec-id}', $rid);
							$tpl->set("{activetab-{$_GET['type']}}", 'activetab');
							$tpl->compile('error_yellow');
							
							if($cnt_rec['cnt'] < 1)
								msgbox('', $lang['wall_no_rec'], 'error_gray');
						} else {
							$user_speedbar = $lang['error'];
							msgbox('', $lang['no_notes'], 'error_yellow');
						}
					} else
						msgbox('', $lang['wall_no_rec'], 'error_gray');
				}

				if(!$CheckBlackList){
					include ENGINE_DIR.'/classes/wall.php';
					$wall = new wall();
					
						if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $check_friend OR $user_id == $id)
							$wall->query("SELECT SQL_CALC_FOUND_ROWS tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 {$where_sql} ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}");
						elseif($wallAuthorId['author_user_id'] == $id)
							$wall->query("SELECT SQL_CALC_FOUND_ROWS tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 {$where_sql} ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}");
						else {
							$wall->query("SELECT SQL_CALC_FOUND_ROWS tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 AND tb1.author_user_id = '{$id}' ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}");
							if($wallAuthorId['author_user_id'])
								$Hacking = true;
						}
					//Если вызвана страница стены, не со страницы юзера
					if(!$Hacking){
						if($rid OR $walluid){
							$wall->template('wall/one_record.tpl');
							$wall->compile('content');
							$wall->select();

							if($cnt_rec['cnt'] > $gcount AND $_GET['type'] == '' OR $_GET['type'] == 'own')
								navigation($gcount, $cnt_rec['cnt'], $page_type);
						} else {
							$wall->template('wall/record.tpl');
							$wall->compile('wall');
							$wall->select();
						}
					}
				}
	}
	$tpl->clear();
	$db->free();
} else
	echo 'no_log';
?>