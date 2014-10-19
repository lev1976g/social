
[all-albums]
<script type="text/javascript" src="{theme}/js/albums.js"></script>
<ul class="nav_menu">
  <li id="nav_menu_active" style="cursor:default; color:#8f99a2;">[not-owner]All albums {name}[/not-owner][owner]All albums[/owner] </li>
  [owner]
  <li onClick="Albums.CreatAlbum(); return false;"> Create album </li>
  [/owner]
  <li onClick="Page.Go('/albums/comments/{user-id}'); return false;">Albums comments </li>
  [not-owner]
  <li onClick="Page.Go('/u{user-id}'); return false;"> Go to {name} page </li>
  [/not-owner]
</ul>
[/all-albums]
[view]
<script type="text/javascript" src="{theme}/js/albums.js"></script>
<input type="hidden" id="all_p_num" value="{all_p_num}" />
<input type="hidden" id="aid" value="{aid}" />
<ul class="nav_menu">
  <li onClick="Page.Go('/albums/{user-id}'); return false;">[not-owner]All albums {name}[/not-owner][owner]All albums[/owner] </li>
  <li id="nav_menu_active" style="cursor:default; color:#8f99a2;">{album-name}</li>
  <li onClick="Page.Go('/albums/view/{aid}/comments/'); return false;">Album comments</li>
  [owner]
  <li onClick="Page.Go('/albums/add/{aid}'); return false;">Add photos</li>
  [/owner]
  [not-owner]
  <li onClick="Page.Go('/u{user-id}'); return false;"> Go to {name} page </li>
  [/not-owner]
</ul>
[/view]
[comments]
<script type="text/javascript" src="{theme}/js/albums.js"></script>
<ul class="nav_menu">
  <li onClick="Page.Go('/albums/{user-id}'); return false;">[not-owner]All albums {name}[/not-owner][owner]All albums[/owner] </li>
  [owner]
  <li onClick="Albums.CreatAlbum(); return false;"> Create album </li>
  [/owner]
  <li  id="nav_menu_active" style="cursor:default; color:#8f99a2;">Albums comments </li>
  [not-owner]
  <li onClick="Page.Go('/u{user-id}'); return false;"> Go to {name} page </li>
  [/not-owner]
</ul>
[/comments]
[albums-comments]
<script type="text/javascript" src="{theme}/js/albums.js"></script>
<ul class="nav_menu">
  <li onClick="Page.Go('/albums/{user-id}'); return false;">[not-owner]All albums {name}[/not-owner][owner]All albums[/owner] </li>
  <li onClick="Page.Go('/albums/view/{aid}'); return false;">{album-name}</li>
  <li id="nav_menu_active" style="cursor:default; color:#8f99a2;">Album comments</li>
  [owner]
  <li onClick="Page.Go('/albums/add/{aid}'); return false;">Add photos</li>
  [/owner]
  [not-owner]
  <li onClick="Page.Go('/u{user-id}'); return false;"> Go to {name} page </li>
  [/not-owner]
</ul>
[/albums-comments]
[all-photos]
<div class="buttonsprofile albumsbuttonsprofile" style="height:10px;">
  <a href="/albums/{user-id}" onClick="Page.Go(this.href); return false;">[not-owner]All albums {name}[/not-owner][owner]All albums[/owner]</a>
  [owner]<a href="" onClick="Albums.CreatAlbum(); return false;">Create album</a>
  [/owner] <a href="/albums/comments/{user-id}" onClick="Page.Go(this.href); return false;">Комментарии к альбомам</a>
  <div class="activetab"><a href="/photos{user-id}" onClick="Page.Go(this.href); return false;">
    <div>Обзор фотографий</div>
    </a>
  </div>
  [not-owner]<a href="/u{user-id}" onClick="Page.Go(this.href); return false;">Go to {name} page</a>
  [/not-owner] </div>
<div class="clear"></div>
<div style="margin-top:8px;"></div>
[/all-photos]