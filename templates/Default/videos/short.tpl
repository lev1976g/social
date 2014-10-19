<div class="friend_list" id="video_{id}">
  <a href="/video{user-id}_{id}" onClick="videos.show({id}, this.href); return false">
  <div class="friend_list_ava"><img src="{photo}jpg" id="ava_{user-id}" width="100px;"  height="100px;"/></div>
  </a>
   <a href="/video{user-id}_{id}" id="video_title_{id}" onClick="videos.show({id}, this.href); return false"><b>{title}</b></a>
  <div class="fl_r">
    <button onClick="videos.editbox({id}); return false" class="green_button fl_r">Edit video</button>
    <div class="clear" style="height:10px;"></div>
    <button onClick="videos.delet({id}); return false" class="gray_button fl_r">Delete</button>
  </div>
  <div style="margin-top:5px;"></div>
  {comm}
  <div style="margin-top:5px;"></div>
  Added {date}
  <div class="clear"></div>
  <input type="hidden" value="{id}" id="onevideo" />
</div>
