<?php
include "../../../../model/model.php";

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once '../../../../classes/PHPExcel-1.8/Classes/PHPExcel.php';

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

$emp_id = $_SESSION['emp_id'];
$role = $_SESSION['role'];
$role_id = $_SESSION['role_id'];
$branch_admin_id = $_SESSION['branch_admin_id'];
$branch_status = $_GET['branch_status'];
$customer_id = $_GET['customer_id'];
$booking_id = $_GET['booking_id'];
$from_date = $_GET['from_date'];
$to_date = $_GET['to_date'];
$cust_type = $_GET['cust_type'];
$company_name = $_GET['company_name'];
$financial_year_id = $_SESSION['financial_year_id'];

$sql_booking_date = mysqli_fetch_assoc(mysqlQuery("select * from bus_booking_master where booking_id = '$booking_id'")) ;
$booking_date = $sql_booking_date['created_at'];
$yr = explode("-", $booking_date);
$year =$yr[0];

if($customer_id!=""){
	$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
    if($sq_customer_info['type']=='Corporate'||$sq_customer_info['type'] == 'B2B'){
        $cust_name = $sq_customer_info['company_name'];
    }else{
        $cust_name = $sq_customer_info['first_name'].' '.$sq_customer_info['last_name'];
    }
}
else{
	$cust_name = "";
}

if($from_date!="" && $to_date!=""){
    $date_str = $from_date.' to '.$to_date;
}
else{
    $date_str = "";
}

$invoice_id = ($booking_id!="") ? get_bus_booking_id($booking_id,$year): "";
if($company_name == 'undefined') { $company_name = ''; }
// Add some data
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B2', 'Report Name')
            ->setCellValue('C2', 'Bus Report')
            ->setCellValue('B3', 'Booking ID')
            ->setCellValue('C3', $invoice_id)
            ->setCellValue('B4', 'Customer')
            ->setCellValue('C4', $cust_name)
            ->setCellValue('B5', 'From-To Date')
            ->setCellValue('C5', $date_str)
            ->setCellValue('B6', 'Customer Type')
            ->setCellValue('C6', $cust_type)
            ->setCellValue('B7', 'Company Name')
            ->setCellValue('C7', $company_name);

$objPHPExcel->getActiveSheet()->getStyle('B2:C2')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B2:C2')->applyFromArray($borderArray);    

$objPHPExcel->getActiveSheet()->getStyle('B3:C3')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B3:C3')->applyFromArray($borderArray);    

$objPHPExcel->getActiveSheet()->getStyle('B4:C4')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B4:C4')->applyFromArray($borderArray);   

$objPHPExcel->getActiveSheet()->getStyle('B5:C5')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B5:C5')->applyFromArray($borderArray);   

$objPHPExcel->getActiveSheet()->getStyle('B6:C6')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B6:C6')->applyFromArray($borderArray);

$objPHPExcel->getActiveSheet()->getStyle('B7:C7')->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B7:C7')->applyFromArray($borderArray);

$query = "select * from bus_booking_entries where 1 ";
if($booking_id!=""){
    $query .= " and booking_id='$booking_id'";
}
if($customer_id!=""){
    $query .= " and booking_id in ( select booking_id from bus_booking_master where customer_id='$customer_id' )";
}
if($from_date!='' && $to_date!=''){
            $from_date = get_date_db($from_date);
            $to_date = get_date_db($to_date);
            $query .=" and booking_id in ( select booking_id from bus_booking_master where created_at between '$from_date' and '$to_date')";
}
if($cust_type != ""){
    $query .= " and booking_id in (select booking_id from bus_booking_master where customer_id in ( select customer_id from customer_master where type='$cust_type' ))";
}
if($company_name != ""){
    $query .= " and booking_id in (select booking_id from bus_booking_master where customer_id in ( select customer_id from customer_master where company_name='$company_name' ))";
}
if($branch_status=='yes'){
	if($role=='Branch Admin' || $role=='Accountant' || $role_id>'7'){
		$query .= " and booking_id in (select booking_id from bus_booking_master where branch_admin_id ='$branch_admin_id')";
	}
	elseif($role!='Admin' && $role!='Branch Admin' && $role_id!='7' && $role_id<'7'){
		$query .= " and booking_id in (select booking_id from bus_booking_master where emp_id ='$emp_id' and branch_admin_id ='$branch_admin_id')";
	}
}
elseif($role!='Admin' && $role!='Branch Admin' && $role_id!='7' && $role_id<'7'){
	$query .= " and booking_id in (select booking_id from bus_booking_master where emp_id ='$emp_id')";
}
if($financial_year_id != ''){
	$query .= " and booking_id in (select booking_id from bus_booking_master where financial_year_id='$financial_year_id')";
}
$row_count = 9;
         
$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, "Sr. No")
        ->setCellValue('C'.$row_count, "Booking ID")
        ->setCellValue('D'.$row_count, "Customer Name")
        ->setCellValue('E'.$row_count, "Bus Operator")
        ->setCellValue('F'.$row_count, "Bus Type")
        ->setCellValue('G'.$row_count, "Source-Destination")
        ->setCellValue('H'.$row_count, "Journey Date&Time")
        ->setCellValue('I'.$row_count, "Reporting Time");
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':I'.$row_count)->applyFromArray($header_style_Array);
$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':I'.$row_count)->applyFromArray($borderArray);    

        $row_count++;
        $count = 0;
        $sq_entry = mysqlQuery($query);
        while($row_entry = mysqli_fetch_assoc($sq_entry)){

            $sq_bus = mysqli_fetch_assoc(mysqlQuery("select * from bus_booking_master where booking_id='$row_entry[booking_id]'"));
            $date = $sq_bus['created_at'];
            $yr = explode("-", $date);
            $year =$yr[0];
            $sq_customer = mysqli_fetch_assoc(mysqlQuery("select first_name, last_name,type,company_name from customer_master where customer_id='$sq_bus[customer_id]'"));
			if($sq_customer['type'] == 'Corporate'||$sq_customer['type'] == 'B2B'){
				$customer_name = $sq_customer['company_name'];
			}else{
				$customer_name = $sq_customer['first_name'].' '.$sq_customer['last_name'];
			}


	$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('B'.$row_count, ++$count)
        ->setCellValue('C'.$row_count, get_bus_booking_id($row_entry['booking_id'],$year))
        ->setCellValue('D'.$row_count, $customer_name)
        ->setCellValue('E'.$row_count, $row_entry['company_name'])
        ->setCellValue('F'.$row_count, $row_entry['bus_type'])
        ->setCellValue('G'.$row_count, $row_entry['origin'].' -- '.$row_entry['destination'])
        ->setCellValue('H'.$row_count, get_datetime_user($row_entry['date_of_journey']))
        ->setCellValue('I'.$row_count, $row_entry['departure_time'].' '.$row_entry['reporting_time']);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':I'.$row_count)->applyFromArray($content_style_Array);
	$objPHPExcel->getActiveSheet()->getStyle('B'.$row_count.':I'.$row_count)->applyFromArray($borderArray);    



		$row_count++;

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
header('Content-Disposition: attachment;filename="Bus Report('.date('d-m-Y H:i').').xls"');
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
