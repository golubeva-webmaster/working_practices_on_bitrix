console.log('1');

// VARIABLES
var sber_url = 'https://xxxxx',
    //sber_url_prom = 'https://xxxxx';
    client_id = 11111;

console.log('client_id = '+client_id);
/* sber test user User370-00-01 / Qwerty54321 / sms: 11111 */

currentUrl = document.location.href.split('?')[0];

// разбиваем адресную строку на массив параметров
var params = window
    .location
    .search
    .replace('?','')
    .split('&')
    .reduce(
        function(p,e){
            var a = e.split('=');
            p[ decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
            return p;
        },
        {}
    );

if(params['login'] === 'yes'){
    getErrorMessage();
}

if(localStorage.getItem('sber_token')) {
    colorAuthIcon();
    if(document.location.pathname =='/order/'){
        if(params['ORDER_ID']){ // Страница после оформления заказа
            // STEP 5 Отправка запроса на кредитную заявку после оформления заказа
            if(localStorage.getItem("sber_credit"))
                createSbbolCreditOrder(params['ORDER_ID']);
        }
        else{
            getSberUserInfo();
        }
    }
}

// STEP 1 Получение одноразового параметра code

$(document).on('click', '.sberbank_button_auth', function() {
    console.log('STEP 1 Получение одноразового параметра code');
    let url = sber_url + '/ic/sso/api/v1/oauth/authorize?' + //ok
        'redirect_uri=' + encodeURIComponent(currentUrl) + // закодируем
        '&scope=openid+Vneshniy_VOLMA' +
        '&nonce=0050569e-6881-1eeb-8cfa-9df2734c3750&state=0050569e-6881-1eeb-8cfa-9df2734c5750' +
        '&response_type=code' +
        '&client_id='+client_id;

    document.location.href = url;
});

// STEP 2 Получение токена авторизации

if(params['code'] !== undefined && !localStorage.getItem('sber_token')) {
    console.group("STEP 2 Получение токена авторизации");
    console.log('Сбер вернул одноразовый code = ' + params['code']);

    const body = {
        "grant_type": "authorization_code",
        "redirect_uri": encodeURIComponent(currentUrl),
        "client_id": client_id,
        "client_secret": "FxqeNR8g",
        "code": params['code']
    }
    console.log('Отправляем в запрос на токен body:');
    console.table(body);
    $.ajax({
        type: 'POST',
        url: '/local/templates/main/ajax/sb-get-token.php',
        data: JSON.stringify(body),
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
            console.group('STEP 2 success {');
            console.table(data);
            let result = JSON.parse(data);

            if(data.includes('access_token')){
                console.log("access_token: ", result['access_token']); // токен авторизации
                localStorage.removeItem('sber_token');
                localStorage.setItem('sber_token', result['access_token']);

                colorAuthIcon();

                //вызвать окно с сообщением об авторизации через Сбер Бизнес
                document.getElementById('succes-sber-auth-link').click();

                if(document.location.pathname =='/order/') {
                    //выбрать Юрлицо
                    console.log('выбрать Юрлицо кликом после получения токена авторизации');
                    document.querySelector('input[name="PERSON_TYPE"][value="2"]').click();

                    getSberUserInfo();
                }
            }
            else
                console.log('else: access_token не получен');

            console.groupEnd();
        },
        error: function(jqXHR, textStatus, errorThrown ){
            console.log('STEP 2 error {');
            console.log('textStatus: '+textStatus);
            console.log("errorThrown %o", errorThrown);
            console.log('STEP 2 error }');
            //getErrorMessage();
            //вызвать окно с сообщением о НЕудачной авторизации через Сбер Бизнес
            document.getElementById('error-sber-auth-link').click();
        }
    })
    console.groupEnd();
}

// STEP 3 Получение данных пользователя

