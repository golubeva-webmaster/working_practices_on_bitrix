//Вывод Согласия с условиями офферты для физ или юр лица при оформлении заказа

$(document).on('click', 'input[name="PERSON_TYPE"]',
	function(){

		var usertext = '';
		$('.usertype_license').remove();
		if($(this).val() == 1)
			usertext = '<a href="/pages/offer_fiz.php" target="_blank">условия оферты</a> для физического лица';
		else if($(this).val() == 2)
			usertext = '<a href="/pages/offer_ur.php" target="_blank">условия оферты</a> для юридического лица';
		else
			usertext = '';

		$('<p class="usertype_license">'+usertext+'</p>').insertBefore($('.user_type_metka'));
}
)
