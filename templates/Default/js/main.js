var uagent = navigator.userAgent.toLowerCase();
var is_safari = ((uagent.indexOf('safari') != -1) || (navigator.vendor == "Apple Computer, Inc."));
var is_ie = ((uagent.indexOf('msie') != -1) && (!is_opera) && (!is_safari) && (!is_webtv));
var is_ie4 = ((is_ie) && (uagent.indexOf("msie 4.") != -1));
var is_moz = (navigator.product == 'Gecko');
var is_ns = ((uagent.indexOf('compatible') == -1) && (uagent.indexOf('mozilla') != -1) && (!is_opera) && (!is_webtv) && (!is_safari));
var is_ns4 = ((is_ns) && (parseInt(navigator.appVersion) == 4));
var is_opera = (uagent.indexOf('opera') != -1);
var is_kon = (uagent.indexOf('konqueror') != -1);
var is_webtv = (uagent.indexOf('webtv') != -1);
var is_win = ((uagent.indexOf("win") != -1) || (uagent.indexOf("16bit") != -1));
var is_mac = ((uagent.indexOf("mac") != -1) || (navigator.vendor == "Apple Computer, Inc."));
var is_chrome = (uagent.match(/Chrome\/\w+\.\w+/i)); if(is_chrome == 'null' || !is_chrome || is_chrome == 0) is_chrome = '';
var ua_vers = parseInt(navigator.appVersion);
var req_href = location.href;
var vii_interval = false;
var vii_interval_im = false;
var scrollTopForFirefox = 0;
var url_next_id = 1;

if(CheckRequestPhoto(req_href)){
	$(document).ready(function(){
		Photo.Show(req_href);
	});
}

if(CheckRequestVideo(req_href)){
	$(document).ready(function(){
		var video_id = req_href.split('_');
		var section = req_href.split('sec=');
		var fuser = req_href.split('wall/fuser=');

		if(fuser[1])
			var close_link = '/u'+fuser[1];
		else
			var close_link = '';
		
		if(section[1]){
			var xSection = section[1].split('/');

			if(xSection[0] == 'news')
				var close_link = 'news';

			if(xSection[0] == 'msg'){
				var msg_id = xSection[1].split('id=');
				var close_link = '/messages/show/'+msg_id[1];
			}
		}
		
		videos.show(video_id[1], req_href, close_link);
	});
}

//AJAX PAGES
window.onload = function(){ 
	window.setTimeout(
		function(){ 
			window.addEventListener(
				"popstate",  
				function(e){
					e.preventDefault(); 

					if(CheckRequestPhoto(e.state.link))
						Photo.Prev(e.state.link);
					else if(CheckRequestVideo(e.state.link))
						videos.prev(e.state.link);
					else
						Page.Prev(e.state.link);
				},  
			false); 
		}, 
	1); 
}
function CheckRequestPhoto(request){
	var pattern = new RegExp(/photo[0-9]/i);
 	return pattern.test(request);
}
function CheckRequestVideo(request){
	var pattern = new RegExp(/video[0-9]/i);
 	return pattern.test(request);
}
var Page = {
	Loading: function(f){
		var top_pad = $(window).height()/2-50;
		if(f == 'start'){
			$('#ajax_position').remove();
			$('html').append('<div id="ajax_position"><div align="center"><div class="ajax_content" style="margin-top:'+top_pad+'px"><div class="ajax_loader"></div><div class="clear"></div></div></div>');
			$('#ajax_position').show();
		}
		if(f == 'stop'){
			$('#ajax_position').remove();
		}
	},
	Go: function(h){	
		history.pushState({link:h}, null, h);
		$('.js_titleRemove').remove();
		
		clearInterval(vii_interval);
		clearInterval(vii_interval_im);

		Page.Loading('start');
		$('#content').load(h, {ajax: 'yes'}, function(data){
			Page.Loading('stop');
			$('html, body').scrollTop(0);
			
			$('.ladybug_ant').imgAreaSelect({remove: true});
			
			//Удаляем кеш фоток, видео, модальных окон
			$('.photo_view, .box, .info_box, .video_view').remove();
			$('#query, #query_full').val('');
			
			//Возвращаем scroll
			$('html').css('overflow-y', 'auto');

		}).css('min-height', '0px');
	},
	Prev: function(h){
		clearInterval(vii_interval);
		clearInterval(vii_interval_im);
		
		Page.Loading('start');
		$('#content').load(h, {ajax: 'yes'}, function(data){
			Page.Loading('stop');

			$('html, body').scrollTop(0);
			
			$('.ladybug_ant').imgAreaSelect({remove: true});
			
			//Удаляем кеш фоток, видео, модальных окон
			$('.photo_view, .box, .info_box, .video_view').remove();
			
			//Возвращаем scroll
			$('html').css('overflow-y', 'auto');

		}).css('min-height', '0px');		
	}
}

