<div class="yellow_error" id="info_save" style="display:none;font-weight:normal;"></div>
<div class="clear"></div>
<div class="texta">Пол:</div>
<div class="padstylej">
  <select id="sex" class="inpst" onChange="sp.check()">
    <option value="0">- Не выбрано -</option>
  {sex}
  </select>
</div>
<div class="mgclr"></div>
<div class="texta">Дата рождения:</div>
<div class="padstylej">
  <select id="day" class="inpst">
    <option>- День -</option>
  {user-day}
  </select>
  <select id="month" class="inpst">
    <option>- Месяц -</option>
  {user-month}
  </select>
  <select id="year" class="inpst">
    <option>- Год -</option>
    {user-year}
  </select>
</div>
<div class="mgclr"></div>
<div class="texta">Страна:</div>
<div class="padstylej">
  <select id="country" class="inpst" onChange="General.AllCities(this.value); return false;">
    <option value="0">- Не выбрано -</option>  
  {country}
  </select>
  <img src="{theme}/images/loaders/loading_mini.gif" alt="" class="load_mini" id="load_mini" /></div>
<div class="mgclr"></div>
<span id="show_city">
<div class="texta">Город:</div>
<div class="padstylej">
  <select id="city" class="inpst">
    <option value="0">- Не выбрано -</option>
  {city}
  </select>
  <img src="{theme}/images/loaders/loading_mini.gif" class="load_mini" id="load_mini" /></div>
<div class="mgclr"></div>
</span>
<div class="mgclr"></div>
<div class="texta">Телефон:</div>
<div class="padstylej">
  <input type="text" id="mobile" class="inpst" placeholder="Телефон:" value="{mobile}" />
  <img src="{theme}/images/loaders/loading_mini.gif" alt="" class="load_mini" id="load_mini" /></div>
<div class="mgclr"></div>
<div class="texta">Twitter:</div>
<div class="padstylej">
  <input type="text" id="twitter" class="inpst" placeholder="Twitter:" value="{twitter}" />
  <img src="{theme}/images/loaders/loading_mini.gif" alt="" class="load_mini" id="load_mini" /></div>
<div class="texta">&nbsp;</div>
<div class="button_div fl_l">
  <button id="saveform" onClick="EditProfile.SaveInfo();">Сохранить</button>
</div>
<div class="mgclr"></div>
<div class="margin_top_10"></div>
<div class="allbar_title">Изменить имя</div>
<div class="red_error no_display name_errors" id="err_name_1" style="font-weight:normal;">Специальные символы и пробелы запрещены.</div>
<div class="yellow_error no_display name_errors" id="ok_name" style="font-weight:normal;">Изменения успешно сохранены.</div>
<div class="texta">Ваше имя:</div>
<input type="text" id="name" class="inpst" maxlength="100"  style="width:150px;" value="{name}" />
<span id="validName"></span>
<div class="mgclr"></div>
<div class="texta">Ваша фамилия:</div>
<input type="text" id="lastname" class="inpst" maxlength="100"  style="width:150px;" value="{lastname}" />
<span id="validLastname"></span>
<div class="mgclr"></div>
<div class="texta">&nbsp;</div>
<div class="button_div fl_l">
  <button onClick="EditProfile.SaveName();" id="SaveName">Изменить имя</button>
</div>