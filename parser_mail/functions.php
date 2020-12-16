<?require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');?>

<?php
function check_utf8($charset){
    if(strtolower($charset) != "utf-8")
        return false;
    return true;
}

function convert_to_utf8($in_charset, $str){
    return iconv(strtolower($in_charset), "utf-8", $str);
}

function get_imap_title($str){
    $mime = imap_mime_header_decode($str);
    $title = "";
    foreach($mime as $key => $m){
        if(!check_utf8($m->charset))
            $title .= convert_to_utf8($m->charset, $m->text);
        else
            $title .= $m->text;
    }
    return $title;
}

function recursive_search($structure){
    $encoding = "";
    if($structure->subtype == "HTML" ||
        $structure->type == 0){
        if($structure->parameters[0]->attribute == "charset"){
            $charset = $structure->parameters[0]->value;
        }

        return array(
            "encoding" => $structure->encoding,
            "charset"  => strtolower($charset),
            "subtype"  => $structure->subtype
        );
    }else{
        if(isset($structure->parts[0])){
            return recursive_search($structure->parts[0]);
        }else{
            if($structure->parameters[0]->attribute == "charset"){
                $charset = $structure->parameters[0]->value;
            }
            return array(
                "encoding" => $structure->encoding,
                "charset"  => strtolower($charset),
                "subtype"  => $structure->subtype
            );
        }
    }
}

function structure_encoding($encoding, $msg_body){
    /*
    0 7bit
    1 8bit              ?UTF-8?B?    encoding = 1    Thinderbird, все хорошо
    2 Binary
    3 Base64            ?UTF-8?B?     encoding = 3      из web gmail , кодировка хорошо, но идет вложение UTF-8 - все письмо
                                      encoding = 3      из Аутлука
    4 Quoted-Printable  utf-8?Q?      encoding = 4    из САПа, все хорошо
    5 other
    no need to decode binary or 8bit!
    в примере декодируются только 3 и 4 кодировки: https://www.php.net/manual/ru/function.imap-fetchstructure
    */
    switch((int) $encoding){
        case 4:
            $body = quoted_printable_decode($msg_body); //imap_qprint($msg_body); //
            break;

        case 3:
            $body = base64_decode($msg_body); //mb_convert_encoding(base64_decode($msg_body), 'UTF-8', 'KOI8-R'); //imap_base64($msg_body); //base64_decode($data) сработало;
            break;

        case 2:
            $body = imap_binary($msg_body);
            break;

        case 1:
            $body = imap_8bit($msg_body);
            break;

        case 0:
            $body = $msg_body;
            break;

        default:
            $body = "";
            break;
    }

    //mariMail(['in structure_encoding' => 'yes', 'encoding' => $encoding,'msg_body' => $msg_body, 'body' => $body]);
    return $body;
}

