<div class="blacklist_user"><a href="/u{user-id}" onClick="Page.Go(this.href); return false">
  <img src="{ava}" style="float:left; margin-right:5px;" /></a>
  <div style="height:3px;"></div>
  <a href="/u{user-id}" onClick="Page.Go(this.href); return false"><b style="font-size:14px;">{name}</b></a>
  <div style="margin-top:4px"><a href="/u{user-id}" onClick="settings.delblacklist('{user-id}'); return false" id="del_{user-id}">Разблокировать</a>
  </div>
</div>
<input type="hidden" id="badlistnum{user-id}" />