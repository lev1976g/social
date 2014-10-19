$(document).ready(function(){
	var aid = $('#aid').val();
	Xajax = new AjaxUpload('upload', {
		action: '/index.php?go=albums&act=upload&aid='+aid,
		name: 'uploadfile',
		onSubmit: function (file, ext) {
			if (!(ext && /^(jpg|png|jpeg|gif|jpe)$/.test(ext))) {
				Box.Info('load_photo_er', lang_dd2f_no, lang_bad_format, 400);
				return false;
			}
			Page.Loading('start');
		},
		onComplete: function (file, response){
			if(response == 'max_img'){
				Box.Info('load_photo_er2', lang_dd2f_no, lang_max_imgs, 340);
				Page.Loading('stop');
				return false
			}
			
			if(response == 'big_size'){
				Box.Info('load_photo_er2', lang_dd2f_no, lang_max_size, 250);
				Page.Loading('stop');
				return false
			}
				
			if(response == 'hacking'){
				return false
			} else {
				response = response.split('|||');
				$('<span id="photo_'+response[0]+'"></span>').appendTo('#photos').html('<div id="cover_'+response[0]+'" class="friend_list" ><a href="/photo'+response[2]+'_'+response[0]+'_sec=loaded" onClick="Photo.Show(this.href); return false"><div class="friend_list_ava" style="width:220px;"><span id="count_img"><img src="'+response[1]+'" alt="" /></span></div></a><div style="font-size:14px; float:left;"><b>'+lang_albums_add_photo+'</b></div><textarea placeholder="Введите описание фотографии.." id="descr_'+response[0]+'" style="width:334px; margin-top:10px; height:95px;"></textarea><div class="clear" style="height:10px;"></div><div align="center"><button onClick="AlbumDeletePhoto(\''+response[0]+'\'); return false;" class="gray_button" style="margin-left:10px;">'+lang_albums_del_photo+'</button><button style="margin-left:10px;" class="gray_button" onClick="PhotoSaveDescr(\''+response[0]+'\'); return false;">'+lang_albums_save_descr+'</button></div><div class="clear" style="height:5px;"></div></div>');
				
				var count_img = $('#count_img img').size();
				if(count_img == 1){
					$('#l_text').show();
					$('.yellow_error').hide();
				}
				
				$('html, body').animate({scrollTop: 99999}, 250);
				Page.Loading('stop');
			}
		}
	});
});
function AlbumDeletePhoto(i){
	Page.Loading('start');
	$.get('/index.php?go=albums&act=del_photo', {id: i}, function(){
		$('#photo_'+i).remove();
		var count_img = $('#count_img img').size();
		if(count_img < 1)
			$('#l_text').hide();

		Page.Loading('stop');
	});
}
function PhotoSaveDescr(i){
	var descr = $('#descr_'+i).val();
	Page.Loading('start');
	$.post('/index.php?go=albums&act=save_descr', {id: i, descr: descr}, function(d){
		Page.Loading('stop');
	});
}
function StartCreatAlbum(){
	var name=$("#name").val();
	var descr=$("#descr").val();
	if(name !=0){
		$("#name").css('background','#fff');
		$('#box_loading').show();
		$.post('/index.php?go=albums&act=create',{name:name, descr:descr, privacy:$('#privacy').val()},function(data){
			$('#box_loading').hide();
			if(data=='no_name'){
				$('.red_error').show().text(lang_empty);
			} else if(data=='no'){
				$('.red_error').show().text(lang_nooo_er);
			} else if(data=='max'){
				Box.Close('albums');
				Box.Info('load_album',lang_dd2f_no,lang_max_albums,280)
			} else {
				Box.Close('albums');
				Page.Go(data);
			}
		})
	} else {
		$("#name").css('background','#ffefef');
		setTimeout("$('#name').css('background', '#fff').focus()",800);
		$('#box_loading').hide();
	}
}