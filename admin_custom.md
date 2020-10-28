# Кастомизация админки при помощи файла local/php_interface/admin_header.php

> Задача. В списке обращений в модуле техподдержки раскрасить различные критичности в разные цвета

1. В local/php_interface_init.php Назначим классы для ячеек, содержащих справочник "Критичность"
  \Bitrix\Main\EventManager::getInstance()->addEventHandler("main", "OnAdminListDisplay", array("AdminCustom", "SuppotrTicketList"));

  class AdminCustom
  {
      public static function SuppotrTicketList(&$list)
      {
          if ($list->table_id=="t_ticket_list") {
              foreach ($list->aRows as $row){
                  $tmp = $row->aFields["CRITICALITY_ID"]["view"]["value"];
                  $row->aFields["CRITICALITY_ID"]["view"]["value"] = '<span class="criticality">'.$tmp.'</span>';
              }
          }
      }

2. В файле local/php_interface/admin_header.php (если нет, создать) пропишим стили и скрипты
Файл выполненяется в начале загрузки страницы админки, поэтому чтобы выполнялись скрипты на странице, проверить выполнение  DOMContentLoaded

  <style>
      .criticality{
          padding: 3px;
          width: 100%;
          display: block;
      }
  </style>
  <script type='text/javascript'>
      document.addEventListener("DOMContentLoaded", function(){
          console.log('DOMContentLoaded');
          document.querySelector('#t_ticket_list').querySelectorAll('.criticality').forEach((e)=>{
              console.log(e.innerText);
              if(e.innerText === 'Высокая'){
                  e.style.backgroundColor = '#ea2e49';
                  e.style.color = 'white';
              }
              if(e.innerText === 'Низкая'){
                  e.style.backgroundColor = '#8e9eb3';
                  e.style.color = 'white';
              }
              if(e.innerText === 'Критическая'){
                  e.style.backgroundColor = '#b8855c';
                  e.style.color = 'white';
              }
              if(e.innerText === 'Средняя'){
                  e.style.backgroundColor = '#5cb85c';
                  e.style.color = 'white';
              }
          });
          console.log('DOM end');
      });
  </script>
