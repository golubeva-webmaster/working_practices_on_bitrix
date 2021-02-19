// Событие "Изменение ИНН в форме заказа"
$(document).on("keyup change", "#soa-property-34", function (e) {

	var	props = {
		'14': '',
		'25': '',
		'35': '',
		'36': '',
		'39': '',
		'40': '',
		'71': '',
		'73': '',
		'76': '',
	},
	$val = ($(this).val()).trim();

	arguments.callee.oldval = arguments.callee.oldval || '';
	if(arguments.callee.oldval != $val && ($val.length == 10 || $val.length == 12)) {

		for(let k in props){
			$('input[name="ORDER_PROP_' + k + '"]').val('');
		}
		// Авторизовать, если если есть пользователь с таким ИНН
		EmailAuth({'UF_INN': $val});

		arguments.callee.tmint = arguments.callee.tmint || null;

		clearTimeout(arguments.callee.tmint);

		arguments.callee.tmint = setTimeout(function() {

		// Взять данные из dadata и подставить в форму
			$.ajax({
				type: 'POST',
				url: '/local/templates/main/ajax/get_company.php',
				data: {
					'inn': $val
				},
				dataType: 'json',
			}).done(function (res) {
				try{
					if(typeof res.suggestions[0] == 'object') {

						console.log("%o", res.suggestions[0]);
						console.log('typeof phones: '+ typeof res.suggestions[0].data.phones);
						console.log("%o", res.suggestions[0].data.phones);

						if(res.suggestions[0].data.phones != null){
							props['14'] = res.suggestions[0].data.phones;
						}
						if(res.suggestions[0].data.emails != null){
							props['25'] = res.suggestions[0].data.emails;
						}
						if(res.suggestions[0].data.kpp != null){
							props['35'] = res.suggestions[0].data.kpp;
						}
						if(res.suggestions[0].data.ogrn != null){
							props['36'] = res.suggestions[0].data.ogrn;
						}
						if(res.suggestions[0].data.okpo != null){
							props['39'] = res.suggestions[0].data.okpo;
						}
						if(res.suggestions[0].unrestricted_value != null){
							props['71'] = res.suggestions[0].unrestricted_value;
						}
						if(res.suggestions[0].data.address.unrestricted_value != ''){
							props['76'] = res.suggestions[0].data.address.unrestricted_value;
						}
						if(res.suggestions[0].data.management != null){
							props['40'] = res.suggestions[0].data.management.name;
							props['73'] = res.suggestions[0].data.management.name;
						}
					}

					// переобход полей и отображение пустых
					console.log("%oprops:", props);
					for (let key in props) {
						//console.log('key = ' + key, typeof key, "props[key] = " + props[key]);

						if (props[key] != '') {
							$('input[name="ORDER_PROP_' + key + '"]').val(props[key]);
							$('input[name="ORDER_PROP_' + key + '"]').closest('.bx-soa-customer-field').addClass('hidden');
						}
						else
							$('input[name="ORDER_PROP_' + key + '"]').closest('.bx-soa-customer-field').removeClass('hidden');
					}
				}
				catch(err){
					console.error('%cError custom.js '+err, 'color:red; background:yellow;');
				}
			});
		}, 200);
	}

	arguments.callee.oldval = $val;
});

//mari Проверить, если такой юзер есть в системе,то авторизовать.
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
