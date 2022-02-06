# Интеграция в интернет-магазин на битриксе системы Сбербанк Кредит
## План:
 - По нажатию на кнопку авторизации "Авторизоваться в Сбер Бизнесс" переходим по ссылке авторизации
 - Авторизирумся в СберБизнес
 - После авторизации СберБизнес вернет на сайт, указанный в редиректе. В параметрах URL будет добавлен code – код авторизации, который мы используем на следующем шаге 
 - Формируем HTTP запрос на получение токена GET (old POST) https://edupir.testsbi.sberbank.ru:9443/ic/sso/api/v1/oauth/token
 - Формируем HTTP запрос на получение данных пользователя GET https://edupir.testsbi.sberbank.ru:9443/ic/sso/api/v1/oauth/user-info
 - Формируем HTTP запрос на получение кредитных предложений GET https://edupirfintech.sberbank.ru:9443/fintech/api/v1/credit-offers?params_list
 - Полученные данные загружаем в модальное окно Сббол creditInBasket-sdk.js
 - По нажатию «Оформить заявку» формируется запрос на создание кредитного предложения
 - Формируем HTTP запрос создание кредитной заявки POST https://edupirfintech.sberbank.ru:9443/fintech/api/v1/credit-requests
 - В случае положительного запроса (получаем код ответа 200) переводим клиента по ссылке на созданное кредитное предложение: https://edupir.testsbi.sberbank.ru:9443/ic/dcb/index.html#/credits/credit-financing/credit-partners?params_list
 - На крон вешаем запрос с проверкой состояния заказа GET https://fintech.sberbank.ru:9443/fintech/api/v1/payments/orders_GUID/state 

<img src="https://github.com/golubeva-webmaster/Portfolio/blob/main/img/order_sber.jpg" alt="order sber" width="50%" height="50%">

