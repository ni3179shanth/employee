<?php

class Admin_Class
{	

/* -------------------------set_database_connection_using_PDO---------------------- */

	public function __construct()
	{ 
        $host_name='localhost';
		$user_name='root';
		$password='';
		$db_name='emp_db';

		try{
			$connection=new PDO("mysql:host={$host_name}; dbname={$db_name}", $user_name,  $password);
			$this->db = $connection; // connection established
		} catch (PDOException $message ) {
			echo $message->getMessage();
		}
	}

/* ---------------------- test_form_input_data ----------------------------------- */
	
	public function test_form_input_data($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
	return $data;
	}

 
/* ---------------------- Admin Login Check ----------------------------------- */

    public function admin_login_check($data) {
        
        $upass = $this->test_form_input_data(md5($data['admin_password']));
		$username = $this->test_form_input_data($data['username']);
        try
       {
          $stmt = $this->db->prepare("SELECT * FROM tbl_admin WHERE username=:uname AND password=:upass LIMIT 1");
          $stmt->execute(array(':uname'=>$username, ':upass'=>$upass));
          $userRow=$stmt->fetch(PDO::FETCH_ASSOC);
          if($stmt->rowCount() > 0)
          {
          		session_start();
	            $_SESSION['admin_id'] = $userRow['user_id'];
	            $_SESSION['name'] = $userRow['fullname'];
	            $_SESSION['security_key'] = 'rewsgf@%^&*nmghjjkh';
	            $_SESSION['user_role'] = $userRow['user_role'];
	            $_SESSION['temp_password'] = $userRow['temp_password'];

          		if($userRow['temp_password'] == null){
	                header('Location: task-info.php');
          		}else{
          			header('Location: changePasswordForEmployee.php');
          		}
                
             
          }else{
			  $message = 'Invalid user name or Password';
              return $message;
		  }
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }	
		
    }



    public function change_password_for_employee($data){
    	$password  = $this->test_form_input_data($data['password']);
		$re_password = $this->test_form_input_data($data['re_password']);

		$user_id = $this->test_form_input_data($data['user_id']);
		$final_password = md5($password);
		$temp_password = '';

		if($password == $re_password){
			try{
				$update_user = $this->db->prepare("UPDATE tbl_admin SET password = :x, temp_password = :y WHERE user_id = :id ");

				$update_user->bindparam(':x', $final_password);
				$update_user->bindparam(':y', $temp_password);
				$update_user->bindparam(':id', $user_id);
				$update_user->execute();



				$stmt = $this->db->prepare("SELECT * FROM tbl_admin WHERE user_id=:id LIMIT 1");
		          $stmt->execute(array(':id'=>$user_id));
		          $userRow=$stmt->fetch(PDO::FETCH_ASSOC);

		          if($stmt->rowCount() > 0){
			          		session_start();
				            $_SESSION['admin_id'] = $userRow['user_id'];
				            $_SESSION['name'] = $userRow['fullname'];
				            $_SESSION['security_key'] = 'rewsgf@%^&*nmghjjkh';
				            $_SESSION['user_role'] = $userRow['user_role'];
				            $_SESSION['temp_password'] = $userRow['temp_password'];

				            header('Location: task-info.php');
			          }

			}catch (PDOException $e) {
				echo $e->getMessage();
			}

		}else{
			$message = 'Sorry !! Password Can not match';
            return $message;
		}

		
    }


/* -------------------- Admin Logout ----------------------------------- */

    public function admin_logout() {
        
        session_start();
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_name']);
        unset($_SESSION['security_key']);
        unset($_SESSION['user_role']);
        header('Location: index.php');
    }

/*----------- add_new_user--------------*/

	public function add_new_user($data){
		$user_fullname  = $this->test_form_input_data($data['em_fullname']);
		$user_username = $this->test_form_input_data($data['em_username']);
		$user_email = $this->test_form_input_data($data['em_email']);
		$temp_password = rand(000000001,10000000);
		$user_password = $this->test_form_input_data(md5($temp_password));
		$user_role = 2;
		try{
			$sqlEmail = "SELECT email FROM tbl_admin WHERE email = '$user_email' ";
			$query_result_for_email = $this->manage_all_info($sqlEmail);
			$total_email = $query_result_for_email->rowCount();

			$sqlUsername = "SELECT username FROM tbl_admin WHERE username = '$user_username' ";
			$query_result_for_username = $this->manage_all_info($sqlUsername);
			$total_username = $query_result_for_username->rowCount();

			if($total_email != 0 && $total_username != 0){
				$message = "Email and Password both are already taken";
            	return $message;

			}elseif($total_username != 0){
				$message = "Username Already Taken";
            	return $message;

			}elseif($total_email != 0){
				$message = "Email Already Taken";
            	return $message;

			}else{
				$add_user = $this->db->prepare("INSERT INTO tbl_admin (fullname, username, email, password, temp_password, user_role) VALUES (:x, :y, :z, :a, :b, :c) ");

				$add_user->bindparam(':x', $user_fullname);
				$add_user->bindparam(':y', $user_username);
				$add_user->bindparam(':z', $user_email);
				$add_user->bindparam(':a', $user_password);
				$add_user->bindparam(':b', $temp_password);
				$add_user->bindparam(':c', $user_role);

				$add_user->execute();
			}


		}catch (PDOException $e) {
			echo $e->getMessage();
		}
	}


/* ---------update_user_data----------*/

