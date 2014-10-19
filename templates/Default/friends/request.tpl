<div class="friend_list">
  <a href="/u{user-id}" onClick="Page.Go(this.href); return false">
  <div class="friend_list_ava"><img src="{ava}" alt="" /></div>
  </a>
  <a href="/u{user-id}" style="font-size:14px;" onClick="Page.Go(this.href); return false"><b>{name}</b></a>
  <div id="action_{user-id}" class="fl_r">
    <button onClick="friends.take({user-id}); return false" class="green_button fl_r">Дружить</button>
    <div class="clear" style="height:10px;"></div>
    <button onClick="friends.reject({user-id}); return false" class="gray_button fl_r">Отклонить</button>
  </div>
  <div style="margin-top:5px;"></div>
  {age}
  <div style="margin-top:5px;"></div>
  {country}{city}
  <div class="clear"></div>
</div>
