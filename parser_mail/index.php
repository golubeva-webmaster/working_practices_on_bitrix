#!/usr/local/bin/php
<?
/* Скрипт распарсивает непрочитанные письма из почтовика.
 * И создает по ним заявку в техподдержку битрикса.
  * */
$_SERVER['DOCUMENT_ROOT'] =     '/home/bitrix/www';
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('USER_NAME', 'test'); //define('USER_NAME', 'help');
define('USER_PASSWD', 'kqeUzYz6F58gEyu'); //define('USER_PASSWD', 'gaCxakSE4FK2M1I');
define('MAIL_ANSWER', 'Re: [TicketID: ');
define('HOSTNAME', '{mail.volma.ru:993/imap/ssl}INBOX');
define('IMAP_CRITERIA', 'RECENT'); // новые, не просмотренные письма // 'FROM test@volma.ru' //RECENT

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
set_time_limit(0);
require_once($_SERVER['DOCUMENT_ROOT']."/local/php_interface/volma_functions/mail_parser/functions.php");

// Список учитываемых типов файлов
$mail_filetypes = ["DOC", "DOCX", "JPEG", "JPG", "PNG", "GIF", "TXT", "XLS", "XLSX", "CSV", "PDF"];

$connection = imap_open(HOSTNAME, USER_NAME, USER_PASSWD);

$f = fopen ("/home/bitrix/www/logs/local-cron-generate_mail_".date("d-m-Y")."_.log", "a+");
fwrite ($f, "\n"."-------------------------------------------"."\n".date("H:i:s")."\n");
fwrite ($f, '[local/cron/generate_mail_to_support_ticket.php] '."\n");

