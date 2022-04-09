<?php

    class Student_model extends CI_Model{
        private $table = 'tbl_students';
        public function __construct()
        {
            parent::__construct();
            $this->load->database();
        }

        public function create_student($data)
        {
            return $this->db->insert($this->table, $data);
        }
        
        public function fetch_student($key)
        {
            $this->db->select('*')->where(['email' => $key])->or_where(['id' => $key])->order_by('id', 'desc');
            return $this->db->get($this->table)->row_object();
        }
        
        public function check_student_edit_email_existence($email,$id)
        {
            $this->db->select('*')->where(['email' => $email])->where(['id !=' => $id]);
            return count((array) $this->db->get($this->table)->row());

        }
        
        public function fetch_all_students()
        {
            $this->db->select('student.*, branch.name as branch_name');
            $this->db->from($this->table . " as student");
            $this->db->join("tbl_branches as branch", "student.branch_id = branch.id", "inner");
            $this->db->order_by('student.id', 'desc');
            return $this->db->get()->result();
        }
        
        public function update_student($student_data,$id)
        {
            $this->db->where('id', $id);
            return $this->db->update($this->table, $student_data);
        }
        
        public function delete_student($id)
        {
            $this->db->where('id', $id);
            return $this->db->delete($this->table);
        }
    }