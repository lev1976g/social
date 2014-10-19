<div class="photo_box_comments" id="comment_{id}">
  <img src="{ava}" />
  <div style="float:left;width:179px">
    <a href="/u{uid}" style="font-family:Roboto Bold;" onClick="Page.Go(this.href); return false">{author}</a>
    <div class="clear"></div>
    <div class="photo_box_comment_text">{comment}</div>
    <div class="photo_box_info_date">{date}[owner]&nbsp;|&nbsp;
      <a href="/" onClick="comments.delet({id}, '{hash}'); return false" id="del_but_{id}">Удалить</a>
      [/owner]</div>
  </div>
  <div class="clear"></div>
</div>
<div class="clear"></div>
