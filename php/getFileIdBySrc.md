## Получить из базы ID файла, зная его src

Простой способ получить ID картинки зная лишь путь к файлу (/upload/iblock/212/21232121288522265c927d1df55305f8.jpg)
Обратная функция CFile::GetByID.

###Готового способа нет, поэтому функция выполняет несколько действий:
 * определяет путь к файлу без начального /upload/,
 * ищет в БД файл, который лежит по такому пути (в БД подпапка iblock/212 и имя файла 21232121288522265c927d1df55305f8.jpg хранятся в разных полях, поэтому применяется CONCAT).


```
function getFileIdBySrc($strFilename){
	$strUploadDir = '/'.\Bitrix\Main\Config\Option::get('main', 'upload_dir').'/';
	echo '<br>strUploadDir = '.$strUploadDir;
	$strFile = substr($strFilename, strlen($strUploadDir));
	echo '<br>strFile = '.$strFile;
	$strSql = "SELECT ID FROM b_file WHERE CONCAT(SUBDIR, '/', FILE_NAME) = '{$strFile}'";
	return \Bitrix\Main\Application::getConnection()->query($strSql)->fetch()['ID'];
}

$strFilename = '/upload/medialibrary/2d3/2d3wewewewewewewew.jpg';
$imgId[] = getFileIdBySrc($strFilename);

```
