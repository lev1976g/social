//AVATAR
var Avatar = {
	Delete: function(img){
		Box.Show('delete_ava', 400, lang_title_del_photo, '<div style="padding:15px; text-align:center; font-size:16px; color:#888;"><img src="'+img+'" /><br /><br />'+lang_del_photo+'</div>', true, lang_box_yes, 'Avatar.StartDelete(); return false;');
	},
	StartDelete: function(){
		Page.Loading('start');
		$.get('/index.php?go=editprofile&act=delete_avatar', function(){
			$('#ava').html('<img src="/templates/Default/images/no_avatars/no_ava_200.gif" />');
			$('#ava_delete_but').hide();
			Page.Loading('stop');
			Box.Close('delete_ava');
		});
	},
}
//EDIT PROFILE
function isValidMobile(xname){
	var pattern = new RegExp(/^[0-9]+$/);
 	return pattern.test(xname);
}
var EditProfile = {
	SaveInfo: function(){
		var sex = $("#sex").val();
		var day = $("#day").val();
		var year = $("#year").val();
		var month = $("#month").val();
		var country = $("#country").val();
		var city = $("#city").val();
		var mobile = $("#mobile").val();
		var twitter = $("#twitter").val();
		butloading('saveform', '55', 'disabled', '');
		$.post('/index.php?go=editprofile&act=save_info', {sex: sex, day: day, month: month, year: year, country: country, city: city, mobile: mobile, twitter: twitter}, function(data){
			$('#info_save').hide();
			if(data == 'ok'){
				$('#info_save').show();
				$('#info_save').html(lang_infosave);
			} else {
				$('#info_save').show();
				$('#info_save').html(data);
			}
			butloading('saveform', '55', 'enabled', lang_box_save);
		});
	},
	SaveName: function(){
		var name = $('#name').val();
		var lastname = $('#lastname').val();
		butloading('SaveName', '55', 'disabled', '');
		if(name.length >= 2 && name != 0 && EditProfile.isValidName(name)){
			if(lastname.length >= 2 && lastname != 0 && EditProfile.isValidName(lastname)){
				$.post('/index.php?go=editprofile&act=SaveName', {name: name, lastname: lastname}, function(data){
					if(data == 'ok'){
						$('.name_errors').hide();
						$('#ok_name').show();
						butloading('SaveName', 69, 'enabled', 'Изменить имя');
					}
				});
			} else {
				$('.name_errors').hide();
				$('#err_name_1').show();
				butloading('SaveName', 69, 'enabled', 'Изменить имя');
			}
		} else {
			$('.name_errors').hide();
			$('#err_name_1').show();
			butloading('SaveName', 69, 'enabled', 'Изменить имя');
		}
	},
	isValidName: function(xname){
		var pattern = new RegExp(/^[a-zA-Zа-яА-Я]+$/);
		return pattern.test(xname);
	},
}

//ALBUMS
var Albums = {
	CreatAlbum: function(){
		Page.Loading('start');
		$.post('/index.php?go=albums&act=create_page', function(data){
			Box.Show('albums', 450, lang_title_new_album, data, true, lang_album_create, 'StartCreatAlbum(); return false;');
			Page.Loading('stop');
		});
	},
	Delete: function(id, hash){
		Box.Show('del_album_'+id, 350, lang_title_del_photo, '<div style="padding:15px;">'+lang_del_album+'</div>', lang_box_canсel, lang_box_yes, 'Albums.StartDelete('+id+', \''+hash+'\'); return false;');
	},
	StartDelete: function(id, hash){
		$('#box_loading').show();
		$.post('/index.php?go=albums&act=del_album', {id: id, hash: hash}, function(d){
			Box.Close('del_album_'+id);
			$('#album_'+id).remove();
			updateNum('#albums_num');
			if($('.albums').size() < 1)
				Page.Go(location.href);
		});
	},
	EditBox: function(id){
		Page.Loading('start');
		$.post('/index.php?go=albums&act=edit_page', {id: id}, function(d){
			Page.Loading('stop');
			Box.Show('edit_albums_'+id, 450, lang_edit_albums, d, true, lang_box_save, 'Albums.SaveDescr('+id+'); return false');
		});
	},
	SaveDescr: function(id){
		var name = $("#name_"+id).val();
		var descr = $("#descr_t"+id).val();
		if(name != 0){
			$("#name_"+id).css('background', '#fff');
			$('#box_loading').show();
			$.post('/index.php?go=albums&act=save_album', {id: id, name: name, descr: descr, privacy: $('#privacy').val(), privacy_comm: $('#privacy_comment').val()}, function(data){
				$('#box_loading').hide();
				if(data == 'no_name'){
					$('.red_error').show().text(lang_empty);
					ge('box_but').disabled = false;
				} else if(data == 'no'){
					$('.red_error').show().text(lang_nooo_er);
					ge('box_but').disabled = false;
				} else {
					Box.Close('edit_albums_'+id);
					row = data.split('|#|||#row#|||#|');
					$('#descr_'+id).html('<div style="padding-top:4px;">'+row[1]+'</div>');
					$('#albums_name_'+id).html(row[0]);
				}
			});
		} else {
			$("#name_"+id).css('background', '#ffefef');
			setTimeout("$('#name_"+id+"').css('background', '#fff').focus()", 800);
			$('#box_loading').hide();
		}
	},
	EditCover: function(id, page_num){
		if(page_num)
			page = '&page='+page_num;
		else {
			page = '';
			page_num = 1;
		}
		
		Box.Page(
			'/index.php?go=albums&act=edit_cover', //URL
			'id='+id+page, //POST данные
			'edit_cover_'+id, //ID
			625, //Ширина окна
			lang_edit_cover_album, //Заголовок окна
			false, //Имя кнопки для закртие окна
			'', //Текст кнопки выполняющая функцию
			'', //Сама функция для выполнения
			400, //Высота окна
			'overflow', //Скролл
			''
		);
	},
	SetCover: function(id, aid, photo){
		Page.Loading('start');
		$.get('/index.php?go=albums&act=set_cover', {id: id}, function(){
			$('#cover_'+aid).html('<img src="'+photo+'" />');
			Box.Close('edit_cover_'+aid);
			Page.Loading('stop');
		});
	}
}

//PHOTOS
var Photo = {
	Show: function(h){
		var id = h.split('_');
		var uid = id[0].split('photo');
		var section = h.split('sec=');
		var fuser = h.split('wall/fuser=');
		var msg_id = h.split('msg/id=');
		
		if(fuser[1])
			section[1] = 'wall';
		
		if(msg_id[1]){
			section[1] = 'msg';
			fuser[1] = msg_id[1];
		}

		$('.photo_box').hide();
		
		if(is_moz && !is_chrome) scrollTopForFirefox = $(window).scrollTop();
		$('html').css('overflow', 'hidden');
		if(is_moz && !is_chrome) $(window).scrollTop(scrollTopForFirefox);

		if(ge('photo_view_'+id[1])){
			$('#photo_view_'+id[1]).show();
			history.pushState({link:h}, null, h);
		} else {
			Photo.Loading('start');
			$.post('/index.php?go=photo', {uid: uid[1], pid: id[1], section: section[1], fuser: fuser[1]}, function(d){
				if(d == 'no_photo'){
					Photo.Loading('stop');
					Box.Info('no_video', lang_dd2f_no, lang_photo_info_text, 300);
					$('html, body').css('overflow-y', 'hidden');
					return false;
				} else if(d == 'err_privacy'){
					Photo.Loading('stop');
					ShowInfo('red', lang_pr_no_title, 2000);
					$('html').css('overflow-y', 'hidden');
				}
				
				if(section[1] != 'loaded')
					history.pushState({link:h}, null, h);
				
				$('body').append(d);
				$('#photo_view_'+id[1]).show();

				Photo.Loading('stop');
			});
		}
	},
	Profile: function(uid, photo, type){
		Photo.Loading('start');
		$.post('/index.php?go=photo&act=profile', {uid: uid, photo: photo, type: type}, function(d){
			Photo.Loading('stop');
			if(d == 'no_photo'){
				Box.Info('no_video', lang_dd2f_no, lang_photo_info_text, 300);
				$('html, body').css('overflow-y', 'auto');
			} else {
				$('body').append(d);
				$('#photo_view').show();
				$('html, body').css('overflow-y', 'hidden');
			}
		});
	},
	Prev: function(h){
		var id = h.split('_');
		$('.photo_view').hide();
		$('html, body').css('overflow', 'hidden');

		$('.pinfo, .photo_prev_but, .photo_next_but').show();
		$('.save_crop_text').hide();
		$('.ladybug_ant').imgAreaSelect({remove: true});
		
		if(ge('photo_view_'+id[1])){
			$('#photo_view_'+id[1]).show();
			return false;
		} else {
			Photo.Show(h);
		}
	},
	Close: function(close_link){
		$('.ladybug_ant').imgAreaSelect({remove: true});
		
		$('.photo_box').remove();
		$('html, body').css('overflow-y', 'auto');
		
		if(is_moz && !is_chrome) $(window).scrollTop(scrollTopForFirefox);

		if(close_link != false)
			history.pushState({link: close_link}, null, close_link);
	},
	Loading: function(f){
		if(f == 'start'){
			if(is_moz && !is_chrome) scrollTopForFirefox = $(window).scrollTop();
			$('html').css('overflow', 'hidden');
			if(is_moz && !is_chrome) $(window).scrollTop(scrollTopForFirefox);
			var loadcontent = '<div class="photo_box" id="photo_load" onClick="Photo.setEvent(event, false)">'+
			'<div class="photo_close" onClick="Photo.LoadingClose(); return false" style="right:15px;"></div>'+
			'<div class="photo_box_position"><div class="photo_box_image" style="line-height:550px;">'+
			'<center><img src="/templates/Default/images/progress.gif" alt="" /></center>'+
			'</div><div id="pinfo_{id}" class="photo_box_info" style="line-height:520px;"><center><img src="/templates/Default/images/progress.gif" alt="" /></center></div>'
			'</div></div>';
			$('body').append(loadcontent);
			$('#photo_load').show();
		} 
		if(f == 'stop')
			$('#photo_load').remove();
	},
	LoadingClose: function(){
		$('#photo_load').remove();
		$('html, body').css('overflow-y', 'auto');
	},
	Init: function(target){
		this.target = $(target);
		var that = this;
		$(window).scroll(function(){
			if ($(document).height() - $(window).height() <= $(window).scrollTop()){
				alert(1);
			}
		});
	},
	Panel: function(id, f){
		if(f == 'show')
			$('#albums_photo_panel_'+id).show();
		else
			$('#albums_photo_panel_'+id).hide();
	},
	MsgDelete: function(id, aid, type){
		Box.Show('del_photo_'+id, '400', lang_title_del_photo, '<div style="padding:15px;">'+lang_del_photo+'</div>', lang_box_canсel, lang_box_yes, 'Photo.Delete('+id+', '+aid+', '+type+'); return false');
	},
	Delete: function(id, aid, type){
		$('#box_loading').show();
		$.get('/index.php?go=albums&act=del_photo', {id: id}, function(){
			Box.Close('del_photo_'+id);
			if(!type){
				$('#a_photo_'+id).remove();
				$('#p_jid'+id).remove();
				
				updateNum('#photo_num');
			} else 
				$('#pinfo_'+id).html(lang_photo_info_delok);
		});
	},
	SetCover: function(id, jid){
		Page.Loading('start');
		$.get('/index.php?go=albums&act=set_cover', {id: id}, function(){
			$('.albums_new_cover').fadeOut();
			$('#albums_new_cover_'+jid).fadeIn();
			Page.Loading('stop');
		});
	},
	EditBox: function(id, r){
		Page.Loading('start');
		$.get('/index.php?go=albums&act=editphoto', {id: id}, function(data){
			Box.Show('edit_photo_'+id, '400', 'Редактирование фотографии', '<div class="box_ppad"><div  style="color:#888;padding-bottom:5px;"><b>Описание фотографии</b></div><textarea class="inpst" id="descr_'+id+'" style="width:355px;height:71px;">'+data+'</textarea></div>', 'Отмена', 'Сохранить', 'Photo.SaveDescr('+id+', '+r+'); return false');
			Page.Loading('stop');
		});
	},
	SaveDescr: function(id, r){
		var descr = $('#descr_'+id).val();
		$('#box_loading').show();
		$.post('/index.php?go=albums&act=save_descr', {id: id, descr: descr}, function(d){
			Box.Close('edit_photo_'+id);
			if(r == 1)
				$('.photo_view').remove();
			else
				$('#photo_descr_'+id).html(d);
		});
	},
	setEvent: function(event, close_link){
		var oi = (event.target) ? event.target.id: ((event.srcElement) ? event.srcElement.id : null);
		var el = oi.substring(0, 10);
		if(el == 'photo_view' || el == 'photo_load')
			Photo.Close(close_link);
	},
	Rotation: function(pos, id){
		$('#loading_gradus'+id).show();
		$.post('/index.php?go=photo&act=rotation', {id: id, pos: pos}, function(d){
			var rndval = new Date().getTime(); 
			$('#ladybug_ant'+id).attr('src', d+'?'+rndval);
			$('#loading_gradus'+id).hide();
		});
	}
}

//PHOTOS COMMENTS
var comments = {
	add: function(id){
		var comment = $('#textcom_'+id).val();
		if(comment != 0){
			butloading('add_comm', '56', 'disabled', '');
			$.post('/index.php?go=photo&act=addcomm', {pid: id, comment: comment},  function(data){
				if(data == 'err_privacy'){
					ShowInfo('red', lang_pr_no_title, 2000);
				} else {
					$('#comments_'+id).append(data);
					$('#textcom_'+id).val('');
				}
				butloading('add_comm', '56', 'enabled', lang_box_send);
			});
		} else {
			$('#textcom_'+id).val('');
			$('#textcom_'+id).focus();
		}
	},
	delet: function(id, hash){
		textLoad('del_but_'+id);
		$.post('/index.php?go=photo&act=del_comm', {hash: hash}, function(){
			$('#comment_'+id).html('<div style="padding-bottom:5px;color:#777;">'+lang_del_comm+'</div>');
		});
	},
	delet_page_comm: function(id, hash){
		textLoad('full_del_but_'+id);
		$.post('/index.php?go=photo&act=del_comm', {hash: hash}, function(){
			$('#comment_all_'+id).html('<div style="padding-bottom:5px;color:#777;">'+lang_del_comm+'</div>');
		});
	},
	all: function(id, num){
		textLoad('all_lnk_comm_'+id);
		$('#all_href_lnk_comm_'+id).attr('onClick', '').attr('href', '#');
		$.post('/index.php?go=photo&act=all_comm', {pid: id, num: num}, function(d){
			$('#all_href_lnk_comm_'+id).hide();
			$('#all_comments_'+id).html(d);
		});
	},
}

