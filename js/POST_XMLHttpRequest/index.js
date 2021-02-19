// На примера запроса для Сбербанка: получение токена пользователя

const requestURL = 'xxx';
const body = {
    "grant_type": "xxx",
    "redirect_uri":document.location.href.split('?')[0],
    "client_id": "xxx",
    "client_secret": "xx",
    "code": localStorage.getItem('sber_code')
}

function sendRequest(method, url, body=null){
    return new Promise((resolve, reject) =>
    {
        const xhr = new XMLHttpRequest();

        xhr.open(method, url);

        xhr.responseType = 'json';
        xhr.setRequestHeader('Host', 'xxx');
        xhr.setRequestHeader('Accept-Encoding', 'gzip, deflate, br');
        xhr.setRequestHeader('Connection', 'keep-alive');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = () => {
            if (xhr.status >= 400) {
                reject(xhr.response)
            } else {
                resolve(xhr.response);
            }
        }

        xhr.onerror = () => {
            reject(xhr.response);
        }
        xhr.send(JSON.stringify(body));
    })
}

sendRequest('POST', requestURL, body)
    .then(data => console.log(data))
    .catch(err => console.log(err))
    
// Request Header - заголовки с нашего браузера
// Preview ответ
