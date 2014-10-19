<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
{head}
</head>
<body>
<div class="top_nav">
  <div class="container" [not-logged]style="margin:auto; width:200px; float:inherit;"[/not-logged]><a href="/" class="top_logo"></a>
    [logged]
    <div class="top_search_ico" onClick="gSearch.go(); return false"></div>
    <input type="text" placeholder="Search.." onKeyPress="if(event.keyCode == 13) gSearch.go();" id="query" maxlength="65" class="top_search" />
    [/logged] </div>
</div>
[logged]
<div class="left_head_menu">
  <ul>
    <li onClick="Page.Go('{my-page-link}'); return false" id="left_head_menu_user">
      <div  id="top_ava"><img src="{top-ava}" width="34px;" class="fl_l" style="margin-right:8px;" /></div>
      <span class="fl_l" style="font-family:Roboto Bold; margin-top:7px; color:#8ca2b4;">{top-name}</span>
    </li>
    <a onClick="Page.Go('/friends{new-demands-link}'); return false" id="new_demands_link">
    <li id="l_h_m_friends">Friends<span id="new_demands">{new_demands}</span></li>
    </a>
    <a onClick="Page.Go('/albums/{my-id}'); return false">
    <li id="l_h_m_photos">Photos</li>
    </a>
    <a onClick="Page.Go('/messages'); return false">
    <li id="l_h_m_messages">Messages<span id="new_msg">{new_msg}</span></li>
    </a>
    <a onClick="Page.Go('/videos'); return false">
    <li id="l_h_m_videos">Videos</li>
    </a>
    <a onClick="Page.Go('/audios'); return false">
    <li id="l_h_m_music">Audios</li>
    </a>
    <a onClick="Page.Go('/news{new-news-link}'); return false" id="new_news_link">
    <li id="l_h_m_news" id="new_news_link">
    News<span id="new_news">{new-news}</span>
    </li>
    </a>
    <a onClick="Page.Go('/groups'); return false">
    <li id="l_h_m_groups">Groups</li>
    </a>
    <a onClick="Page.Go('/fave'); return false">
    <li id="l_h_m_fave">Fave</li>
    </a>
    <a onClick="Page.Go('{new-gifts-link}'); return false" id="new_gifts_link">
    <li id="l_h_m_apps">Gifts<span id="new_gifts">{new-gifts}</span></li>
    </a>
    <a onClick="Page.Go('/docs'); return false">
    <li id="l_h_m_docs">Documents</li>
    </a>
    <li id="left_head_menu_line"></li>
    <a onClick="Page.Go('/settings'); return false">
    <li id="l_h_m_settings">Settings</li>
    </a>
    <a onClick="Page.Go('/help'); return false">
    <li id="l_h_m_help">Help</li>
    </a>
    <a onClick="Page.Go('/terms'); return false">
    <li id="l_h_m_terms">Terms</li>
    </a>
  </ul>
</div>
[/logged]
<div class="clear" style="height:56px;"></div>
<div class="container" [not-logged]style="margin:auto; min-height:520px; float:inherit;"[/not-logged]>
  <div id="content" [not-logged]style="min-height:520px;"[/not-logged]>{info}{content}</div>
  <div class="clear" [/logged]style="height:60px;"[/logged]>
</div>
[not-logged]
<div class="footer">
     <a href="/about" onClick="Page.Go(this.href); return false">About</a>
      <a href="/terms" onClick="Page.Go(this.href); return false">Terms</a>
      <a href="/help" onClick="Page.Go(this.href); return false">Help</a>
      <a href="/jobs" onClick="Page.Go(this.href); return false">Jobs</a>
      <a href="/developers" onClick="Page.Go(this.href); return false">Developers</a>
      <div class="fl_r" >
      <a style="color:#999; cursor:default;">SocialCOM © 2014  ·</a>
      <a style="margin-left:0px;" onClick="Language.Change();">{language}</a></div>
</div>
[/not-logged]
</div>
<div class="clear"></div>
</body>
</html>