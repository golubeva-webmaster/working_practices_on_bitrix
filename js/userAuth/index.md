* Проверить, если такой юзер есть в системе,то авторизовать
```
function EmailAuth(arr){
	try{
		$.ajax({
			type: 'POST',
			url: '/local/templates/main/ajax/user-authorize.php',
			data: arr,
			dataType: 'json',
		}).done(function(res){
			console.table('Email Auth done res: '+res);
			if ("status" in res) {
				if(res.status === 'success') {
					//Перезагрузить стр, чтобы в поля подставились данные авторизованного пользователя
					document.location.reload();
					console.log('Email Auth done res: success');
				}
				else{
					console.log('Email Auth done res: Такого пользователя нет в системе');
				}
			}
		});
	}
	catch(err){
		console.error('%cError Email Auth '+err, 'color:yellow:background:red;');
	}
};
```