//FRIENDS
var friends = {
	add: function(for_id, user_name){
		if(for_id){
			Page.Loading('start');
			
			if(user_name)
				name = user_name;
			else
				name = $('title').text();
			
			$.get('/friedns/send_demand/'+for_id, function(data){
				if(data == 'yes_demand')
					Box.Info('add_demand_'+for_id, lang_demand_ok, lang_demand_no, 300);
				else if(data == 'yes_demand2')
					Box.Info('add_demand_k_'+for_id, lang_dd2f_no, lang_dd2f22_no, 300);
				else if(data == 'yes_friend')
					Box.Info('add_demand_k_'+for_id, lang_dd2f_no, lang_22dd2f22_no, 300);
				else
					Box.Info('add_demand_ok_'+for_id, lang_demand_ok, '<b><a href="'+location.href+'" onClick="Page.Go(this.href); return false">'+name+'</a></b> '+lang_demand_s_ok, 400);
					
				Page.Loading('stop');
			});
		}
	},
	sending_demand: function(for_id){
		Box.Info('add_sending_demand_'+for_id, lang_demand_sending, lang_demand_sending_t);
	},
	take: function(take_user_id){
		Page.Loading('start');
		$.get('/friedns/take/'+take_user_id, function(data){
			Page.Loading('stop');
			$('#action_'+take_user_id).html(lang_take_ok).css('color', '#888');
		});
	},
	reject: function(reject_user_id){
		Page.Loading('start');
		$.get('/friedns/reject/'+reject_user_id, function(data){
			Page.Loading('stop');
			$('#action_'+reject_user_id).html(lang_take_no).css('color', '#888');
		});
	},
	delet: function(user_id, atype){
		if(atype){
			var ava_s1 = $('#ava_'+user_id).attr('src');
			var ava = ava_s1.replace('/users/'+user_id+'/', '/users/'+user_id+'/');
		} else
			var ava = $('#ava_'+user_id).attr('src');
		
		Box.Show('del_friend_'+user_id, 410, lang_title_del_photo, '<div style="padding:15px; color:#8f99a2; text-align:center;"><img src="'+ava+'" alt="" /><br /><br />Вы уверены, что хотите удалить этого пользователя из списка друзей?</div>', lang_box_canсel, lang_box_yes, 'friends.goDelte('+user_id+', '+atype+'); return false');
	},
	goDelte: function(user_id, atype){
		$('#box_loading').show();
		$.post('/index.php?go=friends&act=delete', {delet_user_id: user_id}, function(data){
			if(atype > 0){
				Page.Go(location.href);
			} else {
				$('#friend_'+user_id).remove();
				updateNum('#friend_num');
			}
			
			Box.Close('del_friend_'+user_id);
		});
	}
}

//FAVE
var fave = {
	add: function(fave_id){
		Page.Loading('start');
		$.post('/index.php?go=fave&act=add', {fave_id: fave_id}, function(data){
			if(data == 'no_user')
				Box.Info('add_fave_err_'+fave_id, lang_dd2f_no, lang_no_user_fave, 300);
			else if(data == 'yes_user')
				Box.Info('add_fave_err_'+fave_id, lang_dd2f_no, lang_yes_user_fave, 300);
				
			$('#addfave_but').attr('onClick', 'fave.delet('+fave_id+'); return false').attr('href', '/');
			$('#text_add_fave').text(lang_del_fave);
			Page.Loading('stop');
		});
	},
	delet: function(fave_id){
		Page.Loading('start');
		$.post('/index.php?go=fave&act=delet', {fave_id: fave_id}, function(data){
			$('#addfave_but').attr('onClick', 'fave.add('+fave_id+'); return false').attr('href', '/');
			$('#text_add_fave').text(lang_add_fave);
			Page.Loading('stop');
		});
	},
	del_box: function(fave_id){
		Box.Show('del_fave', 410, lang_title_del_photo, '<div style="padding:15px;">'+lang_fave_info+'</div>', lang_box_canсel, lang_box_yes, 'fave.gDelet('+fave_id+'); return false');
	},
	gDelet: function(fave_id){
		$('#box_loading').show();
		$.post('/index.php?go=fave&act=delet', {fave_id: fave_id}, function(data){
			$('#user_'+fave_id).remove();
			Box.Close('del_fave');
			
			fave_num = $('#fave_num').text();
			
			$('#fave_num').text(fave_num-1);

			if($('#fave_num').text() < 1){
				$('#speedbar').text(lang_dd2f_no);
				$('#page').html(lang_fave_no_users);
			}
				
		});
	}
}

//MESSAGES
var Messages = {
	Write: function(user_id, name){
		var content = '<div style="padding:20px">'+
		'<textarea id="msg" placeholder="Введите текст сообщения" style="width:344px; resize:vertical; height:60px;"></textarea><div class="clear"></div>'+
		'</div>';
		Box.Show('new_msg', 400, lang_new_msg, content, true, lang_new_msg_send, 'Messages.Send('+user_id+'); return false');
		$('#msg').focus();
	},
	Send: function(for_user_id){
		var msg = $('#msg').val();
		if(msg != 0){
			$('#box_loading').show();
			$.post('/index.php?go=messages&act=send', {for_user_id: for_user_id, msg: msg}, function(data){
				Box.Close('new_msg');
				if(data == 'max_strlen')
					Box.Info('msg_info', lang_dd2f_no, lang_msg_max_strlen, 300, 2000);
				else if(data == 'no_user')
					Box.Info('msg_info', lang_dd2f_no, lang_no_user_fave, 300, 2000);
				else if(data == 'err_privacy')
					Box.Info('msg_info', lang_pr_no_title, lang_pr_no_msg, 400, 4000);
				else
					Box.Info('msg_info', lang_msg_ok_title, lang_msg_ok_text, 300, 2200);
			});
		} else {
			$('#msg').val('');
			$('#msg').focus();
		}
	},
	Search: function(folder){
		var msg_query = $('#msg_query').val();
		if(folder)
			var se_folder = '&act=outbox';
		else
			var se_folder = '';
			
		if(msg_query != 0 && msg_query != 'Поиск по полученным сообщениям' && msg_query != 'Поиск по отправленным сообщениям'){
			var se_query = '&se_query='+encodeURIComponent(msg_query);
			Page.Go('/index.php?go=messages'+se_folder+se_query);
		} else {
			$('#msg_query').val('');
			$('#msg_query').focus();
			$('#msg_query').css('color', '#000');
		}
	},
	Delete: function(mid, folder){
		$('#del_text_'+mid).remove();
		$('#del_load_'+mid).show();
		$.post('/index.php?go=messages&act=delet', {mid: mid, folder: folder}, function(){
			$('#bmsg_'+mid).remove();
			$('#del_load_'+mid).remove();
			updateNum('#all_msg_num');
			myhtml.title_close(mid);
		});
	},
	Reply: function(for_user_id, type){
		var theme = $('#theme_value').val();
		var msg = $('#msg_value').val();
		var attach_files = $('#vaLattach_files').val();
		if(msg != 0 || attach_files != 0){
			if(type == 'reply')
				butloading('msg_sending', 50, 'disabled');
			else
				butloading('msg_sending', 56, 'disabled');
			$.post('/index.php?go=messages&act=send', {for_user_id: for_user_id, theme: theme, msg: msg, attach_files: attach_files}, function(data){
				if(data == 'max_strlen')
					Box.Info('msg_info', lang_dd2f_no, lang_msg_max_strlen, 300);
				else if(data == 'no_user')
					Box.Info('msg_info', lang_dd2f_no, lang_no_user_fave, 300);
				else if(data == 'err_privacy')
					Box.Info('msg_info', lang_pr_no_title, lang_pr_no_msg, 400, 4000);
				else
					Page.Go('/messages/i');

				if(type == 'reply')
					butloading('msg_sending', 50, 'enabled', 'Ответить');
				else
					butloading('msg_sending', 56, 'enabled', 'Отправить');
			});
		} else {
			$('#msg_value').val('');
			$('#msg_value').focus();
		}
	},
	History: function(for_user_id, page){
		textLoad('history_lnk');
		if(page)
			Page.Loading('start');
		$.post('/index.php?go=messages&act=history', {for_user_id: for_user_id, page: page}, function(data){
			$('#history_lnk').hide();
			$('.msg_view_history_title').show();
			$('#msg_historyies').html(data);
			if(page)
				Page.Loading('stop');
		});
	}
}

//SUBSCRIPTIONS
var subscriptions = {
	followers: function(uid, page_num, followers_num){
		if(page_num){
			page = '&page='+page_num;
		} else {
			page = '';
			page_num = 1;
		}
		Box.Page('/index.php?go=subscriptions&act=followers', 'uid='+uid+'&followers_num='+followers_num+page, 'followers', 340, 'Followers', false, '');    
	},
	add: function(for_user_id){
		followers_num = parseInt($('#followers_num').text())+1;
		Page.Loading('start');
		$.post('/index.php?go=subscriptions&act=add', {for_user_id: for_user_id}, function(d){
			Page.Loading('stop');
			$('#text_add_subscription').text(lang_unsubscribe);
			$('#lnk_unsubscription').attr('onClick', 'subscriptions.del('+for_user_id+'); return false');
			if(!followers_num){
				$('#followers_block').show();
				followers_num = 1;
			}
			$('#followers_num').text(followers_num)
		});
	},
	del: function(del_user_id){
		var followers_num = parseInt($('#followers_num').text())-1;
		Page.Loading('start');
		$.post('/index.php?go=subscriptions&act=del', {del_user_id: del_user_id}, function(){
			Page.Loading('stop');
			$('#text_add_subscription').text(lang_subscription);
			$('#lnk_unsubscription').attr('onClick', 'subscriptions.add('+del_user_id+'); return false');
			if(!followers_num){
				followers_num = '';
				$('#followers_block').hide();
			}
			$('#followers_num').text(followers_num)
		});
	},
	all: function(for_user_id, page_num, following_num){
		if(page_num)
			page = '&page='+page_num;
		else {
			page = '';
			page_num = 1;
		}
			
		Box.Page('/index.php?go=subscriptions&act=all', 'for_user_id='+for_user_id+'&following_num='+following_num+page, 'following', 340, lang_subscription_box_title, false);
	}
}

