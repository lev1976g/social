var reg = {
	send: function(){
		var name = $('#name').val();
		var lastname = $('#lastname').val();
		var sex = $("#sex").val();
		var day = $("#day").val();
		var month = $("#month").val();
		var year = $("#year").val();
		var country = $("#country").val();
		var city = $("#city").val();
		var email = $('#email').val();
		var new_pass = $('#new_pass').val();
		var new_pass2 = $('#new_pass2').val();
		$.post('/index.php?go=signup', {
				name: name,
				lastname: lastname,
				sex: sex,
				day: day,
				month: month,
				year: year,
				country: country,
				city: city,
				email: email,
				password_first: new_pass,
				password_second: new_pass2,
			}, function(d){
			var exp = d.split('|');
			if(exp[0] == 'ok'){
				window.location = '/u'+exp[1];
			} else if(exp[0] == 'err_mail'){
				$('#err2').show().html('Пользователь с таким E-Mail адресом уже зарегистрирован.');
				Box.Close('sec_code');
			} else {
				Box.Info('boxerr', 'Ошибка', 'Неизвестная ошибка', 300);
				Box.Close('sec_code');
			}
		});
	}
}

//RESTORE
var Restore = {
	Start: function(){
        Page.Loading('start');
        $.post('/index.php?go=restore', function(data){
            Box.Show('restore_box', 700, 'Restore account password', data, false);
			$('.box_content').css('max-height', '700px');
            Page.Loading('stop');
        });
    },
	Next: function(){
		var user_email = $('#email').val();
		butloading('restore_button', 'auto', 'disabled', '');
		if(user_email != 0 && isValidEmailAddress(user_email)){
			$.post('/index.php?go=restore&act=next', {user_email: user_email}, function(data){
				if(data == 'no_user'){
					ShowInfo('red', 'Пользователь с адресом: <b>'+user_email+'</b> не найден.<br />Пожалуйста, убедитесь, что правильно ввели E-mail', 7000);
					butloading('restore_button', 'auto', 'enabled', 'Следующий шаг');
				} else {
					var exp = data.split('|');
					$('#step1').hide();
					$('#step2').show();
					$('#user_avatar').attr('src', exp[1]);
					$('#name').html(exp[0]);
				}
			});
		} else {
			ShowInfo('red', 'Введен не правильный формат E-mail адреса!', 3000);
			butloading('restore_button', 'auto', 'enabled', 'Следующий шаг');
		}
	},
	Send: function(){
		var email = $('#email').val();
		butloading('restore_button2', 'auto', 'disabled', '');
		$.post('/index.php?go=restore&act=send', {email: email}, function(d){
			$('#step2').hide();
			$('#step3').show();
		});
	},
	Finish: function(){
		var new_pass = $('#new_pass').val();
		var new_pass2 = $('#new_pass2').val();
		var hash = $('#hash').val();
		if(new_pass != 0 && new_pass != 'Новый пароль'){
			if(new_pass2 != 0 && new_pass2 != 'Повторите еще раз новый пароль'){
				if(new_pass == new_pass2){
					if(new_pass.length >= 6){
						$('#err').hide();
						butloading('send', '43', 'disabled', '');
						$.post('/index.php?go=restore&act=finish', {new_pass: new_pass, new_pass2: new_pass2, hash: hash}, function(d){
							$('#step1').hide();
							$('#step2').show();
						});
					} else
						$('#err').show().html('Длина пароля должна быть не менее 6 символов.');
				} else
					$('#err').show().html('Оба введенных пароля должны быть идентичны.');
			} else
				setErrorInputMsg('new_pass2');
		} else
			setErrorInputMsg('new_pass');
	}
}

function isValidName(xname){
	var pattern = new RegExp(/^[a-zA-Zа-яА-Я]+$/);
 	return pattern.test(xname);
}
function isValidEmailAddress(emailAddress) {
 	var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
 	return pattern.test(emailAddress);
}