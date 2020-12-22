# Связь Статуса "Решена" обращения в модуле "Техподдержка" и закрытия обращения.
>Задача. По выставлению статуса "Решена" - закрывать заявку. И наоборот. При смене статуса "Решена" на любой другой - открывать обращение. При открытии обращения, установить статус "Открыта"
>Аналогично связать статус "Отложена" и маркер "Отложена"
## Решение
В local/php_interface/init.php
```
  AddEventHandler('support', 'OnBeforeTicketUpdate', array("SupportTicket","OnBeforeTicketUpdateHandler"));
  class SupportTicket
    {
      function OnBeforeTicketUpdateHandler($arFields){
              //Связать смену статуса на "Решена" и маркер "Закрыта"
        // Логика: Статус "Решена" установлен (STATUS_ID = 10)- установить маркер "Закрыта" (CLOSE - "Y")
        // Изменен статус "Решена" на любой другой - снять маркер "Закрыта"
        // и наоборот

        // Маркер Закрыта-Открыта
        if ($arFields['CLOSE'] == 'Y') {
            $arFields['STATUS_ID'] = 10;
            $arFields['HOLD_ON'] = 'N'; // HOLD_ON - отложена если Y
            //mariMail(['id'=>$arFields['ID'], 'str'=>440, 'close Y'=>'Status 10']);
        }
        elseif($arFields['CLOSE'] == 'N'){
            if($arFields['STATUS_ID'] == 49){
                $arFields['HOLD_ON'] = 'Y';
            }
            else {
                $arFields['STATUS_ID'] = 7;
                $arFields['HOLD_ON'] = 'N';
                //mariMail(['id'=>$arFields['ID'], 'str'=>444, 'close N'=>'Status 7']);
            }
        }
        else {}


        switch ((int)$arFields['STATUS_ID']) {
            case 10:
                $arFields['CLOSE'] = 'Y';
                $arFields['HOLD_ON'] = 'N'; // не Отложить
                mariMail(['id'=>$arFields['ID'], 'str'=>473, 'status_id'=>$arFields['STATUS_ID'],'close'=>'Y', 'HOLD_ON'=> 'N']);
                break;
            case 49:
                $arFields['CLOSE'] = 'N';
                $arFields['HOLD_ON'] = 'Y';
                mariMail(['id'=>$arFields['ID'], 'str'=>478, 'status_id'=>$arFields['STATUS_ID'],'close' => 'N', 'HOLD_ON'=> 'Y']);
                break;
            default:
                $arFields['CLOSE'] = 'N';
                $arFields['HOLD_ON'] = 'N';
                mariMail(['id'=>$arFields['ID'], 'str'=>483, 'status_id'=>$arFields['STATUS_ID'],'close' => 'N', 'HOLD_ON'=> 'N']);
                break;
        }
      }
```