//GENERAL FUNCTIONS
var General = {
	AllCities: function(id){
		$('#load_mini').show();
		if(id > 0){
			$('#show_city').slideDown();
			$('#city').load('/index.php?go=all_cities', {country: id});
			$('#load_mini').hide();
		} else {
			$('#show_city').slideUp();
			$('#load_mini').hide();
		}
	},
}

//MODAL BOX
var Box = {
	Page: function(url, data, name, width, title, footer, func_text, func, height, overflow, footer_text){
	
		//url - ссылка которую будем загружать
		//data - POST данные
		//name - id окна
		//width - ширина окна
		//title - заголовк окна
		//content - контент окна
		//close_text - текст закрытия
		//func_text - текст который будет выполнять функцию
		//func - функция текста "func_text"
		//height - высота окна
		//overflow - постоянный скролл
		
		Page.Loading('start');
		$.post(url, data, function(html){
			if(!CheckRequestVideo(location.href))
				Box.Close(name);
			Box.Show(name, width, title, html, footer, func_text, func, height, overflow, footer_text);
			Page.Loading('stop');
		});
	},
	Show: function(name, width, title, content, footer, func_text, func, height, overflow, footer_text){
		
		//name - id окна
		//width - ширина окна
		//title - заголовк окна
		//content - контент окна
		//footer - footer окна
		//func_text - текст который будет выполнять функцию
		//func - функция текста "func_text"
		//height - высота окна
		//overflow - постоянный скролл
		
		if(func_text)
			var func_but = '<button onClick="'+func+'" id="box_button">'+func_text+'</button>';
		else
			var func_but = '';
		
		var box_loading = '<img id="box_loading" src="/templates/'+template+'/images/loaders/loading_mini.gif" />';
		
		if(height)
			var top_pad = ($(window).height()-150-height)/2;
			if(top_pad < 0)
				top_pad = 100;
			
		if(overflow)
			var overflow = 'overflow-y:scroll;';
		else
			var overflow = '';
			
		if(height)
			var sheight = 'height:'+height+'px';
		else
			var sheight = '';
			
		if(footer_text)
			var footer_text = '<div id="box_bottom_left_text" class="fl_l">'+footer_text+box_loading+'</div>';
		else
			var footer_text = '<div id="box_bottom_left_text" class="fl_l">'+box_loading+'</div>';
			
		if(footer)
			var footer = '<div class="box_footer">'+footer_text+func_but+'</div>';
		else
			var footer = '';

		$('html').append('<div id="box_'+name+'" class="box"><div class="box_position" style="width:'+width+'px;margin-top:'+top_pad+'px;"><div class="box_title" id="box_title_'+name+'">'+title+'<div class="box_close" onClick="Box.Close(\''+name+'\'); return false;"></div></div><div class="box_content" id="box_content_'+name+'" style="'+sheight+';'+overflow+'">'+content+'<div class="clear"></div></div>'+footer+'</div></div>');
		
		$('#box_'+name).show();

		if(is_moz && !is_chrome)
			scrollTopForFirefox = $(window).scrollTop();
		
		$('html').css('overflow', 'hidden');

		if(is_moz && !is_chrome)
			$(window).scrollTop(scrollTopForFirefox);
		
		$(window).keydown(function(event){
			if(event.keyCode == 27) {
				Box.Close(name, cache);
			} 
		});
	},
	Close: function(name){
	
		$('#box_'+name).remove();

		if(CheckRequestVideo(location.href) == false && CheckRequestPhoto(location.href) == false)
			$('html').css('overflow-y', 'auto');
			
		if(CheckRequestVideo(location.href))
			$('#video_object').show();
			
		if(is_moz && !is_chrome)
			$(window).scrollTop(scrollTopForFirefox);
	},
	Info: function(bid, title, content, width, tout){
		var top_pad = ($(window).height()-115)/2;
		$('html').append('<div id="'+bid+'" class="info_box"><div class="info_box_margin" style="width: '+width+'px; margin-top: '+top_pad+'px"><strong><span>'+title+'</span></strong><br />'+content+'</div></div>');
		$(bid).show();
		
		if(!tout)
			var tout = 1800;
		
		setTimeout("Box.InfoClose()", tout);
		
		$(window).keydown(function(event){
			if(event.keyCode == 27) {
				Box.InfoClose();
			} 
		});
	},
	InfoClose: function(){
		$('.info_box').fadeOut();
	}
}

