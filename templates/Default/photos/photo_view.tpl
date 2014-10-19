<div id="photo_view_{id}" class="photo_box" onClick="Photo.setEvent(event, '{close-link}')">
  <div class="photo_close" onClick="Photo.Close('{close-link}'); return false;"></div>
  <div class="photo_box_position">
    <div class="photo_box_image">
      <div class="photo_box_image_title">[all]Фотография {jid} из {photo-num}[/all][wall]Просмотр фотографии[/wall] </div>
      [all]<a href="/photo{uid}_{prev-id}{section}" onClick="Photo.Show(this.href); return false">
      <div class="photo_box_image_prev"></div>
      </a>
      <a href="/photo{uid}_{next-id}{section}" onClick="Photo.Show(this.href); return false">
      <div class="photo_box_image_next"></div>
      </a>
      [/all]
      <ul class="photo_box_menu">
        [owner]
        <li onClick="Photo.EditBox({id}, 0); return false"> Редактировать фотографию </li>
        <li style="padding:10px 0px; cursor:default;">|</li>
        <li onClick="Photo.MsgDelete({id}, {aid}, 1); return false"> Удалить фотографию </li>
        [/owner]
        <li style="padding:10px 0px; cursor:default;">|</li>
        <li onClick="Report.Box('photo', '{id}')"> Пожаловаться на фотаграфию </li>
      </ul>
      <img id="ladybug_ant{id}" src="{photo}" />
      <div class="clear"></div>
    </div>
    <div id="pinfo_{id}" class="photo_box_info">
      <div class="photo_box_info_author">
        <img src="{author_ava}" />
        <div style="float:left; width:179px;"><a href="/u{uid}" style="font-family:Roboto Bold; line-height:14px; font-size:14px;" onClick="Page.Go(this.href); return false">{author}</a>
          <div class="photo_box_info_date">{date}</div>
          <div class="clear"></div>
        </div>
        <div class="clear"></div>
        <div class="photo_box_info_desc" id="photo_descr_{id}">{descr}</div>
      </div> [all-comm]<a href="/" onClick="comments.all({id}, {num}); return false" id="all_href_lnk_comm_{id}">
        <div class="photo_box_all_comm" id="all_lnk_comm_{id}">Показат все комменты</div>
        </a>
        <span id="all_comments_{id}"></span>[/all-comm]
      <div class="photo_box_info_comments"> <span id="comments_{id}">{comments}</span>
        <div class="clear"></div>
      </div>
      [add-comm] <a class="photo_box_info_comments_h5"></a>
      <input type="text" id="textcom_{id}" placeholder="Ваш комментарий.." style="width:204px; margin:10px 0px; height:14px;" />
      <button id="add_comm" onClick="comments.add({id}); return false" style="height:28px;">Отправить</button>
      [/add-comm] </div>
    <div class="clear"></div>
  </div>
</div>
