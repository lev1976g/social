<?php
/* 
	Appointment: Подключение модулей
	File: mod.php 
 
*/
if(!defined('MOZG'))
	die('Hacking attempt!');

if(isset($_GET['go']))
	$go = htmlspecialchars(strip_tags(stripslashes(trim(urldecode(mysql_escape_string($_GET['go']))))));
else
	$go = "main";

$mozg_module = $go;

check_xss();

switch($go){
	
	//SITE PAGES
	case "pages":
		include ENGINE_DIR.'/modules/pages.php';
	break;
	
	//Регистрация
	case "signup":
		include ENGINE_DIR.'/modules/signup.php';
	break;
	
	//Профиль пользователя
	case "profile":
		include ENGINE_DIR.'/modules/profile.php';
	break;
	
	//Редактирование моей страницы
	case "editprofile":
		include ENGINE_DIR.'/modules/editprofile.php';
	break;
	
	//Загрузка городов
	case "all_cities":
		include ENGINE_DIR.'/modules/all_cities.php';
	break;
	
	//Альбомы
	case "albums":
		if($config['album_mod'] == 'yes')
			include ENGINE_DIR.'/modules/albums.php';
		else {
			msgbox('', 'Сервис отключен.', 'error_yellow');
		}
	break;
	
	//Просмотр фотографии
	case "photo":
		include ENGINE_DIR.'/modules/photo.php';
	break;
	
	//Друзья
	case "friends":
		include ENGINE_DIR.'/modules/friends.php';
	break;
	
	//Закладки
	case "fave":
		include ENGINE_DIR.'/modules/fave.php';
	break;
	
	//Сообщения
	case "messages":
		include ENGINE_DIR.'/modules/messages.php';
	break;
	
	//Диалоги
	case "im":
		include ENGINE_DIR.'/modules/im.php';
	break;
	
	//Подписки
	case "subscriptions":
		include ENGINE_DIR.'/modules/subscriptions.php';
	break;
	
	//Видео
	case "videos":
		if($config['video_mod'] == 'yes')
			include ENGINE_DIR.'/modules/videos.php';
		else {
			msgbox('', 'Сервис отключен.', 'error_yellow');
		}
	break;
	
	//Поиск
	case "search":
		include ENGINE_DIR.'/modules/search.php';
	break;
	
	//Стена
	case "wall":
		include ENGINE_DIR.'/modules/wall.php';
	break;
	
	//Новости
	case "news":
		include ENGINE_DIR.'/modules/news.php';
	break;
	
	//Настройки
	case "settings":
		include ENGINE_DIR.'/modules/settings.php';
	break;
	
	//Удаление страницы
	case "delete_account":
		NoAjaxQuery();
		if($logged){
			$user_id = $user_info['user_id'];
			$db->query("UPDATE `".PREFIX."_users` SET user_delet = 1 WHERE user_id = '".$user_id."'");
			mozg_clear_cache_file('user_'.$user_id.'/profile_'.$user_id);
		}
		die();
	break;
	
	//Воостановление доступа
	case "restore":
		include ENGINE_DIR.'/modules/restore.php';
	break;
	
	//Загрузка картинок при прикриплении файлов со стены, заметок, или сообщений
	case "attach":
		include ENGINE_DIR.'/modules/attach.php';
	break;

	//Баланс
	case "balance":
		include ENGINE_DIR.'/modules/balance.php';
	break;
	
	//Подарки
	case "gifts":
		include ENGINE_DIR.'/modules/gifts.php';
	break;

	//Сообщества
	case "groups":
		include ENGINE_DIR.'/modules/groups.php';
	break;
	
	//Сообщества -> Публичные страницы
	case "public":
		include ENGINE_DIR.'/modules/public.php';
	break;
	
	//Сообщества -> Загрузка фото
	case "attach_groups":
		include ENGINE_DIR.'/modules/attach_groups.php';
	break;

	//Скрываем блок Дни рожденья друзей
	case "happy_friends_block_hide":
		$_SESSION['happy_friends_block_hide'] = 1;
		die();
	break;
	
	//Графический поиск
	case "fast_search":
		include ENGINE_DIR.'/modules/fast_search.php';
	break;

	//Жалобы
	case "report":
		include ENGINE_DIR.'/modules/report.php';
	break;

	//Отправка записи в сообщество или другу
	case "repost":
		include ENGINE_DIR.'/modules/repost.php';
	break;
	
	//Выбор языка
	case "languages":
		include ENGINE_DIR.'/modules/languages.php';
	break;

		default:
			
		if($logged)
				header("Location: /u{$user_info['user_id']}");
			else
				if($go != 'main')
					msgbox('', $lang['no_str_bar'], 'error_yellow');
}

if(!$metatags['title'])
	$metatags['title'] = $config['home'];

?>