//LANG
var Language = {
	Change: function(){
		Page.Loading('start');
		$.post('/index.php?go=languages', function(data){
			Box.Show('languages', 240, 'Выберите язык:', data, false);
			Page.Loading('stop');
	});
  }
}

function ge(i){
	return document.getElementById(i);
}
function butloading(i, w, d, t){
	if(d == 'disabled'){
		$('#'+i).html('<div style="width:'+w+'px;text-align:center;"><img src="/templates/Default/images/loaders/loading_mini.gif" alt="" /></div>');
		ge(i).disabled = true;
	} else {
		$('#'+i).html(t);
		ge(i).disabled = false;
	}
}
function textLoad(i){
	$('#'+i).html('<img src="/templates/Default/images/loaders/loading_mini.gif" alt="" />').attr('onClick', '').attr('href', '#');
}
function updateNum(i, type){
	if(type)
		$(i).text(parseInt($(i).text())+1);
	else
		$(i).text($(i).text()-1);
}
function ShowInfo(color, text, tim){
	if(!tim)
		var tim = 2500;
	if(color == 'red'){
		var color = 'tooltip_red';
	} else if(color == 'green'){
		var color = 'tooltip_green';
	}
		
	$('#'+color+'').remove();
	$('html').append('<div id="'+color+'" style="display:none;">'+text+'</div>');
	$('#'+color+'').fadeIn('fast');
	setTimeout("$('#"+color+"').fadeOut('fast')", tim);
}
function langNumric(id, num, text1, text2, text3, text4, text5){
	strlen_num = num.length;
	
	if(num <= 21){
		numres = num;
	} else if(strlen_num == 2){
		parsnum = num.substring(1,2);
		numres = parsnum.replace('0','10');
	} else if(strlen_num == 3){
		parsnum = num.substring(2,3);
		numres = parsnum.replace('0','10');
	} else if(strlen_num == 4){
		parsnum = num.substring(3,4);
		numres = parsnum.replace('0','10');
	} else if(strlen_num == 5){
		parsnum = num.substring(4,5);
		numres = parsnum.replace('0','10');
	}
	
	if(numres <= 0)
		var gram_num_record = text5;
	else if(numres == 1)
		var gram_num_record = text1;
	else if(numres < 5)
		var gram_num_record = text2;
	else if(numres < 21)
		var gram_num_record = text3;
	else if(numres == 21)
		var gram_num_record = text4;
	else
		var gram_num_record = '';
	
	$('#'+id).html(gram_num_record);
}

var Account = {
	Delete: function(){
		 Box.Show('del_page', 400, 'Удаление страницы', '<div style="padding:15px;">Вы уверены, что хотите удалить свою страницу ?</div>', lang_box_canсel, 'Да, удалить страницу', 'Account.StartDelete();');
	},
	StartDelete: function(){
		$('#box_loading').fadeIn('fast');
		$('.box_footer .button_div, .box_footer .button_div_gray').fadeOut('fast');
		$.post('/index.php?go=delete_account', function(){
			window.location.href = '/';
		});
	},
}