//VIDEOS
var videos = {
	add: function(){
		Box.Page('/index.php?go=videos&act=add', '', 'add_video', 450, lang_video_new, true, lang_album_create, 'videos.send(); return false');
	},
	load: function(){
		video_lnk = $('#video_lnk').val();
		good_video_lnk = $('#good_video_lnk').val();
		if(videos.serviece(video_lnk)){
			if(video_lnk != 0){
				if(video_lnk != good_video_lnk){
					$('#box_loading').show();
					$.post('/index.php?go=videos&act=load', {video_lnk: video_lnk}, function(data){
						if(data == 'no_serviece'){
							$('#no_serviece').show();
						} else {
							row = data.split(':|:');
							$('#result_load').show();
							$('#photo').html('<img src="'+row[0]+'" alt="" width="100px;" />');
							$('#title').val(row[1]);
							$('#descr').val(row[2]);
							$('#good_video_lnk').val(video_lnk);
							$('#no_serviece').hide();
						}
						$('#box_button').show();
						$('#box_loading').hide();
					});
				} else
					$('#no_serviece').hide();
			} else
				$('#result_load').hide();
		} else
			$('#no_serviece').show();
	},
	serviece: function(request){
		var pattern = new RegExp(/http:\/\/www.youtube.com|http:\/\/youtube.com|http:\/\/rutube.ru|http:\/\/www.rutube.ru|http:\/\/www.vimeo.com|http:\/\/vimeo.com|http:\/\/smotri.com|http:\/\/www.smotri.com/i);
		return pattern.test(request);
	},
	send: function(notes){
		title = $('#title').val();
		good_video_lnk = $('#good_video_lnk').val();
		descr = $('#descr').val();
		photo = $('#res_photo_ok').attr('src');
		if(good_video_lnk != 0){
			if(title != 0){
				$('#box_loading').show();
				$('#box_but').hide();
				$.post('/index.php?go=videos&act=send', {title: title, good_video_lnk: good_video_lnk, descr: descr, photo: photo, privacy: $('#privacy').val(), notes: notes}, function(d){
					$('#box_loading').hide();
					Box.Close('add_video');
					d = d.split('|');
					Page.Go('/videos');
				});
			} else
				Box.Info('msg_videos', lang_dd2f_no, lang_videos_no_url, 300);
		} else
			Box.Info('msg_videos', lang_dd2f_no, lang_videos_no_url, 300);
	},
	page: function(){
		name = $('.scroll_page').attr('id');
		get_user_id = $('#user_id').val();
		last_id = $('#'+name+' input:last').attr('value');
		set_last_id = $('#set_last_id').val();
		videos_size = $('#videos_num').val();
		videos_opened_num = $('.onevideo').size();
		
		if(set_last_id != last_id && videos_size > 20 && videos_size != videos_opened_num){
			$('#'+name).append(lang_scroll_loading);
			$.post('/index.php?go=videos&act=page', {get_user_id: get_user_id, last_id: last_id}, function(d){
				$('#'+name).append(d);
				$('#scroll_loading').remove();
			});
			$('#set_last_id').val(last_id);
		}
	},
	scroll: function(){
		$(window).scroll(function(){
			if($(document).height() - $(window).height() <= $(window).scrollTop()+250){
				videos.page();
			}
		});
	},
	delet: function(vid, type){
		Box.Show('del_video_'+vid, 400, lang_title_del_photo, '<div style="padding:15px;" id="text_del_video_'+vid+'">'+lang_videos_del_text+'</div>', lang_box_canсel, lang_box_yes, 'videos.startDel('+vid+', \''+type+'\'); return false');
		$('#video_object').hide(); //скрываем код видео, чтоб модал-окно норм появилось
	},
	startDel: function(vid, type){
		$('#box_but').hide();
		$('#box_loading').show();
		$('#text_del_video_'+vid).text(lang_videos_deletes);
		$.post('/index.php?go=videos&act=delet', {vid: vid}, function(){
			$('#video_'+vid).html(lang_videos_delok);
			Box.Close('del_video_'+vid);
			updateNum('#nums');
			
			if(type == 1)
				$('#video_del_info').html(lang_videos_delok_2);
		});
	},
	editbox: function(vid){
		Box.Page('/index.php?go=videos&act=edit', 'vid='+vid, 'edit_video', 510, lang_video_edit, lang_box_canсel, lang_box_save, 'videos.editsave('+vid+'); return false', 255, 0, 1, 1, 0);
		$('#video_object').hide(); //скрываем код видео, чтоб модал-окно норм появилось
	},
	editsave: function(vid){
		var title = $('#title').val();
		var descr = $('#descr').val();
		$('#box_but').hide();
		$('#box_loading').fadeIn();
		$.post('/index.php?go=videos&act=editsave', {vid: vid, title: title, descr: descr, privacy: $('#privacy').val()}, function(d){
			$('#video_title_'+vid+', #video_full_title_'+vid).text(title);
			$('#video_descr_'+vid+', #video_full_descr_'+vid).html(d);
			Box.Close('edit_video');
			$('#video_object').show(); //показываем код видео, чтоб модал-окно норм появилось
		});
	},
	show: function(vid, h, close_link){
		if(vid){
			Page.Loading('start');
			$.post('/index.php?go=videos&act=view', {vid: vid, close_link: close_link}, function(data){
				Page.Loading('stop');
				if(data == 'no_video'){
					Box.Info('no_video', lang_dd2f_no, lang_video_info_text, 300);
					$('html, body').css('overflow-y', 'auto');
				} else if(data == 'err_privacy'){
					ShowInfo('red', lang_pr_no_title, 2000);
					$('html, body').css('overflow-y', 'auto');
				} else {
					$('html').css('overflow', 'hidden');
					history.pushState({link:h}, null, h);
					Box.Show('video_show', 800, 'Video', data, false);
					$('#video_show_'+vid).show();
				}
			});
		}
	},
	prev: function(req_href){
		filter_one = req_href.split('_');
		filter_two = filter_one[0].split('video');
		video_url = '/video'+filter_two[1]+'_'+filter_one[1];
		videos.show(filter_one[1], video_url);
	},
	addcomment: function(vid){
		comment = $('#comment').val();
		if(comment != 0){
			butloading('add_comm', '56', 'disabled', '');
			$.post('/index.php?go=videos&act=addcomment', {vid: vid, comment: comment}, function(d){
				$('#comments').append(d);
				$('#comment').val('');
				butloading('add_comm', '56', 'enabled', lang_box_send);
			});
		} else {
			$('#comment').val('');
			$('#comment').focus();
		}
	},
	allcomment: function(vid, num, owner_id){
		textLoad('all_lnk_comm');
		$('#all_href_lnk_comm').attr('onClick', '').attr('href', '#');
		$.post('/index.php?go=videos&act=all_comm', {vid: vid, num: num, owner_id: owner_id}, function(d){
			$('#all_href_lnk_comm').hide();
			$('#all_comments').html(d);
		});
	},
	deletcomm: function(comm_id){
		textLoad('video_del_but_'+comm_id);
		$.post('/index.php?go=videos&act=delcomment', {comm_id: comm_id}, function(){
			$('#video_comment_'+comm_id).html(lang_del_comm);
		});
	},
	setEvent: function(event, owner_id, close_link){
		var oi = (event.target) ? event.target.id: ((event.srcElement) ? event.srcElement.id : null);
		var el = oi.substring(0, 10);
		if(el == 'video_show')
			videos.close(owner_id, close_link);
	},
	addmylist: function(vid){
		$('#addok').html('Добавлено');
		$.post('/index.php?go=videos&act=addmylist', {vid: vid});
	}
}

//SEARCH
function CheckRequestSearch(request){
	var pattern = new RegExp(/search/i);
 	return pattern.test(request);
}

var gSearch = {
	go: function(){
		var query = $('#query').val();
		if(!query)
			var query = $('#fast_search_txt').text();
		
		//Если открыта страница поиска
		if(CheckRequestSearch(location.href)){
			query = $('#query_full').val();
		}
		
		if(query != 0){
			lnk = '/?go=search&query='+encodeURIComponent(query)+'&type='+1;
			Page.Loading('start');
			$.post(lnk, {ajax: 'yes'}, function(data){
				Page.Loading('stop');
				history.pushState({link:lnk}, null, lnk);
				$('#content').html(data);
				//Прокручиваем страницу в самый верх
				$('html, body').scrollTop(0);
				//Удаляем кеш фоток и видео
				$('.photo_view, .box, .info_box, .video_view').remove();
				//Возвращаем scroll
				$('html, body').css('overflow-y', 'auto');
				$('#query_full').focus();
				$('.fast_search_bg').hide();
			});
		} else {
			$('#query, #query_full').val('');
			$('#query, #query_full').focus();
		}
	}
}

//CHECKBOX
var myhtml = {
	checkbox: function(id){
		name = '#'+id;
		$(name).addClass('html_checked');
		
		if(ge('checknox_'+id)){
			myhtml.checkbox_off(id);
		} else {
			$(name).append('<div id="checknox_'+id+'"><input type="hidden" id="'+id+'" /></div>');
			$(name).val('1');
		}
	},
	checkbox_off: function(id){
		name = '#'+id;
		$('#checknox_'+id).remove();
		$(name).removeClass('html_checked');
		$(name).val('');
	},
	checked: function(arr){
		$.each(arr, function(){
			myhtml.checkbox(this);
		});
	},
	title: function(id, text, prefix_id, pad_left){
		if(!pad_left)
			pad_left = 5;
			
		$("body").append('<div id="js_title_'+id+'" class="js_titleRemove"><div id="easyTooltip">'+text+'</div><div class="tooltip"></div></div>');	
		xOffset = $('#'+prefix_id+id).offset().left-pad_left;
		yOffset = $('#'+prefix_id+id).offset().top-32;
		
		$('#js_title_'+id)
			.css("position","absolute")
			.css("top", yOffset+"px")
			.css("left", xOffset+"px")						
			.css("display","none")
			.fadeIn('fast');
			
		$('#'+prefix_id+id).mouseout(function(){
			$('.js_titleRemove').remove();
		});
	},
	title_close: function(id){
		$('#js_title_'+id).remove();
	},
	updateAjaxNav: function(gc, pref, num, page){
		$.get('/updateAjaxNav', {gcount: gc, pref: pref, num: num, page:page}, function(data){
			$('#nav').html(data);
		});
	},
	scrollTop: function(){
		$('.scroll_fix_bg').hide(); 
		$(window).scrollTop(0);
	}
}

//WALL
var wall = {
	ShowMenu: function(id){
		$('#wall_menu').remove();
		$('#profile_wall_arrow'+id).append('<menu id="wall_menu"><div id="notification_box_arrow"></div><li onClick="wall.delet('+id+'); return false" id="wall_del_'+id+'">Удалить запись со стены</li><li onClick="Report.WallSend(\'wall\', '+id+'); return false" id="wall_spam_'+id+'">Отметить запись как спам</li></menu>');
	},
	send: function(){
		wall_text = $('#wall_text').val();
		
		attach_files = $('#vaLattach_files').val();
		for_user_id = location.href.split('http://'+location.host+'/u');
			
		if(wall_text != 0 || attach_files != 0){
			butloading('wall_send', 56, 'disabled');
			$.post('/index.php?go=wall&act=send', {wall_text: wall_text, for_user_id: for_user_id[1], attach_files: attach_files}, function(data){
				if(data == 'err_privacy'){
					ShowInfo('red', lang_pr_no_title, 2000);
				} else {
					$('#wall_records').html(data);
					$('#wall_all_record').html('');
					$('#wall_text').val('');
					$('#attach_files').hide();
					$('#attach_files').html('');
					$('#vaLattach_files').val('');
				}
				butloading('wall_send', 56, 'enabled', lang_box_send);
			});
		} else {
			ShowInfo('red', 'А текст.. Текст-то забыли!', 2000);
		}
	},
	delet: function(rid){		
		$('#wall_record_'+rid).fadeIn("slow").html('<div style="color:#869ba7; padding:15px; text-align:center;">'+lang_wall_del_ok+'</div>');
		setTimeout("$('#wall_recordss_"+rid+"').fadeOut('slow')", 2500);
		$('#wall_fast_block_'+rid).remove();
		myhtml.title_close(rid);
		$.post('/index.php?go=wall&act=delet', {rid: rid});
	},
	fast_comm_del: function(rid){
		$('#wall_fast_comment_'+rid).html('<span style="color:#869ba7; padding:0px; text-align:center;">'+lang_wall_del_com_ok+'</span>');
		$.post('/index.php?go=wall&act=delet', {rid: rid});
	},
	page: function(for_user_id){
		textLoad('wall_link');
		$('#wall_l_href').attr('onClick', '');
		last_id = $('.profile_wall:last').attr('id').replace('wall_record_', '');
		rec_num = parseInt($('#wall_rec_num').text());
		$.post('/index.php?go=wall&act=page', {last_id: last_id, for_user_id: for_user_id}, function(data){
			$('#wall_all_record').append(data);
			$('#wall_l_href').attr('onClick', 'wall.page('+for_user_id+'); return false');
			$('#wall_link').html(lang_wall_all_lnk);
			count_record = $('.profile_wall').size();
			if(count_record >= rec_num)
				$('#wall_l_href').hide();
		});
	},
	fast_send: function(rid, for_user_id, type){
		wall_text = $('#fast_text_'+rid).val();
		if(wall_text != 0){
			butloading('fast_buts_'+rid, 56, 'disabled');
			$.post('/index.php?go=wall&act=send', {wall_text: wall_text, for_user_id: for_user_id, rid: rid, type: type}, function(data){
				if(data == 'err_privacy'){
					ShowInfo('red', lang_pr_no_title, 2000);
				} else {
					$('#ava_rec_'+rid).addClass('wall_ava_mini'); //добавляем для авы класс wall_ava_mini
					$('#fast_form_'+rid).remove(); //удаляем полей texatra 
					$('#fast_comm_link_'+rid).remove(); //удаляем кнопку комментировать
					$('#wall_fast_block_'+rid).html(data); //выводим сам результат
					$('.wall_fast_text').val(''); //Текстовое значение полей Texatrea делаем 0
					wall.fast_form_close();
				}
				butloading('fast_buts_'+rid, 56, 'enabled', lang_box_send);
			});
		} else {
			$('#fast_text_'+rid).val('');
			$('#fast_text_'+rid).focus();
		}
	},
	all_comments: function(rid, for_user_id, type){
		textLoad('wall_all_comm_but_'+rid);
		$('#wall_all_but_link_'+rid).attr('onClick', '');
		$.post('/index.php?go=wall&act=all_comm', {fast_comm_id: rid, for_user_id: for_user_id, type: type}, function(data){
			if(data == 'err_privacy')
				ShowInfo('red', lang_pr_no_title, 2000);
			else
				$('#wall_fast_block_'+rid).html(data); //выводим сам результат
		});
	},
	wall_add_like: function(rec_id, user_id, type){
		if($('#wall_like_cnt'+rec_id).text())
			var wall_like_cnt = parseInt($('#wall_like_cnt'+rec_id).text())+1;
		else {
			$('#update_like'+rec_id).val('1');
			var wall_like_cnt = 1;
		}
		$('#like_ico').show();
		$('#wall_like_cnt'+rec_id).html(wall_like_cnt);
		$('#wall_like_link'+rec_id).css('color', '#3a81ad').attr('onClick', 'wall.wall_remove_like('+rec_id+', '+user_id+', \''+type+'\')');
		updateNum('#like_text_num'+rec_id, 1);
		
		$.post('/index.php?go=wall&act=like_yes', {rid: rec_id});

	},
	wall_remove_like: function(rec_id, user_id, type){
		var wall_like_cnt = parseInt($('#wall_like_cnt'+rec_id).text())-1;
		if(wall_like_cnt <= 0){
			var wall_like_cnt = '';
			$('#like_ico').hide();
		}
		
		$('#wall_like_cnt'+rec_id).html(wall_like_cnt);
		$('#wall_like_link'+rec_id).css('color', '#6a7481').attr('onClick', 'wall.wall_add_like('+rec_id+', '+user_id+', \''+type+'\')');
		updateNum('#like_text_num'+rec_id);

		$.post('/index.php?go=wall&act=like_no', {rid: rec_id});
	},
	all_liked_users: function(rid, page_num, liked_num){
		if(page_num)
			page = '&page='+page_num;
		else {
			page = '';
			page_num = 1;
		}
			
		if(liked_num > 0){
			Box.Page('/index.php?go=wall&act=all_liked_users', 'rid='+rid+'&liked_num='+liked_num+page, 'all_liked_users_'+rid+page_num, 340, lang_wall_liked_users, false);
		} else {
			Box.Page('/index.php?go=wall&act=all_liked_users', 'rid='+rid+'&liked_num='+liked_num+page, 'all_liked_users_'+rid+page_num, 340, lang_wall_liked_users, false);
		}
	},
	attach_insert: function(type, data, action_url, uid){
		
		$('#attach_files').show();
		var attach_id = Math.floor(Math.random()*(1000-1+1))+1;
		var for_user_id = location.href.split('/u');
		if(uid)
			for_user_id[1] = uid;
		
		//Если вставляем фотографию
		if(type == 'photo'){
			Box.Close('all_photos');
			res_attach_id = 'photo_'+attach_id;
			$('#attach_files').append('<span id="attach_file_'+res_attach_id+'" class="attach_file attaced_photo"><div class="wall_attach_photo fl_l"><div class="wall_attach_del" onMouseOver="myhtml.title(\''+res_attach_id+'\', \''+lang_wall_no_atttach+'\', \'wall_photo_\')" onMouseOut="myhtml.title_close(\''+res_attach_id+'\')" onClick="wall.attach_deletePhoto(\''+res_attach_id+'\', \'photo_u|'+action_url+'||\')" id="wall_photo_'+res_attach_id+'"></div><img src="'+data+'" alt="" /></div></span>');
			$('#vaLattach_files').val($('#vaLattach_files').val()+'photo_u|'+action_url+'||');
			
			count = $('.attaced_photo').size();
			if(count == 1){
				$('#attach_photo').attr('onClick', '').css('cursor', 'default');
				$('#attach_photo').attr('onMouseOver', 'myhtml.title("photo", "Можно прикрепить не более одной фотографии!", "attach_", "-2")');
			}
		}
		
		//Если вставляем видео
		if(type == 'video'){
			Box.Close('all_videos');
			res_attach_id = 'video_'+attach_id;
			aPslit = action_url.split('|');
			action_url = action_url.replace('http://'+location.host+'/uploads/videos/'+aPslit[2]+'/', '');
			$('#attach_files').append('<span id="attach_file_'+res_attach_id+'" class="attach_file attaced_video"><div class="wall_attach_photo fl_l"><div class="wall_attach_del" onMouseOver="myhtml.title(\''+res_attach_id+'\', \''+lang_wall_no_atttach+'\', \'wall_video_\')" onMouseOut="myhtml.title_close(\''+res_attach_id+'\')" onClick="wall.attach_deleteVideo(\''+res_attach_id+'\', \'video|'+action_url+'||\')" id="wall_video_'+res_attach_id+'"></div><img src="'+data+'" alt="" /></div></span>');
			$('#vaLattach_files').val($('#vaLattach_files').val()+'video|'+action_url+'||');
			count = $('.attaced_video').size();
			if(count == 1){
				$('#attach_video').attr('onClick', '').css('cursor', 'default');
				$('#attach_video').attr('onMouseOver', 'myhtml.title("video", "Можно прикрепить не более одной видеозаписи!", "attach_", "-2")');
			}
		}
	},
	attach_deletePhoto: function(id, realId){
		$('#vaLattach_files').val($('#vaLattach_files').val().replace(realId, ''));
		$('#attach_file_'+id).remove();
		myhtml.title_close(id);
		count = $('.attaced_photo').size();
		if(!count)
			$('#attach_file_'+id).hide();

		if(count < 1){
			$('#attach_photo').attr('onClick', 'wall.attach_addphoto()').css('cursor', 'pointer');
			$('#attach_photo').attr('onMouseOver', 'myhtml.title("photo", "Прикрепить фотографию", "attach_", "-2")');
		}
	},
	attach_deleteVideo: function(id, realId){
		$('#vaLattach_files').val($('#vaLattach_files').val().replace(realId, ''));
		$('#attach_file_'+id).remove();
		myhtml.title_close(id);
		count = $('.attaced_video').size();
		if(!count)
			$('#attach_file_'+id).hide();

		if(count < 1){
			$('#attach_video').attr('onClick', 'wall.attach_addvideo()').css('cursor', 'pointer');
			$('#attach_video').attr('onMouseOver', 'myhtml.title("video", "Прикрепить видеозапись", "attach_", "-2")');
		}
	},
	attach_addphoto: function(id, page_num){
		if(page_num)
			page = '&page='+page_num;
		else {
			page = '';
			page_num = 1;
		}
		
		Box.Page('/index.php?go=albums&act=all_photos_box', page, 'all_photos', 610, lang_wall_attatch_photos, false);
	},
	attach_addvideo: function(id, page_num){
		if(page_num)
			page = '&page='+page_num;
		else {
			page = '';
			page_num = 1;
		}
		
		Box.Page('/index.php?go=videos&act=all_videos', page, 'all_videos', 610, lang_wall_attatch_photos, false);

	},
	FullText: function(rid){
		$('#hide_wall_rec'+rid).css('max-height', 'none');
		$('#hide_wall_rec_lnk'+rid).hide();
	}
}

