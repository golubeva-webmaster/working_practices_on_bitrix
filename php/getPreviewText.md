## Формируем превью описание из детального
```
function getPreviewText($detailText){
    $arrP = explode('.',strip_tags($detailText));   // убрать html, разбить на массив
    $tmp = trim($arrP[0].'. '.$arrP[1]);            // первые 2 предложения
    $tmp = substr($tmp, 0, strrpos($tmp, ' '));     //убрать последний пробел
    $tmp = str_replace('<br>','', $tmp);
    if(strlen($tmp) > 250){
        $tmp = substr($tmp, 0, 250).'...';
    }
    return $tmp;
}

```
