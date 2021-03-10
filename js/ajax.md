# Ajax запрос на примере получения данных о заказе по его id
```
	// По id заказа достанем price и id товаров
	let urlPar = getParamsFromUrl();
	if(urlPar['ORDER_ID']){
		let dataOrder = {'orderId': urlPar['ORDER_ID']};
		console.log('Посылаю запрос на получение данных заказа ',dataOrder)
		try{
			$.ajax({
				type: 'POST',
				url: '/local/templates/main/ajax/order-get-price-and-products.php',
				data: dataOrder,
				dataType: 'json',
				statusCode: {
					404: function () { // выполнить функцию если код ответа HTTP 404
						console.log("statusCode: страница не найдена");
					},
					403: function () { // выполнить функцию если код ответа HTTP 403
						console.log("statusCode: доступ запрещен");
					},
					200: function () { // выполнить функцию если код ответа HTTP 200
						console.log("statusCode: Ok");
					}
				},
				success: function(data, textStatus, jqXHR){
					console.group('success');
					//let res = JSON.parse(data);
					let res = data;					
					if ("status" in res) {
						if(res.status === 'success') {
							dataLayer.push({
								'google_tag_params': {
									'ecomm_prodid' : res.id,
									'ecomm_pagetype': 'purchase',
									'ecomm_totalvalue' : res.price
								}
							});
						}
						else{
							console.log('done status error');
						}
					}
					console.groupEnd();
				},
				error: function(jqXHR, textStatus, errorThrown ){
					console.group('ajax error');
					console.log('textStatus: '+textStatus);
					console.log("errorThrown %o", errorThrown);
					console.log('jqXHR %o',jqXHR);
					console.groupEnd();
				}				
			})
			.done(function(data){
				console.log('done');
				console.log('data',data);
			});
		}
		catch(err){
			console.error('%cError не удалось отправить запрос на получение price & products_id для заказа '+urlPar['ORDER_ID']+' '+err, 'color:yellow:background:red;');
		}
	}
```
