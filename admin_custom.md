# Кастомизация админки при помощи файла local/php_interface/admin_header.php

> Задача. В списке обращений в модуле техподдержки раскрасить различные критичности в разные цвета

![img](/img/criticality.png)

1. В local/php_interface_init.php Назначим классы для ячеек, содержащих справочник "Критичность"

```
 \Bitrix\Main\EventManager::getInstance()->addEventHandler("main", "OnAdminListDisplay", array("AdminCustom", "SuppotrTicketList"));
 class AdminCustom
      {
       public static function SuppotrTicketList(&$list)
       {
           if ($list->table_id=="t_ticket_list") {
               foreach ($list->aRows as $row){
                   $value = $row->aFields["CRITICALITY_ID"]["view"]["value"];
                   switch ($value){
                   case 'Низкая':
                       $critic_class = 'critic_low';
                       break;
                   case 'Средняя':
                       $critic_class = 'critic_medium';
                       break;
                   case 'Высокая':
                       $critic_class = 'critic_high';
                       break;
                   case 'Критическая':
                       $critic_class = 'critic_critical';
                       break;
                   default:
                       $critic_class = '';
                       break;
                   }
                   $row->aFields["CRITICALITY_ID"]["view"]["value"] = '<span class="criticality '.$critic_class.'">'.$value.'</span>';
               }
           }
       }
```

2. В файле local/php_interface/admin_header.php (если нет, создать) пропишим стили и скрипты. Файл выполненяется в начале загрузки страницы админки, поэтому чтобы выполнялись скрипты на странице, проверить выполнение  DOMContentLoaded. Но в этой задаче у нас тут только стили.
```
  <style>
      .criticality{
          padding: 3px;
          width: 100%;
          display: block;
          color:white;
      }
      .critic_low{
          background-color: #8e9eb3;
      }
      .critic_medium{
          background-color: #5cb85c;
      }
      .critic_hight{
          background-color: #ea2e49;
      }
      .critic_critical{
          background-color: #b8855c;
      }
  </style>
```
