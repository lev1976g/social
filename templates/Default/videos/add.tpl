<script type="text/javascript">$('#box_button').css('display', 'none');</script>

<div class="albums_create">
  <div class="cover_edit_title" style="margin:-10px; margin-bottom:10px;">Ссылка на видеоролик:</div>
  <input type="text" maxlength="100" placeholder="Введите ссылку.." style="width:414px;" id="video_lnk" onKeyUp="videos.load()" />
  <div id="vi_info" style="margin-top:10px;">
    <div class="red_error" id="no_serviece" style="display:none;">Видеосервис не поддерживается либо ссылка является неправильной<br />
    </div>
    <font style="color:#888;">Поддерживаемые видеосервисы: <strong>YouTube</strong>, <strong>RuTube.Ru</strong>, <strong>Vimeo.Com</strong>, <strong>Smotri.Com</strong></font>
  </div>
  <div id="result_load" class="no_display">
    <div class="cover_edit_title" style="margin:-10px; margin-top:10px; box-shadow:inset 0px 1px 0px 0px #e7edf4; margin-bottom:10px;">Изображение:</div>
    <div id="photo" class="videos_res_photos"></div>
    <div class="clear"></div>
    <div class="cover_edit_title" style="margin:-10px; margin-top:10px; box-shadow:inset 0px 1px 0px 0px #e7edf4; margin-bottom:10px;">Название:</div>
    <input type="text" id="title" style="width:414px;" maxlength="65" />
    <div class="clear"></div>
    <div class="cover_edit_title" style="margin:-10px; margin-top:10px; box-shadow:inset 0px 1px 0px 0px #e7edf4; margin-bottom:10px;">Описание:</div>
    <textarea id="descr" style="width:414px; height:50px"></textarea>
    <input type="hidden" id="good_video_lnk" />
    <div class="clear" style="height:8px;"></div>
    <div>Кто может смотреть это <b>видео</b>?
      <select style="margin-left:10px;" id="privacy" value="1">
        <option value="1">Все пользователи</option>
        <option value="2">Только друзья</option>
        <option value="3">Только я</option>
      </select>
    </div>
    <div class="clear"></div>
  </div>
  <div class="clear" style="height:20px;"></div>
</div>
<div class="clear"></div>
