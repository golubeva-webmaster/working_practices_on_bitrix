# Примеры полезных скриптов на js
## Строки
### Удалить из строки все гласные
`str.replace(/[aeiou]/gi, '');`
### Оставить в строке только цифры
`var num = parseInt("1 809 руб.".replace(/\D+/g,""));`

## Массивы
### Найти максимальное и минимальное числа в массиве
```
function highAndLow(numbers){
  let arr = numbers.split(' ').map(Number);  
  return Math.max(...arr) + ' ' + Math.min(...arr);
}
```


## Выбросить ошибку
```
  if (Object.prototype.toString.call(date) != '[object Date]') {
    throw new Error('THROWN');
  }
```
## Получить параметр из url
```
// разбиваем адресную строку на массив параметров и возвращаем их
function getParamsFromUrl(){
    var param = window
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
    return param;
}
```
