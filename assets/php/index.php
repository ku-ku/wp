<?php
$index = __DIR__ . '/app/index.html';
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("План основных мероприятих Законодательного собрания");
global $USER;
$isAdmin = $USER->IsAdmin();
if ($isAdmin){
    $APPLICATION->ShowPanel = false;
}


if ( file_exists($index) ){
   header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($index)).' GMT', true, 200);
   $c = file_get_contents($index, false);
   preg_match("/<head[^>]*>(.*?)<\/head>/is", $c, $matches);
   $APPLICATION->AddHeadString($matches[1], true, true);
   preg_match("/<body[^>]*>(.*?)<\/body>/is", $c, $matches);
   echo $matches[1];
} else {
	echo 'App is`t installed';
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>