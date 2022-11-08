<?php
require_once "../phpdoc/vendor/autoload.php";

$phpWord = new \PhpOffice\PhpWord\PhpWord();
$phpWord->setDefaultFontName('Times New Roman');
$phpWord->setDefaultFontSize(12);

$info = json_decode(file_get_contents('php://input'));

//print_r($info);
$phpWord->addTitleStyle(1, ['bold' => true, 'size' => 14], ['spaceAfter' => 240, 'contextualSpacing' => true, 'alignment'=>\PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

$section = $phpWord->addSection(['orientation'=>'landscape']);
$section->addTitle('ПЛАН', 1);
$section->addTitle('основных мероприятий Законодательного Собрания Краснодарского края', 1);
$section->addTitle(sprintf('с %s по %s года', $info->start, $info->end), 1);

$cellCentered = ['valign' => 'center', 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];
$cellColSpan = ['gridSpan' => 5, 'valign' => 'center', 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];
$table = $section->addTable([
    'width' => 100 * 50, 
    'unit' => 'pct', 
    'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
    'borderSize' => 1, 
    'borderColor' => '999999'
]);
$row = $table->addRow();
$row->addCell(360)->addTextRun($cellCentered)->addText('Дата,'.PHP_EOL.'время', ["bold" => true]);
$row->addCell(2655)->addTextRun($cellCentered)->addText('Мероприятия', ["bold" => true]);
$row->addCell(595)->addTextRun($cellCentered)->addText('Место'.PHP_EOL.'проведения', ["bold" => true]);
$row->addCell(695)->addTextRun($cellCentered)->addText('Руководитель', ["bold" => true]);
$row->addCell(695)->addTextRun($cellCentered)->addText('Ответственные за'.PHP_EOL.'подготовку', ["bold" => true]);

foreach($info->days as $day){
    $row = $table->addRow();
    $cell = $table->addCell(5000, $cellColSpan);
    $cell->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 120])->addText($day->day->day, ["bold" => true]);
    foreach($day->reds as $red){
        $cell->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER])->addText($red->name, ["underline"=>"single"]);
    }
    foreach($day->acts as $a){
        $row = $table->addRow();
        $row->addCell(360)->addTextRun($cellCentered)->addText($a->tm);
        $row->addCell(2655)->addText($a->name);
        $row->addCell(595)->addText($a->place);
        $row->addCell(695)->addText($a->chief);
        $row->addCell(695); //->addText($a->CHIEF_NAME);
    }
}

$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$file = 'plan.docx';
header("Content-Description: File Transfer");
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');
$objWriter->save("php://output");
?>