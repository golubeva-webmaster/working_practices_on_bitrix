## Отправка массива по почте
```
function mariMail($arrr)
{
    $text = '';
    if (is_array($arrr))
        if(!empty($arrr))
            foreach ($arrr as $keyy => $arr) {
                if (is_array($arr) && !empty($arr)) {
                    $text .= "\r\n".$keyy.'    Array:'."\r\n";
                    foreach ($arr as $key => $ar) {
                        if (is_array($ar) && !empty($ar)) {
                            $text .= "\r\n".$key.'       Array:'."\r\n";
                            foreach ($ar as $k => $v) {
                                $text .= "\r\n".$key.'       ' . $k . ' = ' . $v;
                            }
                        }
                        else $text .= "\r\n" . '   ' . $key . ' = ' . $ar;
                    }
                }
                else {
                    $text .= "\r\n" . $keyy . ' = ' . $arr;
                }
            }
        else $text .= 'Переданное значение, не массив = '.$arrr;
    mail('XXX@gmail.com', 'mariMail made-hand init'.date('U'), $text);
}
```
