[record]<div id="wall_recordss_{rec-id}">
<div class="clear" style="height:15px;"></div>
<div class="profile_wall" id="wall_record_{rec-id}">
  <div class="profile_wall_author">
    <img src="{ava}" width="34px;" style="cursor:pointer;" onClick="Page.Go('/u{user-id}'); return false" />
    <a href="/u{user-id}" onClick="Page.Go(this.href); return false">{name}</a>
    <span style="cursor:default;">
    <div style="cursor:pointer; float:left;" onClick="Page.Go('/wall{author-id}_{rec-id}'); return false">{date}{type}</div>
    </span>
    <div id="profile_wall_arrow{rec-id}" class="profile_wall_arrow" onClick="wall.ShowMenu('{rec-id}')"></div>
  </div>
  <div class="clear"></div>
  <div class="profile_wall_text">{text} </div>
  <ul class="profile_bottom">
    <li style="{like_color}" onClick="{like-js-function}" id="wall_like_link{rec-id}">Like</li>
    <li onClick="Repost.Box('{rec-id}'); return false "id="wall_tell_all_{rec-id}">Share</li>
    <li id="like_ico" class="{like_display}" style="float:right; font-family:Roboto Bold; cursor:pointer; color:#3a81ad;" onClick="wall.all_liked_users('{rec-id}', '', '{likes}')">
      <div class="profile_bottom_like"></div>
      <input type="hidden" id="update_like{rec-id}" value="0" />
      <span class="fl_l" id="wall_like_cnt{rec-id}">{likes}</span></li>
  </ul>
  [privacy-comment][comments-link]
  <div class="fl_l" style="float:left; padding:10px; width:415px;" id="fast_form_{rec-id}">
    <div class="wall_fast_texatrea" id="fast_textarea_{rec-id}">
      <textarea id="fast_text_{rec-id}" style="height:17px; width:394px;" placeholder="Write comment.." ></textarea>
      <div class="clear"></div>
      <button style="height:24px; margin-top:7px; float:left;" onClick="wall.fast_send('{rec-id}', '{author-id}'); return false" id="fast_buts_{rec-id}">Send</button>
      <div class="clear"></div>
    </div>
  </div>
  [/comments-link][/privacy-comment]
  <div class="clear"></div>
</div></div>
[/record]
[all-comm]
<div class="cursor_pointer" onClick="wall.all_comments('{rec-id}', '{author-id}'); return false" id="wall_all_but_link_{rec-id}">
  <div class="fl_l all_comments_button" id="wall_all_comm_but_{rec-id}">Show {gram-record-all-comm}</div>
</div>
[/all-comm]
[comment]
<div style="float:left; width:415px; padding:5px 0px; border-top:1px solid#d4dce3; margin-top:-1px; border-bottom:1px solid#d4dce3;" id="wall_fast_comment_{comm-id}">
  <img src="{ava}" onClick="Page.Go('/u{user-id}'); return false" width="34px;" class="fl_l" style="margin-right:5px;" /><a href="/u{user-id}" onClick="Page.Go(this.href); return false" class="fl_l" style="font-family:Roboto Bold; margin-right:5px; margin-top:-2px;">{name}</a>
  <span class="fl_l" style="color:#888; margin-top:-2px;">{date}</span>[owner] <a class="fl_r" style="font-size:11px; font-family:Tahoma;" id="fast_del_{comm-id}" onClick="wall.fast_comm_del('{comm-id}'); return false">Delete</a>
  [/owner]
  <div class="clear"></div>
  <div class="profile_wall_comment_text">{text}</div>
  <div class="clear"></div>
</div>
[/comment]
[comment-form]
<div class="fl_l" style="float:left; margin-top:10px; width:415px;" id="fast_form">
  <textarea id="fast_text_{rec-id}" style="height:17px; width:394px;" placeholder="Write comment.." onClick="$('#fast_buts_{rec-id}, #comment_write_cancel{rec-id}').show();"></textarea>
  <div class="clear"></div>
  <button style="display:none; height:24px; margin-top:7px; float:left;" onClick="wall.fast_send('{rec-id}', '{author-id}'); return false" id="fast_buts_{rec-id}">Send</button>
  <button class="gray_button" style="display:none; margin-top:7px; height:24px; float:right;" onClick="$('#fast_buts_{rec-id}, #comment_write_cancel{rec-id}').hide();" id="comment_write_cancel{rec-id}">Cancel</button>
</div>
<div class="clear" style="height:10px;"></div>
[/comment-form]