// добавляющего новое обращение в техподдержку приходящего по EMail,
// или добавляющее сообщение к существующему обращению
function addSupportTicket($arr){
    $arr['in_fn'] = 'addSupportTicket';
    if($arr['subject_1'] === 'koi8-r')
        $arr['body'] = iconv('koi8-r','utf-8',$arr['body']);

    //вырежем до подписи 'С уважением'
    $delem = 'С уважением';
    if (strpos($arr['body'], $delem) !== false) {
        $ex = explode($delem, $arr['body']);
        $arr['body'] = $ex[0];
    }

    if(mb_strpos($arr['title'], MAIL_ANSWER) !== false) {
        //Тема содержит '.MAIL_ANSWER;
        $ex = explode('Re: [TicketID: ', $arr['title']);
        $ticket = explode(']', $ex[1]);
        $ticket_id = $ticket[0];
    }
    else {
        //Тема не содержит '.MAIL_ANSWER
        $ticket_id = '';
        if((mb_strpos($arr['title'], '[TicketID:') !== false))
            return false; //Не создаем ничего, выходим из функции
    }

    if((mb_strpos($arr['title'], 'FW:') !== false))
        return false; //Не создаем ничего, выходим из функции
    //Заявка создана через SAP ERP. Пользователь KORNEVA. Уровень критичности [high].
    /*
     low низкая
     middle - средняя
     high - высок
     critical  - критический

    SAP: низкий, средний, высокий, критический
    ex: Заявка создана через SAP ERP. Пользователь IKONNIKOVAN. Приоритет низкий.
    */
    //Определение критичности для заявок из САП
    if ((mb_strpos($arr['body'], 'SAP ERP') !== false)
        &&
        (mb_strpos($arr['body'], 'Приоритет ') !== false)
    ){
        $arrPrior = explode('Приоритет ',$arr['body']);
        $arrPr = explode('.',$arrPrior[1]);
        switch ($arrPr[0]){
            case 'низкий': $criticaly = 'low'; break;
            case 'средний': $criticaly = 'middle'; break;
            case 'высокий': $criticaly =  'high'; break;
            case 'критический': $criticaly =  'critical'; break;
            default: $criticaly = 'middle';
        }
    }
    //TODO источник SAP указать
    CModule::IncludeModule("support");
    $arFields = array(
        "CREATED_MODULE_NAME"       => "mail",          //идентификатор модуля из которого создаётся обращение
        "MODIFIED_MODULE_NAME"      => "mail",
        "OWNER_SID"                 => $arr['from'],    //email автора
        "OWNER_USER_ID"             => $arr['user_id'], //По email определить id юзера, если не найден - TODO завести
        "CATEGORY_SID"              => ((mb_strpos($arr['body'], 'SAP ERP') !== false) ? 'saperp' : ''), //email
        "CATEGORY_ID"               => ((mb_strpos($arr['body'], 'SAP ERP') !== false) ? 2 : 0), //44
        "SOURCE_SID"                => ((mb_strpos($arr['body'], 'SAP ERP') !== false) ? 'saperp' : 'email'), //символьный код источника обращения
        "CRITICALITY_SID"           => ($criticaly <> '') ? $criticaly : "middle",        //символьный код критичности
        "STATUS_SID"                => "open",
        "TITLE"                     => $arr['title'],
        "MESSAGE"                   => $arr['body'],
        "SUPPORT_COMMENTS"          => (strlen($arr['body']) > 200) ? substr(trim($arr['body']),0,200) : trim($arr['body']),
    );
    // Прикладываю файл
    if(isset($arr['attachs']) && !empty($arr['attachs'])) {
        foreach ($arr['attachs'] as $attach) {
            // Создаю временный файл на сервере
            if ($attach['tmp_file']) {
                $arFields["FILES"][] = [
                    "name" => $attach['name'],
                    "type" => $attach['type'],
                    "tmp_name" => $attach['tmp_file_name'],   // путь к файлу на сервере
                    "error" => 0,
                    "size" => $attach['size']   // размер файла
                ];
            }
        }
    }

    //echo '<br>[addSupportTicket] arFields<pre>'; print_r($arFields); echo '</pre>';

    $NEW_TICKET_ID = CTicket::Set($arFields, $MESSAGE_ID, $ticket_id, "N");

    $arr['MESSAGE_ID'] = $MESSAGE_ID;
    $arr['NEW_TICKET_ID'] = $NEW_TICKET_ID;
    mariMail($arr);

    $f = fopen ("/home/bitrix/www/logs/local-cron-generate_mail_".date("d-m-Y")."_.log", "a+");
    fwrite($f,"\n".'[local/php_interface/volma_functions/mail_parser/functions.php: addSupportTicket] '."\n"."\n");
    fwrite($f,'MASSAGE_ID: '.$MESSAGE_ID."\n");
    fwrite($f,'NEW_TICKET_ID: '.$NEW_TICKET_ID."\n");
    fclose($f);

    if($NEW_TICKET_ID)
        return true;
    else
        return false;
}


