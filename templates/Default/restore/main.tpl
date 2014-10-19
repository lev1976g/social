<div class="signup_content" align="center">
  <div id="step1">
    <div class="signup_h1"> Please select <font style="font-family: Roboto Regular"> E-mail </font>, which you used to access the site</div>
    <div class="clear" style="height:15px;"></div>
    <div class="signup_form">
      <input type="text" id="email" placeholder="Email adress:" maxlength="50" />
      <button id="restore_button" onClick="Restore.Next();">Next step</button>
    </div>
    <div class="clear" style="height:0px;"></div>
  </div>
  <div id="step2" style="display:none;">
    <div class="signup_h1"> Upon request, found one account. This is your account?</div>
    <div class="clear" style="height:15px;"></div>
    <div class="signup_form">
      <img style="margin:10px auto;" id="user_avatar" />
      <div style="font-family:Roboto Medium; font-size:22px; margin-bottom:15px;"><a style="text-decoration:none; cursor:default;" id="user_name"></a>
      </div>
      <button id="restore_button2" onClick="Restore.Send();">Yes, it's my account</button>
    </div>
    <div class="clear" style="height:0px;"></div>
  </div>
  <div id="step3" style="display:none;">
    <div class="signup_h1">Your <font style="font-family: Roboto Regular"> E-mail </font> address were sent instructions on how to reset your password </div>
    <div class="clear" style="height:15px;"></div>
    <div class="signup_form">
      <img src="{theme}/images/restore_finish.png" style="margin-bottom:30px;" id="user_avatar" />
      <button id="restore_button" onClick="Box.Close('restore_box');">Close Box</button>
    </div>
    <div class="clear" style="height:0px;"></div>
  </div>
</div>
<div class="signup_footer"> Bethink password? <a onClick="Box.Close('restore_box');">Sign In »</a>
</div>
