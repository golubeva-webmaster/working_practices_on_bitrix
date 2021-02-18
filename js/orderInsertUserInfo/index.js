/** Возвращает true, если пользователь находится на странице оформления заказа */
function isOrderPage() {
	return (document.location.pathname =='/order/') ? true : false;
}
  
if(isOrderPage()){
		// Подстановка данных физ и юр лиц на странице оформления заказа

		$.ajax({
			type: 'POST',
			url: 'user-get-info.php',
			data: '',
			dataType: 'json',
		}).done(function(res){
			
			if ("status" in res) {
				if(res.status === 'success') {
					//console.log("Пользователь авторизован, данные взялись из системы %o", res);
					$('input[name="ORDER_PROP_64"]').val(res.fio); // FIO fiz
					$('input[name="ORDER_PROP_67"]').val(res.address); // Address fiz
					$('input[name="ORDER_PROP_76"]').val(res.address); // Address ur
					$('input[name="ORDER_PROP_71"]').val(res.companyName); // companyName ur
					$('input[name="ORDER_PROP_73"]').val(res.contactFace); // contactFace ur
					$('input[name="ORDER_PROP_36"]').val(res.ogrn);
					$('input[name="ORDER_PROP_39"]').val(res.okpo);
					$('input[name="ORDER_PROP_34"]').val(res.inn);
					$('input[name="ORDER_PROP_35"]').val(res.kpp);
					$('input[name="ORDER_PROP_40"]').val(res.companyRuk);
				}
				else console.log("Пользователь не авторизован %o", res);
			}
		});
	}
