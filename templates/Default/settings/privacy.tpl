<ul class="nav_menu">
  <li onClick="Page.Go('/settings'); return false;">Общее </li>
  <li id="nav_menu_active" style="cursor:default; color:#8f99a2;"> Приватность </li>
  <li onClick="Page.Go('/settings/blacklist'); return false;"> Черный список </li>
  <li onClick="Page.Go('/balance'); return false;"> Баланс </li>
</ul>
<div class="white_content">
  <div style="padding:10px; box-shadow:inset 0px -1px 0px 0px #e7edf4;";>Кто может писать мне личные <b>сообщения</b>:
    <select style="margin-left:10px;" id="val_msg" value="{val_msg}">
      <option value="0" hidden="">{val_msg_text}</option>
      <option value="1">Все пользователи</option>
      <option value="2">Только друзья</option>
      <option value="3">Никто</option>
    </select>
  </div>
  <div class="clear"></div>
  <div style="padding:10px; box-shadow:inset 0px -1px 0px 0px #e7edf4;";>Кто видит чужие записи на моей <b>стене</b>:
    <select style="margin-left:10px;" id="val_wall1" value="{val_wall1}">
      <option value="0" hidden="">{val_wall1_text}</option>
      <option value="1">Все пользователи</option>
      <option value="2">Только друзья</option>
      <option value="3">Только я</option>
    </select>
  </div>
  <div class="clear"></div>
  <div style="padding:10px; box-shadow:inset 0px -1px 0px 0px #e7edf4;";>Кто может оставлять сообщения на моей <b>стене</b>:
    <select style="margin-left:10px;" id="val_wall2" value="{val_wall2}">
      <option value="0" hidden="">{val_wall2_text}</option>
      <option value="1">Все пользователи</option>
      <option value="2">Только друзья</option>
      <option value="3">Только я</option>
    </select>
  </div>
  <div class="clear"></div>
  <div style="padding:10px; box-shadow:inset 0px -1px 0px 0px #e7edf4;";>Кто может комментировать мои <b>записи</b>:
    <select style="margin-left:10px;" id="val_wall3" value="{val_wall3}">
      <option value="0" hidden="">{val_wall3_text}</option>
      <option value="1">Все пользователи</option>
      <option value="2">Только друзья</option>
      <option value="3">Только я</option>
    </select>
  </div>
  <div class="clear"></div>
  <div style="padding:10px; box-shadow:inset 0px -1px 0px 0px #e7edf4;";>Кто видит основную информацию моей <b>страницы</b>:
    <select style="margin-left:10px;" id="val_info" value="{val_info}">
      <option value="0" hidden="">{val_info_text}</option>
      <option value="1">Все пользователи</option>
      <option value="2">Только друзья</option>
      <option value="3">Только я</option>
    </select>
  </div>
  <div class="clear" style="height:10px;"></div>
    <div align="center"><button onClick="settings.savePrivacy(); return false" id="savePrivacy">Сохранить настройки</button></div>
  <div class="clear"></div>
</div>
