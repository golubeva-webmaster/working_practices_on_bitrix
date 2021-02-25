# При снятии ответственного у обращения в Техподдержку, отсылать уведомление прежнему ответственному.
init.php
* Отправить сообщение $arRes['RESPONSIBLE_EMAIL'] о том, что с него снята ответственность.
* 1) Завести тип почтового события TICKET_CHANGE_RESPONSIBLE_FOR_OLD_RESPONSIBLE, в нем параметр: OLD_RESPONSIBLE_USER_EMAIL
* 2) Завести шаблон
* 3) Отправить письмо CEvent::Send

```
 AddEventHandler('support', 'OnBeforeTicketUpdate', array("SupportTicket","OnBeforeTicketUpdateHandler"));
 function OnBeforeTicketUpdateHandler($arFields){
        // определим ответственного до изменения
        $rsFiles = CTicket::GetByID($arFields['ID'], "RU", "N","Y","N");
        if($arRes = $rsFiles->Fetch())
        {
            if($arRes['RESPONSIBLE_USER_ID'] <> $arFields['RESPONSIBLE_USER_ID']){

                CEvent::Send(
                    "TICKET_CHANGE_RESPONSIBLE_FOR_OLD_RESPONSIBLE",
                    ['4c','s1'],
                    [
                        'OLD_RESPONSIBLE_USER_EMAIL' => $arRes['RESPONSIBLE_EMAIL'],
                        'ID'=>$arFields['ID']
                    ]);
            }
        }
 }
 
```
