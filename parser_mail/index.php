<?
/* Скрипт распарсивает непрочитанные письма из почтовика.
 * И создает по ним заявку в Модуль Техподдержка битрикса.
  * */
$_SERVER['DOCUMENT_ROOT'] =     '/home/bitrix/www';
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

define('USER_NAME', '###');
define('USER_PASSWD', '###');
define('MAIL_ANSWER', 'Re: [TicketID: ');
define('HOSTNAME', '{mail.###.ru:993/imap/ssl}INBOX');
define('IMAP_CRITERIA', 'RECENT'); // новые, не просмотренные письма // 'FROM test@volma.ru' //RECENT

require_once("functions.php");
// Список учитываемых типов файлов
$mail_filetypes = ["DOC", "DOCX", "JPEG", "JPG", "PNG", "GIF", "TXT", "XLS", "XLSX", "CSV", "PDF"];
$arNoImg = ['image0001','image0002','image0003','image0004','image0005','image0006','image0007'];

$connection = imap_open(HOSTNAME, USER_NAME, USER_PASSWD);

if(!$connection){
    echo("Ошибка соединения с почтой - ".USER_NAME);
    exit;
}else{

    $msg_num = imap_num_msg($connection);
    $emails = imap_search($connection, IMAP_CRITERIA); //получает массив c индикаторами писем
    echo '<br>писем = '.count($emails);
    $mails_data = array();

    $k = 0;
    foreach($emails as $i) {

        // Шапка письма
        $msg_header = imap_header($connection, $i);
        $mails_data[$i]["time"] = time($msg_header->MailDate);
        $mails_data[$i]["date"] = $msg_header->MailDate;

        foreach ($msg_header->to as $data) {
            $mails_data[$i]["to"] = $data->mailbox . "@" . $data->host;
        }

        foreach ($msg_header->from as $data) {

            $mails_data[$i]["from"] = $data->mailbox . "@" . $data->host;
            $mails_data[$i]["title"] = get_imap_title($msg_header->subject) ? get_imap_title($msg_header->subject) : $msg_header->subject;

            // Тело письма
            $msg_structure = imap_fetchstructure($connection, $i); //Прочитать структуру указанного сообщения
            $msg_body = imap_fetchbody($connection, $i, 1); //Извлечь конкретную секцию тела сообщения
            $recursive_data = recursive_search($msg_structure);

            if(
                $msg_structure -> parts[0] -> subtype == 'ALTERNATIVE'  ||
                $msg_structure -> parts[0] -> subtype == 'MIXED'        ||
                $msg_structure -> parts[0] -> subtype == 'RELATED'
            ) {//есть вложения
                //вырежем первую часть
                $delem = $msg_structure->parts[0]->parameters[0]->value;
                if (strpos($msg_body, $delem) !== false) {
                    $ex = explode($delem, $msg_body);
                    $msg_body = $ex[1];
                }
                //вырежем часть после кодировки
                $arEncodes = ['0'=>'7bit',
                    '1'=>'8bit',
                    '2'=> 'Binary',
                    '3'=> 'Base64',
                    '4'=> 'Quoted-Printable',
                    '5'=> 'other',
                ];
                $delem2 = 'Content-Transfer-Encoding: '.strtolower($arEncodes[$recursive_data["encoding"]]);
                //$delem2 = 'Content-Transfer-Encoding: 8bit';
                if (strpos($msg_body, $delem2) !== false) {
                    $ex = explode($delem2, $msg_body);
                    $msg_body = $ex[1];
                }
            }
            //вырежем подпись
            $delem3 = '--';
            if (strpos($msg_body, $delem3) !== false) {
                $ex = explode($delem3, $msg_body);
                $msg_body = $ex[0];
            }


            // отладка
            $mails_data[$i]['Subject'] = $msg_header->Subject;
            $arSubj = explode('?',$msg_header->Subject);
            $mails_data[$i]['subject_1'] = $arSubj[1];
            $mails_data[$i]['subject_2'] = $arSubj[2];
            $mails_data[$i]['encoding'] = $recursive_data["encoding"];


            $bodystruct = imap_bodystruct($connection, $i, 1); //- Прочитать структуру указанной секции тела заданного сообщения
            $body = "";


            if ($recursive_data["encoding"] == 0 || $recursive_data["encoding"] == 1) {
                $body = $msg_body;
            }
            if ($recursive_data["encoding"] == 2) {
                $body = structure_encoding($recursive_data["encoding"], $msg_body);
            }
            if ($recursive_data["encoding"] == 3) {
                $body = structure_encoding($recursive_data["encoding"], $msg_body);
            }
            if ($recursive_data["encoding"] == 4) {
                $body = structure_encoding($recursive_data["encoding"], $msg_body);
            }

            // попробую для вложения
            if($arSubj[2] == 'Q')
                $body = quoted_printable_decode($msg_body);

            $mails_data[$i]["body"] = $body;

            // Вложенные файлы
            if (isset($msg_structure->parts)) {

                for ($j = 1, $f = 2; $j < count($msg_structure->parts); $j++, $f++) {
                    if($msg_structure->parts[$j]->subtype <> 'HTML'
                        &&
                        $msg_structure->parts[$j]->subtype <> 'text/html'
                    ) {

                        $arr = (array)$msg_structure->parts[$j]->parameters;
                        if (!empty($arr)) {
                            $name = (get_imap_title($msg_structure->parts[$j]->parameters[0]->value) !== '') ?
                                get_imap_title($msg_structure->parts[$j]->parameters[0]->value) :
                                $msg_structure->parts[$j]->parameters[0]->value;
                        }

                            $mails_data[$i]["attachs"][$j]["type"] = $msg_structure->parts[$j]->subtype;
                            $mails_data[$i]["attachs"][$j]["size"] = $msg_structure->parts[$j]->bytes;
                            $mails_data[$i]["attachs"][$j]["name"] = $name;

                            $mails_data[$i]["attachs"][$j]["file"] = structure_encoding(
                                $msg_structure->parts[$j]->encoding, // номер кодировки
                                imap_fetchbody($connection, $i, $f) //закодированное тело файла
                            );

                            $mails_data[$i]["attachs"][$j]["tmp_file_name"] = tempnam(sys_get_temp_dir(), 'img_tmp' . iconv("utf-8", "cp1251", $mails_data[$i]["attachs"][$j]["name"]));
                            $mails_data[$i]["attachs"][$j]["tmp_file"] = file_put_contents($mails_data[$i]["attachs"][$j]["tmp_file_name"], $mails_data[$i]["attachs"][$j]["file"]);
                    }
                }
            }
        }

        if(!empty($mails_data[$i])) {
            if ($mails_data[$i]["to"] !== $mails_data[$i]["from"]) { // если отправитель не равен получателю

                //Определим id пользователя
                $filter = ["EMAIL" => $mails_data[$i]['from']];
                $sql = CUser::GetList(($by="id"), ($order="desc"), $filter);
                if($sql->NavNext(true, "f_"))
                    $mails_data[$i]['user_id'] = $f_ID;

                addSupportTicket($mails_data[$i]);
            }
        }
    }

}

imap_close($connection);
?>
<?require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');?>