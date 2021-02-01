## Очистить множественное св-во MORE_PHOTO
```
<?php
/* Очистить св-во MORE_PHOTO  */

$_SERVER['DOCUMENT_ROOT'] = '/home/m/marigolu18/marigolu18.beget.tech/public_html';
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

$ID =  	50950;//53855;
$text = $mailtext = '';
global $USER;

\Bitrix\Main\Loader::IncludeModule("iblock");
$arSelect = Array("ID", "DETAIL_TEXT", "NAME", "DETAIL_PAGE_URL", "PROPERTY_MORE_PHOTO");
$arFilter = Array(	"IBLOCK_ID"=>48,
	"ACTIVE_DATE"=>"Y",
	"ACTIVE"=>"Y",
	//"ID" => $ID,
	"!PROPERTY_MORE_PHOTO" =>false
);
$res = CIBlockElement::GetList(Array("RAND" => "ASC"), $arFilter, false, Array("nPageSize"=>5), $arSelect);
$i = 0;
while($ob = $res->GetNextElement())
{
	$arFields = $ob->GetFields();
	$text .= ++$i.'<a target="_blank" href="'.$arFields["DETAIL_PAGE_URL"].'">'.$arFields['NAME'].'</a><br>';

	// Получим значения св-в MORE_PHOTO этого эл-та
   $db_props = CIBlockElement::GetProperty($arFields["IBLOCK_ID"], $arFields['ID'], "sort", "asc", Array("CODE"=>"MORE_PHOTO"));
   while($ar_props = $db_props->Fetch()) {
	   if ($ar_props["VALUE"]) {
		   $ar_val = $ar_props["VALUE"];
		   $ar_val_id = $ar_props["PROPERTY_VALUE_ID"];

		   // Удалим значения св-вa MORE_PHOTO
		   $arr[$ar_props['PROPERTY_VALUE_ID']] = array("VALUE" => array("del" => "Y"));
		   CIBlockElement::SetPropertyValueCode($arFields["ID"], "MORE_PHOTO", $arr);
		   CFile::Delete($ar_props['VALUE']);
	   }
   }
}
//echo $text;
//mail('xxx@gmail.com', 'Result of resave '.date('U'), $mailtext);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
?>

```