	public function update_user_data($data, $id){
		$user_fullname  = $this->test_form_input_data($data['em_fullname']);
		$user_username = $this->test_form_input_data($data['em_username']);
		$user_email = $this->test_form_input_data($data['em_email']);
		try{
			$update_user = $this->db->prepare("UPDATE tbl_admin SET fullname = :x, username = :y, email = :z WHERE user_id = :id ");

			$update_user->bindparam(':x', $user_fullname);
			$update_user->bindparam(':y', $user_username);
			$update_user->bindparam(':z', $user_email);
			$update_user->bindparam(':id', $id);
			
			$update_user->execute();

			$_SESSION['update_user'] = 'update_user';

			header('Location: admin-manage-user.php');
		}catch (PDOException $e) {
			echo $e->getMessage();
		}
	}


/* ------------update_admin_data-------------------- */

	public function update_admin_data($data, $id){
		$user_fullname  = $this->test_form_input_data($data['em_fullname']);
		$user_username = $this->test_form_input_data($data['em_username']);
		$user_email = $this->test_form_input_data($data['em_email']);

		try{
			$update_user = $this->db->prepare("UPDATE tbl_admin SET fullname = :x, username = :y, email = :z WHERE user_id = :id ");

			$update_user->bindparam(':x', $user_fullname);
			$update_user->bindparam(':y', $user_username);
			$update_user->bindparam(':z', $user_email);
			$update_user->bindparam(':id', $id);
			
			$update_user->execute();

			header('Location: manage-admin.php');
		}catch (PDOException $e) {
			echo $e->getMessage();
		}
	}


/* ------update_user_password------------------*/
	
	public function update_user_password($data, $id){
		$employee_password  = $this->test_form_input_data(md5($data['employee_password']));
		
		try{
			$update_user_password = $this->db->prepare("UPDATE tbl_admin SET password = :x WHERE user_id = :id ");

			$update_user_password->bindparam(':x', $employee_password);
			$update_user_password->bindparam(':id', $id);
			
			$update_user_password->execute();

			$_SESSION['update_user_pass'] = 'update_user_pass';

			header('Location: admin-manage-user.php');
		}catch (PDOException $e) {
			echo $e->getMessage();
		}
	}




/* -------------admin_password_change------------*/

	public function admin_password_change($data, $id){
		$admin_old_password  = $this->test_form_input_data(md5($data['admin_old_password']));
		$admin_new_password  = $this->test_form_input_data(md5($data['admin_new_password']));
		$admin_cnew_password  = $this->test_form_input_data(md5($data['admin_cnew_password']));
		$admin_raw_password = $this->test_form_input_data($data['admin_new_password']);
		
		try{

			// old password matching check 

			$sql = "SELECT * FROM tbl_admin WHERE user_id = '$id' AND password = '$admin_old_password' ";

			$query_result = $this->manage_all_info($sql);

			$total_row = $query_result->rowCount();
			$all_error = '';
			if($total_row == 0){
				$all_error = "Invalid old password";
			}
			

			if($admin_new_password != $admin_cnew_password ){
				$all_error .= '<br>'."New and Confirm New password do not match";
			}

			$password_length = strlen($admin_raw_password);

			if($password_length < 6){
				$all_error .= '<br>'."Password length must be more then 6 character";
			}

			if(empty($all_error)){
				$update_admin_password = $this->db->prepare("UPDATE tbl_admin SET password = :x WHERE user_id = :id ");

				$update_admin_password->bindparam(':x', $admin_new_password);
				$update_admin_password->bindparam(':id', $id);
				
				$update_admin_password->execute();

				$_SESSION['update_user_pass'] = 'update_user_pass';

				header('Location: admin-manage-user.php');

			}else{
				return $all_error;
			}

			
		}catch (PDOException $e) {
			echo $e->getMessage();
		}
	}




	/* =================Task Related===================== */

