<div id="album_{aid}" class="friend_list">
  <a href="/albums/view/{aid}" onClick="Page.Go(this.href); return false">
  <div class="friend_list_ava" style="width:220px; height:140px;"><span id="cover_{aid}"><img src="{cover}" alt="" /></span></div>
  </a>
  <div class="albums_name"><a href="/albums/view/{aid}" onClick="Page.Go(this.href); return false" id="albums_name_{aid}">{name}</a>
  </div>
  <div class="albums_photo_num" style="max-height:53px; padding-top:2px; color:#555; overflow:hidden;"><span id="descr_{aid}">{descr}</span></div>
  <div class="albums_photo_num">{photo-num}, {comm-num}</div>
  <div class="albums_photo_num">Updated {date}</div>
  <div style="height:5px;"></div>
  [owner]
  <div class="albums_infowalltext"><a href="/" onClick="Albums.EditBox({aid}); return false">Edit album</a>
    &nbsp; | &nbsp;<a href="/" onClick="Albums.EditCover({aid}); return false">Edit cover photo</a>
    &nbsp; | &nbsp;<a href="/" onClick="Albums.Delete({aid}, '{hash}'); return false">Delete</a>
  </div>
  [/owner]
  <div class="clear"></div>
</div>
