<?
class Dadata
{
    public function suggest($type, $fields)
    {
		$cache = new CPHPCache();
		$cache_time = 3600 * 24;
		$cache_id = md5((string)json_encode($fields) . $type);
		$cache_path = '/dadata/';
		
		if($cache->InitCache($cache_time, $cache_id, $cache_path)) {
			$res = $cache->GetVars();
			$result = @$res['result'];
		}
		else {
			$result = false;
			if ($ch = curl_init("http://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/$type"))
			{
				 curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				 curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					 'Content-Type: application/json',
					 'Accept: application/json',
					 'Authorization: Token 1011a937b39cabb2543acdec7823b9aa82be8e88'
				  ));
				 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
				 curl_setopt($ch, CURLOPT_POST, 1);
				 // json_encode
				 curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
				 $result = curl_exec($ch);
				 //$result = json_decode($result, true);
				 curl_close($ch);
			}
			$cache->StartDataCache($cache_time, $cache_id, $cache_path);
			$cache->EndDataCache(array("result" => $result));
		}	
        return $result;
    }
}
