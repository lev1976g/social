<script type="text/javascript">
$(document).ready(function(){
	Xajax = new AjaxUpload('upload_ava', {
		action: '/index.php?go=editprofile&act=upload_avatar',
		name: 'uploadfile',
		onSubmit: function (file, ext) {
		Page.Loading('start');
		if (!(ext && /^(jpg|png|jpeg|gif|jpe)$/.test(ext))) {
			ShowInfo('red', lang_bad_format, 3000);
			Page.Loading('stop');
				return false;
			}
		},
		onComplete: function (file, response) {
			Page.Loading('start');
			if(response == 'bad_format'){
				ShowInfo('red', lang_bad_format, 3000);
				Page.Loading('stop');
			} else if(response == 'big_size'){
				ShowInfo('red', lang_bad_size, 3000);
				Page.Loading('stop');
			} else if(response == 'bad'){
				ShowInfo('red', lang_bad_aaa, 3000);
				Page.Loading('stop');
			} else {
				$('#ava').html('<img src="'+response+'" />');
				$('html').animate({scrollTop: 0}, 250);
				$('#ava_delete_but').show();
				Page.Loading('stop');
			}
		}
	});
});
</script>
<!-- start PROFILE LEFT BLOCKS -->

<div class="profile_left">
  <div class="profile_avatar">
    <span id="ava"><img src="{ava}" id="ava_{user-id}" /></span>
    <div class="clear"></div>
    [not-owner][blacklist]
    <div style="padding:10px;"> [privacy-msg]
      <button style="width:180px; margin-bottom:10px;" onClick="Messages.Write({user-id}, '{name} {lastname}'); return false">Send a message</button>
      [/privacy-msg]
      <div class="clear"></div>
      [no-friends]
      <button style="width:180px;" onClick="friends.add({user-id}); return false">Add as Friend</button>
      [/no-friends]
      [yes-friends]
      <div class="clear"></div>
      <span style="width:170px; cursor:default; float:left; padding:12px 5px; text-align:center; color:#8f99a2;"><strong>{name}</strong> you friend</span> [/yes-friends]
      <div class="clear"></div>
    </div>
    [/blacklist]
    [/not-owner]
    <div class="clear"></div>
  </div>
  [owner]
  <div class="clear" style="height:15px;"></div>
  <ul class="avatar_menu">
    <li onClick="Page.Go('/editprofile'); return false;">Edit profile</li>
    <li id="upload_ava">Change avatar</li>
    <li onClick="Avatar.Delete('{ava}'); return false;" id="ava_delete_but" {display-ava}>Delete this avatar</li>
  </ul>
  [/owner]
  [blacklist]
  <div class="clear" style="height:15px;"></div>
  <ul class="avatar_menu">
    <li onClick="subscriptions.all('{user-id}', '', '{following-num}')" class="{following_display}"> Following <span class="fl_r">{following-num}</span>
    </li>
    <li onClick="subscriptions.followers('{user-id}', '', '{followers-num}')" class="{followers_display}" id="followers_block"> Followers <span class="fl_r" id="followers_num">{followers-num}</span>
    </li>
  </ul>
  [gifts]
  <div class="clear" style="height:15px;"></div>
  <div class="profile_friends">
    <div class="profile_friends_title" onClick="Page.Go('/gifts{user-id}'); return false"> Gifts <span>{gifts-text}</span>
    </div>
    <div class="clear"></div>
    <div style="padding:10px; padding-bottom:5px; padding-left:0px;" align="center">{gifts}</div>
    <div class="clear"></div>
    [not-owner]
    <div style="padding:10px; padding-top:0px;">
      <button style="width:180px;" class="gray_button" onClick="gifts.box('{user-id}', '{balance}'); return false">Send a gift</button>
    </div>
    <div class="clear"></div>
    [/not-owner] </div>
  [/gifts]
  [/blacklist]
  [owner]
  <div class="clear" style="height:15px;"></div>
  <div class="profile_links">
    <a href="/about" onClick="Page.Go(this.href); return false">About</a>
    <a href="/terms" onClick="Page.Go(this.href); return false">Terms</a>
    <a href="/help" onClick="Page.Go(this.href); return false">Help</a>
    <a href="/jobs" onClick="Page.Go(this.href); return false">Jobs</a>
    <a href="/developers" onClick="Page.Go(this.href); return false">Developers</a>
    <div class="clear" style="height:5px;"></div>
    <a style="color:#999; cursor:default;">SocialCOM © 2014  ·</a>
    <a style="margin-left:0px;" onClick="Language.Change();">{language}</a>
  </div>
  [/owner]
  [not-owner]
  <div class="clear" style="height:15px;"></div>
  <ul class="avatar_menu">
    [blacklist]  [yes-friends]
    <li onClick="friends.delet({user-id}, 1); return false"> Remove from friends </li>
    [/yes-friends]
    [no-subscription]
    <li onClick="subscriptions.add({user-id}); return false" id="lnk_unsubscription">
      <span id="text_add_subscription">Follow</span>
    </li>
    [/no-subscription]
    [yes-subscription]
    <li onClick="subscriptions.del({user-id}); return false" id="lnk_unsubscription">
      <span id="text_add_subscription">Unfollow</span>
    </li>
    [/yes-subscription]
    [no-gifts]
    <li onClick="gifts.box('{user-id}', '{balance}'); return false"> Send a  gift </li>
    [/no-gifts]
    [/blacklist]
    [no-fave]
    <li onClick="fave.add({user-id}); return false" id="addfave_but">
      <span id="text_add_fave">Add to fave</span>
    </li>
    [/no-fave]
    [yes-fave]
    <li onClick="fave.delet({user-id}); return false" id="addfave_but">
      <span id="text_add_fave">Delete from fave</span>
    </li>
    [/yes-fave]
    [no-blacklist]
    <li onClick="settings.addblacklist({user-id}, 1); return false" id="addblacklist_but">
      <span id="text_add_blacklist">Add to blacklist</span>
    </li>
    [/no-blacklist]
    [yes-blacklist]
    <li onClick="settings.delblacklist({user-id}, 1); return false" id="addblacklist_but">
      <span id="text_add_blacklist">Remove from blacklist</span>
    </li>
    [/yes-blacklist]
  </ul>
  [/not-owner] </div>
