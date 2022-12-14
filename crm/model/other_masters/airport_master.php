<?php 
$flag = true;
class airport_master{

	public function airport_save()
	{
		$city_id_arr = $_POST['city_id_arr'];
		$airport_name_arr = $_POST['airport_name_arr'];
		$airport_code_arr = ($_POST['airport_code_arr']!='')?$_POST['airport_code_arr']:[];
		$airport_status_arr = $_POST['airport_status_arr'];

		$created_at = date('Y-m-d H:i');

		begin_t();

		for($i=0; $i<sizeof($airport_code_arr); $i++){

			$airport_name1 = addslashes($airport_name_arr[$i]);
			$airport_code1 = addslashes($airport_code_arr[$i]);
			
			$sq_count = mysqli_num_rows(mysqlQuery("select airport_id from airport_master where airport_code='$airport_code1'"));
			if($sq_count>0){
				$GLOBALS['flag'] = false;
				echo "error--".'('.$airport_code1.')'." already exists!";
				exit;
			}
			
			$sq_count = mysqli_num_rows(mysqlQuery("select airport_id from airport_master where airport_name='$airport_name1' and airport_code='$airport_code1'"));
			if($sq_count>0){
				$GLOBALS['flag'] = false;
				echo "error--".$airport_name1.'('.$airport_code1.')'." already exists!";
				exit;
			}

			$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(airport_id) as max from airport_master"));
			$airport_id = $sq_max['max'] + 1;

			$sq_airport = mysqlQuery("insert into airport_master (airport_id, city_id, airport_name, airport_code,flag, created_at) values ('$airport_id', '$city_id_arr[$i]', '$airport_name1', '$airport_code1','$airport_status_arr[$i]', '$created_at')");
			if(!$sq_airport){
				$GLOBALS['flag'] = false;
				echo "error--Some entries not saved";
			}

		}

		if($GLOBALS['flag']){
			commit_t();
			echo "Airport has been successfully saved.";
			exit;
		}
		else{
			rollback_t();
			exit;
		}
	}

	public function airport_update()
	{
		$airport_id = $_POST['airport_id'];
		$city_id = $_POST['city_id'];
		$airport_name = $_POST['airport_name'];
		$airport_code = $_POST['airport_code'];
		$active_flag = $_POST['active_flag'];

		$airport_name1 = addslashes($airport_name);
		$airport_code1 = addslashes($airport_code);

		$sq_count = mysqli_num_rows(mysqlQuery("select airport_id from airport_master where airport_code='$airport_code1' and airport_id!='$airport_id'"));
		if($sq_count>0){
			$GLOBALS['flag'] = false;
			echo "error--".'('.$airport_code1.')'." already exists!";
			exit;
		}
		$sq_count = mysqli_num_rows(mysqlQuery("select airport_id from airport_master where airport_name='$airport_name1' and airport_code='$airport_code1' and airport_id!='$airport_id'"));
		if($sq_count>0){
			$GLOBALS['flag'] = false;
			echo "error--".$airport_name1.'('.$airport_code1.')'." already exists!";
			exit;
		}

		$sq_airport = mysqlQuery("update airport_master set city_id='$city_id', airport_name='$airport_name1', airport_code='$airport_code1', flag = '$active_flag' where airport_id='$airport_id'");
		if($sq_airport){
			echo "Airport has been successfully updated.";
			exit;
		}
		else{
			echo "error--Airport not updated";
			exit;
		}

	}

}
?>