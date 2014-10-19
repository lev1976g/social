<div id="comment_all_{id}">
  <div class="friend_list">
    <div class="friend_list_ava">
      <a href="/u{uid}" onClick="Page.Go(this.href); return false">
      <img src="{ava}" alt="" title="" />
      </a>
    </div>
    <a href="/u{uid}" style="font-size:14px;" onClick="Page.Go(this.href); return false"><strong>{author}</strong></a>
    <div class="fl_r" style="line-height:0px;"><a href="/photo{user-id}_{pid}{aid}_sec={section}" onClick="Photo.Show(this.href); return false"><img width="155px;" src="{photo}" alt="" /></a>
    </div>
    <div style="margin-top:5px;"></div>
    {comment}
    <div style="margin-top:5px;"></div>
    <font style="color:#999;">{date} </font>[owner]&nbsp;|&nbsp;
    <a href="/" onClick="comments.delet_page_comm({id}, '{hash}'); return false" id="full_del_but_{id}">Удалить</a>
    [/owner] </div>
</div>
<div class="clear"></div>
