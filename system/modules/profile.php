<?php
/* 
	Appointment: Просмотр страницы пользователей
	File: profile.php 
 
*/
if(!defined('MOZG'))
	die('Hacking attempt!');

if($ajax == 'yes')
	NoAjaxQuery();

$user_id = $user_info['user_id'];

if($logged){
	$id = intval($_GET['id']);
	$cache_folder = 'user_'.$id;

	//Читаем кеш
	$row = unserialize(mozg_cache($cache_folder.'/profile_'.$id));

	//Проверяем на наличие кеша, если нету то выводи из БД и создаём его 
	if(!$row){
		$row = $db->super_query("SELECT user_id, user_search_pref, user_country_city_name, user_birthday, user_city, user_country, user_photo, user_friends_num, user_subscriptions_num, followers_num, user_wall_num, user_albums_num, user_last_visit, user_videos_num, user_privacy, user_sex, user_gifts, mobile, twitter, skype, work, user_public_num, user_delet, user_ban_date FROM `".PREFIX."_users` WHERE user_id = '{$id}'");
		$row_online['user_last_visit'] = $row['user_last_visit'];
	} else 
		$row_online = $db->super_query("SELECT user_last_visit FROM `".PREFIX."_users` WHERE user_id = '{$id}'");

	//Если есть такой, юзер то продолжаем выполнение скрипта
	if($row){
		//Если удалена
		if($row['user_delet']){
			$metatags['title'] = $row['user_search_pref'];
			$user_speedbar = $row['user_search_pref'];
			$tpl->load_template("profile/deleted.tpl");
			$user_name_lastname_exp = explode(' ', $row['user_search_pref']);
			$tpl->set('{name}', $user_name_lastname_exp[0]);
			$tpl->set('{lastname}', $user_name_lastname_exp[1]);
			$tpl->compile('content');
		//Если заблокирована
		} else if($row['user_ban_date'] >= $server_time OR $row['user_ban_date'] == '0'){
			$metatags['title'] = $row['user_search_pref'];
			$user_speedbar = $row['user_search_pref'];
			$tpl->load_template("profile/baned.tpl");
			$user_name_lastname_exp = explode(' ', $row['user_search_pref']);
			$tpl->set('{name}', $user_name_lastname_exp[0]);
			$tpl->set('{lastname}', $user_name_lastname_exp[1]);
			//Аватарка
			if($row['user_photo']){
				$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['user_id'].'/200_'.$row['user_photo']);
				$tpl->set('{display-ava}', 'style="display:block;"');
			} else {
				$tpl->set('{ava}', '/templates/Default/images/no_avatars/no_ava_200.gif');
				$tpl->set('{display-ava}', 'style="display:none;"');
			}
			if($row['user_ban_date']){
				$tpl->set('{date}', langdate('j F Y в H:i', $row['user_ban_date']));
			} else {
				$tpl->set('{date}', 'Неограниченно');
			}
			$tpl->compile('content');
		//Если все хорошо, то выводим дальше
		} else {
			$CheckBlackList = CheckBlackList($id);
			$user_privacy = xfieldsdataload($row['user_privacy']);
			$metatags['title'] = $row['user_search_pref'];
			
			$user_name_lastname_exp = explode(' ', $row['user_search_pref']);
			$user_country_city_name_exp = explode('|', $row['user_country_city_name']);

			//################### Друзья ###################//
			if($row['user_friends_num']){
				$sql_friends = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.friend_id, tb2.user_search_pref, user_photo FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.user_id  AND subscriptions = 0 ORDER by rand() DESC LIMIT 0, 8", 1);
				$tpl->load_template('profile/friends.tpl');
				foreach($sql_friends as $row_friends){
					$friend_info = explode(' ', $row_friends['user_search_pref']);
					$tpl->set('{user-id}', $row_friends['friend_id']);
					$tpl->set('{name}', $friend_info[0]);
					$tpl->set('{last-name}', $friend_info[1]);
					if($row_friends['user_photo'])
						$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_friends['friend_id'].'/50_'.$row_friends['user_photo']);
					else
						$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
					$tpl->compile('all_friends');
				}
			}
			
			//################### Интересные страницы ###################//
			if($row['user_public_num']){
				$sql_groups = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.friend_id, tb2.id, title, photo, adres FROM `".PREFIX."_friends` tb1, `".PREFIX."_communities` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.id AND tb1.subscriptions = 2 ORDER by `traf` DESC LIMIT 0, 4", 1, "groups/".$id);
				$tpl->load_template('profile/groups.tpl');
				foreach($sql_groups as $row_groups){
					if($row_groups['adres']){
						$adres = $row_groups['adres'];
					} else {
						$adres = 'public'.$row_groups['id'];
					}
					if($row_groups['photo']){
						$ava_groups = "/uploads/groups/{$row_groups['id']}/50_{$row_groups['photo']}";
					} else {
						$ava_groups = "{theme}/images/no_avatars/no_ava_50.gif";
					}
					$groups .= '<div class="onesubscription onesubscriptio2n cursor_pointer" onClick="Page.Go(\'/'.$adres.'\')"><a href="/'.$adres.'" onClick="Page.Go(this.href); return false"><img src="'.$ava_groups.'" /></a><div class="onesubscriptiontitle"><a href="/'.$adres.'" onClick="Page.Go(this.href); return false">'.stripslashes($row_groups['title']).'</a></div></div>';
					$tpl->set('{user-id}', $adres);
					$tpl->set('{name}', stripslashes($row_groups['title']));
					$tpl->set('{last-name}', '');
					if($row_groups['photo'])
						$tpl->set('{ava}', "/uploads/groups/{$row_groups['id']}/50_{$row_groups['photo']}");
					else
						$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
					$tpl->compile('all_groups');
				}
			} 
				
			//################### Праздники друзей ###################//
			if($user_id == $id AND !$_SESSION['happy_friends_block_hide']){
				$sql_happy_friends = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.friend_id, tb2.user_search_pref, user_photo, user_birthday FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = '".$id."' AND tb1.friend_id = tb2.user_id  AND subscriptions = 0 AND user_day = '".date('j', $server_time)."' AND user_month = '".date('n', $server_time)."' ORDER by `user_last_visit` DESC LIMIT 0, 50", 1);
				$tpl->load_template('profile/profile_happy_friends.tpl');
				$cnt_happfr = 0;
				foreach($sql_happy_friends as $happy_row_friends){
					$cnt_happfr++;
					$tpl->set('{user-id}', $happy_row_friends['friend_id']);
					$tpl->set('{user-name}', $happy_row_friends['user_search_pref']);
					$user_birthday = explode('-', $happy_row_friends['user_birthday']);
					$tpl->set('{user-age}', user_age($user_birthday[0], $user_birthday[1], $user_birthday[2]));
					if($happy_row_friends['user_photo']) $tpl->set('{ava}', '/uploads/users/'.$happy_row_friends['friend_id'].'/100_'.$happy_row_friends['user_photo']);
					else $tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_100.gif');	
					$tpl->compile('happy_all_friends');
				}
			}

			//################### Видеозаписи ###################//
			if($row['user_videos_num']){	
				//Настройки приватности
				if($user_id == $id)
					$sql_privacy = "";
				elseif($check_friend){
					$sql_privacy = "AND privacy regexp '[[:<:]](1|2)[[:>:]]'";
					$cache_pref_videos = "_friends";
				} else {
					$sql_privacy = "AND privacy = 1";
					$cache_pref_videos = "_all";
				}
				
				//Если страницу смотрит другой юзер, то считаем кол-во видео
				if($user_id != $id){
					$video_cnt = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_videos` WHERE owner_user_id = '{$id}' {$sql_privacy}", false, "user_{$id}/videos_num{$cache_pref_videos}");
					$row['user_videos_num'] = $video_cnt['cnt'];
				}
					
				$sql_videos = $db->super_query("SELECT SQL_CALC_FOUND_ROWS id, title, add_date, comm_num, photo FROM `".PREFIX."_videos` WHERE owner_user_id = '{$id}' {$sql_privacy} ORDER by `add_date` DESC LIMIT 0,2", 1, "user_{$id}/page_videos_user{$cache_pref_videos}");
				
				$tpl->load_template('profile/videos.tpl');
				foreach($sql_videos as $row_videos){
					$tpl->set('{photo}', $row_videos['photo'].'jpg');
					$tpl->set('{id}', $row_videos['id']);
					$tpl->set('{user-id}', $id);
					$tpl->set('{title}', stripslashes($row_videos['title']));
					$tpl->set('{comm-num}', $row_videos['comm_num'].' '.gram_record($row_videos['comm_num'], 'comments'));
					megaDate(strtotime($row_videos['add_date']), '');
					$tpl->compile('videos');
				}
			}
			
			//################### Загрузка стены ###################//
			if($row['user_wall_num'])
				include ENGINE_DIR.'/modules/wall.php';
			
			//################### Загрузка самого профиля ###################//
			$tpl->load_template('profile.tpl');
			
			$tpl->set('{language}', $rMyLang);
			$tpl->set('{user-id}', $row['user_id']);
			$tpl->set('{balance}', $user_info['user_balance']);
			$tpl->set('{name}', $user_name_lastname_exp[0]);
			$tpl->set('{lastname}', $user_name_lastname_exp[1]);
			
			//Country, City, Online time
			$tpl->set('{country}', $user_country_city_name_exp[0]);
			$tpl->set('{country-id}', $row['user_country']);
			$tpl->set('{city}', $user_country_city_name_exp[1]);
			$tpl->set('{city-id}', $row['user_city']);
			
			if($row_online['user_last_visit'] >= $online_time)
				$tpl->set('{online}', '<span class="profile_online_green">'.$lang['online'].'</span>');
			else {
				if(date('Y-m-d', $row_online['user_last_visit']) == date('Y-m-d', $server_time))
					$dateTell = langdate('сегодня в H:i', $row_online['user_last_visit']);
				elseif(date('Y-m-d', $row_online['user_last_visit']) == date('Y-m-d', ($server_time-84600)))
					$dateTell = langdate('вчера в H:i', $row_online['user_last_visit']);
				else
					$dateTell = langdate('j F Y в H:i', $row_online['user_last_visit']);
				if($row['user_sex'] == 2)
					$tpl->set('{online}', '<span class="profile_online_red">последний раз была '.$dateTell.'</span>');
				else
					$tpl->set('{online}', '<span class="profile_online_red">последний раз был '.$dateTell.'</span>');
			}
			
			if($row['user_city'] AND $row['user_country']){
				$tpl->set('[not-all-city]','');
				$tpl->set('[/not-all-city]','');
			} else 
				$tpl->set_block("'\\[not-all-city\\](.*?)\\[/not-all-city\\]'si","");
				
			if($row['user_country']){
				$tpl->set('[not-all-country]','');
				$tpl->set('[/not-all-country]','');
			} else 
				$tpl->set_block("'\\[not-all-country\\](.*?)\\[/not-all-country\\]'si","");
			
			//Birthday
			$user_birthday = explode('-', $row['user_birthday']);
			$row['user_day'] = $user_birthday[2];
			$row['user_month'] = $user_birthday[1];
			$row['user_year'] = $user_birthday[0];
			
			if($row['user_day'] > 0 && $row['user_day'] <= 31 && $row['user_month'] > 0 && $row['user_month'] < 13){
				$tpl->set('[not-all-birthday]', '');
				$tpl->set('[/not-all-birthday]', '');
				
				if($row['user_day'] && $row['user_month'] && $row['user_year'] > 1929 && $row['user_year'] < 2012)
					$tpl->set('{birth-day}', '<a href="/?go=search&day='.$row['user_day'].'&month='.$row['user_month'].'&year='.$row['user_year'].'" onClick="Page.Go(this.href); return false">'.langdate('j F Y', strtotime($row['user_year'].'-'.$row['user_month'].'-'.$row['user_day'])).' г.</a>');
				else
					$tpl->set('{birth-day}', '<a href="/?go=search&day='.$row['user_day'].'&month='.$row['user_month'].'" onClick="Page.Go(this.href); return false">'.langdate('j F', strtotime($row['user_year'].'-'.$row['user_month'].'-'.$row['user_day'])).'</a>');
			} else {
				$tpl->set_block("'\\[not-all-birthday\\](.*?)\\[/not-all-birthday\\]'si","");
			}
			
			//Номер телефона
			if($row['mobile']){
				$tpl->set('{mobile}', $row['mobile']);
				$tpl->set('[not-mobile]', '');
				$tpl->set('[/not-mobile]', '');
			} else {
				$tpl->set_block("'\\[not-mobile\\](.*?)\\[/not-mobile\\]'si","");
			}
			
			//Твиттер
			if($row['twitter']){
				$tpl->set('{twitter}', '<a href="http://www.twitter.com/'.$row['twitter'].'" target="_blank">'.$row['twitter'].'</a>');
				$tpl->set('[not-twitter]', '');
				$tpl->set('[/not-twitter]', '');
			} else {
				$tpl->set_block("'\\[not-twitter\\](.*?)\\[/not-twitter\\]'si","");
			}
			
			//SKYPE
			if($row['skype']){
				$tpl->set('{skype}', $row['skype']);
				$tpl->set('[not-skype]', '');
				$tpl->set('[/not-skype]', '');
			} else {
				$tpl->set_block("'\\[not-skype\\](.*?)\\[/not-skype\\]'si","");
			}
			
			//Работа
			if($row['work']){
				$tpl->set('{work}', $row['work']);
				$tpl->set('[not-work]', '');
				$tpl->set('[/not-work]', '');
			} else {
				$tpl->set_block("'\\[not-work\\](.*?)\\[/not-work\\]'si","");
			}
			
			//Показ скрытых текста только для владельца страницы
			if($user_info['user_id'] == $row['user_id']){
				$tpl->set('[owner]', '');
				$tpl->set('[/owner]', '');
				$tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
			} else {
				$tpl->set('[not-owner]', '');
				$tpl->set('[/not-owner]', '');
				$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
			}
			
			//Аватарка
			if($row['user_photo']){
				$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['user_id'].'/200_'.$row['user_photo']);
				$tpl->set('{display-ava}', 'style="display:block;"');
			} else {
				$tpl->set('{ava}', '/templates/Default/images/no_avatars/no_ava_200.gif');
				$tpl->set('{display-ava}', 'style="display:none;"');
			}
			
			if($row['user_subscriptions_num']){
				$tpl->set('{following_display}', 'display_block');
				$tpl->set('{following-num}', $row['user_subscriptions_num']);
			} else {
				$tpl->set('{following_display}', 'no_display');
			}
			
			if($row['followers_num']){
				$tpl->set('{followers_display}', 'display_block');
				$tpl->set('{followers-num}', $row['followers_num']);
			} else {
				$tpl->set('{followers_display}', 'no_display');
			}
				
			 //################### Фотографии ################//
			 $photo_cnt = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_photos` WHERE user_id = '{$id}' ", false);
			 if ($photo_cnt['cnt']){
				 $sql_photos_view = $db->super_query("SELECT * FROM `".PREFIX."_photos` WHERE user_id = '{$id}' ORDER BY id DESC LIMIT 4",1);
				 if($sql_photos_view){
					 foreach($sql_photos_view as $row_view_photos){
						 $photos_view_albums .= "<a onclick=\"Photo.Show(this.href); return false\" href=\"/photo{$row_view_photos['user_id']}_{$row_view_photos['id']}_{$row_view_photos['album_id']}\"><img src=\"/uploads/users/{$row_view_photos['user_id']}/albums/{$row_view_photos['album_id']}/95_{$row_view_photos['photo_name']}\"></a>";
					}
				} 
				$tpl->set('{photos}', $photos_view_albums); 
				$tpl->set('[photos]', '');
				$tpl->set('[/photos]', '');
				$tpl->set('{photos-num}', $photo_cnt['cnt']);
				$tpl->set('{photos-right-num}', $photo_cnt['cnt'].' '.gram_record($photo_cnt['cnt'], 'photos')); 
			}  else if($user_info['user_id'] == $row['user_id']) {
				$photos_view_albums = '<br /><div class="info_center"><img src="{theme}/images/no_uploaded/photos.png" style="width:42px; float:inherit; margin-bottom:2px;" /><br />Нет загруженных фотографий!</div><br /><br />';
				$tpl->set('{photos}', $photos_view_albums); 
				$tpl->set('{photos-num}', '');  
				 if($photo_cnt['cnt']){
					 $tpl->set('{photos-right-num}', $photo_cnt['cnt'].' '.gram_record($photo_cnt['cnt'], 'photos')); 
				 } else {
					 $tpl->set('{photos-right-num}', gram_record($photo_cnt['cnt'], 'photos')); 
				 }
				$tpl->set('[photos]', '');
				$tpl->set('[/photos]', '');
			} else {
				$tpl->set_block("'\\[photos\\](.*?)\\[/photos\\]'si","");
			}
					
			//Делаем проверки на существования запрашиваемого юзера у себя в друзьяз, заклаках, в подписка, делаем всё это если страницу смотрет другой человек
			if($user_id != $id){
			
				$check_friend = CheckFriends($row['user_id']);
				//Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
				if($check_friend){
					$tpl->set('[yes-friends]', '');
					$tpl->set('[/yes-friends]', '');
					$tpl->set_block("'\\[no-friends\\](.*?)\\[/no-friends\\]'si","");
				} else {
					$tpl->set('[no-friends]', '');
					$tpl->set('[/no-friends]', '');
					$tpl->set_block("'\\[yes-friends\\](.*?)\\[/yes-friends\\]'si","");
				}
				
				//Проверка естьли запрашиваемый юзер в закладках у юзера который смотрит стр
				$check_fave = $db->super_query("SELECT user_id FROM `".PREFIX."_fave` WHERE user_id = '{$user_info['user_id']}' AND fave_id = '{$id}'");
				if($check_fave){
					$tpl->set('[yes-fave]', '');
					$tpl->set('[/yes-fave]', '');
					$tpl->set_block("'\\[no-fave\\](.*?)\\[/no-fave\\]'si","");
				} else {
					$tpl->set('[no-fave]', '');
					$tpl->set('[/no-fave]', '');
					$tpl->set_block("'\\[yes-fave\\](.*?)\\[/yes-fave\\]'si","");
				}

				//Проверка естьли запрашиваемый юзер в подписках у юзера который смотрит стр
				$check_subscr = $db->super_query("SELECT user_id FROM `".PREFIX."_friends` WHERE user_id = '{$user_info['user_id']}' AND friend_id = '{$id}' AND subscriptions = 1");
				if($check_subscr){
					$tpl->set('[yes-subscription]', '');
					$tpl->set('[/yes-subscription]', '');
					$tpl->set_block("'\\[no-subscription\\](.*?)\\[/no-subscription\\]'si","");
				} else {
					$tpl->set('[no-subscription]', '');
					$tpl->set('[/no-subscription]', '');
					$tpl->set_block("'\\[yes-subscription\\](.*?)\\[/yes-subscription\\]'si","");
				}
				
				//Проверка естьли запрашиваемый юзер в черном списке
				$MyCheckBlackList = MyCheckBlackList($id);
				if($MyCheckBlackList){
					$tpl->set('[yes-blacklist]', '');
					$tpl->set('[/yes-blacklist]', '');
					$tpl->set_block("'\\[no-blacklist\\](.*?)\\[/no-blacklist\\]'si","");
				} else {
					$tpl->set('[no-blacklist]', '');
					$tpl->set('[/no-blacklist]', '');
					$tpl->set_block("'\\[yes-blacklist\\](.*?)\\[/yes-blacklist\\]'si","");
				}
				
			}

			$author_info = explode(' ', $row['user_search_pref']);
			$tpl->set('{gram-name}', gramatikName($author_info[0]));

			$tpl->set('{online-friends-num}', $online_friends['cnt']);
			
			//Если есть видео то выводим
			if($row['user_videos_num'] AND $config['video_mod'] == 'yes'){
				$tpl->set('[videos]', '');
				$tpl->set('[/videos]', '');
				$tpl->set('{videos}', $tpl->result['videos']);
				$tpl->set('{videos-num}', $row['user_videos_num']);
			} else if($user_info['user_id'] == $row['user_id']) {
				$tpl->set('[videos]', '');
				$tpl->set('[/videos]', '');
				$tpl->set('{videos}', '<br /><div class="info_center"><img src="{theme}/images/no_uploaded/videos.png" style="width:42px; float:inherit; height:42px; margin-bottom:5px;" /><br />Нет загруженных видео!</div><br /><br />');
				$tpl->set('{videos-num}', '');
			} else {
				$tpl->set_block("'\\[videos\\](.*?)\\[/videos\\]'si","");
			}

			//Если есть друзья, то выводим
			if($row['user_friends_num']){
				$tpl->set('[friends]', '');
				$tpl->set('[/friends]', '');
				$tpl->set('{friends}', $tpl->result['all_friends']);
				$tpl->set('{friends-num}', $row['user_friends_num']);
			} else if($user_info['user_id'] == $row['user_id']) {
				$tpl->set('[friends]', '');
				$tpl->set('[/friends]', '');
				$tpl->set('{friends}', '<br /><div class="info_center"><img src="{theme}/images/no_uploaded/friends.png" style="width:42px; float:inherit; height:42px;" /><br />У Вас нет друзей</div><br /><br />');
				$tpl->set('{friends-num}', '');
			} else {
				$tpl->set_block("'\\[friends\\](.*?)\\[/friends\\]'si","");
			}
				
			//Если есть друзья на сайте, то выводим
			if($row['user_public_num']){
				$tpl->set('[groups]', '');
				$tpl->set('[/groups]', '');
				$tpl->set('{groups}', $tpl->result['all_groups']);
				$tpl->set('{groups-num}', $row['user_public_num']);
			} else if($user_info['user_id'] == $row['user_id']) {
				$tpl->set('[groups]', '');
				$tpl->set('[/groups]', '');
				$tpl->set('{groups}', '<br /><div class="info_center"><img src="{theme}/images/no_uploaded/groups.png" style="width:42px; float:inherit; height:42px;" /><br />Вы не состоите в группах</div><br /><br />');
				$tpl->set('{groups-num}', '');
			} else {
				$tpl->set_block("'\\[groups\\](.*?)\\[/groups\\]'si","");
			}
						
			//Если есть gifts, то выводим
			if(!$row['user_gifts']){
				$tpl->set('[no-gifts]', '');
				$tpl->set('[/no-gifts]', '');
			} else
				$tpl->set_block("'\\[no-gifts\\](.*?)\\[/no-gifts\\]'si","");

			//Стена
			$tpl->set('{records}', $tpl->result['wall']);

			if($user_id != $id){
				if($user_privacy['val_wall1'] == 3 OR $user_privacy['val_wall1'] == 2 AND !$check_friend){
					$cnt_rec = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_wall` WHERE for_user_id = '{$id}' AND author_user_id = '{$id}' AND fast_comm_id = 0");
					$row['user_wall_num'] = $cnt_rec['cnt'];
				}
			}
			
			$row['user_wall_num'] = $row['user_wall_num'] ? $row['user_wall_num'] : '';
			if($row['user_wall_num'] > 10){
				$tpl->set('[wall-link]', '');
				$tpl->set('[/wall-link]', '');
			} else
				$tpl->set_block("'\\[wall-link\\](.*?)\\[/wall-link\\]'si","");
			
			$tpl->set('{wall-rec-num}', $row['user_wall_num']);
			
			if($row['user_wall_num'])
				$tpl->set_block("'\\[no-records\\](.*?)\\[/no-records\\]'si","");
			else {
				$tpl->set('[no-records]', '');
				$tpl->set('[/no-records]', '');
			}
			
			//Приватность сообщений
			if($user_privacy['val_msg'] == 1 OR $user_privacy['val_msg'] == 2 AND $check_friend){
				$tpl->set('[privacy-msg]', '');
				$tpl->set('[/privacy-msg]', '');
			} else {
				$tpl->set_block("'\\[privacy-msg\\](.*?)\\[/privacy-msg\\]'si","");
			}

			//Приватность стены
			if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $check_friend OR $user_id == $id){
				$tpl->set('[privacy-wall]', '');
				$tpl->set('[/privacy-wall]', '');
			} else
				$tpl->set_block("'\\[privacy-wall\\](.*?)\\[/privacy-wall\\]'si","");
				
			if($user_privacy['val_wall2'] == 1 OR $user_privacy['val_wall2'] == 2 AND $check_friend OR $user_id == $id){
				$tpl->set('[privacy-wall]', '');
				$tpl->set('[/privacy-wall]', '');
			} else
				$tpl->set_block("'\\[privacy-wall\\](.*?)\\[/privacy-wall\\]'si","");

			//Приватность информации
			if($user_privacy['val_info'] == 1 OR $user_privacy['val_info'] == 2 AND $check_friend OR $user_id == $id){
				$tpl->set('[privacy-info]', '');
				$tpl->set('[/privacy-info]', '');
			} else
				$tpl->set_block("'\\[privacy-info\\](.*?)dfg\\[/privacy-info\\]'si","");
			
			//ЧС
			if(!$CheckBlackList){
				$tpl->set('[blacklist]', '');
				$tpl->set('[/blacklist]', '');
				$tpl->set_block("'\\[not-blacklist\\](.*?)\\[/not-blacklist\\]'si","");
			} else {
				$tpl->set('[not-blacklist]', '');
				$tpl->set('[/not-blacklist]', '');
				$tpl->set_block("'\\[blacklist\\](.*?)\\[/blacklist\\]'si","");
			}
			
			//################### Подарки ###################//
			if($row['user_gifts']){
				$sql_gifts = $db->super_query("SELECT gift FROM `".PREFIX."_gifts` WHERE uid = '{$id}' ORDER by `gdate` DESC LIMIT 0, 3", 1, "user_{$id}/gifts");
				foreach($sql_gifts as $row_gift){
					$gifts .= "<img src=\"/uploads/gifts/{$row_gift['gift']}.png\" width=\"52px;\" style=\"margin-left:10px;\" />";
				}
				$tpl->set('[gifts]', '');
				$tpl->set('[/gifts]', '');
				$tpl->set('{gifts}', $gifts);
				$tpl->set('{gifts-text}', $row['user_gifts'].' '.gram_record($row['user_gifts'], 'gifts'));
			} else {
				$tpl->set_block("'\\[gifts\\](.*?)\\[/gifts\\]'si","");	
			}

			//################### Праздники друзей ###################//
			if($cnt_happfr){
				$tpl->set('{happy-friends}', $tpl->result['happy_all_friends']);
				$tpl->set('{happy-friends-num}', $cnt_happfr);
				$tpl->set('[happy-friends]', '');
				$tpl->set('[/happy-friends]', '');
			} else
				$tpl->set_block("'\\[happy-friends\\](.*?)\\[/happy-friends\\]'si","");
	
			$tpl->compile('content');
		}
	} else {
		msgbox('', $lang['no_upage'], '404');
	}
	
	$tpl->clear();
	$db->free();
} else {
	msgbox('', $lang['not_logged'], 'error_yellow');
}
?>