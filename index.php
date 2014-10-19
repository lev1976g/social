<?php
@session_start();
@ob_start();
@ob_implicit_flush(0);

@error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

define('MOZG', true);
define('ROOT_DIR', dirname (__FILE__));
define('ENGINE_DIR', ROOT_DIR.'/system');

header('Content-type: text/html; charset=utf-8');
	
//AJAX
$ajax = $_POST['ajax'];

$logged = false;
$user_info = false;

include ENGINE_DIR.'/initializer.php';

//Опредиления браузера
if(stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0')) $BadBrowser = 'ie6';
elseif(stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0')) $BadBrowser = 'ie7';
elseif(stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0')) $BadBrowser = 'ie8';
elseif(stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 9.0')) $BadBrowser = 'ie9';
elseif(stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 10.0')) $BadBrowser = 'ie10';
elseif(stristr($_SERVER['HTTP_USER_AGENT'], 'Trident/7')) $BadBrowser = 'ie11';
if($BadBrowser == 'ie6' OR $BadBrowser == 'ie7' OR $BadBrowser == 'ie8' OR $BadBrowser == 'ie9' OR $BadBrowser == 'ie10' OR $BadBrowser == 'ie11')
	header("Location: /badbrowser.php");

//Загружаем кол-во новых новостей
$CacheNews = mozg_cache('user_'.$user_info['user_id'].'/new_news');
if($CacheNews){
	$new_news = "<div class=\"top_notification\">{$CacheNews}</div>";
	$new_news_link = '/notifications';
} else {
	$new_news = "";
	$new_news_link = '';
}

//Загружаем кол-во новых подарков
$CacheGift = mozg_cache('user_'.$user_info['user_id'].'/new_gifts');
if($CacheGift){
	$new_gifts = "<div class=\"top_notification\">{$CacheGift}</div>";
	$new_gifts_link = "/gifts{$user_info['user_id']}?new=1";
} else {
	$new_gifts = "";
	$new_gifts_link = "/gifts{$user_info['user_id']}";
}
	
//Новые друзья
$user_friends_demands = $user_info['user_friends_demands'];
if($user_friends_demands){
	$new_demands = "<div class=\"top_notification\">{$user_friends_demands}</div>";
	$new_demands_link = '/requests';
} else {
	$new_demands = '';
	$new_demands_link = '';
}

//Новые сообщения
$user_pm_num = $user_info['user_pm_num'];
if($user_pm_num)
	$user_pm_num = "<div class=\"top_notification\">{$user_pm_num}</div>";
else
	$user_pm_num = '';

//Если включен AJAX то загружаем стр.
if($ajax == 'yes'){

	//Если есть POST Запрос и значение AJAX, а $ajax не равняется "yes" то не пропускаем
	if($_SERVER['REQUEST_METHOD'] == 'POST' AND $ajax != 'yes')
		die('Неизвестная ошибка');

	$result_ajax = "
<script type='text/javascript'>
document.title = '{$metatags['title']}';
document.getElementById('new_msg').innerHTML = '{$user_pm_num}';
document.getElementById('new_news').innerHTML = '{$new_news}';
document.getElementById('new_news_link').setAttribute('href', '/news{$new_news_link}');
document.getElementById('new_gifts').innerHTML = '{$new_gifts}';
document.getElementById('new_demands').innerHTML = '{$new_demands}';
document.getElementById('new_demands_link').setAttribute('href', '/friends{$new_demands_link}');
document.getElementById('new_gifts_link').setAttribute('href', '{$new_gifts_link}');
</script>
{$tpl->result['error_yellow']}{$tpl->result['content']}
";
	echo str_replace('{theme}', '/templates/'.$config['temp'], $result_ajax);

	$tpl->global_clear();
	$db->close();
		
	die();
} 

//Если обращение к модулю регистрации или главной и юзер не авторизован то показываем регистрацию
if($go == 'signup' OR $go == 'main' AND !$logged){
	
	$tpl->load_template('home.tpl');
	$sql_country = $db->super_query("SELECT SQL_CALC_FOUND_ROWS * FROM `".PREFIX."_country` ORDER by `name` ASC", true, "country", true);
	foreach($sql_country as $row_country){
		$all_country .= '<option value="'.$row_country['id'].'">'.stripslashes($row_country['name']).'</option>';
	}
	$tpl->set('{country}', $all_country);
	$tpl->compile('content');
}

$tpl->load_template('main.tpl');

//Если юзер залогинен
if($logged){
	$tpl->set_block("'\\[not-logged\\](.*?)\\[/not-logged\\]'si","");
	$tpl->set('[logged]','');
	$tpl->set('[/logged]','');
	$tpl->set('{my-page-link}', '/u'.$user_info['user_id']);
	$tpl->set('{my-id}', $user_info['user_id']);
	$tpl->set('{top-name}', $user_info['user_search_pref']);
	if($user_info['user_photo']){
		$tpl->set('{top-ava}', $config['home_url'].'uploads/users/'.$user_info['user_id'].'/50_'.$user_info['user_photo']);
	} else {
		$tpl->set('{top-ava}', '{theme}/images/no_avatars/no_ava_50.gif');
	}
	
	//Новости
	if($CacheNews){
		$tpl->set('{new-news}', $new_news);
		$tpl->set('{new-news-link}', $new_news_link);
	} else {
		$tpl->set('{new-news}', '');
		$tpl->set('{new-news-link}', '');
	}
	
	//Подарки
	if($CacheGift){
		$tpl->set('{new-gifts}', $new_gifts);
		$tpl->set('{new-gifts-link}', $new_gifts_link);
	} else {
		$tpl->set('{new-gifts}', '');
		$tpl->set('{new-gifts-link}', $new_gifts_link);
	}
	
	//Заявки в друзья
	$user_friends_demands = $user_info['user_friends_demands'];
	if($user_friends_demands){
		$tpl->set('{new_demands}', $new_demands);
		$tpl->set('{new-demands-link}', $new_demands_link);
	} else {
		$tpl->set('{new_demands}', '');
		$tpl->set('{new-demands-link}', '');
	}

	//Сообщения
	if($user_pm_num)
		$tpl->set('{new_msg}', $user_pm_num);
	else 
		$tpl->set('{new_msg}', '');

} else {
	$tpl->set_block("'\\[logged\\](.*?)\\[/logged\\]'si","");
	$tpl->set('[not-logged]','');
	$tpl->set('[/not-logged]','');
	$tpl->set('{my-page-link}', '');
}

$tpl->set('{language}', $rMyLang);
$tpl->set('{info}', $tpl->result['error_yellow']);
$tpl->set('{content}', $tpl->result['content']);

//BUILD JS
if($logged){
	$tpl->set('{head}', '<title>'.$metatags['title'].'</title>
<meta name="generator" content="Vii Engine from forum http://xmaxi.pp.ua" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<noscript><meta http-equiv="refresh" content="0; URL=/badbrowser.php"></noscript>
<link media="screen" href="{theme}/style/main.css" type="text/css" rel="stylesheet" />  
<link media="screen" href="{theme}/style/general.css" type="text/css" rel="stylesheet" />  
<script type="text/javascript" src="{theme}/js/jquery.lib.js"></script>
<script type="text/javascript" src="{theme}/js/'.$checkLang.'/lang.js"></script>
<script type="text/javascript" src="{theme}/js/main.js"></script>
<script type="text/javascript" src="{theme}/js/profile.js"></script>
<link rel="shortcut icon" href="{theme}/images/fav.png" />');
} else {
	$tpl->set('{head}', '<title>'.$metatags['title'].'</title>
<meta name="generator" content="Vii Engine from forum http://xmaxi.pp.ua" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<noscript><meta http-equiv="refresh" content="0; URL=/badbrowser.php"></noscript>
<link media="screen" href="{theme}/style/general.css" type="text/css" rel="stylesheet" />  
<link media="screen" href="{theme}/style/main.css" type="text/css" rel="stylesheet" />  
<script type="text/javascript" src="{theme}/js/jquery.lib.js"></script>
<script type="text/javascript" src="{theme}/js/'.$checkLang.'/lang.js"></script>
<script type="text/javascript" src="{theme}/js/main.js"></script>
<script type="text/javascript" src="{theme}/js/signup.js"></script>
<link rel="shortcut icon" href="{theme}/images/fav.png" />');
}

$tpl->compile('main');

echo str_replace('{theme}', '/templates/'.$config['temp'], $tpl->result['main']);

$tpl->global_clear();
$db->close();

?>