//NEWS
var news = {
	page: function(){
		var type = $('#type').val();
		$('#wall_l_href_news').attr('onClick', '');
		if($('#loading_news').text() == 'Показать предыдущие новости'){
			textLoad('loading_news');
			$.post('/index.php?go=news&type='+type, {page: 1, page_cnt: page_cnt}, function(d){
				if(d != 'no_news'){
					$('#news').append(d);
					$('#wall_l_href_news').attr('onClick', 'news.page(\''+type+'\')');
					$('#loading_news').html('Показать предыдущие новости');
					page_cnt++;
				} else
					$('#wall_l_href_news').hide();
			});
		}
	},
	showWallText: function(id){
		var wh2 = $('#2href_text_'+id).width();
		var wh = $('#href_text_'+id).width()-wh2-40;
		$('.news_wall_msg_bg').hide();
		$('#wall_text_'+id).fadeIn('fast').css('margin-left', wh);
		$('#wall_text_'+id).mouseover(function(){
			$('#wall_text_'+id).fadeOut('fast');
		});
	},
	hideWallText: function(id){
		$('#wall_text_'+id).fadeOut('fast');
	}
}

//SETTINGS
var settings = {
	savenewmail: function(){
		var email = $('#email').val();
		if(settings.isValidEmailAddress(email)){
			butloading('saveNewEmail', 'auto', 'disabled', '');
			$.post('/index.php?go=settings&act=change_mail', {email: email}, function(d){
				if(d == 1){
					$('#err_email').html('Этот E-Mail адрес уже занят.').show();
				} else {
					Box.Info('email', 'Удача!', 'На <b>оба</b> почтовых ящика придут письма с подтверждением.', 240, 4200);
					$('#email').val('');
				}
				butloading('saveNewEmail', 'auto', 'enabled', 'Сохранить адрес');
			});
		} else {
			ShowInfo('red', 'Неправильный E-mail адрес!', 2000);
		}
	},
	isValidEmailAddress: function(emailAddress){
		var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
		return pattern.test(emailAddress);
	},
	saveNewPwd: function(){
		var old_pass = $('#old_pass').val();
		var new_pass = $('#new_pass').val();
		var new_pass2 = $('#new_pass2').val();
		if(old_pass != 0){
			if(new_pass != 0){
				if(new_pass2 != 0){
					butloading('saveNewPwd', 'auto', 'disabled');
					$.post('/index.php?go=settings&act=newpass', {old_pass: old_pass, new_pass: new_pass, new_pass2: new_pass2}, function(data){
						$('.pass_errors').hide();
						if(data == 1)
							ShowInfo('red', 'Пароль не изменён, так как прежний пароль введён неправильно', 4000);
						else if(data == 2)
							ShowInfo('red', 'Пароль не изменён, так как новый пароль повторен неправильно', 4000);
						else
							Box.Info('password', 'Удача!', 'Пароль успешно изменён!', 240, 4200);
							
						butloading('saveNewPwd', 'auto', 'enabled', 'Изменить пароль');
					});
				} else
					ShowInfo('red', 'Повторите новый пароль!', 2000);
			} else
				ShowInfo('red', 'Введите новый пароль!', 2000);
		} else
			ShowInfo('red', 'Введите старый пароль!', 2000);
	},
	savePrivacy: function(){
		var val_msg = $('#val_msg').val();
		var val_wall1 = $('#val_wall1').val();
		var val_wall2 = $('#val_wall2').val();
		var val_wall3 = $('#val_wall3').val();
		var val_info = $('#val_info').val();
		butloading('savePrivacy', 'auto', 'disabled');
		$.post('/index.php?go=settings&act=saveprivacy', {val_msg: val_msg, val_wall1: val_wall1, val_wall2: val_wall2, val_wall3: val_wall3, val_info: val_info}, function(){
			Box.Info('privacy', 'Удача!', 'Новые настройки приватности вступили в силу!', 240, 3000);
			butloading('savePrivacy', 'auto', 'enabled', 'Сохранить настройки');
		});
	},
	addblacklist: function(bad_user_id, type){
		Page.Loading('start');
		$.post('/index.php?go=settings&act=addblacklist', {bad_user_id: bad_user_id}, function(){
			if(type){
				$('#addblacklist_but').attr('onClick', 'settings.delblacklist('+bad_user_id+', 1); return false');
				$('#text_add_blacklist').text('Разблокировать');
				Page.Loading('stop');
			} else {
				$('#del_'+bad_user_id).attr('onClick', 'settings.delblacklist('+bad_user_id+'); return false');
				$('#del_'+bad_user_id).text('Разблокировать');
				updateNum('#badlistnum', 1);
				Page.Loading('stop');
			}
		});
	},
	delblacklist: function(bad_user_id, type){
		Page.Loading('start');
		$.post('/index.php?go=settings&act=delblacklist', {bad_user_id: bad_user_id}, function(){
			if(type){
				$('#addblacklist_but').attr('onClick', 'settings.addblacklist('+bad_user_id+', 1); return false');
				$('#text_add_blacklist').text('Заблокировать');
				Page.Loading('stop');
			} else {
				$('#del_'+bad_user_id).attr('onClick', 'settings.addblacklist('+bad_user_id+'); return false');
				$('#del_'+bad_user_id).text('Заблокировать');
				updateNum('#badlistnum');
				Page.Loading('stop');
			}
		});
	}
}

//CROP
var crop = {
	start: function(id){
		$('.pinfo, .photo_prev_but, .photo_next_but').hide();
		$('#save_crop_text'+id).show();
		var x1w = $('#ladybug_ant'+id).width()-50;
		var y1h = $('#ladybug_ant'+id).height()-50;
		$('#i_left'+id).val('50');
		$('#i_top'+id).val('50');
		$('#i_width'+id).val(x1w);
		$('#i_height'+id).val(y1h);
		$('#ladybug_ant'+id).imgAreaSelect({
			minWidth: 100, 
			minHeight: 100, 
			handles: true, 
			x1: 50, 
			y1: 50, 
			x2: x1w, 
			y2: y1h,
			onSelectEnd: function(img, selection){
				$('#i_left'+id).val(selection.x1);
				$('#i_top'+id).val(selection.y1);
				$('#i_width'+id).val(selection.width);
				$('#i_height'+id).val(selection.height);
			}
			
		});
	},
	close: function(id){
		$('.pinfo, .photo_prev_but, .photo_next_but').show();
		$('#save_crop_text'+id).hide();
		$('#ladybug_ant'+id).imgAreaSelect({
			remove: true
		});
	},
	save: function(pid, uid){
		var i_left = $('#i_left'+pid).val();
		var i_top = $('#i_top'+pid).val();
		var i_width = $('#i_width'+pid).val();
		var i_height = $('#i_height'+pid).val();
		Page.Loading('start');
		$.post('/index.php?go=photo&act=crop', {i_left: i_left, i_top: i_top, i_width: i_width, i_height: i_height, pid: pid}, function(data){Page.Go('/u'+uid);});
	}
}

//SUPPORT
var support = {
	send: function(){
		var title = $('#title').val();
		var question = $('#question').val();
		if(title != 0 && title != 'Пожалуйста, добавьте заголовок к Вашему вопросу..'){
			if(question != 0 && question != 'Пожалуйста, расскажите о Вашей проблеме чуть подробнее..'){
				$('#cancel').hide();
				butloading('send', '56', 'disabled', '');
				$.post('/index.php?go=support&act=send', {title: title, question: question}, function(data){
					if(data == 'limit'){
						Box.Info('err', lang_support_ltitle, lang_support_ltext, 280, 2000);
					} else {
						var qid = data.split('r|x');
						$('#data').html(qid[0]);
						history.pushState({link:'/support?act=show&qid='+qid[1]}, null, '/support?act=show&qid='+qid[1]);
					}
					butloading('send', '56', 'enabled', 'Отправить');
				});
			} else
				setErrorInputMsg('question');
		} else
			setErrorInputMsg('title');
	},
	delquest: function(qid){
		Box.Show('del_quest', 400, lang_title_del_photo, '<div style="padding:15px;" id="text_del_quest">'+lang_support_text+'</div>', lang_box_canсel, lang_box_yes, 'support.startDel('+qid+'); return false');
	},
	startDel: function(qid){
		$('#box_loading').show();
		$.post('/index.php?go=support&act=delet', {qid: qid}, function(){
			Page.Go('/support');
		});
	},
	answer: function(qid, uid){
		var answer = $('#answer').val();
		if(answer != 0 && answer != 'Комментировать..'){
			butloading('send', '56', 'disabled', '');
			$.post('/index.php?go=support&act=answer', {answer: answer, qid: qid}, function(data){
				if(uid == 0)
					$('#status').text('Есть ответ.');
				else
					$('#status').text('Вопрос ожидает обработки.');
				$('#answers').append(data);
				$('#answer').val('');
				butloading('send', '56', 'enabled', lang_box_send);
			});
		} else
			setErrorInputMsg('answer');
	},
	delanswe: function(id){
		$('#asnwe_'+id).html(lang_del_comm);
		$.post('/index.php?go=support&act=delet_answer', {id: id});
	},
	close: function(qid){
		butloading('close', '30', 'disabled', '');
		$.post('/index.php?go=support&act=close', {qid: qid}, function(){
			$('#status').text('Есть ответ.');
			$('#close_but').hide();
		});
	}
}

