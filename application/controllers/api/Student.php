<?php    
    require APPPATH . 'libraries/REST_Controller.php';

    header('Accesss-Control-Allow-Origin: *');
    header('Accesss-Control-Allow-Methods: POST, GET, DELETE, PUT');

    class Student extends REST_Controller{
        public function __construct()
        {
            parent::__construct();
            $this->load->model('api/student_model');
            $this->load->helper([
                'authorization',
                'jwt',
                'security'
            ]);
        }

        public function list_get()
        {
            $students = $this->student_model->fetch_all_students();
            return $this->response([
                'status' => 1,
                'message' => 'Student fetched successfully',
                'data' => $students
            ], parent::HTTP_OK);
        }

        public function register_post()
        {
            $data = $this->_getJsonData();
            if (!isset($data->name) || !isset($data->branch_id) || !isset($data->email) || !isset($data->phone) || !isset($data->gender) || !isset($data->password)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'All Field Required',
                ], parent::HTTP_CONFLICT);  
            }
            $student_data = [
                'name' => $this->security->xss_clean($data->name),
                'branch_id' => $this->security->xss_clean($data->branch_id),
                'email' => $this->security->xss_clean($data->email),
                'phone' => $this->security->xss_clean($data->phone),
                'gender' => $this->security->xss_clean($data->gender),
                'password' => password_hash($data->password, PASSWORD_DEFAULT),
            ];

            $check_student_existence = $this->student_model->fetch_student($data->email);

            if (!empty($check_student_existence)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Email already existed in our database',
                ], parent::HTTP_CONFLICT);    
            }

            if ($this->student_model->create_student($student_data)) {
                $student = $this->student_model->fetch_student($data->email);
                return $this->response([
                    'status' => 1,
                    'message' => 'Student account created successfully',
                    'data' => $student,
                ], parent::HTTP_CREATED);
            }
        }

        public function login_post()
        {
            $data = $this->_getJsonData();

            $student = $this->student_model->fetch_student($data->email);

            if (empty($student)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Email address not found',
                ], parent::HTTP_CONFLICT);    
            }

            $verify_password = password_verify($data->password, $student->password);
            if (!$verify_password) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Inalid password',
                ], parent::HTTP_CONFLICT);
            }
            else {
                $token = authorization::generateToken((array) $student);
                return $this->response([
                    'status' => 1,
                    'message' => 'User logged in successfully',
                    'data' => $token,
                ], parent::HTTP_OK);
            }
        }

        public function update_put()
        {
            $data = $this->_getJsonData();
            if (!isset($data->id) || !isset($data->name) || !isset($data->branch_id) || !isset($data->email) || !isset($data->phone) || !isset($data->gender) || !isset($data->password)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'All Field Required',
                ], parent::HTTP_CONFLICT);  
            }
            $student_data = [
                'name' => $this->security->xss_clean($data->name),
                'branch_id' => $this->security->xss_clean($data->branch_id),
                'email' => $this->security->xss_clean($data->email),
                'phone' => $this->security->xss_clean($data->phone),
                'gender' => $this->security->xss_clean($data->gender),
            ];

            $is_student_edit_email_existing = $this->student_model->check_student_edit_email_existence($data->email,$data->id);

            if (!empty($is_student_edit_email_existing)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Email already existed in our database',
                ], parent::HTTP_CONFLICT);    
            }

            if ($this->student_model->update_student($student_data, $data->id)) {
                $student = $this->student_model->fetch_student($data->email);
                return $this->response([
                    'status' => 1,
                    'message' => 'Student account updated successfully',
                    'data' => $student,
                ], parent::HTTP_CREATED);
            }
        }

        public function delete_student_delete()
        {
            $data = $this->_getJsonData();
            
            if (!isset($data->id)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Student id is required',
                ], parent::HTTP_NOT_FOUND); 
            }
            
            $id = $data->id;
            $student = $this->student_model->fetch_student($id);
            if (empty($student)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Student not found',
                ], parent::HTTP_NOT_FOUND);
            }

            if ($this->student_model->delete_student($id)) {
                return $this->response([
                    'status' => 1,
                    'message' => 'Student deleted successfully',
                ], parent::HTTP_OK);
            }
            else {
                return $this->response([
                    'status' => 0,
                    'message' => 'Unable to deleted student',
                ], parent::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

?>