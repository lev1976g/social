<?php
/* 
	Appointment: Выбор языка
	File: lang.php 
	Author: f0rt1 
	Engine: Vii Engine
	Copyright: NiceWeb Group (с) 2011
	e-mail: niceweb@i.ua
	URL: http://www.niceweb.in.ua/
	ICQ: 427-825-959
	Данный код защищен авторскими правами
*/
if(!defined('MOZG'))
	die('Hacking attempt!');

NoAjaxQuery();

$tpl->load_template('languages.tpl');

$useLang = intval($_COOKIE['lang']);
if($useLang <= 0) $useLang = 1;
$config['lang_list'] = nl2br($config['lang_list']);
$expLangList = explode('<br />', $config['lang_list']);
$numLangs = count($expLangList);
$cil = 0;
foreach($expLangList as $expLangData){
	
	$expLangName = explode(' | ', $expLangData);
	
	if($expLangName[0]){
		
		$cil++;
		
		if($cil == $useLang OR $numLangs == 1)
		
			$langs .= "<li id=\"language_active\">".$expLangName[0]."</li>";
			
		else
		
			$langs .= "<a href=\"/index.php?act=change_lang&id={$cil}\"><li>{$expLangName[0]}</li></a>";
		
	}
	
}

if(!$checkLang) $checkLang = 'English';

$tpl->set('{langs}', $langs);

$tpl->compile('content');

AjaxTpl();

exit();
?>