//BLOG
var blog = {
	add: function(){
		var title = $('#title').val();
		var text = $('#text').val();
		if(title != 0){
			if(text != 0){
				butloading('notes_sending', 74, 'disabled');
				$.post('/index.php?go=blog&act=send', {title: title, text: text}, function(){
					Page.Go('/blog');
				});
			} else
				setErrorInputMsg('text');
		} else
			setErrorInputMsg('title');
	},
	del: function(id){
		Box.Show('del_quest', 400, lang_title_del_photo, '<div style="padding:15px;" id="text_del_quest">'+lang_news_text+'</div>', lang_box_canсel, lang_box_yes, 'blog.startDel('+id+'); return false');
	},
	startDel: function(id){
		$('#box_loading').show();
		$.post('/index.php?go=blog&act=del', {id: id}, function(){
			Page.Go('/blog');
		});
	},
	save: function(id){
		var title = $('#title').val();
		var text = $('#text').val();
		if(title != 0){
			if(text != 0){
				butloading('notes_sending', 55, 'disabled');
				$.post('/index.php?go=blog&act=save', {id: id, title: title, text: text}, function(){
					Page.Go('/blog?id='+id);
				});
			} else
				setErrorInputMsg('text');
		} else
			setErrorInputMsg('title');
	}
}

//GIFTS
var gifts = {
	box: function(user_id, balance){
		Page.Loading('start');
		$.post('/index.php?go=gifts&act=view', {user_id: user_id}, function(data){
			var my_balance = 'У Вас <b>'+balance+' $.</b>';
			Box.Show('gift_send_box', 610, 'Gift send', data, true, 'Закрыть', 'Box.Close(\'gift_send_box\')', 350, 1, my_balance);
			Page.Loading('stop');
		});
	},
	showgift: function(id){
		$('#g'+id).show();
	},
	showhide: function(id){
		$('#g'+id).hide();
	},
	select: function(gid, fid){
		Box.Close('gift_send_box');
		Box.Show('send_gift'+gid, 460, lang_gifts_title, 
			'<center><img src="/uploads/gifts/'+gid+'.jpg" style="margin:30px 0px;" /></center><div class="fl_l" style="padding:5px; color:#95a7b1; font-size:14px; margin-left:20px; margin-right:5px">Тип подарка:</div><select class="fl_l" id="privacy_comment\''+gid+'\'" value="1"><option value="1">Виден всем</option><option value="2">Личный</option><option value="3">Анонимный</option></select><div class="fl_l" style="margin-left:10px; margin-top:7px;" id="addmsgtext'+gid+'"><a href="" onClick="gifts.addmssbox('+gid+'); return false">Добавить сообщение</a></div><div class="clear" style="height:10px;"></div>', 
		true, lang_box_send, 'gifts.send('+gid+', '+fid+')', 363, '', '', 0);
	},
	send: function(gfid, fid){
		var privacy = $('#privacy_comment'+gfid).val();
		var msgfgift = $('#msgfgift'+gfid).val();
		$('#box_loading').show().css('margin-top', '-5px');
		$.post('/index.php?go=gifts&act=send', {for_user_id: fid, gift: gfid, privacy: privacy, msg: msgfgift}, function(d){
			if(d == 1){
				ShowInfo('red', lang_gifts_tnoubm, 3500);
				Box.Close('send_gift');
			} else {
				Box.Info('giftok', lang_gifts_oktitle, lang_gifts_oktext, 250, 2000);
				Box.Close('send_gift'+gfid);
			}
		});
	},
	addmssbox: function(gid){
		$('.box_content').css('height', '375px');
		$('#addmsgtext'+gid).html('<textarea id="msgfgift'+gid+'" placeholder="Введите Ваше сообщение.." style="width:197px; margin-top:-7px; height:34px;"></textarea>');
		$('#msgfgift'+gid).focus();
	},
	delet: function(gid){
		$('#gift_'+gid).html('<div class="color777" style="margin-bottom:5px">Подарок удалён.</div>');
		updateNum('#num');
		$.post('/index.php?go=gifts&act=del', {gid: gid});
	}
}