function getUserID($email){
    global $USERS;

    $filter = ["EMAIL" => $email];
    echo '<br>[getUserID] <br>filter: <pre>'; print_r($filter); echo '</pre>';
    $sql = CUser::GetList(($by="id"), ($order="desc"), $filter);
    if($sql->NavNext(true, "f_")) {
        $user_id = $f_ID;
        echo 'Пользователь существует: '.$user_id.'<br>';
        return $user_id;
    }
    else{
        echo '[getUserID] Пользователь c email '.$email.' НЕ существует<br>';
        $userName = explode('@',$email);
        echo '<pre>'; print_r($userName); echo '</pre>';

        // Если уже есть юзер с таким логином, дополнить логин
        $userLogin = $userName[0];
        $rsUser = CUser::GetByLogin($userLogin);
        if($arUser = $rsUser->Fetch())
            $userLogin .= '_'.$userName[1];

        $userPass = (strlen($userLogin) < 6) ? $userLogin.$userLogin : $userLogin;

        $user = new CUser;
        $arFields = Array(
            "NAME"              => $userLogin,
            "EMAIL"             => $email,
            "LOGIN"             => $userLogin,
            "LID"               => "ru",
            "ACTIVE"            => "Y",
            "GROUP_ID"          => array(19,21),
            "PASSWORD"          => $userPass,
            "CONFIRM_PASSWORD"  => $userPass,
        );
        echo '<pre>'; print_r($arFields); echo '</pre>';

        $ID = $user->Add($arFields);

        if (intval($ID) > 0) {
            echo 'Пользователь создан: ' . $ID . '<br>';

            return $ID;
        }
        else {
            echo 'Пользователь создан с ошибкой: ';
            mail('marishagolubeva@gmail.com','shop.volma[local/php_interface/volma_functions/mail_parser/functions.php str 220]','Не создан пользователь на основе email '.$email.' отшибка: '.$user->LAST_ERROR);
            mariMail($arFields);
            echo $user->LAST_ERROR.'<br>';
        }
    }
}

function getAttach($obj, $connection, $i, $j, $fn, $f){

    echo '[getAttach] j = '.$j.'<br>';
    echo 'subtype = '.$obj->subtype.'<br>';
    if($obj->subtype <> 'HTML'
        &&
        $obj->subtype <> 'text/html'
        &&
        $obj->subtype <> 'PLAIN') {

        $arr = (array)$obj->parameters;
        if (!empty($arr)) {
            $name = (get_imap_title($obj->parameters[0]->value) !== '') ?
                get_imap_title($obj->parameters[0]->value) :
                $obj->parameters[0]->value;
        }
        $arrRes = [];
        $arrRes["type"] = $obj->subtype;
        $arrRes["size"] = $obj->bytes;
        $arrRes["name"] = $name;
        $arrRes["encoding"] = $obj->encoding;
        $arrRes["sys_get_temp_dir()"] = sys_get_temp_dir(); //TODO удалить после теста
        $arrRes["file"] = structure_encoding(
            $obj->encoding, // номер кодировки
            imap_fetchbody($connection, $i, $fn) //закодированное тело файла
        );
        ///tmp/img_tmp/image001.jpgUGD0DR
        /// /tmp/image001.jpgdBBcix
        //$arrRes["tmp_file_name"] = tempnam(sys_get_temp_dir(), 'img_tmp/' . iconv("utf-8", "cp1251", $arrRes["name"]));
        $arrRes["tmp_file_name"] = tempnam(sys_get_temp_dir(), 'img_tmp/' . $arrRes["name"]);
        $arrRes["tmp_file"] = file_put_contents($arrRes["tmp_file_name"], $arrRes["file"]);

        fwrite($f, "\n" . 'Вложение ' . $j . ': ' . "\n");
        fwrite($f, 'название: ' . $arrRes["name"] . "\n");
        fwrite($f, 'тип: ' . $arrRes["type"] . "\n");
        fwrite($f, 'размер: ' . $arrRes["size"] . "\n");

        return $arrRes;
    }
}
?>

