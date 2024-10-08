<?php
require_once "../phpdoc/vendor/autoload.php";

$phpWord = new \PhpOffice\PhpWord\PhpWord();
$phpWord->setDefaultFontName('Times New Roman');
$phpWord->setDefaultFontSize(12);
$phpWord->setDefaultParagraphStyle(array(
        'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(0),
        'spacing' => 120,
        'lineHeight' => 1,
    )
);

$info = json_decode(file_get_contents('php://input'));

//print_r($info);
$phpWord->addTitleStyle(1, ['bold' => true, 'size' => 14], ['spaceAfter' => 240, 'contextualSpacing' => true, 'alignment'=>\PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

$section = $phpWord->addSection([
    'orientation'=>'landscape',
    'marginTop' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2),
    'marginLeft' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1.75),
    'marginRight' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1.75),
    'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1.7)
]);
$section->addTitle('ПЛАН', 1);
$section->addTitle('основных мероприятий Законодательного Собрания Краснодарского края', 1);
$section->addTitle(sprintf('с %s по %s года', $info->start, $info->end), 1);

$cellCentered = ['valign' => 'center', 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];
$cellColSpan = ['gridSpan' => 5, 'valign' => 'center', 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];
$cellJustify = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH];

$table = $section->addTable([
    'width' => 100 * 50, 
    'unit' => 'pct', 
    'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
    'layout' => 'fixed',
    'borderSize' => 1, 
    'borderColor' => '999999',
    'textProperties' => [
        'lineHeight' => 1,
        'contextualSpacing' => true
    ]
]);
$row = $table->addRow();
$row->addCell(385)->addTextRun($cellCentered)->addText('Дата,'.PHP_EOL.'время', ["bold" => true]);
$row->addCell(2212)->addTextRun($cellCentered)->addText('Мероприятия', ["bold" => true]);
$row->addCell(769)->addTextRun($cellCentered)->addText('Место'.PHP_EOL.'проведения', ["bold" => true]);
$row->addCell(865)->addTextRun($cellCentered)->addText('Руководитель', ["bold" => true]);
$row->addCell(769)->addTextRun($cellCentered)->addText('Ответственные за'.PHP_EOL.'подготовку', ["bold" => true]);

foreach($info->days as $day){
    $row = $table->addRow();
    $cell = $table->addCell(5000, $cellColSpan);
    $cell->addTextRun()->addText(" ", ["size" => 8]);
    $cell->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER])->addText($day->day->day, ["bold" => true]);
    foreach($day->reds as $red){
        $cell->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER])->addText($red->name, ["underline"=>"single", "italic"=>true, "size"=>12]);
    }
    $cell->addTextRun()->addText(" ", ["size" => 8]);
    foreach($day->acts as $a){
        $row = $table->addRow();
        $row->addCell(385)->addTextRun($cellCentered)->addText($a->tm);
        $row->addCell(2212)->addTextRun($cellJustify)->addText($a->name);
        $row->addCell(769)->addText($a->place);
        
        $cell = $row->addCell(865);
        if ( strlen($a->chief)>0 ){
            $ar = explode(",", $a->chief);
            if (count($ar)>0){
                foreach($ar as $e){
                    $cell->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT])->addText(trim($e));
                }
            }
        }
        
        $cell = $row->addCell(769);
        if ( strlen($a->emps)>0 ){
            $ar = explode(",", $a->emps);
            if (count($ar)>0){
                foreach($ar as $e){
                    $cell->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT])->addText(trim($e));
                }
            }
        }
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