<!-- end PROFILE LEFT BLOCKS -->
<!-- start PROFILE RIGHT BLOCKS -->
<div class="profile_right">
  <!-- start USER INFO BLOCK -->
  <div class="user_info">
    <div class="user_info_title">{name} {lastname} [blacklist]{online} [/blacklist]</div>
    <div class="clear"></div>
    [blacklist]
    <ul class="user_info_list">
      [privacy-info]
      <div style="width:310px; float:left;"> [not-all-birthday]
        <div id="p_birthday_ico"></div>
        <li>Was born <span>{birth-day}</span></li>
        [/not-all-birthday]
        [not-all-country]
        <div id="p_home_ico"></div>
        <li>Lives in <span>{city}, Country</span></li>
        [/not-all-country]
        [not-work]
        <div id="p_work_ico"></div>
        <li>Work <span>{work}</span></li>
        [/not-work] </div>
      <div style="width:310px; float:left;"> [not-mobile]
        <div id="p_mob_ico"></div>
        <li>Mobile number: <span>{mobile}</span></li>
        [/not-mobile]
        [not-skype]
        <div id="p_skype_ico"></div>
        <li>Skype: <span>{skype}</span></li>
        [/not-skype]
        [not-twitter]
        <div id="p_twitter_ico"></div>
        <li>Twitter: <span>{twitter}</span></li>
        [/not-twitter] </div>
      [/privacy-info]
    </ul>
    [/blacklist]
    [not-blacklist]
    <div class="red_error" style="margin:0px;">User <strong> {name} </strong>, added you to the blacklist.</div>
    [/not-blacklist] </div>
  <!-- end USER INFO BLOCK -->
  <div class="clear" style="height:15px;"></div>
  <!-- start WALL BLOCK -->
  <div style="float:left; width:430px;"> [privacy-wall][blacklist]
    <div class="profile_wall">
      <span id="wall_rec_num" style="display:none;">{wall-rec-num}</span>
      <div class="profile_wall_text">
        <textarea  id="wall_text" placeholder="Have something new, {name}?"></textarea>
        <div id="attach_files" class="no_display" style="margin:10px 0px;"></div>
        <div class="clear"></div>
        <input id="vaLattach_files" type="hidden" />
        <div class="attach_photo" onClick="wall.attach_addphoto()" onMouseOver="myhtml.title('photo', 'Attach photo', 'attach_', '-2')" id="attach_photo"></div>
        <div class="attach_video" onClick="wall.attach_addvideo()" onMouseOver="myhtml.title('video', 'Attach video', 'attach_', '-2')" id="attach_video"></div>
        <button onClick="wall.send(); return false" id="wall_send">Send</button>
      </div>
    </div>
    [/blacklist]
    [/privacy-wall]
    [blacklist]
    <div id="wall_records" style="float:left; width:430px;">{records}[no-records]
      <div class="fl_l" style="padding:15px 10px; margin-top:15px; background-color:#fff; width:410px; border-radius:4px; text-align:center; color:#869ba7; font-size:12px;">На стене пока нет ни одной записи.</div>
      [/no-records]</div>
    [wall-link]<span id="wall_all_record"></span>
    <div class="clear" style="height:15px;"></div>
    <div onClick="wall.page('{user-id}'); return false" id="wall_l_href" class="cursor_pointer">
      <div class="fl_l" style="padding:15px 10px; background-color:#fff; width:410px; border-radius:4px; text-align:center; color:#869ba7; font-size:16px;" id="wall_link">к предыдущим записям</div>
    </div>
    [/wall-link] [/blacklist] </div>
  <!-- end WALL BLOCK -->
  <!-- start RIGHT BLOCKS --> 
  [blacklist]
  <div style="float:right; width:200px;"> [friends]
    <div class="profile_friends">
      <div class="profile_friends_title" onClick="Page.Go('/friends/{user-id}'); return false"> Friends <span>{friends-num}</span>
      </div>
      <div class="clear"></div>
      {friends}
      <div class="clear"></div>
    </div>
    <div class="clear" style="height:15px;"></div>
    [/friends]
    [groups]
    <div class="profile_friends">
      <div class="profile_friends_title" onClick="groups.all_publics('{user-id}', '', '{groups-num}')"> Groups <span>{groups-num}</span>
      </div>
      <div class="clear"></div>
      {groups}
      <div class="clear"></div>
    </div>
    <div class="clear" style="height:15px;"></div>
    [/groups]
    [photos]
    <div class="profile_photos">
      <div class="profile_photos_title" onClick="Page.Go('/albums/{user-id}'); return false"> Photos <span>{photos-num}</span>
      </div>
      <div class="clear"></div>
      {photos} </div>
    <div class="clear" style="height:15px;"></div>
    [/photos]
    [videos]
    <div class="profile_videos">
      <div class="profile_videos_title" onClick="Page.Go('/videos/{user-id}'); return false"> Videos <span>{videos-num}</span>
      </div>
      <div class="clear"></div>
      {videos}
      <div class="clear"></div>
    </div>
    <div class="clear" style="height:15px;"></div>
    [/videos]
    [not-owner]
    <div class="profile_links">
      <a href="/about" onClick="Page.Go(this.href); return false">About</a>
      <a href="/terms" onClick="Page.Go(this.href); return false">Terms</a>
      <a href="/help" onClick="Page.Go(this.href); return false">Help</a>
      <a href="/jobs" onClick="Page.Go(this.href); return false">Jobs</a>
      <a href="/developers" onClick="Page.Go(this.href); return false">Developers</a>
      <div class="clear" style="height:5px;"></div>
      <a style="color:#999; cursor:default;">SocialCOM © 2014  ·</a>
      <a style="margin-left:0px;" onClick="Language.Change();">{language}</a>
    </div>
    [/not-owner]
    <div class="clear"></div>
  </div>
  [/blacklist]<!-- end RIGHT BLOCKS -->
</div>
