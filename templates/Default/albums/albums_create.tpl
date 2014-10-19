<script type="text/javascript" src="{theme}/js/albums.js"></script>
<div class="albums_create">
  <div class="cover_edit_title" style="margin:-10px; margin-bottom:10px;">Название</div>
  <input type="text" placeholder="Введите название фотоальбома.." id="name" maxlength="100" style="width:414px;" />
  <div class="cover_edit_title" style="margin:-10px; margin-top:10px; border-top:1px solid #e7edf4; margin-bottom:10px;">Описание</div>
  <textarea id="descr" placeholder="Введите описание фотоальбома.." style="width:414px; height:70px"></textarea>
  <div class="clear"></div>
  <div style="padding:10px; box-shadow:inset 0px -1px 0px 0px #e7edf4;";>Кто может просматривать этот альбом?
    <select style="margin-left:10px;" id="privacy" value="1">
      <option value="1">Все пользователи</option>
      <option value="2">Только друзья</option>
      <option value="3">Только я</option>
    </select>
  </div>
  <div class="clear"></div>
  <div style="padding:10px; box-shadow:inset 0px -1px 0px 0px #e7edf4;";>Кто может комментировать фотографии?
    <select style="margin-left:10px;" id="privacy_comment" value="1">
      <option value="1">Все пользователи</option>
      <option value="2">Только друзья</option>
      <option value="3">Только я</option>
    </select>
  </div>
  <div class="clear"></div>
</div>
