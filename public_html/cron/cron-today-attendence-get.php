<?php
//LIVE - PROD SITE
//set_include_path('/home3/bayyanor/public_html/ick/academy/includes/');

//LIVE - QA SITE
 //set_include_path('/home3/bayyanor/public_html/ick/academy_new/includes/');

//Devlopment - QA SITE
//set_include_path('/webroot/b/a/bayyan005/ick-saturday-aca.click2clock.com/www/includes/');

//Devlopment - QA SITE
set_include_path('/webroot/b/a/bayyan005/icksaturdayqa.click2clock.com/www/includes/');

include_once "config.php";
include_once "FortePayment.class.php";


$current_date = date('Y-m-d');
$today_numeric_day = date('w');

$created_user_id = $db->get_var("select id from ss_user where (username='systemuser' OR email='digitalsupport@quasardigital.com') and is_active = 1 AND  is_deleted = 0");


//DATE_FORMAT( `fieldname` , '%d-%m-%Y' )

 $school_days = $db->get_row("select school_opening_days from ss_client_settings where status = 1 and client_user_id = '".$_SESSION['icksumm_uat_login_userid']."'");
 $serdays = unserialize($school_days->school_opening_days);

if(count((array)$serdays) > 0){

 if(in_array($today_numeric_day, $serdays)){


	$check_attendence = $db->get_results("select * from ss_attendance where DATE_FORMAT(`attendance_date` , '%Y-%m-%d' ) = '".$current_date."' ");


	    if(count((array)$check_attendence) == 0){

             $holiday_array = [];
		     $holiday_all_group_array = [];

		     	  $check_holiday = $db->get_results("select * from ss_holidays where (ss_holidays.date_start <= '".$current_date."' and ss_holidays.date_end >= '".$current_date."')  and is_active=1 ");

		     	   if(count((array)$check_holiday) > 0){

                    foreach ($check_holiday as $holiday) {

		  	 	        if($holiday->is_for_all_groups == 0){
		  	 	      	  $holiday_array[] = $db->get_var("select group_id from ss_holiday_groups  where holiday_id = '".$holiday->id."'  ");
		  	 	         }elseif($holiday->is_for_all_groups == 1){
                          	$holiday_all_group_array[] = $holiday;
		  	 	         }

		  	 	   }

		  	 	}



		   if(count((array)$holiday_all_group_array) == 0){

		   $results = $db->get_results("select * from ss_classtime where  day_number='".$today_numeric_day."' and  is_active=1  and DATE_FORMAT(`created_on` , '%Y' ) = '".date('Y')."' ");


           if(count((array)$results) > 0){

		  	   foreach ($results as $row) {

		  	   	if(!in_array($row->group_id,$holiday_array)){

	                 $get_stu = $db->get_results("select * from ss_studentgroupmap where  group_id='".$row->group_id."' and  class_id='".$row->class_id."' and latest = 1 ");

	                 if(count((array)$get_stu) > 0){

			  	      foreach ($get_stu as $stu) {

			  	      	$sql_ret = $db->query("insert into ss_attendance set student_user_id='".$stu->student_user_id."',
							attendance_date='".$current_date."',student_group_map_id='".$stu->id."',classtime_id='".$row->id."',
							is_present='-1', created_by_user_id = '".$created_user_id."', 
							created_on='".date('Y-m-d H:i:s')."', updated_by_user_id = '".$created_user_id."', 
							updated_on='".date('Y-m-d H:i:s')."'"); 

                         
			  	       }

			  	     }



                 }

		  		
		  	 }

		  	 echo "Sucess";

	     }

	 }


	 }


  }


}





?>