<?php
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

	global $USER;

	if($USER->IsAuthorized()) {

		$fio = $USER->GetLastName().' '.$USER->GetFirstName().' '.$USER->GetSecondName();

		$rsUser = CUser::GetByID($USER->GetID());
		$arUser = $rsUser->Fetch();
		if(in_array('6',$USER-> GetUserGroupArray())) //fiz
			$address = $arUser['PERSONAL_STATE'].' '.$arUser['PERSONAL_CITY'].' '.$arUser['PERSONAL_ZIP'].' '.$arUser['PERSONAL_STREET'].' '.$arUser['PERSONAL_MAILBOX'].' '.$arUser['PERSONAL_NOTES'];		
		if(in_array('7',$USER-> GetUserGroupArray()))// ur	
			$address = $arUser['UF_U_ADDRESS'];

		echo json_encode([
			'status'=>'success', 
			'text'=>'User is Authorized.', 
			'fio'=>$fio,
			'address'=>trim($address), 
			'companyName'=>$arUser['UF_COMPANY_NAME'],
			'contactFace'=>$arUser['UF_K_FACE'],
			'ogrn'=>$arUser['UF_OGRN'],
			'okpo'=>$arUser['UF_OKPO'],
			'inn'=>$arUser['UF_INN'],
			'kpp'=>$arUser['UF_KPP'],
			'companyRuk'=>$arUser['UF_RUK'],
			
			'ogrn'=>$arUser['UF_OGRN'],
			
			'all' =>$arUser,
			]);
	}
	else 
		echo json_encode(['status'=>'error', 'text'=>'User is NOT Authorized.']);
