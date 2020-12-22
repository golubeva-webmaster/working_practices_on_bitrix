# Связь Критичности обращения в модуле "Техподдержка" и крайнего срока.
>В битриксе не связан крайней срок решения и Критичности. Что было бы логичным.
## Решение
1. Создаем "Уровни техподдержки", называем их как Критичности. Настраиваем доступы, время реакции.
2. В local/php_interface/init.php делаем связь Критичности и Уровня поддержки.
В события 

  AddEventHandler('support', 'OnAfterTicketAdd', array("SupportTicket", "OnAfterTicketAddHandler"));
  AddEventHandler('support', 'OnBeforeTicketUpdate', array("SupportTicket","OnBeforeTicketUpdateHandler"));
  
помещаем код:


  // Дедлайн. При изменении заявки изменяем Уровень поддержки в соотв с Критичностью. Это повлечет пересчет Крайнего срока заявки
  switch ($arFields['CRITICALITY_ID']) {
    case 4:
      $arFields['SLA_ID'] = 1;
      break;
    case 5:
      $arFields['SLA_ID'] = 2;
      break;
    case 6:
      $arFields['SLA_ID'] = 3;
      break;
    case 40:
      $arFields['SLA_ID'] = 4;
      break;
    default:
      $arFields['SLA_ID'] = 2;
        }
