<?php
include "../../../model/model.php";

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once '../../../classes/PHPExcel-1.8/Classes/PHPExcel.php';

//This function generates the background color
function cellColor($cells,$color){
    global $objPHPExcel;

    $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()->applyFromArray(array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array(
             'rgb' => $color
        )
    ));
}

//This array sets the font atrributes
$header_style_Array = array(
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => '000000'),
        'size'  => 12,
        'name'  => 'Verdana'
    ));
$table_header_style_Array = array(
    'font'  => array(
        'bold'  => false,
        'color' => array('rgb' => '000000'),
        'size'  => 11,
        'name'  => 'Verdana'
    ));
$content_style_Array = array(
    'font'  => array(
        'bold'  => false,
        'color' => array('rgb' => '000000'),
        'size'  => 9,
        'name'  => 'Verdana'
    ));

//This is border array
$borderArray = array(
          'borders' => array(
              'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
              )
          )
      );

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                             ->setLastModifiedBy("Maarten Balliauw")
                             ->setTitle("Office 2007 XLSX Test Document")
                             ->setSubject("Office 2007 XLSX Test Document")
                             ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                             ->setKeywords("office 2007 openxml php")
                             ->setCategory("Test result file");

//////////////////////////****************Content start**************////////////////////////////////

$income_type_id = $_GET['income_type_id'];
$from_date = $_GET['from_date'];
$to_date = $_GET['to_date'];
$financial_year_id = $_GET['financial_year_id'];

if($income_type_id!=""){
	$sq_income_type = mysqli_fetch_assoc(mysqlQuery("select * from other_income_type_master where income_type_id='$income_type_id'"));
  $income_str = $sq_income_type['income_type'];
}
else{
	$income_str = "";
}

if($from_date!="" && $to_date!=""){
    $date_str = $from_date.' to '.$to_date;
}
else{
    $date_str = "";
}

if($financial_year_id != ""){ 
    $query_year = mysqli_fetch_assoc(mysqlQuery("Select * from financial_year where financial_year_id='$financial_year_id'")); 
    $fin_year = $query_year['year_name_long'];   
}
else{
    $fin_year = "";
}

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B2', 'Report Name')
            ->setCellValue('C2', 'Office Expense')
            ->setCellValue('B3', 'Income Type')
            ->setCellValue('C3', $income_str)
            ->setCellValue('B4', 'From-To Date')
            ->setCellValue('C4', $date_str)
            ->setCellValue('B5', 'Financial Year')
            ->setCellValue('C5', $fin_year);

$objPHPExcel->getActiveSheet()->getStyle('B2:C2')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B2:C2')->applyFromArray($borderArray);    

$objPHPExcel->getActiveSheet()->getStyle('B3:C3')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B3:C3')->applyFromArray($borderArray);    

$objPHPExcel->getActiveSheet()->getStyle('B4:C4')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B4:C4')->applyFromArray($borderArray);   

$objPHPExcel->getActiveSheet()->getStyle('B5:C5')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B5:C5')->applyFromArray($borderArray);   


$query = "select * from other_income_master where 1 ";
if($from_date!="" && $to_date!=""){
  $from_date = get_date_db($from_date);
  $to_date = get_date_db($to_date);

  $query .= " and payment_date between '$from_date' and '$to_date'";
}
if($income_type_id!=""){
  $query .= " and income_type_id='$income_type_id' ";
}
if($financial_year_id!=""){
  $query .=" and financial_year_id='$financial_year_id'";
}

$row_count = 7;

$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, "Sr. No")
        ->setCellValue('C'.$row_count, "Income Type")
        ->setCellValue('D'.$row_count, "Date")
        ->setCellValue('E'.$row_count, "Mode")
        ->setCellValue('F'.$row_count, "Narration")
        ->setCellValue('G'.$row_count, "Amount");
         
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':G'.$row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':G'.$row_count)->applyFromArray($borderArray);    

$row_count++;
$count = 0;
$sq_pending_amount=0;
$sq_cancel_amount=0;
$sq_paid_amount=0;
$Total_payment=0;
$sq_income = mysqlQuery($query);
while($row_income = mysqli_fetch_assoc($sq_income)){

  $sq_income_type_info = mysqli_fetch_assoc(mysqlQuery("select * from other_income_type_master where income_type_id='$row_income[income_type_id]'"));

  $sq_paid_amount = $sq_paid_amount + $row_income['payment_amount'];
  if($row_income['clearance_status']=="Pending"){ 
    $sq_pending_amount = $sq_pending_amount + $row_income['payment_amount'];
  }
  else if($row_income['clearance_status']=="Cancelled"){ 
    $sq_cancel_amount = $sq_cancel_amount + $row_income['payment_amount'];
  }

	$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, ++$count)
        ->setCellValue('C'.$row_count, $sq_income_type_info['income_type'])
        ->setCellValue('D'.$row_count, get_date_user($row_income['payment_date']))
        ->setCellValue('E'.$row_count, $row_income['payment_mode'])
        ->setCellValue('F'.$row_count, $row_income['particular'])
        ->setCellValue('G'.$row_count, $row_income['payment_amount']);

  $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':G'.$row_count)->applyFromArray($content_style_Array);
	$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':G'.$row_count)->applyFromArray($borderArray);    

		$row_count++;

    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, "")
        ->setCellValue('C'.$row_count, "")
        ->setCellValue('D'.$row_count, 'Paid Amount : '.number_format($sq_paid_amount,2))
        ->setCellValue('E'.$row_count, 'Pending Amount : '.number_format($sq_pending_amount,2))
        ->setCellValue('F'.$row_count, 'Cancellation Charges : '.number_format($sq_cancel_amount,2))
        ->setCellValue('G'.$row_count, 'Payment Amount :'.number_format(($sq_paid_amount - $sq_pending_amount - $sq_cancel_amount),2));

$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':G'.$row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':G'.$row_count)->applyFromArray($borderArray);

}
	

//////////////////////////****************Content End**************////////////////////////////////
	

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Simple');


for($col = 'A'; $col !== 'N'; $col++) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
}


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Other Income('.date('d-m-Y H:i').').xls"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