function getSberUserInfo(){

    console.group("STEP 3 Получение данных пользователя");

    $.ajax({
        type: 'GET',
        url: '/local/templates/main/ajax/sb-get-user-data.php',
        data: JSON.stringify({"token_type": 'Bearer', "token": localStorage.getItem('sber_token')}),
        dataType: 'json',
        success: function (data, textStatus, jqXHR) {
            console.log('STEP 3 %csuccess {', 'background-color:blue; color:white');
            console.log('textStatus: ' + textStatus);
            console.log("data %o", data); // то что возвращает

            //TODO подставить полученные данные в поля оформления заказа: ИНН data.inn, и вызвать авто заполение
            console.log('data.inn = '+ data.inn);
            console.log('Временно подставляю реальный ИНН = 3447004459');
            data.inn = '3447004459'; //TODO закомментировать на боевом. Это подстановка реального ИНН, вместо тестового

            fromDadataToOrderPage(data.inn);

            //$(document).on("keyup change", "#soa-property-34"


            getCreditOffers(data.orgLawFormShort);

            console.log('STEP 3 success }');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('STEP 3 error {');
            console.log('textStatus: ' + textStatus);
            console.log("errorThrown %o", errorThrown);
            console.log('STEP 3 error }');
            getErrorMessage(); // удалим переменные из localStorage
        }
    })
    console.groupEnd();
}

// STEP 4 Получаем кредитные предложения для пользователя.

function getCreditOffers(orgLawFormShort){

    console.group("STEP 4 Получаем кредитные предложения для пользователя");

    $.ajax({
        type: 'GET',
        url: '/local/templates/main/ajax/sb-get-credit-offers.php',
        data: JSON.stringify({
                        "token_type": 'Bearer',
                        "token": localStorage.getItem('sber_token'),
                        "lawForm": orgLawFormShort}),
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
        success: function (data, textStatus, jqXHR) {
            console.log('STEP 4 %csuccess {', 'background-color:blue; color:white');
            console.log("data %o", data); // то что возвращает
            transferDataToModalWindow(data);
            console.log('STEP 4 %csuccess }');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('STEP 4 error {');
            console.log('textStatus: ' + textStatus);
            console.log("errorThrown %o", errorThrown);
            console.log('STEP 4 error }');
        }
    })
    console.groupEnd();
}

function getErrorMessage(){
    localStorage.removeItem('sber_token');
    //localStorage.removeItem('sber_inn'); //TODO это нужно было, когда этот скрипт не видел DOMa, потом это может быть не нужным
}


// Подстановка параметров во всплывающее окно -------------------------

function transferDataToModalWindow(data) {
    console.group('fn transferDataToModalWindow');

    // TODO пока подменяю вопросы, т.к. для тестового юзера они приходят пустыми, а в скрипте не стоит проверка на null
    data[0]['questions'] =  [
        {
            question: 'Как мне оплатить счёт кредитными деньгами?',
            answer: 'Подайте заявку онлайн, после одобрения подпишите кредитный договор электронной подписью и нажмите кнопку «Оплатить».'
        }
    ];
    data[1]['questions'] =  [
        {
            question: 'Как мне оплатить счёт кредитными деньгами?',
            answer: 'Подайте заявку онлайн, после одобрения подпишите кредитный договор электронной подписью и нажмите кнопку «Оплатить».'
        }
    ]

    var style = {
        theme: 'default',
        type: 'default',
        size: 'default',
        text: 'Выбрать кредитные условия'
    };

    var params = {
        style: style,
        containerModal: 'previewModal',
        containerButton: 'previewButton',
        amount: parseInt(document.querySelector('span.totalSumm').textContent.replace(/\D+/g,"")),
        deliveryAmount: 0,
        shopName: 'ВОЛМА-Маркетинг',
        creditAvailable: true,
        repeatOpen: false,
        creditProducts: data,
    };

    var params2 = {
        style: style,
        containerModal: 'previewModal',
        containerButton: 'previewButton',
        amount: 37800.00,
        creditAvailable: true,
        shopName: 'ВОЛМА-Маркетинг'
    };

    function onOpenModalCallback() {}//Открытие модального окна

    function onSuccessCallback(result) {
        document.querySelector('.fancybox-close-small').click();

        // Записываю в сессию пользователя возвращенные Модальным окном данные
        localStorage.setItem("sber_credit", true);
        localStorage.setItem("creditAmount", result.creditAmount);
        localStorage.setItem("productCode", result.productCode);
        localStorage.setItem("creditTerm", result.creditTerm);
        localStorage.setItem("orderSumm",parseInt(document.querySelector('span.totalSumm').textContent.replace(/\D+/g,""))); // сумма заказа
        localStorage.setItem("orderNDS", parseInt(document.querySelector('span.nds').textContent.replace(/\D+/g,""))); //НДС

        //вызвать окно с сообщением о кредите
        document.getElementById('sber-appl-itog-link').click();
    }
    function onCancelCallback(result) {
        console.log("%o Вы отклонили заявку: ", result);
        document.querySelector('.fancybox-close-small').click();

        //clearDataSberCredit();
    }
    function onErrorCallback(result) {
        console.log('Пользователь не авторизован');
        document.querySelector('.fancybox-close-small').click()
        setTimeout(function() {
            console.log('refresh');
            refreshModal(params, onSuccessCallback, onCancelCallback, onErrorCallback, onOpenModalCallback);
        }, 2000)
    }

    var obj = new CredInBaskSDK(params, onSuccessCallback, onCancelCallback, onErrorCallback, onOpenModalCallback);
    console.groupEnd();
};


