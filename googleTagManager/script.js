/*
Задача.
На различных страницах сайта сделать выборку для google tag manager
и добавить ее в объект dataLayer
*/
var dataLayer = window.dataLayer || [];
let currentPageGTM = document.location.pathname;

if (currentPageGTM === '/') { //  главная страница
//	console.log('%cглавная страница', 'color:blue');
	dataLayer.push({
		'google_tag_params': {
			'ecomm_pagetype': 'home'
		}
	});
} else if (document.querySelector('meta[itemprop="sku"]')) {    // страница карточки товара
	//console.log('%cстраница карточки товара', 'color:blue');
	dataLayer.push({
		'google_tag_params': {
			'ecomm_prodid': document.querySelector('meta[itemprop="sku"]').getAttribute('content'),
			'ecomm_pagetype': 'product',
			'ecomm_totalvalue': document.querySelector('meta[itemprop="price"]').getAttribute('content'),
		}
	});
} else if (currentPageGTM.includes('/catalog/') && currentPageGTM.length > 9) { 		// страница категорий
	//console.log('%cстраница категорий', 'color:blue');
	let arCategoryTmp = [];
	document.querySelectorAll('span[data-param-form_id="fast_view"]').forEach(function (item, index, arr) {
		arCategoryTmp.push(item.getAttribute('data-param-id'));
	});
	dataLayer.push({
		'google_tag_params': {
			'ecomm_prodid': arCategoryTmp,
			'ecomm_pagetype': 'category'
		}
	});
} else if (currentPageGTM.includes('/catalog/') && document.location.search.includes('?q=')) {	// страница результата поиска
	let test = [];
	//console.log('%cстраница результата поиска', 'color:blue');
	document.querySelectorAll('span[data-param-form_id="fast_view"]').forEach(function (item, index, arr) {
		test.push(item.getAttribute('data-param-id'));
	});
	dataLayer.push({
		'google_tag_params': {
			'ecomm_prodid': test,
			'ecomm_pagetype': 'searchresults'
		}
	});
} else if (currentPageGTM.includes('/basket/')) { 		// страница корзины
	//console.log('%cстраница корзины', 'color:blue');
	try{
		$.ajax({
			type: 'POST',
			url: '/local/templates/main/ajax/basket-get-items.php',
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
				// console.group('success basket');
				// console.log(data);
			
				dataLayer.push({
					'google_tag_params': {
						'ecomm_prodid': data.id,
						'ecomm_pagetype': 'cart',
						'ecomm_totalvalue': data.totalSumm
					}
				});
				//console.groupEnd();
			},
			error: function(jqXHR, textStatus, errorThrown ){
				// console.group('ajax error basket');
				// console.log('textStatus: '+textStatus);
				// console.log("errorThrown %o", errorThrown);
				// console.log('jqXHR %o',jqXHR);
				// console.groupEnd();
			}				
		});
	}
	catch(err){
		console.error('%cError не удалось отправить запрос на получение price & products_id для заказа '+urlPar['ORDER_ID']+' '+err, 'color:yellow:background:red;');
	}
}
else if(currentPageGTM.includes('/order/') && document.location.search.includes('?ORDER_ID=')){ // страница "спасибо" после покупки
	// По id заказа достанем price и id товаров
	let urlPar = getParamsFromUrl();
	if(urlPar['ORDER_ID']){
		let dataOrder = {'orderId': urlPar['ORDER_ID']};
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
					//console.group('success');
					dataLayer.push({
						'google_tag_params': {
							'ecomm_prodid' : data.id,
							'ecomm_pagetype': 'purchase',
							'ecomm_totalvalue' : data.price
						}
					});

					//console.groupEnd();
				},
				error: function(jqXHR, textStatus, errorThrown ){
					console.group('ajax error');
					// console.log('textStatus: '+textStatus);
					// console.log("errorThrown %o", errorThrown);
					// console.log('jqXHR %o',jqXHR);
					// console.groupEnd();
				}				
			});
		}
		catch(err){
			console.error('%cError не удалось отправить запрос на получение price & products_id для заказа '+urlPar['ORDER_ID']+' '+err, 'color:yellow:background:red;');
		}
	}
}
else {
	//console.log('%cстраница OTHER', 'color:blue');
	dataLayer.push({
		'google_tag_params': {
			'ecomm_pagetype': 'other'
		}
	});
}
