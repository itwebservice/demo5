<?php
include_once('../model.php');

global $app_email_id, $app_name, $app_contact_no, $admin_logo_url, $app_website;
global $mail_em_style, $mail_font_family, $mail_strong_style, $mail_color;
global $secret_key,$encrypt_decrypt;

$cur_time = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('+2 days'));

$sq_hotel =  mysqlQuery("select * from package_hotel_accomodation_master where date(from_date)='$start_date'");
while($row_hotel=mysqli_fetch_assoc($sq_hotel)){

	$pass_count= mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$row_hotel[booking_id]'"));
	$cancle_count= mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$row_hotel[booking_id]' and status='Cancel'"));
	if($pass_count != $cancle_count){
        $booking_id = $row_hotel['booking_id'];
        $from_date = get_date_user($row_hotel['from_date']);

        $sq_hotel_name = mysqli_fetch_assoc(mysqlQuery("select * from hotel_master where hotel_id='$row_hotel[hotel_id]'"));
        $contact_person_name = $sq_hotel_name['contact_person_name'];
        $hotel_name = $sq_hotel_name['hotel_name'];

        $email_id = $encrypt_decrypt->fnDecrypt($sq_hotel_name['email_id'], $secret_key);
        $sq_booking_details = mysqli_fetch_assoc(mysqlQuery("select * from package_tour_booking_master where booking_id='$booking_id'"));

        $customer_id = $sq_booking_details['customer_id'];
        $tour_name = $sq_booking_details['tour_name'];
        $tour_start_date = get_date_user($sq_booking_details['tour_from_date']);

        $total_pax = mysqli_num_rows(mysqlQuery("select * from package_travelers_details where booking_id='$booking_id' and status='Active'"));
        $sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));

        $email_content = '
        <table style="padding:0 30px">
            <tr>
                <td>
                    <table>
                        <tr>
                            <td>
                                <p style="line-height: 24px;">Hello '.$contact_person_name.',</p>
                            </td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td>
                                <p style="line-height: 24px;">This mail is inform you that '.$sq_cust['first_name'].' '.$sq_cust['last_name'].' with '.$total_pax.' no of pax has been booked your hotel('.$hotel_name.') from '.$from_date.' for '.$tour_name.'. Tour starting from '.$tour_start_date.'. </p>
                                </td>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <td>
                                <p style="line-height: 24px;">
                                    Thank You.<br/><br/>
                                </p>
                                </td>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <td>
                                <p style="line-height: 24px;">
                                    Kind Regards,<br>
                                    <span style="color:'.$mail_color.'">'.$app_name.'</span>
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>';
        global $model;
        $model->app_email_master($email_id, $email_content, 'Tour Reminder');
    }
}
?>