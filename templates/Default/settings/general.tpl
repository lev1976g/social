<ul class="nav_menu">
  <li id="nav_menu_active" style="cursor:default; color:#8f99a2;">General </li>
  <li onClick="Page.Go('/settings/privacy'); return false;"> Privacy </li>
  <li onClick="Page.Go('/settings/blacklist'); return false;"> Black List </li>
  <li onClick="Page.Go('/balance'); return false;"> Balance </li>
</ul>
<div class="white_content" style="padding:0px; width:600px;">
  <div style="float:left; width:600px; box-shadow:inset 0px -1px 0px 0px #e7edf4;">
    <div class="fl_l" style="width:300px; box-shadow:inset -1px -1px 0px 0px #e7edf4;">
      <div class="cover_edit_title" style="box-shadow:inset -1px 0px 0px 0px #e7edf4; padding:12px;">Change password</div>
      <div style="padding:15px;">
        <div class="yellow_error {code-1}">Код активации из письма с текущего почтового ящика принят. Осталось подтвердить код активации в письме, отправленном на новый почтовый ящик.</div>
        <div class="yellow_error {code-2}">Код активации из письма с нового почтового ящика принят. Осталось подтвердить код активации в письме, отправленном на текущий почтовый ящик.</div>
        <div class="yellow_error {code-3}">Адрес Вашей электронной почты был успешно изменен на новый.</div>
        <div class="clear"></div>
        <div class="green_error no_display" id="ok_pass">Пароль успешно изменён.</div>
        <input type="password" id="old_pass" placeholder="Old password:" maxlength="100" style="width:253px;" />
        <span id="validOldpass"></span>
        <div class="clear" style="height:12px;"></div>
        <input type="password" id="new_pass" placeholder="New password:" maxlength="100" style="width:253px;" onMouseOver="myhtml.title('', 'Пароль должен быть не менее 6 символов в длину', 'new_pass', -15)" />
        <span id="validNewpass"></span>
        <div class="clear" style="height:12px;"></div>
        <input type="password" id="new_pass2" placeholder="Repeat new password:" maxlength="100" style="width:253px;" onMouseOver="myhtml.title('', 'Введите еще раз новый пароль', 'new_pass2', -15)" />
        <span id="validNewpass2"></span>
        <div class="clear" style="height:12px;"></div>
        <button style="width:270px;" onClick="settings.saveNewPwd(); return false" id="saveNewPwd">Change password</button>
      </div>
    </div>
    <div class="fl_l" style="width:300px;">
      <div class="cover_edit_title" style="padding:12px;">Your email address</div>
      <div style="padding:15px; padding-top:12px;">
      <div style="float:left; color:#555; padding-bottom:8px; width:270px;"><strong>Current address: </strong></div>
        <input type="text" placeholder="{email}" disabled maxlength="100" style="width:253px; padding:4px 8px;" />
        <div class="clear" style="height:19px;"></div>
         <div style="float:left; color:#555; padding-bottom:8px; width:270px;"><strong>New adress:</strong></div>
        <input type="text" id="email" placeholder="Write new adress:" maxlength="100" style="width:253px;" />
        <span id="validName"></span>
        <div class="clear" style="height:12px;"></div>
        <button style="width:270px;" onClick="settings.savenewmail(); return false" id="saveNewEmail">Save adress</button>
        <div class="clear"></div>
      </div>
    </div>
  </div>
  <div class="clear"></div>
  <div style="color:#555; text-align:center; padding:15px; font-size:14px;">You can <a onClick="Account.Delete();"> delete your </a>
  </div>
</div>
