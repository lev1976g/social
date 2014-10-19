<script type="text/javascript">
$(document).ready(function(){
	videos.scroll();
});
</script>

<ul class="nav_menu">
  <li id="nav_menu_active" style="cursor:default; color:#8f99a2;">All videos[not-owner] {name}[/not-owner] </li>
  [admin-video-add][owner]
  <li onClick="videos.add(); return false;"> Add video </li>
  [/owner][/admin-video-add]
  [not-owner]
  <li onClick="Page.Go('/u{user-id}'); return false;"> К странице {name} </li>
  [/not-owner]
</ul>
<input type="hidden" value="{user-id}" id="user_id" />
<input type="hidden" id="set_last_id" />
<input type="hidden" id="videos_num" value="{videos_num}" />