//GROUPS
var groups = {
	createbox: function(){
		Box.Show('create', 490, lang_groups_new, '<div style="padding:20px"><div class="videos_text">Название</div><input type="text" id="title" maxlength="65" /></div>', lang_box_canсel, lang_groups_cretate, 'groups.creat()');
	},
	creat: function(){
		var title = $('#title').val();
		if(title != 0){
			$('#box_loading').show();
			ge('box_button').disabled = true;
			$.post('/index.php?go=groups&act=send', {title: title}, function(id){
				Box.Close();
				Page.Go('/public'+id);
			});
		}
	},
	follow: function(id){
		var followers_num = parseInt($('#followers_num').text())+1;
		$('#addsubscription_load').show();
		butloading('follow', '174', 'disabled', '');
		$.post('/index.php?go=groups&act=follow', {id: id}, function(){
			$('#yes_follow').hide();
			$('#no_follow').fadeIn('fast');
			if(!followers_num){
				followers_num = 1;
				$('#followers_block').show();
			}
			$('#followers_num').text(followers_num);
			butloading('follow', '174', 'enabled', 'Подписаться');
		});
		$.post('/index.php?go=groups&act=login', {id: id});
	},
	unfollow: function(id, user_id){
		butloading('unfollow', '174', 'disabled', '');
		var followers_num = parseInt($('#followers_num').text())-1;
		$.post('/index.php?go=groups&act=unfollow', {id: id}, function(){
			$('#no_follow').hide();
			$('#yes_follow').fadeIn('fast');
			if(!followers_num){
				followers_num = '';
				$('#followers_block').hide();
				$('#you_be_one').show();
			} 
			$('#followers_num').text(followers_num);
			butloading('unfollow', '174', 'enabled', 'Отписаться');
		});
	},
	loadphoto: function(id){
		Box.Page('/index.php?go=groups&act=loadphoto_page', 'id='+id, 'loadphoto', 400, lang_title_load_photo, lang_box_canсel, 0, 0, 0, 0, 0, 0, 0, 1);
	},
	delphoto: function(id){
		Box.Show('del_photo', 400, lang_title_del_photo, '<div style="padding:15px;">'+lang_del_photo+'</div>', lang_box_canсel, lang_box_yes, 'groups.startdelete('+id+')');
	},
	startdelete: function(id){
		$('#box_loading').show();
		ge('box_butt_create').disabled = true;
		$.post('/index.php?go=groups&act=delphoto', {id: id}, function(){
			$('#ava').attr('src', template_dir+'/images/no_avatars/no_ava_200.gif');
			$('#del_pho_but').hide();
			Box.Close();
		});
	},
	addcontact: function(id){
		Box.Page('/index.php?go=groups&act=addfeedback_pg', 'id='+id, 'addfeedback', 400, 'Добавление контактного лица', lang_box_canсel, 'Сохранить', 'groups.savefeedback('+id+')', 0, 0, 0, 0, 'upage', 0);
	},
	savefeedback: function(id){
		var upage = $('#upage').val();
		var office = $('#office').val();
		var phone = $('#phone').val();
		var email = $('#email').val();
		if($('#feedimg').attr('src') != template_dir+'/images/contact_info.png'){
			$('#box_loading').show();
			ge('box_butt_create').disabled = true;
			$.post('/index.php?go=groups&act=addfeedback_db', {id: id, upage: upage, office: office, phone: phone, email: email}, function(d){
				if(d == 1){
					Box.Info('err', 'Информация', 'Этот пользователь уже есть в списке контактов.', 300, 2000);
					ge('box_butt_create').disabled = false;
					$('#box_loading').hide();
				} else {
					Box.Close();
					Page.Go('/public'+id);
				}
			});
		} else
			setErrorInputMsg('upage');
	},
	allfeedbacklist: function(id){
		Box.Page('/index.php?go=groups&act=allfeedbacklist', 'id='+id, 'allfeedbacklist', 450, 'Контакты', '', 'Закрыть', "Page.Go('/public"+id+"');", 300, 1, 1, 1, 0, 0);
	},
	delfeedback: function(id, uid){
		$('#f'+uid+', #fb'+uid).remove();
		var si = $('.public_obefeed').size();
		updateNum('#fnumu');
		if(si <= 0){
			$('#feddbackusers').html('<div class="line_height color777" align="center">Страницы представителей, номера телефонов, e-mail<br /><a href="/public'+id+'" onClick="groups.addcontact('+id+'); return false">Добавить контакты</a></div>');
			$('.box_conetnt').html('<div align="center" style="padding-top:10px;color:#777;font-size:13px;">Список контактов пуст.</div><style>#box_bottom_left_text{padding-top:6px}</style>');
		}
		$.post('/index.php?go=groups&act=delfeedback', {id: id, uid: uid});
	},
	editfeedback: function(uid){
		$('#close_editf'+uid).hide();
		$('#editf'+uid).show();
		$('#email'+uid).val($('#email'+uid).val().replace(', ', ''));
	},
	editfeeddave: function(id, uid){
		var office = $('#office'+uid).val();
		var phone = $('#phone'+uid).val();
		var email = $('#email'+uid).val();
		$('#close_editf'+uid).show();
		$('#editf'+uid).hide();
		$('#okoffice'+uid).text(office);
		$('#okphone'+uid).text(phone);
		if(phone != 0 && email != 0)
			$('#okemail'+uid).text(', '+email);
		else
			$('#okemail'+uid).text(email);
			
		$.post('/index.php?go=groups&act=editfeeddave', {id: id, uid: uid, office: office, phone: phone, email: email});
	},
	checkFeedUser: function(){
		var upage = $('#upage').val();
		var pattern = new RegExp(/^[0-9]+$/);
		if(pattern.test(upage)){
			$.post('/index.php?go=groups&act=checkFeedUser', {id: upage}, function(d){
				d = d.split('|');
				if(d[0]){
					if(d[1])
						$('#feedimg').attr('src', '/uploads/users/'+upage+'/100_'+d[1]);
					else
						$('#feedimg').attr('src', template_dir+'/images/no_avatars/no_ava_100.gif');
						
					$('#office').focus();
				} else {
					setErrorInputMsg('upage');
					$('#feedimg').attr('src', template_dir+'/images/contact_info.png');
				}
			});
		} else
			$('#feedimg').attr('src', template_dir+'/images/contact_info.png');
	},
	saveinfo: function(id){
		var title = $('#title').val();
		var descr = $('#descr').val();
		var adres_page = $('#adres_page').val();
		var comments = $('#comments').val();
		$('#e_public_title').text(title);
		if(descr != 0){
			$('#descr_display').show();
			$('#e_descr').html(descr);
		}
		if(!adres_page)	var adres_page = 'public'+id;
		var pattern = new RegExp(/^[a-zA-Z0-9_-]+$/);
		if(pattern.test(adres_page)){
			butloading('pubInfoSave', 55, 'disabled');
			$.post('/index.php?go=groups&act=saveinfo', {id: id, title: title, descr: descr, comments: comments, adres_page: adres_page}, function(d){
				if(d == 'err_adres')
					Box.Info('err', 'Ошибка', 'Такой адрес уже занят', 130, 1500);
				else
					if($('#prev_adres_page').val() == adres_page)
						groups.editformClose();
					else if(d == 'no_new')
						Page.Go('/public'+id);
					else
						Page.Go('/'+adres_page);
				
				butloading('pubInfoSave', 55, 'enabled', 'Сохранить');
			});
		} else {
			setErrorInputMsg('adres_page');
			Box.Info('err', 'Ошибка', 'Вы можете изменить короткий адрес Вашей страницы на более удобный и запоминающийся. Для этого введите имя страницы, состоящее из латинских букв, цифр или знаков «_» .', 300, 5500);
		}
	},
	editform: function(){
		$('#edittab1').slideDown('fast');
		$('#public_editbg_container').animate({scrollLeft: "+560"});
	},
	editformClose: function(){
		$('#public_editbg_container').animate({scrollLeft: "-560"}, 1000);
		setTimeout("$('#edittab1').slideUp('fast')", 200);
		$('#edittab2').hide();
	},
	edittab_admin: function(id){
		$('#edittab2').show();
		$('#public_editbg_container').animate({scrollLeft: "+1120"});
	},
	addadmin: function(id){
		var new_admin_id = $('#new_admin_id').val().replace('http://site.ru/u', '');
		var check_adm = $('#admin'+new_admin_id).text();
		if(new_admin_id && !check_adm){
			Box.Page('/index.php?go=groups&act=new_admin', 'new_admin_id='+new_admin_id, 'new_admin_id', 400, 'Назначение руководителя', 'Закрыть', 'Назначить руководителем', 'groups.send_new_admin('+id+', '+new_admin_id+')', 130, 0, 0, 0, 0, 0);
		} else
			ShowInfo('red', 'Этот пользователь уже есть в списке руководителей.', 3500);
	},
	send_new_admin: function(id, new_admin_id){
		var ava = $('#adm_ava').attr('src');
		var adm_name = $('#adm_name').text();
		var data = '<div class="public_oneadmin" id="admin'+new_admin_id+'"><a href="/u'+new_admin_id+'" onClick="Page.Go(this.href); return false"><img src="'+ava+'" align="left" width="32" /></a><a href="/u'+new_admin_id+'" onClick="Page.Go(this.href); return false">'+adm_name+'</a><br /><a href="/" onClick="groups.deladmin(\''+id+'\', \''+new_admin_id+'\'); return false"><small>Удалить</small></a></div>';
		$('#admins_tab').append(data);
		Box.Close();
		$('#new_admin_id').val('');
		$.post('/index.php?go=groups&act=send_new_admin', {id: id, new_admin_id: new_admin_id});
	},
	deladmin: function(id, uid){
		$('#admin'+uid).remove();
		$.post('/index.php?go=groups&act=deladmin', {id: id, uid: uid});
	},
	wall_send: function(id){
		var wall_text = $('#wall_text').val();
		var attach_files = $('#vaLattach_files').val();

		if(wall_text != 0 || attach_files != 0){
			butloading('wall_send', 56, 'disabled');
			$.post('/index.php?go=groups&act=wall_send', {id: id, wall_text: wall_text, attach_files: attach_files, vote_title: $('#vote_title').val(), vote_answer_1: $('#vote_answer_1').val(), vote_answer_2: $('#vote_answer_2').val(), vote_answer_3: $('#vote_answer_3').val(), vote_answer_4: $('#vote_answer_4').val(), vote_answer_5: $('#vote_answer_5').val(), vote_answer_6: $('#vote_answer_6').val(), vote_answer_7: $('#vote_answer_7').val(), vote_answer_8: $('#vote_answer_8').val(), vote_answer_9: $('#vote_answer_9').val(), vote_answer_10: $('#vote_answer_10').val()}, function(data){
				if($('#rec_num').text() == 'Нет записей')
					$('.albtitle').html('<b id="rec_num">1</b> запись');
				else
					updateNum('#rec_num', 1);
				
				$('#wall_text').val('');
				$('#attach_files').hide();
				$('#attach_files').html('');
				$('#vaLattach_files').val('');
				wall.form_close();
				wall.RemoveAttachLnk();
				butloading('wall_send', 56, 'enabled', lang_box_send);
				$('#public_wall_records').html(data);
				
				if($('#rec_num').text() > 10){
					$('#page_cnt').val('1');
					$('#wall_all_records').show();
					$('#load_wall_all_records').html('к предыдущим записям');
				}
			});
		} else
			setErrorInputMsg('wall_text');
	},
	wall_send_comm: function(rec_id, public_id){
		var wall_text = $('#fast_text_'+rec_id).val();

		if(wall_text != 0){
			butloading('fast_buts_'+rec_id, 56, 'disabled');
			$.post('/index.php?go=groups&act=wall_send_comm', {rec_id: rec_id, wall_text: wall_text, public_id: public_id}, function(data){
				$('#fast_form_'+rec_id+', #fast_comm_link_'+rec_id).remove();
				$('#wall_fast_block_'+rec_id).html(data);
				var pattern = new RegExp(/news/i);
				if(pattern.test(location.href)) $('#fast_text_'+rec_id+', #fast_inpt_'+rec_id).css('width', '688px');
			});
		} else
			setErrorInputMsg('fast_text_'+rec_id);
	},
	wall_delet: function(rec_id){
		$('#wall_record_'+rec_id).html('<span class="color777">Запись удалена.</span>');
		$('#wall_fast_block_'+rec_id+', .wall_fast_opened_form').remove();
		$('#wall_record_'+rec_id).css('padding-bottom', '5px');
		myhtml.title_close(rec_id);
		updateNum('#rec_num');
		$.post('/index.php?go=groups&act=wall_del', {rec_id: rec_id});
	},
	comm_wall_delet: function(rec_id, public_id){
		$('#wall_fast_comment_'+rec_id).html('<div class="color777" style="margin-bottom:7px">Комментарий удалён.</div>');
		$.post('/index.php?go=groups&act=wall_del', {rec_id: rec_id, public_id: public_id});
	},
	wall_all_comments: function(rec_id, public_id){
		textLoad('wall_all_comm_but_'+rec_id);
		$('#wall_all_but_link_'+rec_id).attr('onClick', '');
		$.post('/index.php?go=groups&act=all_comm', {rec_id: rec_id, public_id: public_id}, function(data){
			$('#wall_fast_block_'+rec_id).html(data); //выводим сам результат
			var pattern = new RegExp(/news/i);
			if(pattern.test(location.href)) $('#fast_text_'+rec_id+', #fast_inpt_'+rec_id).css('width', '688px');
		});
	},
	wall_page: function(){
		var page_cnt = $('#page_cnt').val();
		var public_id = $('#public_id').val();
		$('#wall_all_records').attr('onClick', '');
		if($('#load_wall_all_records').text() == 'к предыдущим записям' && $('#rec_num').text() > 10){
			textLoad('load_wall_all_records');
			$.post('/index.php?go=public&pid='+public_id, {page_cnt: page_cnt}, function(data){
				$('#public_wall_records').append(data);
				$('#page_cnt').val((parseInt($('#page_cnt').val())+1));
				if($('.wallrecord').size() == $('#rec_num').text()){
					$('#wall_all_records').hide();
				} else {
					$('#wall_all_records').attr('onClick', 'groups.wall_page(\''+public_id+'\')');
					$('#load_wall_all_records').html('к предыдущим записям');
				}
			});
		}
	},
	wall_attach_addphoto: function(id, page_num, public_id){
		wall.attach_menu('close', 'wall_attach', 'wall_attach_menu');
		
		if(page_num)
			page = '&page='+page_num;
		else {
			page = '';
			page_num = 1;
		}
		
		Box.Page('/index.php?go=groups&act=photos', 'public_id='+public_id+page, 'c_all_photos_'+page_num, 627, lang_wall_attatch_photos, lang_box_canсel, 0, 0, 400, 1, 0, 1, 0, 1);
	},
	wall_attach_insert: function(type, data, action_url){
		if(!$('#wall_text').val())
			wall.form_open();
		
		$('#attach_files').show();
		var attach_id = Math.floor(Math.random()*(1000-1+1))+1;

		//Если вставляем фотографию
		if(type == 'photo'){
			Box.Close('all_photos', 1);
			res_attach_id = 'photo_'+attach_id;
			$('#attach_files').append('<span id="attach_file_'+res_attach_id+'" class="attach_file"><div class="wall_attach_photo fl_l"><div class="wall_attach_del" onMouseOver="myhtml.title(\''+res_attach_id+'\', \''+lang_wall_no_atttach+'\', \'wall_photo_\')" onMouseOut="myhtml.title_close(\''+res_attach_id+'\')" onClick="wall.attach_delete(\''+res_attach_id+'\', \'photo|'+action_url+'||\')" id="wall_photo_'+res_attach_id+'"></div><img src="'+data+'" alt="" /></div></span>');
			$('#vaLattach_files').val($('#vaLattach_files').val()+'photo|'+action_url+'||');
		}
		
		//Если вставляем видео
		if(type == 'video'){
			Box.Close('attach_videos');
			res_attach_id = 'video_'+attach_id;
			$('#attach_files').append('<span id="attach_file_'+res_attach_id+'" class="attach_file"><div class="wall_attach_photo fl_l"><div class="wall_attach_del" onMouseOver="myhtml.title(\''+res_attach_id+'\', \''+lang_wall_no_atttach+'\', \'wall_photo_\')" onMouseOut="myhtml.title_close(\''+res_attach_id+'\')" onClick="wall.attach_delete(\''+res_attach_id+'\', \'video|'+action_url+'||\')" id="wall_photo_'+res_attach_id+'"></div><img src="'+data+'" alt="" /></div></span>');
			$('#vaLattach_files').val($('#vaLattach_files').val()+'video|'+action_url+'||');
		}

		var count = $('.attach_file').size();
		if(count > 9)
			$('#wall_attach').hide();
	},
	wall_photo_view: function(rec_id, public_id, src, pos){
		var photo = $('#photo_wall_'+rec_id+'_'+pos).attr('src').replace('c_', '');
		var size = $('.page_num'+rec_id).size();
		if(size == 1){
			var topTxt = 'Просмотр фотографии';
			var next = 'Photo.Close(\'\'); return false';
		} else {
			var topTxt = 'Фотография <span id="pTekPost">'+pos+'</span> из '+size;
			var next = 'groups.wall_photo_view_next('+rec_id+'); return false';
		}
		
		var content = '<div id="photo_view" class="photo_view" onClick="groups.wall_photo_view_setEvent(event)">'+
'<div class="photo_close" onClick="Photo.Close(\'\'); return false;"></div>'+
 '<div class="photo_bg cursor_pointer" onClick="'+next+'" style="min-height:400px">'+
  '<div class="photo_com_title" style="padding-top:0px;">'+topTxt+'<div><a href="/" onClick="Photo.Close(\'\'); return false">Закрыть</a></div></div>'+
  '<div class="photo_img_box"><img src="'+photo+'" id=\"photo_view_src\" style="margin-bottom:7px" /></div><div class="line_height">'+
  '<input type="hidden" id="photo_pos" value="'+pos+'" />'+
  '</div><div class="clear"></div>'+
 '</div>'+
'<div class="clear"></div>'+
'</div>';

		$('body').append(content);
		$('#photo_view').show();

		if(is_moz && !is_chrome) scrollTopForFirefox = $(window).scrollTop();
		
		$('html, body').css('overflow-y', 'hidden');
		
		if(is_moz && !is_chrome) $(window).scrollTop(scrollTopForFirefox);
		
	},
	wall_photo_view_next: function(rec_id){
		var pos = parseInt($('#photo_pos').val())+1;
		if($('#photo_wall_'+rec_id+'_'+pos).attr('src'))
			var next_src = $('#photo_wall_'+rec_id+'_'+pos).attr('src').replace('c_', '');
		else
			var next_src = false;

		$('#photo_pos').val(pos);
		$('#pTekPost').text(pos);
		
		//Если уже последняя фотка, то следующей фоткой делаем первую
		if(pos > $('.page_num'+rec_id).size()){
			$('#photo_pos').val('1');
			$('#pTekPost').text('1');
			var next_src = $('#photo_wall_'+rec_id+'_1').attr('src').replace('c_', '');
		}
		$('#photo_view_src').attr('src', next_src);
	},
	wall_photo_view_setEvent: function(event){
		var oi = (event.target) ? event.target.id: ((event.srcElement) ? event.srcElement.id : null);
		if(oi == 'photo_view')
			Photo.Close('');
	},
	wall_video_add_box: function(){
		wall.attach_menu('close', 'wall_attach', 'wall_attach_menu');
		Box.Show('attach_videos', 400, 'Ссылка видеозаписи на УдинБала', '<div style="padding:15px;"><input  type="text"  placeholder="Введите ссылку видеозаписи на УдинБала.."  class="videos_input" id="video_attach_lnk" style="width:355px;margin-top:10px" /></div>', lang_box_canсel, 'Прикрпепить', 'groups.wall_video_add_select()');
		$('#video_attach_lnk').focus();
	},
	wall_video_add_select: function(){
		var video_attach_lnk = $('#video_attach_lnk').val().replace('http://'+location.host+'/video', '');
		var data = video_attach_lnk.split('_');
		if(video_attach_lnk != 0){
			$('#box_loading').show();
			ge('box_butt_create').disabled = true;
			$.post('/index.php?go=groups&act=select_video_info', {video_id: data[1]}, function(row){
				if(row == 1){
					ShowInfo('red', 'Неверный адрес видеозаписи', 3000);
					$('#box_loading').hide();
					ge('box_butt_create').disabled = false;
				} else {
					groups.wall_attach_insert('video', '/uploads/videos/'+data[0]+'/'+row, row+'|'+data[1]+'|'+data[0]);
					$('#video_attach_lnk').val('');
				}
			});
		} else
			setErrorInputMsg('video_attach_lnk');
	},
	wall_add_like: function(rec_id, user_id, type){
		if($('#wall_like_cnt'+rec_id).text())
			var wall_like_cnt = parseInt($('#wall_like_cnt'+rec_id).text())+1;
		else {
			$('#public_likes_user_block'+rec_id).show();
			$('#update_like'+rec_id).val('1');
			var wall_like_cnt = 1;
		}
		
		$('#wall_like_cnt'+rec_id).html(wall_like_cnt).css('color', '#2f5879');
		$('#wall_active_ic'+rec_id).addClass('public_wall_like_yes');
		$('#wall_like_link'+rec_id).attr('onClick', 'groups.wall_remove_like('+rec_id+', '+user_id+', \''+type+'\')');
		$('#like_user'+user_id+'_'+rec_id).show();
		updateNum('#like_text_num'+rec_id, 1);
		
		if(type == 'uPages')
			$.post('/index.php?go=wall&act=like_yes', {rid: rec_id});
		else
			$.post('/index.php?go=groups&act=wall_like_yes', {rec_id: rec_id});
	},
	wall_remove_like: function(rec_id, user_id, type){
		var wall_like_cnt = parseInt($('#wall_like_cnt'+rec_id).text())-1;
		if(wall_like_cnt <= 0){
			var wall_like_cnt = '';
			$('#public_likes_user_block'+rec_id).hide();
		}
		
		$('#wall_like_cnt'+rec_id).html(wall_like_cnt).css('color', '#95adc0');
		$('#wall_active_ic'+rec_id).removeClass('public_wall_like_yes');
		$('#wall_like_link'+rec_id).attr('onClick', 'groups.wall_add_like('+rec_id+', '+user_id+', \''+type+'\')');
		$('#Xlike_user'+user_id+'_'+rec_id).hide();
		$('#like_user'+user_id+'_'+rec_id).hide();
		updateNum('#like_text_num'+rec_id);

		if(type == 'uPages')
			$.post('/index.php?go=wall&act=like_no', {rid: rec_id});
		else
			$.post('/index.php?go=groups&act=wall_like_remove', {rec_id: rec_id});
	},
	wall_like_users_five: function(rec_id, type){		
		$('.public_likes_user_block').hide();
		if(!ge('like_cache_block'+rec_id) && $('#wall_like_cnt'+rec_id).text() && $('#update_like'+rec_id).val() == 0){
			if(type == 'uPages'){
				$.post('/index.php?go=wall&act=liked_users', {rid: rec_id}, function(data){
					$('#likes_users'+rec_id).html(data+'<span id="like_cache_block'+rec_id+'"></span>');
					$('#public_likes_user_block'+rec_id).show();
				});
			} else {
				$.post('/index.php?go=groups&act=wall_like_users_five', {rec_id: rec_id}, function(data){
					$('#likes_users'+rec_id).html(data+'<span id="like_cache_block'+rec_id+'"></span>');
					$('#public_likes_user_block'+rec_id).show();
				});
			}
		} else
			if($('#wall_like_cnt'+rec_id).text())
				$('#public_likes_user_block'+rec_id).show();
	},
	wall_like_users_five_hide: function(){
		$('.public_likes_user_block').hide();
	},
	wall_all_liked_users: function(rid, page_num, liked_num){
		$('.public_likes_user_block').hide();
		if(page_num)
			page = '&page='+page_num;
		else {
			page = '';
			page_num = 1;
		}
		
		if(!liked_num)
			liked_num = 1;
			
		Box.Page('/index.php?go=groups&act=all_liked_users', 'rid='+rid+'&liked_num='+liked_num+page, 'all_liked_users_'+rid+page_num, 525, lang_wall_liked_users, lang_msg_close, 0, 0, 345, 1, 1, 1, 0, 1);
	},
	wall_tell: function(rec_id){
		$('#wall_tell_'+rec_id).hide();
		myhtml.title_close(rec_id);
		$('#wall_ok_tell_'+rec_id).fadeIn(150);
		$.post('/index.php?go=groups&act=wall_tell', {rec_id: rec_id}, function(data){
			if(data == 1)
				ShowInfo('red', lang_wall_tell_tes, 3000);
		});
	},
	all_people: function(public_id, page_num){
		if(page_num)
			page = '&page='+page_num;
		else {
			page = '';
			page_num = 1;
		}
		
		var followers_num = $('#traf').text();
			
		Box.Page('/index.php?go=groups&act=all_people', 'public_id='+public_id+'&num='+followers_num+page, 'all_peoples_users_'+public_id+page_num, 525, 'Подписчики', lang_msg_close, 0, 0, 345, 1, 1, 1, 0, 1);
	},
	all_followers: function(public_id, page_num, followers_num){
		if(page_num){
			page = '&page='+page_num;
		} else {
			page = '';
			page_num = 1;
		}
		var followers_num = parseInt($('#followers_num').text());
		Box.Page('/index.php?go=groups&act=all_followers', 'public_id='+public_id+'&followers_num='+followers_num+page, 'all_public_followers_'+page_num, 490, 'Followers', lang_msg_close, 0, 0, 345, 1, 1, 1, 0);    
	},
	all_publics: function(for_user_id, page_num, following_num){
		if(page_num)
			page = '&page='+page_num;
		else {
			page = '';
			page_num = 1;
		}
			
		Box.Page('/index.php?go=groups&act=all_publics', 'for_user_id='+for_user_id+'&following_num='+following_num+page, 'all_user_publics', 340, 'Интересные страницы', false);
	}
}

