<?
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

	$request = \Bitrix\Main\Context::getCurrent()->getRequest()->toArray();
	
	$result = json_encode(array(
		'error' => 'error #1'
	));
	
	if(isset($request['inn']) && (strlen(trim($request['inn'])) == 10 || strlen(trim($request['inn'])) == 12)  ) {
	
		$result = Dadata::suggest("party", array("query" => trim($request['inn']), "count" => 1));
	
	}
	
	echo $result;
