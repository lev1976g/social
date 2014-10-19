<?
/* 
	Appointment: Процессор сайта
	File: init.php
 
*/
if(!defined('MOZG'))
	die('Hacking attempt!');

	
@include ENGINE_DIR.'/data/config.php';

if(!$config['home_url']) die("Vii Engine not installed. Please run install.php");

include ENGINE_DIR.'/classes/mysql.php';
include ENGINE_DIR.'/data/db.php';
include ENGINE_DIR.'/classes/templates.php';

//FUNC. COOKIES
function clean_url($url) {
	if( $url == '' ) return;
	
	$url = str_replace( "http://", "", strtolower( $url ) );
	$url = str_replace( "https://", "", $url );
	if( substr( $url, 0, 4 ) == 'www.' ) $url = substr( $url, 4 );
	$url = explode( '/', $url );
	$url = reset( $url );
	$url = explode( ':', $url );
	$url = reset( $url );
	
	return $url;
}

$domain_cookie = explode (".", clean_url( $_SERVER['HTTP_HOST'] ));
$domain_cookie_count = count($domain_cookie);
$domain_allow_count = -2;

if($domain_cookie_count > 2){

	if(in_array($domain_cookie[$domain_cookie_count-2], array('com', 'net', 'org') )) 
		$domain_allow_count = -3;
		
	if($domain_cookie[$domain_cookie_count-1] == 'ua' ) 
		$domain_allow_count = -3;
		
	$domain_cookie = array_slice($domain_cookie, $domain_allow_count);
}

$domain_cookie = ".".implode(".", $domain_cookie);

define('DOMAIN', $domain_cookie);

function set_cookie($name, $value, $expires) {
	
	if( $expires ) {
		
		$expires = time() + ($expires * 86400);
	
	} else {
		
		$expires = FALSE;
	
	}
	
	if( PHP_VERSION < 5.2 ) {
		
		setcookie($name, $value, $expires, "/", DOMAIN."; HttpOnly");
	
	} else {
		
		setcookie($name, $value, $expires, "/", DOMAIN, NULL, TRUE);
	
	}
}

//Смена языка
if($_GET['act'] == 'change_lang'){
	
	$langId = intval($_GET['id']);
	$config['lang_list'] = nl2br($config['lang_list']);
	$expLangList = explode('<br />', $config['lang_list']);
	$numLangs = count($expLangList);
	
	if($langId > 0 AND $langId <= $numLangs){

		//Меняем язык
		set_cookie("lang", $langId, 365);

	}
	
	$langReferer = $_SERVER['HTTP_REFERER'];
	
	header("Location: {$langReferer}");

}

//lang
$config['lang_list'] = nl2br($config['lang_list']);
$expLangList = explode('<br />', $config['lang_list']);
$numLangs = count($expLangList);
$useLang = intval($_COOKIE['lang']);
if($useLang <= 0) $useLang = 1;
$cil = 0;
foreach($expLangList as $expLangData){

	$cil++;
	
	$expLangName = explode(' | ', $expLangData);
	
	if($cil == $useLang AND $expLangName[0]){
	
		$rMyLang = $expLangName[0];
		$checkLang = $expLangName[1];
		
	}
	
}

if(!$checkLang){
	$rMyLang = 'English';
	$checkLang = 'English';
}

include ENGINE_DIR.'/languages/'.$checkLang.'/site.lng';
include ENGINE_DIR.'/modules/functions.php';

$tpl = new mozg_template;
$tpl->dir = ROOT_DIR.'/templates/'.$config['temp'];
define('TEMPLATE_DIR', $tpl->dir);

$_DOCUMENT_DATE = false;
$Timer = new microTimer();
$Timer->start();

$server_time = intval($_SERVER['REQUEST_TIME']);

include ENGINE_DIR.'/modules/signin.php';

if($config['offline'] == "yes"){
	include ENGINE_DIR.'/modules/offline.php';
}
if($user_info['user_delet']){
	include ENGINE_DIR.'/modules/profile_delete.php';
}
$sql_banned = $db->super_query("SELECT * FROM ".PREFIX."_banned", true, "banned", true);
if(isset($sql_banned)){
	$blockip = check_ip($sql_banned);
} else {
	$blockip = false;
}
if($user_info['user_ban_date'] >= $server_time OR $user_info['user_ban_date'] == '0' OR $blockip){
	include ENGINE_DIR.'/modules/profile_baned.php';
}

//Елси юзер залогинен то обновляем последнюю дату посещения в таблице друзей и на личной стр
if($logged){
	//Начисления 1 убм.
	if(!$user_info['user_lastupdate']) $user_info['user_lastupdate'] = 1;

	if(date('Y-m-d', $user_info['user_lastupdate']) < date('Y-m-d', $server_time))
		$sql_balance = ", user_balance = user_balance+1, user_lastupdate = '{$server_time}'";

	$db->query("UPDATE LOW_PRIORITY `".PREFIX."_users` SET user_last_visit = '{$server_time}' {$sql_balance} WHERE user_id = '{$user_info['user_id']}'");
}

//Настройки групп пользователей
$user_group = unserialize(serialize(array(
							1 => array( #Администрация
								'addnews' => '1', 
							),
							2 => array( #Главный модератор
								'addnews' => '0', 
							),
							3 => array( #Модератор
								'addnews' => '0', 
							),
							4 => array( #Техподдержка
								'addnews' => '0', 
							), 
							5 => array( #Пользователи
								'addnews' => '0', 
							),
						)));

//Время онлайна
$online_time = $server_time - $config['online_time'];

include ENGINE_DIR.'/modules.php';

?>