if(!$connection){
    echo("Ошибка соединения с почтой - ".USER_NAME);
    fwrite($f, "Ошибка соединения с почтой - ".USER_NAME."\n");
    fclose($f);
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
            if($mails_data[$i]["title"] == '')
                $mails_data[$i]["title"] = 'Без темы';

            // Тело письма
            $msg_structure = imap_fetchstructure($connection, $i); //Прочитать структуру указанного сообщения
            $msg_body = imap_fetchbody($connection, $i, 1); //Извлечь конкретную секцию тела сообщения
            $recursive_data = recursive_search($msg_structure);

            $ar_msg_structure = (array)$msg_structure;
            echo '<pre>'; print_r((array)$ar_msg_structure); echo '</pre>';

            if(
                $msg_structure->parts[0] -> subtype == 'ALTERNATIVE'  ||
                $msg_structure->parts[0] -> subtype == 'MIXED'        ||
                $msg_structure->parts[0] -> subtype == 'RELATED'
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
            //вырежем до подписи 'С уважением'
            $delem4 = 'С уважением';
            if (strpos($msg_body, $delem4) !== false) {
                $ex = explode($delem4, $msg_body);
                $msg_body = $ex[0];
            }

            // отладка
            $mails_data[$i]['Subject'] = $msg_header->Subject;
            $arSubj = explode('?',$msg_header->Subject);
            $mails_data[$i]['subject_1'] = $arSubj[1];
            $mails_data[$i]['subject_2'] = $arSubj[2];

            //Иногда в письме нет Subject. Попытаемся достать кодировку из From
            //From: =?koi8-r?B?58/S2MvBxdfBIOXMydrB18XUwSDzxdLHxcXXzsE=?= <vsk-gorkaeva@volma.ru>
            if($arSubj[1] == ''){
                $arFrom = explode('?',$msg_header->From);
                $mails_data[$i]['subject_1'] = $arFrom[1];
            }


            $mails_data[$i]['encoding'] = $recursive_data["encoding"];

            fwrite($f,'title: '.$mails_data[$i]["title"]."\n");


            $bodystruct = imap_bodystruct($connection, $i, 1); //- Прочитать структуру указанной секции тела заданного сообщения
            $body = "";


            if ($recursive_data["encoding"] == 0 || $recursive_data["encoding"] == 1)
                $body = $msg_body;
            if ($recursive_data["encoding"] == 2)
                $body = structure_encoding($recursive_data["encoding"], $msg_body);
            if ($recursive_data["encoding"] == 3)
                $body = structure_encoding($recursive_data["encoding"], $msg_body);
            if ($recursive_data["encoding"] == 4)
                $body = structure_encoding($recursive_data["encoding"], $msg_body);

            //if (!check_utf8($recursive_data["charset"]))  $body = convert_to_utf8($recursive_data["charset"], $msg_body);

            // попробую для вложения
            if($arSubj[2] == 'Q')
                $body = quoted_printable_decode($msg_body);

            $mails_data[$i]["body"] = $body;

            // Вложенные файлы
            if (isset($msg_structure->parts)) {
                fwrite($f,"Есть вложенные файлы"."\n");
                for ($j = 1, $fn = 2; $j < count($msg_structure->parts); $j++, $fn++) {
                    //if(in_array($msg_structure->parts[$j]->subtype, $mail_filetypes)) {
                    if($msg_structure->parts[$j]->subtype <> 'HTML'
                        &&
                        $msg_structure->parts[$j]->subtype <> 'text/html'
                        &&
                        $msg_structure->parts[$j]->subtype <> 'PLAIN'
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
                            imap_fetchbody($connection, $i, $fn) //закодированное тело файла
                        );

                        $mails_data[$i]["attachs"][$j]["tmp_file_name"] = tempnam(sys_get_temp_dir(), 'img_tmp' . iconv("utf-8", "cp1251", $mails_data[$i]["attachs"][$j]["name"]));
                        $mails_data[$i]["attachs"][$j]["tmp_file"] = file_put_contents($mails_data[$i]["attachs"][$j]["tmp_file_name"], $mails_data[$i]["attachs"][$j]["file"]);

                        fwrite($f,"\n".'Вложение '.$j.': '."\n");
                        fwrite($f,'название: '.$mails_data[$i]["attachs"][$j]["name"]."\n");
                        fwrite($f,'тип: '.$mails_data[$i]["attachs"][$j]["type"]."\n");
                        fwrite($f,'размер: '.$mails_data[$i]["attachs"][$j]["size"]."\n");
                    }
                }
            }
            else
                fwrite($f,"\n"."Нет вложенных файлов"."\n");
        }

        if(!empty($mails_data[$i])) {
            if ($mails_data[$i]["to"] !== $mails_data[$i]["from"]) { // если отправитель не равен получателю

                //Определим id пользователя
                $mails_data[$i]["user_id"] = getUserID($mails_data[$i]["from"]);

                //echo '<br>[before addSupportTicket] arFields<pre>'; print_r($mails_data[$i]); echo '</pre>';
                //mariMail(array_merge(['str'=>'206 before addSupportTicket'], $mails_data[$i]));
                //mariLog(array_merge($mails_data[$i], $msg_structure), FALSE, $path='/logs/support_title_'.$mails_data[$i]['title'].date('U'));
                fwrite($f,"\n".'mails_data не пуст => Отдаю в функцию создания обращения: '."\n");
                foreach($mails_data[$i] as $key=>$val) {
                    fwrite($f, $key.':  '.$val . "\n");
                    if ((gettype($val) === 'array') || ($key === 'attachs')){
                        foreach($val as $k=>$v) {
                            if($k !== 'file') {
                                fwrite($f, '  ' . $k . ':  ' . $v . "\n");
                                if (gettype($v) === 'array') {
                                    foreach ($v as $klast => $vlast) {
                                        if($klast !== 'file')
                                            fwrite($f, '  ' . $klast . ':  ' . $vlast . "\n");
                                    }
                                }
                            }
                        }
                    }
                }

                if(addSupportTicket($mails_data[$i]))
                    imap_delete ($connection, $i);

            }
        }
        else
            fwrite($f,"\n".'mails_data пуст'."\n");
        fclose($f);
    }
}

imap_close($connection, CL_EXPUNGE);
//TODO английский title пустой, но перед отправкой его на добавление обращения addSupportTicket($mails_data[$i])
// он уже заполнен, если он на кирилице, пустой если на латинице
//
//TODO нет вложения , заявка 388
//
//TODO В Ответе на письмо-уведомление убрать все после > Пример обращения 389
//
// Подпись. Найти первое вхождение '--' и вырезать все что после него



?>
<?require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');?>
