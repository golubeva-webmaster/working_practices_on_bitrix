	if(isOrderPage()) {
		// Оптовикам форма
		$.ajax({
			type: 'POST',
			url: 'basket_get_weight.php',
			data: {},
			dataType: 'json',
			error: function () {
				console.log('error ajax optovik message');
			},
			success: function (res) {
				if(res.weight > 5000000) { // если вес корзины больше 5 т
					 $("#opt_message").trigger('click');
					// Учет цели яндекс метрики
					try {
						dataLayer.push({'event': 'ym_event', 'ym_event_name': 'optovik_show'});
						dataLayer.push({'event': 'ga_virtual_page', 'ga_virtual_page_url': '/optovik_show'});
						setTimeout(function(){
							$('form[name="OPTOVIK"]').submit(function(){
								dataLayer.push({'event': 'ym_event', 'ym_event_name': 'optovik_send'});
								dataLayer.push({'event': 'ga_virtual_page', 'ga_virtual_page_url': '/optovik_send'});
							});
						},300);
					} catch (err) {
						console.log("Ошибка! Счетчиков нет.");
					}
				}
			}
		});
	}
