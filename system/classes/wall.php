<?php
/* 
	Appointment: Класс для стены
	File: wall.php 
 
*/

class wall {

	var $query = false;
	var $template = false;
	var $compile = false;
	var $comm_query = false;
	var $comm_template = false;
	var $comm_compile = false;
	
	function query($query){
		global $db;
		
		$this->query = $db->super_query($query, 1);
	}

	function template($template){
		global $tpl;
		$this->template = $tpl->load_template($template);
	}
	
	function compile($compile){
		$this->compile = $compile;
	}
	
	function select(){
		global $tpl, $db, $config, $user_id, $id, $for_user_id, $lang, $user_privacy, $check_friend, $server_time, $user_info;

		$this->template;
		foreach($this->query as $row_wall){
			$tpl->set('{rec-id}', $row_wall['id']);
			
			//КНопка Показать полностью..
			$expBR = explode('<br />', $row_wall['text']);
			$textLength = count($expBR);
			$strTXT = strlen($row_wall['text']);
			if($textLength > 9 OR $strTXT > 600)
				$row_wall['text'] = ' <div class="wall_strlen" id="hide_wall_rec'.$row_wall['id'].'">'.$row_wall['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_wall['id'].', this.id)" id="hide_wall_rec_lnk'.$row_wall['id'].'">Показать полностью..</div>';
			
			//Прикрипленные файлы
			if($row_wall['attach']){
				$attach_arr = explode('||', $row_wall['attach']);
				$cnt_attach = 1;
				$cnt_attach_link = 1;
				$jid = 0;
				$attach_result = '';
				foreach($attach_arr as $attach_file){
					$attach_type = explode('|', $attach_file);
					
					//Фото со стены юзера
					if($attach_type[0] == 'photo_u'){
						if($row_wall['tell_uid']) $attauthor_user_id = $row_wall['tell_uid'];
						else $attauthor_user_id = $row_wall['author_user_id'];

						if($attach_type[1] == 'attach' AND file_exists(ROOT_DIR."/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){

							if($cnt_attach == 1)
							
								$attach_result .= "<div class=\"clear\" style=\"height:12px;\"></div><div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/{$attach_type[2]}\" /></div>";
							
							$cnt_attach++;

							
						} elseif(file_exists(ROOT_DIR."/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){
						
							if($cnt_attach < 2)
								$attach_result .= "<div class=\"clear\" style=\"height:12px;\"></div><div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/{$attach_type[1]}\" align=\"left\" /></div>";
								
							$cnt_attach++;
						}
						
						$resLinkTitle = '';

					//Видео
					} elseif($attach_type[0] == 'video' AND file_exists(ROOT_DIR."/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")){
						$attach_result .= "<div class=\"clear\" style=\"height:12px;\"></div><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><div class=\"profile_wall_attach_photo\"><div class=\"profile_wall_attach_video_ico\"></div><img src=\"/uploads/videos/{$attach_type[3]}/420_{$attach_type[1]}\"  /></div></a>";
						
						$resLinkTitle = '';

					}  else
					
						$attach_result .= '';
						
				}

				if($resLinkTitle AND $row_wall['text'] == $resLinkUrl OR !$row_wall['text']){
					$row_wall['text'] = $resLinkTitle.$attach_result;
				} else if($attach_result){
					$row_wall['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_wall['text']).$attach_result;
					$row_wall['text'] = preg_replace("/\B#(\S{1,24}+)/u", '<a href="/?go=search&query=$1&type=4">#$1</a>', $row_wall['text']);
				} else {
					$row_wall['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_wall['text']);
					$row_wall['text'] = preg_replace("/\B#(\S{1,24}+)/u", '<a href="/?go=search&query=$1&type=4">#$1</a>', $row_wall['text']);
			}
			} else {
				$row_wall['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a> ', $row_wall['text']);
				$row_wall['text'] = preg_replace("/\B#(\S{1,24}+)/u", '<a href="/?go=search&query=$1&type=4">#$1</a>', $row_wall['text']);
			}
			
			
				
			$resLinkTitle = '';
			
			//Если это запись с "рассказать друзьям"
			if($row_wall['tell_uid']){
				if($row_wall['public'])
					$rowUserTell = $db->super_query("SELECT title, photo FROM `".PREFIX."_communities` WHERE id = '{$row_wall['tell_uid']}'");
				else
					$rowUserTell = $db->super_query("SELECT user_search_pref, user_photo FROM `".PREFIX."_users` WHERE user_id = '{$row_wall['tell_uid']}'");

				if(date('Y-m-d', $row_wall['tell_date']) == date('Y-m-d', $server_time))
					$dateTell = langdate('сегодня в H:i', $row_wall['tell_date']);
				elseif(date('Y-m-d', $row_wall['tell_date']) == date('Y-m-d', ($server_time-84600)))
					$dateTell = langdate('вчера в H:i', $row_wall['tell_date']);
				else
					$dateTell = langdate('j F Y в H:i', $row_wall['tell_date']);
				
				if($row_wall['public']){
					$rowUserTell['user_search_pref'] = stripslashes($rowUserTell['title']);
					$tell_link = 'public';
					if($rowUserTell['photo'])
						$avaTell = '/uploads/groups/'.$row_wall['tell_uid'].'/50_'.$rowUserTell['photo'];
					else
						$avaTell = '{theme}/images/no_avatars/no_ava_50.gif';
				} else {
					$tell_link = 'u';
					if($rowUserTell['user_photo'])
						$avaTell = '/uploads/users/'.$row_wall['tell_uid'].'/50_'.$rowUserTell['user_photo'];
					else
						$avaTell = '{theme}/images/no_avatars/no_ava_50.gif';
				}

				if($row_wall['tell_comm']){
					$border_tell_height = '5px;';
				} else $border_tell_height = '-10px;';

				$row_wall['text'] = <<<HTML
{$row_wall['tell_comm']}
<div class="clear" style="height:5px;"></div>
<div class="wall_tell_info" style="margin-top:{$border_tell_height}"><div class="wall_tell_ava"><a href="/{$tell_link}{$row_wall['tell_uid']}" onClick="Page.Go(this.href); return false"><img src="{$avaTell}" width="30" /></a></div><div class="wall_tell_name"><a href="/{$tell_link}{$row_wall['tell_uid']}" onClick="Page.Go(this.href); return false"><b>{$rowUserTell['user_search_pref']}</b></a></div><div class="wall_tell_date">{$dateTell}</div></div>{$row_wall['text']}
<div class="clear"></div>

HTML;
			}
			
			$tpl->set('{text}', stripslashes($row_wall['text']));
			
			$tpl->set('{name}', $row_wall['user_search_pref']);
			$tpl->set('{user-id}', $row_wall['author_user_id']);
			OnlineTpl($row_wall['user_last_visit']);
			megaDate($row_wall['add_date']);
			
			if($row_wall['user_photo'])
				$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_wall['author_user_id'].'/50_'.$row_wall['user_photo']);
			else
				$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
			
			//Мне нравится
			if(stripos($row_wall['likes_users'], "u{$user_id}|") !== false){
				$tpl->set('{like-js-function}', 'wall.wall_remove_like('.$row_wall['id'].', '.$user_id.')');
				$tpl->set('{like_color}', 'color:#3a81ad;');
			} else {
				$tpl->set('{like-js-function}', 'wall.wall_add_like('.$row_wall['id'].', '.$user_id.')');
				$tpl->set('{like_color}', '');
			}

			if($row_wall['likes_num']){
				$tpl->set('{likes}', $row_wall['likes_num']);
				$tpl->set('{like_display}', '');
			} else {
				$tpl->set('{likes}', '');
				$tpl->set('{like_display}', 'no_display');
			}
						
			//Выводим информцию о том кто смотрит страницу для себя
			$tpl->set('{viewer-id}', $user_id);
			if($user_info['user_photo'])
				$tpl->set('{viewer-ava}', '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo']);
			else
				$tpl->set('{viewer-ava}', '{theme}/images/no_avatars/no_ava_50.gif');
			
			if($row_wall['type'])
				$tpl->set('{type}', $row_wall['type']);
			else
				$tpl->set('{type}', '');

			if(!$id)
				$id = $for_user_id;
			
			//Тег Owner означает показ записей только для владельца страницы или для того кто оставил запись
			if($user_id == $row_wall['author_user_id'] OR $user_id == $id){
				$tpl->set('[owner]', '');
				$tpl->set('[/owner]', '');
			} else
				$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

			//Показа кнопки "Рассказать др" только если это записи владельца стр.
			if($row_wall['author_user_id'] == $id AND $user_id != $id){
				$tpl->set('[owner-record]', '');
				$tpl->set('[/owner-record]', '');
			} else
				$tpl->set_block("'\\[owner-record\\](.*?)\\[/owner-record\\]'si","");
			
			//Если есть комменты к записи, то выполняем след. действия / Приватность
			if($row_wall['fasts_num']){
				$tpl->set('[if-comments]', '');
				$tpl->set('[/if-comments]', '');
				$tpl->set_block("'\\[comments-link\\](.*?)\\[/comments-link\\]'si","");
			} else {
				$tpl->set('[comments-link]', '');
				$tpl->set('[/comments-link]', '');
				$tpl->set_block("'\\[if-comments\\](.*?)\\[/if-comments\\]'si","");
			}

			//Приватность комментирования записей
			if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $check_friend OR $user_id == $id){
				$tpl->set('[privacy-comment]', '');
				$tpl->set('[/privacy-comment]', '');
			} else
				$tpl->set_block("'\\[privacy-comment\\](.*?)\\[/privacy-comment\\]'si","");
				
			$tpl->set('[record]', '');
			$tpl->set('[/record]', '');
			$tpl->set('{author-id}', $id);
			$tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
			$tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
			$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
			$tpl->compile($this->compile);

			//Помещаем все комменты в id wall_fast_block_{id} это для JS
			$tpl->result[$this->compile] .= '<div class="profile_wall_comment" id="wall_fast_block_'.$row_wall['id'].'">';
				
			//Если есть комменты к записи, то открываем форму ответа уже в развернутом виде и выводим комменты к записи
			if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $check_friend OR $user_id == $id){
				if($row_wall['fasts_num']){
					
					if($row_wall['fasts_num'] > 3)
						$comments_limit = $row_wall['fasts_num']-3;
					else
						$comments_limit = 0;
					
					$sql_comments = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.id, author_user_id, text, add_date, tb2.user_photo, user_search_pref FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '{$row_wall['id']}' ORDER by `add_date` ASC LIMIT {$comments_limit}, 3", 1);

					//Загружаем кнопку "Показать N запсии"
					$tpl->set('{gram-record-all-comm}', gram_record(($row_wall['fasts_num']-3), 'prev').' '.($row_wall['fasts_num']-3).' '.gram_record(($row_wall['fasts_num']-3), 'comments'));
					if($row_wall['fasts_num'] < 4)
						$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
					else {
						$tpl->set('{rec-id}', $row_wall['id']);
						$tpl->set('[all-comm]', '');
						$tpl->set('[/all-comm]', '');
					}
					$tpl->set('{author-id}', $id);
					$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
					$tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
					$tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
					$tpl->compile($this->compile);
				
					//Сообственно выводим комменты
					foreach($sql_comments as $row_comments){
						$tpl->set('{name}', $row_comments['user_search_pref']);
						if($row_comments['user_photo'])
							$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comments['author_user_id'].'/50_'.$row_comments['user_photo']);
						else
							$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
						$tpl->set('{comm-id}', $row_comments['id']);
						$tpl->set('{user-id}', $row_comments['author_user_id']);
						
						$expBR2 = explode('<br />', $row_comments['text']);
						$textLength2 = count($expBR2);
						$strTXT2 = strlen($row_comments['text']);
						if($textLength2 > 6 OR $strTXT2 > 470)
							$row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';
					
						$tpl->set('{text}', stripslashes($row_comments['text']));
						megaDate($row_comments['add_date']);
						if($user_id == $row_comments['author_user_id'] || $user_id == $id){
							$tpl->set('[owner]', '');
							$tpl->set('[/owner]', '');
						} else
							$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
					
						$tpl->set('[comment]', '');
						$tpl->set('[/comment]', '');
						$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
						$tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
						$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
						$tpl->compile($this->compile);
					}

					//Загружаем форму ответа
					$tpl->set('{rec-id}', $row_wall['id']);
					$tpl->set('{author-id}', $id);
					$tpl->set('[comment-form]', '');
					$tpl->set('[/comment-form]', '');
					$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
					$tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
					$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
					$tpl->compile($this->compile);
				}
			}
			
			//Закрываем блок для JS
			$tpl->result[$this->compile] .= '</div>';

		}

	}
	
	function comm_query($query){
		global $db;
		
		$this->comm_query = $db->super_query($query, 1);
	}
	
	function comm_template($template){
		global $tpl;
		$this->comm_template = $tpl->load_template($template);
	}
	
	function comm_compile($compile){
		$this->comm_compile = $compile;
	}
	
	function comm_select(){
		global $tpl, $db, $config, $user_id, $id, $for_user_id, $fast_comm_id, $record_fasts_num;
		
		if($this->comm_query){
			$this->comm_template;
			
			//Помещаем все комменты в id wall_fast_block_{id} это для JS
			$tpl->result[$this->compile] .= '<div class="profile_wall_comment" id="wall_fast_block_'.$fast_comm_id.'">';
			
			//Загружаем кнопку "Показать N запсии" если их больше 3
			if($record_fasts_num > 3){
				$tpl->set('{gram-record-all-comm}', gram_record(($record_fasts_num-3), 'prev').' '.($record_fasts_num-3).' '.gram_record(($record_fasts_num-3), 'comments'));
				$tpl->set('[all-comm]', '');
				$tpl->set('[/all-comm]', '');
				$tpl->set('{rec-id}', $fast_comm_id);
				$tpl->set('{author-id}', $for_user_id);
				$tpl->set('[wall-func]', '');
				$tpl->set('[/wall-func]', '');
				$tpl->set_block("'\\[groups\\](.*?)\\[/groups\\]'si","");
				$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
				$tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
				$tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
				$tpl->compile($this->comm_compile);
			} else
				$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");

			//Сообственно выводим комменты
			foreach($this->comm_query as $row_comments){
				$tpl->set('{name}', $row_comments['user_search_pref']);
				if($row_comments['user_photo'])
					$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comments['author_user_id'].'/50_'.$row_comments['user_photo']);
				else
					$tpl->set('{ava}', '{theme}/images/no_avatars/no_ava_50.gif');
				$tpl->set('{comm-id}', $row_comments['id']);
				$tpl->set('{user-id}', $row_comments['author_user_id']);
				
				$expBR2 = explode('<br />', $row_comments['text']);
				$textLength2 = count($expBR2);
				$strTXT2 = strlen($row_comments['text']);
				if($textLength2 > 6 OR $strTXT2 > 470)
					$row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';
							
				$tpl->set('{text}', stripslashes($row_comments['text']));
				
				megaDate($row_comments['add_date']);
				
				if(!$id)
					$id = $for_user_id;
					
				if($user_id == $row_comments['author_user_id'] || $user_id == $id){
					$tpl->set('[owner]', '');
					$tpl->set('[/owner]', '');
				} else
					$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
					
				$tpl->set('[comment]', '');
				$tpl->set('[/comment]', '');
				$tpl->set('[wall-func]', '');
				$tpl->set('[/wall-func]', '');
				$tpl->set_block("'\\[groups\\](.*?)\\[/groups\\]'si","");
				$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
				$tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
				$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
				$tpl->compile($this->comm_compile);
			}
			
			//Закрываем блок для JS
			$tpl->result[$this->compile] .= '</div>';
			
			//Загружаем форму ответа
			$tpl->set('{rec-id}', $fast_comm_id);
			$tpl->set('{author-id}', $for_user_id);
			$tpl->set('[comment-form]', '');
			$tpl->set('[/comment-form]', '');
			$tpl->set('[wall-func]', '');
			$tpl->set('[/wall-func]', '');
			$tpl->set_block("'\\[groups\\](.*?)\\[/groups\\]'si","");
			$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
			$tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
			$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
			$tpl->compile($this->comm_compile);
		}
	}
}
?>