//AUDIO
var audio = {
	addBox: function(){
		Box.Close();
		Box.Show('addaudio', 510, lang_audio_add, '<div class="videos_pad"><div class="buttonsprofile albumsbuttonsprofile buttonsprofileSecond" style="height:22px;margin-bottom:20px;margin-top:-5px"><div class="buttonsprofileSec cursor_pointer"><a><div><b>По ссылке</b></div></a></div><a class="cursor_pointer" onClick="audio.addBoxComp()"><div><b>С компьютера</b></div></a></div><div class="videos_text">Вставьте ссылку на mp3 файл</div><input type="text" class="videos_input" id="audio_lnk" style="margin-top:5px" /><span id="vi_info">Например: <b>http://music.com/uploads/files/audio/2012/faxo_-_kalp.mp3</b></span></div>', lang_box_canсel, lang_album_create, 'audio.send()', 0, 0, 1, 1);
		$('#audio_lnk').focus();
	},
	addBoxComp: function(){
		Box.Close();
		Box.Show('addaudio_comp', 510, lang_audio_add, '<div class="videos_pad"><div class="buttonsprofile albumsbuttonsprofile buttonsprofileSecond" style="height:22px;margin-bottom:20px;margin-top:-5px"><a onClick="audio.addBox()" class="cursor_pointer"><div><b>По ссылке</b></div></a><div class="buttonsprofileSec cursor_pointer"><a><div><b>С компьютера</b></div></a></div></div><div class="videos_text">Ограничения<div class="clear"></div><li style="font-weight:normal;color:#000;font-size:11px;margin-top:10px">Аудиофайл не должен превышать 10 Мб и должен быть в формате MP3.</li><li style="font-weight:normal;color:#000;font-size:11px;margin-bottom:15px">Аудиофайл не должен нарушать авторские права.</li><div class="button_div fl_l" style="margin-left:170px"><button id="upload">Выбрать файл</button></div><div class="clear"></div><div style="margin-top:15px;font-size:11px;color:#000;font-weight:normal">Вы также можете добавить аудиозапись из числа уже загруженных файлов, воспользовавшись <a href="/?go=search&type=5"><b>поиском по аудио.</b></a></div></div></div>', lang_box_canсel, lang_album_create, 'audio.send()', 0, 0, 1, 1);
		$('#audio_lnk').focus();
		$('#box_but').hide();
		Xajax = new AjaxUpload('upload', {
			action: '/index.php?go=audio&act=upload',
			name: 'uploadfile',
			onSubmit: function (file, ext){
				if(!(ext && /^(mp3)$/.test(ext))){
					Box.Info('load_photo_er', lang_dd2f_no, 'Аудиофайл должен быть в формате MP3.', 250);
					return false;
				}
				butloading('upload', '73', 'disabled', '');
			},
			onComplete: function (file, data){
				butloading('upload', '73', 'enabled', 'Выбрать файл');
				if(data == 1)
					Box.Info('load_photo_er', lang_dd2f_no, 'Аудиофайл не должен превышать 10 Мб и должен быть в формате MP3.', 250);
				else {
					Box.Close();
					Page.Go('/audio');
				}
			}
		});
	},
	send: function(){
		var lnk = $('#audio_lnk').val();
		if(lnk != 0){
			$('#box_loading').show();
			ge('box_butt_create').disabled = true;
			$.post('/index.php?go=audio&act=send', {lnk: lnk}, function(d){
				if(d){

					
					ge('box_butt_create').disabled = false;
				} else {
					Box.Close();
					Page.Go('/audio');
				}
				$('#box_loading').hide();
			});
		} else
			setErrorInputMsg('audio_lnk');
	},
	page: function(){
		var page_cnt = $('#page_cnt').val();
		var uid = $('#uid').val();
		$('#wall_all_records').attr('onClick', '');
		if($('#load_wall_all_records').text() == 'Показать больше аудиозаписей'){
			textLoad('load_wall_all_records');
			$.post('/index.php?go=audio&uid='+uid, {page_cnt: page_cnt}, function(data){
				$('#audioPage').append(data);
				$('#page_cnt').val((parseInt($('#page_cnt').val())+1));
				if(!data){
					$('#wall_all_records').hide();
				} else {
					$('#wall_all_records').attr('onClick', 'audio.page()');
					$('#load_wall_all_records').html('Показать больше аудиозаписей');
				}
			});
		}
	},
	edit: function(aid, pid){
		if(pid) funcsave = 'PublicAudioEditsave('+aid+', '+pid+')';
		else funcsave = 'audio.editsave('+aid+')';
			
		Box.Show('edit'+aid, 510, 'Редактирование аудиозаписи', '<div class="videos_pad"><div class="videos_text">Исполнитель</div><input type="text" class="videos_input" id="valartis'+aid+'" style="margin-bottom:15px" value="'+$('#artis'+aid).html()+'" /><div class="videos_text">Название</div><input type="text" class="videos_input" id="vaname'+aid+'" value="'+$('#name'+aid).html()+'" /></div>', lang_box_canсel, 'Сохранить', funcsave, 0, 0, 1, 1);
		$('#audio_lnk').focus();
	},
	editsave: function(aid){
		if($('#valartis'+aid).val() != 0)
			$('#artis'+aid).text($('#valartis'+aid).val());
		else
			$('#artis'+aid).text('Неизвестный исполнитель');
		
		if($('#vaname'+aid).val() != 0)
			$('#name'+aid).text($('#vaname'+aid).val());
		else
			$('#name'+aid).text('Без названия');

		$.post('/index.php?go=audio&act=editsave', {aid: aid, artist: $('#valartis'+aid).val(), name: $('#vaname'+aid).val()});
		Box.Close();
	},
	del: function(aid){
		Page.Loading('start');
		$('.js_titleRemove').hide();
		$.post('/index.php?go=audio&act=del', {aid: aid}, function(d){
			Page.Go('/audio');
		});
	},
	addMyList: function(aid){
		$('.js_titleRemove').hide();
		$('#atrack_'+aid).remove();
		$('#atrackAddOk'+aid).show();
		$.post('/index.php?go=audio&act=addmylist', {aid: aid});
	}
}

//AUDIO -> PLAYER
var music = {
	jPlayerInc: function(){
		var hs = location.hash.replace('#', '');
		if(hs >= 1 && hs <= 3){
			$('#teck_id').val(hs);
		}
		if($('#typePlay').val() == 'standart'){
			$("#jquery_jplayer").jPlayer();
		} else {
			$("#jquery_jplayer").jPlayer({
				ready: function(){
					var musId = $('#music_'+$('#teck_id').val()).attr('data');
					var musName = $('#music_'+$('#teck_id').val()).text();
					$('#teck_track_name').text(musName);
					$("#jquery_jplayer").change(musId);
					if(hs >= 1 && hs <= 3){
						music.nullPlay();
					}
				},
				cssPrefix: "different_prefix_example"
			});
		}
		$("#jquery_jplayer").jPlayerId("play", "player_play");
		$("#jquery_jplayer").jPlayerId("pause", "player_pause");
		$("#jquery_jplayer").jPlayerId("stop", "player_stop");
		$("#jquery_jplayer").jPlayerId("loadBar", "player_progress_load_bar");
		$("#jquery_jplayer").jPlayerId("playBar", "player_progress_play_bar");
		$("#jquery_jplayer").jPlayerId("volumeMin", "player_volume_min");
		$("#jquery_jplayer").jPlayerId("volumeMax", "player_volume_max");
		$("#jquery_jplayer").jPlayerId("volumeBar", "player_volume_bar");
		$("#jquery_jplayer").jPlayerId("volumeBarValue", "player_volume_bar_value");
		$("#jquery_jplayer").onProgressChange( function(loadPercent, playedPercentRelative, playedPercentAbsolute, playedTime, totalTime) {
			var myPlayedTime = new Date(playedTime);
			var ptMin = (myPlayedTime.getMinutes() < 10) ? "0" + myPlayedTime.getMinutes() : myPlayedTime.getMinutes();
			var ptSec = (myPlayedTime.getSeconds() < 10) ? "0" + myPlayedTime.getSeconds() : myPlayedTime.getSeconds();
			if($('#typePlay').val() == 'standart')
				$("#play_time"+$('#teck_prefix').val()+$('#teck_id').val()).text(ptMin+":"+ptSec);
			else
				$("#play_time").text(ptMin+":"+ptSec);
			var myTotalTime = new Date(totalTime);
			var ttMin = (myTotalTime.getMinutes() < 10) ? "0" + myTotalTime.getMinutes() : myTotalTime.getMinutes();
			var ttSec = (myTotalTime.getSeconds() < 10) ? "0" + myTotalTime.getSeconds() : myTotalTime.getSeconds();
			if(ttSec <= 0) ttSec = '';
			if(ptMin+ptSec == ttMin+ttSec){
				music.next();
			}
		});
	},
	newStartPlay: function(id, prefix){	
		if(!prefix) var prefix = '';
		
		if($('#typePlay').val() == 'standart'){
			$('#ppbarPro'+$('#teck_prefix').val()+$('#teck_id').val()).html('').hide();
			$("#play_time"+$('#teck_prefix').val()+$('#teck_id').val()).hide();
			$('#ppbarPro'+prefix+id).html('<div id="player_progress_load_bar" onClick="$(\'#jquery_jplayer\').loadBar(event)" style="height:5px"><div id="player_progress_play_bar" style="height:5px"></div></div>').show();
			$("#play_time"+prefix+id).show();
		} else {
			if(!prefix){
				var size = $('.audio_onetrack').size();
				var randId = Math.floor(Math.random()*size);
				if(randId == 0) randId = 1;
				if($('#rand').val() == 1)
					id = randId;

				var idUload = size-7;
				if(id >= idUload)
					audio.page();
			}
		}
		
		if($('#refresh').val() > 0){
			$('#jquery_jplayer').stop();
			$('#jquery_jplayer').play();
			$('#icPlay_'+$('#teck_id').val()).addClass('audio_stopic').attr('onClick', '$(\'#jquery_jplayer\').pause(); music.pause()');
		} else {
			if($('#teck_prefix').val())
				$('#icPlay_'+$('#teck_prefix').val()+$('#teck_id').val()).removeClass('audio_stopic').attr('onClick', 'music.newStartPlay('+$('#teck_id').val()+', '+$('#teck_prefix').val()+')');
			else
				$('#icPlay_'+$('#teck_prefix').val()+$('#teck_id').val()).removeClass('audio_stopic').attr('onClick', 'music.newStartPlay('+$('#teck_id').val()+')');
			
			$('#teck_id').val(id);
			
			$('#jquery_jplayer').stop();
			
			$('#icPlay_'+prefix+id).addClass('audio_stopic').attr('onClick', '$(\'#jquery_jplayer\').pause(); music.pause()');
			
			$('#teck_prefix').val(prefix);
			
			if($('#music_'+prefix+$('#teck_id').val()).attr('data')){
				var musId = $('#music_'+prefix+$('#teck_id').val()).attr('data');
				var musName = $('#music_'+prefix+$('#teck_id').val()).text();
				$('#teck_track_name').text(musName);
				$("#jquery_jplayer").change(musId);
				$('#jquery_jplayer').play();
			} else
				music.newStartPlay(1, $('#teck_prefix').val());
		}
	},
	next: function(){
		$('#icPlay_'+$('#teck_prefix').val()+$('#teck_id').val()).removeClass('audio_stopic');
		if($('#teck_prefix').val()){
			var size = $('.audioForSize'+$('#teck_prefix').val()).size();
			if(size > 1 && $('#teck_id').val() < size){
				music.newStartPlay((parseInt($('#teck_id').val())+1), $('#teck_prefix').val());
			} else {
				$('#ppbarPro'+$('#teck_prefix').val()+$('#teck_id').val()).html('').hide();
				$("#play_time"+$('#teck_prefix').val()+$('#teck_id').val()).hide();
				$('#icPlay_'+$('#teck_prefix').val()+$('#teck_id').val()).removeClass('audio_stopic').attr('onClick', 'music.newStartPlay('+$('#teck_id').val()+', '+$('#teck_prefix').val()+')');
			}
		} else
			music.newStartPlay((parseInt($('#teck_id').val())+1));
	},
	prev: function(){
		$('#icPlay_'+$('#teck_prefix').val()+$('#teck_id').val()).removeClass('audio_stopic');
		music.newStartPlay((parseInt($('#teck_id').val())-1));
	},
	pause: function(){
		$('#icPlay_'+$('#teck_prefix').val()+$('#teck_id').val()).removeClass('audio_stopic').attr('onClick', 'music.proceed()');
	},
	proceed: function(){
		$('#jquery_jplayer').play();
		$('#icPlay_'+$('#teck_prefix').val()+$('#teck_id').val()).addClass('audio_stopic').attr('onClick', '$(\'#jquery_jplayer\').pause(); music.pause()');
	},
	nullPlay: function(){
		$('#icPlay_'+$('#teck_id').val()).addClass('audio_stopic').attr('onClick', '$(\'#jquery_jplayer\').pause(); music.pause()');
		$('#jquery_jplayer').play();
	},
	nullPause: function(){
		$('#icPlay_'+$('#teck_id').val()).removeClass('audio_stopic').attr('onClick', 'music.nullPlay()');
		$('#jquery_jplayer').pause();
	},
	volumeOff: function(){
		$('.player_del_volume').css('opacity', '1');
		$('.player_max_volume').css('opacity', '0.5');
		$('#jquery_jplayer').volume(0);
	},
	volumeMax: function(){
		$('.player_del_volume').css('opacity', '0.5');
		$('.player_max_volume').css('opacity', '1');
		$('#jquery_jplayer').volume(100);
	},
	volume: function(){
		$('.player_max_volume, .player_del_volume').css('opacity', '0.5');
	},
	refresh: function(){
		$('.player_refresh').css('opacity', '1').attr('onClick', 'music.refreshOff()');
		$('#refresh').val($('#teck_id').val());
		music.randOff();
	},
	refreshOff: function(){
		$('.player_refresh').css('opacity', '0.5').attr('onClick', 'music.refresh()');
		$('#refresh').val(0);
	},
	randOn: function(){
		$('.player_rand').css('opacity', '1').attr('onClick', 'music.randOff()');
		$('#rand').val(1);
		music.refreshOff();
	},
	randOff: function(){
		$('.player_rand').css('opacity', '0.5').attr('onClick', 'music.randOn()');
		$('#rand').val(0);
	}
}

