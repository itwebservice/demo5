<?php
$flag = true;
class hotel_master{

  public function vendor_csv_save(){

    $vendor_csv_dir = $_POST['vendor_csv_dir'];
    $created_at = date('Y-m-d');
    $flag = true;

    $vendor_csv_dir = explode('uploads', $vendor_csv_dir);
    $vendor_csv_dir = BASE_URL.'uploads'.$vendor_csv_dir[1];
    $timestamp = date('U');
    
    begin_t();
    $count = 1;
    $validCount=0;
    $invalidCount=0;
    $unprocessedArray=array();
    $arrResult  = array();
    $handle = fopen($vendor_csv_dir, "r");
    global $encrypt_decrypt, $secret_key;
    if(empty($handle) === false){
        while(($data = fgetcsv($handle,5000, ",")) !== FALSE){
            if($count == 1){ $count++; continue; }
            
            if($count>0){

                $city_id = $data[0];
                $hotel_name = $data[1];
                $mobile = $data[2];
                $landline = $data[3];
                $email = $data[4];
                $contact_person = $data[5];
                $emergency_contact = $data[6];
                $hotel_address = $data[7];
                $state_id= $data[8];
                $country = $data[9];
                $website = $data[10];
                $bank_name = $data[11];
                $account_name = $data[12];
                $account_no = $data[13];
                $branch = $data[14];
                $ifsc_code = $data[15];
                $gst_no = $data[16];
                $supp_pan = $data[17];
                $opening_balance = '';
                $as_on_date = '';
                $hotel_type = $data[18];
                $side = '';     
                $cwob_from = $data[19];
                $cwob_to = $data[20];           
                $cwb_from = $data[21];
                $cwb_to = $data[22];           
                $meal_plan = $data[23];
                $description = $data[24];
                
                $as_on_date = get_date_db($as_on_date);
                if($cwb_from == 0 || $cwb_from == ''){
                  $invalidCount++;
                  array_push($unprocessedArray, $data);
                }
                else if($cwb_to == 0 || $cwb_to == ''){
                  $invalidCount++;
                  array_push($unprocessedArray, $data);
                }
                else if($cwob_from == 0 || $cwob_from == ''){
                  $invalidCount++;
                  array_push($unprocessedArray, $data);
                }
                else if($cwob_to == 0 || $cwob_to == ''){
                  $invalidCount++;
                  array_push($unprocessedArray, $data);
                }
                else if($cwb_from > $cwb_to||$cwob_to == $cwb_from){
                  $invalidCount++;
                  array_push($unprocessedArray, $data);
                }
                else if($cwob_from > $cwob_to){
                  $invalidCount++;
                  array_push($unprocessedArray, $data);
                }
                else if($cwob_to > $cwb_from){
                  $invalidCount++;
                  array_push($unprocessedArray, $data);
                }
                else{
                  if(preg_match('/^[0-9]*$/', $city_id) && preg_match('/^[0-9 \s]{6,20}+$/', $mobile) && preg_match('/^[0-9]*$/', $state_id) && (!strlen($mobile)<10) && (strlen($mobile)<=20) && !empty($cwb_from) && !empty($cwb_to) && !empty($cwob_from) && !empty($cwob_to) ){
                    
                      $mobile1 = $encrypt_decrypt->fnEncrypt($mobile, $secret_key);
                      $hotel_name = addslashes($hotel_name);
                      $hotel_name_count = mysqli_num_rows(mysqlQuery("select hotel_name from hotel_master where hotel_name='$hotel_name' and city_id='$city_id' and mobile_no='$mobile1'"));

                      if($hotel_name_count == 0){

                          $validCount++;
                          $email = $encrypt_decrypt->fnEncrypt($email, $secret_key);
                          $sq_max_id = mysqli_fetch_assoc(mysqlQuery("select max(hotel_id) as max from hotel_master"));
                          $hotel_id = $sq_max_id['max']+1;
                          $hotel_name = addslashes($hotel_name);
                          
                          //Login save
                          $vendor_login_master = new vendor_login_master;
                          $vendor_login_master->vendor_login_save($hotel_name, $mobile1, 'Hotel Vendor',$hotel_id, 'Active', $email,$opening_balance,$side,$as_on_date);
                          
                          $query = "insert into hotel_master ( hotel_id, city_id, hotel_name, mobile_no, landline_no, email_id, contact_person_name, immergency_contact_no, hotel_address, country, website, opening_balance, rating_star,active_flag , bank_name, account_name ,account_no, branch, ifsc_code, service_tax_no, state_id,side,pan_no,as_of_date, `cwb_from`, `cwb_to`, `cwob_from`, `cwob_to`,`meal_plan`,`description`) values ('$hotel_id', '$city_id', '$hotel_name', '$mobile1', '$landline', '$email', '$contact_person', '$emergency_contact', '$hotel_address', '$country', '$website', '$opening_balance', '$hotel_type', 'Active','$bank_name','$account_name','$account_no','$branch','$ifsc_code','$gst_no','$state_id','$side','$supp_pan','$as_on_date', '$cwb_from', '$cwb_to', '$cwob_from', '$cwob_to','$meal_plan','$description')";
                          $sq_enquiry = mysqlQuery($query);
                          if($sq_enquiry){
                          }   
                          else{
                            $flag = false;
                            echo "error--Supplier Information Not Saved.";
                            //exit;
                          }
                      }
                      else{
                        $invalidCount++;
                        array_push($unprocessedArray, $data);
                      }
                  }
                else{
                  $invalidCount++;
                  array_push($unprocessedArray, $data);
                }
              }
            }
            $count++;  
        }
        fclose($handle);
        if(isset($unprocessedArray) && !empty($unprocessedArray))
        {
          $filePath='../../download/unprocessed_hotel_records'.$created_at.''.$timestamp.'.csv';
          $save = preg_replace('/(\/+)/','/',$filePath);
          $downloadurl='../../../download/unprocessed_hotel_records'.$created_at.''.$timestamp.'.csv';
          header("Content-type: text/csv ; charset:utf-8");
          header("Content-Disposition: attachment; filename=file.csv");
          header("Pragma: no-cache");
          header("Expires: 0");
          $output = fopen($save, "w");  
          fputcsv($output, array('city_id' , 'Hotel_name' , 'Mobile' , 'landline' , 'Email' , 'Contact_Person' , 'Emergency_Contact_No' ,'Hotel_address', 'state_id' , 'Country' , 'Website' , 'Bank_Name' , 'Account_Name' , 'Account_No' , 'Branch' , 'IFSC_swift_Code' , 'Tax_No', 'PAN_TAN_No' , 'Hotel Category' ,'Child Without Bed From Age','Child Without Bed To Age','Child With Bed From Age','Child With Bed To Age','Meal Plan','Hotel Description'));   
          
          foreach($unprocessedArray as $row){
            fputcsv($output, $row);
          }
          fclose($output); 
          echo "<script> window.location ='$downloadurl'; </script>";  
        } 
    }

    if($flag){
      commit_t();
      if($validCount>0){
        echo  $validCount." Records successfully imported<br>
        ".$invalidCount." Records failed.";
      }
      else{
        echo "No Supplier information imported";
      }
      exit;
    }
    else{
      rollback_t();
      exit;
    }
  }

///////////////////////***Hotel Master save start*********//////////////
function hotel_master_save($city_id, $hotel_name, $mobile_no, $landline_no, $email_id,$email_id_1,$email_id_2, $contact_person_name, $immergency_contact_no, $hotel_address, $country, $website, $opening_balance,$rating_star, $active_flag, $bank_name,$account_name,$account_no,$branch, $ifsc_code, $service_tax_no ,$state,$side,$supp_pan,$hotel_image_path,$as_of_date,$description,$policies,$amenities,$hotel_type,$meal_plan,$cwb_from,$cwb_to,$cwob_from,$cwob_to){

  $city_id = mysqlREString($city_id);
  $mobile_no = mysqlREString($mobile_no);
  $email_id = mysqlREString($email_id);
  $email_id_1 = mysqlREString($email_id_1);
  $email_id_2 = mysqlREString($email_id_2);
  $contact_person_name = mysqlREString($contact_person_name);
  $immergency_contact_no = mysqlREString($immergency_contact_no);
  $hotel_address = mysqlREString($hotel_address);
  $country = mysqlREString($country);
  $website = mysqlREString($website);
  $opening_balance = mysqlREString($opening_balance);
  $rating_star = mysqlREString($rating_star);
  $active_flag = mysqlREString($active_flag);
  $bank_name = mysqlREString($bank_name);
  $account_name = mysqlREString($account_name);
  $account_no = mysqlREString($account_no);
  $branch = mysqlREString($branch);
  $ifsc_code = mysqlREString($ifsc_code);
  $service_tax_no = mysqlREString($service_tax_no);
  $state = mysqlREString($state);
  $side = mysqlREString($side);
  $supp_pan = mysqlREString($supp_pan);
  $hotel_image_path = mysqlREString($hotel_image_path);
  $as_of_date = mysqlREString($as_of_date);
  $as_of_date = get_date_db($as_of_date);
  $description = mysqlREString($description);
  $policies = mysqlREString($policies);
  $amenities = mysqlREString($amenities);
  $hotel_type = mysqlREString($hotel_type);
  $meal_plan = mysqlREString($meal_plan);
  $cwb_from = mysqlREString($cwb_from);
  $cwb_to = mysqlREString($cwb_to);
  $cwob_from = mysqlREString($cwob_from);
  $cwob_to = mysqlREString($cwob_to);

  global $encrypt_decrypt, $secret_key;
  $mobile_no = $encrypt_decrypt->fnEncrypt($mobile_no, $secret_key);
  $email_id = $encrypt_decrypt->fnEncrypt($email_id, $secret_key);
  $email_id_1 = $encrypt_decrypt->fnEncrypt($email_id_1, $secret_key);
  $email_id_2 = $encrypt_decrypt->fnEncrypt($email_id_2, $secret_key);
  begin_t();

  $hotel_name = addslashes($hotel_name);
  $hotel_name1 = ltrim($hotel_name);
  $hotel_name_count = mysqli_num_rows(mysqlQuery("select hotel_name from hotel_master where hotel_name='$hotel_name1' and city_id='$city_id'  and mobile_no='$mobile_no'"));

  if($hotel_name_count>0){
    echo "error--Hotel name already exist in this city!";
    exit;
  }
  
  $max_hotel_id1 = mysqli_fetch_assoc(mysqlQuery("select max(hotel_id) as max from hotel_master"));
  $max_hotel_id = $max_hotel_id1['max']+1;
  
  $sq = mysqlQuery("insert into hotel_master ( hotel_id, city_id, hotel_name, mobile_no, landline_no, email_id,alternative_email_1,alternative_email_2, contact_person_name, immergency_contact_no, hotel_address, country, website, opening_balance, rating_star,meal_plan,hotel_type, bank_name,account_name, account_no, branch, ifsc_code, service_tax_no,active_flag, state_id,side,pan_no,as_of_date,description,policies,amenities, `cwb_from`, `cwb_to`, `cwob_from`, `cwob_to`) values ( '$max_hotel_id', '$city_id', '$hotel_name', '$mobile_no', '$landline_no', '$email_id', '$email_id_1', '$email_id_2', '$contact_person_name', '$immergency_contact_no', '$hotel_address', '$country', '$website', '$opening_balance','$rating_star','$meal_plan','$hotel_type', '$bank_name','$account_name','$account_no','$branch','$ifsc_code', '$service_tax_no', '$active_flag','$state','$side','$supp_pan','$as_of_date','$description','$policies','$amenities', '$cwb_from', '$cwb_to', '$cwob_from', '$cwob_to')");

  if(!$sq){
    rollback_t();
    echo "error--Hotel supplier details not saved!";
    exit;
  }
  else{
    //Login save
    $vendor_login_master = new vendor_login_master;
    $result = $vendor_login_master->vendor_login_save($hotel_name, $mobile_no, 'Hotel Vendor',$max_hotel_id, $active_flag, $email_id,$opening_balance,$side,$as_of_date);

    $hotel_image_array = explode(",",$hotel_image_path);
    for($i=0; $i<sizeof($hotel_image_array);$i++)
    {
        $sq_count=mysqli_num_rows(mysqlQuery("select * from hotel_vendor_images_entries where hotel_id='$max_hotel_id'"));
        if($sq_count<10)
        {
          if($hotel_image_array[$i] != ''){
            $max_img_entry_id = mysqli_fetch_assoc(mysqlQuery("select max(id) as max from hotel_vendor_images_entries"));
            $max_entry_id = $max_img_entry_id['max']+1;
            $sq_img = mysqlQuery("INSERT INTO `hotel_vendor_images_entries`(`id`, `hotel_id`, `hotel_pic_url`) VALUES ('$max_entry_id','$max_hotel_id','$hotel_image_array[$i]')");
          }
        }
        else{
          echo "error--Sorry,You can Upload upto 10 images.";
        }
    }

    if($GLOBALS['flag']){
      commit_t();
      echo "Hotel supplier has been successfully saved.";
      exit;
    }
    else{
      rollback_t();
      exit;

    }
  }
}

///////////////////////***Hotel Master save end*********//////////////

///////////////////////***Hotel Master update start*********//////////////
function hotel_master_update( $hotel_id, $vendor_login_id, $city_id, $hotel_name, $mobile_no, $landline_no, $email_id,$email_id_1,$email_id_2, $contact_person_name, $immergency_contact_no, $hotel_address, $country, $website, $opening_balance,$rating_star, $active_flag, $bank_name,$account_name ,$account_no, $branch, $ifsc_code, $service_tax_no,$state,$side1,$supp_pan,$as_of_date,$description,$policies,$amenities,$hotel_type,$meal_plan,$cwb_from,$cwb_to,$cwob_from,$cwob_to)
{
  $city_id = mysqlREString($city_id);
  $mobile_no = mysqlREString($mobile_no);
  $email_id = mysqlREString($email_id);
  $email_id_1 = mysqlREString($email_id_1);
  $email_id_2 = mysqlREString($email_id_2);
  $contact_person_name = mysqlREString($contact_person_name);
  $immergency_contact_no = mysqlREString($immergency_contact_no);
  $hotel_address = mysqlREString($hotel_address);
  $country = mysqlREString($country);
  $website = mysqlREString($website);
  $opening_balance = mysqlREString($opening_balance);
  $rating_star = mysqlREString($rating_star);
  $active_flag = mysqlREString($active_flag);
  $bank_name = mysqlREString($bank_name);
  $account_name = mysqlREString($account_name);
  $account_no = mysqlREString($account_no);
  $branch = mysqlREString($branch);
  $ifsc_code = mysqlREString($ifsc_code);
  $service_tax_no = mysqlREString($service_tax_no);
  $state = mysqlREString($state);
  $side1 = mysqlREString($side1);
  $supp_pan = mysqlREString($supp_pan);
  $as_of_date = mysqlREString($as_of_date);
  $as_of_date = get_date_db($as_of_date);
  $description = mysqlREString($description);
  $policies = mysqlREString($policies);
  $amenities = mysqlREString($amenities);
  $hotel_type = mysqlREString($hotel_type);
  $meal_plan = mysqlREString($meal_plan);
  $cwb_from = mysqlREString($cwb_from);
  $cwb_to = mysqlREString($cwb_to);
  $cwob_from = mysqlREString($cwob_from);
  $cwob_to = mysqlREString($cwob_to);
  global $encrypt_decrypt, $secret_key;
  $mobile_no = $encrypt_decrypt->fnEncrypt($mobile_no, $secret_key);
  $email_id = $encrypt_decrypt->fnEncrypt($email_id, $secret_key);
  $email_id_1 = $encrypt_decrypt->fnEncrypt($email_id_1, $secret_key);
  $email_id_2 = $encrypt_decrypt->fnEncrypt($email_id_2, $secret_key);
  begin_t();

  $hotel_name = addslashes($hotel_name);
  $sq = mysqlQuery("update hotel_master set city_id='$city_id', hotel_name='$hotel_name', mobile_no='$mobile_no', landline_no='$landline_no', email_id='$email_id', alternative_email_1='$email_id_1', alternative_email_2='$email_id_2', contact_person_name='$contact_person_name', immergency_contact_no='$immergency_contact_no', hotel_address='$hotel_address', country='$country',website = '$website', opening_balance='$opening_balance',rating_star = '$rating_star',meal_plan='$meal_plan',hotel_type='$hotel_type', active_flag='$active_flag', bank_name='$bank_name',account_name='$account_name',account_no='$account_no', branch='$branch', ifsc_code='$ifsc_code',  service_tax_no='$service_tax_no', state_id='$state',side='$side1',pan_no='$supp_pan',as_of_date='$as_of_date',description='$description',policies='$policies',amenities='$amenities',cwb_from='$cwb_from', cwb_to='$cwb_to', cwob_from='$cwob_from', cwob_to='$cwob_to' where hotel_id='$hotel_id' ");

  if(!$sq){
    rollback_t();
    echo "error--Hotel supplier details not updated!";
    exit;
  }
  else{
    $vendor_login_master = new vendor_login_master;
    $vendor_login_master->vendor_login_update($vendor_login_id, $hotel_name, $mobile_no, $hotel_id, $active_flag, $email_id,'Hotel Vendor',$opening_balance,$side1,$as_of_date);
    if($GLOBALS['flag']){
      commit_t();
      echo "Hotel supplier has been successfully updated.";
      exit;
    }
    else{
      rollback_t();
      exit;
    }
  } 
}

///////////////////////***Hotel Master update end*********//////////////

}
?>