# Примеры полезных скриптов на js
## Строки
### Удалить из строки все гласные
`str.replace(/[aeiou]/gi, '');`
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

console.log( params['code']); // получим значение параметра code
```
