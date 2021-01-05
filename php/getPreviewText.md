## Формируем превью описание из детального
```
function getPreviewText($detailText){
    $arrP = explode('.',strip_tags($detailText));   // убрать html, разбить на массив
    $tmp = trim($arrP[0].'. '.$arrP[1]);               // взяли первые 2 предложения
    $tmp = substr($tmp, 0, strrpos($tmp, ' ')); //убрать последний пробел
    $tmp = str_replace('<br>','', $tmp);
    if(strlen($tmp) > 145){
        $tmp = substr($tmp, 0, 145).'...';
    }
    return $tmp;
}

```