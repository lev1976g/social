<script type="text/javascript" src="{theme}/js/albums.js"></script>

<ul class="nav_menu">
  <li onClick="Page.Go('/albums/{user-id}'); return false;">Все альбомы </li>
  <li onClick="Page.Go('/albums/view/{aid}'); return false;">{album-name}</li>
  <li onClick="Page.Go('/albums/view/{aid}/comments/'); return false;">Комментарии к альбому</li>
  <li id="nav_menu_active" style="cursor:default; color:#8f99a2;">Добавить фотографии</li>
</ul>
<div class="white_content">
  <div class="yellow_error">Поддерживаемые форматы файлов:  JPG, PNG и GIF.</div>
  <div class="cover_edit_title" id="l_text" style="display:none; margin:-10px; padding:12px; font-size:14px; margin-bottom:10px;">Загруженные фотографии</div>
  <div class="clear"></div>
  <span id="photos"></span>
  <div class="clear" style="height:50px;"></div>
  <div align="center">
    <button id="upload">Добавить фотографию</button>
    <button class="green_button" style="margin-left:10px;" onClick="Page.Go('/albums/view/{aid}'); return false;">Просмотр альбома</button>
  </div>
  <div class="clear" style="height:50px;"></div>
</div>
<input type="hidden" value="{aid}" id="aid" />
