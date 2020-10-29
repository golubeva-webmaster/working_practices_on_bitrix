<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?><?
require_once("../../vendor/autoload.php");
require_once("Parser.php");
require_once ("../../mari-functions/bxmari/Bxmari.php");


$url_head = 'https://ru.wikipedia.org';

function getImage($url_page){
    if ($url_page) {
        $htmlBigIMG = Parser::getPage(["url" => $url_page]);
        if (!empty($htmlBigIMG["data"])) {
            $content = $htmlBigIMG["data"]["content"];
            phpQuery::newDocument($content);
            $ar['path'] = 'https:' . pq("#file")->find("img")->attr("src");
            $type_str = explode('.',$ar['path']);
            $ar['type'] = 'image/'.$type_str[count($type_str)-1];//'image/svg';//pq(".mime-type")->text();
            $name_str = pq("#file")->find("img")->attr("alt");
            $ar['name'] = explode('Файл:', $name_str)['1'];
            //$ar['size'] = Bxmari::getFilesize($ar['path']);

            return $ar;
        }
        phpQuery::unloadDocuments();
    }
}

function getSubjectData($data){
    $url_head = 'https://ru.wikipedia.org';
    $ar = [];
    phpQuery::newDocument($data["content"]);

    if(pq('[data-wikidata-property-id="P2046"]')->find("p")->text())
		$ar['UF_TERRITORIA'] = explode('[', pq('[data-wikidata-property-id="P2046"]')->find("p")->text())[0];

    $tm= explode('[', pq('[data-wikidata-property-id="P1082"]')->find("p")->text())[0];
    if($tm)
		$ar['UF_COUNT_NASELENIE'] = explode('↗',$tm)[1];

    $ar['UF_TIMEZONE'] = pq('span[data-wikidata-property-id="P421"]')->find("a")->text();// часовой пояс
    if(pq('span[data-wikidata-property-id="P856"]')->find("a")->attr("href"))
		$ar['UF_OF_SITE'] = pq('span[data-wikidata-property-id="P856"]')->find("a")->attr("href");// официальный сайт

    $ar['UF_MAP_GEO_GOOGLE'] = pq('span.geo-google')->find("a")->attr("href");
    /* комментирую пока, т.к. что-то ломается в длинных строках   $ar['MAP']['geo-geohack'] = pq('span.geo-geohack')->find("a")->attr("href");  $ar['MAP']['geo-yandex'] = pq('span.geo-geohack')->find("a")->attr("href");  $ar['MAP']['geo-osm'] = pq('span.geo-geohack')->find("a")->attr("href");   */

    $img_url = pq(".infobox-image")->find("a")->attr("href");
    $flag_url = pq('span[data-wikidata-property-id="P41"]')->find("a")->attr("href");// флаг
    $gerb_url = pq('span[data-wikidata-property-id="P94"]')->find("a")->attr("href");// герб
    $img_on_map_url = pq('span[data-wikidata-property-id="P242"]')->find("a")->attr("href");// на карте

    // Идем на стр картинок, забираем
    // Детальная
    if($img_url) {
        $img_tmp = getImage($url_head.$img_url);
        $ar['PICTURE'] = Bxmari::saveFile($img_tmp);
    }
    //Флаг
    if($flag_url) {
        $img_tmp = getImage($url_head.$flag_url);
        $ar["UF_FLAG"] = Bxmari::saveFile($img_tmp);
    }

    if($gerb_url) {
        $img_tmp = getImage($url_head . $gerb_url);
        $ar["UF_GERB"] = Bxmari::saveFile($img_tmp);
    }

    if($img_on_map_url) {
        $img_tmp = getImage($url_head.$img_on_map_url);
        $ar['UF_ON_MAP'] = Bxmari::saveFile($img_tmp);
    }

    return $ar;
}

// Регионы
$html = Parser::getPage([
    "url" => "https://ru.wikipedia.org/wiki/Субъекты_Российской_Федерации"
]);
//if(!empty($html["data"])){
//    $content = $html["data"]["content"];
//    phpQuery::newDocument($content);
//    $sub = pq(".standard tbody tr");
//
//        $tmp = [
//            "ACTIVE" => 'Y',
//            "IBLOCK_SECTION_ID" => IBLOCK_SECTION_ID,
//            "IBLOCK_ID" => $GLOBALS['REGIONS_IBLOCK_ID'],
//            //"DESCRIPTION" => 'DESCRIPTION qwq qwq qwqw', "DESCRIPTION_TYPE" => 'text',
//        ];
//
//    foreach($sub as $key => $category){
//        $cat = pq($category);
//        $tmp[$key] = [
//            "NAME" => trim($cat->find("td:nth-child(2) a")->attr('title')),
//            "UF_WIKI"  => $url_head.trim($cat->find("td:nth-child(2) a")->attr("href")),
//        ];
//    }
//    phpQuery::unloadDocuments();
//}