// STEP 5 Отправка запроса на кредитную заявку после оформления заказа

function createSbbolCreditOrder(orderID){

    console.group("STEP 5 Отправка запроса на кредитную заявку");

    let q= new Date(),
        date = q.getDate()+'.'+q.getMonth()+'.'+q.getFullYear(),
        externalId = uuidv4();

    const body = {
        "token": localStorage.getItem('sber_token'),

        "account": "40702810711170001928", // номер счета Волмы
        "amount":  +localStorage.getItem("orderSumm"),    // сумма заказа
        "creditRequestUid": "",
        "externalId": externalId, // сгенерированный GUID заказа
        "orderId": orderID,
        "orderUrl": document.location.protocol + '//' + document.location.host + '/personal/orders/' + orderID + '/', //референс ссылка на номер заказа в личный кабинет пользователя
        "purpose": 'Оплата заказа ' + orderID + ' от ' + date + '. В том числе НДС - ' + localStorage.getItem("orderNDS") + ' руб.', //текст заявки на кредит
        "vatAmount": +localStorage.getItem("orderNDS"),        // сумма НДС
        "creditAmount": +localStorage.getItem("creditAmount"), // сумма кредита
        "creditProductCode":  localStorage.getItem("productCode"),  // код кредитного предложения (полученый из запроса на получение кредитных предложений)
        "creditTerm":   localStorage.getItem("creditTerm"),   // срок кредита (полученый из запроса на получение кредитных предложений)
    }

    console.log("sber.js отправляем заявку body: ");
    console.table(body);

    $.ajax({
        type: 'POST',
        url: '/local/templates/main/ajax/sb-request-credit-application.php',
        data: JSON.stringify(body),
        dataType: 'json',
        statusCode: {
            404: function () { // выполнить функцию если код ответа HTTP 404
                console.log("statusCode: страница не найдена 404");
            },
            403: function () { // выполнить функцию если код ответа HTTP 403
                console.log("statusCode: доступ запрещен 403");
            },
            200: function () { // выполнить функцию если код ответа HTTP 200
                console.log("statusCode: 200");
            }
        },
        success: function (data, textStatus, jqXHR) {
            console.log('STEP 5 %csuccess {', 'background-color:blue; color:white');
            console.log('request data:')
            console.table(data);
            if('checks' in data) {
                console.table('data[checks]:');
                console.table(data['checks']);
            }
            if(data.bankStatus === 'RECEIVED'){
                console.log('=> переводим клиента на создание кредитного предложения');
                document.getElementById('sber-goto-sber-link').click(); //показать диалоговое окно о переходе на стр оформления кредита
                setTimeout(function(){
                    document.location.href = 'https://edupir.testsbi.sberbank.ru:9443/ic/dcb/index.html#/credits/credit-financing/credit-partners?order='+externalId;
                }, 3000);
            }
            console.log('STEP 5 %csuccess }');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('STEP 5 error {');
            console.log('textStatus: ' + textStatus);
            console.log("errorThrown %o", errorThrown);
            console.log('STEP 5 error }');
        }
    });

    console.groupEnd();
}

// Генерация уникального GUID заказа
function uuidv4() {
    return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
        (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
    );
}

// Удаляю из сессии пользователя данные о кредите
function clearDataSberCredit(){
	localStorage.removeItem("sber_credit");
    localStorage.removeItem("creditAmount");
    localStorage.removeItem("productCode");
    localStorage.removeItem("creditTerm");
    localStorage.removeItem("orderSumm");
    localStorage.removeItem("orderNDS");
}
function colorAuthIcon(){
    // Перекрасим иконку авторизации
    document.querySelectorAll('.personal-link > i').forEach(function callback(cur, index, array) {
        cur.setAttribute('title','Авторизован в СберБизнес');
        cur.classList.add('personal-link-sber-auth');
    })
}
