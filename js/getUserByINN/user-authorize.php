<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

global $USER;
if (!$USER->IsAuthorized()) {
    foreach ($_POST as $key=>$val) {
        $filter[$key] = $val;
    }
    $params = ["SELECT" => ['ID', 'EMAIL', 'INN']];
    $rsUser = CUser::GetList(($by = "NAME"), ($order = "desc"), $filter, $params);

    while ($arUser = $rsUser->Fetch()) {
        if ($USER->Authorize($arUser['ID'])) {
            $arr = ['status' => 'success', 'text' => 'id = ' . $arUser['ID'] . ' Такой user уже есть в системе. Пользователь успешно авторизован.'];
        } else {
            $arr = ['status' => 'error', 'text' => 'not authorized'];
        }
        echo json_encode($arr);
    }
}
else echo json_encode(['status' => 'error', 'text' => 'user already authorized']);
