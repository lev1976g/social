<?php
/* 
	Appointment: Просмотр фотографии
	File: photo.php 
 
*/
if(!defined('MOZG'))
	die('Hacking attempt!');

if($logged){
	$act = $_GET['act'];
	$user_id = $user_info['user_id'];

	switch($act){
	
		//################### Добавления комментария ###################//
		case "addcomm":
			NoAjaxQuery();
			$pid = intval($_POST['pid']);
			$comment = ajax_utf8(textFilter($_POST['comment']));
			$date = date('Y-m-d H:i:s', $server_time);
			$hash = md5($user_id.$server_time.$_IP.$user_info['user_email'].rand(0, 1000000000)).$comment.$pid;
			
			$check_photo = $db->super_query("SELECT album_id, user_id, photo_name FROM `".PREFIX."_photos` WHERE id = '{$pid}'");

			//Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
			if($user_info['user_id'] != $check_photo['user_id']){
				$check_friend = CheckFriends($check_photo['user_id']);
				
				$row_album = $db->super_query("SELECT privacy FROM `".PREFIX."_albums` WHERE aid = '{$check_photo['album_id']}'");
				$album_privacy = explode('|', $row_album['privacy']);
			}
				
			//ЧС
			$CheckBlackList = CheckBlackList($check_photo['user_id']);
			
			//Проверка на существование фотки и приватность
			if(!$CheckBlackList AND $check_photo AND $album_privacy[1] == 1 OR $album_privacy[1] == 2 AND $check_friend OR $user_info['user_id'] == $check_photo['user_id']){
				$db->query("INSERT INTO `".PREFIX."_photos_comments` (pid, user_id, text, date, hash, album_id, owner_id, photo_name) VALUES ('{$pid}', '{$user_id}', '{$comment}', '{$date}', '{$hash}', '{$check_photo['album_id']}', '{$check_photo['user_id']}', '{$check_photo['photo_name']}')");
				$id = $db->insert_id();
				$db->query("UPDATE `".PREFIX."_photos` SET comm_num = comm_num+1 WHERE id = '{$pid}'");
				$db->query("UPDATE `".PREFIX."_albums` SET comm_num = comm_num+1 WHERE aid = '{$check_photo['album_id']}'");

				$date = langdate('сегодня в H:i', $server_time);
				$tpl->load_template('photos/photo_comment.tpl');
				$tpl->set('{author}', $user_info['user_search_pref']);
				$tpl->set('{comment}', stripslashes($comment));
				$tpl->set('{uid}', $user_id);
				$tpl->set('{hash}', $hash);
				$tpl->set('{id}', $id);
				
				if($user_info['user_photo'])
					$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$user_id.'/50_'.$user_info['user_photo']);
				else
					$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
				
				$tpl->set('{online}', $lang['online']);
				$tpl->set('{date}', langdate('сегодня в H:i', $server_time));
				$tpl->set('[owner]', '');
				$tpl->set('[/owner]', '');
				$tpl->compile('content');
				
				//Добавляем действие в ленту новостей "ответы" владельцу фотографии
				if($user_id != $check_photo['user_id']){
					$comment = str_replace("|", "&#124;", $comment);
					$db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 8, action_text = '{$comment}|{$check_photo['photo_name']}|{$pid}|{$check_photo['album_id']}', obj_id = '{$id}', for_user_id = '{$check_photo['user_id']}', action_time = '{$server_time}'");
					
					//Добавляем +1 юзеру для оповещания
					$cntCacheNews = mozg_cache('user_'.$check_photo['user_id'].'/new_news');
					mozg_create_cache('user_'.$check_photo['user_id'].'/new_news', ($cntCacheNews+1));
									
					//Отправка уведомления на E-mail
					if($config['news_mail_4'] == 'yes'){
						$rowUserEmail = $db->super_query("SELECT user_name, user_email FROM `".PREFIX."_users` WHERE user_id = '".$check_photo['user_id']."'");
						if($rowUserEmail['user_email']){
							include_once ENGINE_DIR.'/classes/mail.php';
							$mail = new dle_mail($config);
							$rowMyInfo = $db->super_query("SELECT user_search_pref FROM `".PREFIX."_users` WHERE user_id = '".$user_id."'");
							$rowEmailTpl = $db->super_query("SELECT text FROM `".PREFIX."_mail_tpl` WHERE id = '4'");
							$rowEmailTpl['text'] = str_replace('{%user%}', $rowUserEmail['user_name'], $rowEmailTpl['text']);
							$rowEmailTpl['text'] = str_replace('{%user-friend%}', $rowMyInfo['user_search_pref'], $rowEmailTpl['text']);
							$rowEmailTpl['text'] = str_replace('{%rec-link%}', $config['home_url'].'photo'.$check_photo['user_id'].'_'.$vid.'_'.$check_photo['album_id'], $rowEmailTpl['text']);
							$mail->send($rowUserEmail['user_email'], 'Новый комментарий к Вашей фотографии', $rowEmailTpl['text']);
						}
					}
				}
				
				//Чистим кеш кол-во комментов
				mozg_mass_clear_cache_file("user_{$check_photo['user_id']}/albums_{$check_photo['user_id']}_comm|user_{$check_photo['user_id']}/albums_{$check_photo['user_id']}_comm_all|user_{$check_photo['user_id']}/albums_{$check_photo['user_id']}_comm_friends");

				AjaxTpl();
			} else
				echo 'err_privacy';
		break;
		
		//################### Удаление комментария ###################//
		case "del_comm":
			NoAjaxQuery();
			$hash = $db->safesql(substr($_POST['hash'], 0, 32));
			$check_comment = $db->super_query("SELECT id, pid, album_id, owner_id FROM `".PREFIX."_photos_comments` WHERE hash = '{$hash}'");
			if($check_comment){
				$db->query("DELETE FROM `".PREFIX."_photos_comments` WHERE hash = '{$hash}'");
				$db->query("DELETE FROM `".PREFIX."_news` WHERE obj_id = '{$check_comment['id']}' AND action_type = 8");
				$db->query("UPDATE `".PREFIX."_photos` SET comm_num = comm_num-1 WHERE id = '{$check_comment['pid']}'");
				$db->query("UPDATE `".PREFIX."_albums` SET comm_num = comm_num-1 WHERE aid = '{$check_comment['album_id']}'");
				
				//Чистим кеш кол-во комментов
				mozg_mass_clear_cache_file("user_{$check_comment['owner_id']}/albums_{$check_comment['owner_id']}_comm|user_{$check_comment['owner_id']}/albums_{$check_comment['owner_id']}_comm_all|user_{$check_comment['owner_id']}/albums_{$check_comment['owner_id']}_comm_friends");
			}
			die();
		break;
				
		//################### Показ всех комментариев ###################//
		case "all_comm":
			NoAjaxQuery();
			$pid = intval($_POST['pid']);
			$num = intval($_POST['num']);
			if($num > 7){
					$limit = $num-3;
					$sql_comm = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.user_id,text,date,id,hash,pid, tb2.user_search_pref, user_photo, user_last_visit FROM `".PREFIX."_photos_comments` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.pid = '{$pid}' ORDER by `date` ASC LIMIT 0, {$limit}", 1);
					
					$tpl->load_template('photos/photo_comment.tpl');
					foreach($sql_comm as $row_comm){
						$tpl->set('{comment}', stripslashes($row_comm['text']));
						$tpl->set('{uid}', $row_comm['user_id']);
						$tpl->set('{id}', $row_comm['id']);
						$tpl->set('{hash}', $row_comm['hash']);
						$tpl->set('{author}', $row_comm['user_search_pref']);
						
						if($row_comm['user_photo'])
							$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comm['user_id'].'/50_'.$row_comm['user_photo']);
						else
							$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
						

						OnlineTpl($row_comm['user_last_visit']);
						megaDate(strtotime($row_comm['date']));
							
						$row_photo = $db->super_query("SELECT user_id FROM `".PREFIX."_photos` WHERE id = '{$row_comm['pid']}'");
						
						if($row_comm['user_id'] == $user_info['user_id'] OR $row_photo['user_id'] == $user_info['user_id']){
							$tpl->set('[owner]', '');
							$tpl->set('[/owner]', '');
						} else
							$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
					
						$tpl->compile('content');
					}
				AjaxTpl();
			}
		break;
		
		//################### Просмотр ПРОСТОЙ фотографии не из альбома ###################//
		case "profile":
			$uid = intval($_POST['uid']);
			
			if($_POST['type'])
				$photo = ROOT_DIR."/uploads/attach/{$uid}/c_{$_POST['photo']}";
			else
				$photo = ROOT_DIR."/uploads/users/{$uid}/{$_POST['photo']}";
			
			if(file_exists($photo)){
				$tpl->load_template('photos/photo_profile.tpl');
				$tpl->set('{uid}', $uid);
				if($_POST['type'])
					$tpl->set('{photo}', "/uploads/attach/{$uid}/{$_POST['photo']}");
				else
					$tpl->set('{photo}', "/uploads/users/{$uid}/{$_POST['photo']}");
				$tpl->set('{close-link}', $_POST['close_link']);
				$tpl->compile('content');
				AjaxTpl();
			} else
				echo 'no_photo';
		break;
		
		default:
		
			//################### Просмотр фотографии ###################//
			NoAjaxQuery();
			$user_id = intval($_POST['uid']);
			$photo_id = intval($_POST['pid']);
			$fuser = intval($_POST['fuser']);
			$section = $_POST['section'];

			//ЧС
			$CheckBlackList = CheckBlackList($user_id);
			if(!$CheckBlackList){
				//Получаем ID альбома
				$check_album = $db->super_query("SELECT album_id FROM `".PREFIX."_photos` WHERE id = '{$photo_id}'");

				//Если фотография вызвана не со стены
				if(!$fuser AND $check_album){
				
					//Проверяем на наличии файла с позициям только для этого фоток
					$check_pos = mozg_cache('user_'.$user_id.'/position_photos_album_'.$check_album['album_id']);
		 
					//Если нету, то вызываем функцию генерации
					if(!$check_pos){
						GenerateAlbumPhotosPosition($user_id, $check_album['album_id']);
						$check_pos = mozg_cache('user_'.$user_id.'/position_photos_album_'.$check_album['album_id']);
					}
						
					$position = xfieldsdataload($check_pos);
				}

				$row = $db->super_query("SELECT tb1.id, photo_name, comm_num, descr, date, position, tb2.user_id, user_photo, user_search_pref, user_country_city_name FROM `".PREFIX."_photos` tb1, `".PREFIX."_users` tb2 WHERE id = '{$photo_id}' AND tb1.user_id = tb2.user_id");

				if($row){
					//Вывод названия альбома, приватноть из БД
					$info_album = $db->super_query("SELECT name, privacy FROM `".PREFIX."_albums` WHERE aid = '{$check_album['album_id']}'");
					$album_privacy = explode('|', $info_album['privacy']);
					
					//Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
					if($user_info['user_id'] != $row['user_id'])
						$check_friend = CheckFriends($row['user_id']);

					//Приватность
					if($album_privacy[0] == 1 OR $album_privacy[0] == 2 AND $check_friend OR $user_info['user_id'] == $row['user_id']){
				
						//Если фотография вызвана не со стены
						if(!$fuser){
							$exp_photo_num = count(explode('||', $check_pos));
							$row_album['photo_num'] = $exp_photo_num-1;
						}

						//Выводим комментарии если они есть
						if($row['comm_num'] > 0){
							$tpl->load_template('photos/photo_comment.tpl');
								
							if($row['comm_num'] > 7)
								$limit_comm = $row['comm_num']-3;
							else
								$limit_comm = 0;
								
							$sql_comm = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.user_id,text,date,id,hash, tb2.user_search_pref, user_photo, user_last_visit FROM `".PREFIX."_photos_comments` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.pid = '{$photo_id}' ORDER by `date` ASC LIMIT {$limit_comm}, {$row['comm_num']}", 1);
							foreach($sql_comm as $row_comm){
								//КНопка Показать полностью..
								$expBR = explode('<br />', $row_comm['text']);
								$textLength = count($expBR);
								$strTXT = strlen($row_comm['text']);
								if($textLength > 9 OR $strTXT > 600)
									$row_comm['text'] = ' <div class="wall_strlen" id="hide_wall_rec'.$row_comm['id'].'">'.$row_comm['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comm['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comm['id'].'">Показать полностью..</div>';
								$tpl->set('{comment}', stripslashes($row_comm['text']));
								$tpl->set('{uid}', $row_comm['user_id']);
								$tpl->set('{id}', $row_comm['id']);
								$tpl->set('{hash}', $row_comm['hash']);
								$tpl->set('{author}', $row_comm['user_search_pref']);
									
								if($row_comm['user_photo'])
									$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comm['user_id'].'/50_'.$row_comm['user_photo']);
								else
									$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
										
								OnlineTpl($row_comm['user_last_visit']);
								megaDate(strtotime($row_comm['date']));
									
								if($row_comm['user_id'] == $user_info['user_id'] OR $row['user_id'] == $user_info['user_id']){
									$tpl->set('[owner]', '');
									$tpl->set('[/owner]', '');
								} else 
									$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
							
								$tpl->compile('comments');
							}
						}

						//Сама фотография
						$tpl->load_template('photos/photo_view.tpl');
						$tpl->set('{photo}', $config['home_url'].'uploads/users/'.$row['user_id'].'/albums/'.$check_album['album_id'].'/'.$row['photo_name'].'?'.$server_time);
						$tpl->set('{descr}', stripslashes($row['descr']));
						$tpl->set('{photo-num}', $row_album['photo_num']);
						$tpl->set('{id}', $row['id']);
						$tpl->set('{aid}', $check_album['album_id']);
						$tpl->set('{album-name}', stripslashes($info_album['name']));
						$tpl->set('{uid}', $row['user_id']);
							
						//Составляем адрес строки который будет после закрытия и опридиляем секцию
						if($section == 'all_comments'){
							$tpl->set('{close-link}', '/albums/comments/'.$row['user_id']);
							$tpl->set('{section}', '_sec=all_comments');
						} elseif($section == 'album_comments'){
							$tpl->set('{close-link}', '/albums/view/'.$check_album['album_id'].'/comments/');
							$tpl->set('{section}', '_'.$check_album['album_id'].'_sec=album_comments');
						} elseif($section == 'user_page'){
							$tpl->set('{close-link}', '/u'.$row['user_id']);
							$tpl->set('{section}', '_sec=user_page');
						} elseif($section == 'wall'){
							$tpl->set('{close-link}', '/u'.$fuser);
							$tpl->set('{section}', '_sec=wall/fuser='.$fuser);
						} elseif($section == 'notes'){
							$tpl->set('{close-link}', '/notes/view/'.$fuser);
							$tpl->set('{section}', '_sec=notes/id='.$fuser);
						} elseif($section == 'loaded'){
							$tpl->set('{close-link}', '/albums/add/'.$check_album['album_id']);
							$tpl->set('{section}', '_sec=loaded');
						} elseif($section == 'news'){
							$fuser = 1;
							$tpl->set('{close-link}', '/news');
							$tpl->set('{section}', '_sec=news');
						} elseif($section == 'msg'){
							$tpl->set('{close-link}', '/messages/show/'.$fuser);
							$tpl->set('{section}', '_sec=msg');
						} elseif($section == 'newphotos'){
							$tpl->set('{close-link}', '/albums/newphotos');
							$tpl->set('{section}', '_'.$check_album['album_id'].'_sec=newphotos');
						} else {
							$tpl->set('{close-link}', '/albums/view/'.$check_album['album_id']);
							$tpl->set('{section}', '_'.$check_album['album_id']);
						}
							
						if(!$fuser){
							$tpl->set('[all]', '');
							$tpl->set('[/all]', '');
							$tpl->set_block("'\\[wall\\](.*?)\\[/wall\\]'si","");
						} else {
							$tpl->set('[wall]', '');
							$tpl->set('[/wall]', '');
							$tpl->set_block("'\\[all\\](.*?)\\[/all\\]'si","");
						}
											
						$tpl->set('{jid}', $row['position']);
						$tpl->set('{comm_num}', ($row['comm_num']-3).' '.gram_record(($row['comm_num']-3), 'comments'));
						$tpl->set('{num}', $row['comm_num']);

						$tpl->set('{author_ava}', $config['home_url'].'uploads/users/'.$row['user_id'].'/50_'.$row['user_photo']);
						$tpl->set('{author}', $row['user_search_pref']);
						$author_info = explode('|', $row['user_country_city_name']);
						
						if($author_info[0]) $tpl->set('{author-info}', $author_info[0]); 
						else $tpl->set('{author-info}', '');
						if($author_info[1]) $tpl->set('{author-info}', $author_info[0].', '.$author_info[1].'<br />');

						megaDate(strtotime($row['date']), 1, 1);

						if($user_id == $user_info['user_id']){
							$tpl->set('[owner]', '');
							$tpl->set('[/owner]', '');
						} else 
							$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

						$tpl->set('{comments}', $tpl->result['comments']);
							
						//Показываем стрелочки если фотографий больше одной и фотография вызвана не со стены
						if($row_album['photo_num'] > 1 && !$fuser){
							
							//Если фотография вызвана из альбом "все фотографии" или вызвана со страницы юзера
							if($row['position'] == $row_album['photo_num'])
								$next_photo = $position[1];
							else
								$next_photo = $position[($row['position']+1)];
									
							if($row['position'] == 1)
								$prev_photo = $position[($row['position']+$row_album['photo_num']-1)];
							else
								$prev_photo = $position[($row['position']-1)];

							$tpl->set('{next-id}', $next_photo);
							$tpl->set('{prev-id}', $prev_photo);
						} else {
							$tpl->set('{next-id}', $row['id']);
							$tpl->set('{prev-id}', $row['id']);
						}

						if($row['comm_num'] < 3){
							$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
						} else {
							$tpl->set('[all-comm]', '');
							$tpl->set('[/all-comm]', '');
						}
							
						//Приватность комментариев
						if($album_privacy[1] == 1 OR $album_privacy[1] == 2 AND $check_friend OR $user_info['user_id'] == $row['user_id']){
							$tpl->set('[add-comm]', '');
							$tpl->set('[/add-comm]', '');
						} else
							$tpl->set_block("'\\[add-comm\\](.*?)\\[/add-comm\\]'si","");
						
						$tpl->compile('content');
							
						AjaxTpl();

					} else
						echo 'err_privacy';
				} else
					echo 'no_photo';
			} else
				echo 'err_privacy';
	}
	$tpl->clear();
	$db->free();
} else
	echo 'no_photo';

die();
?>