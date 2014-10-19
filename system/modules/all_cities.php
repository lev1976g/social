<?php
/* 
	Appointment: Загрузка городов
	File: loadcity.php 
 
*/
if(!defined('MOZG'))
	die('Hacking attempt!');

NoAjaxQuery();
	
$country_id = intval($_POST['country']);

echo '<option value="0">- Выбрать -</option>';

if($country_id){
	$sql_ = $db->super_query("SELECT SQL_CALC_FOUND_ROWS id, name FROM `".PREFIX."_city` WHERE id_country = '{$country_id}' ORDER by `name` ASC", true, "country_city_".$country_id, true);
	foreach($sql_ as $row) 
		echo '<option value="'.$row['id'].'">'.stripslashes($row['name']).'</option>';
}

die();
?>