	public function add_new_task($data){
		// data insert   
		$task_title  = $this->test_form_input_data($data['task_title']);
		$task_description = $this->test_form_input_data($data['task_description']);
		$t_start_time = $this->test_form_input_data($data['t_start_time']);
		$t_end_time = $this->test_form_input_data($data['t_end_time']);
		$assign_to = $this->test_form_input_data($data['assign_to']);

		try{
			$add_task = $this->db->prepare("INSERT INTO task_info (t_title, t_description, t_start_time, 	t_end_time, t_user_id) VALUES (:x, :y, :z, :a, :b) ");

			$add_task->bindparam(':x', $task_title);
			$add_task->bindparam(':y', $task_description);
			$add_task->bindparam(':z', $t_start_time);
			$add_task->bindparam(':a', $t_end_time);
			$add_task->bindparam(':b', $assign_to);

			$add_task->execute();

			$_SESSION['Task_msg'] = 'Task Add Successfully';

			header('Location: task-info.php');
		}catch (PDOException $e) {
			echo $e->getMessage();
		}
	}


		public function update_task_info($data, $task_id, $user_role){
			$task_title  = $this->test_form_input_data($data['task_title']);
			$task_description = $this->test_form_input_data($data['task_description']);
			$t_start_time = $this->test_form_input_data($data['t_start_time']);
			$t_end_time = $this->test_form_input_data($data['t_end_time']);
			$status = $this->test_form_input_data($data['status']);

			if($user_role == 1){
				$assign_to = $this->test_form_input_data($data['assign_to']);
			}else{
				$sql = "SELECT * FROM task_info WHERE task_id='$task_id' ";
				$info = $this->manage_all_info($sql);
				$row = $info->fetch(PDO::FETCH_ASSOC);
				$assign_to = $row['t_user_id'];

			}

			try{
				$update_task = $this->db->prepare("UPDATE task_info SET t_title = :x, t_description = :y, t_start_time = :z, t_end_time = :a, t_user_id = :b, status = :c WHERE task_id = :id ");

				$update_task->bindparam(':x', $task_title);
				$update_task->bindparam(':y', $task_description);
				$update_task->bindparam(':z', $t_start_time);
				$update_task->bindparam(':a', $t_end_time);
				$update_task->bindparam(':b', $assign_to);
				$update_task->bindparam(':c', $status);
				$update_task->bindparam(':id', $task_id);

				$update_task->execute();

				$_SESSION['Task_msg'] = 'Task Update Successfully';

				header('Location: task-info.php');
			}catch (PDOException $e) {
				echo $e->getMessage();
			}

		}


	/* =================Attendance Related===================== */
	public function add_punch_in($data){
		// data insert 
		$date = new DateTime('now', new DateTimeZone('Asia/Dhaka'));
 		
		$user_id  = $this->test_form_input_data($data['user_id']);
		$punch_in_time = $date->format('d-m-Y H:i:s');

		try{
			$add_attendance = $this->db->prepare("INSERT INTO attendance_info (atn_user_id, in_time) VALUES ('$user_id', '$punch_in_time') ");
			$add_attendance->execute();

			header('Location: attendance-info.php');

		}catch (PDOException $e) {
			echo $e->getMessage();
		}
	}


	public function add_punch_out($data){
		$date = new DateTime('now', new DateTimeZone('Asia/Dhaka'));
		$punch_out_time = $date->format('d-m-Y H:i:s');
		$punch_in_time  = $this->test_form_input_data($data['punch_in_time']);

		$dteStart = new DateTime($punch_in_time);
        $dteEnd   = new DateTime($punch_out_time);
        $dteDiff  = $dteStart->diff($dteEnd);
        $total_duration = $dteDiff->format("%H:%I:%S");

		$attendance_id  = $this->test_form_input_data($data['aten_id']);

		try{
			$update_user = $this->db->prepare("UPDATE attendance_info SET out_time = :x, total_duration = :y WHERE aten_id = :id ");

			$update_user->bindparam(':x', $punch_out_time);
			$update_user->bindparam(':y', $total_duration);
			$update_user->bindparam(':id', $attendance_id);
			
			$update_user->execute();

			header('Location: attendance-info.php');
		}catch (PDOException $e) {
			echo $e->getMessage();
		}

	}



	/* --------------------delete_data_by_this_method--------------*/

	public function delete_data_by_this_method($sql,$action_id,$sent_po){
		try{
			$delete_data = $this->db->prepare($sql);

			$delete_data->bindparam(':id', $action_id);

			$delete_data->execute();

			header('Location: '.$sent_po);
		}catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

/* ----------------------manage_all_info--------------------- */

	public function manage_all_info($sql) {
		try{
			$info = $this->db->prepare($sql);
			$info->execute();
			return $info;
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}





}
?>