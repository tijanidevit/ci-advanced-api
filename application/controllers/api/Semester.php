<?php    
    require APPPATH . 'libraries/REST_Controller.php';

    header('Accesss-Control-Allow-Origin: *');
    header('Accesss-Control-Allow-Methods: POST, GET, DELETE, PUT');

    class Semester extends REST_Controller{
        public function __construct()
        {
            parent::__construct();
            $this->load->model('api/semester_model');
            $this->load->helper([
                'authorization',
                'jwt',
                'security'
            ]);
        }

        // public function index_get()
        // {
        //     $auth_student = auth_user($this);
        // }

        public function create_project_post()
        {
            $auth_student = auth_user($this);

            if (!$auth_student) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Token invalid! Please login again',
                ], parent::HTTP_UNAUTHORIZED);
            }

            $student_id = $auth_student->id;
            $data = $this->_getJsonData();

            if (!isset($data->title) || !isset($data->level) || !isset($data->description) || !isset($data->complete_days) || !isset($data->semester) ) {
                return $this->response([
                    'status' => 0,
                    'message' => 'All fields are required',
                ], parent::HTTP_CONFLICT); 
            }

            $project_data = [
                'student_id' => $student_id,
                'title' => $this->security->xss_clean($data->title),
                'level' => $this->security->xss_clean($data->level),
                'description' => $this->security->xss_clean($data->description),
                'complete_days' => $this->security->xss_clean($data->complete_days),
                'semester' => $this->security->xss_clean($data->semester),
            ];

            if ($this->semester_model->create_semester_project($project_data)) {
                return $this->response([
                    'status' => 1,
                    'message' => 'Semester project created successfully',
                ], parent::HTTP_CREATED);
            }
            else{
                return $this->response([
                    'status' => 0,
                    'message' => 'Unable to create semester project'
                ], parent::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        public function list_get()
        {
            $projects = $this->semester_model->fetch_all_semester_projects();
            if (empty($projects)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Semester projects empty',
                    'data' => $projects
                ], parent::HTTP_NOT_FOUND);
            }
            return $this->response([
                'status' => 1,
                'message' => 'Semester projects fetched successfully',
                'data' => $projects
            ], parent::HTTP_OK);
        }
        public function student_project_get()
        {
            $auth_student = auth_user($this);

            if (!$auth_student) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Token invalid! Please login again',
                ], parent::HTTP_UNAUTHORIZED);
            }

            $student_id = $auth_student->id;
            $projects = $this->semester_model->fetch_student_projects($student_id);
            if (empty($projects)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Student projects empty',
                    'data' => $projects
                ], parent::HTTP_NOT_FOUND);
            }
            return $this->response([
                'status' => 1,
                'message' => 'Student projects fetched successfully',
                'data' => $projects
            ], parent::HTTP_OK);
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
            $semester_data = [
                'name' => $this->security->xss_clean($data->name),
                'branch_id' => $this->security->xss_clean($data->branch_id),
                'email' => $this->security->xss_clean($data->email),
                'phone' => $this->security->xss_clean($data->phone),
                'gender' => $this->security->xss_clean($data->gender),
            ];

            $is_semester_edit_email_existing = $this->semester_model->check_semester_edit_email_existence($data->email,$data->id);

            if (!empty($is_semester_edit_email_existing)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Email already existed in our database',
                ], parent::HTTP_CONFLICT);    
            }

            if ($this->semester_model->update_semester($semester_data, $data->id)) {
                $semester = $this->semester_model->fetch_semester($data->email);
                return $this->response([
                    'status' => 1,
                    'message' => 'Semester account updated successfully',
                    'data' => $semester,
                ], parent::HTTP_CREATED);
            }
        }

        public function delete_semester_delete()
        {
            $data = $this->_getJsonData();
            
            if (!isset($data->id)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Semester id is required',
                ], parent::HTTP_NOT_FOUND); 
            }
            
            $id = $data->id;
            $semester = $this->semester_model->fetch_semester($id);
            if (empty($semester)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Semester not found',
                ], parent::HTTP_NOT_FOUND);
            }

            if ($this->semester_model->delete_semester($id)) {
                return $this->response([
                    'status' => 1,
                    'message' => 'Semester deleted successfully',
                ], parent::HTTP_OK);
            }
            else {
                return $this->response([
                    'status' => 0,
                    'message' => 'Unable to deleted semester',
                ], parent::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        public function delete_student_project_delete()
        {
            $auth_student = auth_user($this);

            if (!$auth_student) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Token invalid! Please login again',
                ], parent::HTTP_UNAUTHORIZED);
            }

            $student_id = $auth_student->id;
            $delete_status = $this->semester_model->delete_student_projects($student_id);
            if (!($delete_status)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Unable to delete student projects',
                ], parent::HTTP_CONFLICT);
            }
            return $this->response([
                'status' => 1,
                'message' => 'Student projects deleted successfully',
            ], parent::HTTP_OK);
        }
    }

?>