$tmp = [
//    '40' => ['NAME' => 'Владимирская область','UF_WIKI' => $url_head.'/wiki/%D0%92%D0%BB%D0%B0%D0%B4%D0%B8%D0%BC%D0%B8%D1%80%D1%81%D0%BA%D0%B0%D1%8F_%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C' ],
//    '41' => ['NAME' => 'Волгоградская область', 'UF_WIKI' => $url_head.'/wiki/%D0%92%D0%BE%D0%BB%D0%B3%D0%BE%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F_%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C',    ],
//    "2"=>["NAME"=>"Республика Адыгея", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%90%D0%B4%D1%8B%D0%B3%D0%B5%D1%8F"],
//    "3"=>["NAME"=>"Республика Алтай", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%A0%D0%B5%D1%81%D0%BF%D1%83%D0%B1%D0%BB%D0%B8%D0%BA%D0%B0_%D0%90%D0%BB%D1%82%D0%B0%D0%B9"],
//    "4"=>["NAME"=>"Республика Башкортостан", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%91%D0%B0%D1%88%D0%BA%D0%BE%D1%80%D1%82%D0%BE%D1%81%D1%82%D0%B0%D0%BD"],
//    "5"=>["NAME"=>"Республика Бурятия", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%91%D1%83%D1%80%D1%8F%D1%82%D0%B8%D1%8F"],
//    "6"=>["NAME"=>"Республика Дагестан", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%94%D0%B0%D0%B3%D0%B5%D1%81%D1%82%D0%B0%D0%BD"],
//    "7"=>["NAME"=>"Республика Ингушетия", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%98%D0%BD%D0%B3%D1%83%D1%88%D0%B5%D1%82%D0%B8%D1%8F"],
//    "8"=>["NAME"=>"Республика Кабардино-Балкария", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%9A%D0%B0%D0%B1%D0%B0%D1%80%D0%B4%D0%B8%D0%BD%D0%BE-%D0%91%D0%B0%D0%BB%D0%BA%D0%B0%D1%80%D0%B8%D1%8F"],
//    "9"=>["NAME"=>"Республика Калмыкия", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%9A%D0%B0%D0%BB%D0%BC%D1%8B%D0%BA%D0%B8%D1%8F"],
//    "10"=>["NAME"=>"Республика Карачаево-Черкесия", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%9A%D0%B0%D1%80%D0%B0%D1%87%D0%B0%D0%B5%D0%B2%D0%BE-%D0%A7%D0%B5%D1%80%D0%BA%D0%B5%D1%81%D0%B8%D1%8F"],
  "11"=>["NAME"=>"Республика Карелия", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%A0%D0%B5%D1%81%D0%BF%D1%83%D0%B1%D0%BB%D0%B8%D0%BA%D0%B0_%D0%9A%D0%B0%D1%80%D0%B5%D0%BB%D0%B8%D1%8F"],
//    "12"=>["NAME"=>"Республика Коми", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%A0%D0%B5%D1%81%D0%BF%D1%83%D0%B1%D0%BB%D0%B8%D0%BA%D0%B0_%D0%9A%D0%BE%D0%BC%D0%B8"],
//    "13"=>["NAME"=>"Республика Крым", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%A0%D0%B5%D1%81%D0%BF%D1%83%D0%B1%D0%BB%D0%B8%D0%BA%D0%B0_%D0%9A%D1%80%D1%8B%D0%BC"],
//    "14"=>["NAME"=>"Республика Марий Эл", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%9C%D0%B0%D1%80%D0%B8%D0%B9_%D0%AD%D0%BB"],
//    "15"=>["NAME"=>"Республика Мордовия", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%9C%D0%BE%D1%80%D0%B4%D0%BE%D0%B2%D0%B8%D1%8F"],
//    "16"=>["NAME"=>"Республика Якутия", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%AF%D0%BA%D1%83%D1%82%D0%B8%D1%8F"],
//    "17"=>["NAME"=>"Республика Северная Осетия", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%A1%D0%B5%D0%B2%D0%B5%D1%80%D0%BD%D0%B0%D1%8F_%D0%9E%D1%81%D0%B5%D1%82%D0%B8%D1%8F"],
//    "18"=>["NAME"=>"Республика Татарстан", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%A2%D0%B0%D1%82%D0%B0%D1%80%D1%81%D1%82%D0%B0%D0%BD"],
//    "19"=>["NAME"=>"Республика Тыва", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%A2%D1%8B%D0%B2%D0%B0"],
//    "20"=>["NAME"=>"Республика Удмуртия", "UF_WIKI"=>"https://ru.wikipedia.org/wiki/%D0%A3%D0%B4%D0%BC%D1%83%D1%80%D1%82%D0%B8%D1%8F"],

];

//echo '<pre>'; print_r($tmp); echo '</pre>';
//foreach($tmp as $key=>$val){
//    echo '<br>"'.$key.'"=>["NAME"=>"'.$val['NAME'].'", "UF_WIKI"=>"'.$val['UF_WIKI'].'"],<br>';
//
//}


// внутри Субъекта РФ
foreach($tmp as $key => $arObl) {

     $htmlRegion = Parser::getPage([
        "url" => $arObl['UF_WIKI']
    ]);
    if (!empty($htmlRegion["data"])) {

        $arSubj = getSubjectData($htmlRegion["data"]);
        $tmp[$key] = array_merge($tmp[$key], $arSubj);
        phpQuery::newDocument($htmlRegion["data"]["content"]);

        // Создаем раздел Регион
        $tmp[$key]["ACTIVE"] = 'Y';
        $tmp[$key]["CODE"] = Bxmari::transliterate($tmp[$key]['NAME']);
        $tmp[$key]["IBLOCK_ID"] = $GLOBALS['REGIONS_IBLOCK_ID'];
        $tmp[$key]["DETAIL_PICTURE"] = $tmp[$key]["PICTURE"];
        //$arObl["DESCRIPTION"] = 'DESCRIPTION qwq qwq qwqw';    $arObl["DESCRIPTION_TYPE"] = 'text';
        $region_section_id = '';
        $region_section_id = Bxmari::createSection($tmp[$key]);
        //echo '<br>функция вернула id региона= '.$region_section_id.'<br>';

        //cоздаем раздел Города
        $arCityContainer = [
            'NAME' => 'Города',
            'CODE' => $tmp[$key]["CODE"] . '-goroda',
            'IBLOCK_ID' => $GLOBALS['REGIONS_IBLOCK_ID'],
            'IBLOCK_SECTION_ID' => $region_section_id,
            'DETAIL_PICTURE' => $tmp[$key]['PICTURE'],
        ];
        $region_city_id = '';
        $region_city_id = Bxmari::createSection($arCityContainer);

        // Населенные пункты
        $cities = pq("table.wide")->find('tr');
        $tmp[$key]['CITES'] = [];
        $i=0;

        foreach ($cities as $city) {
            $cat = pq($city);
            $url = trim($cat->find("td:nth-child(1)")->find("a")->attr('href'));

            $tmp[$key]['CITES'][$i] = [
                "NAME" => trim($cat->find("td:nth-child(1)")->find("a")->text()),
                "UF_WIKI" => $url_head . $url
            ];
            // Парсим населенный пункт
            $htmlSub = Parser::getPage([
                "url" => $url_head . $url
            ]);
            if (!empty($htmlSub["data"])) {
                $arSubj = getSubjectData($htmlSub["data"]);
                $tmp[$key]['CITES'][$i] = array_merge($tmp[$key]['CITES'][$i], $arSubj);
            }


            // Создаем раздел Населенный пункт
            $tmp[$key]['CITES'][$i]["ACTIVE"] = 'Y';
            $tmp[$key]['CITES'][$i]["CODE"] = Bxmari::transliterate($tmp[$key]['CITES'][$i]['NAME']);
            $tmp[$key]['CITES'][$i]["IBLOCK_ID"] = $GLOBALS['REGIONS_IBLOCK_ID'];
            $tmp[$key]['CITES'][$i]['IBLOCK_SECTION_ID'] = intval($region_city_id);
            $tmp[$key]['CITES'][$i]['DETAIL_PICTURE'] = ($i == 0) ? $tmp[$key]['CITES'][$i]['PICTURE'] : []; // детальную только столице

            $city_section_id = '';
            $city_section_id = Bxmari::createSection($tmp[$key]['CITES'][$i]);

            $i++;
        }

    phpQuery::unloadDocuments();
    }
    echo '<br><br><a href="'.$tmp[$key]['UF_WIKI'].'">'.$tmp[$key]['NAME'].'</a><br>';
    echo '<a href="'.$tmp[$key]['CENTER']['url'].'">'.$tmp[$key]['CENTER']['title'].'</a><br>';
    echo '<pre>'; print_r($tmp[$key]); echo '</pre>';
}


?>
<?php
/*
 * Ошибки curl http://php-zametki.ru/php-prodvinutym/75-php-curl.html
 * Парсинг: http://falbar.ru/article/pishem-parser-kontenta-na-php
 * */?>

<?php/*
новости туризма РФ
РФ туризм https://russian.rt.com/tag/turizm
мир туризм, РФ в основном https://tourism.interfax.ru/ru/?tpid=961&tpl=47 ---
мир туризм https://ria.ru/tourism_news/

храмы, регоины: https://hramy.ru/regions/regfull.htm
офицмальные сайты субъектов РФ http://www.gov.ru/main/regions/regioni-44.html

wiki API https://www.ibm.com/developerworks/ru/library/x-phpwikipedia/index.html
видео о разном https://zilcc.ru/news/8364.html
Спецпроект Путешествуйте с кагоцелом https://kagoceltravelrussia.kudago.com/?utm_source=kudago.com&utm_medium=editorial_interesting&utm_campaign=kagocel#/article/baikals
*/
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
