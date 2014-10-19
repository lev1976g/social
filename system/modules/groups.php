<?php
/* 
	Appointment: Группы
	File: groups.php 
 
*/
if(!defined('MOZG'))
	die('Hacking attempt!');

if($ajax == 'yes')
	NoAjaxQuery();

if($logged){
	$act = $_GET['act'];
	$user_id = $user_info['user_id'];
	
	if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
	$gcount = 20;
	$limit_page = ($page-1)*$gcount;
	
	$metatags['title'] = $lang['communities'];
	
	switch($act){
		
		//################### Отправка сообщества БД ###################//
		case "send":
			NoAjaxQuery();
			$title = ajax_utf8(textFilter($_POST['title'], false, true));
			if(isset($title) AND !empty($title)){
				$db->query("INSERT INTO `".PREFIX."_communities` SET title = '{$title}', type = 1, traf = 1, ulist = '|{$user_id}|', date = NOW(), admin = 'u{$user_id}|', real_admin = '{$user_id}', comments = 1");
				$cid = $db->insert_id();
				$db->query("INSERT INTO `".PREFIX."_friends` SET friend_id = '{$cid}', user_id = '{$user_id}', friends_date = NOW(), subscriptions = 2");
				$db->query("UPDATE `".PREFIX."_users` SET user_public_num = user_public_num+1 WHERE user_id = '{$user_id}'");
				
				@mkdir(ROOT_DIR.'/uploads/groups/'.$cid.'/', 0777);
				@chmod(ROOT_DIR.'/uploads/groups/'.$cid.'/', 0777);
				
				@mkdir(ROOT_DIR.'/uploads/groups/'.$cid.'/photos/', 0777);
				@chmod(ROOT_DIR.'/uploads/groups/'.$cid.'/photos/', 0777);
				
				mozg_mass_clear_cache_file("user_{$user_id}/profile_{$user_id}|groups/{$user_id}");
				
				echo $cid;
			} else
				echo 'no_title';
				
			die();
		break;

		//################### Страница загрузки главного фото сообщества ###################//
		case "loadphoto_page":
			NoAjaxQuery();
			$tpl->load_template('groups/load_photo.tpl');
			$tpl->set('{id}', $_POST['id']);
			$tpl->compile('content');
			AjaxTpl();
			die();
		break;
		
		//################### Загрузка и изминение главного фото сообщества ###################//
		case "loadphoto":
			NoAjaxQuery();
			
			$id = intval($_GET['id']);
			
			//Проверка на то, что фото обновляет адмиH
			$row = $db->super_query("SELECT admin, photo, del, ban FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			if(stripos($row['admin'], "u{$user_id}|") !== false AND $row['del'] == 0 AND $row['ban'] == 0){
			
				//Разришенные форматы
				$allowed_files = array('jpg', 'jpeg', 'jpe', 'png', 'gif');
				
				//Получаем данные о фотографии
				$image_tmp = $_FILES['uploadfile']['tmp_name'];
				$image_name = totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
				$image_rename = substr(md5($server_time+rand(1,100000)), 0, 20); // имя фотографии
				$image_size = $_FILES['uploadfile']['size']; // размер файла
				$type = end(explode(".", $image_name)); // формат файла
				
				//Проверям если, формат верный то пропускаем
				if(in_array(strtolower($type), $allowed_files)){
					if($image_size < 5000000){
						$res_type = strtolower('.'.$type);
						
						$upload_dir = ROOT_DIR."/uploads/groups/{$id}/";

						if(move_uploaded_file($image_tmp, $upload_dir.$image_rename.$res_type)){
							//Подключаем класс для фотографий
							include ENGINE_DIR.'/classes/images.php';
							
							//Создание оригинала
							$tmb = new thumbnail($upload_dir.$image_rename.$res_type);
							$tmb->size_auto('200', 1);
							$tmb->jpeg_quality('97');
							$tmb->save($upload_dir.$image_rename.$res_type);

							//Создание маленькой копии 100
							$tmb = new thumbnail($upload_dir.$image_rename.$res_type);
							$tmb->size_auto('100x100');
							$tmb->jpeg_quality('100');
							$tmb->save($upload_dir.'100_'.$image_rename.$res_type);
							
							//Создание маленькой копии 50
							$tmb = new thumbnail($upload_dir.$image_rename.$res_type);
							$tmb->size_auto('50x50');
							$tmb->jpeg_quality('100');
							$tmb->save($upload_dir.'50_'.$image_rename.$res_type);

							if($row['photo']){
								@unlink($upload_dir.$row['photo']);
								@unlink($upload_dir.'50_'.$row['photo']);
								@unlink($upload_dir.'100_'.$row['photo']);
							}

							//Вставляем фотографию
							$db->query("UPDATE `".PREFIX."_communities` SET photo = '{$image_rename}{$res_type}' WHERE id = '{$id}'");

							//Результат для ответа
							echo $image_rename.$res_type;
							
							mozg_clear_cache_folder('groups');
						} else
							echo 'big_size';
					} else
						echo 'big_size';
				} else
					echo 'bad_format';
			}
			die();
		break;
		
		//################### Удаление фото сообщества ###################//
		case "delphoto":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			
			//Проверка на то, что фото удалет админ
			$row = $db->super_query("SELECT photo, admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			if(stripos($row['admin'], "u{$user_id}|") !== false){
				$upload_dir = ROOT_DIR."/uploads/groups/{$id}/";
				@unlink($upload_dir.$row['photo']);
				@unlink($upload_dir.'50_'.$row['photo']);
				@unlink($upload_dir.'100_'.$row['photo']);
				$db->query("UPDATE `".PREFIX."_communities` SET photo = '' WHERE id = '{$id}'");
				
				mozg_clear_cache_folder('groups');
			}
			die();
		break;
		
		//################### Вступление в сообщество ###################//
		case "follow":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			
			//Проверка на существования юзера в сообществе
			$row = $db->super_query("SELECT ulist, del, ban FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			if(stripos($row['ulist'], "|{$user_id}|") === false AND $row['del'] == 0 AND $row['ban'] == 0){
				$ulist = $row['ulist']."|{$user_id}|";
				$db->query("UPDATE `".PREFIX."_communities` SET traf = traf+1, ulist = '{$ulist}' WHERE id = '{$id}'");
				$db->query("UPDATE `".PREFIX."_users` SET user_public_num = user_public_num+1 WHERE user_id = '{$user_id}'");
				$db->query("INSERT INTO `".PREFIX."_friends` SET friend_id = '{$id}', user_id = '{$user_id}', friends_date = NOW(), subscriptions = 2");
				
				mozg_mass_clear_cache_file("user_{$user_id}/profile_{$user_id}|groups/{$user_id}");
			}
			die();
		break;
		
		//################### Выход из сообщества ###################//
		case "unfollow":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			
			$check = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_friends` WHERE friend_id = '{$id}' AND user_id = '{$user_id}' AND subscriptions = 2");
			if($check['cnt']){
				$db->query("DELETE FROM `".PREFIX."_friends` WHERE friend_id = '{$id}' AND user_id = '{$user_id}' AND subscriptions = 2");
				$db->query("UPDATE `".PREFIX."_users` SET user_public_num = user_public_num-1 WHERE user_id = '{$user_id}'");
				$db->query("UPDATE `".PREFIX."_communities` SET traf = traf-1, ulist = REPLACE(ulist, '|{$user_id}|', '') WHERE id = '{$id}'");
				
				mozg_mass_clear_cache_file("user_{$user_id}/profile_{$user_id}|groups/{$user_id}");
			}
			die();
		break;
		
		//################### Страница добавления контактов ###################//
		case "addfeedback_pg":
			NoAjaxQuery();
			$tpl->load_template('groups/addfeedback_pg.tpl');
			$tpl->set('{id}', $_POST['id']);
			$tpl->compile('content');
			AjaxTpl();
			die();
		break;
		
		//################### Добавления контакт в БД ###################//
		case "addfeedback_db":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$upage = intval($_POST['upage']);
			$office = ajax_utf8(textFilter($_POST['office'], false, true));
			$phone = ajax_utf8(textFilter($_POST['phone'], false, true));
			$email = ajax_utf8(textFilter($_POST['email'], false, true));
			
			//Проверка на то, что действиие делает админ
			$checkAdmin = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			
			//Проверяем что такой юзер есть на сайте
			$row = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_users` WHERE user_id = '{$upage}'");
			
			//Проверяем на то что юзера нет в списке контактов
			$checkSec = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_feedback` WHERE fuser_id = '{$upage}' AND cid = '{$id}'");

			if($row['cnt'] AND stripos($checkAdmin['admin'], "u{$user_id}|") !== false AND !$checkSec['cnt']){
				$db->query("UPDATE `".PREFIX."_communities` SET feedback = feedback+1 WHERE id = '{$id}'");
				$db->query("INSERT INTO `".PREFIX."_communities_feedback` SET cid = '{$id}', fuser_id = '{$upage}', office = '{$office}', fphone = '{$phone}', femail = '{$email}', fdate = '{$server_time}'");
			} else
				echo 1;
			
			die();
		break;

		//################### Удаление контакта из БД ###################//
		case "delfeedback":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$uid = intval($_POST['uid']);
			
			//Проверка на то, что действиие делает админ
			$checkAdmin = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			
			//Проверяем на то что юзера есть в списке контактов
			$checkSec = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_feedback` WHERE fuser_id = '{$uid}' AND cid = '{$id}'");
			
			if(stripos($checkAdmin['admin'], "u{$user_id}|") !== false AND $checkSec['cnt']){
				$db->query("UPDATE `".PREFIX."_communities` SET feedback = feedback-1 WHERE id = '{$id}'");
				$db->query("DELETE FROM `".PREFIX."_communities_feedback` WHERE fuser_id = '{$uid}' AND cid = '{$id}'");
			}
			
			die();
		break;
		
		//################### Выводим фотографию юзера при указании ИД страницы ###################//
		case "checkFeedUser":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$row = $db->super_query("SELECT user_photo, user_search_pref FROM `".PREFIX."_users` WHERE user_id = '{$id}'");
			if($row) echo $row['user_search_pref']."|".$row['user_photo'];
			die();
		break;
		
		//################### Сохранение отредактированых данных контакт в БД ###################//
		case "editfeeddave":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$upage = intval($_POST['uid']);
			$office = ajax_utf8(textFilter($_POST['office'], false, true));
			$phone = ajax_utf8(textFilter($_POST['phone'], false, true));
			$email = ajax_utf8(textFilter($_POST['email'], false, true));
			
			//Проверка на то, что действиие делает админ
			$checkAdmin = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			
			//Проверяем на то что юзера есть в списке контактов
			$checkSec = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_feedback` WHERE fuser_id = '{$upage}' AND cid = '{$id}'");
			
			if(stripos($checkAdmin['admin'], "u{$user_id}|") !== false AND $checkSec['cnt']){
				$db->query("UPDATE `".PREFIX."_communities_feedback` SET office = '{$office}', fphone = '{$phone}', femail = '{$email}' WHERE fuser_id = '{$upage}' AND cid = '{$id}'");
			} else
				echo 1;
			
			die();
		break;
		
		//################### Все контакты (БОКС) ###################//
		case "allfeedbacklist":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			
			//Выводим ИД админа
			$owner = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			
			$sql_ = $db->super_query("SELECT tb1.fuser_id, office, fphone, femail, tb2.user_search_pref, user_photo FROM `".PREFIX."_communities_feedback` tb1, `".PREFIX."_users` tb2 WHERE tb1.cid = '{$id}' AND tb1.fuser_id = tb2.user_id ORDER by `fdate` ASC", 1);
			$tpl->load_template('groups/allfeedbacklist.tpl');
			if($sql_){
				foreach($sql_ as $row){
					$tpl->set('{id}', $id);
					$tpl->set('{name}', $row['user_search_pref']);
					$tpl->set('{office}', stripslashes($row['office']));
					$tpl->set('{phone}', stripslashes($row['fphone']));
					$tpl->set('{user-id}', $row['fuser_id']);
					if($row['fphone'] AND $row['femail']) $tpl->set('{email}', ', '.stripslashes($row['femail']));
					else $tpl->set('{email}', stripslashes($row['femail']));
					if($row['user_photo']) $tpl->set('{ava}', '/uploads/users/'.$row['fuser_id'].'/50_'.$row['user_photo']);
					else $tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
					if(stripos($owner['admin'], "u{$user_id}|") !== false){
						$tpl->set('[admin]', '');
						$tpl->set('[/admin]', '');
					} else
						$tpl->set_block("'\\[admin\\](.*?)\\[/admin\\]'si","");
					$tpl->compile('content');
				}
				AjaxTpl();
			} else
				echo '<div align="center" style="padding-top:10px;color:#777;font-size:13px;">Список контактов пуст.</div>';

			if(stripos($owner['admin'], "u{$user_id}|") !== false)
				echo "<style>#box_bottom_left_text{padding-top:6px;float:left}</style><script>$('#box_bottom_left_text').html('<a href=\"/\" onClick=\"groups.addcontact({$id}); return false\">Добавить контакт</a>');</script>";
			
			die();
		break;
		
		//################### Сохранение отредактированых данных группы ###################//
		case "saveinfo":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$comments = intval($_POST['comments']);
			$title = ajax_utf8(textFilter($_POST['title'], false, true));
			$adres_page = ajax_utf8(strtolower(textFilter($_POST['adres_page'], false, true)));
			$descr = ajax_utf8(textFilter($_POST['descr'], 5000));
			
			if(!preg_match("/^[a-zA-Z0-9_-]+$/", $adres_page)) $adress_ok = false;
			else $adress_ok = true;

			//Проверка на то, что действиие делает админ
			$checkAdmin = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '".$id."'");

			if(stripos($checkAdmin['admin'], "u{$user_id}|") !== false AND isset($title) AND !empty($title) AND $adress_ok){
				if(preg_match('/public[0-9]/i', $adres_page))
					$adres_page = '';
					
				//Проверка на то, что адрес страницы свободен
				if($adres_page)
					$checkAdres = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities` WHERE adres = '".$adres_page."' AND id != '".$id."'");
				
				if(!$checkAdres['cnt'] OR $adres_page == ''){
					$db->query("UPDATE `".PREFIX."_communities` SET title = '".$title."', descr = '".$descr."', comments = '".$comments."', adres = '".$adres_page."' WHERE id = '".$id."'");
					if(!$adres_page)
						echo 'no_new';
				} else
					echo 'err_adres';
					
				mozg_clear_cache_folder('groups');
			}
			
			die();
		break;
		
		//################### Выводим информацию о пользователе которого будем делать админом ###################//
		case "new_admin":
			NoAjaxQuery();
			$new_admin_id = intval($_POST['new_admin_id']);
			$row = $db->super_query("SELECT tb1.user_id, tb2.user_photo, user_search_pref, user_sex FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = '{$new_admin_id}' AND tb1.user_id = tb2.user_id AND tb1.subscriptions = 2");
			if($row AND $user_id != $new_admin_id){
				if($row['user_photo']) $ava = "/uploads/users/{$new_admin_id}/100_{$row['user_photo']}";
				else $ava = "/templates/{$config['temp']}/images/no_avatars/no_ava_100.gif";
				if($row['user_sex'] == 1) $gram = 'был';
				else $gram = 'была';
				echo "<div style=\"padding:15px\"><img src=\"{$ava}\" align=\"left\" style=\"margin-right:10px\" id=\"adm_ava\" />Вы хотите чтоб <b id=\"adm_name\">{$row['user_search_pref']}</b> {$gram} одним из руководителей страницы?</div>";
			} else
				echo "<div style=\"padding:15px\"><div class=\"red_error\">Пользователь с таким адресом страницы не подписан на эту страницу.</div></div><script>$('#box_but').hide()</script>";
			
			die();
		break;
		
		//################### Запись нового админа в БД ###################//
		case "send_new_admin":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$new_admin_id = intval($_POST['new_admin_id']);
			$row = $db->super_query("SELECT admin, ulist FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			if(stripos($row['admin'], "u{$user_id}|") !== false AND stripos($row['admin'], "u{$new_admin_id}|") === false AND stripos($row['ulist'], "|{$user_id}|") !== false){
				$admin = $row['admin']."u{$new_admin_id}|";
				$db->query("UPDATE `".PREFIX."_communities` SET admin = '{$admin}' WHERE id = '{$id}'");
			}
			die();
		break;
		
		//################### Удаление админа из БД ###################//
		case "deladmin":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$uid = intval($_POST['uid']);
			$row = $db->super_query("SELECT admin, ulist, real_admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			if(stripos($row['admin'], "u{$user_id}|") !== false AND stripos($row['admin'], "u{$uid}|") !== false AND $uid != $row['real_admin']){
				$admin = str_replace("u{$uid}|", '', $row['admin']);
				$db->query("UPDATE `".PREFIX."_communities` SET admin = '{$admin}' WHERE id = '{$id}'");
			}
			die();
		break;
		
		//################### Добавление записи на стену ###################//
		case "wall_send":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$wall_text = ajax_utf8(textFilter($_POST['wall_text']));
			$attach_files = ajax_utf8(textFilter($_POST['attach_files'], false, true));
			
			//Проверка на админа
			$row = $db->super_query("SELECT admin, del, ban FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			if(stripos($row['admin'], "u{$user_id}|") !== false AND isset($wall_text) AND !empty($wall_text) OR isset($attach_files) AND !empty($attach_files) AND $row['del'] == 0 AND $row['ban'] == 0){
		
					//Оприделение изображения к ссылке
					if(stripos($attach_files, 'link|') !== false){
						$attach_arr = explode('||', $attach_files);
						$cnt_attach_link = 1;
						foreach($attach_arr as $attach_file){
							$attach_type = explode('|', $attach_file);
							if($attach_type[0] == 'link' AND preg_match('/http:\/\/(.*?)+$/i', $attach_type[1]) AND $cnt_attach_link == 1){
								$domain_url_name = explode('/', $attach_type[1]);
								$rdomain_url_name = str_replace('http://', '', $domain_url_name[2]);
								$rImgUrl = $attach_type[4];
								$rImgUrl = str_replace("\\", "/", $rImgUrl);
								$img_name_arr = explode(".", $rImgUrl);
								$img_format = totranslit(end($img_name_arr));
								$image_name = substr(md5($server_time.md5($rImgUrl)), 0, 15);
										
								//Разришенные форматы
								$allowed_files = array('jpg', 'jpeg', 'jpe', 'png', 'gif');

								//Загружаем картинку на сайт
								if(in_array(strtolower($img_format), $allowed_files) AND preg_match("/http:\/\/(.*?)(.jpg|.png|.gif|.jpeg|.jpe)/i", $rImgUrl)){
													
									//Директория загрузки фото
									$upload_dir = ROOT_DIR.'/uploads/attach/'.$user_id;
														
									//Если нет папки юзера, то создаём её
									if(!is_dir($upload_dir)){ 
										@mkdir($upload_dir, 0777);
										@chmod($upload_dir, 0777);
									}
														
									//Подключаем класс для фотографий
									include ENGINE_DIR.'/classes/images.php';

									if(@copy($rImgUrl, $upload_dir.'/'.$image_name.'.'.$img_format)){
										$tmb = new thumbnail($upload_dir.'/'.$image_name.'.'.$img_format);
										$tmb->size_auto('100x80');
										$tmb->jpeg_quality(100);
										$tmb->save($upload_dir.'/'.$image_name.'.'.$img_format);
														
										$attach_files = str_replace($attach_type[4], '/uploads/attach/'.$user_id.'/'.$image_name.'.'.$img_format, $attach_files);
									}
								}
								$cnt_attach_link++;
							}
						}
					}
			
				$attach_files = str_replace('vote|', 'hack|', $attach_files);
				$attach_files = str_replace(array('&amp;#124;', '&amp;raquo;', '&amp;quot;'), array('&#124;', '&raquo;', '&quot;'), $attach_files);
							
				//Голосование
				$vote_title = ajax_utf8(textFilter($_POST['vote_title'], false, true));
				$vote_answer_1 = ajax_utf8(textFilter($_POST['vote_answer_1'], false, true));

				$ansers_list = array();
							
				if(isset($vote_title) AND !empty($vote_title) AND isset($vote_answer_1) AND !empty($vote_answer_1)){
								
					for($vote_i = 1; $vote_i <= 10; $vote_i++){
									
						$vote_answer = ajax_utf8(textFilter($_POST['vote_answer_'.$vote_i], false, true));
						$vote_answer = str_replace('|', '&#124;', $vote_answer);
									
						if($vote_answer)
							$ansers_list[] = $vote_answer;
								
					}
								
					$sql_answers_list = implode('|', $ansers_list);
									
					//Вставляем голосование в БД
					$db->query("INSERT INTO `".PREFIX."_votes` SET title = '{$vote_title}', answers = '{$sql_answers_list}'");
									
					$attach_files = $attach_files."vote|{$db->insert_id()}||";
								
				}
				
				//Вставляем саму запись в БД
				$db->query("INSERT INTO `".PREFIX."_communities_wall` SET public_id = '{$id}', text = '{$wall_text}', attach = '{$attach_files}', add_date = '{$server_time}'");
				$dbid = $db->insert_id();
				$db->query("UPDATE `".PREFIX."_communities` SET rec_num = rec_num+1 WHERE id = '{$id}'");
				
				//Вставляем в ленту новотсей
				$db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$id}', action_type = 11, action_text = '{$wall_text}', obj_id = '{$dbid}', action_time = '{$server_time}'");
				
				//Загружаем все записи
				if(stripos($row['admin'], "u{$user_id}|") !== false)
					$public_admin = true;
				else
					$public_admin = false;
			
				$limit_select = 10;
				$pid = $id;
				include ENGINE_DIR.'/classes/wall.public.php';
				$wall = new wall();
				$wall->query("SELECT SQL_CALC_FOUND_ROWS tb1.id, text, public_id, add_date, fasts_num, attach, likes_num, likes_users, tell_uid, public, tell_date, tell_comm, tb2.title, photo, comments FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.public_id = '{$id}' AND tb1.public_id = tb2.id AND fast_comm_id = 0 ORDER by `add_date` DESC LIMIT 0, {$limit_select}");
				$wall->template('groups/record.tpl');
				$wall->compile('content');
				$wall->select($public_admin, $server_time);
				AjaxTpl();
			}
			die();
		break;
		
		//################### Добавление комментария к записи ###################//
		case "wall_send_comm":
			NoAjaxQuery();
			$rec_id = intval($_POST['rec_id']);
			$public_id = intval($_POST['public_id']);
			$wall_text = ajax_utf8(textFilter($_POST['wall_text']));
	
			//Проверка на админа и проверяем включены ли комменты
			$row = $db->super_query("SELECT tb1.fasts_num, tb2.admin, comments FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.public_id = tb2.id AND tb1.id = '{$rec_id}'");
			
			if($row['comments'] OR stripos($row['admin'], "u{$user_id}|") !== false AND isset($wall_text) AND !empty($wall_text)){

				//Вставляем саму запись в БД
				$db->query("INSERT INTO `".PREFIX."_communities_wall` SET public_id = '{$user_id}', text = '{$wall_text}', add_date = '{$server_time}', fast_comm_id = '{$rec_id}'");
				$db->query("UPDATE `".PREFIX."_communities_wall` SET fasts_num = fasts_num+1 WHERE id = '{$rec_id}'");

				$row['fasts_num'] = $row['fasts_num']+1;
				
				if($row['fasts_num'] > 3)
					$comments_limit = $row['fasts_num']-3;
				else
					$comments_limit = 0;
						
				$sql_comments = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.id, public_id, text, add_date, tb2.user_photo, user_search_pref FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.public_id = tb2.user_id AND tb1.fast_comm_id = '{$rec_id}' ORDER by `add_date` ASC LIMIT {$comments_limit}, 3", 1);
				
				//Загружаем кнопку "Показать N запсии"
				$tpl->load_template('groups/record.tpl');
				$tpl->set('{gram-record-all-comm}', gram_record(($row['fasts_num']-3), 'prev').' '.($row['fasts_num']-3).' '.gram_record(($row['fasts_num']-3), 'comments'));
				if($row['fasts_num'] < 4)
					$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
				else {
					$tpl->set('{rec-id}', $rec_id);
					$tpl->set('[all-comm]', '');
					$tpl->set('[/all-comm]', '');
				}
				$tpl->set('{public-id}', $public_id);
				$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
				$tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
				$tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
				$tpl->compile('content');
					
				$tpl->load_template('groups/record.tpl');
				//Сообственно выводим комменты
				foreach($sql_comments as $row_comments){
					$tpl->set('{public-id}', $public_id);
					$tpl->set('{name}', $row_comments['user_search_pref']);
					if($row_comments['user_photo'])
						$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo']);
					else
						$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
					$tpl->set('{comm-id}', $row_comments['id']);
					$tpl->set('{user-id}', $row_comments['public_id']);
					
					$expBR2 = explode('<br />', $row_comments['text']);
					$textLength2 = count($expBR2);
					$strTXT2 = strlen($row_comments['text']);
					if($textLength2 > 6 OR $strTXT2 > 470)
						$row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';
										
					$tpl->set('{text}', stripslashes($row_comments['text']));
					megaDate($row_comments['add_date']);
					if(stripos($row['admin'], "u{$user_id}|") !== false OR $user_id == $row_comments['public_id']){
						$tpl->set('[owner]', '');
						$tpl->set('[/owner]', '');
					} else
						$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
						
					$tpl->set('[comment]', '');
					$tpl->set('[/comment]', '');
					$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
					$tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
					$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
					$tpl->compile('content');
				}
				
				//Загружаем форму ответа
				$tpl->load_template('groups/record.tpl');
				$tpl->set('{rec-id}', $rec_id);
				$tpl->set('{user-id}', $public_id);
				$tpl->set('[comment-form]', '');
				$tpl->set('[/comment-form]', '');
				$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
				$tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
				$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
				$tpl->compile('content');
				
				AjaxTpl();
			}
			die();
		break;
		
		//################### Удаление записи ###################//
		case "wall_del":
			NoAjaxQuery();
			$rec_id = intval($_POST['rec_id']);
			$public_id = intval($_POST['public_id']);

			//Проверка на админа и проверяем включены ли комменты
			if($public_id){
				$row = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");
				$row_rec = $db->super_query("SELECT fast_comm_id, public_id FROM `".PREFIX."_communities_wall` WHERE id = '{$rec_id}'");
			} else
				$row = $db->super_query("SELECT tb1.public_id, attach, tb2.admin FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.public_id = tb2.id AND tb1.id = '{$rec_id}'");

			if(stripos($row['admin'], "u{$user_id}|") !== false OR $user_id == $row_rec['public_id']){
				if($public_id)
					$db->query("UPDATE `".PREFIX."_communities_wall` SET fasts_num = fasts_num-1 WHERE id = '{$row_rec['fast_comm_id']}'");
				else {
					$db->query("DELETE FROM `".PREFIX."_communities_wall` WHERE fast_comm_id = '{$rec_id}'");
					$db->query("DELETE FROM `".PREFIX."_news` WHERE obj_id = '{$rec_id}' AND action_type = '11'");
					$db->query("UPDATE `".PREFIX."_communities` SET rec_num = rec_num-1 WHERE id = '{$row['public_id']}'");
					
					//Удаляем фотку из прикрипленой ссылке, если она есть
					if(stripos($row['attach'], 'link|') !== false){
						$attach_arr = explode('link|', $row['attach']);
						$attach_arr2 = explode('|/uploads/attach/'.$user_id.'/', $attach_arr[1]);
						$attach_arr3 = explode('||', $attach_arr2[1]);
						if($attach_arr3[0])
							@unlink(ROOT_DIR.'/uploads/attach/'.$user_id.'/'.$attach_arr3[0]);	
					}
				}
				
				$db->query("DELETE FROM `".PREFIX."_communities_wall` WHERE id = '{$rec_id}'");
			}
			die();
		break;
		
		//################### Показ всех комментариев к записи ###################//
		case "all_comm":
			NoAjaxQuery();
			$rec_id = intval($_POST['rec_id']);
			$public_id = intval($_POST['public_id']);

			//Проверка на админа и проверяем включены ли комменты
			$row = $db->super_query("SELECT tb2.admin, comments FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.public_id = tb2.id AND tb1.id = '{$rec_id}'");

			if($row['comments'] OR stripos($row['admin'], "u{$user_id}|") !== false){
				$sql_comments = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.id, public_id, text, add_date, tb2.user_photo, user_search_pref FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.public_id = tb2.user_id AND tb1.fast_comm_id = '{$rec_id}' ORDER by `add_date` ASC", 1);
				$tpl->load_template('groups/record.tpl');
				//Сообственно выводим комменты
				foreach($sql_comments as $row_comments){
					$tpl->set('{public-id}', $public_id);
					$tpl->set('{name}', $row_comments['user_search_pref']);
					if($row_comments['user_photo'])
						$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo']);
					else
						$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
					$tpl->set('{comm-id}', $row_comments['id']);
					$tpl->set('{user-id}', $row_comments['public_id']);
					
					$expBR2 = explode('<br />', $row_comments['text']);
					$textLength2 = count($expBR2);
					$strTXT2 = strlen($row_comments['text']);
					if($textLength2 > 6 OR $strTXT2 > 470)
						$row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';
										
					$tpl->set('{text}', stripslashes($row_comments['text']));
					megaDate($row_comments['add_date']);
					if(stripos($row['admin'], "u{$user_id}|") !== false OR $user_id == $row_comments['public_id']){
						$tpl->set('[owner]', '');
						$tpl->set('[/owner]', '');
					} else
						$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
					
					$tpl->set('[comment]', '');
					$tpl->set('[/comment]', '');
					$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
					$tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
					$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
					$tpl->compile('content');
				}
				
				//Загружаем форму ответа
				$tpl->load_template('groups/record.tpl');
				$tpl->set('{rec-id}', $rec_id);
				$tpl->set('{user-id}', $public_id);
				$tpl->set('[comment-form]', '');
				$tpl->set('[/comment-form]', '');
				$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
				$tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
				$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
				$tpl->compile('content');
				
				AjaxTpl();
			}
			die();
		break;
		
		//################### Страница загрузки фото в сообщество ###################//
		case "photos":
			NoAjaxQuery();
			$public_id = intval($_POST['public_id']);
			$rowPublic = $db->super_query("SELECT admin, photos_num FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");
			if(stripos($rowPublic['admin'], "u{$user_id}|") !== false){
				
				if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
				$gcount = 36;
				$limit_page = ($page-1)*$gcount;
			
				//HEAD
				$tpl->load_template('public/photos/head.tpl');
				$tpl->set('{photo-num}', $rowPublic['photos_num'].' '.gram_record($rowPublic['photos_num'], 'photos'));
				$tpl->set('{public_id}', $public_id);
				$tpl->set('[top]', '');
				$tpl->set('[/top]', '');
				$tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
				$tpl->compile('error_yellow');
				
				//Выводим фотографии
				if($rowPublic['photos_num']){
					$sql_ = $db->super_query("SELECT SQL_CALC_FOUND_ROWS photo FROM `".PREFIX."_communities_photos` WHERE public_id = '{$public_id}' ORDER by `add_date` DESC LIMIT {$limit_page}, {$gcount}", 1);
					$tpl->load_template('public/photos/photo.tpl');
					foreach($sql_ as $row){
						$tpl->set('{photo}', $row['photo']);
						$tpl->set('{public-id}', $public_id);
						$tpl->compile('content');
					}
					box_navigation($gcount, $rowPublic['photos_num'], $page, 'groups.wall_attach_addphoto', $public_id);
				} else
					msgbox('', '<div class="clear" style="margin-top:150px;margin-left:27px"></div>В альбоме сообщества нет загруженных фотографий.', 'error_gray');
				
				//BOTTOM
				$tpl->load_template('public/photos/head.tpl');
				$tpl->set('[bottom]', '');
				$tpl->set('[/bottom]', '');
				$tpl->set_block("'\\[top\\](.*?)\\[/top\\]'si","");
				$tpl->compile('content');
				
				AjaxTpl();
			}
			die();
		break;
		
		//################### Выводим инфу о видео при прикриплении видео на стену ###################//
		case "select_video_info":
			NoAjaxQuery();
			$video_id = intval($_POST['video_id']);
			$row = $db->super_query("SELECT photo FROM `".PREFIX."_videos` WHERE id = '".$video_id."'");
			if($row){
				$photo = end(explode('/', $row['photo']));
				echo $photo;
			} else
				echo '1';
			
			die();
		break;
		
		//################### Ставим мне нравится ###################//
		case "wall_like_yes":
			NoAjaxQuery();
			$rec_id = intval($_POST['rec_id']);
			$row = $db->super_query("SELECT likes_users FROM `".PREFIX."_communities_wall` WHERE id = '".$rec_id."'");
			if($row AND stripos($row['likes_users'], "u{$user_id}|") === false){
				$likes_users = "u{$user_id}|".$row['likes_users'];
				$db->query("UPDATE `".PREFIX."_communities_wall` SET likes_num = likes_num+1, likes_users = '{$likes_users}' WHERE id = '".$rec_id."'");
				$db->query("INSERT INTO `".PREFIX."_communities_wall_like` SET rec_id = '".$rec_id."', user_id = '".$user_id."', date = '".$server_time."'");
			}
			die();
		break;
		
		//################### Убераем мне нравится ###################//
		case "wall_like_remove":
			NoAjaxQuery();
			$rec_id = intval($_POST['rec_id']);
			$row = $db->super_query("SELECT likes_users FROM `".PREFIX."_communities_wall` WHERE id = '".$rec_id."'");
			if(stripos($row['likes_users'], "u{$user_id}|") !== false){
				$likes_users = str_replace("u{$user_id}|", '', $row['likes_users']);
				$db->query("UPDATE `".PREFIX."_communities_wall` SET likes_num = likes_num-1, likes_users = '{$likes_users}' WHERE id = '".$rec_id."'");
				$db->query("DELETE FROM `".PREFIX."_communities_wall_like` WHERE rec_id = '".$rec_id."' AND user_id = '".$user_id."'");
			}
			die();
		break;
		
		//################### Выводим последних 7 юзеров кто поставил "Мне нравится" ###################//
		case "wall_like_users_five":
			NoAjaxQuery();
			$rec_id = intval($_POST['rec_id']);
			$sql_ = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.user_id, tb2.user_photo FROM `".PREFIX."_communities_wall_like` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.rec_id = '{$rec_id}' ORDER by `date` DESC LIMIT 0, 7", 1);
			if($sql_){
				foreach($sql_ as $row){
					if($row['user_photo']) $ava = '/uploads/users/'.$row['user_id'].'/50_'.$row['user_photo'];
					else $ava = '/templates/'.$config['temp'].'/images/no_avatars/no_ava_50.gif';
					echo '<a href="/u'.$row['user_id'].'" id="Xlike_user'.$row['user_id'].'_'.$rec_id.'" onClick="Page.Go(this.href); return false"><img src="'.$ava.'" width="32" /></a>';
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
				$sql_ = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.user_id, tb2.user_photo, user_search_pref FROM `".PREFIX."_communities_wall_like` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.rec_id = '{$rid}' ORDER by `date` DESC LIMIT {$limit_page}, {$gcount}", 1);
				
				if($sql_){
					$tpl->load_template('profile_subscription_box_top.tpl');
					$tpl->set('[top]', '');
					$tpl->set('[/top]', '');
					$tpl->set('{subcr-num}', 'Понравилось '.$liked_num.' '.gram_record($liked_num, 'like'));
					$tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
					$tpl->compile('content');
					
					$tpl->result['content'] = str_replace('Всего', '', $tpl->result['content']);
					
					$tpl->load_template('profile_friends.tpl');
					foreach($sql_ as $row){
						if($row['user_photo'])
							$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['user_id'].'/50_'.$row['user_photo']);
						else
							$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
						$follow_user_info = explode(' ', $row['user_search_pref']);
						$tpl->set('{user-id}', $row['user_id']);
						$tpl->set('{name}', $follow_user_info[0]);
						$tpl->set('{last-name}', $follow_user_info[1]);
						$tpl->compile('content');
					}
					box_navigation($gcount, $liked_num, $rid, 'groups.wall_all_liked_users', $liked_num);
					
					AjaxTpl();
				}
			}
			die();
		break;
		
		//################### Рассказать друзьям "Мне нравится" ###################//
		case "wall_tell":
			NoAjaxQuery();
			$rid = intval($_POST['rec_id']);
			
			//Проверка на существование записи
			$row = $db->super_query("SELECT add_date, text, public_id, attach, tell_uid, tell_date, public FROM `".PREFIX."_communities_wall` WHERE fast_comm_id = 0 AND id = '{$rid}'");

			if($row){
				if($row['tell_uid']){
					$row['add_date'] = $row['tell_date'];
					$row['author_user_id'] = $row['tell_uid'];
					$row['public_id'] = $row['tell_uid'];
				} else
					$row['public'] = 1;
						
				//Проверяем на существование этой записи у себя на стене
				$myRow = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_wall` WHERE tell_uid = '{$row['public_id']}' AND tell_date = '{$row['add_date']}' AND author_user_id = '{$user_id}' AND public = '{$row['public']}'");
				if($row['tell_uid'] != $user_id AND $myRow['cnt'] == false){
					$row['text'] = $db->safesql($row['text']);
					$row['attach'] = $db->safesql($row['attach']);
						
					//Всталвяем себе на стену
					$db->query("INSERT INTO `".PREFIX."_wall` SET author_user_id = '{$user_id}', for_user_id = '{$user_id}', text = '{$row['text']}', add_date = '{$server_time}', fast_comm_id = 0, tell_uid = '{$row['public_id']}', tell_date = '{$row['add_date']}', public = '{$row['public']}', attach = '".$row['attach']."'");
					$dbid = $db->insert_id();
					$db->query("UPDATE `".PREFIX."_users` SET user_wall_num = user_wall_num+1 WHERE user_id = '{$user_id}'");
						
					//Вставляем в ленту новостей
					$db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 1, action_text = '{$row['text']}', obj_id = '{$dbid}', action_time = '{$server_time}'");
						
					//Чистим кеш
					mozg_clear_cache_file("user_{$user_id}/profile_{$user_id}");
				} else
					echo 1;
			} else
				echo 1;
				
			die();
		break;
		
		//################### Показ всех подпискок ###################//
		case "all_followers":
			NoAjaxQuery();
			
			if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
			$gcount = 24;
			$limit_page = ($page-1)*$gcount;
			
			$public_id = intval($_POST['public_id']);
			$followers_num = intval($_POST['followers_num']);
			
			$sql_ = $db->super_query("SELECT tb1.user_id, tb2.user_name, user_lastname, user_photo, user_country_city_name FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.friend_id = '{$public_id}' AND tb1.user_id = tb2.user_id AND tb1.subscriptions = 2 ORDER by `friends_date` DESC LIMIT {$limit_page}, {$gcount}", 1);
			
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
					$tpl->set('{user-id}', $row['user_id']);
					$tpl->set('{name}', $row['user_name']);
					$tpl->set('{last-name}', $row['user_lastname']);
					$country_city = explode('|', $row['user_country_city_name']);
					$tpl->set('{info}', $country_city[1].', '.$country_city[0]);
					$tpl->compile('content');

				}
				box_navigation($gcount, $followers_num, $public_id, 'groups.all_people', $followers_num);
				
			}
			
			AjaxTpl();
			
			die();
		break;
		
		//################### Показ всех сообщества юзера на которые он подписан (BOX) ###################//
		case "all_publics":
			if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
			$gcount = 6;
			$limit_page = ($page-1)*$gcount;
			
			$for_user_id = intval($_POST['for_user_id']);
			$following_num = intval($_POST['following_num']);

			$sql_ = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.friend_id, tb2.id, title, photo, traf, adres FROM `".PREFIX."_friends` tb1, `".PREFIX."_communities` tb2 WHERE tb1.user_id = '{$for_user_id}' AND tb1.friend_id = tb2.id AND tb1.subscriptions = 2 ORDER by `traf` DESC LIMIT {$limit_page}, {$gcount}", 1);
			
			if($sql_){
				$tpl->load_template('profile/all_publics_top.tpl');
				$tpl->set('[top]', '');
				$tpl->set('[/top]', '');
				$tpl->set('{following-num}', $following_num.' '.gram_record($following_num, 'following'));
				$tpl->compile('content');		
				foreach($sql_ as $row){
					$tpl->load_template('profile/all_publics.tpl');
					if($row['photo']){
						$tpl->set('{ava}', '/uploads/groups/'.$row['id'].'/50_'.$row['photo']);
					} else {
						$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
					}
					$tpl->set('{user-id}', $row['user_id']);
					$tpl->set('{name}', stripslashes($row['title']));
					$tpl->set('{last-name}', '');
					$tpl->set('{public-id}', $row['id']);
					if($row['adres']){
						$tpl->set('{adres}', $row['adres']);
					} else {
						$tpl->set('{adres}', 'public'.$row['id']);
					}
					$tpl->set('{info}', '<span id="all_publics_num">'.$row['traf'].' '.gram_record($row['traf'], 'followers').'</span>');
					$tpl->compile('content');

				}
				box_navigation($gcount, $following_num, $for_user_id, 'groups.all_publics', $following_num);
				
			}
			
			AjaxTpl();
			
			die();
		break;
		
		default:
		
			//################### Вывод всех сообществ ###################//
			$owner = $db->super_query("SELECT user_public_num FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");
			
			if($act == 'admin'){
				$tpl->load_template('groups/head_admin.tpl');
				$sql_sort = "SELECT SQL_CALC_FOUND_ROWS id, title, photo, traf, adres FROM `".PREFIX."_communities` WHERE admin regexp '[[:<:]](u{$user_id})[[:>:]]' ORDER by `traf` DESC LIMIT {$limit_page}, {$gcount}";
				$sql_count = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities` WHERE admin regexp '[[:<:]](u{$user_id})[[:>:]]'");
				$owner['user_public_num'] = $sql_count['cnt'];
			} else {
				$sql_sort = "SELECT SQL_CALC_FOUND_ROWS tb1.friend_id, tb2.id, title, photo, traf, adres FROM `".PREFIX."_friends` tb1, `".PREFIX."_communities` tb2 WHERE tb1.user_id = '{$user_id}' AND tb1.friend_id = tb2.id AND tb1.subscriptions = 2 ORDER by `traf` DESC LIMIT {$limit_page}, {$gcount}";
				$tpl->load_template('groups/head.tpl');
			}
			
			if($owner['user_public_num']){
				$tpl->set('{num}', $owner['user_public_num'].' '.gram_record($owner['user_public_num'], 'groups'));
				$tpl->set('[yes]', '');
				$tpl->set('[/yes]', '');
				$tpl->set_block("'\\[no\\](.*?)\\[/no\\]'si","");
			} else {
				$tpl->set('[no]', '');
				$tpl->set('[/no]', '');
				$tpl->set_block("'\\[yes\\](.*?)\\[/yes\\]'si","");
			}
			$tpl->compile('error_yellow');
			
			if($owner['user_public_num']){

				$sql_ = $db->super_query($sql_sort, 1);
				
				$tpl->load_template('groups/group.tpl');
				foreach($sql_ as $row){
					$tpl->set('{id}', $row['id']);
					if($row['adres']) $tpl->set('{adres}', $row['adres']);
					else $tpl->set('{adres}', 'public'.$row['id']);
					
					$tpl->set('{name}', stripslashes($row['title']));
					$tpl->set('{traf}', $row['traf'].' '.gram_record($row['traf'], 'groups_users'));
					
					if($act != 'admin'){
						$tpl->set('[admin]', '');
						$tpl->set('[/admin]', '');
					} else
						$tpl->set_block("'\\[admin\\](.*?)\\[/admin\\]'si","");
					
					if($row['photo'])
						$tpl->set('{photo}', "/uploads/groups/{$row['id']}/100_{$row['photo']}");
					else
						$tpl->set('{photo}', "{theme}/images/no_ava_groups_100.gif");
					
					$tpl->compile('content');
				}
				
				if($act == 'admin') $admn_act = 'act=admin&';
				
				navigation($gcount, $owner['user_public_num'], 'groups?'.$admn_act.'page=');
				
			}
	}
	$tpl->clear();
	$db->free();
} else {
	$user_speedbar = $lang['no_infooo'];
	msgbox('', $lang['not_logged'], 'error_yellow');
}
?>