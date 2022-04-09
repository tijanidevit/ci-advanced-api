<?php    
    require APPPATH . 'libraries/REST_Controller.php';

    header('Accesss-Control-Allow-Origin: *');
    header('Accesss-Control-Allow-Methods: POST, GET');

    class Branch extends REST_Controller{
        public function __construct()
        {
            parent::__construct();
            $this->load->model('api/branch_model');
        }

        public function list_get()
        {
            $branches = $this->branch_model->fetch_all_branches();
            if (!empty($branches)) {
                return $this->response([
                    'status' => 1,
                    'message' => 'Branches fetched successfully',
                    'data' => $branches
                ], parent::HTTP_OK);
            }
            else {
                return $this->response([
                    'status' => 0,
                    'message' => 'No Branches added yet',
                ], parent::HTTP_OK);
            }
        }

        public function create_post()
        {
            $data = $this->_getJsonData();

            if (!isset($data->name)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Branch name is a required field',
                ], parent::HTTP_NOT_FOUND); 
            }

            $branch_data = [
                'name' => $this->security->xss_clean($data->name)
            ];

            $check_branch_existence = $this->branch_model->fetch_branch($data->name);

            if (!empty($check_branch_existence)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Branch already added in the database',
                ], parent::HTTP_CONFLICT);    
            }

            if ($this->branch_model->create_branch($branch_data)) {
                $branch = $this->branch_model->fetch_branch($data->name);
                return $this->response([
                    'status' => 1,
                    'message' => 'Branch created successfully',
                    'data' => $branch,
                ], parent::HTTP_CREATED);
            }
            else{
                return $this->response([
                    'status' => 0,
                    'message' => 'Unable to create branch'
                ], parent::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        public function delete_branch_delete()
        {
            $data = $this->_getJsonData();
            
            if (!isset($data->id)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Branch id is required',
                ], parent::HTTP_NOT_FOUND); 
            }
            
            $id = $data->id;
            $branch = $this->branch_model->fetch_branch($id);
            if (empty($branch)) {
                return $this->response([
                    'status' => 0,
                    'message' => 'Branches not found',
                ], parent::HTTP_NOT_FOUND);
            }

            if ($this->branch_model->delete_branch($id)) {
                return $this->response([
                    'status' => 1,
                    'message' => 'Branch deleted successfully',
                ], parent::HTTP_OK);
            }
            else {
                return $this->response([
                    'status' => 0,
                    'message' => 'Unable to deleted branch',
                ], parent::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

?>