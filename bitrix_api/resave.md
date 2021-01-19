# Переобход всего каталога. И какое-либо действие с элементами
Вешаем этот скрипт на крон. Он ежеминутно делает выборку 5-ти элементов с незаполненным св-вом MORE_PHOTE и заполняет го, делая выборку картинок из DETAIL_TEXT.

```
<?php
$_SERVER['DOCUMENT_ROOT'] = '/home/m/marigolu18/marigolu18.beget.tech/public_html';
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

$ID = 53855;
$text = $mailtext = '';
global $USER;

\Bitrix\Main\Loader::IncludeModule("iblock");
$arSelect = Array("ID", "DETAIL_TEXT", "NAME", "DETAIL_PAGE_URL", "PROPERTY_MORE_PHOTO");
$arFilter = Array(	"IBLOCK_ID"=>48,
    "ACTIVE_DATE"=>"Y",
    "ACTIVE"=>"Y",
    //"ID" => $ID,
    "PROPERTY_MORE_PHOTO" =>false
);
$res = CIBlockElement::GetList(Array("RAND" => "ASC"), $arFilter, false, Array("nPageSize"=>5), $arSelect);

while($ob = $res->GetNextElement())
{
    $arImgMFA = [];
    $arFields = $ob->GetFields();

    //обновим элемент
    $el = new CIBlockElement;
    $arLoadProductArray = Array(
        "MODIFIED_BY"    => $USER->GetID(), // элемент изменен текущим пользователем
        "ACTIVE"         => "Y",            // активен
        "DETAIL_TEXT"    => $arFields['DETAIL_TEXT'],
    );

    $ress = $el->Update($arFields['ID'], $arLoadProductArray);



    //echo '<pre>'; print_r($arFields); echo '</pre>';
    $text .= '<a target="_blank" href="'.$arFields["DETAIL_PAGE_URL"].'">'.$arFields['NAME'].'</a><br>';
    $mailtext .= $arFields['NAME'].' - https://www.made-hand.ru'.$arFields["DETAIL_PAGE_URL"]."\r\n";

    if(strpos($arFields["DETAIL_TEXT"], '/upload/medialibrary/')){
        $arFirst = explode('/upload/medialibrary/', $arFields["DETAIL_TEXT"]);
        for ($i = 1; $i < count($arFirst); $i++) {
            $it = explode('"', $arFirst[$i]);
            $arImgMFA[] = ["VALUE" => CFile::MakeFileArray('/upload/medialibrary/'.$it[0]) ,"DESCRIPTION"=>""];
        }
    }

    CIBlockElement::SetPropertyValueCode($arFields['ID'], "MORE_PHOTO", $arImgMFA);
}
//echo $text;

mail('golubeva.webmaster@gmail.com', 'Result of resave '.date('U'), $mailtext);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
?>

```
