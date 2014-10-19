<div class="friend_list" id="friend_{user-id}">
  <a href="/u{user-id}" onClick="Page.Go(this.href); return false">
  <div class="friend_list_ava"><img src="{ava}" id="ava_{user-id}" /></div>
  </a>
  <a href="/u{user-id}" style="font-size:14px;" onClick="Page.Go(this.href); return false"><b>{name}</b></a>
  <div class="fl_r">
    <button onClick="Messages.Write({user-id}); return false" class="green_button fl_r">Write message</button>
    <div class="clear" style="height:10px;"></div>
    <button onClick="friends.delet({user-id}, 1); return false" class="gray_button fl_r">Remove from friends</button>
  </div>
  <div style="margin-top:5px;"></div>
  {age}
  <div style="margin-top:5px;"></div>
  {country}{city}
  <div class="clear"></div>
</div>
