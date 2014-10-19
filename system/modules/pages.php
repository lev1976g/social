<?php
/* 
	Appointment: Вывод формы регистрации на главной
	File: register_main.php 
 
*/
if(!defined('MOZG'))
	die('Hacking attempt!');
if($ajax == 'yes')
	NoAjaxQuery();

	$act = $_GET['act'];
	
	switch($act){
		
		//################### Регистрация ###################//
		case "signup":
			$metatags['title'] = 'Регистрация';
			
			if(!$logged){
				$tpl->load_template('pages/signup.tpl');
				//################## Загружаем Страны ##################//
$sql_country = $db->super_query("SELECT SQL_CALC_FOUND_ROWS * FROM `".PREFIX."_country` ORDER by `name` ASC", true, "country", true);
foreach($sql_country as $row_country)
	$all_country .= '<option value="'.$row_country['id'].'">'.stripslashes($row_country['name']).'</option>';
			
$tpl->set('{country}', $all_country);
				$tpl->compile('content');
			} else {
				msgbox('', $lang['already_logged'], '404');
			}
		break;
		
		//################### О сайте ###################//
		case "about":
			$metatags['title'] = 'О сайте';
			$tpl->load_template('pages/about.tpl');
			$tpl->compile('content');	
		break;
		
		//################### Правила ###################//
		case "terms":
			$metatags['title'] = 'Правила';
			$tpl->load_template('pages/terms.tpl');
			$tpl->compile('content');	
		break;
		
		//################### Разработчикам ###################//
		case "developers":
			$metatags['title'] = 'Разработчикам';
			$tpl->load_template('pages/developers.tpl');
			$tpl->compile('content');	
		break;
		
		//################### Вакансии ###################//
		case "jobs":
			$metatags['title'] = 'Вакансии';
			$tpl->load_template('pages/jobs.tpl');
			$tpl->compile('content');	
		break;
		
		//################### Правила ###################//
		case "help":
			$metatags['title'] = 'Правила';
			$tpl->load_template('pages/help.tpl');
			$tpl->compile('content');	
		break;
		
		default:
			$tpl->load_template('access_denied.tpl');
			$tpl->compile('content');	
		exit();
	}
	$tpl->clear();
	$db->free();

?>