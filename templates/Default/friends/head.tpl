[all-friends]
<ul class="nav_menu">
  <li style="cursor:default; color:#8f99a2;" id="nav_menu_active">All friends </li>
  <li onClick="Page.Go('/friends/online/{user-id}'); return false;"> Online friends </li>
  [owner]
  <li onClick="Page.Go('/friends/requests'); return false;"> Friends demands {demands} </li>
  [/owner]
  [not-owner]
  <li onClick="Page.Go('/u{user-id}"> К странице {name} </li>
  [/not-owner]
</ul>
[/all-friends]

[request-friends]
<ul class="nav_menu">
  <li onClick="Page.Go('/friends/{user-id}'); return false;">All friends </li>
  <li onClick="Page.Go('/friends/online/{user-id}'); return false;"> Online friends </li>
  <li style="cursor:default; color:#8f99a2;" id="nav_menu_active"> Friends demands {demands} </li>
</ul>
[/request-friends]

[online-friends]
<ul class="nav_menu">
  <li onClick="Page.Go('/friends/{user-id}'); return false;">All friends </li>
  <li style="cursor:default; color:#8f99a2;" id="nav_menu_active"> Online friends </li>
  [owner]
  <li onClick="Page.Go('/friends/requests'); return false;"> Friends demands {demands} </li>
  [/owner]
  [not-owner]
  <li onClick="Page.Go('/u{user-id}"> К странице {name} </li>
  [/not-owner]
</ul>
[/online-friends]