//IM
var i = 0;
var vii_typograf_delay = false;
var vii_msg_te_val = '';
var vii_typograf = true;
var im = {
	typograf: function(){
		var for_user_id = $('#for_user_id').val();
		var a = $('#msg_text').val();
		if(vii_typograf){
			$.post('/index.php?go=im&act=typograf', {for_user_id: for_user_id});
			vii_typograf = false;
		}
		if(!vii_typograf){
			0 == vii_msg_te_val != a && a != 0 < a.length && (clearInterval(vii_typograf_delay), vii_typograf_delay = setInterval(function(){
				$.post('/index.php?go=im&act=typograf&stop=1', {for_user_id: for_user_id});
				vii_typograf = true;
			}, 3000));
		}
	},
	settTypeMsg: function(){
		Page.Loading('start');
		$.post('/index.php?go=messages&act=settTypeMsg', function(d){
			Page.Go('/messages');
		});
	},
	open: function(uid){
		$('.im_oneusr').removeClass('im_usactive');
		$('#dialog'+uid).addClass('im_usactive');
		$('#imViewMsg').html('<img src="'+template_dir+'/images/loading_im.gif" style="margin-left:225px;margin-top:220px" />');
		$.post('/index.php?go=im&act=history', {for_user_id: uid}, function(d){
			$('#imViewMsg').html(d);
			
			$('.im_scroll').append('<div class="im_typograf"></div>').scrollTop(99999);
			
			var aco = $('.im_usactive').text().split(' ');
			$('.im_typograf').html('<div class="no_display" id="im_typograf"><img src="'+template_dir+'/images/typing.gif" /> '+aco[0]+' набирает сообщение..</div>');
	
			$('#msg_text').focus();
		});
	},
	read: function(msg_id, auth_id, my_id){
		if(auth_id != my_id){
			var msg_num = parseInt($('#new_msg').text().replace(')', '').replace('(', ''))-1;
			$.post('/index.php?go=im&act=read', {msg_id: msg_id}, function(){
				if(msg_num > 0)
					$('#new_msg').html("+"+msg_num);
				else
					$('#new_msg').html('');
				
				updateNum('#msg_num'+auth_id);
				if($('#msg_num'+auth_id).text() <= 0)
					$('#msg_num'+auth_id).hide();
			
				$('#imMsg'+msg_id).css('background', '#fff').attr('onMouseOver', '');
			});
		}
	},
	send: function(for_user_id, my_name, my_ava){
		var msg_text = $('#msg_text').val();
		var attach_files = $('#vaLattach_files').val();
		if(msg_text != 0 && $('#status_sending').val() == 1 || attach_files != 0){
			butloading('sending', 56, 'disabled');
			$('#status_sending').val('0');
			$.post('/index.php?go=im&act=send', {for_user_id: for_user_id, my_name: my_name, my_ava: my_ava, msg: msg_text, attach_files: attach_files}, function(data){
				if(data == 'err_privacy')
					Box.Info('msg_info', lang_pr_no_title, lang_pr_no_msg, 400, 4000);
				else {
					$('#im_scroll').append(data);
					$('.im_scroll').scrollTop(99999);
					$('#msg_text, #vaLattach_files').val('');
					$('#attach_files').html('');
					$('#msg_text').focus();
					$('#status_sending').val('1');
					butloading('sending', 56, 'enabled', 'Отправить');
				}
			});
		} else
			setErrorInputMsg('msg_text');
	},
	delet: function(mid, folder){
		$('.js_titleRemove, #imMsg'+mid).remove();
		$.post('/index.php?go=messages&act=delet', {mid: mid, folder: folder});
	},
	update: function(){
		var for_user_id = $('#for_user_id').val();
		var last_id = $('.im_msg:last').attr('id').replace('imMsg', '');
		$.post('/index.php?go=im&act=update', {for_user_id: for_user_id, last_id: last_id}, function(d){
			if(d.length != '49' && d != 'no_new'){
				$('#im_scroll').html(d);
				$('.im_scroll').scrollTop(99999);
			}
			
			if(d.length == 49) $('#im_typograf').fadeIn();
			else $('#im_typograf').fadeOut()
			
		});
	},
	page: function(for_user_id){
		var first_id = $('.im_msg:first').attr('id').replace('imMsg', '');
		$('#wall_all_records').attr('onClick', '');
		if($('#load_wall_all_records').text() == 'Показать предыдущие сообщения'){
			textLoad('load_wall_all_records');
			$.post('/index.php?go=im&act=history', {first_id: first_id, for_user_id: for_user_id}, function(data){
				i++;
				var imHeiah = $('.im_scroll').height();
				$('#prevMsg').html('<div id="appMsgFScroll'+i+'" class="no_display">'+data+'</div>'+$('#prevMsg').html());
				$('.im_scroll').scrollTop($('#appMsgFScroll'+i).show().height()+imHeiah);
				if(!data){
					$('#wall_all_records').hide();
				} else {
					$('#wall_all_records').attr('onClick', 'im.page('+for_user_id+')');
					$('#load_wall_all_records').html('Показать предыдущие сообщения');
				}
			});
		}
	},
	box_del: function(u){
		Box.Show('im_del'+u, 350, 'Удалить все сообщения', '<div style="padding:15px;" id="del_status_text_im">Вы действительно хотите удалить всю переписку с данным пользователем?<br /><br />Отменить это действие будет невозможно.</div>', lang_box_canсel, lang_box_yes, 'im.del('+u+')');
	},
	del: function(u){
		$('#box_loading').show();
		ge('box_butt_create').disabled = true;
		$('#del_status_text_im').text('Переписка удаляется..');
		$.post('/index.php?go=im&act=del', {im_user_id: u}, function(d){
			Box.Close('im_del'+u);
			Box.Info('ok_im', 'История переписки удалена', 'Все сообщения диалога были успешно удалены.', 300, 3000);
			$('#okim'+u).remove();
		});
	},
	updateDialogs: function(){
		$.post('/index.php?go=im&act=upDialogs', function(d){
			$('#updateDialogs').html(d);
		});
	}
}

//HAPPY FRIENDS
var HappyFr = {
	Show: function(){
		$('.profile_block_happy_friends').css('max-height', (($('.profile_onefriend_happy').size()-4)/2)*190+190+'px');
		$('#happyAllLnk').attr('onClick', 'HappyFr.Close()');
		$('.profile_block_happy_friends_lnk').text('Скрыть');
	},
	Close: function(){
		$('.profile_block_happy_friends').css('max-height', '190px');
		$('#happyAllLnk').attr('onClick', 'HappyFr.Show()');
		$('.profile_block_happy_friends_lnk').text('Показать все');
	},
	HideSess: function(){
		$('.js_titleRemove').remove();
		$('#happyBLockSess').hide();
		$.post('/index.php?go=happy_friends_block_hide');
	}
}

//FAST SEARCH
var vii_search_delay = false;
var vii_search_val = '';
var FSE = {
	Txt: function(){
		var a = $('#query').val();
		if(a.length > 43){
			tch = '..';
			nVal = a.substring(0, 43);
		} else {
			tch = '';
			nVal = a;
		}
		$('#fast_search_txt').text(nVal+tch);
		0 == a.length ? $(".fast_search_bg").hide() : vii_search_val != a && a != 0 < a.length && (clearInterval(vii_search_delay), vii_search_delay = setInterval(function(){
			FSE.GoSe(a);
		}, 600));
		if(a != 0)
			$(".fast_search_bg").show();
	},
	GoSe: function(val){
		clearInterval(vii_search_delay);
		if(val != 0){
			if($('#se_type').val() == 1 || $('#se_type').val() == 2 || $('#se_type').val() == 4){
				$.post('/index.php?go=fast_search', {query: val, se_type: $('#se_type').val()}, function(d){
					$('#reFastSearch').html(d);
				});
			} else
				$('#reFastSearch').html('');
		} else {
			$(".fast_search_bg").hide();
			$('#reFastSearch').html('');
		}

		vii_search_val = val;
	},
	ClrHovered: function(id){
		for(i = 0; i <= 8; i++){
			$('#all_fast_res_clr'+i).css('background', '#fff');
		}
		$('#'+id).css('background', '#eef3f5');
	}
}

//COMPLAIT / REPORT
var Report = {
	Box: function(act, id){
		Box.Close();
		if(act == 'photo') lang_report = 'Жалоба на фотографию';
		else if(act == 'video') lang_report = 'Жалоба на видеозапись';
		else if(act == 'note') lang_report = 'Жалоба на заметку';
		else lang_report = '';
		Box.Show('report', 400, lang_report, '<div class="report_pad">Пожалуйста, выберите причину, по которой Вы хотите сообщить администрации сайта об этом материале.<div class="clear"></div><br /><select id="type_report" class="inpst" style="width:212px" onChange="if(this.value > 1) {$(\'#report_comm_block\').show();$(\'#text_report\').focus()} else {$(\'#report_comm_block\').hide();$(\'#text_report\').val(\'\')}"><option value="1">Материал для взрослых</opyion><option value="2">Детская порнография</opyion><option value="3">Эктремизм</opyion><option value="4">Насилие</opyion><option value="5">Пропаганда наркотиков</opyion></select><div class="clear"></div><div id="report_comm_block" class="no_display"><br />Комментарий:<br /><br /><textarea id="text_report" class="inpst" style="width:200px;height:80px"></textarea></div></div>', lang_msg_close, lang_box_send, 'Report.Send(\''+act+'\', '+id+')');
		$('#audio_lnk').focus();
		$('#video_object').hide();
	},
	Send: function(act, id){
		$('#box_loading').show();
		ge('box_butt_create').disabled = true;
		$.post('/index.php?go=report', {act: act, id: id, type_report: $('#type_report').val(), text_report: $('#text_report').val()}, function(d){
			Box.Close();
			Box.Info('yes_report', 'Спасибо', 'Ваша жалоба отправлена администрации сайта и будет рассмотрена в ближайшее время.', 300, 3000);
			$('#video_object').show();
		});
	},
	WallSend: function(act, id){
		$('#wall_record_'+id).html('<div style="color:#869ba7; padding:15px; text-align:center;">Сообщение помечено как спам.</div>');
		setTimeout("$('#wall_recordss_"+id+"').fadeOut('slow')", 3200);
		$('#wall_fast_block_'+id).remove();
		$('.js_titleRemove').remove();
		$.post('/index.php?go=report', {act: act, id: id});
	}
}

//REPOST
var Repost = {
	Box: function(rec_id, g_tell){
		Box.Page('/index.php?go=repost&act=all', 'rec_id='+rec_id, 'repost', 430, 'Отправка записи', true, 'Поделиться записью', 'Repost.Send('+rec_id+', '+g_tell+')');
	},
	Send: function(rec_id, g_tell){
		comm = $('#comment_repost').val();
		type = $('#type_repost').val();
		if(type == 1) cas = 'for_wall';
		else if(type == 2)
			if(g_tell) cas = 'groups_2';
			else cas = 'groups';
		else if(type == 3) cas = 'message';
		else cas = '';
		$('#box_loading').show();
		ge('box_button').disabled = true;
		$.post('/index.php?go=repost&act='+cas, {rec_id: rec_id, comm: comm, sel_group: $('#sel_group').val(), g_tell: g_tell, for_user_id: $('#for_user_id').val()}, function(d){
			if(d == 1){
				$('#box_loading').hide();
				ge('box_button').disabled = false;
				ShowInfo('red', lang_wall_tell_tes, 3000);
			} else {
				if(type == 1) Box.Info('yes_report', 'Запись отправлена.', 'Теперь эта запись появится в новостях у Ваших друзей.', 300, 2500);
				if(type == 2) Box.Info('yes_report', 'Запись отправлена.', 'Теперь эта запись появится на странице сообщества.', 300, 2500);
				if(type == 3) Box.Info('yes_report', 'Сообщение отправлено.', 'Ваше сообщение отправлено.', 300, 2500);
				Box.Close();
			}
		});
	}
}