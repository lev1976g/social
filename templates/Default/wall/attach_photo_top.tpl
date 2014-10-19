[top]<script type="text/javascript">
$(document).ready(function(){
	Xajax = new AjaxUpload('upload', {
		action: '/index.php?go=attach',
		name: 'uploadfile',
		onSubmit: function (file, ext) {
			if (!(ext && /^(jpg|png|jpeg|gif|jpe)$/.test(ext))) {
				ShowInfo('red', lang_bad_format, 3500);
				return false;
			}
			Page.Loading('start');
		},
		onComplete: function (file, response){
			if(response == 'big_size'){
				ShowInfo('red', lang_max_size, 3500);
				Page.Loading('stop');
			} else {
				var response = response.split('|||');
				var imgname = response[1].split('/');
				wall.attach_insert('photo', response[1], 'attach|'+imgname[6].replace('c_', ''), response[2]);
				Page.Loading('stop');
			}
		}
	});
});
</script>
<div class="cover_edit_title">
<div class="fl_l" style="margin-top:8px; margin-left:5px;">Всего {photo-num}</div>
<div class="button_div_gray fl_r"><button class="green_button" id="upload">Загрузить новую фотографию</button></div>
<div class="clear"></div>
</div>
<div class="clear"></div>
<div style="padding:10px;padding-bottom:15px;">[/top]
[bottom]<div class="clear"></div></div>[/bottom]